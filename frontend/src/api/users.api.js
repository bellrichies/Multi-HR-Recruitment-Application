import { api } from './client';

export function users(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/users${search ? `?${search}` : ''}`);
}

export function userDetail(userId) {
  return api.get(`/users/${userId}`);
}

export function createUser(body) {
  return api.post('/users', body);
}

export function updateUser(userId, body) {
  return api.put(`/users/${userId}`, body);
}

export function assignUserRoles(userId, roleIds) {
  return api.post(`/users/${userId}/roles`, { role_ids: roleIds });
}

export function suspendUser(userId) {
  return api.post(`/users/${userId}/suspend`);
}

export function activateUser(userId) {
  return api.post(`/users/${userId}/activate`);
}

export function deactivateUser(userId) {
  return api.post(`/users/${userId}/deactivate`);
}
