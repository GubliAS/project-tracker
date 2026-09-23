<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  title: { type: String, default: 'Forbidden' },
  message: { type: String, default: 'You do not have permission to do that.' },
})
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <div class="box max-w-xl mx-auto mt-8">
        <div class="box-body text-center py-10">
          <div class="text-4xl font-semibold text-primary mb-2">403</div>
          <h1 class="text-xl font-semibold mb-2">{{ title }}</h1>
          <p class="text-textmuted mb-4">{{ message }}</p>
          <Link href="/" class="ti-btn ti-btn-primary">Back to dashboard</Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
