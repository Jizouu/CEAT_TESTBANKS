import { useAuth } from '../../context/AuthContext';
import { Button } from '../ui/button';
import { BookOpen, LogOut, Key, Shield } from 'lucide-react';
import { useNavigate } from 'react-router';

interface HeaderProps {
  title: string;
  showChangePassword?: boolean;
}

export function Header({ title, showChangePassword = true }: HeaderProps) {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const getRoleIcon = () => {
    if (user?.role === 'superadmin') return Shield;
    return BookOpen;
  };

  const Icon = getRoleIcon();

  return (
    <header className="bg-gradient-to-r from-[#800000] to-[#A52A2A] text-white shadow-lg">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center">
            <Icon className="w-6 h-6" />
          </div>
          <div>
            <h1>{title}</h1>
            <p className="text-sm text-white/80">Welcome, {user?.name}</p>
          </div>
        </div>
        <div className="flex gap-2">
          {showChangePassword && (
            <Button
              onClick={() => navigate('/change-password')}
              variant="outline"
              className="bg-white/10 border-white/20 text-white hover:bg-white/20"
            >
              <Key className="w-4 h-4 mr-2" />
              Change Password
            </Button>
          )}
          <Button
            onClick={logout}
            variant="outline"
            className="bg-white/10 border-white/20 text-white hover:bg-white/20"
          >
            <LogOut className="w-4 h-4 mr-2" />
            Logout
          </Button>
        </div>
      </div>
    </header>
  );
}
