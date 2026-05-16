export function formatDate(value) {
  if (!value) {
    return 'Not set';
  }

  return new Intl.DateTimeFormat('en-NG', {
    dateStyle: 'medium',
    timeStyle: String(value).includes(':') ? 'short' : undefined,
  }).format(new Date(value));
}
