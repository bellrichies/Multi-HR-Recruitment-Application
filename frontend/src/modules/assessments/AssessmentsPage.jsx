import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { EmptyState } from '../../components/feedback/EmptyState';

export function AssessmentsPage() {
  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Assessments']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Assessments</h1>
      </div>
      <EmptyState title="Assessment workspace" description="Assessment APIs are available and can be expanded into creation, assignment, submission, and grading views." />
    </section>
  );
}
