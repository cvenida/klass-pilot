<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCourseStore } from '@/stores/courses'
import { get } from 'lodash'
import { MoreVertical, SquarePen, Trash2 } from 'lucide-vue-next'
import CourseFormDialog from '@/components/CourseFormDialog.vue'
import DeleteDialog from '@/components/DeleteDialog.vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const basePath = '/' + route.path.split('/')[1]


const courseStore = useCourseStore()

const searchQuery = ref('')
const selectedStatusFilter = ref(null)
const isDialogOpen = ref(false)
const selectedCourseData = ref(null)

const filteredCourses = computed(() => {
  return courseStore.allCourses.filter((course) => {
    const matchesStatus = selectedStatusFilter.value
      ? course.status === selectedStatusFilter.value
      : true
    const matchesSearch =
      get(course, 'title', '').toLowerCase().includes(searchQuery.value.toLowerCase())

    return matchesStatus && matchesSearch
  })
})

const totalEnrolled = computed(() => 0)

const openEditDialog = (course) => {
  selectedCourseData.value = {
    id: course.id,
    title: course.title,
    description: course.description || '',
    tags: Array.isArray(course.course_tags) ? [...course.course_tags] : [],
    status: course.status || 'draft',
    cooldownDays: course.reapply_cooldown_days || 0
  }
  isDialogOpen.value = true
}

const handleFormSubmit = async (data) => {
  if (data.id) {
    await courseStore.editCourse(data.id, {
      title: data.title,
      description: data.description,
      tags: data.tags,
      status: data.status,
      cooldownDays: data.cooldownDays
    })
  } else {
    await courseStore.addCourse({
      title: data.title,
      description: data.description,
      tags: data.tags,
      cooldownDays: data.cooldownDays
    })
  }

  selectedCourseData.value = null;
  isDialogOpen.value = false
}

const isDeleteDialogOpen = ref(false)
const courseToDelete = ref(null)
const isDeleting = ref(false)

const confirmDeleteCourse = (course) => {
  courseToDelete.value = course
  isDeleteDialogOpen.value = true
}

const handleExecuteDelete = async () => {
  if (!courseToDelete.value) return

  isDeleting.value = true
  try {
    await courseStore.removeCourse(courseToDelete.value.id)
    isDeleteDialogOpen.value = false
    courseToDelete.value = null
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
  if (!courseStore.allCourses.length) await courseStore.fetchCourses()
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
          Add New Course
        </v-btn>
      </div>

      <v-row class="mb-3">
        <v-col cols="12" md="6">
          <v-card flat class="p-4 border border-zinc-200 dark:border-zinc-800 rounded-2xl bg-surface">
            <p class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Total Courses</p>
            <p class="text-2xl font-bold text-primary mt-1">{{ courseStore.allCourses.length }}</p>
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

      <v-row v-if="!courseStore.isLoading && courseStore.allCourses.length">
        <v-col
          cols="12"
          md="6"
          v-for="course in filteredCourses"
          :key="course.id"
        >
          <v-card
            flat
            @click="$router.push(`${basePath}/${course.id}`)"
            class="border border-zinc-200 dark:border-zinc-800 cursor-pointer rounded-2xl bg-surface flex flex-col justify-between max-h-100 transition-all"
          >
            <div class="p-5">
              <div class="flex items-center justify-between gap-2 mb-3">
                <span 
                  class="size-2 rounded-full transition-colors duration-200" 
                  :class="getStatusBadgeColor(course.status || 'draft')"
                />
                <span
                  :class="[
                    'px-2.5 py-0.5 text-xs font-semibold rounded-full border',
                    getStatusColor(course.status || 'draft')
                  ]"
                >
                  {{ capitalize(course.status) || 'Draft' }}
                </span>
              </div>
              <h2 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ course.title }}</h2>
              <p class="text-xs text-zinc-600 dark:text-zinc-400 line-clamp-3 leading-relaxed mt-1">
                {{ course.description }}
              </p>

              <v-container class="flex flex-wrap p-0 gap-1 mt-5">
                <v-chip 
                  v-for="tag in course.course_tags" 
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
                <span><strong class="text-zinc-900 dark:text-zinc-100 font-semibold">{{ course.enrolledStudents || 0 }}</strong> Enrolled</span>
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
                    @click="openEditDialog(course)"
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
                    @click="confirmDeleteCourse(course)"
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
      <v-row v-else-if="courseStore.isLoading">
        <v-col v-for="x in 4" :key="x" cols="12" sm="6" xl="4">
          <v-skeleton-loader type="card" class="rounded-lg"></v-skeleton-loader>
        </v-col>
      </v-row>
      <v-row v-else>
        <p class="text-center text-gray-600">No courses</p>
      </v-row>

      <CourseFormDialog
        v-model="isDialogOpen"
        :initial-data="selectedCourseData"
        :is-loading="courseStore.isLoading"
        @submit="handleFormSubmit"
      />

      <DeleteDialog
        v-model="isDeleteDialogOpen"
        title="Delete Course"
        :item-name="courseToDelete?.title"
        :is-loading="isDeleting"
        @confirm="handleExecuteDelete"
      />
    </v-main>
  </v-layout>
</template>