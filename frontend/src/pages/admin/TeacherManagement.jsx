import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function TeacherManagement() {
  const [teachers, setTeachers] = useState([]);
  const [classrooms, setClassrooms] = useState([]);
  const [subjects, setSubjects] = useState([]); // Pour charger la liste des matières
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);

  // Recherche & Filtres
  const [search, setSearch] = useState('');
  const [selectedStatus, setSelectedStatus] = useState('');

  // Gestion des modales
  const [isFormModalOpen, setIsFormModalOpen] = useState(false);
  const [isAssignModalOpen, setIsAssignModalOpen] = useState(false);
  const [currentTeacher, setCurrentTeacher] = useState(null);

  // Formulaires
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    status: 'active'
  });

  const [assignData, setAssignData] = useState({
    classroom_id: '',
    subject_id: ''
  });

  useEffect(() => {
    fetchInitialTools();
  }, []);

  useEffect(() => {
    fetchTeachers();
  }, [search, selectedStatus]);

  const fetchInitialTools = async () => {
    try {
      const [clsData, subData] = await Promise.all([
        adminService.getClassrooms(),
        adminService.getSubjects(),
      ]);
      setClassrooms(clsData);
      setSubjects(subData);
    } catch (err) {
      console.error("Erreur de chargement des outils d'affectation", err);
    }
  };

  const fetchTeachers = async () => {
    setLoading(true);
    try {
      const filters = {};
      if (search) filters.search = search;
      if (selectedStatus) filters.status = selectedStatus;

      const data = await adminService.getTeachers(filters);
      setTeachers(data);
      setError(null);
    } catch (err) {
      setError("Impossible de récupérer la liste des enseignants.");
    } finally {
      setLoading(false);
    }
  };

  const handleFormSubmit = async (e) => {
    e.preventDefault();
    try {
      if (currentTeacher) {
        await adminService.updateTeacher(currentTeacher.id, formData);
        showToast("Enseignant mis à jour avec succès !");
      } else {
        await adminService.createTeacher(formData);
        showToast("Nouvel enseignant enregistré sur EduSmart !");
      }
      setIsFormModalOpen(false);
      fetchTeachers();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de la sauvegarde.");
    }
  };

  const handleAssignSubmit = async (e) => {
    e.preventDefault();
    try {
      await adminService.assignTeacherClass(
        currentTeacher.id, 
        assignData.classroom_id, 
        assignData.subject_id
      );
      showToast("Affectation de classe validée !");
      setIsAssignModalOpen(false);
      fetchTeachers();
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de l'affectation.");
    }
  };

  const handleUnassign = async (teacherId, classroomId, subjectId) => {
    if (window.confirm("Retirer cet enseignant de cette classe pour cette matière ?")) {
      try {
        await adminService.unassignTeacherClass(teacherId, classroomId, subjectId);
        showToast("Affectation retirée avec succès.");
        fetchTeachers();
      } catch (err) {
        setError("Erreur lors du retrait de l'affectation.");
      }
    }
  };

  const openAddModal = () => {
    setFormData({ first_name: '', last_name: '', email: '', status: 'active' });
    setCurrentTeacher(null);
    setIsFormModalOpen(true);
  };

  const openEditModal = (teacher) => {
    setCurrentTeacher(teacher);
    // Le backend retourne teacher.user.name — on le split pour pré-remplir les champs
    const nameParts = (teacher.user?.name || '').split(' ');
    const lastName  = nameParts[0] || '';
    const firstName = nameParts.slice(1).join(' ') || '';
    setFormData({
      first_name: firstName,
      last_name: lastName,
      email: teacher.user?.email || '',
      status: teacher.status || 'active'
    });
    setIsFormModalOpen(true);
  };

  const openAssignModal = (teacher) => {
    setCurrentTeacher(teacher);
    setAssignData({ classroom_id: '', subject_id: '' });
    setIsAssignModalOpen(true);
  };

  const showToast = (msg) => {
    setSuccessMessage(msg);
    setTimeout(() => setSuccessMessage(null), 4000);
  };

  return (
    <div className="space-y-6">
      {/* En-tête */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Gestion des Enseignants</h1>
          <p className="text-sm text-slate-500">Supervisez le corps enseignant, créez les comptes d'accès et gérez les attributions pédagogiques.</p>
        </div>
        <button 
          onClick={openAddModal}
          className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition shadow-sm flex items-center gap-2 text-sm"
        >
          ➕ Ajouter un enseignant
        </button>
      </div>

      {/* Notifications */}
      {successMessage && <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">{successMessage}</div>}
      {error && <div className="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm flex justify-between items-center"><span>{error}</span><button onClick={() => setError(null)}>✕</button></div>}

      {/* Barre de Filtres */}
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white p-4 rounded-xl border border-slate-100 shadow-xs">
        <input 
          type="text" 
          placeholder="Rechercher par nom ou adresse email..." 
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-blue-500 focus:bg-white text-slate-700"
        />
        <select
          value={selectedStatus}
          onChange={(e) => setSelectedStatus(e.target.value)}
          className="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-hidden focus:border-blue-500 focus:bg-white text-slate-700"
        >
          <option value="">Tous les statuts</option>
          <option value="active">Actif</option>
          <option value="suspended">Suspendu</option>
        </select>
      </div>

      {/* Tableau du corps enseignant */}
      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {loading ? (
          <div className="p-12 text-center text-slate-400">Chargement des enseignants...</div>
        ) : teachers.length === 0 ? (
          <div className="p-12 text-center text-slate-400">Aucun enseignant trouvé.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-50/70 border-b border-slate-100 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                  <th className="py-4 px-6">Enseignant</th>
                  <th className="py-4 px-6">Email</th>
                  <th className="py-4 px-6">Classes & Matières Assignées</th>
                  <th className="py-4 px-6">Statut</th>
                  <th className="py-4 px-6 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50 text-sm text-slate-700">
                {teachers.map((teacher) => (
                  <tr key={teacher.id} className="hover:bg-slate-50/50 transition">
                    <td className="py-4 px-6 font-medium text-slate-900">{teacher.user?.name || '—'}</td>
                    <td className="py-4 px-6 text-slate-500 text-xs font-mono">{teacher.user?.email || '—'}</td>
                    <td className="py-4 px-6">
                      <div className="flex flex-wrap gap-1.5 max-w-md">
                        {teacher.classrooms && teacher.classrooms.length > 0 ? (
                          teacher.classrooms.map((cls, idx) => (
                            <span key={idx} className="inline-flex items-center gap-1 px-2 py-1 bg-slate-100 border border-slate-200 text-slate-700 text-xs font-medium rounded-lg">
                              {cls.level} {cls.name} — <span className="text-blue-600 font-semibold">{cls.pivot?.subject?.name || 'Matière'}</span>
                              <button 
                                onClick={() => handleUnassign(teacher.id, cls.id, cls.pivot?.subject_id)}
                                className="ml-1 text-slate-400 hover:text-rose-500 font-bold"
                                title="Retirer cette affectation"
                              >
                                ×
                              </button>
                            </span>
                          ))
                        ) : (
                          <span className="text-xs text-slate-400 italic">Aucune classe assignée</span>
                        )}
                      </div>
                    </td>
                    <td className="py-4 px-6">
                      <span className={`px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        teacher.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'
                      }`}>
                        {teacher.status === 'active' ? 'Actif' : 'Suspendu'}
                      </span>
                    </td>
                    <td className="py-4 px-6 text-right space-x-2">
                      <button 
                        onClick={() => openEditModal(teacher)}
                        className="text-slate-500 hover:text-slate-800 font-medium text-xs bg-slate-100 px-2.5 py-1.5 rounded-lg transition"
                      >
                        ✏️ Éditer
                      </button>
                      <button 
                        onClick={() => openAssignModal(teacher)}
                        className="text-white hover:bg-blue-700 font-medium text-xs bg-blue-600 px-2.5 py-1.5 rounded-lg transition shadow-xs"
                      >
                        🔗 Affecter
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* MODALE : CRÉATION / MODIFICATION ENSEIGNANT */}
      {isFormModalOpen && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-md w-full shadow-xl overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
              <h3 className="font-bold text-slate-800">{currentTeacher ? "Modifier le profil" : "Enregistrer un Enseignant"}</h3>
              <button onClick={() => setIsFormModalOpen(false)} className="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form onSubmit={handleFormSubmit} className="p-6 space-y-4">
              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-500 mb-1">Nom</label>
                  <input type="text" required value={formData.last_name} onChange={(e) => setFormData({...formData, last_name: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-blue-500 text-slate-800"/>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-slate-500 mb-1">Prénom</label>
                  <input type="text" required value={formData.first_name} onChange={(e) => setFormData({...formData, first_name: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-blue-500 text-slate-800"/>
                </div>
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-500 mb-1">Adresse Email</label>
                <input type="email" required value={formData.email} onChange={(e) => setFormData({...formData, email: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-blue-500 text-slate-800" placeholder="exemple@edusmart.cm"/>
              </div>
              {currentTeacher && (
                <div>
                  <label className="block text-xs font-semibold text-slate-500 mb-1">Statut d'accès</label>
                  <select value={formData.status} onChange={(e) => setFormData({...formData, status: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl bg-white text-sm focus:border-blue-500 text-slate-800">
                    <option value="active">Autorisé (Actif)</option>
                    <option value="suspended">Suspendu</option>
                  </select>
                </div>
              )}
              <div className="pt-4 border-t border-slate-100 flex justify-end gap-2 text-sm">
                <button type="button" onClick={() => setIsFormModalOpen(false)} className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Annuler</button>
                <button type="submit" className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Sauvegarder</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* MODALE : AFFECTATION CLASSE ET MATIÈRE */}
      {isAssignModalOpen && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-sm w-full shadow-xl overflow-hidden">
            <div className="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
              <h3 className="font-bold text-slate-800">Nouvelle attribution</h3>
              <button onClick={() => setIsAssignModalOpen(false)} className="text-slate-400 hover:text-slate-600">✕</button>
            </div>
            <form onSubmit={handleAssignSubmit} className="p-6 space-y-4">
              <p className="text-xs text-slate-500">Assigner une charge de cours à <strong>M./Mme {currentTeacher?.user?.name || currentTeacher?.last_name}</strong>.</p>
              <div>
                <label className="block text-xs font-semibold text-slate-500 mb-1">Classe ciblée</label>
                <select required value={assignData.classroom_id} onChange={(e) => setAssignData({...assignData, classroom_id: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl bg-white text-sm text-slate-800 focus:border-blue-500">
                  <option value="">Choisir une classe</option>
                  {classrooms.map(c => <option key={c.id} value={c.id}>{c.level} {c.name}</option>)}
                </select>
              </div>
              <div>
                <label className="block text-xs font-semibold text-slate-500 mb-1">Matière à enseigner</label>
                <select required value={assignData.subject_id} onChange={(e) => setAssignData({...assignData, subject_id: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl bg-white text-sm text-slate-800 focus:border-blue-500">
                  <option value="">Choisir la matière</option>
                  {subjects.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                </select>
              </div>
              <div className="pt-4 border-t border-slate-100 flex justify-end gap-2 text-sm">
                <button type="button" onClick={() => setIsAssignModalOpen(false)} className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl hover:bg-slate-50">Fermer</button>
                <button type="submit" className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">Lier à la classe</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}