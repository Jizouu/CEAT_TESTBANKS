import { useState, useEffect } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router';
import { Button } from './ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Header } from './shared/Header';
import { Activity, Users, FileText, TrendingUp, Settings, Database } from 'lucide-react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from './ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from './ui/tabs';

interface ActivityLog {
  userId: string;
  userName: string;
  action: string;
  timestamp: string;
}

export function SuperAdminDashboard() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [logs, setLogs] = useState<ActivityLog[]>([]);
  const [filter, setFilter] = useState<string>('all');

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
    totalActions: logs.length,
    recentActivity: logs.slice(0, 10).length
  };

  const filteredLogs = filter === 'all' ? logs : logs.filter(log => log.action === filter);

  const clearLogs = () => {
    localStorage.setItem('activityLogs', '[]');
    setLogs([]);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Super Admin" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="grid gap-4 md:grid-cols-3 mb-8">
          <Button onClick={() => navigate('/system-settings')} className="h-20 bg-[#800000] hover:bg-[#600000]">
            <Settings className="w-5 h-5 mr-2" />
            System Settings
          </Button>
          <Button onClick={() => navigate('/database-editor')} className="h-20 bg-[#FF8C00] hover:bg-[#E67E00]">
            <Database className="w-5 h-5 mr-2" />
            Database Editor
          </Button>
          <Button onClick={() => navigate('/dashboard')} variant="outline" className="h-20">
            <Activity className="w-5 h-5 mr-2" />
            View Analytics
          </Button>
        </div>

        <div className="grid gap-4 md:grid-cols-4 mb-8">
          <Card className="border-l-4 border-l-[#800000]">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Users</CardTitle>
              <Users className="h-4 w-4 text-[#800000]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalUsers}</div>
              <p className="text-xs text-muted-foreground">Active users in system</p>
            </CardContent>
          </Card>
          <Card className="border-l-4 border-l-[#FF8C00]">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Logins</CardTitle>
              <Activity className="h-4 w-4 text-[#FF8C00]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalLogins}</div>
              <p className="text-xs text-muted-foreground">Login attempts</p>
            </CardContent>
          </Card>
          <Card className="border-l-4 border-l-[#800000]">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Activities</CardTitle>
              <FileText className="h-4 w-4 text-[#800000]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalActions}</div>
              <p className="text-xs text-muted-foreground">All logged actions</p>
            </CardContent>
          </Card>
          <Card className="border-l-4 border-l-[#FF8C00]">
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Recent Activity</CardTitle>
              <TrendingUp className="h-4 w-4 text-[#FF8C00]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.recentActivity}</div>
              <p className="text-xs text-muted-foreground">Last 10 actions</p>
            </CardContent>
          </Card>
        </div>

        <Tabs defaultValue="logs" className="w-full">
          <TabsList>
            <TabsTrigger value="logs">Activity Logs</TabsTrigger>
            <TabsTrigger value="analytics">Analytics</TabsTrigger>
          </TabsList>

          <TabsContent value="logs">
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle>User Activity Logs</CardTitle>
                    <CardDescription>Monitor all user activities in real-time</CardDescription>
                  </div>
                  <div className="flex gap-2">
                    <select
                      className="p-2 border rounded-md"
                      value={filter}
                      onChange={(e) => setFilter(e.target.value)}
                    >
                      <option value="all">All Actions</option>
                      <option value="login">Logins Only</option>
                      <option value="logout">Logouts Only</option>
                    </select>
                    <Button onClick={clearLogs} variant="outline" className="text-destructive">
                      Clear Logs
                    </Button>
                  </div>
                </div>
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
                    {filteredLogs.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={4} className="text-center text-muted-foreground">
                          No activity logs yet
                        </TableCell>
                      </TableRow>
                    ) : (
                      filteredLogs.map((log, index) => (
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
          </TabsContent>

          <TabsContent value="analytics">
            <div className="grid gap-4 md:grid-cols-2">
              <Card>
                <CardHeader>
                  <CardTitle>User Activity by Action</CardTitle>
                  <CardDescription>Breakdown of all user actions</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-3">
                    {['login', 'logout'].map(action => {
                      const count = logs.filter(log => log.action === action).length;
                      const percentage = logs.length > 0 ? (count / logs.length) * 100 : 0;
                      return (
                        <div key={action}>
                          <div className="flex justify-between mb-1">
                            <span className="capitalize">{action}</span>
                            <span>{count}</span>
                          </div>
                          <div className="w-full bg-gray-200 rounded-full h-2">
                            <div
                              className="bg-[#800000] h-2 rounded-full"
                              style={{ width: `${percentage}%` }}
                            ></div>
                          </div>
                        </div>
                      );
                    })}
                    {logs.filter(log => !['login', 'logout'].includes(log.action)).length > 0 && (
                      <div>
                        <div className="flex justify-between mb-1">
                          <span>Other Actions</span>
                          <span>{logs.filter(log => !['login', 'logout'].includes(log.action)).length}</span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2">
                          <div
                            className="bg-[#FF8C00] h-2 rounded-full"
                            style={{
                              width: `${logs.length > 0 ? (logs.filter(log => !['login', 'logout'].includes(log.action)).length / logs.length) * 100 : 0}%`
                            }}
                          ></div>
                        </div>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Most Active Users</CardTitle>
                  <CardDescription>Users with most activities</CardDescription>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    {Array.from(new Set(logs.map(log => log.userId)))
                      .map(userId => ({
                        userId,
                        userName: logs.find(log => log.userId === userId)?.userName || '',
                        count: logs.filter(log => log.userId === userId).length
                      }))
                      .sort((a, b) => b.count - a.count)
                      .slice(0, 5)
                      .map((userStat, idx) => (
                        <div key={idx} className="flex items-center justify-between">
                          <div>
                            <p>{userStat.userName}</p>
                            <p className="text-xs text-muted-foreground">ID: {userStat.userId}</p>
                          </div>
                          <span className="px-3 py-1 bg-[#800000] text-white rounded-full text-sm">
                            {userStat.count}
                          </span>
                        </div>
                      ))}
                  </div>
                </CardContent>
              </Card>
            </div>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
}
