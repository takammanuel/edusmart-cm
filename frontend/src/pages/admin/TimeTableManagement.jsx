import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function TimeTableManagement() {
  const [classrooms, setClassrooms] = useState([]);
  const [teachers, setTeachers] = useState([]);
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  // Sélection classe pour affichage emploi du temps
  const [selectedClassroom, setSelectedClassroom] = useState('');
  const [timeTable, setTimeTable] = useState([]);

  // Gestion des Modales
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [currentSlot, setCurrentSlot] = useState(null);

  // Jours de la semaine et créneaux
  const DAYS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
  const TIMES = ['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'];

  // Formulaire
  const [formData, setFormData] = useState({
    classroom_id: '',
    day_of_week: '',
    start_time: '',
    end_time: '',
    teacher_id: '',
    subject_id: '',
  });

  useEffect(() => {
    fetchInitialData();
  }, []);

  useEffect(() => {
    if (selectedClassroom) {
      fetchTimeTable();
    }
  }, [selectedClassroom]);

  const fetchInitialData = async () => {
    setLoading(true);
    try {
      const [classroomsData, teachersData, subjectsData] = await Promise.all([
        adminService.getClassrooms(),
        adminService.getTeachers(),
        adminService.getSubjects()
      ]);

      setClassrooms(classroomsData);
      setTeachers(teachersData);
      setSubjects(subjectsData);
      setError(null);

      if (classroomsData.length > 0) {
        setSelectedClassroom(classroomsData[0].id);
      }
    } catch (err) {
      setError("Impossible de charger les données initiales.");
    } finally {
      setLoading(false);
    }
  };

  const fetchTimeTable = async () => {
    try {
      const data = await adminService.getTimetables({ classroom_id: selectedClassroom });
      setTimeTable(data);
    } catch (err) {
      console.error("Erreur loading timetable:", err);
    }
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentSlot) {
        await adminService.updateTimetable(currentSlot.id, formData);
        triggerNotification("Créneau horaire mis à jour avec succès !");
      } else {
        await adminService.createTimetable(formData);
        triggerNotification("Créneau horaire enregistré avec succès !");
      }
      setIsFormModalOpen(false);
      resetForm();
      fetchTimeTable();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de l'enregistrement.");
    }
  };

  const openAddModal = (day, time) => {
    resetForm();
    setCurrentSlot(null);
    setFormData(prev => ({
      ...prev,
      classroom_id: selectedClassroom,
      day_of_week: day.toLowerCase(), // backend attend 'lundi', 'mardi', etc.
      start_time: time
    }));
    setIsFormModalOpen(true);
  };

  const openEditModal = (slot) => {
    setCurrentSlot(slot);
    setFormData({
      classroom_id: slot.classroom_id || selectedClassroom,
      day_of_week:  slot.day_of_week || '',
      start_time:   slot.start_time  || '',
      end_time:     slot.end_time    || '',
      teacher_id:   slot.teacher_id  || '',
      subject_id:   slot.subject_id  || '',
    });
    setIsFormModalOpen(true);
  };

  const resetForm = () => {
    setFormData({
      classroom_id: selectedClassroom,
      day_of_week: '',
      start_time: '',
      end_time: '',
      teacher_id: '',
      subject_id: '',
    });
  };

  const triggerNotification = (msg) => {
    setSuccessMessage(msg);
    setTimeout(() => setSuccessMessage(null), 4000);
  };

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 p-6 flex items-center justify-center">
        <div className="text-gray-500">Chargement...</div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* En-tête */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Gestion des Emplois du Temps</h1>
          <p className="text-gray-600 mt-2">Créez et gérez les emplois du temps des classes</p>
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

        {/* Sélecteur de Classe */}
        <div className="bg-white rounded-lg shadow-md p-6 mb-6">
          <label className="block text-sm font-medium text-gray-700 mb-2">Sélectionner une classe</label>
          <select
            value={selectedClassroom}
            onChange={(e) => setSelectedClassroom(e.target.value)}
            className="w-full md:w-1/3 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            {classrooms.map(classroom => (
              <option key={classroom.id} value={classroom.id}>
                {classroom.name}
              </option>
            ))}
          </select>
        </div>

        {/* Emploi du Temps */}
        {selectedClassroom && (
          <div className="bg-white rounded-lg shadow-md p-6 overflow-x-auto">
            <h2 className="text-xl font-bold text-gray-900 mb-4">
              Emploi du Temps - {classrooms.find(c => c.id == selectedClassroom)?.name}
            </h2>

            <table className="w-full border-collapse">
              <thead>
                <tr className="bg-gray-100">
                  <th className="border border-gray-300 px-4 py-3 text-left text-sm font-semibold text-gray-700">Horaire</th>
                  {DAYS.map(day => (
                    <th key={day} className="border border-gray-300 px-4 py-3 text-center text-sm font-semibold text-gray-700">
                      {day}
                    </th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {TIMES.map((time) => (
                  <tr key={time} className="hover:bg-gray-50">
                    <td className="border border-gray-300 px-4 py-3 text-sm font-medium text-gray-900">
                      {time}
                    </td>
                    {DAYS.map((day) => {
                      const dayKey = day.toLowerCase();
                      const slot = timeTable.find(t => t.day_of_week === dayKey && t.start_time?.startsWith(time));
                      return (
                        <td
                          key={`${day}-${time}`}
                          className="border border-gray-300 px-4 py-3 text-center text-sm cursor-pointer hover:bg-blue-50 transition relative group"
                          onClick={() => openAddModal(day, time)}
                        >
                          {slot ? (
                            <div
                              className="bg-blue-100 border border-blue-300 rounded p-2 text-xs"
                              onClick={(e) => {
                                e.stopPropagation();
                                openEditModal(slot);
                              }}
                            >
                              <p className="font-semibold">{slot.subject?.name}</p>
                              <p className="text-gray-600">{slot.teacher?.user?.name}</p>
                            </div>
                          ) : (
                            <div className="text-gray-400 text-xl group-hover:text-blue-400">+</div>
                          )}
                        </td>
                      );
                    })}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        {/* Section Info */}
        <div className="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
          <h3 className="text-lg font-semibold text-blue-900 mb-2">💡 Guide d'utilisation</h3>
          <ul className="text-sm text-blue-800 space-y-1">
            <li>• Cliquez sur une cellule vide pour ajouter un créneau</li>
            <li>• Cliquez sur un créneau existant pour le modifier</li>
            <li>• Sélectionnez la classe pour voir son emploi du temps complet</li>
          </ul>
        </div>
      </div>

      {/* Modal Formulaire */}
      {isFormModalOpen && (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
          <div className="bg-white rounded-lg p-8 max-w-md w-full">
            <h2 className="text-2xl font-bold mb-6">
              {currentSlot ? 'Modifier un Créneau' : 'Ajouter un Créneau'}
            </h2>

            <form onSubmit={handleFormSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Jour *</label>
                <select
                  name="day_of_week"
                  value={formData.day_of_week}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sélectionner un jour</option>
                  {DAYS.map(d => (
                    <option key={d} value={d.toLowerCase()}>{d}</option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Heure de Début *</label>
                <input
                  type="time"
                  name="start_time"
                  value={formData.start_time}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Heure de Fin *</label>
                <input
                  type="time"
                  name="end_time"
                  value={formData.end_time}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Matière *</label>
                <select
                  name="subject_id"
                  value={formData.subject_id}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sélectionner une matière</option>
                  {subjects.map(subject => (
                    <option key={subject.id} value={subject.id}>
                      {subject.name}
                    </option>
                  ))}
                </select>
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Enseignant *</label>
                <select
                  name="teacher_id"
                  value={formData.teacher_id}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Sélectionner un enseignant</option>
                  {teachers.map(teacher => (
                    <option key={teacher.id} value={teacher.id}>
                      {teacher.user?.name || '—'}
                    </option>
                  ))}
                </select>
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
                  {currentSlot ? 'Mettre à jour' : 'Enregistrer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
