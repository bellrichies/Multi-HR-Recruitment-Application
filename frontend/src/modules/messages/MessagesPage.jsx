import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { EmptyState } from '../../components/feedback/EmptyState';

export function MessagesPage() {
  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Messages']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Messages</h1>
      </div>
      <EmptyState title="Select a conversation" description="Conversation list and message detail endpoints are ready for this workspace." />
    </section>
  );
}
