import { api } from './client';

export function applications(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/applications${search ? `?${search}` : ''}`);
}

export function moveApplicationStage(id, payload) {
  return api.post(`/applications/${id}/move-stage`, payload);
}
