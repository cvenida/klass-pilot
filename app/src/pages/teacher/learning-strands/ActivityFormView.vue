<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { createActivity, updateActivity } from '@/services/activityService'
import { useLearningStrandStore } from '@/stores/learningStrand'
import { Plus, Trash2, ArrowLeft, Save } from 'lucide-vue-next'
import { QUESTION_TYPE } from '@/shared/constants'
import { capitalize } from 'lodash'

const route = useRoute()
const router = useRouter()
const learningStrandStore = useLearningStrandStore()

const isSaving = ref(false)
const isEditMode = computed(() => !!route.params.activityId)
const learningStrandId = route.params.id

// Deadline menu state
const deadlineMenu = ref(false)

const form = ref({
  learning_strand_id: learningStrandId,
  title: '',
  type: 'quiz', // 'quiz', 'assignment', 'exam', 'practice'
  deadlineDate: null,
  deadlineTime: '23:59',
  questions: []
})

const activityTypes = ['quiz', 'assignment', 'exam', 'practice']

// Combined deadline computed property for display & submission
const formattedDeadlineDisplay = computed(() => {
  if (!form.value.deadlineDate) return ''
  const d = new Date(form.value.deadlineDate)
  const dateStr = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  return `${dateStr} ${form.value.deadlineTime}`
})

onMounted(async () => {
  if (!isEditMode.value) {
    addQuestion();
  }
  
  if (!learningStrandStore.currentLearningStrand) {
    await learningStrandStore.fetchLearningStrandById(learningStrandId)
  }

  // Populate data if Editing an existing activity
  if (isEditMode.value && learningStrandStore.currentLearningStrand?.activities) {
    const existing = learningStrandStore.currentLearningStrand.activities.find(
      a => a.id == route.params.activityId
    )
    if (existing) {
      const data = JSON.parse(JSON.stringify(existing))
      if (data.deadline) {
        const d = new Date(data.deadline)
        data.deadlineDate = d
        data.deadlineTime = d.toTimeString().slice(0, 5)
      } else {
        data.deadlineDate = null
        data.deadlineTime = '23:59'
      }
      form.value = data
    }
  }
})

const addQuestion = () => {
  form.value.questions.push({
    question_text: '',
    question_type: 'multiple_choice',
    points: 1,
    options: [
      { option_text: '', is_correct: 1 },
      { option_text: '', is_correct: 0 }
    ]
  })
}

const removeQuestion = (qIndex) => {
  form.value.questions.splice(qIndex, 1)
}

const addOption = (qIndex) => {
  form.value.questions[qIndex].options.push({
    option_text: '',
    is_correct: 0
  })
}

const removeOption = (qIndex, oIndex) => {
  form.value.questions[qIndex].options.splice(oIndex, 1)
}

const setCorrectOption = (qIndex, oIndex) => {
  form.value.questions[qIndex].options.forEach((opt, idx) => {
    opt.is_correct = idx === oIndex ? 1 : 0
  })
}

const handleTypeChange = (question) => {
  if (question.question_type === 'true_false') {
    question.options = [
      { option_text: 'True', is_correct: 1 },
      { option_text: 'False', is_correct: 0 }
    ]
  } else if (question.question_type === 'short_answer') {
    question.options = []
  } else if (question.question_type === 'multiple_choice' && (!question.options || question.options.length === 0)) {
    question.options = [
      { option_text: '', is_correct: 1 },
      { option_text: '', is_correct: 0 }
    ]
  }
}

const handleSubmit = async () => {
  try {
    isSaving.value = true

    let formattedDeadline = null
    if (form.value.deadlineDate) {
      const d = new Date(form.value.deadlineDate)
      const year = d.getFullYear()
      const month = String(d.getMonth() + 1).padStart(2, '0')
      const day = String(d.getDate()).padStart(2, '0')
      const time = form.value.deadlineTime || '23:59'
      formattedDeadline = `${year}-${month}-${day} ${time}:00`
    }

    const cleanedQuestions = form.value.questions.map(q => {
      if (q.question_type === 'short_answer') {
        return { ...q, options: [] }
      }
      return q
    })

    const { deadlineDate, deadlineTime, ...formData } = form.value

    const payload = {
      ...formData,
      learning_strand_id: learningStrandId,
      deadline: formattedDeadline,
      questions: cleanedQuestions
    }

    if (isEditMode.value) {
      await updateActivity(route.params.activityId, payload)
    } else {
      await createActivity(payload)
    }

    await learningStrandStore.fetchLearningStrandById(learningStrandId)
    router.back()
  } catch (error) {
    console.error('Failed to save activity:', error)
  } finally {
    isSaving.value = false
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto p-4 sm:p-6 space-y-6">
    <div class="flex items-center justify-between">
      <v-btn variant="text" rounded="lg" @click="router.back()">
        <template #prepend><ArrowLeft class="size-4" /></template>
        Back
      </v-btn>

      <v-btn
        color="primary"
        rounded="lg"
        :loading="isSaving"
        @click="handleSubmit"
      >
        <template #prepend><Save class="size-4" /></template>
        {{ isEditMode ? 'Update Activity' : 'Save Activity' }}
      </v-btn>
    </div>

    <v-card flat class="p-6 rounded-2xl bg-surface border border-zinc-200 dark:border-zinc-800 space-y-4">
      <h2 class="text-lg font-bold text-zinc-900 dark:text-zinc-100">
        {{ isEditMode ? 'Edit Activity Details' : 'Create New Activity' }}
      </h2>

      <v-text-field
        v-model="form.title"
        label="Activity Title"
        variant="outlined"
        density="compact"
        hide-details
      ></v-text-field>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <v-select
          v-model="form.type"
          :items="activityTypes"
          label="Activity Type"
          variant="outlined"
          density="compact"
          class="capitalize"
          hide-details
        >
          <template #item="{ item, props }">
            <v-list-item v-bind="item">
              {{ capitalize(item) }}
            </v-list-item>
          </template>
        </v-select>

        <v-menu v-model="deadlineMenu" :close-on-content-click="false" location="bottom end">
          <template #activator="{ props }">
            <v-text-field
              v-bind="props"
              :model-value="formattedDeadlineDisplay"
              label="Deadline"
              variant="outlined"
              density="compact"
              readonly
              hide-details
              clearable
              @click:clear="form.deadlineDate = null"
            ></v-text-field>
          </template>

          <v-card class="p-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 space-y-4 max-w-sm">
            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Select Date & Time</div>
            
            <v-date-picker
              v-model="form.deadlineDate"
              hide-header
              density="compact"
            ></v-date-picker>

            <v-text-field
              v-model="form.deadlineTime"
              label="Time"
              type="time"
              variant="outlined"
              density="compact"
              hide-details
            ></v-text-field>

            <div class="flex justify-end gap-2 pt-2">
              <v-btn size="small" variant="text" rounded="lg" @click="deadlineMenu = false">Close</v-btn>
              <v-btn size="small" color="primary" rounded="lg" @click="deadlineMenu = false">Set Deadline</v-btn>
            </div>
          </v-card>
        </v-menu>
      </div>
    </v-card>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-md font-bold text-zinc-900 dark:text-zinc-100">Questions</h3>
        <v-btn size="small" variant="outlined" color="primary" @click="addQuestion">
          <template #prepend><Plus class="size-4" /></template>
          Add Question
        </v-btn>
      </div>

      <div v-for="(question, qIndex) in form.questions" :key="qIndex">
        <v-card flat class="p-5 rounded-xl bg-surface border border-zinc-200 dark:border-zinc-800 space-y-4 relative">
          <v-btn
            icon
            variant="text"
            color="error"
            size="small"
            class="absolute top-2 right-2"
            @click="removeQuestion(qIndex)"
          >
            <Trash2 class="size-4" />
          </v-btn>

          <div class="pr-8 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
              <v-text-field
                v-model="question.question_text"
                :label="`Question ${qIndex + 1}`"
                variant="outlined"
                density="compact"
                class="sm:col-span-2"
                hide-details
              ></v-text-field>

              <v-select
                v-model="question.question_type"
                :items="QUESTION_TYPE"
                item-title="name"
                item-value="id"
                label="Type"
                variant="outlined"
                density="compact"
                hide-details
                @update:model-value="handleTypeChange(question)"
              ></v-select>

              <v-text-field
                v-model.number="question.points"
                label="Points"
                type="number"
                variant="outlined"
                density="compact"
                hide-details
              ></v-text-field>
            </div>

            <v-container v-if="question.question_type === 'multiple_choice'" class="pl-4 border-l-2 border-zinc-300 dark:border-zinc-700 space-y-2 pt-2">
              <div class="text-xs font-semibold text-zinc-500">Options</div>

              <div
                v-for="(option, oIndex) in question.options"
                :key="oIndex"
                class="flex items-center gap-2"
              >
                <v-btn
                  icon
                  size="x-small"
                  :color="option.is_correct ? 'success' : 'default'"
                  variant="tonal"
                  @click="setCorrectOption(qIndex, oIndex)"
                >
                  ✓
                </v-btn>

                <v-text-field
                  v-model="option.option_text"
                  placeholder="Option text"
                  variant="outlined"
                  density="compact"
                  hide-details
                ></v-text-field>

                <v-btn
                  icon
                  variant="text"
                  color="error"
                  size="x-small"
                  @click="removeOption(qIndex, oIndex)"
                >
                  <Trash2 class="size-3" />
                </v-btn>
              </div>

              <v-btn variant="text" size="x-small" color="primary" @click="addOption(qIndex)">
                + Add Option
              </v-btn>
            </v-container>

            <v-container v-else-if="question.question_type === 'true_false'" class="pl-4 border-l-2 border-zinc-300 dark:border-zinc-700 space-y-2 pt-2">
              <div class="text-xs font-semibold text-zinc-500">Select Correct Answer</div>
              <div class="flex gap-4">
                <v-btn
                  v-for="(option, oIndex) in question.options"
                  :key="oIndex"
                  :color="option.is_correct ? 'success' : 'default'"
                  :variant="option.is_correct ? 'flat' : 'outlined'"
                  size="small"
                  rounded="lg"
                  @click="setCorrectOption(qIndex, oIndex)"
                >
                  {{ option.option_text }}
                </v-btn>
              </div>
            </v-container>

            <v-container v-else-if="question.question_type === 'short_answer'" class="pl-4 border-l-2 border-zinc-300 dark:border-zinc-700 pt-2">
              <p class="text-xs text-zinc-500 italic">
                Students will enter a free-text response. Manual grading may be required.
              </p>
            </v-container>
          </div>
        </v-card>
      </div>
    </div>
  </div>
</template>