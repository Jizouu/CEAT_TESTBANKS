import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router';
import { Button } from './ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Header } from './shared/Header';
import { BookOpen, Activity, Users, FileText, GraduationCap, UserPlus } from 'lucide-react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';

interface ActivityLog {
  userId: string;
  userName: string;
  action: string;
  timestamp: string;
}

export function AdminDashboard() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [logs, setLogs] = useState<ActivityLog[]>([]);

  useEffect(() => {
    const loadLogs = () => {
      const storedLogs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      setLogs(storedLogs.reverse());
    };
    loadLogs();
    const interval = setInterval(loadLogs, 2000);
    return () => clearInterval(interval);
  }, []);

  const stats = {
    totalUsers: new Set(logs.map(log => log.userId)).size,
    totalLogins: logs.filter(log => log.action === 'login').length,
    totalActions: logs.length
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Admin" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid gap-4 md:grid-cols-4 mb-8">
          <Button onClick={() => navigate('/manage-programs')} className="h-20 bg-[#800000] hover:bg-[#600000]">
            <GraduationCap className="w-5 h-5 mr-2" />
            Manage Programs
          </Button>
          <Button onClick={() => navigate('/manage-courses')} className="h-20 bg-[#FF8C00] hover:bg-[#E67E00]">
            <BookOpen className="w-5 h-5 mr-2" />
            Manage Courses
          </Button>
          <Button onClick={() => navigate('/manage-users')} className="h-20 bg-[#800000] hover:bg-[#600000]">
            <UserPlus className="w-5 h-5 mr-2" />
            Manage Users
          </Button>
          <Button onClick={() => navigate('/dashboard')} variant="outline" className="h-20">
            <Activity className="w-5 h-5 mr-2" />
            View Logs
          </Button>
        </div>

        <div className="grid gap-4 md:grid-cols-3 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Users</CardTitle>
              <Users className="h-4 w-4 text-[#800000]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalUsers}</div>
              <p className="text-xs text-muted-foreground">Active users in system</p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Logins</CardTitle>
              <Activity className="h-4 w-4 text-[#FF8C00]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalLogins}</div>
              <p className="text-xs text-muted-foreground">Login attempts</p>
            </CardContent>
          </Card>
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Activities</CardTitle>
              <FileText className="h-4 w-4 text-[#800000]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalActions}</div>
              <p className="text-xs text-muted-foreground">All logged actions</p>
            </CardContent>
          </Card>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>User Activity Logs</CardTitle>
            <CardDescription>Monitor all user activities in real-time</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Timestamp</TableHead>
                  <TableHead>User</TableHead>
                  <TableHead>User ID</TableHead>
                  <TableHead>Action</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {logs.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={4} className="text-center text-muted-foreground">
                      No activity logs yet
                    </TableCell>
                  </TableRow>
                ) : (
                  logs.map((log, index) => (
                    <TableRow key={index}>
                      <TableCell>{new Date(log.timestamp).toLocaleString()}</TableCell>
                      <TableCell>{log.userName}</TableCell>
                      <TableCell className="text-muted-foreground">{log.userId}</TableCell>
                      <TableCell>
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs ${
                          log.action === 'login' ? 'bg-green-100 text-green-800' :
                          log.action === 'logout' ? 'bg-red-100 text-red-800' :
                          'bg-blue-100 text-blue-800'
                        }`}>
                          {log.action}
                        </span>
                      </TableCell>
                    </TableRow>
                  ))
                )}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
