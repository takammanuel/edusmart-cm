import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function DashboardController() {
  const [stats, setStats] = useState(null);
  const [absenceStats, setAbsenceStats] = useState(null);
  const [gradeStats, setGradeStats] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    fetchDashboardData();
  }, []);

  const fetchDashboardData = async () => {
    setLoading(true);
    try {
      const [stats, absenceStats, gradeStats] = await Promise.all([
        adminService.getDashboardStats(),
        adminService.getAbsenceStats(),
        adminService.getGradeStats()
      ]);

      setStats(stats);
      setAbsenceStats(absenceStats);
      setGradeStats(gradeStats);
      setError(null);
    } catch (err) {
      console.error("Erreur loading dashboard:", err);
      setError("Impossible de charger le tableau de bord");
    } finally {
      setLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 p-6 flex items-center justify-center">
        <div className="text-gray-500">Chargement du tableau de bord...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="min-h-screen bg-gray-50 p-6">
        <div className="max-w-7xl mx-auto">
          <div className="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
            {error}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* En-tête */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Tableau de Bord de Pilotage</h1>
          <p className="text-gray-600 mt-2">Vue d'ensemble des statistiques de l'établissement</p>
        </div>

        {/* KPIs Principaux */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          {/* Total Élèves */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm font-medium">Élèves Inscrits</p>
                <p className="text-3xl font-bold text-blue-600 mt-2">{stats?.total_students || 0}</p>
              </div>
              <div className="bg-blue-100 p-3 rounded-lg">
                <svg className="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10.5 1.5H3.75A2.25 2.25 0 001.5 3.75v12.5A2.25 2.25 0 003.75 18.5h12.5a2.25 2.25 0 002.25-2.25V9.5m-15-4h15m-15 4v10" stroke="currentColor" strokeWidth="2" fill="none"/>
                </svg>
              </div>
            </div>
          </div>

          {/* Total Absences */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm font-medium">Absences Totales</p>
                <p className="text-3xl font-bold text-red-600 mt-2">{absenceStats?.total_absences || 0}</p>
              </div>
              <div className="bg-red-100 p-3 rounded-lg">
                <svg className="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M10 1a9 9 0 100 18 9 9 0 000-18zm0 2a7 7 0 110 14 7 7 0 010-14z"/>
                </svg>
              </div>
            </div>
          </div>

          {/* Absences Non Justifiées */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm font-medium">Non Justifiées</p>
                <p className="text-3xl font-bold text-yellow-600 mt-2">{absenceStats?.unjustified_absences || 0}</p>
              </div>
              <div className="bg-yellow-100 p-3 rounded-lg">
                <svg className="w-6 h-6 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"/>
                </svg>
              </div>
            </div>
          </div>

          {/* Moyenne Générale */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-gray-600 text-sm font-medium">Moyenne Générale</p>
                <p className="text-3xl font-bold text-green-600 mt-2">{gradeStats?.average_grade?.toFixed(2) || '0.00'}</p>
              </div>
              <div className="bg-green-100 p-3 rounded-lg">
                <svg className="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fillRule="evenodd" d="M12 7a1 1 0 110-2 1 1 0 010 2zM9 9a1 1 0 11-2 0 1 1 0 012 0zm6 0a1 1 0 11-2 0 1 1 0 012 0zM9 15a1 1 0 11-2 0 1 1 0 012 0zm6 0a1 1 0 11-2 0 1 1 0 012 0z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        {/* Statistiques Détaillées */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          {/* Statistiques Absences */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Statistiques des Absences</h2>
            <div className="space-y-4">
              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm text-gray-600">Absences Justifiées</span>
                  <span className="font-semibold text-green-600">{absenceStats?.justified_absences || 0}</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-green-600 h-2 rounded-full" 
                    style={{width: `${((absenceStats?.justified_absences || 0) / (absenceStats?.total_absences || 1) * 100)}%`}}
                  ></div>
                </div>
              </div>

              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm text-gray-600">Absences Non Justifiées</span>
                  <span className="font-semibold text-red-600">{absenceStats?.unjustified_absences || 0}</span>
                </div>
                <div className="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    className="bg-red-600 h-2 rounded-full" 
                    style={{width: `${((absenceStats?.unjustified_absences || 0) / (absenceStats?.total_absences || 1) * 100)}%`}}
                  ></div>
                </div>
              </div>

              <div className="pt-4 border-t">
                <p className="text-sm text-gray-600">
                  Taux d'absences: <span className="font-semibold">{absenceStats?.absence_rate?.toFixed(2) || '0'}%</span>
                </p>
              </div>
            </div>
          </div>

          {/* Statistiques Résultats */}
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Statistiques des Résultats</h2>
            <div className="space-y-4">
              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm text-gray-600">Élèves Reçus</span>
                  <span className="font-semibold text-green-600">{gradeStats?.passed_students || 0}</span>
                </div>
              </div>

              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm text-gray-600">Élèves en Rattrapage</span>
                  <span className="font-semibold text-yellow-600">{gradeStats?.remedial_students || 0}</span>
                </div>
              </div>

              <div>
                <div className="flex justify-between mb-2">
                  <span className="text-sm text-gray-600">Élèves Recalés</span>
                  <span className="font-semibold text-red-600">{gradeStats?.failed_students || 0}</span>
                </div>
              </div>

              <div className="pt-4 border-t">
                <p className="text-sm text-gray-600">
                  Taux de Réussite: <span className="font-semibold">{gradeStats?.success_rate?.toFixed(2) || '0'}%</span>
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Répartition par Classe */}
        {stats?.students_by_classroom && (
          <div className="bg-white rounded-lg shadow-md p-6">
            <h2 className="text-xl font-bold text-gray-900 mb-4">Répartition des Élèves par Classe</h2>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-100 border-b">
                  <tr>
                    <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Classe</th>
                    <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Nombre d'Élèves</th>
                    <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Pourcentage</th>
                  </tr>
                </thead>
                <tbody>
                  {stats.students_by_classroom.map((item) => (
                    <tr key={item.classroom_id} className="border-b hover:bg-gray-50">
                      <td className="px-6 py-4 text-sm font-medium text-gray-900">{item.classroom_name}</td>
                      <td className="px-6 py-4 text-center text-sm">{item.count}</td>
                      <td className="px-6 py-4 text-center text-sm">
                        {((item.count / (stats?.total_students || 1)) * 100).toFixed(1)}%
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
