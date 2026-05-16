export function EmptyState({ title = 'No records found', description = 'There is nothing to show yet.' }) {
  return (
    <div className="rounded-md border border-dashed border-line bg-white p-8 text-center">
      <p className="text-sm font-semibold text-ink">{title}</p>
      <p className="mt-1 text-sm text-muted">{description}</p>
    </div>
  );
}
