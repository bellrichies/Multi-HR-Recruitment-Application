import { api } from './client';

export function notifications(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/notifications${search ? `?${search}` : ''}`);
}

export function unreadNotifications() {
  return api.get('/notifications/unread-count');
}
