import { Link } from 'react-router-dom';
import { Button } from '../../components/ui/Button';

export function ForbiddenPage() {
  return (
    <div className="rounded-md border border-line bg-white p-8">
      <h1 className="text-2xl font-semibold text-ink">Access denied</h1>
      <p className="mt-2 text-sm text-muted">Your account does not have permission to open this page.</p>
      <Button as={Link} className="mt-6" to="/dashboard">Return to dashboard</Button>
    </div>
  );
}
