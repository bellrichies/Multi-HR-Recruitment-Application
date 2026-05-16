export function Toast({ message, type = 'success', onClose }) {
  if (!message) {
    return null;
  }

  const styles = type === 'error' ? 'border-red-200 bg-red-50 text-red-900' : 'border-teal-200 bg-teal-50 text-teal-900';

  return (
    <div className={`fixed right-4 top-4 z-50 max-w-sm rounded-md border p-4 text-sm shadow-soft ${styles}`} role="status">
      <div className="flex items-start justify-between gap-4">
        <span>{message}</span>
        <button className="font-semibold" type="button" onClick={onClose}>
          Close
        </button>
      </div>
    </div>
  );
}
