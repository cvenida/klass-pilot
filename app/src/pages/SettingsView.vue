<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import {
  User,
  Bell,
  ShieldCheck,
  Palette,
  Save,
  Check,
  Lock,
  Moon,
  Sun,
  LogOut
} from 'lucide-vue-next'
import { useDisplay, useTheme } from 'vuetify'
import { useAuthStore } from '@/stores/auth'

// Save Button Loading State
const isSaving = ref(false)
const showSuccessAlert = ref(false)

const { mobile } = useDisplay()
const authStore = useAuthStore()
const theme = useTheme()

// Editable local profile state synced initialized from authStore
const accountForm = ref({
  fullName: computed({
    get: () => {
      const user = authStore.currentUser
      if (!user) return ''
      return `${user.first_name || ''} ${user.last_name || ''}`.trim()
    },
    set: (val) => {
      if (authStore.currentUser) authStore.currentUser.fullName = val
    }
  }),
  email: computed({
    get: () => authStore.currentUser?.email || '',
    set: (val) => {
      if (authStore.currentUser) authStore.currentUser.email = val
    }
  }),
  role: computed(() => authStore.currentUser?.role || authStore.currentUser?.type)
})

const settings = ref({
  account: accountForm,
  notifications: {
    emailDigest: true,
    learningStrandSubmissions: true,
    systemUpdates: false,
    marketingEmails: false
  },
  security: {
    twoFactor: false,
    sessionTimeoutMinutes: 30
  },
  appearance: {
    theme: 'system',
    denseMode: false
  }
})

const applyTheme = (themeMode) => {
  let isDark = false

  if (themeMode === 'system') {
    isDark = window.matchMedia('(prefers-color-scheme: dark)').matches
  } else {
    isDark = themeMode === 'dark'
  }

  theme.global.name.value = isDark ? 'dark' : 'light'

  if (isDark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }

  localStorage.setItem('user-theme', themeMode)
}

watch(
  () => settings.value.appearance.theme,
  (newTheme) => {
    applyTheme(newTheme)
  }
)

onMounted(() => {
  const savedTheme = localStorage.getItem('user-theme') || 'system'
  settings.value.appearance.theme = savedTheme
  applyTheme(savedTheme)

  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (settings.value.appearance.theme === 'system') {
      applyTheme('system')
    }
  })
})

const handleSaveSettings = async () => {
  isSaving.value = true
  try {
    await new Promise((resolve) => setTimeout(resolve, 800))
    showSuccessAlert.value = true
    setTimeout(() => {
      showSuccessAlert.value = false
    }, 3000)
  } finally {
    isSaving.value = false
  }
}

const handleLogout = () => {
  if (authStore.logout) {
    authStore.logout()
  }
}
</script>

<template>
  <div class="h-full">
    <div class="p-6 w-full max-w-5xl mx-auto">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <v-btn
          color="primary"
          rounded="lg"
          class="capitalize font-semibold text-white px-6 shadow-sm"
          :loading="isSaving"
          block
          @click="handleSaveSettings"
        >
          <template #prepend>
            <Save class="size-4 mr-1" />
          </template>
          Save Changes
        </v-btn>
      </div>

      <v-slide-y-transition>
        <div
          v-if="showSuccessAlert"
          class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-300 dark:border-emerald-800 flex items-center justify-between"
        >
          <div class="flex items-center gap-3 text-emerald-900 dark:text-emerald-200 text-sm font-semibold">
            <div class="size-7 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0">
              <Check class="size-4" />
            </div>
            <span>Your settings have been successfully updated.</span>
          </div>
        </div>
      </v-slide-y-transition>

      <v-card flat class="p-6 border border-neutral-300 dark:border-neutral-700 rounded-2xl bg-white dark:bg-neutral-900 space-y-8">
        <div class="space-y-6">
          <div class="flex items-center gap-2">
            <User class="size-5 text-primary shrink-0" />
            <div>
              <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Profile Information</h2>
              <p class="text-xs text-neutral-700 dark:text-neutral-300 font-medium mt-0.5">
                Update your personal info and email address.
              </p>
            </div>
          </div>

          <v-divider class="border-neutral-200 dark:border-neutral-700" />

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <v-text-field
              v-model="settings.account.fullName"
              label="Full Name"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              hide-details
            />

            <v-text-field
              v-model="settings.account.email"
              label="Email Address"
              type="email"
              variant="outlined"
              density="comfortable"
              rounded="lg"
              hide-details
            />
          </div>

          <v-text-field
            v-model="settings.account.role"
            label="Role"
            variant="outlined"
            density="comfortable"
            rounded="lg"
            disabled
            hint="Roles are managed by system administrators."
            persistent-hint
          />
        </div>

        <div class="space-y-6">
          <div class="flex items-center gap-2">
            <ShieldCheck class="size-5 text-primary shrink-0" />
            <div>
              <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Authentication & Security</h2>
              <p class="text-xs text-neutral-700 dark:text-neutral-300 font-medium mt-0.5">
                Manage your password and security options.
              </p>
            </div>
          </div>

          <div>
            <div class="p-4 rounded-xl border border-neutral-200 dark:border-neutral-700 space-y-3">
              <p class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Change Password</p>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <v-text-field
                  label="Current Password"
                  type="password"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  hide-details
                />
                <v-text-field
                  label="New Password"
                  type="password"
                  variant="outlined"
                  density="compact"
                  rounded="lg"
                  hide-details
                />
              </div>
            </div>
          </div>
        </div>

        <section class="space-y-6">
          <div class="flex items-center gap-2">
            <Palette class="size-5 text-primary shrink-0" />
            <div>
              <h2 class="text-base font-bold text-neutral-900 dark:text-neutral-100">Interface Preference</h2>
              <p class="text-xs text-neutral-700 dark:text-neutral-300 font-medium mt-0.5">
                Customize layout density and view themes.
              </p>
            </div>
          </div>

          <v-divider class="border-neutral-200 dark:border-neutral-700" />

          <div class="space-y-4">
            <p class="text-xs font-bold text-neutral-900 dark:text-neutral-100">Theme Preference</p>
            <div class="grid grid-cols-3 gap-3">
              <button
                @click="settings.appearance.theme = 'light'"
                :class="[
                  'p-3 rounded-xl border text-center transition-all flex flex-col items-center gap-2',
                  settings.appearance.theme === 'light'
                    ? 'border-primary bg-primary/5 text-primary'
                    : 'border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800'
                ]"
              >
                <Sun class="size-5" />
                <span class="text-xs font-bold">Light</span>
              </button>

              <button
                @click="settings.appearance.theme = 'dark'"
                :class="[
                  'p-3 rounded-xl border text-center transition-all flex flex-col items-center gap-2',
                  settings.appearance.theme === 'dark'
                    ? 'border-primary bg-primary/5 text-primary'
                    : 'border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800'
                ]"
              >
                <Moon class="size-5" />
                <span class="text-xs font-bold">Dark</span>
              </button>

              <button
                @click="settings.appearance.theme = 'system'"
                :class="[
                  'p-3 rounded-xl border text-center transition-all flex flex-col items-center gap-2',
                  settings.appearance.theme === 'system'
                    ? 'border-primary bg-primary/5 text-primary'
                    : 'border-neutral-300 dark:border-neutral-700 text-neutral-800 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800'
                ]"
              >
                <Palette class="size-5" />
                <span class="text-xs font-bold">System</span>
              </button>
            </div>
          </div>
        </section>
      </v-card>

      <v-btn
        v-if="mobile"
        color="error"
        variant="outlined"
        rounded="lg"
        block
        class="capitalize font-semibold px-4 mt-4"
        @click="handleLogout"
      >
        <template #prepend>
          <LogOut class="size-4 mr-1" />
        </template>
        Logout
      </v-btn>
    </div>
  </div>
</template>