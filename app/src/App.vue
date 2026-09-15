<template>
  <v-app class="min-h-screen">
    <NavBar v-if="shouldShowNavBar" />
    <v-main>
      <router-view />
    </v-main>
  </v-app>
</template>

<script lang="ts" setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/components/NavBar.vue'
import { useAuthStore } from '@/stores/auth'
import { applyTheme } from '@/shared/constants'

const authStore = useAuthStore()
const route = useRoute()

const excludedRoutes = ['/login', '/register', '/settings']

const shouldShowNavBar = computed(() => {
  return authStore.isAuthenticated && !excludedRoutes.includes(route.path)
})

onMounted(() => {
  const savedTheme = localStorage.getItem('user-theme') || 'system'
  applyTheme(savedTheme)

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    applyTheme('system')
  })
})
</script>