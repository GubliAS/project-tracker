<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [String, Number],
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  iconBg: {
    type: String,
    default: 'bg-primary'
  },
  badge: {
    type: String,
    default: ''
  },
  badgeClass: {
    type: String,
    default: 'bg-success/10 text-success'
  }
})
</script>

<template>
  <div class="box overflow-hidden pm-stat-tile">
    <div class="box-body">
      <div class="flex items-center justify-between mb-4">
        <span
          class="avatar avatar-md avatar-rounded text-white"
          :class="iconBg"
          style="display: inline-flex; align-items: center; justify-content: center;"
        >
          <i :class="icon" style="font-size: 1.25rem; line-height: 1;"></i>
        </span>
        <span v-if="badge" class="badge" :class="badgeClass">{{ badge }}</span>
      </div>
      <div class="flex items-end justify-between gap-2">
        <div>
          <div class="text-textmuted dark:text-textmuted/50 mb-1">{{ title }}</div>
          <h4 class="mb-0 text-2xl font-semibold">{{ value }}</h4>
        </div>
        <div v-if="$slots.sparkline" class="flex-shrink-0 text-end ms-auto">
          <slot name="sparkline" />
        </div>
      </div>
    </div>
  </div>
</template>
