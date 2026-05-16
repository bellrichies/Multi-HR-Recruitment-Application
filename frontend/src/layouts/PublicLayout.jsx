import { Outlet, Link } from 'react-router-dom';
import { BriefcaseBusiness } from 'lucide-react';

export function PublicLayout() {
  return (
    <div className="min-h-screen bg-panel">
      <header className="border-b border-line bg-white">
        <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
          <Link className="flex items-center gap-2 text-sm font-semibold text-ink" to="/">
            <BriefcaseBusiness className="text-brand" size={22} />
            Multi HR Platform
          </Link>
          <Link className="text-sm font-semibold text-brand" to="/login">
            Login
          </Link>
        </div>
      </header>
      <main className="mx-auto max-w-6xl px-4 py-8">
        <Outlet />
      </main>
    </div>
  );
}
