export const dashboardRoutes = {
  super_admin: '/dashboard/admin',
  hr_officer: '/dashboard/hr-officer',
  relationship_officer: '/dashboard/relationship-officer',
  recruiter: '/dashboard/recruiter',
  job_seeker: '/dashboard/job-seeker',
};

export function dashboardForUser(user) {
  const role = user?.roles?.[0]?.slug || user?.roles?.[0] || 'job_seeker';

  return dashboardRoutes[role] || dashboardRoutes.job_seeker;
}
