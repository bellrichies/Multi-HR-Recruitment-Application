import { ArrowLeft, MessageCircle, Plus, Search, Send, Star, UserRound } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import {
  conversationMessages,
  conversationParticipants,
  conversations,
  createConversation,
  favoriteConversation,
  sendConversationMessage,
  unfavoriteConversation,
} from '../../api/messages.api';
import { listJobs } from '../../api/jobs.api';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Select } from '../../components/ui/Select';
import { useApi } from '../../hooks/useApi';
import { usePermissions } from '../../hooks/usePermissions';
import { useAuth } from '../../store/auth.store';
import { formatDate } from '../../utils/formatDate';

function emptyNewConversationFor(role) {
  return {
    participant_user_id: '',
    conversation_type: role === 'recruiter' ? 'job_context' : 'direct',
    subject: '',
    job_id: '',
    message_body: '',
  };
}

const conversationFilters = [
  { key: 'all', label: 'All' },
  { key: 'unread', label: 'Unread' },
  { key: 'read', label: 'Read' },
  { key: 'favorites', label: 'Favorites' },
];

export function MessagesPage() {
  const { user } = useAuth();
  const { hasPermission, primaryRole } = usePermissions();
  const [searchParams, setSearchParams] = useSearchParams();
  const initialConversationId = searchParams.get('conversation');
  const [view, setView] = useState(initialConversationId ? 'thread' : 'list');
  const [selectedConversationId, setSelectedConversationId] = useState(initialConversationId);
  const [draft, setDraft] = useState('');
  const [sendError, setSendError] = useState(null);
  const [sending, setSending] = useState(false);
  const [conversationSearch, setConversationSearch] = useState('');
  const [debouncedConversationSearch, setDebouncedConversationSearch] = useState('');
  const [conversationFilter, setConversationFilter] = useState('all');
  const [favoriteSavingId, setFavoriteSavingId] = useState(null);
  const [participantSearch, setParticipantSearch] = useState('');
  const [newConversation, setNewConversation] = useState(() => emptyNewConversationFor(primaryRole()));
  const [createError, setCreateError] = useState(null);
  const [creating, setCreating] = useState(false);
  const messagesEndRef = useRef(null);
  const canSend = hasPermission('messages.send');
  const canViewJobs = hasPermission('jobs.view');
  const conversationQuery = useApi(
    () =>
      conversations({
        per_page: 50,
        search: debouncedConversationSearch,
        filter: conversationFilter,
      }),
    [debouncedConversationSearch, conversationFilter],
  );
  const participantQuery = useApi(
    () =>
      canSend
        ? conversationParticipants({
            search: participantSearch,
            conversation_type: newConversation.conversation_type,
            job_id: newConversation.job_id,
            limit: 20,
          })
        : Promise.resolve({ data: [] }),
    [canSend, participantSearch, newConversation.conversation_type, newConversation.job_id],
  );
  const jobsQuery = useApi(
    () => (canSend && canViewJobs ? listJobs({ per_page: 50 }) : Promise.resolve({ data: [] })),
    [canSend, canViewJobs],
  );
  const conversationRows = conversationQuery.data || [];
  const participantRows = participantQuery.data || [];
  const jobRows = jobsQuery.data || [];
  const selectedConversation = useMemo(
    () => conversationRows.find((conversation) => Number(conversation.id) === Number(selectedConversationId)) || null,
    [conversationRows, selectedConversationId],
  );
  const messagesQuery = useApi(
    () => {
      if (!selectedConversationId) {
        return Promise.resolve({ data: [], meta: null });
      }

      return conversationMessages(selectedConversationId, { per_page: 100 });
    },
    [selectedConversationId],
  );
  const messageRows = messagesQuery.data || [];

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      setDebouncedConversationSearch(conversationSearch.trim());
    }, 250);

    return () => window.clearTimeout(timeoutId);
  }, [conversationSearch]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ block: 'end' });
  }, [messageRows]);

  useEffect(() => {
    const nextConversationId = searchParams.get('conversation');

    if (nextConversationId) {
      setSelectedConversationId(nextConversationId);
      setView('thread');
    }
  }, [searchParams]);

  useEffect(() => {
    if (selectedConversationId && !messagesQuery.loading && !messagesQuery.error) {
      conversationQuery.refresh();
    }
  }, [selectedConversationId, messagesQuery.loading, messagesQuery.error, conversationQuery.refresh]);

  function openConversation(conversationId) {
    setSelectedConversationId(conversationId);
    setSearchParams({ conversation: String(conversationId) });
    setView('thread');
    setSendError(null);
    setCreateError(null);
  }

  function openNewConversation() {
    setNewConversation(emptyNewConversationFor(primaryRole()));
    setView('new');
    setSelectedConversationId(null);
    setSearchParams({});
    setSendError(null);
    setCreateError(null);
  }

  function returnToList() {
    setView('list');
    setSearchParams({});
    setSendError(null);
    setCreateError(null);
  }

  function selectParticipant(participant) {
    setNewConversation((current) => ({
      ...current,
      participant_user_id: String(participant.id),
    }));
  }

  async function handleCreateConversation(event) {
    event.preventDefault();

    if (!newConversation.participant_user_id || creating) {
      return;
    }

    setCreating(true);
    setCreateError(null);

    const payload = {
      participant_user_id: Number(newConversation.participant_user_id),
      conversation_type: newConversation.conversation_type,
      subject: newConversation.subject.trim() || undefined,
      job_id: newConversation.job_id ? Number(newConversation.job_id) : undefined,
      message_body: newConversation.message_body.trim() || undefined,
    };

    try {
      const response = await createConversation(payload);
      setNewConversation(emptyNewConversationFor(primaryRole()));
      setParticipantSearch('');
      await conversationQuery.refresh();
      openConversation(response.data.id);
    } catch (error) {
      setCreateError(error);
    } finally {
      setCreating(false);
    }
  }

  async function handleSend(event) {
    event.preventDefault();

    const messageBody = draft.trim();

    if (!selectedConversationId || !messageBody || sending) {
      return;
    }

    setSending(true);
    setSendError(null);

    try {
      await sendConversationMessage(selectedConversationId, { message_body: messageBody });
      setDraft('');
      await Promise.all([messagesQuery.refresh(), conversationQuery.refresh()]);
    } catch (error) {
      setSendError(error);
    } finally {
      setSending(false);
    }
  }

  async function toggleFavorite(conversation) {
    if (!conversation || favoriteSavingId) {
      return;
    }

    setFavoriteSavingId(conversation.id);

    try {
      if (conversation.is_favorite) {
        await unfavoriteConversation(conversation.id);
      } else {
        await favoriteConversation(conversation.id);
      }

      await conversationQuery.refresh();
    } finally {
      setFavoriteSavingId(null);
    }
  }

  if (conversationQuery.loading && conversationRows.length === 0) return <LoadingState label="Loading conversations..." />;
  if (conversationQuery.error) return <ErrorState error={conversationQuery.error} onRetry={conversationQuery.refresh} />;

  return (
    <section className="space-y-4">
      <div className={view === 'list' ? 'block' : 'hidden md:block'}>
        <Breadcrumbs items={['Messages']} />
        <div className="mt-2 flex items-center justify-between gap-3">
          <h1 className="text-2xl font-semibold text-ink">Messages</h1>
          {canSend ? (
            <Button className="shrink-0" type="button" onClick={openNewConversation}>
              <Plus size={16} />
              New
            </Button>
          ) : null}
        </div>
      </div>

      <div className="h-[calc(100dvh-7rem)] overflow-hidden rounded-md border border-line bg-white md:grid md:h-[680px] md:grid-cols-[360px_1fr]">
        <aside className={`${view === 'list' ? 'flex' : 'hidden'} h-full min-h-0 flex-col border-line md:flex md:border-r`}>
          <div className="flex items-center justify-between gap-3 border-b border-line bg-brand px-4 py-4 text-white md:bg-white md:text-ink">
            <div className="min-w-0">
              <p className="text-lg font-semibold md:text-sm">Chats</p>
              <p className="text-xs text-white/80 md:text-muted">{conversationQuery.meta?.total ?? conversationRows.length} conversations</p>
            </div>
            {canSend ? (
              <button
                aria-label="Start new conversation"
                className="focus-ring inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-brand shadow-sm md:bg-brand md:text-white"
                type="button"
                onClick={openNewConversation}
              >
                <Plus size={18} />
              </button>
            ) : null}
          </div>

          <div className="space-y-3 border-b border-line bg-white p-3">
            <label className="flex items-center gap-2 rounded-md border border-line bg-panel px-3">
              <Search className="text-muted" size={16} />
              <input
                className="focus-ring h-10 min-w-0 flex-1 border-0 bg-transparent text-sm text-ink focus:ring-0 focus:ring-offset-0"
                placeholder="Search recipients or subject"
                type="search"
                value={conversationSearch}
                onChange={(event) => setConversationSearch(event.target.value)}
              />
            </label>
            <div className="grid grid-cols-4 gap-2">
              {conversationFilters.map((item) => (
                <button
                  className={`focus-ring rounded-md px-2 py-2 text-xs font-semibold ${
                    conversationFilter === item.key ? 'bg-brand text-white' : 'bg-panel text-muted hover:text-ink'
                  }`}
                  key={item.key}
                  type="button"
                  onClick={() => setConversationFilter(item.key)}
                >
                  {item.label}
                </button>
              ))}
            </div>
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto bg-white">
            {conversationRows.length === 0 ? (
              <div className="flex h-full flex-col items-center justify-center px-6 text-center">
                <MessageCircle className="text-muted" size={34} />
                <p className="mt-3 text-sm font-semibold text-ink">{emptyConversationTitle(conversationSearch, conversationFilter)}</p>
              </div>
            ) : (
              conversationRows.map((conversation) => {
                const isSelected = Number(conversation.id) === Number(selectedConversationId);
                const unreadCount = Number(conversation.unread_count || 0);
                const title = conversationTitle(conversation, user?.id);
                const subtitle = conversationSubtitle(conversation, user?.id);

                return (
                  <div
                    className={`focus-ring flex w-full items-center gap-3 border-b border-line px-4 py-3 text-left transition ${
                      isSelected && view === 'thread' ? 'bg-teal-50' : 'hover:bg-panel'
                    }`}
                    key={conversation.id}
                  >
                    <button className="flex min-w-0 flex-1 items-center gap-3 text-left" type="button" onClick={() => openConversation(conversation.id)}>
                      <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold text-brand">
                        {initials(title)}
                      </span>
                      <span className="min-w-0 flex-1">
                        <span className="flex min-w-0 items-center justify-between gap-2">
                          <span className="truncate text-sm font-semibold text-ink">{title}</span>
                          <span className="shrink-0 text-[11px] text-muted">{formatMessageTime(conversation.updated_at)}</span>
                        </span>
                        <span className="mt-1 flex min-w-0 items-center justify-between gap-2">
                          <span className="truncate text-xs text-muted">{subtitle}</span>
                          {unreadCount > 0 ? (
                            <span className="rounded-full bg-brand px-2 py-0.5 text-[11px] font-semibold text-white">{unreadCount}</span>
                          ) : null}
                        </span>
                      </span>
                    </button>
                    <button
                      aria-label={conversation.is_favorite ? 'Remove from favorites' : 'Add to favorites'}
                      className={`focus-ring inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${
                        conversation.is_favorite ? 'text-accent hover:bg-amber-50' : 'text-muted hover:bg-panel hover:text-accent'
                      }`}
                      disabled={Number(favoriteSavingId) === Number(conversation.id)}
                      type="button"
                      onClick={() => toggleFavorite(conversation)}
                    >
                      <Star fill={conversation.is_favorite ? 'currentColor' : 'none'} size={17} />
                    </button>
                  </div>
                );
              })
            )}
          </div>
        </aside>

        <main className={`${view === 'list' ? 'hidden md:flex' : 'flex'} h-full min-h-0 flex-col bg-panel`}>
          {view === 'thread' ? (
            <ThreadView
              canSend={canSend}
              draft={draft}
              messagesEndRef={messagesEndRef}
              messagesQuery={messagesQuery}
              messageRows={messageRows}
              favoriteSavingId={favoriteSavingId}
              selectedConversation={selectedConversation}
              sendError={sendError}
              sending={sending}
              user={user}
              onBack={returnToList}
              onDraftChange={setDraft}
              onToggleFavorite={toggleFavorite}
              onSend={handleSend}
            />
          ) : null}

          {view === 'new' ? (
            <NewConversationView
              createError={createError}
              creating={creating}
              form={newConversation}
              jobsLoading={jobsQuery.loading}
              jobRows={jobRows}
              participantRows={participantRows}
              participantSearch={participantSearch}
              participantsLoading={participantQuery.loading}
              selectedParticipant={participantRows.find((participant) => String(participant.id) === newConversation.participant_user_id)}
              onBack={returnToList}
              onChange={setNewConversation}
              onCreate={handleCreateConversation}
              onParticipantSearch={setParticipantSearch}
              onSelectParticipant={selectParticipant}
            />
          ) : null}

          {view === 'list' ? (
            <div className="hidden h-full flex-col items-center justify-center px-8 text-center md:flex">
              <MessageCircle className="text-muted" size={42} />
              <p className="mt-4 text-sm font-semibold text-ink">No thread open</p>
            </div>
          ) : null}
        </main>
      </div>
    </section>
  );
}

function ThreadView({
  canSend,
  draft,
  favoriteSavingId,
  messagesEndRef,
  messagesQuery,
  messageRows,
  selectedConversation,
  sendError,
  sending,
  user,
  onBack,
  onDraftChange,
  onToggleFavorite,
  onSend,
}) {
  const title = selectedConversation ? conversationTitle(selectedConversation, user?.id) : 'Conversation';
  const subtitle = selectedConversation ? conversationSubtitle(selectedConversation, user?.id) : 'Messages';

  return (
    <>
      <div className="flex items-center justify-between gap-3 border-b border-line bg-brand px-3 py-3 text-white md:bg-white md:px-4 md:text-ink">
        <div className="flex min-w-0 items-center gap-3">
          <button className="focus-ring rounded-full p-2 md:hidden" type="button" onClick={onBack}>
            <ArrowLeft size={20} />
          </button>
          <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-sm font-semibold text-brand md:bg-teal-50">
            {initials(title)}
          </span>
          <span className="min-w-0">
            <span className="block truncate text-sm font-semibold">{title}</span>
            <span className="block truncate text-xs text-white/80 md:text-muted">{subtitle}</span>
          </span>
        </div>
        {selectedConversation ? (
          <button
            aria-label={selectedConversation.is_favorite ? 'Remove from favorites' : 'Add to favorites'}
            className={`focus-ring inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full ${
              selectedConversation.is_favorite ? 'text-amber-200 md:text-accent' : 'text-white/80 hover:bg-white/10 md:text-muted md:hover:bg-panel md:hover:text-accent'
            }`}
            disabled={Number(favoriteSavingId) === Number(selectedConversation.id)}
            type="button"
            onClick={() => onToggleFavorite(selectedConversation)}
          >
            <Star fill={selectedConversation.is_favorite ? 'currentColor' : 'none'} size={18} />
          </button>
        ) : null}
      </div>

      <div className="min-h-0 flex-1 overflow-y-auto bg-[#eef2f1] px-3 py-4 md:bg-panel md:p-5">
        {messagesQuery.loading ? <LoadingState label="Loading messages..." /> : null}
        {messagesQuery.error ? <ErrorState error={messagesQuery.error} onRetry={messagesQuery.refresh} /> : null}
        {!messagesQuery.loading && !messagesQuery.error && messageRows.length === 0 ? (
          <div className="rounded-md border border-dashed border-line bg-white p-6 text-center text-sm text-muted">
            No messages in this conversation yet.
          </div>
        ) : null}
        {!messagesQuery.loading && !messagesQuery.error ? (
          <div className="space-y-2">
            {messageRows.map((message) => {
              const isMine = Number(message.sender_id) === Number(user?.id);

              return (
                <div className={`flex ${isMine ? 'justify-end' : 'justify-start'}`} key={message.id}>
                  <article
                    className={`max-w-[86%] rounded-lg px-3 py-2 text-sm shadow-sm md:max-w-[72%] ${
                      isMine ? 'rounded-br-sm bg-[#d9fdd3] text-ink' : 'rounded-bl-sm bg-white text-ink'
                    }`}
                  >
                    <p className="whitespace-pre-wrap break-words leading-relaxed">{message.message_body}</p>
                    {message.attachment_path ? (
                      <a className="mt-2 block font-medium text-brand underline" href={message.attachment_path}>
                        View attachment
                      </a>
                    ) : null}
                    <p className="mt-1 text-right text-[11px] text-muted">{formatMessageTime(message.created_at)}</p>
                  </article>
                </div>
              );
            })}
            <div ref={messagesEndRef} />
          </div>
        ) : null}
      </div>

      <form className="border-t border-line bg-white p-3" onSubmit={onSend}>
        {sendError ? <p className="mb-2 text-sm font-medium text-danger">{sendError.message}</p> : null}
        <div className="flex items-end gap-2">
          <textarea
            className="focus-ring max-h-32 min-h-11 flex-1 resize-y rounded-2xl border border-line bg-panel px-4 py-3 text-sm text-ink"
            disabled={!selectedConversation || !canSend || sending}
            placeholder={canSend ? 'Message' : 'Sending is unavailable'}
            value={draft}
            onChange={(event) => onDraftChange(event.target.value)}
          />
          <button
            aria-label="Send message"
            className="focus-ring inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand text-white disabled:cursor-not-allowed disabled:opacity-60"
            disabled={!selectedConversation || !canSend || !draft.trim() || sending}
            type="submit"
          >
            <Send size={18} />
          </button>
        </div>
      </form>
    </>
  );
}

function NewConversationView({
  createError,
  creating,
  form,
  jobsLoading,
  jobRows,
  participantRows,
  participantSearch,
  participantsLoading,
  selectedParticipant,
  onBack,
  onChange,
  onCreate,
  onParticipantSearch,
  onSelectParticipant,
}) {
  const requiresJob = form.conversation_type !== 'direct';

  return (
    <>
      <div className="flex items-center gap-3 border-b border-line bg-brand px-3 py-3 text-white md:bg-white md:px-4 md:text-ink">
        <button className="focus-ring rounded-full p-2 md:hidden" type="button" onClick={onBack}>
          <ArrowLeft size={20} />
        </button>
        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-brand md:bg-teal-50">
          <UserRound size={18} />
        </span>
        <span className="min-w-0">
          <span className="block truncate text-sm font-semibold">New conversation</span>
          <span className="block truncate text-xs text-white/80 md:text-muted">Recipient and message details</span>
        </span>
      </div>

      <form className="min-h-0 flex-1 overflow-y-auto bg-white p-4 md:bg-panel" onSubmit={onCreate}>
        <div className="mx-auto max-w-2xl space-y-5">
          {createError ? <ErrorState error={createError} /> : null}

          <div>
            <label className="block text-sm font-medium text-ink" htmlFor="participant-search">
              Recipient
            </label>
            <div className="mt-1 flex items-center gap-2 rounded-md border border-line bg-white px-3">
              <Search className="text-muted" size={16} />
              <input
                className="focus-ring h-10 min-w-0 flex-1 border-0 bg-transparent text-sm text-ink focus:ring-0 focus:ring-offset-0"
                id="participant-search"
                placeholder="Search by name or email"
                type="search"
                value={participantSearch}
                onChange={(event) => onParticipantSearch(event.target.value)}
              />
            </div>

            <div className="mt-3 max-h-64 overflow-y-auto rounded-md border border-line bg-white">
              {participantsLoading ? <p className="p-4 text-sm text-muted">Loading recipients...</p> : null}
              {!participantsLoading && participantRows.length === 0 ? (
                <p className="p-4 text-sm text-muted">No recipients found.</p>
              ) : null}
              {!participantsLoading
                ? participantRows.map((participant) => {
                    const isSelected = String(participant.id) === form.participant_user_id;

                    return (
                      <button
                        className={`focus-ring flex w-full items-center gap-3 border-b border-line px-4 py-3 text-left last:border-b-0 ${
                          isSelected ? 'bg-teal-50' : 'hover:bg-panel'
                        }`}
                        key={participant.id}
                        type="button"
                        onClick={() => onSelectParticipant(participant)}
                      >
                        <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold text-brand">
                          {initials(participant.name || participant.email)}
                        </span>
                        <span className="min-w-0">
                          <span className="block truncate text-sm font-semibold text-ink">{participant.name || participant.email}</span>
                          <span className="block truncate text-xs text-muted">{participant.email}</span>
                        </span>
                      </button>
                    );
                  })
                : null}
            </div>
            {selectedParticipant ? (
              <p className="mt-2 text-xs font-medium text-brand">Selected: {selectedParticipant.name || selectedParticipant.email}</p>
            ) : null}
          </div>

          <div className="grid gap-4 md:grid-cols-2">
            <Select
              label="Conversation type"
              value={form.conversation_type}
              onChange={(event) =>
                onChange((current) => ({
                  ...current,
                  conversation_type: event.target.value,
                  job_id: event.target.value === 'direct' ? '' : current.job_id,
                  participant_user_id: '',
                }))
              }
            >
              <option value="direct">Direct</option>
              <option value="job_context">Job context</option>
              <option value="interview_request">Interview request</option>
            </Select>

            {requiresJob ? (
              <Select
                label="Related job"
                value={form.job_id}
                onChange={(event) => onChange((current) => ({ ...current, job_id: event.target.value, participant_user_id: '' }))}
              >
                <option value="">{jobsLoading ? 'Loading jobs...' : 'No job selected'}</option>
                {jobRows.map((job) => (
                  <option key={job.id} value={job.id}>
                    {job.title}
                  </option>
                ))}
              </Select>
            ) : (
              <Input
                label="Subject"
                placeholder="Optional"
                value={form.subject}
                onChange={(event) => onChange((current) => ({ ...current, subject: event.target.value }))}
              />
            )}
          </div>

          {requiresJob ? (
            <Input
              label="Subject"
              placeholder="Optional"
              value={form.subject}
              onChange={(event) => onChange((current) => ({ ...current, subject: event.target.value }))}
            />
          ) : null}

          <label className="block">
            <span className="mb-1 block text-sm font-medium text-ink">First message</span>
            <textarea
              className="focus-ring min-h-32 w-full resize-y rounded-md border border-line bg-white px-3 py-2 text-sm text-ink"
              placeholder="Write the first message"
              value={form.message_body}
              onChange={(event) => onChange((current) => ({ ...current, message_body: event.target.value }))}
            />
          </label>

          <div className="flex justify-end gap-3">
            <Button type="button" variant="secondary" onClick={onBack}>
              Cancel
            </Button>
            <Button disabled={!form.participant_user_id || (requiresJob && !form.job_id) || creating} type="submit">
              <Send size={16} />
              {creating ? 'Starting' : 'Start'}
            </Button>
          </div>
        </div>
      </form>
    </>
  );
}

function conversationTitle(conversation, currentUserId) {
  const otherParticipants = (conversation.participants || []).filter((participant) => Number(participant.id) !== Number(currentUserId));
  const names = otherParticipants.map((participant) => participant.name || participant.email).filter(Boolean);

  return conversation.subject || names.join(', ') || formatConversationType(conversation.conversation_type) || `Conversation #${conversation.id}`;
}

function conversationSubtitle(conversation, currentUserId) {
  const otherParticipants = (conversation.participants || []).filter((participant) => Number(participant.id) !== Number(currentUserId));
  const names = otherParticipants.map((participant) => participant.name || participant.email).filter(Boolean);

  if (conversation.subject && names.length > 0) {
    return names.join(', ');
  }

  return formatConversationType(conversation.conversation_type);
}

function formatConversationType(value) {
  if (!value) {
    return 'Conversation';
  }

  return value
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function emptyConversationTitle(search, filter) {
  if (search.trim()) {
    return 'No matching conversations';
  }

  if (filter === 'favorites') {
    return 'No favorite conversations';
  }

  if (filter === 'unread') {
    return 'No unread conversations';
  }

  if (filter === 'read') {
    return 'No read conversations';
  }

  return 'No conversations yet';
}

function formatMessageTime(value) {
  if (!value) {
    return '';
  }

  return formatDate(value);
}

function initials(value) {
  const words = String(value || 'Conversation')
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  if (words.length === 0) {
    return 'C';
  }

  return words
    .slice(0, 2)
    .map((word) => word[0]?.toUpperCase())
    .join('');
}
