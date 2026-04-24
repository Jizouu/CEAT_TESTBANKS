import { useState } from 'react';
import { useNavigate } from 'react-router';
import { useAuth } from '../../context/AuthContext';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../ui/dialog';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import { ArrowLeft, Plus, Trash2, UserPlus } from 'lucide-react';
import { toast } from 'sonner';

interface User {
  id: string;
  name: string;
  email: string;
  role: 'student' | 'faculty' | 'admin';
  createdDate: string;
}

export function ManageUsers() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [users, setUsers] = useState<User[]>([
    {
      id: '1',
      name: 'John Student',
      email: 'student@uphsd.edu',
      role: 'student',
      createdDate: '2026-03-01'
    },
    {
      id: '2',
      name: 'Jane Faculty',
      email: 'faculty@uphsd.edu',
      role: 'faculty',
      createdDate: '2026-02-15'
    }
  ]);

  const [dialogOpen, setDialogOpen] = useState(false);
  const [newUser, setNewUser] = useState({
    name: '',
    email: '',
    role: 'student' as 'student' | 'faculty' | 'admin',
    password: ''
  });

  const handleAddUser = () => {
    if (newUser.name && newUser.email && newUser.password) {
      const userRecord: User = {
        id: Date.now().toString(),
        name: newUser.name,
        email: newUser.email,
        role: newUser.role,
        createdDate: new Date().toISOString()
      };
      setUsers([...users, userRecord]);
      toast.success('User added successfully!');
      setNewUser({ name: '', email: '', role: 'student', password: '' });
      setDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: `added user ${newUser.email}`,
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const handleDeleteUser = (userToDelete: User) => {
    setUsers(users.filter(u => u.id !== userToDelete.id));
    toast.success('User deleted');

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `deleted user ${userToDelete.email}`,
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Manage Users" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-4">
          <Button onClick={() => navigate('/dashboard')} variant="ghost">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Dashboard
          </Button>

          <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
            <DialogTrigger asChild>
              <Button className="bg-[#FF8C00] hover:bg-[#E67E00]">
                <UserPlus className="w-4 h-4 mr-2" />
                Add User
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New User</DialogTitle>
                <DialogDescription>Create a new user account</DialogDescription>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label>Full Name</Label>
                  <Input
                    placeholder="John Doe"
                    value={newUser.name}
                    onChange={(e) => setNewUser({ ...newUser, name: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Email</Label>
                  <Input
                    type="email"
                    placeholder="john.doe@uphsd.edu"
                    value={newUser.email}
                    onChange={(e) => setNewUser({ ...newUser, email: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Role</Label>
                  <select
                    className="w-full p-2 border rounded-md"
                    value={newUser.role}
                    onChange={(e) => setNewUser({ ...newUser, role: e.target.value as any })}
                  >
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <Label>Password</Label>
                  <Input
                    type="password"
                    placeholder="••••••••"
                    value={newUser.password}
                    onChange={(e) => setNewUser({ ...newUser, password: e.target.value })}
                  />
                </div>
              </div>
              <DialogFooter>
                <Button onClick={handleAddUser} className="bg-[#800000] hover:bg-[#600000]">
                  Add User
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>User Management</CardTitle>
            <CardDescription>{users.length} user{users.length !== 1 ? 's' : ''} in system</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>ID</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Role</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {users.map((u) => (
                  <TableRow key={u.id}>
                    <TableCell>{u.id}</TableCell>
                    <TableCell>{u.name}</TableCell>
                    <TableCell>{u.email}</TableCell>
                    <TableCell>
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs capitalize ${
                        u.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                        u.role === 'faculty' ? 'bg-blue-100 text-blue-800' :
                        'bg-green-100 text-green-800'
                      }`}>
                        {u.role}
                      </span>
                    </TableCell>
                    <TableCell>{new Date(u.createdDate).toLocaleDateString()}</TableCell>
                    <TableCell className="text-right">
                      <Button
                        onClick={() => handleDeleteUser(u)}
                        variant="ghost"
                        size="sm"
                        className="text-destructive hover:text-destructive"
                      >
                        <Trash2 className="w-4 h-4" />
                      </Button>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
