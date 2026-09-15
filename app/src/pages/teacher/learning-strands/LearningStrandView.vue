<script setup>
import { ref, computed, onMounted } from 'vue'
import { useLearningStrandStore } from '@/stores/learningStrand'
import { get } from 'lodash'
import { MoreVertical, SquarePen, Trash2 } from 'lucide-vue-next'
import LearningStrandFormDialog from '@/components/LearningStrandFormDialog.vue'
import DeleteDialog from '@/components/DeleteDialog.vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const basePath = '/' + route.path.split('/')[1]

const learningStrandStore = useLearningStrandStore()

const searchQuery = ref('')
const selectedStatusFilter = ref(null)
const isDialogOpen = ref(false)
const selectedLearningStrandData = ref(null)

const filteredLearningStrands = computed(() => {
  return learningStrandStore.allLearningStrands.filter((strand) => {
    const matchesStatus = selectedStatusFilter.value
      ? strand.status === selectedStatusFilter.value
      : true
    const matchesSearch =
      get(strand, 'title', '').toLowerCase().includes(searchQuery.value.toLowerCase())

    return matchesStatus && matchesSearch
  })
})

const totalEnrolled = computed(() => 0)

const openEditDialog = (strand) => {
  selectedLearningStrandData.value = {
    id: strand.id,
    title: strand.title,
    description: strand.description || '',
    tags: Array.isArray(strand.learning_strand_tags) ? [...strand.learning_strand_tags] : [],
    status: strand.status || 'draft',
    cooldownDays: strand.reapply_cooldown_days || 0
  }
  isDialogOpen.value = true
}

const handleFormSubmit = async (data) => {
  if (data.id) {
    await learningStrandStore.editLearningStrand(data.id, {
      title: data.title,
      description: data.description,
      tags: data.tags,
      status: data.status,
      cooldownDays: data.cooldownDays
    })
  } else {
    await learningStrandStore.addLearningStrand({
      title: data.title,
      description: data.description,
      tags: data.tags,
      cooldownDays: data.cooldownDays
    })
  }

  selectedLearningStrandData.value = null
  isDialogOpen.value = false
}

const isDeleteDialogOpen = ref(false)
const learningStrandToDelete = ref(null)
const isDeleting = ref(false)

const confirmDeleteLearningStrand = (strand) => {
  learningStrandToDelete.value = strand
  isDeleteDialogOpen.value = true
}

const handleExecuteDelete = async () => {
  if (!learningStrandToDelete.value) return

  isDeleting.value = true
  try {
    await learningStrandStore.removeLearningStrand(learningStrandToDelete.value.id)
    isDeleteDialogOpen.value = false
    learningStrandToDelete.value = null
  } finally {
    isDeleting.value = false
  }
}

const getStatusBadgeColor = (level) => {
  switch (level) {
    case 'active':
      return 'bg-emerald-500'
    case 'inactive':
      return 'bg-rose-500'
    default:
      return 'bg-zinc-500'
  }
}

const getStatusColor = (level) => {
  switch (level) {
    case 'active':
      return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
    case 'inactive':
      return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20'
    default:
      return 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400 border-zinc-500/20'
  }
}

const capitalize = (str) => (str ? str.charAt(0).toUpperCase() + str.slice(1) : '')

onMounted(async () => {
  if (!learningStrandStore.allLearningStrands.length) await learningStrandStore.fetchLearningStrands()
})
</script>

<template>
  <v-layout class="min-h-screen">
    <v-main class="p-6">
      <div class="flex flex-col sm:flex-row sm:items-end justify-end gap-4 mb-6">
        <v-btn
          color="primary"
          prepend-icon="mdi-plus"
          rounded="lg"
          class="capitalize font-medium"
          @click="isDialogOpen = true"
        >
          Add New Learning Strand
        </v-btn>
      </div>

      <v-row class="mb-3">
        <v-col cols="12" md="6">
          <v-card flat class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-2xl bg-surface">
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Learning Strands</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ learningStrandStore.allLearningStrands.length }}</p>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card flat class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-2xl bg-surface">
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Active Enrollees</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ totalEnrolled || 0 }}</p>
          </v-card>
        </v-col>
      </v-row>

      <v-card flat class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-2xl mb-6 bg-surface">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <v-text-field
            v-model="searchQuery"
            placeholder="Search by title..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
            clearable
            rounded="lg"
            class="sm:col-span-2"
          ></v-text-field>

          <v-select
            v-model="selectedStatusFilter"
            :items="['active', 'inactive', 'draft']"
            label="Filter by Level"
            variant="outlined"
            density="compact"
            hide-details
            clearable
            rounded="lg"
          >
            <template #selection="{ item }">
              <span class="capitalize">{{ capitalize(item) }}</span>
            </template>

            <template #item="{ item, props }">
              <v-list-item v-bind="props" :title="capitalize(item)" class="capitalize" />
            </template>
          </v-select>
        </div>
      </v-card>

      <v-row v-if="!learningStrandStore.isLoading && learningStrandStore.allLearningStrands.length">
        <v-col
          cols="12"
          md="6"
          v-for="strand in filteredLearningStrands"
          :key="strand.id"
        >
          <v-card
            flat
            @click="$router.push(`${basePath}/${strand.id}`)"
            class="border border-zinc-200 dark:border-zinc-800 cursor-pointer rounded-2xl bg-surface flex flex-col justify-between max-h-100 transition-all"
          >
            <div class="p-5">
              <div class="flex items-center justify-between gap-2 mb-3">
                <span 
                  class="size-2 rounded-full transition-colors duration-200" 
                  :class="getStatusBadgeColor(strand.status || 'draft')"
                />
                <span
                  :class="[
                    'px-2.5 py-0.5 text-xs font-semibold rounded-full border',
                    getStatusColor(strand.status || 'draft')
                  ]"
                >
                  {{ capitalize(strand.status) || 'Draft' }}
                </span>
              </div>
              <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ strand.title }}</h2>
              <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-3 leading-relaxed mt-1">
                {{ strand.description }}
              </p>

              <v-container class="flex flex-wrap p-0 gap-1 mt-5">
                <v-chip 
                  v-for="tag in strand.learning_strand_tags" 
                  :key="tag"
                  size="small" 
                  variant="tonal"
                  class="font-medium"
                >
                  {{ tag }}
                </v-chip>
              </v-container>
            </div>

            <div class="px-5 py-3 flex items-center justify-between border-t border-zinc-200 dark:border-zinc-800 rounded-b-2xl">
              <div class="flex items-center gap-1.5 text-xs text-zinc-600 dark:text-zinc-400">
                <v-icon icon="mdi-account-group-outline" size="small" class="text-zinc-400 dark:text-zinc-500"></v-icon>
                <span><strong class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ strand.enrolledStudents || 0 }}</strong> Enrolled</span>
              </div>
              
              <v-menu location="bottom end">
                <template #activator="{ props }">
                  <v-btn
                    v-bind="props"
                    variant="text"
                    size="small"
                    icon
                    class="text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
                  >
                    <MoreVertical class="size-4" />
                  </v-btn>
                </template>

                <v-list density="compact" class="py-1 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-surface shadow-lg">
                  <v-list-item 
                    value="update" 
                    @click="openEditDialog(strand)"
                    class="hover:bg-zinc-100 dark:hover:bg-zinc-800 min-h-[36px]"
                  >
                    <template #prepend>
                      <SquarePen class="size-4 mr-2 text-zinc-500 dark:text-zinc-400" />
                    </template>
                    <v-list-item-title class="text-xs font-medium text-zinc-700 dark:text-zinc-200">
                      Update
                    </v-list-item-title>
                  </v-list-item>

                  <v-list-item 
                    value="delete" 
                    @click="confirmDeleteLearningStrand(strand)"
                    class="hover:bg-rose-50 dark:hover:bg-rose-950/30 min-h-[36px]"
                  >
                    <template #prepend>
                      <Trash2 class="size-4 mr-2 text-rose-500" />
                    </template>
                    <v-list-item-title class="text-xs font-medium text-rose-600 dark:text-rose-400">
                      Delete
                    </v-list-item-title>
                  </v-list-item>
                </v-list>
              </v-menu>
            </div>
          </v-card>
        </v-col>
      </v-row>
      <v-row v-else-if="learningStrandStore.isLoading">
        <v-col v-for="x in 4" :key="x" cols="12" sm="6" xl="4">
          <v-skeleton-loader type="card" class="rounded-lg"></v-skeleton-loader>
        </v-col>
      </v-row>
      <v-row v-else>
        <p class="text-center text-gray-600">No learning strands</p>
      </v-row>

      <LearningStrandFormDialog
        v-model="isDialogOpen"
        :initial-data="selectedLearningStrandData"
        :is-loading="learningStrandStore.isLoading"
        @submit="handleFormSubmit"
      />

      <DeleteDialog
        v-model="isDeleteDialogOpen"
        title="Delete Learning Strand"
        :item-name="learningStrandToDelete?.title"
        :is-loading="isDeleting"
        @confirm="handleExecuteDelete"
      />
    </v-main>
  </v-layout>
</template>