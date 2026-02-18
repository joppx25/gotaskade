<script setup lang="ts">
import { ref, nextTick } from 'vue'
import { Trash2 } from 'lucide-vue-next'
import type { Task } from '~/stores/tasks'
import { cn } from '~/lib/utils'

interface Props {
  task: Task
}

const props = defineProps<Props>()

const emit = defineEmits<{
  toggle: [taskId: number]
  update: [taskId: number, description: string]
  delete: [taskId: number]
}>()

const isEditing = ref(false)
const editValue = ref('')
const editInput = ref<HTMLInputElement>()

function startEdit() {
  isEditing.value = true
  editValue.value = props.task.description
  nextTick(() => {
    editInput.value?.focus()
  })
}

function saveEdit() {
  if (editValue.value.trim() && editValue.value.trim() !== props.task.description) {
    emit('update', props.task.id, editValue.value.trim())
  }
  isEditing.value = false
}

function cancelEdit() {
  isEditing.value = false
}

function handleEditKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter') {
    event.preventDefault()
    saveEdit()
  }
  else if (event.key === 'Escape') {
    cancelEdit()
  }
}
</script>

<template>
  <div
    :class="cn(
      'group flex items-center gap-3 px-5 py-3.5 rounded-xl border border-border bg-white transition-colors hover:bg-muted/30 cursor-grab',
      task.is_completed ? 'opacity-80' : '',
    )"
  >
    <!-- Custom circle checkbox -->
    <button
      :class="cn(
        'h-5 w-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-colors',
        task.is_completed
          ? 'bg-blue-500 border-blue-500 text-white'
          : 'border-muted-foreground/30 hover:border-muted-foreground/60',
      )"
      @click="emit('toggle', task.id)"
    >
      <svg
        v-if="task.is_completed"
        class="h-3 w-3"
        fill="none"
        viewBox="0 0 24 24"
        stroke="currentColor"
        stroke-width="3"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
      </svg>
    </button>

    <!-- Task description -->
    <div class="flex-1 min-w-0">
      <input
        v-if="isEditing"
        ref="editInput"
        v-model="editValue"
        class="w-full bg-transparent text-sm border-b border-foreground/20 focus:border-foreground focus:outline-none py-0.5"
        @blur="saveEdit"
        @keydown="handleEditKeydown"
      >
      <span
        v-else
        :class="cn(
          'text-sm cursor-pointer block truncate',
          task.is_completed ? 'line-through text-muted-foreground' : 'text-foreground',
        )"
        @click="startEdit"
      >
        {{ task.description }}
      </span>
    </div>

    <!-- Delete button -->
    <button
      class="h-7 w-7 flex items-center justify-center rounded text-muted-foreground/40 hover:text-destructive hover:bg-destructive/10 transition-all shrink-0"
      @click="emit('delete', task.id)"
    >
      <Trash2 class="h-4 w-4" />
    </button>
  </div>
</template>
