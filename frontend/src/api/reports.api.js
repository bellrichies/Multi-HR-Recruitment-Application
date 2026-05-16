import { api } from './client';

export function dashboardForRole(role) {
  const routes = {
    super_admin: '/reports/admin/summary',
    hr_officer: '/reports/hr-officer/summary',
    relationship_officer: '/reports/relationship-officer/summary',
    recruiter: '/reports/recruiter/summary',
    job_seeker: '/reports/job-seeker/summary',
  };

  return api.get(routes[role] || routes.job_seeker);
}

export function financialReport() {
  return api.get('/reports/financial');
}

export function placementReport() {
  return api.get('/reports/placements');
}

export function auditReport(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/reports/audit${search ? `?${search}` : ''}`);
}
