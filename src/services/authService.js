import apiClient, { getStorageKey } from './api';

export const authService = {
  /**
   * Connexion de l'utilisateur
   */
  async login(credentials) {
    // Note: l'URL cible finale sera : http://localhost:8000/api/v1/login
    const response = await apiClient.post('/login', credentials);
    return response.data;
  },

  /**
   * Déconnexion
   */
  async logout() {
    try {
      await apiClient.post('/logout', {});
    } finally {
      localStorage.removeItem(getStorageKey('token'));
      localStorage.removeItem(getStorageKey('user'));
      localStorage.removeItem(getStorageKey('etablissement'));
    }
  },

  /**
   * Récupérer le profil actuel
   */
  async me() {
    const response = await apiClient.get('/me');
    return response.data;
  }
};