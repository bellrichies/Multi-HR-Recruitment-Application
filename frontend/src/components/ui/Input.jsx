export function Input({ label, error, className = '', ...props }) {
  return (
    <label className="block">
      <span className="mb-1 block text-sm font-medium text-ink">{label}</span>
      <input
        className={`focus-ring h-10 w-full rounded-md border border-line bg-white px-3 text-sm text-ink ${className}`}
        {...props}
      />
      {error ? <span className="mt-1 block text-xs text-danger">{error}</span> : null}
    </label>
  );
}
