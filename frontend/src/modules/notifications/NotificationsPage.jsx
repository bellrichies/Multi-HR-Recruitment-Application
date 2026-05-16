import { notifications } from '../../api/notifications.api';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { useApi } from '../../hooks/useApi';

export function NotificationsPage() {
  const { data, loading, error, refresh } = useApi(() => notifications({ per_page: 20 }), []);

  if (loading) return <LoadingState label="Loading notifications..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Notifications']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Notifications</h1>
      </div>
      <DataTable
        columns={[
          { key: 'title', label: 'Title' },
          { key: 'type', label: 'Type' },
          { key: 'channel', label: 'Channel' },
          { key: 'created_at', label: 'Created' },
        ]}
        emptyTitle="No notifications"
        rows={data || []}
      />
    </section>
  );
}
