import { useEffect, useState } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useNavigate, useSearchParams } from 'react-router';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { CheckCircle, XCircle, AlertTriangle, Home } from 'lucide-react';

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

export function ViewResult() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const examId = searchParams.get('id');
  const [result, setResult] = useState<ExamResult | null>(null);

  useEffect(() => {
    const results = JSON.parse(localStorage.getItem('examResults') || '[]');
    const foundResult = results.find(
      (r: ExamResult) => r.examId === examId && r.studentId === user?.id
    );
    setResult(foundResult || null);
  }, [examId, user]);

  if (!result) {
    return (
      <div className="min-h-screen bg-gray-50">
        <Header title="UPHSD Test Bank - Results" />
        <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
          <Card>
            <CardContent className="p-8 text-center">
              <p className="text-muted-foreground">No result found for this exam.</p>
              <Button onClick={() => navigate('/dashboard')} className="mt-4">
                Return to Dashboard
              </Button>
            </CardContent>
          </Card>
        </main>
      </div>
    );
  }

  const percentage = parseFloat(result.percentage);
  const isPassing = percentage >= 60;

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Exam Results" />

      <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Card className={`border-t-4 ${isPassing ? 'border-t-green-500' : 'border-t-red-500'}`}>
          <CardHeader className="text-center">
            <div className={`mx-auto w-20 h-20 rounded-full flex items-center justify-center mb-4 ${
              isPassing ? 'bg-green-100' : 'bg-red-100'
            }`}>
              {isPassing ? (
                <CheckCircle className="w-12 h-12 text-green-600" />
              ) : (
                <XCircle className="w-12 h-12 text-red-600" />
              )}
            </div>
            <CardTitle className="text-3xl">
              {isPassing ? 'Congratulations!' : 'Result'}
            </CardTitle>
            <CardDescription>
              Exam completed on {new Date(result.submittedAt).toLocaleString()}
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-6">
            <div className="grid grid-cols-2 gap-4">
              <Card>
                <CardContent className="p-6 text-center">
                  <p className="text-sm text-muted-foreground mb-2">Score</p>
                  <p className="text-4xl">{result.score}/{result.totalQuestions}</p>
                </CardContent>
              </Card>
              <Card>
                <CardContent className="p-6 text-center">
                  <p className="text-sm text-muted-foreground mb-2">Percentage</p>
                  <p className={`text-4xl ${isPassing ? 'text-green-600' : 'text-red-600'}`}>
                    {result.percentage}%
                  </p>
                </CardContent>
              </Card>
            </div>

            <div className="space-y-2">
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Course Code:</span>
                <span>{result.courseCode}</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Student Name:</span>
                <span>{result.studentName}</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Total Questions:</span>
                <span>{result.totalQuestions}</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Correct Answers:</span>
                <span>{result.score}</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Incorrect Answers:</span>
                <span>{result.totalQuestions - result.score}</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Status:</span>
                <span className={isPassing ? 'text-green-600' : 'text-red-600'}>
                  {isPassing ? 'PASSED' : 'FAILED'}
                </span>
              </div>
            </div>

            {result.cheatAttempts > 0 && (
              <Card className="border-yellow-500 bg-yellow-50">
                <CardContent className="p-4">
                  <div className="flex items-start gap-2">
                    <AlertTriangle className="w-5 h-5 text-yellow-600 mt-0.5" />
                    <div className="text-sm text-yellow-800">
                      <p className="font-medium mb-1">Integrity Notice</p>
                      <p>{result.cheatAttempts} violation{result.cheatAttempts > 1 ? 's were' : ' was'} detected during this exam. This has been reported to your instructor.</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            )}

            <Button
              onClick={() => navigate('/dashboard')}
              className="w-full bg-[#800000] hover:bg-[#600000]"
            >
              <Home className="w-4 h-4 mr-2" />
              Return to Dashboard
            </Button>
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
