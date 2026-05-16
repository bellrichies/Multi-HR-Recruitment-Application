import { api } from './client';

export function listJobs(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/jobs${search ? `?${search}` : ''}`);
}

export function publicJobs(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/public/jobs${search ? `?${search}` : ''}`);
}

export function jobDetail(id) {
  return api.get(`/jobs/${id}`);
}

export function publicJobDetail(slug) {
  return api.get(`/public/jobs/${slug}`);
}
