import { createBrowserRouter, Navigate } from 'react-router';
import { LoginPage } from './components/LoginPage';
import { StudentDashboard } from './components/StudentDashboard';
import { FacultyDashboard } from './components/FacultyDashboard';
import { AdminDashboard } from './components/AdminDashboard';
import { SuperAdminDashboard } from './components/SuperAdminDashboard';
import { ChangePassword } from './components/shared/ChangePassword';
import { Exam } from './components/student/Exam';
import { ViewResult } from './components/student/ViewResult';
import { ViewStudents } from './components/faculty/ViewStudents';
import { ViewStudentResults } from './components/faculty/ViewStudentResults';
import { ArchivedClasses } from './components/faculty/ArchivedClasses';
import { FacultyAnalytics } from './components/faculty/FacultyAnalytics';
import { ManagePrograms } from './components/admin/ManagePrograms';
import { ManageCourses } from './components/admin/ManageCourses';
import { ManageUsers } from './components/admin/ManageUsers';
import { SystemSettings } from './components/superadmin/SystemSettings';
import { DatabaseEditor } from './components/superadmin/DatabaseEditor';
import { useAuth } from './context/AuthContext';

function DashboardRouter() {
  const { user } = useAuth();

  if (!user) {
    return <Navigate to="/" replace />;
  }

  switch (user.role) {
    case 'student':
      return <StudentDashboard />;
    case 'faculty':
      return <FacultyDashboard />;
    case 'admin':
      return <AdminDashboard />;
    case 'superadmin':
      return <SuperAdminDashboard />;
    default:
      return <Navigate to="/" replace />;
  }
}

function ProtectedRoute() {
  const { user } = useAuth();

  if (user) {
    return <Navigate to="/dashboard" replace />;
  }

  return <LoginPage />;
}

export const router = createBrowserRouter([
  {
    path: '/',
    element: <ProtectedRoute />
  },
  {
    path: '/dashboard',
    element: <DashboardRouter />
  },
  {
    path: '/change-password',
    element: <ChangePassword />
  },
  {
    path: '/exam',
    element: <Exam />
  },
  {
    path: '/view-result',
    element: <ViewResult />
  },
  {
    path: '/view-students',
    element: <ViewStudents />
  },
  {
    path: '/view-student-results',
    element: <ViewStudentResults />
  },
  {
    path: '/archived-classes',
    element: <ArchivedClasses />
  },
  {
    path: '/faculty-analytics',
    element: <FacultyAnalytics />
  },
  {
    path: '/manage-programs',
    element: <ManagePrograms />
  },
  {
    path: '/manage-courses',
    element: <ManageCourses />
  },
  {
    path: '/manage-users',
    element: <ManageUsers />
  },
  {
    path: '/system-settings',
    element: <SystemSettings />
  },
  {
    path: '/database-editor',
    element: <DatabaseEditor />
  },
  {
    path: '*',
    element: <Navigate to="/" replace />
  }
]);
