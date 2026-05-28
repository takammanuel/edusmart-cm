import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function BulletinManagement() {
  const [bulletins, setBulletins] = useState([]);
  const [students, setStudents] = useState([]);
  const [classrooms, setClassrooms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  // Filtres
  const [selectedClassroom, setSelectedClassroom] = useState('');
  const [selectedStudent, setSelectedStudent] = useState('');

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    fetchBulletins();
  }, [selectedClassroom, selectedStudent]);

  const fetchInitialData = async () => {
    try {
      const [classroomsData, studentsData] = await Promise.all([
        adminService.getClassrooms(),
        adminService.getStudents()
      ]);
      setClassrooms(classroomsData);
      setStudents(studentsData);
    } catch (err) {
      console.error("Erreur loading initial data:", err);
    }
  };

  const fetchBulletins = async () => {
    setLoading(true);
    try {
      const filters = {};
      if (selectedClassroom) filters.classroom_id = selectedClassroom;
      if (selectedStudent) filters.student_id = selectedStudent;

      const data = await adminService.getBulletins(filters);
      setBulletins(data);
      setError(null);
    } catch (err) {
      setError("Impossible de charger la liste des bulletins.");
    } finally {
      setLoading(false);
    }
  };

  const handleDownloadStudentBulletin = async (studentId) => {
    try {
      const blob = await adminService.downloadStudentBulletin(studentId);
      downloadFile(blob, `bulletin_etudiant_${studentId}.pdf`);
      triggerNotification("Téléchargement du bulletin en cours...");
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors du téléchargement du bulletin.");
    }
  };

  const handleDownloadClassroomBulletin = async (classroomId) => {
    try {
      const blob = await adminService.downloadClassroomBulletin(classroomId);
      downloadFile(blob, `bulletin_classe_${classroomId}.pdf`);
      triggerNotification("Téléchargement du bulletin de classe en cours...");
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors du téléchargement du bulletin.");
    }
  };

  const downloadFile = (blob, filename) => {
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  };

  const triggerNotification = (msg) => {
    setSuccessMessage(msg);
    setTimeout(() => setSuccessMessage(null), 4000);
  };

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* En-tête */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Gestion des Bulletins</h1>
          <p className="text-gray-600 mt-2">Consultez et téléchargez les bulletins scolaires</p>
        </div>

        {/* Messages */}
        {error && (
          <div className="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {error}
          </div>
        )}
        {successMessage && (
          <div className="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
            {successMessage}
          </div>
        )}

        {/* Filtres */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            {/* Filtre Classe */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Classe</label>
              <select
                value={selectedClassroom}
                onChange={(e) => setSelectedClassroom(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Toutes les classes</option>
                {classrooms.map(classroom => (
                  <option key={classroom.id} value={classroom.id}>
                    {classroom.name}
                  </option>
                ))}
              </select>
            </div>

            {/* Filtre Élève */}
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">Élève</label>
              <select
                value={selectedStudent}
                onChange={(e) => setSelectedStudent(e.target.value)}
                className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Tous les élèves</option>
                {students.map(student => (
                  <option key={student.id} value={student.id}>
                    {student.first_name} {student.last_name}
                  </option>
                ))}
              </select>
            </div>
          </div>
        </div>

        {/* Tableau des bulletins */}
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Chargement...</div>
          ) : bulletins.length === 0 ? (
            <div className="p-8 text-center text-gray-500">Aucun bulletin disponible</div>
          ) : (
            <table className="w-full">
              <thead className="bg-gray-100 border-b">
                <tr>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Élève</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Classe</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Moyenne</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Status</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                {bulletins.map((bulletin) => (
                  <tr key={bulletin.id} className="border-b hover:bg-gray-50 transition">
                    <td className="px-6 py-4 text-sm font-medium">
                      {bulletin.student?.first_name} {bulletin.student?.last_name}
                    </td>
                    <td className="px-6 py-4 text-sm">{bulletin.classroom?.name}</td>
                    <td className="px-6 py-4 text-center text-sm font-semibold">
                      {bulletin.average?.toFixed(2) || 'N/A'}
                    </td>
                    <td className="px-6 py-4 text-center text-sm">
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                        bulletin.status === 'passed' ? 'bg-green-100 text-green-800' :
                        bulletin.status === 'remedial' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                      }`}>
                        {bulletin.status === 'passed' ? 'Reçu' :
                         bulletin.status === 'remedial' ? 'Rattrapage' :
                         'Recalé'}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-center">
                      <button
                        onClick={() => handleDownloadStudentBulletin(bulletin.student_id)}
                        className="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 transition text-sm"
                        title="Télécharger le bulletin PDF"
                      >
                        📥 Télécharger
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>

        {/* Section Bulletins de Classe */}
        {classrooms.length > 0 && (
          <div className="mt-8">
            <div className="mb-8">
              <h2 className="text-2xl font-bold text-gray-900">Bulletins de Classe</h2>
              <p className="text-gray-600 mt-2">Téléchargez les bulletins complets par classe</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {classrooms.map(classroom => (
                <div key={classroom.id} className="bg-white rounded-lg shadow-md p-6 flex flex-col items-center">
                  <h3 className="text-lg font-semibold text-gray-900 mb-4">{classroom.name}</h3>
                  <p className="text-gray-600 text-sm mb-4">
                    {classroom.students_count || 0} élève(s)
                  </p>
                  <button
                    onClick={() => handleDownloadClassroomBulletin(classroom.id)}
                    className="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition font-medium w-full"
                  >
                    📋 Générer Bulletin
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
