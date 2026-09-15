<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useLearningStrandStore } from '@/stores/learningStrand'
import {
  CheckCircle2,
  ChevronRight,
  FileText,
  User,
  Paperclip,
} from 'lucide-vue-next'
import { orderBy } from 'lodash'

const learningStrandStore = useLearningStrandStore()

const appHeaders = [
  { title: 'Applicant', key: 'name' },
  { title: 'Requested Learning Strand', key: 'requestedLearningStrandId' },
  { title: 'Applied Date', key: 'date' },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end' as const },
]

const isAcceptDialogOpen = ref(false)
const selectedApp = ref<any>(null)
const assignedLearningStrandId = ref<number | null>(null)

const openAcceptModal = (app: any) => {
  selectedApp.value = app
  assignedLearningStrandId.value = app.requestedLearningStrandId
  isAcceptDialogOpen.value = true
}

const applications = ref([
  { id: 101, name: 'John Doe', email: 'john@example.com', requestedLearningStrandId: 1, date: 'Sep 02, 2026', status: 'Pending' },
  { id: 102, name: 'Maria Santos', email: 'maria@example.com', requestedLearningStrandId: 3, date: 'Sep 01, 2026', status: 'Pending' },
  { id: 103, name: 'Robert Lee', email: 'robert@example.com', requestedLearningStrandId: 2, date: 'Aug 30, 2026', status: 'Pending' },
])

const confirmAccept = () => {
  if (!selectedApp.value || !assignedLearningStrandId.value) return
  applications.value = applications.value.filter(a => a.id !== selectedApp.value.id)
  isAcceptDialogOpen.value = false
  selectedApp.value = null
}

const declineApplication = (appId: number) => {
  applications.value = applications.value.filter(a => a.id !== appId)
}

const getLearningStrandTitle = (learningStrandId: number) => {
  return learningStrandStore.allLearningStrands.find(ls => ls.id === learningStrandId)?.title || 'Unassigned'
}

const getRecentLearningStrands = () => {
  return orderBy(learningStrandStore.allLearningStrands, 'updated_at', 'desc').slice(0, 4)
}

const activities = ref([
  {
    id: 1,
    title: 'New assignment submitted',
    description: 'John Doe submitted Activity 2 in Communication Skills',
    time: '10m ago',
    icon: FileText,
    color: 'text-sky-500 bg-sky-500/10',
  },
  {
    id: 2,
    title: 'Learning strand application received',
    description: 'Maria Santos requested to join Scientific Literacy',
    time: '1h ago',
    icon: User,
    color: 'text-emerald-500 bg-emerald-500/10',
  },
  {
    id: 3,
    title: 'Quiz completed',
    description: 'Robert Lee completed Pre-Assessment Quiz',
    time: '3h ago',
    icon: CheckCircle2,
    color: 'text-amber-500 bg-amber-500/10',
  },
])

onMounted(async () => {
  if (!learningStrandStore.allLearningStrands.length) await learningStrandStore.fetchLearningStrands();
})
</script>

<template>
  <v-container fluid class="space-y-8 p-4 my-4 sm:p-6">
    <div>
      <div class="mb-4 flex items-center justify-between p-0">
        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Recent learning strands</h2>
        <v-btn @click="$router.push('/learning-strands')" variant="text" color="primary" class="text-none font-medium text-sm">
          View all <ChevronRight class="size-4 ml-1" />
        </v-btn>
      </div>
      <v-row v-if="!learningStrandStore.isLoading && learningStrandStore.allLearningStrands.length">
        <v-col v-for="strand in getRecentLearningStrands()" :key="strand.id" cols="12" sm="6" xl="4">
          <v-card flat rounded="xl" class="cursor-pointer group bg-surface p-5 transition-shadow hover:shadow-md border border-zinc-200 dark:border-zinc-800">
            <v-container class="flex items-center p-0 gap-2">
              <span :class="['size-2.5 rounded-full', strand.color || 'bg-emerald-500']" />
              <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 group-hover:text-primary transition-colors">{{ strand.title }}</h3>
            </v-container>
            <v-container class="mt-2 space-y-1.5 p-0 text-sm text-zinc-600 dark:text-zinc-400">
              <p class="flex items-center gap-2">
                <User class="size-3.5" /> {{ strand.students || 0 }} students
              </p>
              <p class="flex items-center gap-2">
                <Paperclip class="size-3.5" /> {{ strand.activities || 0 }} activities
              </p>
            </v-container>

            <v-container class="flex flex-wrap p-0 gap-2 mt-3">
              <v-chip 
                v-for="tag in (strand.learning_strand_tags || strand.learning_strand_tags)" 
                :key="tag"
                size="small" 
                variant="tonal"
                class="font-medium"
              >
                {{ tag }}
              </v-chip>
            </v-container>
          </v-card>
        </v-col>
      </v-row>
      <v-row v-else-if="learningStrandStore.isLoading">
        <v-col v-for="x in 2" :key="x" cols="12" sm="6" xl="4">
          <v-skeleton-loader type="article" class="rounded-lg"></v-skeleton-loader>
        </v-col>
      </v-row>
      <v-row v-else>
        <p class="text-gray-600">No recent learning strands</p>
      </v-row>
    </div>

    <v-row class="mt-4">
      <v-col cols="12" lg="7" xl="8">
        <v-card flat class="border border-zinc-200 dark:border-zinc-800 bg-surface rounded-2xl p-4 sm:p-5">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Pending Applications</h2>
              <p class="text-xs text-zinc-500 dark:text-zinc-400">Review student enrollment requests</p>
            </div>
            <v-chip size="small" class="font-medium bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
              {{ applications.length }} Pending
            </v-chip>
          </div>

          <div class="w-full max-w-full overflow-x-auto">
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
                    class="bg-rose-500/10 text-rose-600 dark:text-rose-400 font-semibold text-none hover:bg-rose-500/20"
                    @click="declineApplication(item.id)"
                  >
                    Decline
                  </v-btn>
                  <v-btn
                    variant="flat"
                    size="small"
                    rounded="lg"
                    class="bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-semibold text-none hover:bg-emerald-500/20"
                    @click="openAcceptModal(item)"
                  >
                    Accept
                  </v-btn>
                </div>
              </template>
            </v-data-table>
          </div>
        </v-card>
      </v-col>

      <v-col cols="12" lg="5" xl="4">
        <v-card flat class="border border-zinc-200 dark:border-zinc-800 bg-surface rounded-2xl p-4 sm:p-5 h-full">
          <div class="mb-4 flex items-center justify-between">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Recent Activity</h2>
            <span class="text-xs text-zinc-500 dark:text-zinc-400">Live feed</span>
          </div>

          <div class="space-y-4">
            <div 
              v-for="activity in activities" 
              :key="activity.id"
              class="flex items-start gap-3 p-2.5 rounded-xl transition-colors hover:bg-zinc-100 dark:hover:bg-zinc-800/50"
            >
              <div :class="['p-2 rounded-lg shrink-0', activity.color]">
                <component :is="activity.icon" class="size-4" />
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 truncate">
                    {{ activity.title }}
                  </p>
                  <span class="text-[10px] text-zinc-400 dark:text-zinc-500 whitespace-nowrap">
                    {{ activity.time }}
                  </span>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 line-clamp-2 mt-0.5">
                  {{ activity.description }}
                </p>
              </div>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

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
            :items="learningStrandStore.allLearningStrands"
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