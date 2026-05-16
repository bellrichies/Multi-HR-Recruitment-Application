import { usePermissions } from '../../hooks/usePermissions';

export function PermissionGate({ permission, children, fallback = null }) {
  const { hasPermission } = usePermissions();

  return hasPermission(permission) ? children : fallback;
}
