<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
  title: String,
  subtitle: { type: String, default: 'Live records from the project database' },
  items: { type: Array, default: () => [] },
  fields: { type: Array, default: () => [] },
})

function value(item, field) {
  return field.path.split('.').reduce((current, key) => current?.[key], item) ?? '—'
}
</script>

<template>
  <AppLayout :title="title"><div class="pm-dash"><PageHeader :title="title" :subtitle="subtitle" />
    <div class="box"><div class="box-body p-0"><div v-if="!items.length" class="p-12 text-center text-textmuted">No records have been created yet.</div>
      <div v-else class="table-responsive"><table class="table table-hover"><thead><tr><th v-for="field in fields" :key="field.path">{{ field.label }}</th></tr></thead><tbody><tr v-for="item in items" :key="item.id"><td v-for="field in fields" :key="field.path">{{ value(item, field) }}</td></tr></tbody></table></div>
    </div></div>
  </div></AppLayout>
</template>
