import { Navigate, Route, Routes } from 'react-router-dom';
import { AuthProvider } from './store/auth.store';
import { AuthLayout } from './layouts/AuthLayout';
import { DashboardLayout } from './layouts/DashboardLayout';
import { PublicLayout } from './layouts/PublicLayout';
import { LoginPage } from './modules/auth/LoginPage';
import { RegisterPage } from './modules/auth/RegisterPage';
import { ApplicationPipelinePage } from './modules/applications/ApplicationPipelinePage';
import { AssessmentsPage } from './modules/assessments/AssessmentsPage';
import { CandidateDiscoveryPage } from './modules/candidates/CandidateDiscoveryPage';
import { ForbiddenPage } from './modules/common/ForbiddenPage';
import { DashboardPage, DashboardRedirect } from './modules/dashboard/DashboardPage';
import { InterviewsPage } from './modules/interviews/InterviewsPage';
import { JobDetailPage } from './modules/jobs/JobDetailPage';
import { JobsPage } from './modules/jobs/JobsPage';
import { MessagesPage } from './modules/messages/MessagesPage';
import { NotificationsPage } from './modules/notifications/NotificationsPage';
import { ReportsPage } from './modules/reports/ReportsPage';
import { RolePermissionsPage } from './modules/admin/RolePermissionsPage';
import { UserManagementPage } from './modules/admin/UserManagementPage';
import { WalletPage } from './modules/wallet/WalletPage';
import { ProtectedRoute } from './routes/ProtectedRoute';
import { PermissionRoute } from './routes/PermissionRoute';

export default function App() {
  return (
    <AuthProvider>
      <Routes>
        <Route element={<PublicLayout />}>
          <Route index element={<Navigate replace to="/login" />} />
        </Route>
        <Route element={<AuthLayout />}>
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register/:type" element={<RegisterPage />} />
        </Route>
        <Route element={<ProtectedRoute />}>
          <Route element={<DashboardLayout />}>
            <Route path="/dashboard" element={<DashboardRedirect />} />
            <Route path="/dashboard/admin" element={<DashboardPage role="super_admin" />} />
            <Route path="/dashboard/hr-officer" element={<DashboardPage role="hr_officer" />} />
            <Route path="/dashboard/relationship-officer" element={<DashboardPage role="relationship_officer" />} />
            <Route path="/dashboard/recruiter" element={<DashboardPage role="recruiter" />} />
            <Route path="/dashboard/job-seeker" element={<DashboardPage role="job_seeker" />} />
            <Route element={<PermissionRoute permission="jobs.view" />}>
              <Route path="/jobs" element={<JobsPage />} />
              <Route path="/jobs/:id" element={<JobDetailPage />} />
            </Route>
            <Route element={<PermissionRoute permission="candidates.discover" />}>
              <Route path="/candidates" element={<CandidateDiscoveryPage />} />
            </Route>
            <Route element={<PermissionRoute permission="applications.view" />}>
              <Route path="/applications" element={<ApplicationPipelinePage />} />
            </Route>
            <Route element={<PermissionRoute permission="wallet.view" />}>
              <Route path="/wallet" element={<WalletPage />} />
            </Route>
            <Route element={<PermissionRoute permission="reports.view" />}>
              <Route path="/reports" element={<ReportsPage />} />
            </Route>
            <Route element={<PermissionRoute permission="roles.view" />}>
              <Route path="/roles-permissions" element={<RolePermissionsPage />} />
            </Route>
            <Route element={<PermissionRoute permission="users.view" />}>
              <Route path="/users" element={<UserManagementPage />} />
            </Route>
            <Route element={<PermissionRoute permission="messages.view" />}>
              <Route path="/messages" element={<MessagesPage />} />
            </Route>
            <Route element={<PermissionRoute permission="notifications.view" />}>
              <Route path="/notifications" element={<NotificationsPage />} />
            </Route>
            <Route element={<PermissionRoute permission="assessments.view" />}>
              <Route path="/assessments" element={<AssessmentsPage />} />
            </Route>
            <Route element={<PermissionRoute permission="interviews.view" />}>
              <Route path="/interviews" element={<InterviewsPage />} />
            </Route>
            <Route path="/forbidden" element={<ForbiddenPage />} />
          </Route>
        </Route>
        <Route path="*" element={<Navigate replace to="/dashboard" />} />
      </Routes>
    </AuthProvider>
  );
}
