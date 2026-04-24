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
import { ArrowLeft, Plus, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

interface Course {
  id: string;
  name: string;
  code: string;
  program: string;
  units: number;
  description: string;
}

export function ManageCourses() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [courses, setCourses] = useState<Course[]>([
    {
      id: '1',
      name: 'Introduction to Programming',
      code: 'CS101',
      program: 'CS',
      units: 3,
      description: 'Fundamentals of programming'
    },
    {
      id: '2',
      name: 'Data Structures',
      code: 'CS201',
      program: 'CS',
      units: 3,
      description: 'Advanced data structures and algorithms'
    }
  ]);

  const [dialogOpen, setDialogOpen] = useState(false);
  const [newCourse, setNewCourse] = useState({
    name: '',
    code: '',
    program: 'CS',
    units: 3,
    description: ''
  });

  const handleAddCourse = () => {
    if (newCourse.name && newCourse.code) {
      const course: Course = {
        id: Date.now().toString(),
        ...newCourse
      };
      setCourses([...courses, course]);
      toast.success('Course added successfully!');
      setNewCourse({ name: '', code: '', program: 'CS', units: 3, description: '' });
      setDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: `added course ${newCourse.code}`,
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const handleDeleteCourse = (course: Course) => {
    setCourses(courses.filter(c => c.id !== course.id));
    toast.success('Course deleted');

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `deleted course ${course.code}`,
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Manage Courses" />

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
                Add Course
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Add New Course</DialogTitle>
                <DialogDescription>Create a new course</DialogDescription>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label>Course Name</Label>
                  <Input
                    placeholder="Introduction to Programming"
                    value={newCourse.name}
                    onChange={(e) => setNewCourse({ ...newCourse, name: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Course Code</Label>
                  <Input
                    placeholder="CS101"
                    value={newCourse.code}
                    onChange={(e) => setNewCourse({ ...newCourse, code: e.target.value })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Program</Label>
                  <select
                    className="w-full p-2 border rounded-md"
                    value={newCourse.program}
                    onChange={(e) => setNewCourse({ ...newCourse, program: e.target.value })}
                  >
                    <option value="CS">Computer Science</option>
                    <option value="IT">Information Technology</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <Label>Units</Label>
                  <Input
                    type="number"
                    min="1"
                    max="6"
                    value={newCourse.units}
                    onChange={(e) => setNewCourse({ ...newCourse, units: parseInt(e.target.value) })}
                  />
                </div>
                <div className="space-y-2">
                  <Label>Description</Label>
                  <Textarea
                    placeholder="Course description..."
                    value={newCourse.description}
                    onChange={(e) => setNewCourse({ ...newCourse, description: e.target.value })}
                  />
                </div>
              </div>
              <DialogFooter>
                <Button onClick={handleAddCourse} className="bg-[#800000] hover:bg-[#600000]">
                  Add Course
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Course Directory</CardTitle>
            <CardDescription>{courses.length} course{courses.length !== 1 ? 's' : ''} available</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Code</TableHead>
                  <TableHead>Course Name</TableHead>
                  <TableHead>Program</TableHead>
                  <TableHead>Units</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {courses.map((course) => (
                  <TableRow key={course.id}>
                    <TableCell>{course.code}</TableCell>
                    <TableCell>{course.name}</TableCell>
                    <TableCell>{course.program}</TableCell>
                    <TableCell>{course.units}</TableCell>
                    <TableCell>{course.description}</TableCell>
                    <TableCell className="text-right">
                      <Button
                        onClick={() => handleDeleteCourse(course)}
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
