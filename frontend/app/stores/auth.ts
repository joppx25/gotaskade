import { defineStore } from 'pinia'

interface User {
  id: number
  email: string
  name: string
}

interface AuthState {
  user: User | null
  isAuthenticated: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    isAuthenticated: false,
  }),

  actions: {
    async login(email: string, password: string) {
      const api = useApi()

      const response = await api.post<{ data: User }>('/login', { email, password })

      this.user = response.data
      this.isAuthenticated = true
    },

    async logout() {
      const api = useApi()

      try {
        await api.post('/logout')
      }
      catch {
        // Even if the API call fails, clear local state
      }

      this.user = null
      this.isAuthenticated = false
    },

    async fetchUser() {
      const api = useApi()

      try {
        const response = await api.get<{ data: User }>('/user')
        this.user = response.data
        this.isAuthenticated = true
      }
      catch {
        this.user = null
        this.isAuthenticated = false
      }
    },
  },

  getters: {
    userInitials: (state): string => {
      if (!state.user) return ''
      return state.user.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    },
  },
})
