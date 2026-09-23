<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Gantt Chart' },
  tasks: { type: Array, default: () => [] },
  milestones: { type: Array, default: () => [] },
})

const weeks = ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8']

const bars = computed(() => {
  const items = [
    ...props.tasks.map((task) => ({
      id: `task-${task.id}`,
      name: task.title,
      date: task.due_date,
      progress: { todo: 0, in_progress: 45, review: 75, done: 100 }[task.status] ?? 0,
      color: task.status === 'done' ? 'bg-success' : task.status === 'todo' ? 'bg-secondary' : 'bg-primary',
    })),
    ...props.milestones.map((milestone) => ({
      id: `ms-${milestone.id}`,
      name: milestone.title,
      date: milestone.due_date,
      progress: milestone.status === 'completed' ? 100 : milestone.status === 'in_progress' ? 50 : 0,
      color: milestone.status === 'completed' ? 'bg-success' : milestone.status === 'in_progress' ? 'bg-primary' : 'bg-secondary',
    })),
  ].filter((item) => item.date)

  if (!items.length) {
    return []
  }

  const times = items.map((item) => new Date(item.date).getTime())
  const min = Math.min(...times)
  const span = Math.max(Math.max(...times) - min, 1000 * 60 * 60 * 24 * 7)

  return items.map((item) => {
    const start = ((new Date(item.date).getTime() - min) / span) * 87.5
    return { ...item, start, duration: 12.5 }
  })
})
</script>

<template>
  <AppLayout title="Gantt Chart">
    <div class="pm-dash">
      <PageHeader title="Gantt Chart" subtitle="Visual project timeline from due dates" />

      <div class="box">
        <div class="box-body overflow-x-auto">
          <p v-if="!bars.length" class="text-center text-textmuted py-8 mb-0">No dated tasks or milestones to plot yet.</p>
          <div v-else class="min-w-[900px]">
            <div class="flex border-b">
              <div class="w-48 p-3 font-medium bg-light">Task Name</div>
              <div class="flex-1 flex">
                <div v-for="week in weeks" :key="week" class="flex-1 p-3 text-center text-sm text-textmuted border-l bg-light">{{ week }}</div>
              </div>
            </div>
            <div v-for="task in bars" :key="task.id" class="flex border-b hover:bg-light">
              <div class="w-48 p-3 flex items-center gap-2">
                <span class="w-3 h-3 rounded" :class="task.color"></span>
                <span class="text-sm">{{ task.name }}</span>
              </div>
              <div class="flex-1 relative h-12">
                <div
                  class="absolute top-2 h-8 rounded flex items-center px-2 text-xs text-white"
                  :class="task.color"
                  :style="{ left: task.start + '%', width: task.duration + '%' }"
                >
                  {{ task.progress }}%
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex gap-4 mt-4">
        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-success"></span><span class="text-sm text-textmuted">Completed</span></div>
        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-primary"></span><span class="text-sm text-textmuted">In Progress</span></div>
        <div class="flex items-center gap-2"><span class="w-4 h-4 rounded bg-secondary"></span><span class="text-sm text-textmuted">Not Started</span></div>
      </div>
    </div>
  </AppLayout>
</template>
