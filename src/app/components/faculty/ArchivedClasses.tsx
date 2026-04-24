import { useState } from 'react';
import { useNavigate } from 'react-router';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { ArrowLeft, Archive, RotateCcw } from 'lucide-react';
import { toast } from 'sonner';

interface ArchivedClass {
  id: string;
  name: string;
  code: string;
  archivedDate: string;
  totalStudents: number;
}

export function ArchivedClasses() {
  const navigate = useNavigate();
  const [archivedClasses, setArchivedClasses] = useState<ArchivedClass[]>([
    {
      id: '1',
      name: 'Introduction to Programming - Fall 2025',
      code: 'CS101-F25',
      archivedDate: '2026-01-15',
      totalStudents: 45
    },
    {
      id: '2',
      name: 'Data Structures - Spring 2025',
      code: 'CS201-S25',
      archivedDate: '2025-12-20',
      totalStudents: 38
    }
  ]);

  const handleRestore = (cls: ArchivedClass) => {
    setArchivedClasses(archivedClasses.filter(c => c.id !== cls.id));
    toast.success(`${cls.name} restored to active classes`);
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Archived Classes" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button onClick={() => navigate('/dashboard')} variant="ghost" className="mb-4">
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Dashboard
        </Button>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
          {archivedClasses.map((cls) => (
            <Card key={cls.id} className="border-l-4 border-l-gray-400">
              <CardHeader>
                <div className="flex items-start justify-between">
                  <div>
                    <CardTitle>{cls.name}</CardTitle>
                    <CardDescription>{cls.code}</CardDescription>
                  </div>
                  <Archive className="w-5 h-5 text-muted-foreground" />
                </div>
              </CardHeader>
              <CardContent className="space-y-3">
                <div className="text-sm">
                  <p className="text-muted-foreground">Students: {cls.totalStudents}</p>
                  <p className="text-muted-foreground">
                    Archived: {new Date(cls.archivedDate).toLocaleDateString()}
                  </p>
                </div>
                <Button
                  onClick={() => handleRestore(cls)}
                  className="w-full bg-[#800000] hover:bg-[#600000]"
                >
                  <RotateCcw className="w-4 h-4 mr-2" />
                  Restore Class
                </Button>
              </CardContent>
            </Card>
          ))}
        </div>

        {archivedClasses.length === 0 && (
          <Card>
            <CardContent className="p-8 text-center text-muted-foreground">
              No archived classes found
            </CardContent>
          </Card>
        )}
      </main>
    </div>
  );
}
