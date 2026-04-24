import { useState, useEffect, useCallback } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useNavigate, useSearchParams } from 'react-router';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '../ui/card';
import { Progress } from '../ui/progress';
import { AlertCircle, Clock } from 'lucide-react';
import { toast } from 'sonner';

interface Question {
  id: string;
  question: string;
  options: string[];
  correctAnswer: number;
}

export function Exam() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const examId = searchParams.get('id');
  const courseCode = searchParams.get('course') || 'CS101';

  const [questions] = useState<Question[]>([
    {
      id: '1',
      question: 'What is the main purpose of a database management system?',
      options: [
        'To store and manage data efficiently',
        'To create websites',
        'To design graphics',
        'To write code'
      ],
      correctAnswer: 0
    },
    {
      id: '2',
      question: 'Which programming paradigm does Python support?',
      options: [
        'Only procedural',
        'Only object-oriented',
        'Multiple paradigms',
        'Only functional'
      ],
      correctAnswer: 2
    },
    {
      id: '3',
      question: 'What does HTML stand for?',
      options: [
        'Hyper Text Markup Language',
        'High Tech Modern Language',
        'Home Tool Markup Language',
        'Hyperlinks and Text Markup Language'
      ],
      correctAnswer: 0
    }
  ]);

  const [currentQuestion, setCurrentQuestion] = useState(0);
  const [answers, setAnswers] = useState<Record<string, number>>({});
  const [timeRemaining, setTimeRemaining] = useState(1800);
  const [cheatAttempts, setCheatAttempts] = useState(0);
  const [isExamSubmitted, setIsExamSubmitted] = useState(false);

  const logCheatAttempt = useCallback((type: string) => {
    const newAttempts = cheatAttempts + 1;
    setCheatAttempts(newAttempts);

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `cheat attempt detected: ${type}`,
      timestamp: new Date().toISOString(),
      examId,
      attemptNumber: newAttempts
    };

    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));

    toast.error(`Warning: Suspicious activity detected (${type}). Attempt ${newAttempts} logged.`);

    if (newAttempts >= 3) {
      toast.error('Too many violations. Exam will be auto-submitted.');
      setTimeout(() => handleSubmit(), 2000);
    }
  }, [cheatAttempts, user, examId]);

  useEffect(() => {
    const handleVisibilityChange = () => {
      if (document.hidden && !isExamSubmitted) {
        logCheatAttempt('tab switch');
      }
    };

    const handleBlur = () => {
      if (!isExamSubmitted) {
        logCheatAttempt('window blur');
      }
    };

    const handleCopy = (e: ClipboardEvent) => {
      e.preventDefault();
      logCheatAttempt('copy attempt');
    };

    const handleContextMenu = (e: MouseEvent) => {
      e.preventDefault();
      logCheatAttempt('right click');
    };

    document.addEventListener('visibilitychange', handleVisibilityChange);
    window.addEventListener('blur', handleBlur);
    document.addEventListener('copy', handleCopy);
    document.addEventListener('contextmenu', handleContextMenu);

    return () => {
      document.removeEventListener('visibilitychange', handleVisibilityChange);
      window.removeEventListener('blur', handleBlur);
      document.removeEventListener('copy', handleCopy);
      document.removeEventListener('contextmenu', handleContextMenu);
    };
  }, [logCheatAttempt, isExamSubmitted]);

  useEffect(() => {
    if (timeRemaining <= 0) {
      handleSubmit();
      return;
    }

    const timer = setInterval(() => {
      setTimeRemaining((prev) => prev - 1);
    }, 1000);

    return () => clearInterval(timer);
  }, [timeRemaining]);

  const handleAnswerSelect = (questionId: string, answerIndex: number) => {
    setAnswers({ ...answers, [questionId]: answerIndex });
  };

  const handleSubmit = () => {
    setIsExamSubmitted(true);

    let score = 0;
    questions.forEach((q) => {
      if (answers[q.id] === q.correctAnswer) {
        score++;
      }
    });

    const percentage = (score / questions.length) * 100;

    const result = {
      examId: examId || Date.now().toString(),
      courseCode,
      studentId: user?.id,
      studentName: user?.name,
      score,
      totalQuestions: questions.length,
      percentage: percentage.toFixed(2),
      cheatAttempts,
      submittedAt: new Date().toISOString()
    };

    const results = JSON.parse(localStorage.getItem('examResults') || '[]');
    results.push(result);
    localStorage.setItem('examResults', JSON.stringify(results));

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `submitted exam ${courseCode}`,
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));

    navigate(`/view-result?id=${examId}`);
  };

  const formatTime = (seconds: number) => {
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
  };

  const progress = ((currentQuestion + 1) / questions.length) * 100;

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Exam" showChangePassword={false} />

      <main className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="mb-6 flex items-center justify-between">
          <div>
            <h2>{courseCode} Examination</h2>
            <p className="text-muted-foreground">Question {currentQuestion + 1} of {questions.length}</p>
          </div>
          <div className="flex items-center gap-6">
            {cheatAttempts > 0 && (
              <div className="flex items-center gap-2 text-red-600">
                <AlertCircle className="w-5 h-5" />
                <span>{cheatAttempts} violation{cheatAttempts > 1 ? 's' : ''}</span>
              </div>
            )}
            <div className="flex items-center gap-2 bg-[#800000] text-white px-4 py-2 rounded-lg">
              <Clock className="w-5 h-5" />
              <span>{formatTime(timeRemaining)}</span>
            </div>
          </div>
        </div>

        <Progress value={progress} className="mb-6" />

        <Card>
          <CardHeader>
            <CardTitle>Question {currentQuestion + 1}</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <p className="text-lg">{questions[currentQuestion].question}</p>

            <div className="space-y-3">
              {questions[currentQuestion].options.map((option, index) => (
                <button
                  key={index}
                  onClick={() => handleAnswerSelect(questions[currentQuestion].id, index)}
                  className={`w-full text-left p-4 rounded-lg border-2 transition-all ${
                    answers[questions[currentQuestion].id] === index
                      ? 'border-[#800000] bg-[#800000]/10'
                      : 'border-gray-200 hover:border-[#FF8C00]'
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <div className={`w-6 h-6 rounded-full border-2 flex items-center justify-center ${
                      answers[questions[currentQuestion].id] === index
                        ? 'border-[#800000] bg-[#800000]'
                        : 'border-gray-300'
                    }`}>
                      {answers[questions[currentQuestion].id] === index && (
                        <div className="w-3 h-3 bg-white rounded-full"></div>
                      )}
                    </div>
                    <span>{option}</span>
                  </div>
                </button>
              ))}
            </div>

            <div className="flex justify-between pt-4">
              <Button
                onClick={() => setCurrentQuestion(Math.max(0, currentQuestion - 1))}
                disabled={currentQuestion === 0}
                variant="outline"
              >
                Previous
              </Button>

              {currentQuestion === questions.length - 1 ? (
                <Button
                  onClick={handleSubmit}
                  className="bg-[#800000] hover:bg-[#600000]"
                  disabled={Object.keys(answers).length !== questions.length}
                >
                  Submit Exam
                </Button>
              ) : (
                <Button
                  onClick={() => setCurrentQuestion(Math.min(questions.length - 1, currentQuestion + 1))}
                  className="bg-[#FF8C00] hover:bg-[#E67E00]"
                >
                  Next
                </Button>
              )}
            </div>
          </CardContent>
        </Card>

        <Card className="mt-6 border-yellow-500 bg-yellow-50">
          <CardContent className="p-4">
            <div className="flex items-start gap-2">
              <AlertCircle className="w-5 h-5 text-yellow-600 mt-0.5" />
              <div className="text-sm text-yellow-800">
                <p className="font-medium mb-1">Exam Monitoring Active</p>
                <p>Do not switch tabs, copy text, or right-click during the exam. Violations will be logged and may result in automatic submission.</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </main>
    </div>
  );
}
