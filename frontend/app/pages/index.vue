<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useTaskStore, type Task } from '~/stores/tasks'
import { useDateNavigation } from '~/composables/useDateNavigation'

definePageMeta({
  middleware: 'auth',
})

const taskStore = useTaskStore()
const { selectedDate, groupedDates, selectDate } = useDateNavigation()

const deleteConfirmId = ref<number | null>(null)

// Fetch tasks when date changes or on mount
async function loadTasks() {
  if (taskStore.isSearching) {
    await taskStore.fetchTasks(undefined, taskStore.searchQuery)
  }
  else {
    await taskStore.fetchTasks(selectedDate.value)
  }
}

onMounted(() => {
  loadTasks()
})

watch(selectedDate, () => {
  if (!taskStore.isSearching) {
    loadTasks()
  }
})

watch(() => taskStore.searchQuery, (query) => {
  if (query.trim()) {
    taskStore.fetchTasks(undefined, query)
  }
  else {
    taskStore.fetchTasks(selectedDate.value)
  }
})

const currentTasks = computed((): Task[] => {
  return taskStore.sortedTasks
})

const hasTasks = computed(() => currentTasks.value.length > 0)

async function handleAddTask(description: string) {
  try {
    await taskStore.addTask(description, selectedDate.value)
  }
  catch (e) {
    console.error('Failed to add task:', e)
  }
}

async function handleToggleTask(taskId: number) {
  await taskStore.toggleTask(taskId)
}

async function handleUpdateTask(taskId: number, description: string) {
  await taskStore.updateTask(taskId, description)
}

function handleDeleteTask(taskId: number) {
  deleteConfirmId.value = taskId
}

async function confirmDelete() {
  if (deleteConfirmId.value) {
    await taskStore.deleteTask(deleteConfirmId.value)
    deleteConfirmId.value = null
  }
}

function cancelDelete() {
  deleteConfirmId.value = null
}

function handleDragStart(event: DragEvent, task: Task) {
  event.dataTransfer?.setData('text/plain', String(task.id))
}

function handleDragOver(event: DragEvent) {
  event.preventDefault()
}

async function handleDrop(event: DragEvent, targetTask: Task) {
  event.preventDefault()
  const draggedId = Number(event.dataTransfer?.getData('text/plain'))
  if (!draggedId || draggedId === targetTask.id) return

  const tasks = [...currentTasks.value]
  const draggedIndex = tasks.findIndex(t => t.id === draggedId)
  const targetIndex = tasks.findIndex(t => t.id === targetTask.id)

  if (draggedIndex === -1 || targetIndex === -1) return

  const [draggedTask] = tasks.splice(draggedIndex, 1)
  tasks.splice(targetIndex, 0, draggedTask)

  const orderedIds = tasks.map(t => t.id)
  await taskStore.reorderTasks(orderedIds)
}
</script>

<template>
  <DateSidebar
    :grouped-dates="groupedDates"
    :selected-date="selectedDate"
    @select-date="selectDate"
  />

  <!-- Main Content -->
  <main class="flex-1 flex flex-col min-h-0 min-w-0 overflow-hidden">
    <div class="flex-1 overflow-y-auto min-h-0">
      <!-- Search results header -->
      <div v-if="taskStore.isSearching" class="px-8 pt-6 pb-2">
        <p class="text-sm text-muted-foreground">
          Search results for "{{ taskStore.searchQuery }}"
        </p>
      </div>

      <!-- Empty state -->
      <div
        v-if="!hasTasks && !taskStore.isSearching && !taskStore.loading"
        class="h-full flex flex-col items-center justify-center px-12"
      >
        <h2 class="text-2xl font-bold text-foreground mb-6">
          What do you have in mind?
        </h2>
        <div class="w-full max-w-2xl">
          <TaskInput
            :is-first-task="true"
            @submit="handleAddTask"
          />
        </div>
      </div>

      <!-- Search empty state -->
      <div
        v-else-if="taskStore.isSearching && !hasTasks && !taskStore.loading"
        class="h-full flex flex-col items-center justify-center px-12"
      >
        <p class="text-muted-foreground text-sm">
          No tasks found matching your search.
        </p>
      </div>

      <!-- Task list -->
      <div v-else-if="hasTasks" class="max-w-3xl mx-auto w-full px-8 pt-4 pb-2">
        <div class="space-y-2">
          <TaskItem
            v-for="task in currentTasks"
            :key="task.id"
            :task="task"
            draggable="true"
            @toggle="handleToggleTask"
            @update="handleUpdateTask"
            @delete="handleDeleteTask"
            @dragstart="handleDragStart($event, task)"
            @dragover="handleDragOver"
            @drop="handleDrop($event, task)"
          />
        </div>
      </div>
    </div>

    <!-- Bottom input (shown when tasks exist) -->
    <div v-if="hasTasks && !taskStore.isSearching" class="shrink-0 max-w-3xl mx-auto w-full px-8 pb-4 pt-2">
      <TaskInput @submit="handleAddTask" />
    </div>
  </main>

  <!-- Delete Confirmation Dialog -->
  <Dialog :open="!!deleteConfirmId" @update:open="cancelDelete">
    <DialogContent :show-close="false" class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle>Delete Task</DialogTitle>
        <DialogDescription>
          Are you sure you want to delete this task? This action cannot be undone.
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="gap-2">
        <Button variant="outline" @click="cancelDelete">
          Cancel
        </Button>
        <Button variant="destructive" @click="confirmDelete">
          Delete
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
