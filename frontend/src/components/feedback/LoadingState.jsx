export function LoadingState({ label = 'Loading records...' }) {
  return (
    <div className="flex min-h-40 items-center justify-center rounded-md border border-line bg-white text-sm text-muted">
      {label}
    </div>
  );
}
