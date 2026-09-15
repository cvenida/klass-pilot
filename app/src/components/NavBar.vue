<script lang="ts" setup>
import { ref, computed } from 'vue'
import { useDisplay } from 'vuetify'
import moment from 'moment'
import { useAuthStore } from '@/stores/auth'
import {
  BookOpen,
  LayoutDashboard,
  GraduationCap,
  Settings,
  Bell,
  LucideLogOut,
  Settings2,
} from 'lucide-vue-next'
import { useRoute } from 'vue-router'
import { USER_TYPE } from '@/shared/constants'

const route = useRoute()
const authStore = useAuthStore()
const { mobile } = useDisplay()

const navItems = [
  { icon: LayoutDashboard, label: 'Dashboard', to: '/dashboard', teacherOnly: false },
  { icon: BookOpen, label: 'Learning Strands', to: '/learning-strands', teacherOnly: false },
  { icon: GraduationCap, label: 'Students', to: '/students', teacherOnly: true },
]

const drawer = ref(true)

const pageTitle = computed(() => route.meta.title || `Good morning, ${authStore.currentUser.first_name}`)
const pageDescription = computed(() => route.meta.description || '')

const userFullName = computed(() => `${authStore.currentUser.first_name} ${authStore.currentUser.last_name}`)
const currentDate = computed(() => moment().format('dddd, MMMM D'))
const isStudent = computed(() => authStore.currentUser.type == USER_TYPE.STUDENT)

const userInitials = computed(() => {
  const name = userFullName.value
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .substring(0, 2)
    .toUpperCase()
})

const filteredNavItems = computed(() => {
  return navItems.filter(item => !(item.teacherOnly && isStudent.value))
})
</script>

<template>
  <v-bottom-navigation
    v-if="mobile"
    grow
    class="border-t border-zinc-200 dark:border-zinc-800 bg-surface"
  >
    <v-btn
      v-for="item in filteredNavItems"
      :key="item.label"
      :to="item.to"
      color="primary"
      :value="item.label"
    >
      <component :is="item.icon" class="size-4 mb-1" />
      <span>{{ item.label }}</span>
    </v-btn>
    <v-btn
      to="/settings"
      color="primary"
      value="settings"
    >
      <component :is="Settings2" class="size-4 mb-1" />
      <span>Settings</span>
    </v-btn>
  </v-bottom-navigation>

  <v-navigation-drawer
    v-else
    v-model="drawer"
    class="border-r border-zinc-200 dark:border-zinc-800 bg-surface"
  >
    <v-list-item class="px-5 py-5 border-b border-zinc-200 dark:border-zinc-800">
      <template #prepend>
        <v-avatar color="primary" size="32" class="text-xs font-semibold text-white">
          {{ userInitials }}
        </v-avatar>
      </template>
      <v-list-item-title class="truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">
        {{ userFullName }}
      </v-list-item-title>
    </v-list-item>

    <v-list density="compact" nav class="px-3 py-2 space-y-1">
      <v-list-item
        v-for="item in filteredNavItems"
        :key="item.label"
        link
        :to="item.to"
        color="primary"
        rounded="lg"
        class="hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
      >
        <template #default="{ isActive }">
          <div class="flex items-center w-full">
            <component 
              :is="item.icon" 
              class="size-4 mr-3" 
              :class="isActive ? 'text-primary' : 'text-zinc-500 dark:text-zinc-400'"
            />
            <v-list-item-title 
              class="text-sm font-medium"
              :class="isActive ? 'text-primary font-semibold' : 'text-zinc-600 dark:text-zinc-400'"
            >
              {{ item.label }}
            </v-list-item-title>
          </div>
        </template>
      </v-list-item>
    </v-list>

    <template #append>
      <v-container class="p-3">
        <v-btn 
          to="/settings" 
          block 
          variant="text" 
          class="justify-start text-none text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800" 
          rounded="lg"
        >
          <template #prepend>
            <Settings class="size-4 mr-1" />
          </template>
          Settings
        </v-btn>

        <v-btn 
          block 
          variant="text" 
          @click="authStore.logout" 
          class="justify-start text-none text-rose-600 dark:text-rose-400 hover:bg-rose-500/10" 
          rounded="lg"
        >
          <template #prepend>
            <LucideLogOut class="size-4 mr-1" />
          </template>
          Logout
        </v-btn>

        <v-list-item class="mt-2 rounded-lg border border-zinc-200 dark:border-zinc-800">
          <template #prepend>
            <v-avatar color="primary" rounded="lg" size="36">
              <GraduationCap class="size-5 text-white" />
            </v-avatar>
          </template>
          
          <v-list-item-title class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">KlassPilot</v-list-item-title>
          <v-list-item-subtitle class="text-xs text-zinc-500 dark:text-zinc-400">{{ isStudent ? 'Student' : 'Teacher' }} Portal</v-list-item-subtitle>
        </v-list-item>
      </v-container>
    </template>
  </v-navigation-drawer>

  <v-app-bar flat class="bg-surface/80 backdrop-blur px-2 py-1 border-b border-zinc-200 dark:border-zinc-800">
    <v-app-bar-title>
      <h1 class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ pageTitle }}</h1>
      <p class="text-xs sm:text-sm text-zinc-500 dark:text-zinc-400">
         {{ pageDescription ? pageDescription : `${currentDate}` }}
      </p>
    </v-app-bar-title>
    <template #append>
      <v-btn icon variant="plain" rounded="lg" size="small" class="relative text-zinc-600 dark:text-zinc-400 mr-2 border border-zinc-200 dark:border-zinc-800">
        <Bell class="size-4" />
        <span class="absolute right-1.5 top-1.5 size-2 rounded-full bg-rose-500" />
      </v-btn>
    </template>
  </v-app-bar>
</template>