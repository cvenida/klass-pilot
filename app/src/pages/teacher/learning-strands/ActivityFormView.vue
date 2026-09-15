<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { createActivity, updateActivity } from '@/services/activityService'
import { useLearningStrandStore } from '@/stores/learningStrand'
import { Plus, Trash2, ArrowLeft, Save } from 'lucide-vue-next'

const route = useRoute()
const router = useRouter()
const learningStrandStore = useLearningStrandStore()

const isSaving = ref(false)
const isEditMode = computed(() => !!route.params.activityId)
const learningStrandId = route.params.id

const form = ref({
  learning_strand_id: learningStrandId,
  title: '',
  type: 'quiz', // 'quiz', 'assignment', 'exam', 'practice'
  deadline: '',
  questions: []
})

const activityTypes = ['quiz', 'assignment', 'exam', 'practice']
const questionTypes = ['multiple_choice', 'short_answer', 'true_false']

onMounted(async () => {
  if (!learningStrandStore.currentLearningStrand) {
    await learningStrandStore.fetchLearningStrandById(learningStrandId)
  }

  // Populate data if Editing an existing activity
  if (isEditMode.value && learningStrandStore.currentLearningStrand?.activities) {
    const existing = learningStrandStore.currentLearningStrand.activities.find(
      a => a.id == route.params.activityId
    )
    if (existing) {
      form.value = JSON.parse(JSON.stringify(existing))
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

// Submit Form
const handleSubmit = async () => {
  try {
    isSaving.value = true

    let formattedDeadline = null
    if (form.value.deadline) {
      const d = new Date(form.value.deadline)
      formattedDeadline = d.toISOString().slice(0, 19).replace('T', ' ')
    }

    // Clean question option payloads depending on question type
    const cleanedQuestions = form.value.questions.map(q => {
      if (q.question_type !== 'multiple_choice') {
        return { ...q, options: [] }
      }
      return q
    })

    const payload = {
      ...form.value,
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
        ></v-select>

        <v-date-input
          v-model="form.deadline"
          label="Deadline"
          variant="outlined"
          density="compact"
          hide-details
          clearable
        ></v-date-input>
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
                :items="questionTypes"
                label="Type"
                variant="outlined"
                density="compact"
                hide-details
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

            <div v-if="question.question_type === 'multiple_choice'" class="pl-4 border-l-2 border-zinc-300 dark:border-zinc-700 space-y-2 pt-2">
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
            </div>
          </div>
        </v-card>
      </div>
    </div>
  </div>
</template>