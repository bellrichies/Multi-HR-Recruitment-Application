import { useParams } from 'react-router-dom';
import { jobDetail } from '../../api/jobs.api';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { StatCard } from '../../components/ui/StatCard';
import { useApi } from '../../hooks/useApi';
import { formatCurrency } from '../../utils/formatCurrency';
import { formatDate } from '../../utils/formatDate';

export function JobDetailPage() {
  const { id } = useParams();
  const { data, loading, error, refresh } = useApi(() => jobDetail(id), [id]);
  const job = data?.job || data;

  if (loading) return <LoadingState label="Loading job detail..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Jobs', job?.title || 'Detail']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">{job?.title}</h1>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Status" value={job?.status} />
        <StatCard label="Location" value={job?.location} />
        <StatCard label="Work mode" value={job?.work_mode} />
        <StatCard label="Deadline" value={formatDate(job?.application_deadline)} />
      </div>
      <article className="rounded-md border border-line bg-white p-5">
        <h2 className="text-base font-semibold text-ink">Role summary</h2>
        <p className="mt-3 whitespace-pre-line text-sm leading-7 text-muted">{job?.description}</p>
        <dl className="mt-5 grid gap-4 text-sm sm:grid-cols-2">
          <div><dt className="font-semibold text-ink">Salary</dt><dd className="text-muted">{formatCurrency(job?.salary_min, job?.currency)} - {formatCurrency(job?.salary_max, job?.currency)}</dd></div>
          <div><dt className="font-semibold text-ink">Employment type</dt><dd className="text-muted">{job?.employment_type}</dd></div>
        </dl>
      </article>
    </section>
  );
}
