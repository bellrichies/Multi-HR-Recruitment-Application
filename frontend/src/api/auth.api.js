import { api, clearSession, persistSession } from './client';

export async function login(credentials) {
  const response = await api.post('/auth/login', credentials);
  const token = response.data?.token || response.data?.access_token;
  const user = response.data?.user || response.data;

  persistSession(token, user);

  return response.data;
}

export async function registerJobSeeker(payload) {
  return api.post('/auth/register/job-seeker', payload);
}

export async function registerRecruiter(payload) {
  return api.post('/auth/register/recruiter', payload);
}

export async function me() {
  return api.get('/auth/me');
}

export async function logout() {
  try {
    await api.post('/auth/logout');
  } finally {
    clearSession();
  }
}
