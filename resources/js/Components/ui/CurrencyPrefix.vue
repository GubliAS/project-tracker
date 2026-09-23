<script setup>
import { computed } from 'vue'
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  code: { type: String, default: null },
})

const { symbol } = useCurrency(computed(() => props.code))
</script>

<template>
  <span class="input-group-text">{{ symbol }}</span>
</template>
