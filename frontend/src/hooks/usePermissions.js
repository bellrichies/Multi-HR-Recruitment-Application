import { useAuth } from '../store/auth.store';

export function usePermissions() {
  const { permissions, roles } = useAuth();
  const roleSlugs = roles.map((role) => role.slug || role);

  return {
    hasPermission(permission) {
      return roleSlugs.includes('super_admin') || permissions.includes(permission);
    },
    hasRole(role) {
      return roleSlugs.includes(role);
    },
    primaryRole() {
      return roleSlugs[0] || 'job_seeker';
    },
  };
}
