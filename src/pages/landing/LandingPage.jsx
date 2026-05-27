import React from 'react';
import { motion } from 'framer-motion';
import { Link } from 'react-router-dom';
import { ShieldCheck, Zap, LayoutDashboard, GraduationCap, BookOpen, Menu, X, ArrowRight, Shield, Globe } from 'lucide-react';

// Wrapper Local du Layout Public pour intégrer le Link de navigation
function PublicLayout({ children }) {
  const [isScrolled, setIsScrolled] = React.useState(false);
  const [isMobileMenuOpen, setIsMobileMenuOpen] = React.useState(false);

  React.useEffect(() => {
    const handleScroll = () => setIsScrolled(window.scrollY > 20);
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
            <Link to="/login" className="flex items-center gap-2 bg-brand hover:bg-brand-hover text-white px-5 py-2.5 rounded-xl font-semibold shadow-lg shadow-brand/10 hover:shadow-brand/20 transition-all active:scale-95">
              Espace Connexion
              <ArrowRight className="h-4 w-4" />
            </Link>
          </div>

          {/* Mobile Toggle */}
          <button className="md:hidden text-slate-700" onClick={() => setIsMobileMenuOpen(!isMobileMenuOpen)}>
            {isMobileMenuOpen ? <X className="h-6 w-6" /> : <Menu className="h-6 w-6" />}
          </button>
        </div>
      </motion.nav>

      {/* Mobile Menu */}
      <div className={`fixed inset-x-0 top-[76px] p-6 glass-effect z-40 md:hidden shadow-xl border-b border-slate-200 transition-all duration-300 ${isMobileMenuOpen ? 'opacity-100 translate-y-0' : 'opacity-0 -translate-y-4 pointer-events-none'}`}>
        <div className="flex flex-col gap-4 font-medium text-slate-700">
          <a href="#features" onClick={() => setIsMobileMenuOpen(false)} className="py-2 hover:text-brand">Fonctionnalités</a>
          <a href="#stats" onClick={() => setIsMobileMenuOpen(false)} className="py-2 hover:text-brand">Statistiques</a>
          <Link to="/login" onClick={() => setIsMobileMenuOpen(false)} className="w-full flex items-center justify-center gap-2 bg-brand text-white py-3 rounded-xl font-semibold">
            Espace Connexion
            <ArrowRight className="h-4 w-4" />
          </Link>
        </div>
      </div>

      <main className="pt-24">{children}</main>

      {/* Footer */}
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

const containerVariants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1, transition: { staggerChildren: 0.15 } }
};

const itemVariants = {
  hidden: { y: 30, opacity: 0 },
  visible: { y: 0, opacity: 1, transition: { duration: 0.6, ease: "easeOut" } }
};

export default function LandingPage() {
  return (
    <PublicLayout>
      {/* Hero Section */}
      <section className="relative overflow-hidden px-6 pt-12 pb-24 md:py-32 max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        <div className="absolute top-0 right-1/4 w-96 h-96 bg-brand/5 rounded-full blur-3xl -z-10" />
        <div className="absolute bottom-10 left-10 w-72 h-72 bg-brand-accent/5 rounded-full blur-3xl -z-10" />

        <motion.div 
          className="lg:col-span-7 space-y-6 text-center lg:text-left"
          initial={{ opacity: 0, x: -50 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.8 }}
        >
          <div className="inline-flex items-center gap-2 bg-brand/5 border border-brand/10 text-brand font-semibold px-4 py-1.5 rounded-full text-sm">
            <ShieldCheck className="h-4 w-4" /> Plateforme Nationale EDUSMART-CM
          </div>
          <h1 className="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900 leading-[1.1]">
            L'excellence numérique pour le pilotage de nos <span className="text-brand">Lycées</span>.
          </h1>
          <p className="text-lg text-slate-600 max-w-2xl mx-auto lg:mx-0 leading-relaxed">
            Une architecture unifiée et innovante pensée pour la gestion des inscriptions, des notes et du suivi en temps réel des établissements scolaires secondaires.
          </p>
          <div className="flex flex-col sm:flex-row justify-center lg:justify-start gap-4 pt-4">
            <Link to="/login" className="bg-brand hover:bg-brand-hover text-white text-base font-semibold px-8 py-4 rounded-xl shadow-xl shadow-brand/15 hover:shadow-brand/25 transition-all transform hover:-translate-y-0.5 text-center active:scale-95">
              Accéder au Portail Académique
            </Link>
            <a href="#features" className="bg-white border border-slate-200 hover:border-slate-300 text-slate-700 font-semibold px-8 py-4 rounded-xl shadow-sm text-center transition-all transform hover:-translate-y-0.5">
              Découvrir les Modules
            </a>
          </div>
        </motion.div>

        {/* Illustration Visuelle */}
        <motion.div 
          className="lg:col-span-5 relative flex justify-center"
          initial={{ opacity: 0, scale: 0.9 }}
          animate={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.8, delay: 0.2 }}
        >
          <div className="relative w-full max-w-[450px] aspect-square rounded-3xl bg-gradient-to-tr from-brand to-brand-accent p-1 shadow-2xl shadow-brand/20">
            <div className="w-full h-full bg-slate-900 rounded-[22px] overflow-hidden p-6 relative flex flex-col justify-between text-white">
              <div className="flex items-center justify-between border-b border-slate-800 pb-4">
                <div className="flex gap-1.5">
                  <div className="w-3 h-3 rounded-full bg-rose-500" />
                  <div className="w-3 h-3 rounded-full bg-amber-500" />
                  <div className="w-3 h-3 rounded-full bg-emerald-500" />
                </div>
                <div className="text-xs bg-slate-800 px-3 py-1 rounded-md text-slate-400">edu-smart.cm/dashboard</div>
              </div>
              
              <div className="space-y-4 flex-1 pt-6">
                <div className="h-8 bg-slate-800/60 rounded-lg w-2/3" />
                <div className="grid grid-cols-3 gap-3">
                  <div className="h-16 bg-brand/20 border border-brand/30 rounded-xl p-3 flex flex-col justify-between">
                    <div className="w-4 h-4 rounded bg-brand" />
                    <div className="h-2 bg-slate-700 rounded w-3/4" />
                  </div>
                  <div className="h-16 bg-slate-800/40 rounded-xl p-3 flex flex-col justify-between">
                    <div className="w-4 h-4 rounded bg-slate-700" />
                    <div className="h-2 bg-slate-700 rounded w-1/2" />
                  </div>
                  <div className="h-16 bg-slate-800/40 rounded-xl p-3 flex flex-col justify-between">
                    <div className="w-4 h-4 rounded bg-slate-700" />
                    <div className="h-2 bg-slate-700 rounded w-2/3" />
                  </div>
                </div>
                <div className="h-24 bg-slate-800/40 rounded-xl p-4 space-y-2">
                  <div className="h-3 bg-slate-700 rounded w-full" />
                  <div className="h-3 bg-slate-700 rounded w-5/6" />
                </div>
              </div>
            </div>
          </div>
        </motion.div>
      </section>

      {/* Section Statistiques */}
      <section id="stats" className="bg-white border-y border-slate-100 py-16">
        <div className="max-w-7xl mx-auto px-6">
          <motion.div 
            variants={containerVariants}
            initial="hidden"
            whileInView="visible"
            viewport={{ once: true }}
            className="grid grid-cols-2 md:grid-cols-4 gap-8 text-center"
          >
            <motion.div variants={itemVariants} className="space-y-2">
              <h3 className="text-4xl md:text-5xl font-extrabold text-brand-dark">150</h3>
              <p className="text-sm font-medium text-slate-500 uppercase tracking-wider">Lycées Pilotes</p>
            </motion.div>
            <motion.div variants={itemVariants} className="space-y-2">
              <h3 className="text-4xl md:text-5xl font-extrabold text-brand-dark">10</h3>
              <p className="text-sm font-medium text-slate-500 uppercase tracking-wider">Régions Couvertes</p>
            </motion.div>
            <motion.div variants={itemVariants} className="space-y-2">
              <h3 className="text-4xl md:text-5xl font-extrabold text-brand-dark">Offline</h3>
              <p className="text-sm font-medium text-slate-500 uppercase tracking-wider">First natif</p>
            </motion.div>
            <motion.div variants={itemVariants} className="space-y-2">
              <h3 className="text-4xl md:text-5xl font-extrabold text-brand-dark">&lt; 3s</h3>
              <p className="text-sm font-medium text-slate-500 uppercase tracking-wider">Temps de charge (3G)</p>
            </motion.div>
          </motion.div>
        </div>
      </section>

      {/* Section Fonctionnalités */}
      <section id="features" className="max-w-7xl mx-auto px-6 py-24 space-y-16">
        <div className="text-center space-y-4 max-w-2xl mx-auto">
          <h2 className="text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
            Une infrastructure pensée pour les exigences de terrain.
          </h2>
          <p className="text-slate-600">
            Conçu pour répondre aux défis spécifiques de connectivité et de performance du secteur éducatif camerounais.
          </p>
        </div>

        <motion.div 
          variants={containerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true }}
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <motion.div variants={itemVariants} className="bg-white p-8 rounded-2xl border border-slate-100 shadow-premium hover:shadow-xl transition-all hover:-translate-y-1 group">
            <div className="w-12 h-12 rounded-xl bg-brand/5 text-brand flex items-center justify-center mb-6 group-hover:bg-brand group-hover:text-white transition-all">
              <LayoutDashboard className="h-6 w-6" />
            </div>
            <h3 className="text-xl font-bold text-slate-900 mb-2">Module Administration</h3>
            <p className="text-slate-600 text-sm leading-relaxed">
              Inscriptions, structuration des classes, gestion du personnel et édition automatisée des bulletins au format PDF.
            </p>
          </motion.div>

          <motion.div variants={itemVariants} className="bg-white p-8 rounded-2xl border border-slate-100 shadow-premium hover:shadow-xl transition-all hover:-translate-y-1 group">
            <div className="w-12 h-12 rounded-xl bg-brand/5 text-brand flex items-center justify-center mb-6 group-hover:bg-brand group-hover:text-white transition-all">
              <GraduationCap className="h-6 w-6" />
            </div>
            <h3 className="text-xl font-bold text-slate-900 mb-2">Espace Enseignant</h3>
            <p className="text-slate-600 text-sm leading-relaxed">
              Saisie simplifiée des notes par classe, appels numériques, absences avec motifs et progression des cours.
            </p>
          </motion.div>

          <motion.div variants={itemVariants} className="bg-white p-8 rounded-2xl border border-slate-100 shadow-premium hover:shadow-xl transition-all hover:-translate-y-1 group">
            <div className="w-12 h-12 rounded-xl bg-brand/5 text-brand flex items-center justify-center mb-6 group-hover:bg-brand group-hover:text-white transition-all">
              <Zap className="h-6 w-6" />
            </div>
            <h3 className="text-xl font-bold text-slate-900 mb-2">Technologie Offline-First</h3>
            <p className="text-slate-600 text-sm leading-relaxed">
              Saisissez vos données même sans couverture réseau, la synchronisation se déclenche automatiquement au retour de la connexion.
            </p>
          </motion.div>
        </motion.div>
      </section>

      {/* Section CTA */}
      <section className="max-w-7xl mx-auto px-6 pb-24">
        <div className="bg-gradient-to-r from-brand-dark to-brand rounded-3xl p-8 md:p-16 text-center text-white relative overflow-hidden shadow-xl">
          <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.12),transparent)] pointer-events-none" />
          <div className="max-w-2xl mx-auto space-y-6 relative z-10">
            <h2 className="text-3xl md:text-4xl font-bold tracking-tight">Prêt à moderniser la gestion de votre établissement ?</h2>
            <p className="text-blue-100 text-sm md:text-base">
              Connectez-vous dès à présent pour accéder à vos classes, planifier vos cours ou piloter vos indicateurs clés de performance.
            </p>
            <div className="pt-4">
              <Link to="/login" className="bg-white text-brand-dark hover:bg-slate-50 font-bold px-8 py-4 rounded-xl transition-all shadow-lg inline-block active:scale-95">
                Accéder à l'Espace Sécurisé
              </Link>
            </div>
          </div>
        </div>
      </section>
    </PublicLayout>
  );
}