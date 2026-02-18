<script setup lang="ts">
import type { DateGroup } from '~/composables/useDateNavigation'
import { cn } from '~/lib/utils'

interface Props {
  groupedDates: DateGroup[]
  selectedDate: string
}

defineProps<Props>()

const emit = defineEmits<{
  selectDate: [date: string]
}>()
</script>

<template>
  <aside class="w-64 flex flex-col overflow-hidden shrink-0">
    <ScrollArea class="flex-1">
      <div class="p-3">
        <template v-for="(group, groupIndex) in groupedDates" :key="groupIndex">
          <!-- Group label (skip "current" group label) -->
          <div
            v-if="group.label !== 'current'"
            class="px-2 py-2 mt-2"
          >
            <span class="text-xs text-muted-foreground italic">{{ group.label }}</span>
          </div>

          <!-- Date items -->
          <button
            v-for="entry in group.dates"
            :key="entry.date"
            :class="cn(
              'w-full text-left px-3 py-2 text-sm transition-colors rounded-lg',
              entry.date === selectedDate
                ? 'bg-foreground text-background font-medium'
                : 'text-foreground hover:bg-accent',
            )"
            @click="emit('selectDate', entry.date)"
          >
            {{ entry.label }}
          </button>
        </template>
      </div>
    </ScrollArea>
  </aside>
</template>
