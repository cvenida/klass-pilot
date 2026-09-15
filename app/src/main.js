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
      authStore.logout()
      return Promise.reject(new Error(response.data.message || 'Token expired'))
    }
    return response
  },
  async (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      await authStore.logout()
    }
    return Promise.reject(error)
  }
)

app.mount('#app')