import { AlertTriangle } from 'lucide-react';

export function ErrorState({ error, onRetry }) {
  return (
    <div className="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900">
      <div className="flex items-center gap-2 font-semibold">
        <AlertTriangle size={18} />
        Request failed
      </div>
      <p className="mt-1">{error?.message || 'Unable to load this view.'}</p>
      {onRetry ? (
        <button className="mt-3 text-sm font-semibold underline" type="button" onClick={onRetry}>
          Try again
        </button>
      ) : null}
    </div>
  );
}
