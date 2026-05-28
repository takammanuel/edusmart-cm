import React, { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { GraduationCap, Users, School, BookOpen, ArrowUpRight, TrendingUp } from 'lucide-react';
import apiClient from '../../services/api';

export default function AdminDashboard() {
  const [stats, setStats] = useState({ studentsCount: 0, teachersCount: 0, classroomsCount: 0 });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function loadDashboardData() {
      try {
        // Appels alignés sur tes préfixes de routes api.php du backend : /v1/admin/...
        const [studentsRes, teachersRes, classroomsRes] = await Promise.all([
          apiClient.get('/v1/admin/students'),
          apiClient.get('/v1/admin/teachers'),
          apiClient.get('/v1/admin/classrooms').catch(() => ({ data: { data: [] } })) // évite le crash si vide
        ]);
        
        setStats({
          studentsCount: studentsRes.data?.data?.length || 0,
          teachersCount: teachersRes.data?.data?.length || 0,
          classroomsCount: classroomsRes.data?.data?.length || 0,
        });
      } catch (err) {
        console.error("Erreur lors de la récupération des métriques", err);
      } finally {
        setLoading(false);
      }
    }
    loadDashboardData();
  }, []);

  if (loading) {
    return (
      <div className="h-96 flex items-center justify-center">
        <div className="w-8 h-8 border-4 border-brand border-t-transparent rounded-full animate-spin" />
      </div>
    );
  }

  return (
    <div className="space-y-8">
      <div>
        <h1 className="text-2xl font-bold text-slate-900 tracking-tight">Vue d'ensemble Pro</h1>
        <p className="text-sm text-slate-500 mt-1">Données analytiques consolidées du réseau d'enseignement.</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Métrique Élèves */}
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex justify-between items-start hover:shadow-md transition-shadow">
          <div className="space-y-4">
            <div className="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center"><GraduationCap className="h-6 w-6" /></div>
            <div>
              <span className="text-sm font-medium text-slate-400 block">Élèves Enregistrés</span>
              <span className="text-3xl font-bold text-slate-900 tracking-tight">{stats.studentsCount}</span>
            </div>
          </div>
          <span className="text-xs bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-full font-medium flex items-center gap-1"><TrendingUp className="h-3 w-3" /> Live</span>
        </motion.div>

        {/* Métrique Enseignants */}
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.1 }} className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex justify-between items-start hover:shadow-md transition-shadow">
          <div className="space-y-4">
            <div className="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center"><Users className="h-6 w-6" /></div>
            <div>
              <span className="text-sm font-medium text-slate-400 block">Enseignants Actifs</span>
              <span className="text-3xl font-bold text-slate-900 tracking-tight">{stats.teachersCount}</span>
            </div>
          </div>
          <span className="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">Validés</span>
        </motion.div>

        {/* Métrique Salles */}
        <motion.div initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }} className="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex justify-between items-start hover:shadow-md transition-shadow">
          <div className="space-y-4">
            <div className="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center"><School className="h-6 w-6" /></div>
            <div>
              <span className="text-sm font-medium text-slate-400 block">Salles de Classes</span>
              <span className="text-3xl font-bold text-slate-900 tracking-tight">{stats.classroomsCount}</span>
            </div>
          </div>
          <span className="text-xs bg-purple-50 text-purple-600 px-2.5 py-1 rounded-full font-medium">Actives</span>
        </motion.div>
      </div>

      
    </div>
  );
}