<script setup lang="ts">
interface Props {
  name?: string
  size?: number
}

const props = withDefaults(defineProps<Props>(), {
  name: '',
  size: 32,
})

const faceIndex = computed(() => {
  let hash = 0
  for (let i = 0; i < props.name.length; i++) {
    hash = props.name.charCodeAt(i) + ((hash << 5) - hash)
  }
  return Math.abs(hash) % faces.length
})

const faces = [
  { eyes: 'dots', mouth: 'smile', brows: true },
  { eyes: 'dots', mouth: 'open', brows: false },
  { eyes: 'lines', mouth: 'smile', brows: true },
  { eyes: 'dots', mouth: 'flat', brows: false },
  { eyes: 'wink', mouth: 'smile', brows: true },
  { eyes: 'dots', mouth: 'grin', brows: false },
  { eyes: 'lines', mouth: 'flat', brows: true },
  { eyes: 'big', mouth: 'open', brows: false },
]

const face = computed(() => faces[faceIndex.value])
</script>

<template>
  <svg
    :width="size"
    :height="size"
    viewBox="0 0 40 40"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="shrink-0"
  >
    <circle cx="20" cy="20" r="20" fill="#E8E8E8" />

    <!-- Brows -->
    <template v-if="face.brows">
      <line x1="13" y1="14" x2="17" y2="13" stroke="#555" stroke-width="1.2" stroke-linecap="round" />
      <line x1="23" y1="13" x2="27" y2="14" stroke="#555" stroke-width="1.2" stroke-linecap="round" />
    </template>

    <!-- Eyes -->
    <template v-if="face.eyes === 'dots'">
      <circle cx="15" cy="18" r="1.8" fill="#333" />
      <circle cx="25" cy="18" r="1.8" fill="#333" />
    </template>
    <template v-else-if="face.eyes === 'lines'">
      <line x1="13" y1="18" x2="17" y2="18" stroke="#333" stroke-width="1.8" stroke-linecap="round" />
      <line x1="23" y1="18" x2="27" y2="18" stroke="#333" stroke-width="1.8" stroke-linecap="round" />
    </template>
    <template v-else-if="face.eyes === 'wink'">
      <circle cx="15" cy="18" r="1.8" fill="#333" />
      <path d="M23 18 Q25 16 27 18" stroke="#333" stroke-width="1.8" fill="none" stroke-linecap="round" />
    </template>
    <template v-else-if="face.eyes === 'big'">
      <circle cx="15" cy="18" r="2.5" fill="#333" />
      <circle cx="25" cy="18" r="2.5" fill="#333" />
      <circle cx="15.8" cy="17.2" r="0.8" fill="white" />
      <circle cx="25.8" cy="17.2" r="0.8" fill="white" />
    </template>

    <!-- Mouth -->
    <template v-if="face.mouth === 'smile'">
      <path d="M16 25 Q20 29 24 25" stroke="#333" stroke-width="1.5" fill="none" stroke-linecap="round" />
    </template>
    <template v-else-if="face.mouth === 'open'">
      <ellipse cx="20" cy="26" rx="3" ry="2.2" fill="#333" />
    </template>
    <template v-else-if="face.mouth === 'flat'">
      <line x1="16" y1="26" x2="24" y2="26" stroke="#333" stroke-width="1.5" stroke-linecap="round" />
    </template>
    <template v-else-if="face.mouth === 'grin'">
      <path d="M15 24 Q20 30 25 24" stroke="#333" stroke-width="1.5" fill="none" stroke-linecap="round" />
      <line x1="17" y1="25.5" x2="23" y2="25.5" stroke="#333" stroke-width="0.8" stroke-linecap="round" />
    </template>
  </svg>
</template>
