import { api } from './client';

export function assignUserRoles(userId, roleIds) {
  return api.post(`/users/${userId}/roles`, { role_ids: roleIds });
}
