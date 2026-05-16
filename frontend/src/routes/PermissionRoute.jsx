import { Navigate, Outlet } from 'react-router-dom';
import { usePermissions } from '../hooks/usePermissions';

export function PermissionRoute({ permission }) {
  const { hasPermission } = usePermissions();

  return hasPermission(permission) ? <Outlet /> : <Navigate replace to="/forbidden" />;
}
