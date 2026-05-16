import { ArrowLeft, Bell, Check, CheckCheck, ExternalLink, MailOpen, RefreshCw } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { notificationDetail, notifications } from '../../api/notifications.api';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { Button } from '../../components/ui/Button';
import { useApi } from '../../hooks/useApi';
import { useNotifications } from '../../store/notifications.store';
import { formatDate } from '../../utils/formatDate';

const filters = [
  { key: 'all', label: 'All' },
  { key: 'unread', label: 'Unread' },
  { key: 'read', label: 'Read' },
];

export function NotificationsPage() {
  const [searchParams, setSearchParams] = useSearchParams();
  const navigate = useNavigate();
  const [filter, setFilter] = useState('all');
  const [selectedId, setSelectedId] = useState(() => searchParams.get('notification'));
  const [detail, setDetail] = useState(null);
  const [detailError, setDetailError] = useState(null);
  const [detailLoading, setDetailLoading] = useState(false);
  const [view, setView] = useState(selectedId ? 'detail' : 'list');
  const notificationState = useNotifications();
  const { data, loading, error, refresh } = useApi(() => notifications({ per_page: 50 }), []);
  const rows = data || [];
  const selectedFromList = rows.find((notification) => String(notification.id) === String(selectedId));
  const selectedNotification = detail || selectedFromList || null;
  const visibleRows = useMemo(() => {
    if (filter === 'unread') {
      return rows.filter((notification) => !notification.read_at);
    }

    if (filter === 'read') {
      return rows.filter((notification) => notification.read_at);
    }

    return rows;
  }, [filter, rows]);

  useEffect(() => {
    const nextSelectedId = searchParams.get('notification');

    if (nextSelectedId) {
      setSelectedId(nextSelectedId);
      setView('detail');
    }
  }, [searchParams]);

  useEffect(() => {
    let cancelled = false;

    async function loadDetail() {
      if (!selectedId) {
        setDetail(null);
        setDetailError(null);
        return;
      }

      if (selectedFromList) {
        setDetail(selectedFromList);
        setDetailError(null);
        return;
      }

      setDetailLoading(true);
      setDetailError(null);

      try {
        const response = await notificationDetail(selectedId);

        if (!cancelled) {
          setDetail(response.data);
        }
      } catch (detailLoadError) {
        if (!cancelled) {
          setDetailError(detailLoadError);
        }
      } finally {
        if (!cancelled) {
          setDetailLoading(false);
        }
      }
    }

    loadDetail();

    return () => {
      cancelled = true;
    };
  }, [selectedId, selectedFromList]);

  async function selectNotification(notification) {
    setSelectedId(String(notification.id));
    setSearchParams({ notification: String(notification.id) });
    setView('detail');

    if (!notification.read_at) {
      const updated = await notificationState.markRead(notification.id);

      if (updated) {
        setDetail(updated);
      }

      await refresh();
    }
  }

  async function toggleRead(notification) {
    if (!notification) {
      return;
    }

    const updated = notification.read_at
      ? await notificationState.markUnread(notification.id)
      : await notificationState.markRead(notification.id);

    if (updated) {
      setDetail(updated);
    }

    await refresh();
  }

  async function handleMarkAllRead() {
    await notificationState.markAllRead();
    await refresh();
  }

  function closeDetail() {
    setView('list');
    setSelectedId(null);
    setDetail(null);
    setSearchParams({});
  }

  if (loading && rows.length === 0) return <LoadingState label="Loading notifications..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  return (
    <section className="space-y-4">
      <div className={view === 'list' ? 'block' : 'hidden md:block'}>
        <Breadcrumbs items={['Notifications']} />
        <div className="mt-2 flex flex-wrap items-center justify-between gap-3">
          <div>
            <h1 className="text-2xl font-semibold text-ink">Notifications</h1>
            <p className="mt-1 text-sm text-muted">{notificationState.unreadCount} unread</p>
          </div>
          <div className="flex items-center gap-2">
            <Button type="button" variant="secondary" onClick={refresh}>
              <RefreshCw size={16} />
              Refresh
            </Button>
            {notificationState.canManage && notificationState.unreadCount > 0 ? (
              <Button type="button" onClick={handleMarkAllRead}>
                <CheckCheck size={16} />
                Read all
              </Button>
            ) : null}
          </div>
        </div>
      </div>

      <div className="h-[calc(100dvh-7rem)] overflow-hidden rounded-md border border-line bg-white md:grid md:h-[680px] md:grid-cols-[380px_1fr]">
        <aside className={`${view === 'list' ? 'flex' : 'hidden'} h-full min-h-0 flex-col border-line md:flex md:border-r`}>
          <div className="border-b border-line bg-white px-4 py-3">
            <div className="grid grid-cols-3 gap-2">
              {filters.map((item) => (
                <button
                  className={`focus-ring rounded-md px-3 py-2 text-sm font-semibold ${
                    filter === item.key ? 'bg-brand text-white' : 'bg-panel text-muted hover:text-ink'
                  }`}
                  key={item.key}
                  type="button"
                  onClick={() => setFilter(item.key)}
                >
                  {item.label}
                </button>
              ))}
            </div>
          </div>

          <div className="min-h-0 flex-1 overflow-y-auto">
            {visibleRows.length === 0 ? (
              <div className="flex h-full flex-col items-center justify-center px-6 text-center">
                <Bell className="text-muted" size={34} />
                <p className="mt-3 text-sm font-semibold text-ink">No notifications</p>
              </div>
            ) : (
              visibleRows.map((notification) => {
                const isSelected = String(notification.id) === String(selectedId);

                return (
                  <NotificationListItem
                    isSelected={isSelected}
                    key={notification.id}
                    notification={notification}
                    onSelect={selectNotification}
                  />
                );
              })
            )}
          </div>
        </aside>

        <main className={`${view === 'list' ? 'hidden md:flex' : 'flex'} h-full min-h-0 flex-col bg-panel`}>
          <NotificationDetail
            canManage={notificationState.canManage}
            detailError={detailError}
            detailLoading={detailLoading}
            notification={selectedNotification}
            onBack={closeDetail}
            onOpenRelated={(path) => navigate(path)}
            onToggleRead={toggleRead}
          />
        </main>
      </div>
    </section>
  );
}

function NotificationListItem({ isSelected, notification, onSelect }) {
  const unread = !notification.read_at;

  return (
    <button
      className={`focus-ring flex w-full gap-3 border-b border-line px-4 py-3 text-left transition ${
        isSelected ? 'bg-teal-50' : unread ? 'bg-white hover:bg-teal-50' : 'hover:bg-panel'
      }`}
      type="button"
      onClick={() => onSelect(notification)}
    >
      <span className={`mt-1 h-2.5 w-2.5 shrink-0 rounded-full ${unread ? 'bg-brand' : 'bg-line'}`} />
      <span className="min-w-0 flex-1">
        <span className="flex min-w-0 items-start justify-between gap-3">
          <span className={`truncate text-sm ${unread ? 'font-semibold text-ink' : 'font-medium text-ink'}`}>{notification.title}</span>
          <span className="shrink-0 text-[11px] text-muted">{formatDate(notification.created_at)}</span>
        </span>
        <span className="mt-1 line-clamp-2 text-xs text-muted">{notification.body}</span>
        <span className="mt-2 inline-flex rounded-full bg-panel px-2 py-0.5 text-[11px] font-medium text-muted">
          {formatType(notification.type)}
        </span>
      </span>
    </button>
  );
}

function NotificationDetail({ canManage, detailError, detailLoading, notification, onBack, onOpenRelated, onToggleRead }) {
  const relatedPath = notification ? relatedNotificationPath(notification) : null;

  if (detailLoading) {
    return <LoadingState label="Loading notification..." />;
  }

  if (detailError) {
    return <ErrorState error={detailError} />;
  }

  if (!notification) {
    return (
      <div className="hidden h-full flex-col items-center justify-center px-8 text-center md:flex">
        <Bell className="text-muted" size={42} />
        <p className="mt-4 text-sm font-semibold text-ink">No notification selected</p>
      </div>
    );
  }

  return (
    <>
      <div className="flex items-center justify-between gap-3 border-b border-line bg-white px-3 py-3 md:px-4">
        <div className="flex min-w-0 items-center gap-2">
          <button className="focus-ring rounded-full p-2 md:hidden" type="button" onClick={onBack}>
            <ArrowLeft size={20} />
          </button>
          <span className={`h-2.5 w-2.5 shrink-0 rounded-full ${notification.read_at ? 'bg-line' : 'bg-brand'}`} />
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-ink">{notification.title}</p>
            <p className="truncate text-xs text-muted">{formatDate(notification.created_at)}</p>
          </div>
        </div>
        {canManage ? (
          <Button type="button" variant="secondary" onClick={() => onToggleRead(notification)}>
            {notification.read_at ? <MailOpen size={16} /> : <Check size={16} />}
            {notification.read_at ? 'Unread' : 'Read'}
          </Button>
        ) : null}
      </div>

      <div className="min-h-0 flex-1 overflow-y-auto p-4 md:p-6">
        <article className="mx-auto max-w-3xl rounded-md border border-line bg-white p-5">
          <div className="flex flex-wrap gap-2">
            <span className="rounded-full bg-teal-50 px-3 py-1 text-xs font-semibold text-brand">{formatType(notification.type)}</span>
            <span className="rounded-full bg-panel px-3 py-1 text-xs font-semibold text-muted">
              {notification.read_at ? 'Read' : 'Unread'}
            </span>
            <span className="rounded-full bg-panel px-3 py-1 text-xs font-semibold text-muted">{notification.channel}</span>
          </div>

          <h2 className="mt-5 text-xl font-semibold text-ink">{notification.title}</h2>
          <p className="mt-3 whitespace-pre-wrap text-sm leading-6 text-ink">{notification.body}</p>

          {notification.data ? <NotificationData data={notification.data} /> : null}

          {relatedPath ? (
            <Button className="mt-6" type="button" onClick={() => onOpenRelated(relatedPath)}>
              <ExternalLink size={16} />
              Open related item
            </Button>
          ) : null}
        </article>
      </div>
    </>
  );
}

function NotificationData({ data }) {
  const entries = Object.entries(data).filter(([, value]) => value !== null && value !== undefined && value !== '');

  if (entries.length === 0) {
    return null;
  }

  return (
    <dl className="mt-5 grid gap-3 rounded-md border border-line bg-panel p-4 sm:grid-cols-2">
      {entries.map(([key, value]) => (
        <div className="min-w-0" key={key}>
          <dt className="text-xs font-semibold uppercase text-muted">{key.replaceAll('_', ' ')}</dt>
          <dd className="mt-1 break-words text-sm text-ink">{String(value)}</dd>
        </div>
      ))}
    </dl>
  );
}

function relatedNotificationPath(notification) {
  const data = notification.data || {};

  if (data.conversation_id) {
    return `/messages?conversation=${data.conversation_id}`;
  }

  if (data.job_id) {
    return `/jobs/${data.job_id}`;
  }

  if (data.interview_id) {
    return '/interviews';
  }

  if (data.application_id) {
    return '/applications';
  }

  if (data.wallet_id || data.payment_id) {
    return '/wallet';
  }

  return null;
}

function formatType(value) {
  if (!value) {
    return 'Notification';
  }

  return value
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}
