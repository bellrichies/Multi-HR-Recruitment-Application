import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import {
  markAllNotificationsRead,
  markNotificationRead,
  markNotificationUnread,
  notifications as fetchNotifications,
  unreadNotifications,
} from '../api/notifications.api';
import { usePermissions } from '../hooks/usePermissions';
import { useAuth } from './auth.store';

const NotificationContext = createContext(null);
const POLL_INTERVAL_MS = 30000;

export function NotificationProvider({ children }) {
  const auth = useAuth();
  const { hasPermission } = usePermissions();
  const canView = auth.isAuthenticated && hasPermission('notifications.view');
  const canManage = auth.isAuthenticated && hasPermission('notifications.manage');
  const [recent, setRecent] = useState([]);
  const [unreadCount, setUnreadCount] = useState(0);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [hasNewActivity, setHasNewActivity] = useState(false);
  const loadedRef = useRef(false);
  const latestIdRef = useRef(null);
  const unreadCountRef = useRef(0);

  const reset = useCallback(() => {
    setRecent([]);
    setUnreadCount(0);
    setLoading(false);
    setError(null);
    setHasNewActivity(false);
    loadedRef.current = false;
    latestIdRef.current = null;
    unreadCountRef.current = 0;
  }, []);

  const refresh = useCallback(
    async ({ silent = false } = {}) => {
      if (!canView) {
        reset();
        return;
      }

      if (!silent) {
        setLoading(true);
      }

      try {
        const [notificationResponse, unreadResponse] = await Promise.all([
          fetchNotifications({ per_page: 6 }),
          unreadNotifications(),
        ]);
        const nextRecent = notificationResponse.data || [];
        const nextUnreadCount = Number(unreadResponse.data?.unread_count || 0);
        const latestId = nextRecent[0]?.id || null;

        if (loadedRef.current && ((latestId && latestId !== latestIdRef.current) || nextUnreadCount > unreadCountRef.current)) {
          setHasNewActivity(true);
        }

        loadedRef.current = true;
        latestIdRef.current = latestId;
        unreadCountRef.current = nextUnreadCount;
        setRecent(nextRecent);
        setUnreadCount(nextUnreadCount);
        setError(null);
      } catch (refreshError) {
        setError(refreshError);
      } finally {
        if (!silent) {
          setLoading(false);
        }
      }
    },
    [canView, reset],
  );

  useEffect(() => {
    refresh();
  }, [refresh]);

  useEffect(() => {
    if (!canView) {
      return undefined;
    }

    const intervalId = window.setInterval(() => refresh({ silent: true }), POLL_INTERVAL_MS);

    function handleFocus() {
      refresh({ silent: true });
    }

    function handleVisibilityChange() {
      if (document.visibilityState === 'visible') {
        refresh({ silent: true });
      }
    }

    window.addEventListener('focus', handleFocus);
    document.addEventListener('visibilitychange', handleVisibilityChange);

    return () => {
      window.clearInterval(intervalId);
      window.removeEventListener('focus', handleFocus);
      document.removeEventListener('visibilitychange', handleVisibilityChange);
    };
  }, [canView, refresh]);

  const markRead = useCallback(
    async (notificationId) => {
      if (!canManage) {
        return null;
      }

      const existing = recent.find((notification) => Number(notification.id) === Number(notificationId));

      if (existing && !existing.read_at) {
        setUnreadCount((count) => Math.max(0, count - 1));
        unreadCountRef.current = Math.max(0, unreadCountRef.current - 1);
      }

      setRecent((items) =>
        items.map((notification) =>
          Number(notification.id) === Number(notificationId)
            ? { ...notification, read_at: notification.read_at || new Date().toISOString() }
            : notification,
        ),
      );

      try {
        const response = await markNotificationRead(notificationId);
        setRecent((items) => items.map((notification) => (notification.id === response.data.id ? response.data : notification)));
        await refresh({ silent: true });
        return response.data;
      } catch (readError) {
        await refresh({ silent: true });
        throw readError;
      }
    },
    [canManage, recent, refresh],
  );

  const markUnread = useCallback(
    async (notificationId) => {
      if (!canManage) {
        return null;
      }

      const existing = recent.find((notification) => Number(notification.id) === Number(notificationId));

      if (existing && existing.read_at) {
        setUnreadCount((count) => count + 1);
        unreadCountRef.current += 1;
      }

      setRecent((items) =>
        items.map((notification) =>
          Number(notification.id) === Number(notificationId) ? { ...notification, read_at: null } : notification,
        ),
      );

      try {
        const response = await markNotificationUnread(notificationId);
        setRecent((items) => items.map((notification) => (notification.id === response.data.id ? response.data : notification)));
        await refresh({ silent: true });
        return response.data;
      } catch (unreadError) {
        await refresh({ silent: true });
        throw unreadError;
      }
    },
    [canManage, recent, refresh],
  );

  const markAllRead = useCallback(async () => {
    if (!canManage) {
      return;
    }

    setUnreadCount(0);
    unreadCountRef.current = 0;
    setRecent((items) => items.map((notification) => ({ ...notification, read_at: notification.read_at || new Date().toISOString() })));

    try {
      await markAllNotificationsRead();
      await refresh({ silent: true });
    } catch (readError) {
      await refresh({ silent: true });
      throw readError;
    }
  }, [canManage, refresh]);

  const value = useMemo(
    () => ({
      canManage,
      canView,
      error,
      hasNewActivity,
      loading,
      markAllRead,
      markRead,
      markUnread,
      recent,
      refresh,
      unreadCount,
      acknowledgeActivity: () => setHasNewActivity(false),
    }),
    [canManage, canView, error, hasNewActivity, loading, markAllRead, markRead, markUnread, recent, refresh, unreadCount],
  );

  return <NotificationContext.Provider value={value}>{children}</NotificationContext.Provider>;
}

export function useNotifications() {
  const context = useContext(NotificationContext);

  if (!context) {
    throw new Error('useNotifications must be used inside NotificationProvider.');
  }

  return context;
}
