import React, { createContext, useContext, useState, useEffect } from 'react';
import apiClient from '../services/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('edu_token');
    const savedUser = localStorage.getItem('edu_user');

    if (token && savedUser) {
      apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      setUser(JSON.parse(savedUser));
    }
    setLoading(false);
  }, []);

  const login = async (email, password) => {
    // Appel direct sur ton endpoint mis à jour : /v1/login
    const response = await apiClient.post('/login', { email, password });
    
    // Ton backend renvoie : response.data.data = { token, user: { id, name, email, role } }
    const { token, user: userData } = response.data.data;

    localStorage.setItem('edu_token', token);
    localStorage.setItem('edu_user', JSON.stringify(userData));
    
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    setUser(userData);

    return userData; // Permet de récupérer le rôle dans l'écran de Login pour la redirection
  };

  const logout = async () => {
    try {
      await apiClient.post('/v1/logout');
    } catch (err) {
      console.warn("Déconnexion côté serveur impossible ou déjà expirée");
    } finally {
      localStorage.removeItem('edu_token');
      localStorage.removeItem('edu_user');
      delete apiClient.defaults.headers.common['Authorization'];
      setUser(null);
    }
  };

  return (
    <AuthContext.Provider value={{ user, login, logout, loading, isAuthenticated: !!user }}>
      {!loading && children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);