<script setup lang="ts">
interface Props {
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
})

const sizeClasses = {
  sm: 'w-[28px] h-auto',
  md: 'w-[60px] h-auto',
  lg: 'w-[80px] h-auto',
}
</script>

<template>
  <svg
    :class="sizeClasses[props.size]"
    viewBox="0 0 220 160"
    xmlns="http://www.w3.org/2000/svg"
  >
    <polygon
      fill="currentColor"
      fill-rule="evenodd"
      points="167.46 95.51 147.7 129.75 111.78 129.75 92.07 95.52 167.46 95.51 167.46 95.51"
    />
    <polygon
      fill="currentColor"
      fill-rule="evenodd"
      points="72.32 129.72 52.54 95.49 70.51 64.38 110 64.43 72.32 129.72 72.32 129.72"
    />
    <polygon
      fill="currentColor"
      fill-rule="evenodd"
      points="90.23 30.26 129.76 30.25 147.73 61.36 127.94 95.54 90.23 30.26 90.23 30.26"
    />
  </svg>
</template>
