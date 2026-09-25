<script setup>
import { computed, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: String,
  tasks: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
  members: { type: Array, default: () => [] },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const currentUserId = computed(() => page.props.auth?.user?.id)
const canManageTasks = computed(() => Boolean(abilities.value.write_ops || abilities.value.write_task_details))
const search = ref('')
const statusFilter = ref('all')
const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  title: '',
  description: '',
  project_id: '',
  user_id: '',
  status: 'todo',
  priority: 'medium',
  weight: 1,
  due_date: '',
})

const assignees = computed(() => (props.members.length ? props.members : props.users))
const statuses = [
  { value: 'todo', label: 'To do' },
  { value: 'in_progress', label: 'In progress' },
  { value: 'review', label: 'Review' },
  { value: 'done', label: 'Done' },
]

const filteredTasks = computed(() => props.tasks.filter((task) => {
  const matchesSearch = task.title.toLowerCase().includes(search.value.toLowerCase())
  const matchesStatus = statusFilter.value === 'all' || task.status === statusFilter.value

  return matchesSearch && matchesStatus
}))

const counts = computed(() => ({
  all: props.tasks.length,
  todo: props.tasks.filter((task) => task.status === 'todo').length,
  in_progress: props.tasks.filter((task) => task.status === 'in_progress').length,
  review: props.tasks.filter((task) => task.status === 'review').length,
  done: props.tasks.filter((task) => task.status === 'done').length,
}))

function canChangeStatus(task) {
  return canManageTasks.value || task.user_id === currentUserId.value
}

function priorityClass(priority) {
  return {
    high: 'pm-task-pill pm-task-pill--high',
    medium: 'pm-task-pill pm-task-pill--medium',
    low: 'pm-task-pill pm-task-pill--low',
  }[priority] || 'pm-task-pill'
}

function statusClass(status) {
  return {
    todo: 'pm-task-pill pm-task-pill--todo',
    in_progress: 'pm-task-pill pm-task-pill--progress',
    review: 'pm-task-pill pm-task-pill--review',
    done: 'pm-task-pill pm-task-pill--done',
  }[status] || 'pm-task-pill'
}

function statusLabel(status) {
  return statuses.find((item) => item.value === status)?.label || status
}

function openCreate() {
  editing.value = null
  form.reset()
  form.status = 'todo'
  form.priority = 'medium'
  form.weight = 1
  form.user_id = ''
  showModal.value = true
}

function openEdit(task) {
  if (!canManageTasks.value) {
    return
  }

  editing.value = task
  Object.assign(form, {
    title: task.title,
    description: task.description || '',
    project_id: task.project_id || '',
    user_id: task.user_id || '',
    status: task.status,
    priority: task.priority,
    weight: task.weight || 1,
    due_date: task.due_date || '',
  })
  showModal.value = true
}

function submit() {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  const data = { ...form.data(), project_id: form.project_id || null, user_id: form.user_id || null }
  editing.value
    ? form.transform(() => data).put(`/tasks/${editing.value.id}`, options)
    : form.transform(() => data).post('/tasks', options)
}

function updateStatus(task, status) {
  if (task.status === status || !canChangeStatus(task)) {
    return
  }

  router.put(`/tasks/${task.id}`, { status }, { preserveScroll: true })
}

function remove(task) {
  if (confirm(`Delete “${task.title}”?`)) {
    router.delete(`/tasks/${task.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Assign work, then let people move their own status.">
        <template v-if="canManageTasks" #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> Add Task
          </button>
        </template>
      </PageHeader>

      <div class="pm-task-toolbar">
        <input v-model="search" class="ti-form-control pm-task-search" placeholder="Search tasks">
        <div class="pm-task-filters">
          <button
            type="button"
            class="pm-task-filter"
            :class="{ 'is-active': statusFilter === 'all' }"
            @click="statusFilter = 'all'"
          >
            All {{ counts.all }}
          </button>
          <button
            v-for="status in statuses"
            :key="status.value"
            type="button"
            class="pm-task-filter"
            :class="{ 'is-active': statusFilter === status.value }"
            @click="statusFilter = status.value"
          >
            {{ status.label }} {{ counts[status.value] }}
          </button>
        </div>
      </div>

      <div v-if="!filteredTasks.length" class="pm-task-empty">
        No tasks match this view yet.
      </div>

      <div v-else class="pm-task-grid">
        <article v-for="task in filteredTasks" :key="task.id" class="pm-task-card">
          <div class="pm-task-card__top">
            <span :class="priorityClass(task.priority)">{{ task.priority }}</span>
            <div v-if="canManageTasks" class="pm-task-card__actions">
              <button class="pm-table-action pm-table-action--primary" type="button" title="Edit" @click="openEdit(task)">
                <i class="ri-pencil-line"></i>
              </button>
              <button class="pm-table-action pm-table-action--danger" type="button" title="Delete" @click="remove(task)">
                <i class="ri-delete-bin-line"></i>
              </button>
            </div>
          </div>
          <h3 class="pm-task-card__title">{{ task.title }}</h3>
          <p v-if="task.description" class="pm-task-card__copy">{{ task.description }}</p>
          <div class="pm-task-card__meta">
            <span>{{ task.project?.name || 'Unassigned project' }}</span>
            <span>{{ task.user?.name || 'Unassigned' }}</span>
            <span>{{ task.due_date || 'No due date' }}</span>
          </div>
          <label class="pm-task-status">
            <span class="sr-only">Status</span>
            <select
              v-if="canChangeStatus(task)"
              class="ti-form-select"
              :value="task.status"
              @change="updateStatus(task, $event.target.value)"
            >
              <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
            </select>
            <span v-else :class="statusClass(task.status)">{{ statusLabel(task.status) }}</span>
          </label>
        </article>
      </div>

      <div v-if="showModal && canManageTasks" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Task' : 'Add Task' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Title</label>
              <input v-model="form.title" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Description</label>
              <textarea v-model="form.description" class="ti-form-control" rows="3"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Project</label>
                <select v-model="form.project_id" class="ti-form-select">
                  <option value="">Unassigned</option>
                  <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Assignee</label>
                <select v-model="form.user_id" name="user_id" class="ti-form-select">
                  <option value="">Unassigned</option>
                  <option v-for="user in assignees" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Status</label>
                <select v-model="form.status" class="ti-form-select">
                  <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Priority</label>
                <select v-model="form.priority" class="ti-form-select">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Weight</label>
                <input v-model.number="form.weight" type="number" min="1" max="8" class="ti-form-control">
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Due date</label>
              <input v-model="form.due_date" type="date" class="ti-form-control">
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Task</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
