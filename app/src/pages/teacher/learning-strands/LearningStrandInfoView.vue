<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useLearningStrandStore } from '@/stores/learningStrand'
import { get } from 'lodash'
import {
  Clock,
  Tag,
  ArrowLeft,
  Plus
} from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const learningStrandStore = useLearningStrandStore()

const basePath = '/' + route.path.split('/')[1]
const isLoading = ref(true)

const learningStrand = computed(() => learningStrandStore.currentLearningStrand)
const activities = computed(() => get(learningStrand, 'value.activities', []))

onMounted(async () => {
  const learningStrandId = route.params.id
  if (learningStrandId) {
    try {
      isLoading.value = true
      await learningStrandStore.fetchLearningStrandById(learningStrandId)
    } catch (error) {
      console.error('Failed to load learning strand details:', error)
    } finally {
      isLoading.value = false
    }
  }
})

const getStatusColor = (status) => {
  switch (status) {
    case 'Completed': return 'success'
    case 'Submitted': return 'info'
    case 'In Progress': return 'warning'
    default: return 'secondary'
  }
}
</script>

<template>
  <div class="min-h-full p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl mx-auto">
    <div class="flex items-center justify-between gap-4">
      <v-btn
        variant="text"
        rounded="lg"
        class="text-none text-zinc-600 dark:text-zinc-400 -ml-2"
        @click="router.back()"
      >
        <template #prepend>
          <ArrowLeft class="size-4" />
        </template>
        Back to Learning Strands
      </v-btn>

      <v-btn
        v-if="learningStrand?.id"
        color="primary"
        @click="router.push(`${basePath}/${learningStrand.id}/activities/create`)"
        rounded="lg"
        class="text-none font-semibold shadow-sm"
      >
        <template #prepend>
          <Plus class="size-4" />
        </template>
        New Activity
      </v-btn>
    </div>

    <v-card v-if="isLoading" flat class="rounded-2xl p-6 bg-surface border border-zinc-200 dark:border-zinc-800">
      <v-skeleton-loader type="article, chip" />
    </v-card>

    <v-card v-else-if="learningStrand" flat class="rounded-2xl p-6 bg-surface border border-zinc-200 dark:border-zinc-800">
      <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
        <div class="space-y-3 flex-1">
          <div class="flex items-center gap-2 flex-wrap">
            <v-chip
              v-if="learningStrand.status"
              size="small"
              color="primary"
              variant="flat"
              class="font-semibold capitalize"
            >
              {{ learningStrand.status }}
            </v-chip>
            
            <span 
              v-if="learningStrand.reapply_cooldown_days || learningStrand.cooldownDays" 
              class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1"
            >
              <Clock class="size-3.5" /> {{ learningStrand.reapply_cooldown_days || learningStrand.cooldownDays }} Days Cooldown
            </span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-bold text-zinc-900 dark:text-zinc-100">
            {{ learningStrand.title }}
          </h1>

          <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed max-w-4xl">
            {{ learningStrand.description || 'No description provided for this learning strand.' }}
          </p>

          <div v-if="learningStrand.tags && learningStrand.tags.length" class="flex items-center gap-2 flex-wrap pt-2">
            <v-chip
              v-for="tag in learningStrand.tags"
              :key="tag"
              size="small"
              variant="outlined"
              class="text-zinc-600 dark:text-zinc-400 border-zinc-300 dark:border-zinc-700 capitalize"
            >
              <template #prepend>
                <Tag class="size-3 mr-1" />
              </template>
              {{ tag }}
            </v-chip>
          </div>
        </div>
      </div>
    </v-card>

    <v-card v-else flat class="rounded-2xl p-8 bg-surface border border-zinc-200 dark:border-zinc-800 text-center">
      <p class="text-zinc-500 dark:text-zinc-400 text-sm">Learning strand information could not be found.</p>
    </v-card>

    <div class="flex items-center justify-between pt-2">
      <div>
        <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">Learning Strand Activities</h2>
        <p class="text-xs text-zinc-500 dark:text-zinc-400">Manage and complete your tasks for this learning strand</p>
      </div>
    </div>

    <div v-if="!isLoading && activities.length" class="space-y-3">
      <v-card
        v-for="activity in activities"
        :key="activity.id"
        flat
        class="rounded-xl p-5 bg-surface border border-zinc-200 dark:border-zinc-800 hover:border-zinc-300 dark:hover:border-zinc-700 transition-colors"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="space-y-1 flex-1">
            <div class="flex items-center gap-2">
              <span class="text-xs font-semibold text-primary uppercase tracking-wider">{{ activity.type }}</span>
              <span class="text-zinc-300 dark:text-zinc-700">•</span>
              <span class="text-xs text-zinc-500 dark:text-zinc-400 flex items-center gap-1">
                <Clock class="size-3" /> Due {{ activity.dueDate }}
              </span>
            </div>

            <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
              {{ activity.title }}
            </h3>

            <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-2">
              {{ activity.description }}
            </p>
          </div>

          <div class="flex items-center justify-between sm:justify-end gap-4 border-t sm:border-t-0 pt-3 sm:pt-0 border-zinc-100 dark:border-zinc-800">
            <div class="text-right">
              <div class="text-xs text-zinc-500 dark:text-zinc-400">Score</div>
              <div class="text-sm font-bold text-zinc-900 dark:text-zinc-100">{{ activity.score ?? 'N/A' }}</div>
            </div>

            <v-chip
              size="small"
              :color="getStatusColor(activity.status)"
              variant="tonal"
              class="font-semibold capitalize shrink-0"
            >
              {{ activity.status }}
            </v-chip>

            <v-btn
              variant="outlined"
              size="small"
              rounded="lg"
              class="capitalize font-semibold border-zinc-300 dark:border-zinc-700 text-zinc-700 dark:text-zinc-300"
            >
              View
            </v-btn>
          </div>
        </div>
      </v-card>
    </div>

    <v-card v-else-if="!isLoading && !activities.length" flat class="rounded-2xl p-8 bg-surface border border-zinc-200 dark:border-zinc-800 text-center">
      <p class="text-zinc-500 dark:text-zinc-400 text-sm">There are no activities for this learning strand.</p>
    </v-card>

    <v-card v-else flat class="rounded-2xl p-6 bg-surface border border-zinc-200 dark:border-zinc-800">
      <v-skeleton-loader type="article" />
    </v-card>
  </div>
</template>