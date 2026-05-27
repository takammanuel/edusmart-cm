import axios from 'axios';
import { environment } from '../environments/environment';

// 1. Génération ou récupération de l'identifiant unique d'onglet (sessionStorage)
const getTabId = () => {
  if (typeof window !== 'undefined') {
    let tabId = sessionStorage.getItem('tabId');
    if (!tabId) {
      tabId = 'tab_' + Date.now() + '_' + Math.random().toString(36).substring(2, 11);
      sessionStorage.setItem('tabId', tabId);
    }
    return tabId;
  }
  return 'default';
};

export const tabId = getTabId();

// 2. Clé dynamique pour le localStorage isolée par onglet
export const getStorageKey = (key) => `${tabId}_${key}`;

// 3. Création de l'instance Axios globale
const apiClient = axios.create({
  baseURL: environment.apiUrl,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Intercepteur pour ajouter dynamiquement le Token Bearer de l'onglet courant
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem(getStorageKey('token'));
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Intercepteur de réponse pour intercepter les erreurs 401 (Non autorisé)
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      localStorage.removeItem(getStorageKey('token'));
      localStorage.removeItem(getStorageKey('user'));
      localStorage.removeItem(getStorageKey('etablissement'));
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default apiClient;