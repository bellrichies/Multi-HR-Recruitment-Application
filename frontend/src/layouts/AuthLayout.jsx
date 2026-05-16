import { Outlet, Link } from 'react-router-dom';
import { BriefcaseBusiness } from 'lucide-react';

export function AuthLayout() {
  return (
    <div className="grid min-h-screen bg-panel lg:grid-cols-[1fr_420px]">
      <section className="hidden border-r border-line bg-white p-10 lg:flex lg:flex-col lg:justify-between">
        <Link className="flex items-center gap-2 text-sm font-semibold text-ink" to="/">
          <BriefcaseBusiness className="text-brand" size={22} />
          Multi HR Platform
        </Link>
        <div>
          <h1 className="max-w-xl text-4xl font-semibold leading-tight text-ink">Recruitment operations, payments, and candidate pipelines in one workspace</h1>
          <p className="mt-4 max-w-xl text-base leading-7 text-muted">
            Secure role dashboards for Super Admins, HR teams, recruiters, and job seekers.
          </p>
        </div>
      </section>
      <main className="flex items-center justify-center px-4 py-10">
        <Outlet />
      </main>
    </div>
  );
}
