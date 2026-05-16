import { useMemo } from 'react';
import { Navigate } from 'react-router-dom';
import { dashboardForRole } from '../../api/reports.api';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { ChartCard } from '../../components/charts/ChartCard';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { StatCard } from '../../components/ui/StatCard';
import { DataTable } from '../../components/tables/DataTable';
import { useApi } from '../../hooks/useApi';
import { useAuth } from '../../store/auth.store';
import { formatCurrency } from '../../utils/formatCurrency';
import { dashboardForUser } from '../../utils/roles';

const roleTitles = {
  super_admin: 'Super Admin Dashboard',
  hr_officer: 'HR Officer Dashboard',
  relationship_officer: 'Relationship Officer Dashboard',
  recruiter: 'Recruiter Dashboard',
  job_seeker: 'Job Seeker Dashboard',
};

export function DashboardRedirect() {
  const { user } = useAuth();
  const role = user?.roles?.[0]?.slug || 'job_seeker';
  const routes = {
    super_admin: '/dashboard/admin',
    hr_officer: '/dashboard/hr-officer',
    relationship_officer: '/dashboard/relationship-officer',
    recruiter: '/dashboard/recruiter',
    job_seeker: '/dashboard/job-seeker',
  };

  return <Navigate replace to={routes[role] || routes.job_seeker} />;
}

export function DashboardPage({ role }) {
  const { data, loading, error, refresh } = useApi(() => dashboardForRole(role), [role]);
  const stats = useMemo(() => dashboardStats(role, data || {}), [role, data]);

  if (loading) {
    return <LoadingState label="Loading dashboard..." />;
  }

  if (error) {
    return <ErrorState error={error} onRetry={refresh} />;
  }

  return (
    <div className="space-y-6">
      <div>
        <Breadcrumbs items={['Dashboard', roleTitles[role]]} />
        <h1 className="mt-2 text-2xl font-semibold text-ink">{roleTitles[role]}</h1>
      </div>
      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        {stats.map((stat) => <StatCard key={stat.label} {...stat} />)}
      </div>
      <DashboardCharts role={role} data={data || {}} />
      <DashboardTables role={role} data={data || {}} />
    </div>
  );
}

export function RoleDashboardPage({ role }) {
  const { user } = useAuth();
  const roleSlugs = (user?.roles || []).map((item) => item.slug || item);
  const isAllowed = role === 'super_admin'
    ? roleSlugs.includes('super_admin')
    : roleSlugs.includes(role);

  if (!isAllowed) {
    return <Navigate replace to={dashboardForUser(user)} />;
  }

  return <DashboardPage role={role} />;
}

function dashboardStats(role, data) {
  const maps = {
    super_admin: [
      ['Total users', data.total_users],
      ['Recruiters', data.total_recruiters],
      ['Job seekers', data.total_job_seekers],
      ['HR officers', data.total_hr_officers],
      ['Relationship officers', data.total_relationship_officers],
      ['Total jobs', data.total_jobs],
      ['Active jobs', data.active_jobs],
      ['Revenue', formatCurrency(data.revenue)],
    ],
    hr_officer: [
      ['Assigned candidates', data.assigned_candidates],
      ['Assigned jobs', data.assigned_jobs],
      ['Pending screenings', data.pending_screenings],
      ['Interviews today', data.interviews_today],
      ['Assessment results pending', data.assessment_results_pending],
      ['Candidates placed', data.candidates_placed],
    ],
    relationship_officer: [
      ['Assigned employers', data.assigned_employers],
      ['Assigned jobs', data.assigned_jobs],
      ['Open jobs', data.open_jobs],
      ['Jobs pending update', data.jobs_pending_update],
    ],
    recruiter: [
      ['Wallet balance', formatCurrency(data.wallet_balance)],
      ['Jobs posted', data.jobs_posted],
      ['Active jobs', data.active_jobs],
      ['Matched candidates', data.matched_candidates],
      ['Unlocked candidates', data.unlocked_candidates],
      ['Interviews scheduled', data.interviews_scheduled],
      ['Hired candidates', data.hired_candidates],
    ],
    job_seeker: [
      ['Profile completion', `${data.profile_completion || 0}%`],
      ['Recommended jobs', data.recommended_jobs?.length || 0],
      ['Applications', data.applications?.length || 0],
      ['Assessments', data.assessments?.length || 0],
      ['Interviews', data.interviews?.length || 0],
      ['Unread messages', data.messages?.unread_count || 0],
      ['Notifications', data.notifications?.length || 0],
    ],
  };

  return (maps[role] || []).map(([label, value]) => ({ label, value }));
}

function DashboardCharts({ role, data }) {
  if (role === 'super_admin') {
    return (
      <div className="grid gap-4 xl:grid-cols-2">
        <ChartCard title="Users by role" data={data.analytics?.users_by_role} />
        <ChartCard title="Applications by stage" data={data.analytics?.applications_by_stage} />
      </div>
    );
  }

  if (role === 'hr_officer') {
    return <ChartCard title="Pipeline summary" data={data.pipeline_summary} />;
  }

  if (role === 'relationship_officer') {
    return <ChartCard title="Fulfillment progress" data={data.fulfillment_progress} />;
  }

  if (role === 'recruiter') {
    return <ChartCard title="Applications by stage" data={data.analytics?.applications_by_stage} />;
  }

  return <ChartCard title="Application stages" data={data.analytics?.application_stages} />;
}

function DashboardTables({ role, data }) {
  if (role === 'super_admin') {
    return (
      <DataTable
        columns={[
          { key: 'action', label: 'Action' },
          { key: 'module', label: 'Module' },
          { key: 'created_at', label: 'Created' },
        ]}
        emptyTitle="No audit activity"
        rows={data.recent_audit_logs || []}
      />
    );
  }

  if (role === 'recruiter') {
    return (
      <DataTable
        columns={[
          { key: 'internal_reference', label: 'Reference' },
          { key: 'amount', label: 'Amount', render: (row) => formatCurrency(row.amount, row.currency) },
          { key: 'status', label: 'Status' },
        ]}
        emptyTitle="No payments yet"
        rows={data.payment_history || []}
      />
    );
  }

  if (role === 'job_seeker') {
    return (
      <DataTable
        columns={[
          { key: 'job_title', label: 'Job' },
          { key: 'current_stage', label: 'Stage' },
          { key: 'status', label: 'Status' },
        ]}
        emptyTitle="No applications yet"
        rows={data.applications || []}
      />
    );
  }

  return null;
}
