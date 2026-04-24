import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import { ArrowLeft, Download, AlertTriangle } from 'lucide-react';

interface ExamResult {
  examId: string;
  courseCode: string;
  studentId: string;
  studentName: string;
  score: number;
  totalQuestions: number;
  percentage: string;
  cheatAttempts: number;
  submittedAt: string;
}

export function ViewStudentResults() {
  const navigate = useNavigate();
  const [results, setResults] = useState<ExamResult[]>([]);

  useEffect(() => {
    const storedResults = JSON.parse(localStorage.getItem('examResults') || '[]');
    setResults(storedResults);
  }, []);

  const exportToCSV = () => {
    const headers = ['Student Name', 'Course', 'Score', 'Total', 'Percentage', 'Status', 'Violations', 'Date'];
    const rows = results.map(r => [
      r.studentName,
      r.courseCode,
      r.score,
      r.totalQuestions,
      r.percentage + '%',
      parseFloat(r.percentage) >= 60 ? 'PASSED' : 'FAILED',
      r.cheatAttempts,
      new Date(r.submittedAt).toLocaleDateString()
    ]);

    const csv = [headers, ...rows].map(row => row.join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'student_results.csv';
    a.click();
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Student Results" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-4">
          <Button onClick={() => navigate('/dashboard')} variant="ghost">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Dashboard
          </Button>
          <Button onClick={exportToCSV} className="bg-[#FF8C00] hover:bg-[#E67E00]">
            <Download className="w-4 h-4 mr-2" />
            Export to CSV
          </Button>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Student Exam Results</CardTitle>
            <CardDescription>{results.length} exam{results.length !== 1 ? 's' : ''} submitted</CardDescription>
          </CardHeader>
          <CardContent>
            {results.length === 0 ? (
              <div className="text-center py-8 text-muted-foreground">
                No exam results available yet
              </div>
            ) : (
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Student Name</TableHead>
                    <TableHead>Course</TableHead>
                    <TableHead>Score</TableHead>
                    <TableHead>Percentage</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Violations</TableHead>
                    <TableHead>Submitted</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {results.map((result, index) => (
                    <TableRow key={index}>
                      <TableCell>{result.studentName}</TableCell>
                      <TableCell>{result.courseCode}</TableCell>
                      <TableCell>{result.score}/{result.totalQuestions}</TableCell>
                      <TableCell>{result.percentage}%</TableCell>
                      <TableCell>
                        <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs ${
                          parseFloat(result.percentage) >= 60
                            ? 'bg-green-100 text-green-800'
                            : 'bg-red-100 text-red-800'
                        }`}>
                          {parseFloat(result.percentage) >= 60 ? 'PASSED' : 'FAILED'}
                        </span>
                      </TableCell>
                      <TableCell>
                        {result.cheatAttempts > 0 ? (
                          <div className="flex items-center gap-1 text-yellow-600">
                            <AlertTriangle className="w-4 h-4" />
                            {result.cheatAttempts}
                          </div>
                        ) : (
                          <span className="text-green-600">None</span>
                        )}
                      </TableCell>
                      <TableCell>{new Date(result.submittedAt).toLocaleDateString()}</TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
