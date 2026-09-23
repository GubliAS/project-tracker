<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
  pill: {
    type: String,
    default: 'Create',
  },
  src: {
    type: String,
    default: '/assets/img/create-illustration.png',
  },
  icon: {
    type: String,
    default: 'ri-pencil-fill',
  },
})
</script>

<template>
  <div class="pm-create-hero">
    <span class="pm-create-hero__glow" aria-hidden="true"></span>
    <div class="pm-create-hero__copy">
      <span v-if="pill" class="pm-focus-pill">
        <i :class="icon" aria-hidden="true"></i>
        {{ pill }}
      </span>
      <h2>{{ title }}</h2>
      <p v-if="subtitle">{{ subtitle }}</p>
      <div v-if="$slots.default" class="pm-create-hero__extra">
        <slot />
      </div>
      <div v-if="$slots.actions" class="pm-create-hero__actions">
        <slot name="actions" />
      </div>
    </div>
    <div class="pm-create-hero__art">
      <img :src="src" alt="" />
    </div>
  </div>
</template>
