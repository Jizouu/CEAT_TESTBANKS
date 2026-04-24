import { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router';
import { Button } from './ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from './ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from './ui/dialog';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Header } from './shared/Header';
import { BookOpen, Calendar, Clock, Plus, FileText, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

interface Enrollment {
  id: string;
  className: string;
  courseCode: string;
  instructor: string;
  enrolledDate: string;
}

interface TestDeadline {
  id: string;
  testName: string;
  courseCode: string;
  dueDate: string;
  status: 'upcoming' | 'overdue' | 'completed';
}

export function StudentDashboard() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [enrollments, setEnrollments] = useState<Enrollment[]>([
    {
      id: '1',
      className: 'Introduction to Programming',
      courseCode: 'CS101',
      instructor: 'Dr. Smith',
      enrolledDate: '2026-03-15'
    }
  ]);
  const [deadlines] = useState<TestDeadline[]>([
    {
      id: '1',
      testName: 'Midterm Exam',
      courseCode: 'CS101',
      dueDate: '2026-04-30',
      status: 'upcoming'
    },
    {
      id: '2',
      testName: 'Quiz 2',
      courseCode: 'MATH201',
      dueDate: '2026-04-28',
      status: 'upcoming'
    }
  ]);
  const [classCode, setClassCode] = useState('');
  const [isDialogOpen, setIsDialogOpen] = useState(false);

  const handleEnroll = () => {
    if (classCode.trim()) {
      const newEnrollment: Enrollment = {
        id: Date.now().toString(),
        className: 'New Course',
        courseCode: classCode,
        instructor: 'Faculty',
        enrolledDate: new Date().toISOString().split('T')[0]
      };
      setEnrollments([...enrollments, newEnrollment]);
      toast.success('Successfully enrolled in class!');
      setClassCode('');
      setIsDialogOpen(false);

      const log = {
        userId: user?.id,
        userName: user?.name,
        action: `enrolled in class ${classCode}`,
        timestamp: new Date().toISOString()
      };
      const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
      logs.push(log);
      localStorage.setItem('activityLogs', JSON.stringify(logs));
    }
  };

  const handleUnenroll = (enrollment: Enrollment) => {
    setEnrollments(enrollments.filter(e => e.id !== enrollment.id));
    toast.success(`Unenrolled from ${enrollment.className}`);

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `unenrolled from ${enrollment.courseCode}`,
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));
  };

  const handleTakeExam = (courseCode: string) => {
    const examId = Date.now().toString();
    navigate(`/exam?id=${examId}&course=${courseCode}`);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Student" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-8">
          <h2>Test Deadlines & Timeline</h2>
          <div className="grid gap-4 mt-4">
            {deadlines.map((deadline) => (
              <Card key={deadline.id} className="border-l-4 border-l-[#FF8C00]">
                <CardContent className="flex items-center justify-between p-4">
                  <div className="flex items-center gap-4">
                    <div className="p-2 bg-[#800000]/10 rounded-lg">
                      <Calendar className="w-5 h-5 text-[#800000]" />
                    </div>
                    <div>
                      <h3>{deadline.testName}</h3>
                      <p className="text-sm text-muted-foreground">{deadline.courseCode}</p>
                    </div>
                  </div>
                  <div className="flex items-center gap-2 text-sm">
                    <Clock className="w-4 h-4 text-muted-foreground" />
                    <span>Due: {new Date(deadline.dueDate).toLocaleDateString()}</span>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        </div>

        <div className="flex items-center justify-between mb-4">
          <h2>My Classes</h2>
          <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
            <DialogTrigger asChild>
              <Button className="bg-[#FF8C00] hover:bg-[#E67E00]">
                <Plus className="w-4 h-4 mr-2" />
                Enroll in Class
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Enroll in a Class</DialogTitle>
                <DialogDescription>Enter the class code provided by your instructor</DialogDescription>
              </DialogHeader>
              <div className="space-y-4 py-4">
                <div className="space-y-2">
                  <Label htmlFor="classCode">Class Code</Label>
                  <Input
                    id="classCode"
                    placeholder="e.g., CS101-2026"
                    value={classCode}
                    onChange={(e) => setClassCode(e.target.value)}
                  />
                </div>
              </div>
              <DialogFooter>
                <Button onClick={handleEnroll} className="bg-[#800000] hover:bg-[#600000]">
                  Enroll
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {enrollments.map((enrollment) => (
            <Card key={enrollment.id}>
              <CardHeader>
                <CardTitle>{enrollment.className}</CardTitle>
                <CardDescription>{enrollment.courseCode}</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-sm mb-2">Instructor: {enrollment.instructor}</p>
                <p className="text-xs text-muted-foreground">
                  Enrolled: {new Date(enrollment.enrolledDate).toLocaleDateString()}
                </p>
                <div className="flex gap-2 mt-4">
                  <Button
                    onClick={() => handleTakeExam(enrollment.courseCode)}
                    className="flex-1 bg-[#800000] hover:bg-[#600000]"
                  >
                    <FileText className="w-4 h-4 mr-2" />
                    Take Exam
                  </Button>
                  <Button
                    onClick={() => handleUnenroll(enrollment)}
                    variant="outline"
                    size="icon"
                    className="text-destructive"
                  >
                    <Trash2 className="w-4 h-4" />
                  </Button>
                </div>
              </CardContent>
            </Card>
          ))}
        </div>
      </main>
    </div>
  );
}
