import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function SubjectManagement() {
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);
  const [search, setSearch] = useState('');

  // Gestion des Modales
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [currentSubject, setCurrentSubject] = useState(null);

  // Formulaire
  const [formData, setFormData] = useState({
    name: '',
    code: '',
    coefficient: 1,
    description: ''
  });

  useEffect(() => {
    fetchSubjects();
  }, [search]);

  const fetchSubjects = async () => {
    setLoading(true);
    try {
      const filters = {};
      if (search) filters.search = search;

      const data = await adminService.getSubjects(filters);
      setSubjects(data);
      setError(null);
    } catch (err) {
      setError("Impossible de charger la liste des matières.");
    } finally {
      setLoading(false);
    }
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentSubject) {
        await adminService.updateSubject(currentSubject.id, formData);
        triggerNotification("Matière mise à jour avec succès !");
      } else {
        await adminService.createSubject(formData);
        triggerNotification("Nouvelle matière créée avec succès !");
      }
      setIsFormModalOpen(false);
      resetForm();
      fetchSubjects();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de l'enregistrement.");
    }
  };

  const handleDelete = async (subject) => {
    if (window.confirm(`Êtes-vous sûr de vouloir supprimer la matière "${subject.name}" ?`)) {
      try {
        await adminService.deleteSubject(subject.id);
        triggerNotification("Matière supprimée avec succès !");
        fetchSubjects();
      } catch (err) {
        setError(err.response?.data?.message || "Erreur lors de la suppression.");
      }
    }
  };

  const openAddModal = () => {
    resetForm();
    setCurrentSubject(null);
    setIsFormModalOpen(true);
  };

  const openEditModal = (subject) => {
    setCurrentSubject(subject);
    setFormData({
      name: subject.name || '',
      code: subject.code || '',
      coefficient: subject.coefficient || 1,
      description: subject.description || ''
    });
    setIsFormModalOpen(true);
  };

  const resetForm = () => {
    setFormData({
      name: '',
      code: '',
      coefficient: 1,
      description: ''
    });
  };

  const triggerNotification = (msg) => {
    setSuccessMessage(msg);
    setTimeout(() => setSuccessMessage(null), 4000);
  };

  const handleInputChange = (e) => {
    const { name, value, type } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: type === 'number' ? parseFloat(value) : value
    }));
  };

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* En-tête */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Gestion des Matières</h1>
          <p className="text-gray-600 mt-2">Créez et gérez les matières enseignées</p>
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
          <div className="flex gap-4">
            <input
              type="text"
              placeholder="Rechercher une matière..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              onClick={openAddModal}
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium"
            >
              + Ajouter une Matière
            </button>
          </div>
        </div>

        {/* Tableau des matières */}
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Chargement...</div>
          ) : subjects.length === 0 ? (
            <div className="p-8 text-center text-gray-500">Aucune matière enregistrée</div>
          ) : (
            <table className="w-full">
              <thead className="bg-gray-100 border-b">
                <tr>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Code</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Coefficient</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                {subjects.map((subject) => (
                  <tr key={subject.id} className="border-b hover:bg-gray-50 transition">
                    <td className="px-6 py-4 text-sm font-medium">{subject.name}</td>
                    <td className="px-6 py-4 text-sm">{subject.code}</td>
                    <td className="px-6 py-4 text-center text-sm">
                      <span className="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">
                        {subject.coefficient}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-sm text-gray-600 truncate">{subject.description}</td>
                    <td className="px-6 py-4 text-center space-x-2">
                      <button
                        onClick={() => openEditModal(subject)}
                        className="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition text-sm"
                      >
                        Modifier
                      </button>
                      <button
                        onClick={() => handleDelete(subject)}
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
              {currentSubject ? 'Modifier une Matière' : 'Ajouter une Matière'}
            </h2>

            <form onSubmit={handleFormSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleInputChange}
                  placeholder="Ex: Mathématiques"
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Code *</label>
                <input
                  type="text"
                  name="code"
                  value={formData.code}
                  onChange={handleInputChange}
                  placeholder="Ex: MAT001"
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Coefficient *</label>
                <input
                  type="number"
                  name="coefficient"
                  value={formData.coefficient}
                  onChange={handleInputChange}
                  min="0.5"
                  step="0.5"
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                  name="description"
                  value={formData.description}
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
                  {currentSubject ? 'Mettre à jour' : 'Créer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
