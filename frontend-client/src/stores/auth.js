import { defineStore } from 'pinia';
import axios from 'axios';

// Set global defaults for Axios (Best done in main.js or a dedicated axios.js file)
axios.defaults.baseURL = 'http://localhost:8000/api'; 
axios.defaults.headers.common['Accept'] = 'application/json';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
  },
  actions: {
    async login(credentials) {
      try {
        // Updated to /login (assuming baseURL has /api)
        const { data } = await axios.post('/login', credentials);
        
        // Match the key 'token' returned by our Laravel Controller
        this.token = data.token; 
        this.user = data.user;
        
        localStorage.setItem('token', data.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;
        
        return true;
      } catch (error) {
        // Use optional chaining to avoid crashing if there's no response
        throw error.response?.data || { message: 'Network Error' };
      }
    },

    // NEW: Action to get user info after a page refresh
    async fetchUser() {
      if (!this.token) return;
      try {
        const { data } = await axios.get('/profile');
        this.user = data;
      } catch (error) {
        this.logout(); // If token is invalid, log out
      }
    },

    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];
    }
  }
});