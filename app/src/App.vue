<template>
  <v-app class="min-h-screen">
    <NavBar v-if="shouldShowNavBar" />
    
    <v-main>
      <router-view />
    </v-main>

    <v-snackbar
      v-model="notificationStore.show"
      :color="notificationStore.color"
      :timeout="notificationStore.timeout"
      location="bottom center"
      variant="elevated"
      elevation="4"
      rounded="lg"
    >
      <div class="d-flex align-center gap-2">
        <v-icon :icon="iconMap[notificationStore.color]" />
        <span>{{ notificationStore.message }}</span>
      </div>

      <template #actions>
        <v-btn
          icon="mdi-close"
          variant="text"
          density="comfortable"
          @click="notificationStore.show = false"
        />
      </template>
    </v-snackbar>
  </v-app>
</template>

<script lang="ts" setup>
import { computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import NavBar from '@/components/NavBar.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { applyTheme } from '@/shared/constants'

const authStore = useAuthStore()
const notificationStore = useNotificationStore()
const route = useRoute()

const excludedRoutes = ['/login', '/register']

const iconMap = {
  success: 'mdi-check-circle',
  error: 'mdi-alert-circle',
  warning: 'mdi-alert',
  info: 'mdi-information',
}

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