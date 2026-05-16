import { api } from './client';

export function notifications(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/notifications${search ? `?${search}` : ''}`);
}

export function notificationDetail(notificationId) {
  return api.get(`/notifications/${notificationId}`);
}

export function unreadNotifications() {
  return api.get('/notifications/unread-count');
}

export function markNotificationRead(notificationId) {
  return api.post(`/notifications/${notificationId}/read`);
}

export function markNotificationUnread(notificationId) {
  return api.post(`/notifications/${notificationId}/unread`);
}

export function markAllNotificationsRead() {
  return api.post('/notifications/read-all');
}
