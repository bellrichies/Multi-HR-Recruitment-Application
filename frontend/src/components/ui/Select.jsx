export function Select({ label, children, className = '', ...props }) {
  return (
    <label className="block">
      <span className="mb-1 block text-sm font-medium text-ink">{label}</span>
      <select className={`focus-ring h-10 w-full rounded-md border border-line bg-white px-3 text-sm text-ink ${className}`} {...props}>
        {children}
      </select>
    </label>
  );
}
