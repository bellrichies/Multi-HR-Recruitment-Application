import { discoverCandidates } from '../../api/candidates.api';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { useApi } from '../../hooks/useApi';

export function CandidateDiscoveryPage() {
  const { data, loading, error, refresh } = useApi(() => discoverCandidates({ per_page: 20 }), []);

  if (loading) return <LoadingState label="Loading candidates..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Candidates', 'Discovery']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Candidate Discovery</h1>
      </div>
      <DataTable
        columns={[
          { key: 'profile_code', label: 'Profile' },
          { key: 'current_job_title', label: 'Current title' },
          { key: 'location', label: 'Location' },
          { key: 'profile_completion_percentage', label: 'Completion', render: (row) => `${row.profile_completion_percentage || 0}%` },
        ]}
        emptyTitle="No candidates match the current filters"
        rows={data || []}
      />
    </section>
  );
}
