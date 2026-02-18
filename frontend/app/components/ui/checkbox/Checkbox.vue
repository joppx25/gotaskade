<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { CheckboxIndicator, CheckboxRoot, type CheckboxRootEmits, type CheckboxRootProps } from 'radix-vue'
import { Check } from 'lucide-vue-next'
import { cn } from '~/lib/utils'
import { useForwardPropsEmits } from 'radix-vue'

const props = defineProps<CheckboxRootProps & { class?: HTMLAttributes['class'] }>()
const emits = defineEmits<CheckboxRootEmits>()

const delegatedProps = useForwardPropsEmits(props, emits)
</script>

<template>
  <CheckboxRoot
    v-bind="delegatedProps"
    :class="cn('peer h-4 w-4 shrink-0 rounded-sm border border-primary shadow focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground', props.class)"
  >
    <CheckboxIndicator class="flex items-center justify-center text-current">
      <Check class="h-4 w-4" />
    </CheckboxIndicator>
  </CheckboxRoot>
</template>
