import React, { createContext, useContext, useState, ReactNode } from 'react';

export type UserRole = 'student' | 'faculty' | 'admin' | 'superadmin';

export interface User {
  id: string;
  name: string;
  email: string;
  role: UserRole;
}

interface AuthContextType {
  user: User | null;
  login: (email: string, password: string) => boolean;
  logout: () => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const mockUsers: Record<string, { password: string; user: User }> = {
  'student@uphsd.edu': {
    password: 'student123',
    user: { id: '1', name: 'John Student', email: 'student@uphsd.edu', role: 'student' }
  },
  'faculty@uphsd.edu': {
    password: 'faculty123',
    user: { id: '2', name: 'Jane Faculty', email: 'faculty@uphsd.edu', role: 'faculty' }
  },
  'admin@uphsd.edu': {
    password: 'admin123',
    user: { id: '3', name: 'Admin User', email: 'admin@uphsd.edu', role: 'admin' }
  },
  'superadmin@uphsd.edu': {
    password: 'superadmin123',
    user: { id: '4', name: 'Super Admin', email: 'superadmin@uphsd.edu', role: 'superadmin' }
  }
};

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<User | null>(null);

  const login = (email: string, password: string): boolean => {
    const userRecord = mockUsers[email];
    if (userRecord && userRecord.password === password) {
      setUser(userRecord.user);
      const log = {
        userId: userRecord.user.id,
        userName: userRecord.user.name,
        action: 'login',
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
      return true;
    }
    return false;
  };

  const logout = () => {
    if (user) {
      const log = {
        userId: user.id,
        userName: user.name,
        action: 'logout',
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
