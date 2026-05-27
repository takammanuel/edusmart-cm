import React, { useState, useEffect } from 'react';
import { BookOpen, Menu, X, ArrowRight, Shield, Globe } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

export default function PublicLayout({ children }) {
  const [isScrolled, setIsScrolled] = useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setIsScrolled(window.scrollY > 20);
    };
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  return (
    <div className="min-h-screen bg-surface-canvas text-slate-900 selection:bg-brand/20">
      {/* Navbar Premium Glassmorphism */}
      <motion.nav 
        initial={{ y: -20, opacity: 0 }}
        animate={{ y: 0, opacity: 1 }}
        className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${
          isScrolled ? 'glass-effect py-4 shadow-premium' : 'bg-transparent py-6'
        }`}
      >
        <div className="max-w-7xl mx-auto px-6 flex justify-between items-center">
          <div className="flex items-center gap-3">
            <div className="bg-brand text-white p-2.5 rounded-xl shadow-md shadow-brand/20">
              <BookOpen className="h-6 w-6" />
            </div>
            <div>
              <span className="text-xl font-bold tracking-tight text-brand-dark">EduSmart</span>
              <span className="text-xs block text-brand font-semibold tracking-widest -mt-1">CM</span>
            </div>
          </div>

          {/* Desktop Menu */}
          <div className="hidden md:flex items-center gap-8 font-medium text-slate-600">
            <a href="#features" className="hover:text-brand transition-colors">Fonctionnalités</a>
            <a href="#stats" className="hover:text-brand transition-colors">Statistiques</a>
            <a href="#propos" className="hover:text-brand transition-colors">À Propos</a>
            <button className="flex items-center gap-2 bg-brand hover:bg-brand-hover text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-brand/10 hover:shadow-brand/20 transition-all active:scale-95">
              Espace Connexion
              <ArrowRight className="h-4 w-4" />
            </button>
          </div>

          {/* Mobile Toggle */}
          <button className="md:hidden text-slate-700" onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}>
            {isMobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        </div>
      </motion.nav>

      {/* Mobile Menu */}
      <AnimatePresence>
        {isMobileMenuOpen && (
          <motion.div 
            initial={{ opacity: 0, y: -10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -10 }}
            className="fixed inset-x-0 top-[76px] p-6 glass-effect z-40 md:hidden shadow-xl border-b border-slate-200"
          >
            <div className="flex flex-col gap-4 font-medium text-slate-700">
              <a href="#features" onClick={() => setIsMobileMenuOpen(false)} className="py-2 hover:text-brand">Fonctionnalités</a>
              <a href="#stats" onClick={() => setIsMiddleMenuOpen(false)} className="py-2 hover:text-brand">Statistiques</a>
              <a href="#propos" onClick={() => setIsMobileMenuOpen(false)} className="py-2 hover:text-brand">À Propos</a>
              <button className="w-full flex items-center justify-center gap-2 bg-brand text-white py-3 rounded-xl font-semibold">
                Espace Connexion
                <ArrowRight className="h-4 w-4" />
              </button>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Main Content */}
      <main className="pt-24">{children}</main>

      {/* Footer Premium */}
      <footer className="bg-slate-900 text-slate-400 py-16 border-t border-slate-800">
        <div className="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-12">
          <div className="space-y-4">
            <div className="flex items-center gap-3 text-white">
              <div className="bg-brand p-2 rounded-xl"><BookOpen className="h-5 w-5" /></div>
              <span className="text-lg font-bold">EduSmart-CM</span>
            </div>
            <p className="text-sm text-slate-400 leading-relaxed">
              Plateforme moderne de gestion des résultats scolaires et du suivi éducatif au Cameroun.
            </p>
          </div>
          <div>
            <h4 className="text-white font-semibold mb-4">Liens Utiles</h4>
            <ul className="space-y-2.5 text-sm">
              <li><a href="#features" className="hover:text-white transition-colors">Fonctionnalités</a></li>
              <li><a href="#stats" className="hover:text-white transition-colors">Statistiques</a></li>
            </ul>
          </div>
          <div>
            <h4 className="text-white font-semibold mb-4">Souveraineté & Cadre</h4>
            <div className="space-y-3 text-xs">
              <p className="flex items-center gap-2"><Shield className="h-4 w-4 text-emerald-400 shrink-0" /> Conforme Loi n°2010/012.</p>
              <p className="flex items-center gap-2"><Globe className="h-4 w-4 text-blue-400 shrink-0" /> Hébergement Souverain Localisé.</p>
            </div>
          </div>
          <div>
            <h4 className="text-white font-semibold mb-4">Déploiement National</h4>
            <p className="text-xs leading-relaxed text-slate-500">
              Projet pilote déployé au sein des lycées des 10 régions administratives du territoire national.
            </p>
          </div>
        </div>
        <div className="max-w-7xl mx-auto px-6 mt-12 pt-6 border-t border-slate-800 text-center text-xs text-slate-500">
          © 2026 EduSmart-CM. Tous droits réservés. Conçu par NEXATEC SOLUTIONS SARL.
        </div>
      </footer>
    </div>
  );
}