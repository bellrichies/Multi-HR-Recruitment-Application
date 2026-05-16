import { api } from './client';

export function wallet() {
  return api.get('/wallet');
}

export function walletTransactions(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/wallet/transactions${search ? `?${search}` : ''}`);
}

export function fundWallet(payload) {
  return api.post('/wallet/fund', payload);
}
