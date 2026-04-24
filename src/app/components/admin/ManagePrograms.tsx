import { useState } from 'react';
import { useNavigate } from 'react-router';
import { useAuth } from '../../context/AuthContext';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '../ui/dialog';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Textarea } from '../ui/textarea';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import { ArrowLeft, Plus, Trash2, Edit } from 'lucide-react';
import { toast } from 'sonner';

interface Program {
  id: string;
  name: string;
  code: string;
  description: string;
  createdDate: string;
}

export function ManagePrograms() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [programs, setPrograms] = useState<Program[]>([
    {
      id: '1',
      name: 'Computer Science',
      code: 'CS',
      description: 'Bachelor of Science in Computer Science',
      createdDate: '2026-01-01'
    },
    {
      id: '2',
      name: 'Information Technology',
      code: 'IT',
      description: 'Bachelor of Science in Information Technology',
      createdDate: '2026-01-01'
    }
  ]);

  const [dialogOpen, setDialogOpen] = useState(false);
  const [newProgram, setNewProgram] = useState({ name: '', code: '', description: '' });

  const handleAddProgram = () => {
    if (newProgram.name && newProgram.code) {
      const program: Program = {
        id: Date.now().toString(),
        ...newProgram,
        createdDate: new Date().toISOString()
      };
      setPrograms([...programs, program]);
      toast.success('Program added successfully!');
      setNewProgram({ name: '', code: '', description: '' });
      setDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: `added program ${newProgram.name}`,
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const handleDeleteProgram = (program: Program) => {
    setPrograms(programs.filter(p => p.id !== program.id));
    toast.success('Program deleted');

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `deleted program ${program.name}`,
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Manage Programs" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-4">
          <Button onClick={() => navigate('/dashboard')} variant="ghost">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Dashboard
          </Button>

          <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
            <DialogTrigger asChild>
              <Button className="bg-[#FF8C00] hover:bg-[#E67E00]">
                <Plus className="w-4 h-4 mr-2" />
                Add Program
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New Program</DialogTitle>
                <DialogDescription>Create a new academic program</DialogDescription>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label>Program Name</Label>
                  <Input
                    placeholder="Computer Science"
                    value={newProgram.name}
                    onChange={(e) => setNewProgram({ ...newProgram, name: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Program Code</Label>
                  <Input
                    placeholder="CS"
                    value={newProgram.code}
                    onChange={(e) => setNewProgram({ ...newProgram, code: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Description</Label>
                  <Textarea
                    placeholder="Program description..."
                    value={newProgram.description}
                    onChange={(e) => setNewProgram({ ...newProgram, description: e.target.value })}
                  />
                </div>
              </div>
              <DialogFooter>
                <Button onClick={handleAddProgram} className="bg-[#800000] hover:bg-[#600000]">
                  Add Program
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Academic Programs</CardTitle>
            <CardDescription>{programs.length} program{programs.length !== 1 ? 's' : ''} configured</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Code</TableHead>
                  <TableHead>Program Name</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead>Created</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {programs.map((program) => (
                  <TableRow key={program.id}>
                    <TableCell>{program.code}</TableCell>
                    <TableCell>{program.name}</TableCell>
                    <TableCell>{program.description}</TableCell>
                    <TableCell>{new Date(program.createdDate).toLocaleDateString()}</TableCell>
                    <TableCell className="text-right">
                      <Button
                        onClick={() => handleDeleteProgram(program)}
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
