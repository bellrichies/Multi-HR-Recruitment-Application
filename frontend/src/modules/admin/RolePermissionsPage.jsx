import { permissions, roles } from '../../api/roles.api';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { useApi } from '../../hooks/useApi';

export function RolePermissionsPage() {
  const roleState = useApi(roles, []);
  const permissionState = useApi(permissions, []);

  if (roleState.loading || permissionState.loading) return <LoadingState label="Loading roles and permissions..." />;
  if (roleState.error) return <ErrorState error={roleState.error} onRetry={roleState.refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Admin', 'Roles and permissions']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Roles and Permissions</h1>
      </div>
      <DataTable
        columns={[
          { key: 'name', label: 'Role' },
          { key: 'slug', label: 'Slug' },
          { key: 'description', label: 'Description' },
        ]}
        emptyTitle="No roles configured"
        rows={roleState.data || []}
      />
      <DataTable
        columns={[
          { key: 'name', label: 'Permission' },
          { key: 'slug', label: 'Slug' },
          { key: 'module', label: 'Module' },
        ]}
        emptyTitle="No permissions configured"
        rows={permissionState.data || []}
      />
    </section>
  );
}
