import { ChevronRight } from 'lucide-react';

export function Breadcrumbs({ items = [] }) {
  return (
    <nav className="flex flex-wrap items-center gap-1 text-sm text-muted">
      {items.map((item, index) => (
        <span className="flex items-center gap-1" key={item}>
          {index > 0 ? <ChevronRight size={14} /> : null}
          {item}
        </span>
      ))}
    </nav>
  );
}
