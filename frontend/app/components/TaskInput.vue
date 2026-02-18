<script setup lang="ts">
import { ref } from 'vue'
import { ArrowUp } from 'lucide-vue-next'

interface Props {
  placeholder?: string
  isFirstTask?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'What else do you need to do?',
  isFirstTask: false,
})

const emit = defineEmits<{
  submit: [statement: string]
}>()

const statement = ref('')

function handleSubmit() {
  if (!statement.value.trim()) return
  emit('submit', statement.value.trim())
  statement.value = ''
}

function handleKeydown(event: KeyboardEvent) {
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    handleSubmit()
  }
}
</script>

<template>
  <div
    :class="[
      'relative border border-border rounded-xl overflow-hidden',
      props.isFirstTask ? 'min-h-[120px]' : '',
    ]"
  >
    <textarea
      v-if="props.isFirstTask"
      v-model="statement"
      placeholder="Write the task you plan to do today here..."
      class="w-full resize-none border-0 bg-transparent px-4 py-3 text-sm placeholder:text-muted-foreground focus:outline-none min-h-[100px]"
      @keydown="handleKeydown"
    />
    <input
      v-else
      v-model="statement"
      id="task-input"
      type="text"
      :placeholder="props.placeholder"
      class="w-full border-0 bg-transparent px-4 py-3 pr-12 text-sm placeholder:text-muted-foreground focus:outline-none h-[44px]"
      @keydown="handleKeydown"
    >
    <button
      class="absolute bottom-3 right-3 h-8 w-8 rounded-full bg-foreground text-background flex items-center justify-center hover:bg-foreground/80 transition-colors disabled:opacity-40" style="top: 6px;"
      :disabled="!statement.trim()"
      @click="handleSubmit"
    >
      <ArrowUp class="h-4 w-4" />
    </button>
  </div>
</template>
