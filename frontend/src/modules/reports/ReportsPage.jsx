import { auditReport, dashboardForRole, financialReport, placementReport } from '../../api/reports.api';
import { ChartCard } from '../../components/charts/ChartCard';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { DataTable } from '../../components/tables/DataTable';
import { StatCard } from '../../components/ui/StatCard';
import { useApi } from '../../hooks/useApi';
import { usePermissions } from '../../hooks/usePermissions';
import { formatCurrency } from '../../utils/formatCurrency';

export function ReportsPage() {
  const { hasRole, primaryRole } = usePermissions();

  if (!hasRole('super_admin')) {
    return <RoleReports role={primaryRole()} />;
  }

  return <AdminReports />;
}

function AdminReports() {
  const financial = useApi(financialReport, []);
  const placements = useApi(placementReport, []);
  const audit = useApi(() => auditReport({ per_page: 10 }), []);

  if (financial.loading || placements.loading || audit.loading) return <LoadingState label="Loading reports..." />;
  if (financial.error) return <ErrorState error={financial.error} onRetry={financial.refresh} />;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Reports']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">Reports</h1>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <StatCard label="Revenue" value={formatCurrency(financial.data?.revenue)} />
        <StatCard label="Pending payments" value={formatCurrency(financial.data?.pending_payments)} />
        <StatCard label="Wallet credits" value={formatCurrency(financial.data?.wallet_credits)} />
        <StatCard label="Placements" value={placements.data?.total_placements || 0} />
      </div>
      <div className="grid gap-4 xl:grid-cols-2">
        <ChartCard title="Revenue by month" data={financial.data?.revenue_by_month} />
        <ChartCard title="Placements by month" data={placements.data?.placements_by_month} />
      </div>
      <DataTable
        columns={[
          { key: 'action', label: 'Action' },
          { key: 'module', label: 'Module' },
          { key: 'created_at', label: 'Created' },
        ]}
        emptyTitle="No audit logs found"
        rows={audit.data || []}
      />
    </section>
  );
}

function RoleReports({ role }) {
  const { data, loading, error, refresh } = useApi(() => dashboardForRole(role), [role]);

  if (loading) return <LoadingState label="Loading role report..." />;
  if (error) return <ErrorState error={error} onRetry={refresh} />;

  const analytics = data?.analytics || {};
  const firstChart = analytics.applications_by_stage || analytics.application_stages || data?.pipeline_summary || data?.fulfillment_progress;

  return (
    <section className="space-y-6">
      <div>
        <Breadcrumbs items={['Reports', 'My report']} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">My Report</h1>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {Object.entries(data || {})
          .filter(([, value]) => ['number', 'string'].includes(typeof value))
          .slice(0, 8)
          .map(([key, value]) => (
            <StatCard key={key} label={key.replaceAll('_', ' ')} value={key.includes('balance') ? formatCurrency(value) : value} />
          ))}
      </div>
      <ChartCard title="Performance summary" data={firstChart} />
    </section>
  );
}
