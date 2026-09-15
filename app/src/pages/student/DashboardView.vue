<script setup>
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const stats = ref([
  { title: 'Total Students', value: '1,284', icon: 'mdi-school', color: 'text-teal-600', bg: 'bg-teal-50', change: '+12%', isUp: true },
  { title: 'Active Learning Strands', value: '42', icon: 'mdi-book-open-page-variant', color: 'text-purple-600', bg: 'bg-purple-50', change: '0%', isUp: true },
])

const headers = [
  { title: 'User', key: 'name' },
  { title: 'Role', key: 'role' },
  { title: 'Status', key: 'status' },
  { title: 'Joined Date', key: 'joined' }
]

const recentUsers = ref([
  { name: 'Alex Johnson', email: 'alex@example.com', role: 'Student', status: 'Active', joined: 'Sep 01, 2026' },
  { name: 'Sarah Smith', email: 'sarah@example.com', role: 'Teacher', status: 'Active', joined: 'Aug 28, 2026' },
  { name: 'Michael Brown', email: 'm.brown@example.com', role: 'Student', status: 'Pending', joined: 'Aug 25, 2026' },
  { name: 'Emily Davis', email: 'emily@example.com', role: 'Student', status: 'Active', joined: 'Aug 20, 2026' }
])
</script>

<template>
  <v-layout class="min-h-screen">
    <v-main class="p-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Overview</h1>
          <p class="text-sm text-slate-500">Welcome back student! Here is what is happening today.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <v-card flat class="border border-slate-200 rounded-2xl p-4">
          <h3 class="text-base font-bold text-slate-800 mb-4">Quick Notice</h3>
          <div class="space-y-4">
            <div class="p-3 bg-slate-100 rounded-xl">
              <p class="text-sm font-semibold text-slate-800">System Maintenance</p>
              <p class="text-xs text-slate-500 mt-1">Scheduled database maintenance will occur this Saturday at 12:00 AM UTC.</p>
            </div>
            <div class="p-3 bg-teal-50 text-teal-900 rounded-xl">
              <p class="text-sm font-semibold">New Term Registration</p>
              <p class="text-xs text-teal-700 mt-1">Teacher applications for the upcoming term are now open.</p>
            </div>
          </div>
        </v-card>
      </div>
    </v-main>
  </v-layout>
</template>