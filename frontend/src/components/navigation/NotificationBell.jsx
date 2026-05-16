import { Bell, CheckCheck, Loader2 } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useNotifications } from '../../store/notifications.store';
import { formatDate } from '../../utils/formatDate';

export function NotificationBell() {
  const [open, setOpen] = useState(false);
  const menuRef = useRef(null);
  const navigate = useNavigate();
  const {
    canManage,
    error,
    hasNewActivity,
    loading,
    markAllRead,
    markRead,
    recent,
    refresh,
    unreadCount,
    acknowledgeActivity,
  } = useNotifications();

  useEffect(() => {
    function handlePointerDown(event) {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        setOpen(false);
      }
    }

    document.addEventListener('pointerdown', handlePointerDown);

    return () => document.removeEventListener('pointerdown', handlePointerDown);
  }, []);

  function toggleOpen() {
    const nextOpen = !open;
    setOpen(nextOpen);

    if (nextOpen) {
      acknowledgeActivity();
      refresh({ silent: true });
    }
  }

  async function openNotification(notification) {
    if (canManage && !notification.read_at) {
      await markRead(notification.id);
    }

    setOpen(false);
    acknowledgeActivity();
    navigate(`/notifications?notification=${notification.id}`);
  }

  async function handleMarkAllRead() {
    await markAllRead();
    acknowledgeActivity();
  }

  return (
    <div className="relative" ref={menuRef}>
      <button
        aria-label={unreadCount > 0 ? `${unreadCount} unread notifications` : 'Notifications'}
        className={`focus-ring relative rounded-md p-2 text-muted hover:bg-panel hover:text-ink ${
          hasNewActivity ? 'text-brand' : ''
        }`}
        type="button"
        onClick={toggleOpen}
      >
        <Bell className={hasNewActivity ? 'animate-pulse' : ''} size={20} />
        {unreadCount > 0 ? (
          <span className="absolute -right-1 -top-1 min-w-5 rounded-full bg-danger px-1.5 py-0.5 text-center text-[10px] font-semibold leading-none text-white">
            {unreadCount > 99 ? '99+' : unreadCount}
          </span>
        ) : null}
        {hasNewActivity && unreadCount === 0 ? (
          <span className="absolute right-1 top-1 h-2 w-2 rounded-full bg-brand" />
        ) : null}
      </button>

      {open ? (
        <div className="absolute right-0 z-50 mt-2 w-[min(92vw,380px)] overflow-hidden rounded-md border border-line bg-white shadow-soft">
          <div className="flex items-center justify-between gap-3 border-b border-line px-4 py-3">
            <div className="min-w-0">
              <p className="text-sm font-semibold text-ink">Notifications</p>
              <p className="text-xs text-muted">{unreadCount} unread</p>
            </div>
            <div className="flex items-center gap-1">
              {loading ? <Loader2 className="animate-spin text-muted" size={16} /> : null}
              {canManage && unreadCount > 0 ? (
                <button
                  className="focus-ring inline-flex h-8 items-center gap-1 rounded-md px-2 text-xs font-semibold text-brand hover:bg-teal-50"
                  type="button"
                  onClick={handleMarkAllRead}
                >
                  <CheckCheck size={14} />
                  Read all
                </button>
              ) : null}
            </div>
          </div>

          {error ? <p className="border-b border-line px-4 py-3 text-sm text-danger">{error.message}</p> : null}

          <div className="max-h-[360px] overflow-y-auto">
            {recent.length === 0 ? (
              <p className="px-4 py-6 text-center text-sm text-muted">No notifications</p>
            ) : (
              recent.map((notification) => (
                <button
                  className={`focus-ring flex w-full gap-3 border-b border-line px-4 py-3 text-left last:border-b-0 ${
                    notification.read_at ? 'hover:bg-panel' : 'bg-teal-50 hover:bg-teal-100'
                  }`}
                  key={notification.id}
                  type="button"
                  onClick={() => openNotification(notification)}
                >
                  <span
                    className={`mt-1 h-2.5 w-2.5 shrink-0 rounded-full ${
                      notification.read_at ? 'bg-line' : 'bg-brand'
                    }`}
                  />
                  <span className="min-w-0 flex-1">
                    <span className="block truncate text-sm font-semibold text-ink">{notification.title}</span>
                    <span className="mt-1 line-clamp-2 block text-xs text-muted">{notification.body}</span>
                    <span className="mt-2 block text-[11px] text-muted">{formatDate(notification.created_at)}</span>
                  </span>
                </button>
              ))
            )}
          </div>

          <button
            className="focus-ring block w-full border-t border-line px-4 py-3 text-center text-sm font-semibold text-brand hover:bg-panel"
            type="button"
            onClick={() => {
              setOpen(false);
              navigate('/notifications');
            }}
          >
            View all
          </button>
        </div>
      ) : null}
    </div>
  );
}
