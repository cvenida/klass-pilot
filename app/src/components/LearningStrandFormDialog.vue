<script setup>
import { ref, watch, computed } from 'vue'
import { capitalize } from 'lodash'
import { LEARNING_STRAND_STATUS } from '@/shared/constants'

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  isLoading: {
    type: Boolean,
    default: false
  },
  initialData: {
    type: Object,
    default: null
  },
  availableTags: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue', 'submit'])

const isEditMode = computed(() => !!props.initialData?.id)

const defaultTags = ['PHP', 'Laravel', 'Vue 3', 'Tailwind', 'Backend']

const formData = ref({
  id: null,
  title: '',
  description: '',
  tags: [],
  status: 'draft',
  cooldownDays: 0
})

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      if (props.initialData) {
        formData.value = {
          id: props.initialData.id ?? null,
          title: props.initialData.title || '',
          description: props.initialData.description || '',
          tags: Array.isArray(props.initialData.learning_strand_tags) 
            ? [...props.initialData.learning_strand_tags] 
            : Array.isArray(props.initialData.tags) 
              ? [...props.initialData.tags] 
              : [],
          status: props.initialData.status || 'draft',
          cooldownDays: props.initialData.reapply_cooldown_days || 0
        }
      } else {
        formData.value = {
          id: null,
          title: '',
          description: '',
          tags: [],
          status: 'draft',
          cooldownDays: 0
        }
      }
    }
  }
)

const closeDialog = () => {
  emit('update:modelValue', false)
}

const handleSubmit = () => {
  if (!formData.value.title) return
  emit('submit', { ...formData.value })
}
</script>

<template>
  <v-dialog
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    max-width="520px"
  >
    <v-card class="rounded-2xl p-2 bg-surface border border-zinc-200 dark:border-zinc-800">
      <v-card-title class="text-lg font-bold text-zinc-900 dark:text-zinc-100 pt-4 px-4">
        {{ isEditMode ? 'Update Learning Strand' : 'Add New Learning Strand' }}
      </v-card-title>

      <v-card-text class="space-y-4 px-4 py-2">
        <v-text-field
          v-model="formData.title"
          label="Learning Strand Title"
          variant="outlined"
          density="comfortable"
          rounded="lg"
        ></v-text-field>

        <v-textarea
          v-model="formData.description"
          label="Learning Strand Description"
          variant="outlined"
          density="comfortable"
          rows="3"
          rounded="lg"
        ></v-textarea>

        <v-select
          v-if="isEditMode"
          v-model="formData.status"
          :items="LEARNING_STRAND_STATUS"
          label="Status"
          variant="outlined"
          density="comfortable"
          rounded="lg"
        >
          <template #selection="{ item }">
            <span class="text-capitalize">{{ capitalize(item) }}</span>
          </template>
          <template #item="{ item, props: itemProps }">
            <v-list-item v-bind="itemProps" :title="capitalize(item)" class="text-capitalize" />
          </template>
        </v-select>

        <v-combobox
          v-model="formData.tags"
          :items="defaultTags"
          variant="outlined"
          chips
          multiple
          clearable
          label="Learning Strand Tags"
          density="comfortable"
          rounded="lg"
          hint="Type a tag and press Enter to add"
          persistent-hint
        >
          <template #chip="{ props: chipProps, item }">
            <v-chip
              v-bind="chipProps"
              size="small"
              class="text-capitalize font-medium"
              closable
            >
              {{ item }}
            </v-chip>
          </template>
        </v-combobox>

        <v-text-field
          type="number"
          v-model.number="formData.cooldownDays"
          label="Reapply Cooldown Days"
          variant="outlined"
          density="comfortable"
          rounded="lg"
          min="0"
          hint="Indicate how many days a student can reapply after rejection."
          persistent-hint
        ></v-text-field>
      </v-card-text>

      <v-card-actions class="p-4 flex justify-end gap-2">
        <v-btn variant="text" rounded="lg" @click="closeDialog">Cancel</v-btn>
        <v-btn
          color="primary"
          variant="elevated"
          rounded="lg"
          :loading="isLoading"
          class="capitalize font-semibold"
          @click="handleSubmit"
        >
          {{ isEditMode ? 'Save Changes' : 'Create Learning Strand' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>