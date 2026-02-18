import { defineStore } from 'pinia'

export interface Task {
  id: number
  description: string
  is_completed: boolean
  task_date: string
  sort_order: number
  created_at: string
  updated_at: string
}

interface TaskState {
  tasks: Task[]
  searchQuery: string
  loading: boolean
}

export const useTaskStore = defineStore('tasks', {
  state: (): TaskState => ({
    tasks: [],
    searchQuery: '',
    loading: false,
  }),

  actions: {
    async fetchTasks(date?: string, search?: string) {
      const api = useApi()
      this.loading = true

      try {
        const params: Record<string, string> = {}
        if (date) params.date = date
        if (search) params.search = search

        const response = await api.get<{ data: Task[] }>('/tasks', params)
        this.tasks = response.data
      }
      catch {
        this.tasks = []
      }
      finally {
        this.loading = false
      }
    },

    async addTask(description: string, taskDate: string) {
      const api = useApi()

      const response = await api.post<{ data: Task }>('/tasks', {
        description,
        task_date: taskDate,
      })

      this.tasks.push(response.data)
    },

    async toggleTask(taskId: number) {
      const api = useApi()
      const task = this.tasks.find(t => t.id === taskId)
      if (!task) return

      const response = await api.patch<{ data: Task }>(`/tasks/${taskId}`, {
        is_completed: !task.is_completed,
      })

      const index = this.tasks.findIndex(t => t.id === taskId)
      if (index !== -1) {
        this.tasks[index] = response.data
      }
    },

    async updateTask(taskId: number, description: string) {
      const api = useApi()

      const response = await api.patch<{ data: Task }>(`/tasks/${taskId}`, {
        description,
      })

      const index = this.tasks.findIndex(t => t.id === taskId)
      if (index !== -1) {
        this.tasks[index] = response.data
      }
    },

    async deleteTask(taskId: number) {
      const api = useApi()

      await api.del(`/tasks/${taskId}`)

      this.tasks = this.tasks.filter(t => t.id !== taskId)
    },

    async reorderTasks(orderedIds: number[]) {
      const api = useApi()

      const items = orderedIds.map((id, index) => ({
        id,
        sort_order: index,
      }))

      await api.post('/tasks/reorder', { items })

      items.forEach(({ id, sort_order }) => {
        const task = this.tasks.find(t => t.id === id)
        if (task) {
          task.sort_order = sort_order
        }
      })
    },

    setSearchQuery(query: string) {
      this.searchQuery = query
    },
  },

  getters: {
    sortedTasks: (state): Task[] => {
      return [...state.tasks].sort((a, b) => a.sort_order - b.sort_order)
    },

    isSearching: (state): boolean => {
      return state.searchQuery.trim().length > 0
    },
  },
})
