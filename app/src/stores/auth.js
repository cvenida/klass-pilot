import { defineStore } from 'pinia'
import { loginUser, registerUser, logoutUser } from '@/services/authService'
import router from '@/router'
import { USER_TYPE } from '@/shared/constants'
import { useCourseStore } from '@/stores/courses'
import { useNotificationStore } from '@/stores/notification'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    getToken: (state) => state.token,
    currentUser: (state) => state.user,
    authError: (state) => state.error,
    isLoading: (state) => state.loading,
  },

  actions: {
    setSession(user, token) {
      this.user = user
      this.token = token
      this.error = null
    },

    clearSession() {
      this.user = null
      this.token = null
      this.error = null
    },

    async login(credentials) {
      const courseStore = useCourseStore();
      const notify = useNotificationStore();

      this.loading = true
      this.error = null

      try {
        const { data } = await loginUser(credentials)

        if (!data.status) {
          throw new Error(data.message || 'Invalid credentials')
        }

        this.setSession(data.data.user, data.data.access_token)
        await courseStore.fetchCourses();
        notify.success('Logged in successfully!')

        await router.push(data.data.user.type == USER_TYPE.TEACHER ? '/dashboard' : '/student/dashboard')
      } catch (err) {
        console.log(err)
      }

      this.loading = false
    },

    
    async signup(userData) {
      const notify = useNotificationStore();
      this.loading = true
      this.error = null

      try {
        const { data } = await registerUser(userData)

        if (!response.ok) {
          throw new Error('Failed to create account')
        }

        this.setSession(data.data.user, data.data.access_token)
        notify.success('Logged out successfully!')
        await router.push('/dashboard')
      } catch (error) {
        console.log(error)
      }
      this.loading = false
    },

    async logout() {
      const notify = useNotificationStore();
      await logoutUser();

      this.clearSession()
      notify.success('Logged out successfully!')
      await router.push('/login')
    },
  },

  persist: {
    key: 'auth',
    storage: localStorage,
  },
})