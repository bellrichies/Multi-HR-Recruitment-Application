import { api } from './client';

export function conversations(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/conversations${search ? `?${search}` : ''}`);
}

export function conversationMessages(conversationId, params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/conversations/${conversationId}/messages${search ? `?${search}` : ''}`);
}

export function conversationParticipants(params = {}) {
  const search = new URLSearchParams(params).toString();

  return api.get(`/conversations/participants${search ? `?${search}` : ''}`);
}

export function createConversation(body) {
  return api.post('/conversations', body);
}

export function sendConversationMessage(conversationId, body) {
  return api.post(`/conversations/${conversationId}/messages`, body);
}

export function favoriteConversation(conversationId) {
  return api.post(`/conversations/${conversationId}/favorite`);
}

export function unfavoriteConversation(conversationId) {
  return api.delete(`/conversations/${conversationId}/favorite`);
}

export function unreadMessages() {
  return api.get('/conversations/unread-count');
}
