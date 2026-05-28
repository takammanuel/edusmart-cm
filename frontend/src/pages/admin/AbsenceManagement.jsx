import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function AbsenceManagement() {
  const [absences, setAbsences] = useState([]);
  const [students, setStudents] = useState([]);
  const [classrooms, setClassrooms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  // Filtres
  const [search, setSearch] = useState('');
  const [selectedClassroom, setSelectedClassroom] = useState('');
  const [selectedStudent, setSelectedStudent] = useState('');

  // Gestion des Modales
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [currentAbsence, setCurrentAbsence] = useState(null);

  // Formulaire d'absence
  const [formData, setFormData] = useState({
    student_id: '',
    classroom_id: '',
    date: '',
    hours: 1,
    is_justified: false,
    reason: ''
  });

  // Charger les données initiales
  useEffect(() => {
    fetchClassrooms();
    fetchStudents();
  }, []);

  // Recharger les absences quand les filtres changent
  useEffect(() => {
    fetchAbsences();
  }, [search, selectedClassroom, selectedStudent]);

  const fetchClassrooms = async () => {
    try {
      const data = await adminService.getClassrooms();
      setClassrooms(data);
    } catch (err) {
      console.error("Erreur classes:", err);
    }
  };

  const fetchStudents = async () => {
    try {
      const data = await adminService.getStudents();
      setStudents(data);
    } catch (err) {
      console.error("Erreur élèves:", err);
    }
  };

  const fetchAbsences = async () => {
    setLoading(true);
    try {
      const filters = {};
      if (search) filters.search = search;
      if (selectedClassroom) filters.classroom_id = selectedClassroom;
      if (selectedStudent) filters.student_id = selectedStudent;

      const data = await adminService.getAbsences(filters);
      setAbsences(data);
      setError(null);
    } catch (err) {
      setError("Impossible de charger la liste des absences.");
    } finally {
      setLoading(false);
    }
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    try {
      // Mapper is_justified → justified (nom du champ backend)
      const payload = {
        student_id:  formData.student_id,
        sequence_id: formData.sequence_id || null,
        date:        formData.date,
        hours:       formData.hours,
        justified:   formData.is_justified,
        reason:      formData.reason,
      };
      if (currentAbsence) {
        await adminService.updateAbsence(currentAbsence.id, payload);
        triggerNotification("Absence mise à jour avec succès !");
      } else {
        await adminService.createAbsence(payload);
        triggerNotification("Absence enregistrée avec succès !");
      }
      setIsFormModalOpen(false);
      resetForm();
      fetchAbsences();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de l'enregistrement de l'absence.");
    }
  };

  const handleDelete = async (absence) => {
    if (window.confirm(`Êtes-vous sûr de vouloir supprimer cette absence ?`)) {
      try {
        await adminService.deleteAbsence(absence.id);
        triggerNotification("Absence supprimée avec succès !");
        fetchAbsences();
      } catch (err) {
        setError(err.response?.data?.message || "Erreur lors de la suppression.");
      }
    }
  };

  const openAddModal = () => {
    resetForm();
    setCurrentAbsence(null);
    setIsFormModalOpen(true);
  };

  const openEditModal = (absence) => {
    setCurrentAbsence(absence);
    setFormData({
      student_id: absence.student?.id || absence.student_id || '',
      classroom_id: absence.student?.classroom_id || '',
      date: absence.date || '',
      hours: absence.hours || 1,
      // Le backend stocke "justified", on mappe vers is_justified pour le formulaire
      is_justified: absence.justified || false,
      reason: absence.reason || ''
    });
    setIsFormModalOpen(true);
  };

  const resetForm = () => {
    setFormData({
      student_id: '',
      classroom_id: '',
      date: '',
      hours: 1,
      is_justified: false,
      reason: ''
    });
  };

  const triggerNotification = (msg) => {
    setSuccessMessage(msg);
    setTimeout(() => setSuccessMessage(null), 4000);
  };

  const handleInputChange = (e) => {
    const { name, value, type, checked } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'checkbox' ? checked : value
    }));
  };

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* En-tête */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Gestion des Absences</h1>
          <p className="text-gray-600 mt-2">Enregistrez et gérez les absences des élèves</p>
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

        {/* Filtres et Bouton Ajouter */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            {/* Recherche */}
            <input
              type="text"
              placeholder="Rechercher..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            {/* Filtre Classe */}
            <select
              value={selectedClassroom}
              onChange={(e) => setSelectedClassroom(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Toutes les classes</option>
              {classrooms.map(classroom => (
                <option key={classroom.id} value={classroom.id}>
                  {classroom.name}
                </option>
              ))}
            </select>

            {/* Filtre Élève */}
            <select
              value={selectedStudent}
              onChange={(e) => setSelectedStudent(e.target.value)}
              className="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Tous les élèves</option>
              {students.map(student => (
                <option key={student.id} value={student.id}>
                  {student.first_name} {student.last_name}
                </option>
              ))}
            </select>

            {/* Bouton Ajouter */}
            <button
              onClick={openAddModal}
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium"
            >
              + Ajouter une Absence
            </button>
          </div>
        </div>

        {/* Tableau des absences */}
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Chargement...</div>
          ) : absences.length === 0 ? (
            <div className="p-8 text-center text-gray-500">Aucune absence enregistrée</div>
          ) : (
            <table className="w-full">
              <thead className="bg-gray-100 border-b">
                <tr>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Élève</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Classe</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Heures</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Justifiée</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Motif</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                {absences.map((absence) => (
                  <tr key={absence.id} className="border-b hover:bg-gray-50 transition">
                    <td className="px-6 py-4 text-sm">
                      {absence.student?.first_name} {absence.student?.last_name}
                    </td>
                    <td className="px-6 py-4 text-sm">{absence.student?.classroom?.name || '—'}</td>
                    <td className="px-6 py-4 text-sm">{new Date(absence.date).toLocaleDateString('fr-FR')}</td>
                    <td className="px-6 py-4 text-sm">{absence.hours}h</td>
                    <td className="px-6 py-4 text-sm">
                      <span className={`px-3 py-1 rounded-full text-xs font-medium ${
                        absence.justified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      }`}>
                        {absence.justified ? 'Oui' : 'Non'}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600">{absence.reason || '-'}</td>
                    <td className="px-6 py-4 text-center space-x-2">
                      <button
                        onClick={() => openEditModal(absence)}
                        className="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition text-sm"
                      >
                        Modifier
                      </button>
                      <button
                        onClick={() => handleDelete(absence)}
                        className="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition text-sm"
                      >
                        Supprimer
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </div>

      {/* Modal Formulaire */}
      {isFormModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 className="text-2xl font-bold mb-6">
              {currentAbsence ? 'Modifier une Absence' : 'Ajouter une Absence'}
            </h2>

            <form onSubmit={handleFormSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Élève *</label>
                <select
                  name="student_id"
                  value={formData.student_id}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sélectionner un élève</option>
                  {students.map(student => (
                    <option key={student.id} value={student.id}>
                      {student.first_name} {student.last_name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Classe *</label>
                <select
                  name="classroom_id"
                  value={formData.classroom_id}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sélectionner une classe</option>
                  {classrooms.map(classroom => (
                    <option key={classroom.id} value={classroom.id}>
                      {classroom.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                <input
                  type="date"
                  name="date"
                  value={formData.date}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Heures *</label>
                <input
                  type="number"
                  name="hours"
                  value={formData.hours}
                  onChange={handleInputChange}
                  min="0.5"
                  step="0.5"
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div className="flex items-center">
                <input
                  type="checkbox"
                  name="is_justified"
                  checked={formData.is_justified}
                  onChange={handleInputChange}
                  className="h-4 w-4 text-blue-600 rounded"
                />
                <label className="ml-2 text-sm text-gray-700">Absence justifiée</label>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Motif</label>
                <textarea
                  name="reason"
                  value={formData.reason}
                  onChange={handleInputChange}
                  rows="3"
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div className="flex gap-4 mt-6">
                <button
                  type="button"
                  onClick={() => setIsFormModalOpen(false)}
                  className="flex-1 bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition font-medium"
                >
                  Annuler
                </button>
                <button
                  type="submit"
                  className="flex-1 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium"
                >
                  {currentAbsence ? 'Mettre à jour' : 'Enregistrer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
