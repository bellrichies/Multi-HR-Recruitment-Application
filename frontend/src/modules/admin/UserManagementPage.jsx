import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { EmptyState } from '../../components/feedback/EmptyState';

export function UserManagementPage() {
  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Admin', 'Users']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">User Management</h1>
      </div>
      <EmptyState
        title="User listing API is not available yet"
        description="The backend currently includes role assignment endpoints, but a paginated user management endpoint is scheduled for a later hardening pass."
      />
    </section>
  );
}
