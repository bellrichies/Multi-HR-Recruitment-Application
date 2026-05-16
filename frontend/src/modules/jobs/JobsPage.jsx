import { Link } from 'react-router-dom';
import { listJobs } from '../../api/jobs.api';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { useApi } from '../../hooks/useApi';
import { formatCurrency } from '../../utils/formatCurrency';

export function JobsPage() {
  const { data, loading, error, refresh } = useApi(() => listJobs({ per_page: 20 }), []);

  if (loading) return <LoadingState label="Loading jobs..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Jobs']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Jobs</h1>
      </div>
      <DataTable
        columns={[
          { key: 'title', label: 'Title', render: (row) => <Link className="font-semibold text-brand" to={`/jobs/${row.id}`}>{row.title}</Link> },
          { key: 'location', label: 'Location' },
          { key: 'status', label: 'Status' },
          { key: 'salary', label: 'Salary', render: (row) => `${formatCurrency(row.salary_min, row.currency)} - ${formatCurrency(row.salary_max, row.currency)}` },
        ]}
        emptyTitle="No jobs found"
        rows={data || []}
      />
    </section>
  );
}
