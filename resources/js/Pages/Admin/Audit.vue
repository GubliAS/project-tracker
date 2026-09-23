<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
  title: { type: String, default: 'Audit Log' },
  logs: { type: Array, default: () => [] },
  actions: { type: Array, default: () => [] },
  filters: { type: Object, default: () => ({ action: '' }) },
})

function formatWhen(value) {
  if (!value) {
    return '—'
  }

  return new Date(value).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}

function actionLabel(action) {
  return String(action || '').replaceAll('.', ' · ').replaceAll('_', ' ')
}

function filterAction(action) {
  router.get('/admin/audit', action ? { action } : {}, { preserveState: true, replace: true })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Who changed what, in which workspace">
        <template #actions>
          <Link href="/admin" class="ti-btn ti-btn-light btn-wave">Overview</Link>
        </template>
      </PageHeader>

      <div class="box mb-4">
        <div class="box-body flex flex-wrap items-center gap-3">
          <label class="ti-form-label mb-0" for="audit-action">Action</label>
          <select
            id="audit-action"
            class="ti-form-select w-auto"
            :value="filters.action || ''"
            @change="filterAction($event.target.value)"
          >
            <option value="">All actions</option>
            <option v-for="action in actions" :key="action" :value="action">{{ actionLabel(action) }}</option>
          </select>
        </div>
      </div>

      <div class="box">
        <div class="box-body p-0">
          <div v-if="!logs.length" class="p-12 text-center text-textmuted">No audit events match this filter.</div>
          <div v-else class="table-responsive">
            <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
              <thead>
                <tr>
                  <th>When</th>
                  <th>Who</th>
                  <th>Workspace</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in logs" :key="log.id">
                  <td class="text-textmuted text-sm">{{ formatWhen(log.created_at) }}</td>
                  <td>
                    <div class="font-medium">{{ log.user?.name || 'System' }}</div>
                    <div v-if="log.user?.email" class="text-textmuted text-xs">{{ log.user.email }}</div>
                  </td>
                  <td>{{ log.workspace?.name || '—' }}</td>
                  <td class="capitalize">{{ actionLabel(log.action) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
