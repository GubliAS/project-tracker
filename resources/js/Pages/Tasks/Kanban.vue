<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: String,
  tasks: { type: Array, default: () => [] },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const currentUserId = computed(() => page.props.auth?.user?.id)
const canManageTasks = computed(() => Boolean(abilities.value.write_ops || abilities.value.write_task_details))

const statuses = ['todo', 'in_progress', 'review', 'done']
const columns = computed(() => statuses.map((status) => ({
  status,
  tasks: props.tasks.filter((task) => task.status === status),
})))

function canChangeStatus(task) {
  return canManageTasks.value || task.user_id === currentUserId.value
}

const move = (task, status) => {
  if (task.status === status || !canChangeStatus(task)) {
    return
  }

  router.put(`/tasks/${task.id}`, { status }, { preserveScroll: true })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Live task status from the database" />
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <section v-for="column in columns" :key="column.status" class="box">
          <div class="box-header font-medium">{{ column.status.replace('_', ' ') }}</div>
          <div class="box-body space-y-3">
            <p v-if="!column.tasks.length" class="text-sm text-textmuted">No tasks</p>
            <article v-for="task in column.tasks" :key="task.id" class="rounded border p-3">
              <div class="mb-1 flex items-center justify-between gap-2">
                <p class="mb-0 font-medium">{{ task.title }}</p>
                <span
                  class="badge"
                  :class="{
                    'bg-danger/10 text-danger': task.priority === 'high',
                    'bg-warning/10 text-warning': task.priority === 'medium',
                    'bg-success/10 text-success': task.priority === 'low',
                  }"
                >{{ task.priority }}</span>
              </div>
              <p class="mb-3 text-xs text-textmuted">{{ task.project?.name || 'No project' }} · {{ task.user?.name || 'Unassigned' }}</p>
              <div v-if="canChangeStatus(task)" class="flex flex-wrap gap-1">
                <button
                  v-for="status in statuses"
                  :key="status"
                  class="ti-btn ti-btn-sm"
                  :class="task.status === status ? 'ti-btn-primary' : 'ti-btn-light'"
                  type="button"
                  @click="move(task, status)"
                >
                  {{ status.replace('_', ' ') }}
                </button>
              </div>
              <span v-else class="badge bg-primary/10 text-primary">{{ task.status.replace('_', ' ') }}</span>
            </article>
          </div>
        </section>
      </div>
    </div>
  </AppLayout>
</template>
