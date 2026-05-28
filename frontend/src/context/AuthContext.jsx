import React, { createContext, useContext, useState, useEffect } from 'react';
import apiClient, { getStorageKey } from '../services/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Utilise la même clé que api.js (getStorageKey) pour la cohérence
    const token = localStorage.getItem(getStorageKey('token'));
    const savedUser = localStorage.getItem(getStorageKey('user'));

    if (token && savedUser) {
      try {
        apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        setUser(JSON.parse(savedUser));
      } catch {
        // JSON corrompu — on nettoie
        localStorage.removeItem(getStorageKey('token'));
        localStorage.removeItem(getStorageKey('user'));
      }
    }
    setLoading(false);
  }, []);

  const login = async (email, password) => {
    const response = await apiClient.post('/login', { email, password });
    
    // Backend renvoie : response.data.data = { token, user: { id, name, email, role } }
    const { token, user: userData } = response.data.data;

    localStorage.setItem(getStorageKey('token'), token);
    localStorage.setItem(getStorageKey('user'), JSON.stringify(userData));
    
    apiClient.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    setUser(userData);

    return userData;
  };

  const logout = async () => {
    try {
      // La baseURL inclut déjà /v1, donc l'endpoint est /logout (pas /v1/logout)
      await apiClient.post('/logout');
    } catch (err) {
      console.warn("Déconnexion côté serveur impossible ou déjà expirée");
    } finally {
      localStorage.removeItem(getStorageKey('token'));
      localStorage.removeItem(getStorageKey('user'));
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