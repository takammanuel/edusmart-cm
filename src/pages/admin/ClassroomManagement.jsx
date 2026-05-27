import React, { useState, useEffect } from 'react';
import { adminService } from '../../services/adminService';

export default function ClassroomManagement() {
  const [classrooms, setClassrooms] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [successMessage, setSuccessMessage] = useState(null);
  const [isOpen, setIsOpen] = useState(false);

  const [formData, setFormData] = useState({
    level: '',
    name: '',
    specialty: ''
  });

  useEffect(() => {
    fetchClassrooms();
  }, []);

  const fetchClassrooms = async () => {
    setLoading(true);
    try {
      const data = await adminService.getClassrooms();
      setClassrooms(data);
    } catch (err) {
      setError("Impossible de charger les classes. Vérifiez vos routes d'API backend.");
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    try {
      await adminService.createClassroom(formData);
      setSuccessMessage("Classe ajoutée avec succès !");
      setFormData({ level: '', name: '', specialty: '' });
      setIsOpen(false);
      fetchClassrooms();
      setTimeout(() => setSuccessMessage(null), 3000);
    } catch (err) {
      setError(err.response?.data?.message || "Erreur lors de la création.");
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
        <div>
          <h1 className="text-2xl font-bold text-slate-800">Gestion des Classes</h1>
          <p className="text-sm text-slate-500">Configurez les structures pédagogiques avant d'y assigner les élèves et professeurs.</p>
        </div>
        <button 
          onClick={() => setIsOpen(true)}
          className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition shadow-sm"
        >
          ➕ Nouvelle Classe
        </button>
      </div>

      {successMessage && <div className="p-4 bg-emerald-50 text-emerald-800 rounded-xl text-sm border border-emerald-100">{successMessage}</div>}
      {error && <div className="p-4 bg-rose-50 text-rose-800 rounded-xl text-sm border border-rose-100">{error}</div>}

      <div className="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        {loading ? (
          <div className="p-12 text-center text-slate-400">Chargement des structures scolaires...</div>
        ) : classrooms.length === 0 ? (
          <div className="p-12 text-center text-slate-400">Aucune classe n'est configurée pour le moment.</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-left border-collapse">
              <thead>
                <tr className="bg-slate-50 border-b border-slate-100 text-slate-600 text-xs font-semibold uppercase tracking-wider">
                  <th className="py-4 px-6">Niveau</th>
                  <th className="py-4 px-6">Intitulé de la classe</th>
                  <th className="py-4 px-6">Spécialité / Série</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-50 text-sm text-slate-700">
                {classrooms.map((cls) => (
                  <tr key={cls.id} className="hover:bg-slate-50/50 transition">
                    <td className="py-4 px-6 font-semibold text-blue-600">{cls.level}</td>
                    <td className="py-4 px-6 font-medium text-slate-900">{cls.name}</td>
                    <td className="py-4 px-6 text-slate-500">{cls.specialty || 'Générale'}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {/* MODALE CRÉATION CLASSE */}
      {isOpen && (
        <div className="fixed inset-0 bg-black/40 backdrop-blur-xs flex items-center justify-center p-4 z-50">
          <div className="bg-white rounded-2xl max-w-sm w-full shadow-xl p-6 space-y-4">
            <h3 className="font-bold text-lg text-slate-800">Ajouter une classe</h3>
            <form onSubmit={handleSubmit} className="space-y-3">
              <div>
                <label className="block text-xs font-medium text-slate-500 mb-1">Niveau (ex: Tle, 1ère, 6ème)</label>
                <input type="text" required value={formData.level} onChange={(e) => setFormData({...formData, level: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-blue-500 text-slate-800" placeholder="ex: Tle"/>
              </div>
              <div>
                <label className="block text-xs font-medium text-slate-500 mb-1">Nom / Index de Classe</label>
                <input type="text" required value={formData.name} onChange={(e) => setFormData({...formData, name: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-blue-500 text-slate-800" placeholder="ex: C, C1, All1"/>
              </div>
              <div>
                <label className="block text-xs font-medium text-slate-500 mb-1">Spécialité / Option (Optionnel)</label>
                <input type="text" value={formData.specialty} onChange={(e) => setFormData({...formData, specialty: e.target.value})} className="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:border-blue-500 text-slate-800" placeholder="ex: C, D, TI, Espagnol"/>
              </div>
              <div className="pt-4 flex justify-end gap-2 text-sm">
                <button type="button" onClick={() => setIsOpen(false)} className="px-4 py-2 border border-slate-200 text-slate-600 rounded-xl">Fermer</button>
                <button type="submit" className="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium">Créer la classe</button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
}