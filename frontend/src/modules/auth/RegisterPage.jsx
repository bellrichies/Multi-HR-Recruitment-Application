import { useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { registerJobSeeker, registerRecruiter } from '../../api/auth.api';
import { Toast } from '../../components/feedback/Toast';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';

export function RegisterPage() {
  const { type = 'job-seeker' } = useParams();
  const isRecruiter = type === 'recruiter';
  const [message, setMessage] = useState('');
  const [error, setError] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [form, setForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    referral_code: '',
    company_name: '',
  });

  async function submit(event) {
    event.preventDefault();
    setSubmitting(true);
    setError('');
    setMessage('');

    try {
      const action = isRecruiter ? registerRecruiter : registerJobSeeker;
      await action(form);
      setMessage('Account created. You can now log in.');
    } catch (err) {
      setError(err.message);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <form className="w-full max-w-sm rounded-md border border-line bg-white p-6 shadow-soft" onSubmit={submit}>
      <Toast message={message} onClose={() => setMessage('')} />
      <Toast message={error} type="error" onClose={() => setError('')} />
      <h1 className="text-2xl font-semibold text-ink">{isRecruiter ? 'Recruiter registration' : 'Job seeker registration'}</h1>
      <div className="mt-6 space-y-4">
        <div className="grid gap-4 sm:grid-cols-2">
          <Input label="First name" value={form.first_name} onChange={(event) => setForm({ ...form, first_name: event.target.value })} required />
          <Input label="Last name" value={form.last_name} onChange={(event) => setForm({ ...form, last_name: event.target.value })} required />
        </div>
        <Input label="Email address" type="email" value={form.email} onChange={(event) => setForm({ ...form, email: event.target.value })} required />
        <Input label="Phone" value={form.phone} onChange={(event) => setForm({ ...form, phone: event.target.value })} />
        {isRecruiter ? (
          <Input label="Company name" value={form.company_name} onChange={(event) => setForm({ ...form, company_name: event.target.value })} />
        ) : (
          <Input label="HR referral code" value={form.referral_code} onChange={(event) => setForm({ ...form, referral_code: event.target.value })} />
        )}
        <Input label="Password" type="password" value={form.password} onChange={(event) => setForm({ ...form, password: event.target.value })} required />
      </div>
      <Button className="mt-6 w-full" disabled={submitting} type="submit">{submitting ? 'Creating account...' : 'Create account'}</Button>
      <p className="mt-4 text-center text-sm text-muted">
        {isRecruiter ? <Link className="font-semibold text-brand" to="/register/job-seeker">Register as job seeker</Link> : <Link className="font-semibold text-brand" to="/register/recruiter">Register as recruiter</Link>}
      </p>
    </form>
  );
}
