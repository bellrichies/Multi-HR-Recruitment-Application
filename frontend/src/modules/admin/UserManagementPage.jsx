import {
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Edit3,
  KeyRound,
  PauseCircle,
  Plus,
  RefreshCw,
  Search,
  ShieldCheck,
  UserRound,
  XCircle,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import {
  activateUser,
  assignUserRoles,
  createUser,
  deactivateUser,
  suspendUser,
  updateUser,
  userDetail,
  users,
} from '../../api/users.api';
import { roles } from '../../api/roles.api';
import { EmptyState } from '../../components/feedback/EmptyState';
import { ErrorState } from '../../components/feedback/ErrorState';
import { LoadingState } from '../../components/feedback/LoadingState';
import { Toast } from '../../components/feedback/Toast';
import { Breadcrumbs } from '../../components/navigation/Breadcrumbs';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Select } from '../../components/ui/Select';
import { useApi } from '../../hooks/useApi';
import { usePermissions } from '../../hooks/usePermissions';
import { useAuth } from '../../store/auth.store';
import { formatDate } from '../../utils/formatDate';

const statusOptions = [
  { value: '', label: 'All statuses' },
  { value: 'active', label: 'Active' },
  { value: 'pending', label: 'Pending' },
  { value: 'suspended', label: 'Suspended' },
  { value: 'deactivated', label: 'Deactivated' },
  { value: 'rejected', label: 'Rejected' },
];

const sortOptions = [
  { value: 'created_at', label: 'Created' },
  { value: 'name', label: 'Name' },
  { value: 'email', label: 'Email' },
  { value: 'status', label: 'Status' },
  { value: 'last_login_at', label: 'Last login' },
];

const pageSizeOptions = [10, 20, 50];

function emptyForm() {
  return {
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    status: 'active',
    role_ids: [],
  };
}

export function UserManagementPage() {
  const { user: currentUser } = useAuth();
  const { hasPermission } = usePermissions();
  const canCreate = hasPermission('users.create');
  const canUpdate = hasPermission('users.update');
  const canSuspend = hasPermission('users.suspend');
  const canDeactivate = hasPermission('users.deactivate');
  const canAssignRoles = hasPermission('roles.assign');
  const canViewRoles = hasPermission('roles.view');
  const [search, setSearch] = useState('');
  const [debouncedSearch, setDebouncedSearch] = useState('');
  const [status, setStatus] = useState('');
  const [role, setRole] = useState('');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(20);
  const [sort, setSort] = useState('created_at');
  const [direction, setDirection] = useState('desc');
  const [panel, setPanel] = useState({ mode: null, user: null });
  const [detailLoading, setDetailLoading] = useState(false);
  const [detailError, setDetailError] = useState(null);
  const [form, setForm] = useState(() => emptyForm());
  const [formErrors, setFormErrors] = useState({});
  const [saving, setSaving] = useState(false);
  const [confirmAction, setConfirmAction] = useState(null);
  const [actionLoading, setActionLoading] = useState(false);
  const [toast, setToast] = useState(null);

  const userQuery = useApi(
    () =>
      users(
        compactParams({
          page,
          per_page: perPage,
          search: debouncedSearch,
          status,
          role,
          sort,
          direction,
        }),
      ),
    [page, perPage, debouncedSearch, status, role, sort, direction],
  );
  const roleQuery = useApi(() => (canViewRoles ? roles() : Promise.resolve({ data: [] })), [canViewRoles]);
  const rows = userQuery.data || [];
  const roleRows = roleQuery.data || [];
  const meta = userQuery.meta || { current_page: page, last_page: 1, total: rows.length, per_page: perPage };
  const activePanelUser = panel.user;
  const panelIsSelf = Number(activePanelUser?.id) === Number(currentUser?.id);
  const canEditPanel = panel.mode === 'create' ? canCreate : canUpdate || (canAssignRoles && !panelIsSelf);

  useEffect(() => {
    const timeoutId = window.setTimeout(() => {
      setDebouncedSearch(search.trim());
      setPage(1);
    }, 250);

    return () => window.clearTimeout(timeoutId);
  }, [search]);

  useEffect(() => {
    setPage(1);
  }, [status, role, sort, direction, perPage]);

  function updateForm(field, value) {
    setForm((current) => ({ ...current, [field]: value }));
    setFormErrors((current) => ({ ...current, [field]: undefined }));
  }

  function openCreatePanel() {
    setForm(emptyForm());
    setFormErrors({});
    setDetailError(null);
    setPanel({ mode: 'create', user: null });
  }

  async function openUserPanel(user, mode = 'view') {
    setPanel({ mode, user });
    setForm(formFromUser(user));
    setFormErrors({});
    setDetailError(null);
    setDetailLoading(true);

    try {
      const response = await userDetail(user.id);
      const nextUser = response.data;
      setPanel((current) => (Number(current.user?.id) === Number(user.id) ? { ...current, user: nextUser } : current));
      setForm(formFromUser(nextUser));
    } catch (error) {
      setDetailError(error);
    } finally {
      setDetailLoading(false);
    }
  }

  function closePanel() {
    setPanel({ mode: null, user: null });
    setDetailError(null);
    setFormErrors({});
  }

  function toggleRole(roleId) {
    setForm((current) => {
      const id = Number(roleId);
      const roleIds = current.role_ids.includes(id)
        ? current.role_ids.filter((item) => item !== id)
        : [...current.role_ids, id];

      return { ...current, role_ids: roleIds };
    });
    setFormErrors((current) => ({ ...current, role_ids: undefined }));
  }

  function changeSort(nextSort) {
    if (sort === nextSort) {
      setDirection((current) => (current === 'asc' ? 'desc' : 'asc'));
      return;
    }

    setSort(nextSort);
    setDirection(nextSort === 'name' ? 'asc' : 'desc');
  }

  async function handleSubmit(event) {
    event.preventDefault();

    const validationErrors = validateForm(form, panel.mode, canAssignRoles && roleRows.length > 0);

    if (Object.keys(validationErrors).length > 0) {
      setFormErrors(validationErrors);
      return;
    }

    setSaving(true);
    setFormErrors({});

    try {
      if (panel.mode === 'create') {
        const payload = {
          first_name: form.first_name.trim(),
          last_name: form.last_name.trim(),
          email: form.email.trim(),
          phone: form.phone.trim() || undefined,
          password: form.password,
          status: form.status,
        };

        if (canAssignRoles) {
          payload.role_ids = form.role_ids;
        }

        await createUser(payload);
        setToast({ type: 'success', message: 'User created successfully.' });
      } else if (activePanelUser) {
        if (canUpdate) {
          const payload = {
            first_name: form.first_name.trim(),
            last_name: form.last_name.trim(),
            email: form.email.trim(),
            phone: form.phone.trim() || undefined,
          };

          if (form.password) {
            payload.password = form.password;
          }

          await updateUser(activePanelUser.id, payload);
        }

        if (canAssignRoles && !panelIsSelf && rolesChanged(activePanelUser, form.role_ids)) {
          await assignUserRoles(activePanelUser.id, form.role_ids);
        }

        setToast({ type: 'success', message: 'User updated successfully.' });
      }

      closePanel();
      await userQuery.refresh();
    } catch (error) {
      setFormErrors(error.errors || {});
      setToast({ type: 'error', message: error.message || 'Unable to save user.' });
    } finally {
      setSaving(false);
    }
  }

  async function handleConfirmedAction() {
    if (!confirmAction) {
      return;
    }

    setActionLoading(true);

    try {
      const actionMap = {
        activate: activateUser,
        suspend: suspendUser,
        deactivate: deactivateUser,
      };
      await actionMap[confirmAction.type](confirmAction.user.id);
      setToast({ type: 'success', message: confirmAction.successMessage });
      setConfirmAction(null);
      await userQuery.refresh();

      if (activePanelUser && Number(activePanelUser.id) === Number(confirmAction.user.id)) {
        const response = await userDetail(confirmAction.user.id);
        setPanel((current) => ({ ...current, user: response.data }));
        setForm(formFromUser(response.data));
      }
    } catch (error) {
      setToast({ type: 'error', message: error.message || 'Unable to update user status.' });
    } finally {
      setActionLoading(false);
    }
  }

  if (userQuery.loading && rows.length === 0) return <LoadingState label="Loading users..." />;
  if (userQuery.error) return <ErrorState error={userQuery.error} onRetry={userQuery.refresh} />;

  return (
    <section className="space-y-5">
      <Toast message={toast?.message} type={toast?.type} onClose={() => setToast(null)} />

      <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
          <Breadcrumbs items={['Admin', 'Users']} />
          <h1 className="mt-2 text-2xl font-semibold text-ink">User Management</h1>
          <p className="mt-1 text-sm text-muted">{meta.total || 0} users in the current view</p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button disabled={userQuery.loading} type="button" variant="secondary" onClick={userQuery.refresh}>
            <RefreshCw size={16} />
            Refresh
          </Button>
          {canCreate ? (
            <Button type="button" onClick={openCreatePanel}>
              <Plus size={16} />
              New User
            </Button>
          ) : null}
        </div>
      </div>

      <div className="rounded-md border border-line bg-white p-4">
        <div className="grid gap-3 lg:grid-cols-[1fr_180px_180px_180px_140px]">
          <label className="block">
            <span className="mb-1 block text-sm font-medium text-ink">Search</span>
            <span className="flex items-center gap-2 rounded-md border border-line bg-white px-3">
              <Search className="text-muted" size={16} />
              <input
                className="focus-ring h-10 min-w-0 flex-1 border-0 bg-transparent text-sm text-ink focus:ring-0 focus:ring-offset-0"
                placeholder="Name, email, or phone"
                type="search"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
              />
            </span>
          </label>
          <Select label="Status" value={status} onChange={(event) => setStatus(event.target.value)}>
            {statusOptions.map((option) => (
              <option key={option.value || 'all'} value={option.value}>
                {option.label}
              </option>
            ))}
          </Select>
          <Select
            disabled={!canViewRoles || roleQuery.loading}
            label="Role"
            value={role}
            onChange={(event) => setRole(event.target.value)}
          >
            <option value="">{roleQuery.loading ? 'Loading roles...' : 'All roles'}</option>
            {roleRows.map((item) => (
              <option key={item.id} value={item.slug}>
                {item.name}
              </option>
            ))}
          </Select>
          <Select label="Sort" value={sort} onChange={(event) => changeSort(event.target.value)}>
            {sortOptions.map((option) => (
              <option key={option.value} value={option.value}>
                {option.label}
              </option>
            ))}
          </Select>
          <Select label="Per page" value={perPage} onChange={(event) => setPerPage(Number(event.target.value))}>
            {pageSizeOptions.map((option) => (
              <option key={option} value={option}>
                {option}
              </option>
            ))}
          </Select>
        </div>
        {roleQuery.error && canViewRoles ? (
          <p className="mt-3 text-sm font-medium text-danger">Roles could not be loaded. Role filters and assignment are unavailable.</p>
        ) : null}
      </div>

      <UserResults
        canAssignRoles={canAssignRoles}
        canDeactivate={canDeactivate}
        canSuspend={canSuspend}
        canUpdate={canUpdate}
        currentUserId={currentUser?.id}
        direction={direction}
        rows={rows}
        sort={sort}
        onAction={setConfirmAction}
        onEdit={(user) => openUserPanel(user, 'edit')}
        onSort={changeSort}
        onView={(user) => openUserPanel(user, 'view')}
      />

      <Pagination meta={meta} loading={userQuery.loading} onPageChange={setPage} />

      {panel.mode ? (
        <UserPanel
          canAssignRoles={canAssignRoles}
          canDeactivate={canDeactivate}
          canEdit={canEditPanel}
          canSuspend={canSuspend}
          canUpdate={canUpdate}
          detailError={detailError}
          detailLoading={detailLoading}
          errors={formErrors}
          form={form}
          isSelf={panelIsSelf}
          mode={panel.mode}
          roleRows={roleRows}
          saving={saving}
          user={activePanelUser}
          onAction={setConfirmAction}
          onChange={updateForm}
          onClose={closePanel}
          onEdit={() => setPanel((current) => ({ ...current, mode: 'edit' }))}
          onSubmit={handleSubmit}
          onToggleRole={toggleRole}
        />
      ) : null}

      {confirmAction ? (
        <ConfirmDialog
          action={confirmAction}
          loading={actionLoading}
          onCancel={() => setConfirmAction(null)}
          onConfirm={handleConfirmedAction}
        />
      ) : null}
    </section>
  );
}

function UserResults({
  canAssignRoles,
  canDeactivate,
  canSuspend,
  canUpdate,
  currentUserId,
  direction,
  rows,
  sort,
  onAction,
  onEdit,
  onSort,
  onView,
}) {
  if (!rows.length) {
    return (
      <EmptyState
        title="No users found"
        description="Adjust the search or filters to find matching platform users."
      />
    );
  }

  return (
    <>
      <div className="hidden overflow-x-auto rounded-md border border-line bg-white lg:block">
        <table className="min-w-full divide-y divide-line text-sm">
          <thead className="bg-panel">
            <tr>
              {[
                ['name', 'User'],
                ['status', 'Status'],
                ['created_at', 'Created'],
                ['last_login_at', 'Last login'],
              ].map(([key, label]) => (
                <th className="px-4 py-3 text-left font-semibold text-muted" key={key}>
                  <button className="focus-ring inline-flex items-center gap-1 rounded px-1" type="button" onClick={() => onSort(key)}>
                    {label}
                    {sort === key ? <span className="text-[11px] uppercase">{direction}</span> : null}
                  </button>
                </th>
              ))}
              <th className="px-4 py-3 text-left font-semibold text-muted">Roles</th>
              <th className="px-4 py-3 text-right font-semibold text-muted">Actions</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-line">
            {rows.map((user) => (
              <tr className="align-top" key={user.id}>
                <td className="px-4 py-4">
                  <button className="focus-ring flex min-w-64 items-center gap-3 rounded text-left" type="button" onClick={() => onView(user)}>
                    <Avatar name={fullName(user)} />
                    <span className="min-w-0">
                      <span className="block truncate font-semibold text-ink">{fullName(user)}</span>
                      <span className="block truncate text-xs text-muted">{user.email}</span>
                      {user.phone ? <span className="block truncate text-xs text-muted">{user.phone}</span> : null}
                    </span>
                  </button>
                </td>
                <td className="px-4 py-4">
                  <StatusBadge status={user.status} />
                </td>
                <td className="px-4 py-4 text-ink">{formatDate(user.created_at)}</td>
                <td className="px-4 py-4 text-ink">{formatDate(user.last_login_at)}</td>
                <td className="px-4 py-4">
                  <RoleChips roles={user.roles} />
                </td>
                <td className="px-4 py-4">
                  <RowActions
                    canAssignRoles={canAssignRoles}
                    canDeactivate={canDeactivate}
                    canSuspend={canSuspend}
                    canUpdate={canUpdate}
                    isSelf={Number(user.id) === Number(currentUserId)}
                    user={user}
                    onAction={onAction}
                    onEdit={onEdit}
                    onView={onView}
                  />
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>

      <div className="space-y-3 lg:hidden">
        {rows.map((user) => (
          <article className="rounded-md border border-line bg-white p-4" key={user.id}>
            <div className="flex items-start justify-between gap-3">
              <button className="focus-ring flex min-w-0 items-center gap-3 rounded text-left" type="button" onClick={() => onView(user)}>
                <Avatar name={fullName(user)} />
                <span className="min-w-0">
                  <span className="block truncate font-semibold text-ink">{fullName(user)}</span>
                  <span className="block truncate text-xs text-muted">{user.email}</span>
                </span>
              </button>
              <StatusBadge status={user.status} />
            </div>
            <div className="mt-3">
              <RoleChips roles={user.roles} />
            </div>
            <dl className="mt-3 grid grid-cols-2 gap-3 text-xs">
              <div>
                <dt className="text-muted">Created</dt>
                <dd className="mt-1 font-medium text-ink">{formatDate(user.created_at)}</dd>
              </div>
              <div>
                <dt className="text-muted">Last login</dt>
                <dd className="mt-1 font-medium text-ink">{formatDate(user.last_login_at)}</dd>
              </div>
            </dl>
            <div className="mt-4 border-t border-line pt-3">
              <RowActions
                canAssignRoles={canAssignRoles}
                canDeactivate={canDeactivate}
                canSuspend={canSuspend}
                canUpdate={canUpdate}
                isSelf={Number(user.id) === Number(currentUserId)}
                user={user}
                onAction={onAction}
                onEdit={onEdit}
                onView={onView}
              />
            </div>
          </article>
        ))}
      </div>
    </>
  );
}

function RowActions({ canAssignRoles, canDeactivate, canSuspend, canUpdate, isSelf, user, onAction, onEdit, onView }) {
  const canEdit = canUpdate || (canAssignRoles && !isSelf);
  const disabledReason = isSelf ? 'Self status changes are disabled' : undefined;

  return (
    <div className="flex flex-wrap justify-end gap-2">
      <Button className="h-9 px-3" type="button" variant="secondary" onClick={() => onView(user)}>
        <UserRound size={15} />
        View
      </Button>
      {canEdit ? (
        <Button className="h-9 px-3" type="button" variant="secondary" onClick={() => onEdit(user)}>
          <Edit3 size={15} />
          Edit
        </Button>
      ) : null}
      {canUpdate && user.status !== 'active' && !isSelf ? (
        <Button
          className="h-9 px-3"
          type="button"
          variant="secondary"
          onClick={() =>
            onAction({
              type: 'activate',
              user,
              title: 'Activate user',
              message: `${fullName(user)} will regain access after activation.`,
              confirmLabel: 'Activate',
              successMessage: 'User activated successfully.',
            })
          }
        >
          <CheckCircle2 size={15} />
          Activate
        </Button>
      ) : null}
      {canSuspend && user.status === 'active' ? (
        <Button
          className="h-9 px-3"
          disabled={isSelf}
          title={disabledReason}
          type="button"
          variant="secondary"
          onClick={() =>
            onAction({
              type: 'suspend',
              user,
              title: 'Suspend user',
              message: `${fullName(user)} will be blocked from signing in until reactivated.`,
              confirmLabel: 'Suspend',
              successMessage: 'User suspended successfully.',
            })
          }
        >
          <PauseCircle size={15} />
          Suspend
        </Button>
      ) : null}
      {canDeactivate && user.status === 'active' ? (
        <Button
          className="h-9 px-3"
          disabled={isSelf}
          title={disabledReason}
          type="button"
          variant="danger"
          onClick={() =>
            onAction({
              type: 'deactivate',
              user,
              title: 'Deactivate user',
              message: `${fullName(user)} will lose platform access until reactivated.`,
              confirmLabel: 'Deactivate',
              successMessage: 'User deactivated successfully.',
            })
          }
        >
          <XCircle size={15} />
          Deactivate
        </Button>
      ) : null}
    </div>
  );
}

function UserPanel({
  canAssignRoles,
  canDeactivate,
  canEdit,
  canSuspend,
  canUpdate,
  detailError,
  detailLoading,
  errors,
  form,
  isSelf,
  mode,
  roleRows,
  saving,
  user,
  onAction,
  onChange,
  onClose,
  onEdit,
  onSubmit,
  onToggleRole,
}) {
  const isCreate = mode === 'create';
  const isEdit = mode === 'edit' || isCreate;
  const title = isCreate ? 'New User' : fullName(user);
  const allowProfileEdit = isCreate ? true : canUpdate;
  const allowRoleEdit = canAssignRoles && !isSelf && isEdit;

  return (
    <div className="fixed inset-0 z-40 bg-ink/30">
      <div className="ml-auto flex h-full w-full max-w-3xl flex-col bg-white shadow-soft">
        <div className="flex items-start justify-between gap-3 border-b border-line px-4 py-4 md:px-6">
          <div className="min-w-0">
            <p className="text-xs font-semibold uppercase text-muted">{isCreate ? 'Create' : mode === 'edit' ? 'Edit' : 'User details'}</p>
            <h2 className="mt-1 truncate text-xl font-semibold text-ink">{title}</h2>
            {!isCreate && user ? (
              <div className="mt-2 flex flex-wrap items-center gap-2">
                <StatusBadge status={user.status} />
                {isSelf ? <span className="rounded-full bg-panel px-2 py-1 text-xs font-semibold text-muted">Current user</span> : null}
              </div>
            ) : null}
          </div>
          <button className="focus-ring rounded-md p-2 text-muted hover:bg-panel hover:text-ink" type="button" onClick={onClose}>
            <XCircle size={20} />
          </button>
        </div>

        <div className="min-h-0 flex-1 overflow-y-auto p-4 md:p-6">
          {detailLoading ? <p className="mb-4 text-sm text-muted">Loading user details...</p> : null}
          {detailError ? <ErrorState error={detailError} /> : null}

          {isEdit ? (
            <form className="space-y-6" onSubmit={onSubmit}>
              <div className="grid gap-4 md:grid-cols-2">
                <Input
                  disabled={!allowProfileEdit || saving}
                  error={firstError(errors.first_name)}
                  label="First name"
                  value={form.first_name}
                  onChange={(event) => onChange('first_name', event.target.value)}
                />
                <Input
                  disabled={!allowProfileEdit || saving}
                  error={firstError(errors.last_name)}
                  label="Last name"
                  value={form.last_name}
                  onChange={(event) => onChange('last_name', event.target.value)}
                />
                <Input
                  disabled={!allowProfileEdit || saving}
                  error={firstError(errors.email)}
                  label="Email"
                  type="email"
                  value={form.email}
                  onChange={(event) => onChange('email', event.target.value)}
                />
                <Input
                  disabled={!allowProfileEdit || saving}
                  error={firstError(errors.phone)}
                  label="Phone"
                  value={form.phone}
                  onChange={(event) => onChange('phone', event.target.value)}
                />
                {isCreate ? (
                  <Select disabled={saving} label="Initial status" value={form.status} onChange={(event) => onChange('status', event.target.value)}>
                    {statusOptions
                      .filter((option) => option.value)
                      .map((option) => (
                        <option key={option.value} value={option.value}>
                          {option.label}
                        </option>
                      ))}
                  </Select>
                ) : null}
                <Input
                  disabled={!allowProfileEdit || saving}
                  error={firstError(errors.password)}
                  label={isCreate ? 'Password' : 'New password'}
                  minLength={8}
                  placeholder={isCreate ? '' : 'Leave unchanged'}
                  type="password"
                  value={form.password}
                  onChange={(event) => onChange('password', event.target.value)}
                />
              </div>

              {canAssignRoles ? (
                <RoleChecklist
                  disabled={!allowRoleEdit || saving}
                  error={firstError(errors.role_ids)}
                  roleIds={form.role_ids}
                  roles={roleRows}
                  onToggleRole={onToggleRole}
                />
              ) : null}

              <div className="flex flex-col-reverse gap-2 border-t border-line pt-4 sm:flex-row sm:justify-end">
                <Button disabled={saving} type="button" variant="secondary" onClick={onClose}>
                  Cancel
                </Button>
                <Button disabled={!canEdit || saving} type="submit">
                  <ShieldCheck size={16} />
                  {saving ? 'Saving' : 'Save'}
                </Button>
              </div>
            </form>
          ) : (
            <UserDetails
              canDeactivate={canDeactivate}
              canSuspend={canSuspend}
              canUpdate={canUpdate}
              isSelf={isSelf}
              user={user}
              onAction={onAction}
              onEdit={onEdit}
            />
          )}
        </div>
      </div>
    </div>
  );
}

function UserDetails({ canDeactivate, canSuspend, canUpdate, isSelf, user, onAction, onEdit }) {
  if (!user) {
    return null;
  }

  return (
    <div className="space-y-6">
      <div className="grid gap-4 md:grid-cols-2">
        <DetailItem label="Email" value={user.email} />
        <DetailItem label="Phone" value={user.phone || 'Not set'} />
        <DetailItem label="Created" value={formatDate(user.created_at)} />
        <DetailItem label="Last login" value={formatDate(user.last_login_at)} />
        <DetailItem label="Email verified" value={formatDate(user.email_verified_at)} />
        <DetailItem label="Phone verified" value={formatDate(user.phone_verified_at)} />
      </div>

      <section>
        <h3 className="text-sm font-semibold text-ink">Roles</h3>
        <div className="mt-3">
          <RoleChips roles={user.roles} />
        </div>
      </section>

      <section>
        <h3 className="text-sm font-semibold text-ink">Effective permissions</h3>
        {user.permissions?.length ? (
          <div className="mt-3 flex flex-wrap gap-2">
            {user.permissions.map((permission) => (
              <span className="rounded-full bg-panel px-2 py-1 text-xs font-medium text-muted" key={permission}>
                {permission}
              </span>
            ))}
          </div>
        ) : (
          <p className="mt-2 text-sm text-muted">No effective permissions returned for this user.</p>
        )}
      </section>

      <div className="flex flex-wrap justify-end gap-2 border-t border-line pt-4">
        {canUpdate ? (
          <Button type="button" variant="secondary" onClick={onEdit}>
            <Edit3 size={16} />
            Edit
          </Button>
        ) : null}
        {canUpdate && user.status !== 'active' && !isSelf ? (
          <Button
            type="button"
            variant="secondary"
            onClick={() =>
              onAction({
                type: 'activate',
                user,
                title: 'Activate user',
                message: `${fullName(user)} will regain access after activation.`,
                confirmLabel: 'Activate',
                successMessage: 'User activated successfully.',
              })
            }
          >
            <CheckCircle2 size={16} />
            Activate
          </Button>
        ) : null}
        {canSuspend && user.status === 'active' ? (
          <Button
            disabled={isSelf}
            title={isSelf ? 'Self status changes are disabled' : undefined}
            type="button"
            variant="secondary"
            onClick={() =>
              onAction({
                type: 'suspend',
                user,
                title: 'Suspend user',
                message: `${fullName(user)} will be blocked from signing in until reactivated.`,
                confirmLabel: 'Suspend',
                successMessage: 'User suspended successfully.',
              })
            }
          >
            <PauseCircle size={16} />
            Suspend
          </Button>
        ) : null}
        {canDeactivate && user.status === 'active' ? (
          <Button
            disabled={isSelf}
            title={isSelf ? 'Self status changes are disabled' : undefined}
            type="button"
            variant="danger"
            onClick={() =>
              onAction({
                type: 'deactivate',
                user,
                title: 'Deactivate user',
                message: `${fullName(user)} will lose platform access until reactivated.`,
                confirmLabel: 'Deactivate',
                successMessage: 'User deactivated successfully.',
              })
            }
          >
            <XCircle size={16} />
            Deactivate
          </Button>
        ) : null}
      </div>
    </div>
  );
}

function RoleChecklist({ disabled, error, roleIds, roles, onToggleRole }) {
  if (!roles.length) {
    return (
      <section className="rounded-md border border-line bg-panel p-4">
        <h3 className="text-sm font-semibold text-ink">Roles</h3>
        <p className="mt-2 text-sm text-muted">No assignable roles were loaded.</p>
      </section>
    );
  }

  return (
    <section>
      <div className="flex items-center gap-2">
        <KeyRound className="text-muted" size={16} />
        <h3 className="text-sm font-semibold text-ink">Roles</h3>
      </div>
      <div className="mt-3 grid gap-2 md:grid-cols-2">
        {roles.map((role) => (
          <label
            className={`flex items-start gap-3 rounded-md border border-line p-3 text-sm ${
              disabled ? 'bg-panel text-muted' : 'bg-white text-ink hover:bg-panel'
            }`}
            key={role.id}
          >
            <input
              checked={roleIds.includes(Number(role.id))}
              className="mt-1 h-4 w-4 rounded border-line text-brand"
              disabled={disabled}
              type="checkbox"
              onChange={() => onToggleRole(role.id)}
            />
            <span>
              <span className="block font-semibold">{role.name}</span>
              <span className="block text-xs text-muted">{role.slug}</span>
            </span>
          </label>
        ))}
      </div>
      {error ? <p className="mt-2 text-xs font-medium text-danger">{error}</p> : null}
    </section>
  );
}

function Pagination({ loading, meta, onPageChange }) {
  const current = Number(meta.current_page || 1);
  const last = Number(meta.last_page || 1);

  return (
    <div className="flex flex-col gap-3 rounded-md border border-line bg-white px-4 py-3 text-sm text-muted sm:flex-row sm:items-center sm:justify-between">
      <span>
        Page {current} of {last} · {meta.total || 0} total
      </span>
      <div className="flex gap-2">
        <Button disabled={loading || current <= 1} type="button" variant="secondary" onClick={() => onPageChange(current - 1)}>
          <ChevronLeft size={16} />
          Previous
        </Button>
        <Button disabled={loading || current >= last} type="button" variant="secondary" onClick={() => onPageChange(current + 1)}>
          Next
          <ChevronRight size={16} />
        </Button>
      </div>
    </div>
  );
}

function ConfirmDialog({ action, loading, onCancel, onConfirm }) {
  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-ink/40 p-4">
      <div className="w-full max-w-md rounded-md bg-white p-5 shadow-soft">
        <div className="flex items-start gap-3">
          <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50 text-danger">
            <XCircle size={18} />
          </span>
          <div>
            <h2 className="text-lg font-semibold text-ink">{action.title}</h2>
            <p className="mt-1 text-sm text-muted">{action.message}</p>
          </div>
        </div>
        <div className="mt-5 flex justify-end gap-2">
          <Button disabled={loading} type="button" variant="secondary" onClick={onCancel}>
            Cancel
          </Button>
          <Button disabled={loading} type="button" variant={action.type === 'activate' ? 'primary' : 'danger'} onClick={onConfirm}>
            {loading ? 'Working' : action.confirmLabel}
          </Button>
        </div>
      </div>
    </div>
  );
}

function DetailItem({ label, value }) {
  return (
    <div className="rounded-md border border-line bg-panel p-3">
      <dt className="text-xs font-medium text-muted">{label}</dt>
      <dd className="mt-1 break-words text-sm font-semibold text-ink">{value}</dd>
    </div>
  );
}

function Avatar({ name }) {
  return (
    <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-50 text-sm font-semibold text-brand">
      {initials(name)}
    </span>
  );
}

function StatusBadge({ status }) {
  const styles = {
    active: 'bg-teal-50 text-brand',
    pending: 'bg-amber-50 text-accent',
    suspended: 'bg-red-50 text-danger',
    deactivated: 'bg-slate-100 text-slate-700',
    rejected: 'bg-red-50 text-danger',
  };

  return (
    <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${styles[status] || 'bg-panel text-muted'}`}>
      {formatLabel(status)}
    </span>
  );
}

function RoleChips({ roles = [] }) {
  if (!roles.length) {
    return <span className="text-sm text-muted">No roles</span>;
  }

  return (
    <div className="flex flex-wrap gap-2">
      {roles.map((role) => (
        <span className="inline-flex items-center gap-1 rounded-full bg-panel px-2 py-1 text-xs font-semibold text-muted" key={role.id || role.slug}>
          <ShieldCheck size={12} />
          {role.name || formatLabel(role.slug)}
        </span>
      ))}
    </div>
  );
}

function formFromUser(user) {
  if (!user) {
    return emptyForm();
  }

  return {
    first_name: user.first_name || '',
    last_name: user.last_name || '',
    email: user.email || '',
    phone: user.phone || '',
    password: '',
    status: user.status || 'active',
    role_ids: (user.roles || []).map((role) => Number(role.id)).filter(Boolean),
  };
}

function rolesChanged(user, nextRoleIds) {
  const current = (user.roles || []).map((role) => Number(role.id)).filter(Boolean).sort((a, b) => a - b);
  const next = [...nextRoleIds].map(Number).filter(Boolean).sort((a, b) => a - b);

  return current.join(',') !== next.join(',');
}

function validateForm(form, mode, requiresRole) {
  const errors = {};

  if (!form.first_name.trim()) errors.first_name = ['First name is required.'];
  if (!form.last_name.trim()) errors.last_name = ['Last name is required.'];
  if (!form.email.trim()) errors.email = ['Email is required.'];
  if (form.email.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    errors.email = ['Email must be valid.'];
  }
  if ((mode === 'create' || form.password) && form.password.length < 8) {
    errors.password = ['Password must be at least 8 characters.'];
  }
  if (requiresRole && form.role_ids.length === 0) {
    errors.role_ids = ['At least one role is required.'];
  }

  return errors;
}

function compactParams(params) {
  return Object.fromEntries(Object.entries(params).filter(([, value]) => value !== '' && value !== undefined && value !== null));
}

function fullName(user) {
  return `${user?.first_name || ''} ${user?.last_name || ''}`.trim() || user?.email || 'User';
}

function initials(value) {
  const words = String(value || 'User')
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  return words
    .slice(0, 2)
    .map((word) => word[0]?.toUpperCase())
    .join('');
}

function formatLabel(value) {
  return String(value || 'unknown')
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function firstError(error) {
  return Array.isArray(error) ? error[0] : error;
}
