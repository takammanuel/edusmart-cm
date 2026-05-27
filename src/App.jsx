import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';

// Importations des composants réels
import LandingPage from './pages/landing/LandingPage';
import LoginPage from './pages/auth/LoginPage';
import AdminLayout from './layouts/AdminLayout';
import AdminDashboard from './pages/admin/AdminDashboard';
import StudentManagement from './pages/admin/StudentManagement';
import TeacherManagement from './pages/admin/TeacherManagement';
import ClassroomManagement from './pages/admin/ClassroomManagement'; // Nouvelle importation

function ProtectedRoute({ children, allowedRoles }) {
  const { user, isAuthenticated } = useAuth();

  if (!isAuthenticated) {
    return <Navigate to="/login" replace />;
  }

  if (allowedRoles && user && !allowedRoles.includes(user.role)) {
    return <Navigate to={user.role === 'enseignant' ? '/teacher/grades' : '/'} replace />;
  }

  return children;
}

function PublicRoute({ children }) {
  const { user, isAuthenticated } = useAuth();

  if (isAuthenticated) {
    if (user?.role === 'admin') {
      return <Navigate to="/admin/dashboard" replace />;
    }
    if (user?.role === 'enseignant') {
      return <Navigate to="/teacher/grades" replace />;
    }
  }

  return children;
}

export default function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          {/* ---- ROUTES PUBLIQUES ---- */}
          <Route path="/" element={<PublicRoute><LandingPage /></PublicRoute>} />
          <Route path="/login" element={<PublicRoute><LoginPage /></PublicRoute>} />

          {/* ---- ESPACE PRIVÉ : ADMINISTRATION ---- */}
          <Route 
            path="/admin" 
            element={
              <ProtectedRoute allowedRoles={['admin']}>
                <AdminLayout />
              </ProtectedRoute>
            }
          >
            <Route index element={<Navigate to="/admin/dashboard" replace />} />
            <Route path="dashboard" element={<AdminDashboard />} />
            <Route path="students" element={<StudentManagement />} />
            <Route path="teachers" element={<TeacherManagement />} />
            <Route path="classrooms" element={<ClassroomManagement />} /> {/* Nouvelle Route de gestion des classes */}
          </Route>

          {/* ---- ESPACE PRIVÉ : ENSEIGNANT ---- */}
          <Route 
            path="/teacher" 
            element={
              <ProtectedRoute allowedRoles={['enseignant']}>
                <AdminLayout /> 
              </ProtectedRoute>
            }
          >
            <Route index element={<Navigate to="/teacher/grades" replace />} />
            <Route path="grades" element={<div className="bg-white p-6 rounded-xl border border-slate-100 text-slate-800">Saisie des Notes — Espace Enseignant</div>} />
          </Route>

          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </Router>
    </AuthProvider>
  );
}