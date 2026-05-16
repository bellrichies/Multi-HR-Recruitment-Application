import { Menu, Bell, LogOut, WalletCards, Users, ShieldCheck, BriefcaseBusiness, MessageSquare, ClipboardList, BarChart3, Settings, X } from 'lucide-react';
import { useState } from 'react';
import { NavLink, Outlet, useNavigate } from 'react-router-dom';
import { usePermissions } from '../hooks/usePermissions';
import { useAuth } from '../store/auth.store';
import { dashboardForUser } from '../utils/roles';
import { PermissionGate } from '../components/navigation/PermissionGate';
import { NotificationBell } from '../components/navigation/NotificationBell';

const navItems = [
  { label: 'Dashboard', to: '/dashboard', icon: BarChart3 },
  { label: 'Jobs', to: '/jobs', icon: BriefcaseBusiness, permission: 'jobs.view' },
  { label: 'Candidates', to: '/candidates', icon: Users, permission: 'candidates.discover' },
  { label: 'Applications', to: '/applications', icon: ClipboardList, permission: 'applications.view' },
  { label: 'Wallet', to: '/wallet', icon: WalletCards, permission: 'wallet.view' },
  { label: 'Reports', to: '/reports', icon: BarChart3, permission: 'reports.view' },
  { label: 'Messages', to: '/messages', icon: MessageSquare, permission: 'messages.view' },
  { label: 'Notifications', to: '/notifications', icon: Bell, permission: 'notifications.view' },
  { label: 'Roles', to: '/roles-permissions', icon: ShieldCheck, permission: 'roles.view' },
  { label: 'Users', to: '/users', icon: Settings, permission: 'users.view' },
];

export function DashboardLayout() {
  const [open, setOpen] = useState(false);
  const auth = useAuth();
  const { hasPermission } = usePermissions();
  const navigate = useNavigate();
  const userName = `${auth.user?.first_name || ''} ${auth.user?.last_name || ''}`.trim() || auth.user?.email || 'User';

  async function handleLogout() {
    await auth.logout();
    navigate('/login');
  }

  const sidebar = (
    <aside className="flex h-full w-72 flex-col border-r border-line bg-white">
      <div className="flex h-16 items-center justify-between border-b border-line px-4">
        <NavLink className="text-sm font-semibold text-ink" to={dashboardForUser(auth.user)}>
          Multi HR Platform
        </NavLink>
        <button className="focus-ring rounded-md p-2 lg:hidden" type="button" onClick={() => setOpen(false)}>
          <X size={18} />
        </button>
      </div>
      <nav className="flex-1 space-y-1 overflow-y-auto p-3">
        {navItems
          .filter((item) => !item.permission || hasPermission(item.permission))
          .map((item) => {
            const Icon = item.icon;

            return (
              <NavLink
                className={({ isActive }) =>
                  `flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium ${
                    isActive ? 'bg-teal-50 text-brand' : 'text-muted hover:bg-panel hover:text-ink'
                  }`
                }
                key={item.to}
                to={item.to}
                onClick={() => setOpen(false)}
              >
                <Icon size={18} />
                {item.label}
              </NavLink>
            );
          })}
      </nav>
      <div className="border-t border-line p-3">
        <button className="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted hover:bg-panel hover:text-ink" type="button" onClick={handleLogout}>
          <LogOut size={18} />
          Logout
        </button>
      </div>
    </aside>
  );

  return (
    <div className="min-h-screen bg-panel lg:grid lg:grid-cols-[288px_1fr]">
      <div className="hidden lg:block">{sidebar}</div>
      {open ? <div className="fixed inset-0 z-40 bg-black/30 lg:hidden" onClick={() => setOpen(false)}>{sidebar}</div> : null}
      <section className="min-w-0">
        <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-line bg-white px-4">
          <button className="focus-ring rounded-md p-2 lg:hidden" type="button" onClick={() => setOpen(true)}>
            <Menu size={20} />
          </button>
          <div className="min-w-0">
            <p className="truncate text-sm font-semibold text-ink">{userName}</p>
            <p className="truncate text-xs text-muted">{auth.user?.email}</p>
          </div>
          <PermissionGate permission="notifications.view">
            <NotificationBell />
          </PermissionGate>
        </header>
        <main className="px-4 py-6 sm:px-6 lg:px-8">
          <Outlet />
        </main>
      </section>
    </div>
  );
}
