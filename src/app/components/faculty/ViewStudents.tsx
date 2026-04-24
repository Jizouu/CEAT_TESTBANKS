import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import { ArrowLeft, Mail, UserMinus } from 'lucide-react';
import { toast } from 'sonner';

interface Student {
  id: string;
  name: string;
  email: string;
  enrolledDate: string;
}

export function ViewStudents() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const classId = searchParams.get('classId');
  const className = searchParams.get('className') || 'Class';

  const [students] = useState<Student[]>([
    {
      id: '1',
      name: 'John Student',
      email: 'student@uphsd.edu',
      enrolledDate: '2026-03-15'
    },
    {
      id: '5',
      name: 'Maria Garcia',
      email: 'maria.garcia@uphsd.edu',
      enrolledDate: '2026-03-16'
    },
    {
      id: '6',
      name: 'James Wilson',
      email: 'james.wilson@uphsd.edu',
      enrolledDate: '2026-03-17'
    }
  ]);

  const handleRemoveStudent = (student: Student) => {
    toast.success(`${student.name} removed from class`);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - View Students" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button
          onClick={() => navigate('/dashboard')}
          variant="ghost"
          className="mb-4"
        >
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Dashboard
        </Button>

        <Card>
          <CardHeader>
            <CardTitle>Students Enrolled in {className}</CardTitle>
            <CardDescription>{students.length} student{students.length !== 1 ? 's' : ''} enrolled</CardDescription>
          </CardHeader>
          <CardContent>
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Student ID</TableHead>
                  <TableHead>Name</TableHead>
                  <TableHead>Email</TableHead>
                  <TableHead>Enrolled Date</TableHead>
                  <TableHead className="text-right">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {students.map((student) => (
                  <TableRow key={student.id}>
                    <TableCell>{student.id}</TableCell>
                    <TableCell>{student.name}</TableCell>
                    <TableCell>
                      <div className="flex items-center gap-2">
                        <Mail className="w-4 h-4 text-muted-foreground" />
                        {student.email}
                      </div>
                    </TableCell>
                    <TableCell>{new Date(student.enrolledDate).toLocaleDateString()}</TableCell>
                    <TableCell className="text-right">
                      <Button
                        onClick={() => handleRemoveStudent(student)}
                        variant="ghost"
                        size="sm"
                        className="text-destructive hover:text-destructive"
                      >
                        <UserMinus className="w-4 h-4 mr-2" />
                        Remove
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
