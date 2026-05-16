import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { LoadingState } from '../components/feedback/LoadingState';
import { useAuth } from '../store/auth.store';

export function ProtectedRoute() {
  const auth = useAuth();
  const location = useLocation();

  if (auth.booting) {
    return <LoadingState label="Checking your session..." />;
  }

  if (!auth.isAuthenticated) {
    return <Navigate replace state={{ from: location }} to="/login" />;
  }

  return <Outlet />;
}
