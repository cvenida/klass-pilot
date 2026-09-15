/**
 * main.js
 *
 * Bootstraps Vuetify and other plugins then mounts the App
 */

// Composables
import { createApp } from 'vue'

// Plugins
import { registerPlugins } from '@/plugins'

// Services & Stores
import { useAuthStore } from '@/stores/auth.js'

// Components
import App from './App.vue'
import axios from 'axios'

// Styles
import 'unfonts.css'
import './styles/tailwind.css'
import './styles/main.scss'

const app = createApp(App)

registerPlugins(app)

axios.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore()
    if (authStore.getToken) {
      config.headers.Authorization = `Bearer ${authStore.getToken}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

axios.interceptors.response.use(
  (response) => {
    if (
      response.data?.code === 401 ||
      response.data?.message === 'Token has expired.'
    ) {
      const authStore = useAuthStore()
      authStore.logout(true)
      return Promise.reject(new Error(response.data?.message || 'Token has expired.'))
    }
    return response
  },
  async (error) => {
    const authStore = useAuthStore()
    console.log('ERROR', error)

    // 1. Standard HTTP 401 error response from server
    if (error.response?.status === 401) {
      await authStore.logout()
      const message = error.response?.data?.message || 'Token has expired.'
      return Promise.reject(new Error(message))
    }

    // 2. Handling errors where error.response is undefined (Network error / CORS / Dropped request)
    if (!error.response) {
      // Optional: auto-logout on network errors if unauthenticated
      if (error.message?.includes('401')) {
        await authStore.logout()
      }
      return Promise.reject(new Error(error.message || 'Network Error or Server Unreachable'))
    }

    return Promise.reject(error)
  }
)

app.mount('#app')