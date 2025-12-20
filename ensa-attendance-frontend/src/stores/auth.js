import { defineStore } from 'pinia'
import api from '../api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null
  }),

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null

      try {
        const res = await api.post('/login', credentials)

        this.token = res.data.token
        this.user = res.data.utilisateur

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))

      } catch (err) {
        console.error(err)
        this.error = 'Email ou mot de passe incorrect'
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }
})
