<script setup>
import { computed, ref } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
  icon: { type: String, default: 'ri-lock-2-line' },
})

const visible = ref(false)
const inputType = computed(() => (visible.value ? 'text' : 'password'))
</script>

<template>
  <div class="pm-auth__control pm-password-input">
    <i v-if="icon" :class="icon" aria-hidden="true"></i>
    <input :type="inputType" class="form-control" v-bind="$attrs">
    <button
      type="button"
      class="pm-password-input__toggle"
      :aria-label="visible ? 'Hide password' : 'Show password'"
      :aria-pressed="visible"
      @click="visible = !visible"
    >
      <i :class="visible ? 'ri-eye-off-line' : 'ri-eye-line'" aria-hidden="true"></i>
    </button>
  </div>
</template>
