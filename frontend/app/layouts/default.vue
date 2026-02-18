<script setup lang="ts">
import { Search } from 'lucide-vue-next'
import { useAuthStore } from '~/stores/auth'
import { useTaskStore } from '~/stores/tasks'

const authStore = useAuthStore()
const taskStore = useTaskStore()

async function handleLogout() {
  await authStore.logout()
  window.location.href = '/login'
}

function handleSearch(event: Event) {
  const target = event.target as HTMLInputElement
  taskStore.setSearchQuery(target.value)
}
</script>

<template>
  <div class="h-screen bg-white">
    <div class="h-full flex flex-col overflow-hidden">
      <!-- Top Navbar -->
      <header class="shrink-0 flex items-center justify-between px-6 py-3 border-b border-border">
        <NuxtLink to="/" class="flex items-center">
          <AppLogo size="md" />
        </NuxtLink>

        <div class="relative flex-1 max-w-md mx-8">
          <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <input
            type="text"
            placeholder="Search"
            :value="taskStore.searchQuery"
            class="w-full h-9 pl-9 pr-4 rounded-full border border-border bg-muted/40 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring"
            @input="handleSearch"
          >
        </div>

        <button class="rounded-full hover:opacity-80 transition-opacity" @click="handleLogout">
          <NotionAvatar :name="authStore.user?.name || ''" :size="32" />
        </button>
      </header>

      <!-- Body -->
      <div class="flex flex-1 min-h-0 overflow-hidden">
        <slot />
      </div>
    </div>
  </div>
</template>
