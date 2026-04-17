import { createContext, useContext, useState, useEffect, useRef, type ReactNode } from 'react';
import api from '../api/client';
import type { Admin, LoginRequest, LoginResponse } from '../types/models';

interface AuthContextType {
  admin: Admin | null;
  token: string | null;
  login: (data: LoginRequest) => Promise<void>;
  logout: () => Promise<void>;
  isLoading: boolean;
}

const AuthContext = createContext<AuthContextType | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [admin, setAdmin] = useState<Admin | null>(null);
  const [token, setToken] = useState<string | null>(localStorage.getItem('mbg_token'));
  const [isLoading, setIsLoading] = useState(true);
  const skipMeCheck = useRef(false);

  useEffect(() => {
    // If we just logged in and already have admin data, skip /me call
    if (skipMeCheck.current) {
      skipMeCheck.current = false;
      setIsLoading(false);
      return;
    }

    if (token) {
      api.get('/me')
        .then((res) => {
          setAdmin(res.data.data);
        })
        .catch((err) => {
          console.error('/me failed:', err?.response?.status, err?.response?.data);
          localStorage.removeItem('mbg_token');
          setToken(null);
          setAdmin(null);
        })
        .finally(() => setIsLoading(false));
    } else {
      setIsLoading(false);
    }
  }, [token]);

  const login = async (data: LoginRequest) => {
    const res = await api.post<LoginResponse>('/auth/login', data);
    const { token: newToken, admin: newAdmin } = res.data;
    localStorage.setItem('mbg_token', newToken);
    setAdmin(newAdmin);
    skipMeCheck.current = true;
    setToken(newToken);
  };

  const logout = async () => {
    try {
      await api.post('/auth/logout');
    } catch {
      // ignore
    }
    localStorage.removeItem('mbg_token');
    setToken(null);
    setAdmin(null);
  };

  return (
    <AuthContext.Provider value={{ admin, token, login, logout, isLoading }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
