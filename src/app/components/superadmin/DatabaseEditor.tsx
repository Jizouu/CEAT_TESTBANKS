import { useState } from 'react';
import { useNavigate } from 'react-router';
import { useAuth } from '../../context/AuthContext';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../ui/tabs';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../ui/table';
import { ArrowLeft, Database, Trash2, RefreshCcw } from 'lucide-react';
import { toast } from 'sonner';

export function DatabaseEditor() {
  const { user } = useAuth();
  const navigate = useNavigate();

  const [activityLogs, setActivityLogs] = useState(() => {
    return JSON.parse(localStorage.getItem('activityLogs') || '[]');
  });

  const [examResults, setExamResults] = useState(() => {
    return JSON.parse(localStorage.getItem('examResults') || '[]');
  });

  const refreshData = () => {
    setActivityLogs(JSON.parse(localStorage.getItem('activityLogs') || '[]'));
    setExamResults(JSON.parse(localStorage.getItem('examResults') || '[]'));
    toast.success('Data refreshed');
  };

  const clearTable = (table: 'logs' | 'results') => {
    if (table === 'logs') {
      localStorage.setItem('activityLogs', '[]');
      setActivityLogs([]);
      toast.success('Activity logs cleared');
    } else {
      localStorage.setItem('examResults', '[]');
      setExamResults([]);
      toast.success('Exam results cleared');
    }

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: `cleared ${table} table`,
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));
  };

  const deleteLogEntry = (index: number) => {
    const newLogs = activityLogs.filter((_: any, i: number) => i !== index);
    setActivityLogs(newLogs);
    localStorage.setItem('activityLogs', JSON.stringify(newLogs));
    toast.success('Log entry deleted');
  };

  const deleteResultEntry = (index: number) => {
    const newResults = examResults.filter((_: any, i: number) => i !== index);
    setExamResults(newResults);
    localStorage.setItem('examResults', JSON.stringify(newResults));
    toast.success('Result entry deleted');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Database Editor" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div className="flex items-center justify-between mb-4">
          <Button onClick={() => navigate('/dashboard')} variant="ghost">
            <ArrowLeft className="w-4 h-4 mr-2" />
            Back to Dashboard
          </Button>
          <Button onClick={refreshData} variant="outline">
            <RefreshCcw className="w-4 h-4 mr-2" />
            Refresh Data
          </Button>
        </div>

        <Tabs defaultValue="logs" className="w-full">
          <TabsList className="mb-6">
            <TabsTrigger value="logs">Activity Logs Table</TabsTrigger>
            <TabsTrigger value="results">Exam Results Table</TabsTrigger>
          </TabsList>

          <TabsContent value="logs">
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle>Activity Logs Table</CardTitle>
                    <CardDescription>{activityLogs.length} record{activityLogs.length !== 1 ? 's' : ''}</CardDescription>
                  </div>
                  <Button
                    onClick={() => clearTable('logs')}
                    variant="destructive"
                    size="sm"
                  >
                    <Trash2 className="w-4 h-4 mr-2" />
                    Clear All
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="overflow-auto max-h-[600px]">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Index</TableHead>
                        <TableHead>User ID</TableHead>
                        <TableHead>User Name</TableHead>
                        <TableHead>Action</TableHead>
                        <TableHead>Timestamp</TableHead>
                        <TableHead className="text-right">Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {activityLogs.length === 0 ? (
                        <TableRow>
                          <TableCell colSpan={6} className="text-center text-muted-foreground">
                            No activity logs in database
                          </TableCell>
                        </TableRow>
                      ) : (
                        activityLogs.map((log: any, index: number) => (
                          <TableRow key={index}>
                            <TableCell>{index}</TableCell>
                            <TableCell>{log.userId}</TableCell>
                            <TableCell>{log.userName}</TableCell>
                            <TableCell>{log.action}</TableCell>
                            <TableCell>{new Date(log.timestamp).toLocaleString()}</TableCell>
                            <TableCell className="text-right">
                              <Button
                                onClick={() => deleteLogEntry(index)}
                                variant="ghost"
                                size="sm"
                                className="text-destructive hover:text-destructive"
                              >
                                <Trash2 className="w-4 h-4" />
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))
                      )}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>

          <TabsContent value="results">
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle>Exam Results Table</CardTitle>
                    <CardDescription>{examResults.length} record{examResults.length !== 1 ? 's' : ''}</CardDescription>
                  </div>
                  <Button
                    onClick={() => clearTable('results')}
                    variant="destructive"
                    size="sm"
                  >
                    <Trash2 className="w-4 h-4 mr-2" />
                    Clear All
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <div className="overflow-auto max-h-[600px]">
                  <Table>
                    <TableHeader>
                      <TableRow>
                        <TableHead>Index</TableHead>
                        <TableHead>Exam ID</TableHead>
                        <TableHead>Student</TableHead>
                        <TableHead>Course</TableHead>
                        <TableHead>Score</TableHead>
                        <TableHead>Percentage</TableHead>
                        <TableHead>Violations</TableHead>
                        <TableHead>Submitted</TableHead>
                        <TableHead className="text-right">Actions</TableHead>
                      </TableRow>
                    </TableHeader>
                    <TableBody>
                      {examResults.length === 0 ? (
                        <TableRow>
                          <TableCell colSpan={9} className="text-center text-muted-foreground">
                            No exam results in database
                          </TableCell>
                        </TableRow>
                      ) : (
                        examResults.map((result: any, index: number) => (
                          <TableRow key={index}>
                            <TableCell>{index}</TableCell>
                            <TableCell>{result.examId}</TableCell>
                            <TableCell>{result.studentName}</TableCell>
                            <TableCell>{result.courseCode}</TableCell>
                            <TableCell>{result.score}/{result.totalQuestions}</TableCell>
                            <TableCell>{result.percentage}%</TableCell>
                            <TableCell>{result.cheatAttempts}</TableCell>
                            <TableCell>{new Date(result.submittedAt).toLocaleDateString()}</TableCell>
                            <TableCell className="text-right">
                              <Button
                                onClick={() => deleteResultEntry(index)}
                                variant="ghost"
                                size="sm"
                                className="text-destructive hover:text-destructive"
                              >
                                <Trash2 className="w-4 h-4" />
                              </Button>
                            </TableCell>
                          </TableRow>
                        ))
                      )}
                    </TableBody>
                  </Table>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
      </main>
    </div>
  );
}
