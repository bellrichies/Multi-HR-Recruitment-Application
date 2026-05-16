import { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { LogIn } from 'lucide-react';
import { login } from '../../api/auth.api';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Toast } from '../../components/feedback/Toast';
import { useAuth } from '../../store/auth.store';
import { dashboardForUser } from '../../utils/roles';

export function LoginPage() {
  const [form, setForm] = useState({ email: '', password: '' });
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const auth = useAuth();
  const navigate = useNavigate();

  async function submit(event) {
    event.preventDefault();
    setSubmitting(true);
    setError('');

    try {
      const data = await login(form);
      const user = data.user || data;
      auth.setSession(data.token || data.access_token, user);
      navigate(dashboardForUser(user), { replace: true });
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form className="w-full max-w-sm rounded-md border border-line bg-white p-6 shadow-soft" onSubmit={submit}>
      <Toast message={error} type="error" onClose={() => setError('')} />
      <h1 className="text-2xl font-semibold text-ink">Login</h1>
      <p className="mt-1 text-sm text-muted">Access your role workspace.</p>
      <div className="mt-6 space-y-4">
        <Input label="Email address" type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} required />
        <Input label="Password" type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} required />
      </div>
      <Button className="mt-6 w-full" disabled={submitting} type="submit">
        <LogIn size={18} />
        {submitting ? 'Signing in...' : 'Sign in'}
      </Button>
      <p className="mt-4 text-center text-sm text-muted">
        New here? <Link className="font-semibold text-brand" to="/register/job-seeker">Create a job seeker account</Link>
      </p>
    </form>
  );
}
