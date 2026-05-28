import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function SequenceManagement() {
  const [sequences, setSequences] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);
  const [search, setSearch] = useState('');

  // Gestion des Modales
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [currentSequence, setCurrentSequence] = useState(null);

  // Formulaire
  const [formData, setFormData] = useState({
    name: '',
    number: '',
    start_date: '',
    end_date: '',
    description: ''
  });

  useEffect(() => {
    fetchSequences();
  }, [search]);

  const fetchSequences = async () => {
    setLoading(true);
    try {
      const filters = {};
      if (search) filters.search = search;

      const data = await adminService.getSequences(filters);
      setSequences(data);
      setError(null);
    } catch (err) {
      setError("Impossible de charger la liste des séquences.");
    } finally {
      setLoading(false);
    }
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentSequence) {
        await adminService.updateSequence(currentSequence.id, formData);
        triggerNotification("Séquence mise à jour avec succès !");
      } else {
        await adminService.createSequence(formData);
        triggerNotification("Nouvelle séquence créée avec succès !");
      }
      setIsFormModalOpen(false);
      resetForm();
      fetchSequences();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de l'enregistrement.");
    }
  };

  const handleDelete = async (sequence) => {
    if (window.confirm(`Êtes-vous sûr de vouloir supprimer la séquence "${sequence.name}" ?`)) {
      try {
        await adminService.deleteSequence(sequence.id);
        triggerNotification("Séquence supprimée avec succès !");
        fetchSequences();
      } catch (err) {
        setError(err.response?.data?.message || "Erreur lors de la suppression.");
      }
    }
  };

  const openAddModal = () => {
    resetForm();
    setCurrentSequence(null);
    setIsFormModalOpen(true);
  };

  const openEditModal = (sequence) => {
    setCurrentSequence(sequence);
    setFormData({
      name: sequence.name || '',
      number: sequence.number || '',
      start_date: sequence.start_date || '',
      end_date: sequence.end_date || '',
      description: sequence.description || ''
    });
    setIsFormModalOpen(true);
  };

  const resetForm = () => {
    setFormData({
      name: '',
      number: '',
      start_date: '',
      end_date: '',
      description: ''
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

  return (
    <div className="min-h-screen bg-gray-50 p-6">
      <div className="max-w-7xl mx-auto">
        {/* En-tête */}
        <div className="mb-8">
          <h1 className="text-3xl font-bold text-gray-900">Gestion des Séquences</h1>
          <p className="text-gray-600 mt-2">Gérez les périodes pédagogiques de l'établissement</p>
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
              placeholder="Rechercher une séquence..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
            <button
              onClick={openAddModal}
              className="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium"
            >
              + Ajouter une Séquence
            </button>
          </div>
        </div>

        {/* Tableau des séquences */}
        <div className="bg-white rounded-lg shadow-md overflow-hidden">
          {loading ? (
            <div className="p-8 text-center text-gray-500">Chargement...</div>
          ) : sequences.length === 0 ? (
            <div className="p-8 text-center text-gray-500">Aucune séquence enregistrée</div>
          ) : (
            <table className="w-full">
              <thead className="bg-gray-100 border-b">
                <tr>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">N°</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date Début</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date Fin</th>
                  <th className="px-6 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
                  <th className="px-6 py-3 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
              </thead>
              <tbody>
                {sequences.map((sequence) => (
                  <tr key={sequence.id} className="border-b hover:bg-gray-50 transition">
                    <td className="px-6 py-4 text-sm font-medium">{sequence.name}</td>
                    <td className="px-6 py-4 text-sm">{sequence.number}</td>
                    <td className="px-6 py-4 text-sm">{new Date(sequence.start_date).toLocaleDateString('fr-FR')}</td>
                    <td className="px-6 py-4 text-sm">{new Date(sequence.end_date).toLocaleDateString('fr-FR')}</td>
                    <td className="px-6 py-4 text-sm text-gray-600 truncate">{sequence.description}</td>
                    <td className="px-6 py-4 text-center space-x-2">
                      <button
                        onClick={() => openEditModal(sequence)}
                        className="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600 transition text-sm"
                      >
                        Modifier
                      </button>
                      <button
                        onClick={() => handleDelete(sequence)}
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
              {currentSequence ? 'Modifier une Séquence' : 'Ajouter une Séquence'}
            </h2>

            <form onSubmit={handleFormSubmit} className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Nom *</label>
                <input
                  type="text"
                  name="name"
                  value={formData.name}
                  onChange={handleInputChange}
                  placeholder="Ex: Séquence 1"
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Numéro *</label>
                <input
                  type="number"
                  name="number"
                  value={formData.number}
                  onChange={handleInputChange}
                  placeholder="1"
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Date Début *</label>
                <input
                  type="date"
                  name="start_date"
                  value={formData.start_date}
                  onChange={handleInputChange}
                  required
                  className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">Date Fin *</label>
                <input
                  type="date"
                  name="end_date"
                  value={formData.end_date}
                  onChange={handleInputChange}
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
                  {currentSequence ? 'Mettre à jour' : 'Créer'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}
