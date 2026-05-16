import { EmptyState } from '../feedback/EmptyState';

export function DataTable({ columns, rows, emptyTitle }) {
  if (!rows?.length) {
    return <EmptyState title={emptyTitle} />;
  }

  return (
    <div className="overflow-x-auto rounded-md border border-line bg-white">
      <table className="min-w-full divide-y divide-line text-sm">
        <thead className="bg-panel">
          <tr>
            {columns.map((column) => (
              <th className="px-4 py-3 text-left font-semibold text-muted" key={column.key}>
                {column.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody className="divide-y divide-line">
          {rows.map((row, index) => (
            <tr key={row.id || index}>
              {columns.map((column) => (
                <td className="px-4 py-3 text-ink" key={column.key}>
                  {column.render ? column.render(row) : row[column.key] ?? 'None'}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
