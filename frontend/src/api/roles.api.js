import { api } from './client';

export function roles() {
  return api.get('/roles');
}

export function permissions() {
  return api.get('/permissions');
}
