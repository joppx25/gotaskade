<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { Separator } from 'radix-vue'
import { cn } from '~/lib/utils'

interface Props {
  class?: HTMLAttributes['class']
  orientation?: 'horizontal' | 'vertical'
  decorative?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  orientation: 'horizontal',
  decorative: true,
})
</script>

<template>
  <Separator
    :decorative="props.decorative"
    :orientation="props.orientation"
    :class="cn(
      'shrink-0 bg-border',
      props.orientation === 'horizontal' ? 'h-px w-full' : 'h-full w-px',
      props.class,
    )"
  />
</template>
