import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../../context/AuthContext';
import { ShieldAlert, Eye, EyeOff, Lock, Mail, Loader2, ArrowLeft } from 'lucide-react';

export default function LoginPage() {
  const { login } = useAuth();
  const navigate = useNavigate();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const userData = await login(email, password);
      
      // Redirection dynamique basée sur ton énumération de rôle backend
      if (userData.role === 'admin') {
        navigate('/admin/dashboard');
      } else if (userData.role === 'enseignant') {
        navigate('/teacher/grades');
      } else {
        setError("Votre compte n'a pas les autorisations nécessaires pour accéder aux portails.");
      }
    } catch (err) {
      setError(err.response?.data?.message || "Identifiants incorrects ou serveur injoignable.");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
      {/* Bouton Retour Home */}
      <div className="absolute top-6 left-6">
        <Link to="/" className="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors">
          <ArrowLeft className="h-4 w-4" /> Retour à l'accueil
        </Link>
      </div>

      <div className="sm:mx-auto w-full max-w-md text-center space-y-2">
        <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight">Portail Authentifié</h2>
        <p className="text-sm text-slate-500">Saisissez vos accès fournis par la direction de votre établissement.</p>
      </div>

      <div className="mt-8 sm:mx-auto w-full max-w-md">
        <div className="bg-white py-8 px-4 shadow-xl border border-slate-100 sm:rounded-2xl sm:px-10">
          
          {error && (
            <div className="mb-6 bg-rose-50 border border-rose-100 rounded-xl p-4 flex items-start gap-3 text-rose-700 text-sm animate-shake">
              <ShieldAlert className="h-5 w-5 shrink-0 text-rose-500" />
              <span>{error}</span>
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-2">Adresse Email</label>
              <div className="relative rounded-xl shadow-sm">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Mail className="h-5 w-5" />
                </div>
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="nom@edusmart.cm"
                  className="block w-full pl-11 pr-4 py-3.5 border border-slate-200 rounded-xl text-slate-900 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-brand focus:border-brand transition-all text-sm"
                />
              </div>
            </div>

            <div>
              <label className="block text-sm font-semibold text-slate-700 mb-2">Mot de passe</label>
              <div className="relative rounded-xl shadow-sm">
                <div className="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <Lock className="h-5 w-5" />
                </div>
                <input
                  type={showPassword ? "text" : "password"}
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  placeholder="••••••••"
                  className="block w-full pl-11 pr-12 py-3.5 border border-slate-200 rounded-xl text-slate-900 bg-slate-50/50 focus:bg-white focus:ring-2 focus:ring-brand focus:border-brand transition-all text-sm"
                />
                <button
                  type="button"
                  onClick={() => setShowPassword(!showPassword)}
                  className="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                >
                  {showPassword ? <EyeOff className="h-5 w-5" /> : <Eye className="h-5 w-5" />}
                </button>
              </div>
            </div>

            <button
              type="submit"
              disabled={loading}
              className="w-full flex justify-center items-center gap-2 bg-brand hover:bg-brand-hover text-white py-3.5 px-4 rounded-xl font-bold shadow-lg shadow-brand/10 transition-all active:scale-95 disabled:opacity-50 disabled:pointer-events-none text-sm"
            >
              {loading ? (
                <>
                  <Loader2 className="h-4 w-4 animate-spin" /> Verifications des clés...
                </>
              ) : "Valider l'accès"}
            </button>
          </form>

        </div>
      </div>
    </div>
  );
}