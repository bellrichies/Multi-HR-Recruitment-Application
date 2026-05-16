import { api } from './client';

export function discoverCandidates(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/candidates/discover${search ? `?${search}` : ''}`);
}

export function candidateSummary(id) {
  return api.get(`/candidates/${id}/summary`);
}
