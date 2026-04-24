import { useState } from 'react';
import { useNavigate } from 'react-router';
import { useAuth } from '../../context/AuthContext';
import { Header } from '../shared/Header';
import { Button } from '../ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../ui/card';
import { ArrowLeft, Database, RotateCcw, Download, AlertTriangle } from 'lucide-react';
import { toast } from 'sonner';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '../ui/alert-dialog';

export function SystemSettings() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [lastBackup, setLastBackup] = useState<string>('2026-04-20');

  const handleBackup = () => {
    const data = {
      activityLogs: localStorage.getItem('activityLogs'),
      examResults: localStorage.getItem('examResults'),
      timestamp: new Date().toISOString()
    };

    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `uphsd-backup-${new Date().toISOString().split('T')[0]}.json`;
    a.click();

    setLastBackup(new Date().toISOString().split('T')[0]);
    toast.success('Database backup created successfully!');

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: 'created database backup',
      timestamp: new Date().toISOString()
    };
    const logs = JSON.parse(localStorage.getItem('activityLogs') || '[]');
    logs.push(log);
    localStorage.setItem('activityLogs', JSON.stringify(logs));
  };

  const handleSemesterReset = () => {
    localStorage.setItem('activityLogs', '[]');
    localStorage.setItem('examResults', '[]');

    const log = {
      userId: user?.id,
      userName: user?.name,
      action: 'performed semester-wide reset',
      timestamp: new Date().toISOString()
    };
    const logs = [log];
    localStorage.setItem('activityLogs', JSON.stringify(logs));

    toast.success('Semester reset completed. All data cleared.');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Header title="UPHSD Test Bank - System Settings" />

      <main className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <Button onClick={() => navigate('/dashboard')} variant="ghost" className="mb-4">
          <ArrowLeft className="w-4 h-4 mr-2" />
          Back to Dashboard
        </Button>

        <div className="grid gap-6">
          <Card className="border-l-4 border-l-[#800000]">
            <CardHeader>
              <div className="flex items-center gap-3">
                <Database className="w-6 h-6 text-[#800000]" />
                <div>
                  <CardTitle>Database Backup</CardTitle>
                  <CardDescription>Create a backup of all system data</CardDescription>
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex justify-between items-center p-4 bg-gray-50 rounded-lg">
                <div>
                  <p className="text-sm">Last Backup</p>
                  <p className="text-lg">{new Date(lastBackup).toLocaleDateString()}</p>
                </div>
                <Button onClick={handleBackup} className="bg-[#800000] hover:bg-[#600000]">
                  <Download className="w-4 h-4 mr-2" />
                  Create Backup
                </Button>
              </div>
              <p className="text-sm text-muted-foreground">
                Regular backups ensure data safety. Download includes all activity logs, exam results, and system configurations.
              </p>
            </CardContent>
          </Card>

          <Card className="border-l-4 border-l-red-500">
            <CardHeader>
              <div className="flex items-center gap-3">
                <RotateCcw className="w-6 h-6 text-red-600" />
                <div>
                  <CardTitle className="text-red-600">Semester-Wide Reset</CardTitle>
                  <CardDescription>Reset all data for new semester</CardDescription>
                </div>
              </div>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
                <div className="flex items-start gap-2">
                  <AlertTriangle className="w-5 h-5 text-red-600 mt-0.5" />
                  <div className="text-sm text-red-800">
                    <p className="font-medium mb-1">Danger Zone</p>
                    <p>This action will permanently delete:</p>
                    <ul className="list-disc ml-5 mt-2 space-y-1">
                      <li>All activity logs</li>
                      <li>All exam results</li>
                      <li>Student enrollments</li>
                      <li>Faculty class assignments</li>
                    </ul>
                    <p className="mt-2 font-medium">This action cannot be undone. Create a backup first!</p>
                  </div>
                </div>
              </div>

              <AlertDialog>
                <AlertDialogTrigger asChild>
                  <Button variant="destructive" className="w-full">
                    <RotateCcw className="w-4 h-4 mr-2" />
                    Perform Semester Reset
                  </Button>
                </AlertDialogTrigger>
                <AlertDialogContent>
                  <AlertDialogHeader>
                    <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle>
                    <AlertDialogDescription>
                      This will permanently delete all system data. This action cannot be undone.
                      Make sure you have created a backup before proceeding.
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                      onClick={handleSemesterReset}
                      className="bg-red-600 hover:bg-red-700"
                    >
                      Yes, Reset Everything
                    </AlertDialogAction>
                  </AlertDialogFooter>
                </AlertDialogContent>
              </AlertDialog>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>System Information</CardTitle>
            </CardHeader>
            <CardContent className="space-y-2">
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Version:</span>
                <span>1.0.0</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Environment:</span>
                <span>Production</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Database:</span>
                <span>LocalStorage</span>
              </div>
              <div className="flex justify-between p-3 bg-gray-50 rounded">
                <span className="text-muted-foreground">Last Maintenance:</span>
                <span>{new Date().toLocaleDateString()}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  );
}
