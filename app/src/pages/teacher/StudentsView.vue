<script setup lang="ts">
import { ref } from 'vue'

const currentTab = ref('applications')

const learningStrands = ref([
  { id: 1, title: 'Basic Literacy Program (BLP)' },
  { id: 2, title: 'Elementary Accreditation & Equivalency (A&E)' },
  { id: 3, title: 'Secondary Accreditation & Equivalency (A&E)' },
])

const applications = ref([
  { id: 101, name: 'John Doe', email: 'john@example.com', requestedLearningStrandId: 1, date: 'Sep 02, 2026', status: 'Pending' },
  { id: 102, name: 'Maria Santos', email: 'maria@example.com', requestedLearningStrandId: 3, date: 'Sep 01, 2026', status: 'Pending' },
  { id: 103, name: 'Robert Lee', email: 'robert@example.com', requestedLearningStrandId: 2, date: 'Aug 30, 2026', status: 'Pending' },
])

const enrolledStudents = ref([
  { id: 1, name: 'Alex Johnson', email: 'alex@example.com', learningStrandId: 1, joined: 'Sep 01, 2026' },
  { id: 2, name: 'Michael Brown', email: 'm.brown@example.com', learningStrandId: 2, joined: 'Aug 25, 2026' },
])

const isAcceptDialogOpen = ref(false)
const selectedApp = ref<any>(null)
const assignedLearningStrandId = ref<number | null>(null)

const openAcceptModal = (app: any) => {
  selectedApp.value = app
  assignedLearningStrandId.value = app.requestedLearningStrandId
  isAcceptDialogOpen.value = true
}

const confirmAccept = () => {
  if (!selectedApp.value || !assignedLearningStrandId.value) return

  enrolledStudents.value.unshift({
    id: selectedApp.value.id,
    name: selectedApp.value.name,
    email: selectedApp.value.email,
    learningStrandId: assignedLearningStrandId.value,
    joined: new Date().toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })
  })

  applications.value = applications.value.filter(a => a.id !== selectedApp.value.id)
  isAcceptDialogOpen.value = false
  selectedApp.value = null
}

const declineApplication = (appId: number) => {
  applications.value = applications.value.filter(a => a.id !== appId)
}

const getLearningStrandTitle = (strandId: number) => {
  return learningStrands.value.find(s => s.id === strandId)?.title || 'Unassigned'
}

const appHeaders = [
  { title: 'Applicant', key: 'name' },
  { title: 'Requested Learning Strand', key: 'requestedLearningStrandId' },
  { title: 'Applied Date', key: 'date' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' as const },
]

const enrolledHeaders = [
  { title: 'Student', key: 'name' },
  { title: 'Enrolled Learning Strand', key: 'learningStrandId' },
  { title: 'Enrolled Date', key: 'joined' },
]
</script>

<template>
  <v-container class="min-h-screen flex flex-col">
    <div class="border-b border-zinc-200 dark:border-zinc-800 mb-6">
      <v-tabs v-model="currentTab" color="primary" align-tabs="start">
        <v-tab value="applications" class="capitalize font-semibold">
          Pending Applications
          <v-chip v-if="applications.length" size="x-small" color="primary" class="ml-2 font-bold">
            {{ applications.length }}
          </v-chip>
        </v-tab>
        <v-tab value="enrolled" class="capitalize font-semibold">
          Enrolled Students ({{ enrolledStudents.length }})
        </v-tab>
      </v-tabs>
    </div>

    <!-- TAB 1: PENDING APPLICATIONS -->
    <v-window v-model="currentTab">
      <v-window-item value="applications">
        <v-card flat class="border border-zinc-200 dark:border-zinc-800 bg-surface rounded-2xl p-4">
          <v-data-table
            :headers="appHeaders"
            :items="applications"
            density="comfortable"
            class="bg-transparent text-zinc-900 dark:text-zinc-100"
          >
            <template #item.name="{ item }">
              <div>
                <p class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">{{ item.name }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ item.email }}</p>
              </div>
            </template>

            <template #item.requestedLearningStrandId="{ item }">
              <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ getLearningStrandTitle(item.requestedLearningStrandId) }}
              </span>
            </template>

            <template #item.actions="{ item }">
              <div class="flex items-center justify-end gap-2">
                <v-btn
                  variant="flat"
                  size="small"
                  rounded="lg"
                  class="bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold capitalize hover:bg-rose-500/20"
                  @click="declineApplication(item.id)"
                >
                  Decline
                </v-btn>
                <v-btn
                  variant="flat"
                  size="small"
                  rounded="lg"
                  class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold capitalize hover:bg-emerald-500/20"
                  @click="openAcceptModal(item)"
                >
                  Accept
                </v-btn>
              </div>
            </template>
          </v-data-table>
        </v-card>
      </v-window-item>

      <!-- TAB 2: ENROLLED STUDENTS -->
      <v-window-item value="enrolled">
        <v-card flat class="border border-zinc-200 dark:border-zinc-800 bg-surface rounded-2xl p-4">
          <v-data-table
            :headers="enrolledHeaders"
            :items="enrolledStudents"
            density="comfortable"
            class="bg-transparent text-zinc-900 dark:text-zinc-100"
          >
            <template #item.name="{ item }">
              <div>
                <p class="font-medium text-zinc-900 dark:text-zinc-100 text-sm">{{ item.name }}</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ item.email }}</p>
              </div>
            </template>

            <template #item.learningStrandId="{ item }">
              <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                {{ getLearningStrandTitle(item.learningStrandId) }}
              </span>
            </template>
          </v-data-table>
        </v-card>
      </v-window-item>
    </v-window>

    <v-dialog v-model="isAcceptDialogOpen" max-width="480px">
      <v-card class="rounded-2xl p-2 bg-surface border border-zinc-200 dark:border-zinc-800">
        <v-card-title class="text-lg font-bold text-zinc-900 dark:text-zinc-100 pt-4 px-4">
          Accept Application
        </v-card-title>

        <v-card-text class="px-4 py-2 space-y-4">
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Confirm enrollment for <strong class="text-zinc-900 dark:text-zinc-100">{{ selectedApp?.name }}</strong>.
          </p>

          <v-select
            v-model="assignedLearningStrandId"
            :items="learningStrands"
            item-title="title"
            item-value="id"
            label="Assign Learning Strand"
            variant="outlined"
            density="comfortable"
            rounded="lg"
            hide-details
          ></v-select>
        </v-card-text>

        <v-card-actions class="p-4 flex justify-end gap-2">
          <v-btn variant="text" rounded="lg" @click="isAcceptDialogOpen = false">Cancel</v-btn>
          <v-btn color="primary" variant="elevated" rounded="lg" class="text-zinc-950 font-semibold" @click="confirmAccept">
            Confirm & Enroll
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>