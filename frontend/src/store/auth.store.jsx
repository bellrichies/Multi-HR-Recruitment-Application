import { createContext, useContext, useEffect, useMemo, useState } from 'react';
import { clearSession, getStoredToken, getStoredUser, persistSession } from '../api/client';
import * as authApi from '../api/auth.api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [token, setToken] = useState(getStoredToken());
  const [user, setUser] = useState(getStoredUser());
  const [booting, setBooting] = useState(Boolean(getStoredToken()));

  useEffect(() => {
    if (!token) {
      setBooting(false);
      return;
    }

    authApi
      .me()
      .then((response) => {
        const nextUser = response.data?.user || response.data;
        setUser(nextUser);
        persistSession(token, nextUser);
      })
      .catch(() => {
        clearSession();
        setToken(null);
        setUser(null);
      })
      .finally(() => setBooting(false));
  }, [token]);

  const value = useMemo(
    () => ({
      token,
      user,
      booting,
      isAuthenticated: Boolean(token && user),
      roles: user?.roles || [],
      permissions: user?.permissions || [],
      setSession(nextToken, nextUser) {
        persistSession(nextToken, nextUser);
        setToken(nextToken);
        setUser(nextUser);
      },
      async logout() {
        await authApi.logout();
        setToken(null);
        setUser(null);
      },
    }),
    [booting, token, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);

  if (!context) {
    throw new Error('useAuth must be used inside AuthProvider.');
  }

  return context;
}
