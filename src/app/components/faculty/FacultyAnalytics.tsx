import { useState, useEffect } from 'react';
import { useAuth } from '../../context/AuthContext';
import { useNavigate } from 'react-router';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { ArrowLeft, BookOpen, Users, FileQuestion, TrendingUp } from 'lucide-react';

export function FacultyAnalytics() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [stats, setStats] = useState({
    totalClasses: 0,
    totalCourses: 0,
    totalQuestions: 0,
    totalStudents: 0,
    averageScore: 0,
    recentActivity: 0
  });

  useEffect(() => {
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    const userLogs = logs.filter((log: any) => log.userId === user?.id);

    const results = JSON.parse(localStorage.getItem('examResults') || '[]');
    const avgScore = results.length > 0
      ? results.reduce((acc: number, r: any) => acc + parseFloat(r.percentage), 0) / results.length
      : 0;

    setStats({
      totalClasses: 3,
      totalCourses: 5,
      totalQuestions: 45,
      totalStudents: 78,
      averageScore: avgScore,
      recentActivity: userLogs.length
    });
  }, [user]);

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - Faculty Analytics" />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button onClick={() => navigate('/dashboard')} variant="ghost" className="mb-4">
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Dashboard
        </Button>

        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-8">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Classes</CardTitle>
              <BookOpen className="h-4 w-4 text-[#800000]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalClasses}</div>
              <p className="text-xs text-muted-foreground">Active classes</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Courses</CardTitle>
              <FileQuestion className="h-4 w-4 text-[#FF8C00]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalCourses}</div>
              <p className="text-xs text-muted-foreground">Course modules</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Question Bank</CardTitle>
              <FileQuestion className="h-4 w-4 text-[#800000]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalQuestions}</div>
              <p className="text-xs text-muted-foreground">Questions created</p>
            </CardContent>
          </Card>

          <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
              <CardTitle className="text-sm">Total Students</CardTitle>
              <Users className="h-4 w-4 text-[#FF8C00]" />
            </CardHeader>
            <CardContent>
              <div className="text-2xl">{stats.totalStudents}</div>
              <p className="text-xs text-muted-foreground">Enrolled students</p>
            </CardContent>
          </Card>
        </div>

        <div className="grid gap-4 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Performance Overview</CardTitle>
              <CardDescription>Student performance metrics</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-4">
                <div>
                  <div className="flex justify-between mb-1">
                    <span className="text-sm">Average Score</span>
                    <span className="text-sm">{stats.averageScore.toFixed(1)}%</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div
                      className="bg-[#800000] h-2 rounded-full"
                      style={{ width: `${stats.averageScore}%` }}
                    ></div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Activity Summary</CardTitle>
              <CardDescription>Your recent activity</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="flex items-center gap-4">
                <div className="p-3 bg-[#800000]/10 rounded-lg">
                  <TrendingUp className="w-8 h-8 text-[#800000]" />
                </div>
                <div>
                  <p className="text-2xl">{stats.recentActivity}</p>
                  <p className="text-sm text-muted-foreground">Total actions logged</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
}
