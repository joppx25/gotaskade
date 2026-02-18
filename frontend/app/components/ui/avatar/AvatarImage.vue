<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { AvatarImage } from 'radix-vue'
import { cn } from '~/lib/utils'

const props = defineProps<{ class?: HTMLAttributes['class']; src?: string; alt?: string }>()
</script>

<template>
  <AvatarImage :src="props.src" :alt="props.alt" :class="cn('aspect-square h-full w-full', props.class)" />
</template>
