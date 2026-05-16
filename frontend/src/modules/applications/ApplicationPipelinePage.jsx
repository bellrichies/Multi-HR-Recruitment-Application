import { applications } from '../../api/applications.api';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { useApi } from '../../hooks/useApi';

export function ApplicationPipelinePage() {
  const { data, loading, error, refresh } = useApi(() => applications({ per_page: 20 }), []);

  if (loading) return <LoadingState label="Loading applications..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Applications', 'Pipeline']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Application Pipeline</h1>
      </div>
      <DataTable
        columns={[
          { key: 'id', label: 'ID' },
          { key: 'job_id', label: 'Job' },
          { key: 'current_stage', label: 'Stage' },
          { key: 'status', label: 'Status' },
          { key: 'match_score', label: 'Match score' },
        ]}
        emptyTitle="No applications yet"
        rows={data || []}
      />
    </section>
  );
}
