import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function StudentManagement() {
  const [students, setStudents] = useState([]);
  const [classrooms, setClassrooms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  // Filtres de recherche
  const [search, setSearch] = useState('');
  const [selectedClassroom, setSelectedClassroom] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');

  // Gestion des Modales
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [isTransferModalOpen, setIsTransferModalOpen] = useState(false);
  const [currentStudent, setCurrentStudent] = useState(null);

  // Formulaire d'inscription / modification
  const [formData, setFormData] = useState({
    matricule: '',
    first_name: '',
    last_name: '',
    birth_date: '',
    classroom_id: ''
  });
  
  // Formulaire de transfert
  const [targetClassroomId, setTargetClassroomId] = useState('');

  // Charger les données initiales
  useEffect(() => {
    fetchClassrooms();
  }, []);

  // Recharger les élèves quand les filtres changent
  useEffect(() => {
    fetchStudents();
  }, [search, selectedClassroom, selectedStatus]);

  const fetchClassrooms = async () => {
    try {
      const data = await adminService.getClassrooms();
      setClassrooms(data);
    } catch (err) {
      console.error("Erreur classes:", err);
    }
  };

  const fetchStudents = async () => {
    setLoading(true);
    try {
      const filters = {};
      if (search) filters.search = search;
      if (selectedClassroom) filters.classroom_id = selectedClassroom;
      if (selectedStatus) filters.status = selectedStatus;

      const data = await adminService.getStudents(filters);
      setStudents(data);
      setError(null);
    } catch (err) {
      setError("Impossible de charger la liste des élèves.");
    } finally {
      setLoading(false);
    }
  };

  // Soumission Ajout / Édition
  const handleFormSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentStudent) {
        await adminService.updateStudent(currentStudent.id, formData);
        triggerNotification("Élève mis à jour avec succès !");
      } else {
        await adminService.createStudent(formData);
        triggerNotification("Nouvel élève inscrit avec succès !");
      }
      setIsFormModalOpen(false);
      resetForm();
      fetchStudents();
    } catch (err) {
      setError(err.response?.data?.message || "Une erreur est survenue lors de l'enregistrement.");
    }
  };

  // Action de transfert
  const handleTransferSubmit = async (e) => {
    e.preventDefault();
    try {
      await adminService.transferStudent(currentStudent.id, targetClassroomId);
      triggerNotification("Élève transféré avec succès !");
      setIsTransferModalOpen(false);
      setCurrentStudent(null);
      setTargetClassroomId('');
      fetchStudents();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors du transfert.");
    }
  };

  // Action de Radiation
  const handleExpel = async (student) => {
    if (window.confirm(`Êtes-vous sûr de vouloir radier définitivement l'élève ${student.first_name} ${student.last_name} ?`)) {
      try {
        await adminService.expelStudent(student.id);
        triggerNotification("L'élève a été marqué comme radié.");
        fetchStudents();
      } catch (err) {
        setError(err.response?.data?.message || "Erreur lors de la radiation.");
      }
    }
  };

  const openAddModal = () => {
    resetForm();
    setCurrentStudent(null);
    setIsFormModalOpen(true);
  };

  const openEditModal = (student) => {
    setCurrentStudent(student);
    setFormData({
      matricule: student.matricule,
      first_name: student.first_name,
      last_name: student.last_name,
      birth_date: student.birth_date || '',
      classroom_id: student.classroom?.id || student.classroom_id || ''
    });
    setIsFormModalOpen(true);
  };

  const openTransferModal = (student) => {
    setCurrentStudent(student);
    setTargetClassroomId('');
    setIsTransferModalOpen(true);
  };

  const resetForm = () => {
    setFormData({ matricule: '', first_name: '', last_name: '', birth_date: '', classroom_id: '' });
  };

  const triggerNotification = (msg) => {
    setSuccessMessage(msg);
    setTimeout(() => setSuccessMessage(null), 4000);
  };

  return (
    <div className="space-y-6">
      {/* En-tête de section */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Gestion des Élèves</h1>
          <p className="text-sm text-slate-500">Inscriptions, mutations d'excellence et suivi administratif d'EduSmart.</p>
        </div>
        <button 
          onClick={openAddModal}
          className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition shadow-sm flex items-center gap-2 text-sm"
        >
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/></svg>
          Inscrire un élève
        </button>
      </div>

      {/* Alertes notifications */}
      {successMessage && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm flex items-center gap-2">
          <svg className="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd"/></svg>
          {successMessage}
        </div>
      )}
      {error && (
        <div className="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex justify-between items-center">
          <span>{error}</span>
          <button onClick={() => setError(null)} className="text-rose-500 font-bold">✕</button>
        </div>
      )}

      {/* Section des filtres */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-xl border border-slate-100 shadow-xs">
        <input 
          type="text" 
          placeholder="Rechercher par Nom, Prénom ou Matricule..." 
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 focus:bg-white text-slate-700"
        />
        <select
          value={selectedClassroom}
          onChange={(e) => setSelectedClassroom(e.target.value)}
          className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 focus:bg-white text-slate-700"
        >
          <option value="">Toutes les classes</option>
          {classrooms.map((cls) => (
            <option key={cls.id} value={cls.id}>{cls.level} {cls.name} ({cls.specialty})</option>
          ))}
        </select>
        <select
          value={selectedStatus}
          onChange={(e) => setSelectedStatus(e.target.value)}
          className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 focus:bg-white text-slate-700"
        >
          <option value="">Tous les statuts</option>
          <option value="active">Actif</option>
          <option value="expelled">Radié / Exclu</option>
        </select>
      </div>

      {/* Tableau des Élèves */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {loading ? (
          <div className="p-12 text-center text-slate-400">Chargement des données élèves...</div>
        ) : students.length === 0 ? (
          <div className="p-12 text-center text-slate-400">Aucun élève trouvé pour ces critères.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-50/70 border-b border-slate-100 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                  <th className="py-4 px-6">Matricule</th>
                  <th className="py-4 px-6">Nom & Prénom</th>
                  <th className="py-4 px-6">Classe</th>
                  <th className="py-4 px-6">Date Naissance</th>
                  <th className="py-4 px-6">Statut</th>
                  <th className="py-4 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50 text-sm text-slate-700">
                {students.map((student) => (
                  <tr key={student.id} className="hover:bg-slate-50/50 transition">
                    <td className="py-3.5 px-6 font-mono text-xs font-bold text-slate-600">{student.matricule}</td>
                    <td className="py-3.5 px-6 font-medium text-slate-900">{student.last_name} {student.first_name}</td>
                    <td className="py-3.5 px-6">
                      <span className="px-2.5 py-1 bg-blue-50 text-blue-700 font-semibold rounded-md text-xs">
                        {student.classroom?.name ? `${student.classroom.level} ${student.classroom.name}` : 'Non assignée'}
                      </span>
                    </td>
                    <td className="py-3.5 px-6 text-slate-500">{student.birth_date || '—'}</td>
                    <td className="py-3.5 px-6">
                      <span className={`px-2 py-0.5 rounded-full text-xs font-medium ${
                        student.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'
                      }`}>
                        {student.status === 'active' ? 'Actif' : 'Radié'}
                      </span>
                    </td>
                    <td className="py-3.5 px-6 text-right space-x-1.5">
                      <button 
                        onClick={() => openEditModal(student)}
                        className="p-1.5 text-slate-400 hover:text-slate-600 transition" 
                        title="Modifier les détails"
                      >
                        ✏️
                      </button>
                      {student.status === 'active' && (
                        <>
                          <button 
                            onClick={() => openTransferModal(student)}
                            className="p-1.5 text-blue-500 hover:text-blue-700 font-medium text-xs transition"
                            title="Transférer de classe"
                          >
                            🔄 Muté
                          </button>
                          <button 
                            onClick={() => handleExpel(student)}
                            className="p-1.5 text-rose-500 hover:text-rose-700 font-medium text-xs transition"
                            title="Radier l'élève"
                          >
                            🚫 Radier
                          </button>
                        </>
                      )}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* MODALE : FORMULAIRE ÉLÈVE (AJOUT / MODIFICATION) */}
      {isFormModalOpen && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-md w-full shadow-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div className="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <h3 className="font-bold text-slate-800">{currentStudent ? "Modifier l'Élève" : "Inscrire un Nouvel Élève"}</h3>
              <button onClick={() => setIsFormModalOpen(false)} className="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form onSubmit={handleFormSubmit} className="p-6 space-y-4">
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Matricule</label>
                <input 
                  type="text" required
                  value={formData.matricule}
                  onChange={(e) => setFormData({...formData, matricule: e.target.value})}
                  className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 text-slate-800"
                  placeholder="ex: 24ED049"
                  disabled={!!currentStudent} // Souvent verrouillé à la modif en prod
                />
              </div>
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Nom</label>
                  <input 
                    type="text" required
                    value={formData.last_name}
                    onChange={(e) => setFormData({...formData, last_name: e.target.value})}
                    className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 text-slate-800"
                  />
                </div>
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Prénom</label>
                  <input 
                    type="text" required
                    value={formData.first_name}
                    onChange={(e) => setFormData({...formData, first_name: e.target.value})}
                    className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 text-slate-800"
                  />
                </div>
              </div>
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Date de Naissance</label>
                <input 
                  type="date" required
                  value={formData.birth_date}
                  onChange={(e) => setFormData({...formData, birth_date: e.target.value})}
                  className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-emerald-500 text-slate-800"
                />
              </div>
              {!currentStudent && (
                <div>
                  <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Classe Initiale</label>
                  <select 
                    required
                    value={formData.classroom_id}
                    onChange={(e) => setFormData({...formData, classroom_id: e.target.value})}
                    className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm bg-white outline-hidden focus:border-emerald-500 text-slate-800"
                  >
                    <option value="">Sélectionner une classe</option>
                    {classrooms.map((cls) => (
                      <option key={cls.id} value={cls.id}>{cls.level} {cls.name}</option>
                    ))}
                  </select>
                </div>
              )}
              <div className="pt-4 border-t border-slate-100 flex justify-end gap-2 text-sm">
                <button type="button" onClick={() => setIsFormModalOpen(false)} className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Annuler</button>
                <button type="submit" className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-medium">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODALE : MUTATION / TRANSFERT DE CLASSE */}
      {isTransferModalOpen && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-sm w-full shadow-xl overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
              <h3 className="font-bold text-slate-800">Mutation de classe</h3>
              <button onClick={() => setIsTransferModalOpen(false)} className="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form onSubmit={handleTransferSubmit} className="p-6 space-y-4">
              <p className="text-xs text-slate-500">
                Vous changez l'affectation de <strong>{currentStudent?.first_name} {currentStudent?.last_name}</strong>. Sa classe actuelle est <em>{currentStudent?.classroom?.level} {currentStudent?.classroom?.name}</em>.
              </p>
              <div>
                <label className="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1">Classe de Destination</label>
                <select 
                  required
                  value={targetClassroomId}
                  onChange={(e) => setTargetClassroomId(e.target.value)}
                  className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm bg-white outline-hidden focus:border-emerald-500 text-slate-800"
                >
                  <option value="">Sélectionner la nouvelle classe</option>
                  {classrooms
                    .filter(c => c.id !== currentStudent?.classroom?.id)
                    .map((cls) => (
                      <option key={cls.id} value={cls.id}>{cls.level} {cls.name}</option>
                    ))
                  }
                </select>
              </div>
              <div className="pt-4 border-t border-slate-100 flex justify-end gap-2 text-sm">
                <button type="button" onClick={() => setIsTransferModalOpen(false)} className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Fermer</button>
                <button type="submit" className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Confirmer le Transfert</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}