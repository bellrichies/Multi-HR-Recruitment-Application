import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { EmptyState } from '../../components/feedback/EmptyState';

export function InterviewsPage() {
  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Interviews']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Interviews</h1>
      </div>
      <EmptyState title="Interview workspace" description="Scheduling, rescheduling, cancellation, and feedback APIs are ready for deeper UI workflows." />
    </section>
  );
}
