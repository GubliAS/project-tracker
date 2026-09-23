<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: String,
  tasks: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const search = ref('')
const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  title: '',
  description: '',
  project_id: '',
  user_id: '',
  status: 'todo',
  priority: 'medium',
  due_date: '',
})

const tasks = computed(() => props.tasks.filter((task) => task.title.toLowerCase().includes(search.value.toLowerCase())))

const openCreate = () => {
  editing.value = null
  form.reset()
  form.status = 'todo'
  form.priority = 'medium'
  showModal.value = true
}

const openEdit = (task) => {
  editing.value = task
  Object.assign(form, {
    title: task.title,
    description: task.description || '',
    project_id: task.project_id || '',
    user_id: task.user_id || '',
    status: task.status,
    priority: task.priority,
    due_date: task.due_date || '',
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  const data = { ...form.data(), project_id: form.project_id || null, user_id: form.user_id || null }
  editing.value
    ? form.transform(() => data).put(`/tasks/${editing.value.id}`, options)
    : form.transform(() => data).post('/tasks', options)
}

const remove = (task) => {
  if (confirm(`Delete “${task.title}”?`)) {
    router.delete(`/tasks/${task.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Tasks stored in the tracker database">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> Add Task
          </button>
        </template>
      </PageHeader>
      <CreateHero :title="title" subtitle="Add a task or refine one already in the tracker." pill="Tasks" />
      <div class="box">
        <div class="box-header">
          <input v-model="search" class="ti-form-control max-w-sm" placeholder="Search tasks">
        </div>
        <div class="box-body p-0">
          <div v-if="!tasks.length" class="p-12 text-center text-textmuted">No tasks have been created yet.</div>
          <table v-else class="table table-hover">
            <thead>
              <tr>
                <th>Task</th>
                <th>Project</th>
                <th>Assignee</th>
                <th>Status</th>
                <th>Due</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="task in tasks" :key="task.id">
                <td>{{ task.title }}</td>
                <td>{{ task.project?.name || 'Unassigned' }}</td>
                <td>{{ task.user?.name || 'Unassigned' }}</td>
                <td><span class="badge bg-primary/10 text-primary">{{ task.status }}</span></td>
                <td>{{ task.due_date || '—' }}</td>
                <td>
                  <div class="flex gap-1">
                    <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" type="button" @click="openEdit(task)"><i class="ri-edit-line"></i></button>
                    <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(task)"><i class="ri-delete-bin-line"></i></button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
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
                <select v-model="form.user_id" class="ti-form-select">
                  <option value="">Unassigned</option>
                  <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Status</label>
                <select v-model="form.status" class="ti-form-select">
                  <option value="todo">To do</option>
                  <option value="in_progress">In progress</option>
                  <option value="review">Review</option>
                  <option value="done">Done</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Priority</label>
                <select v-model="form.priority" class="ti-form-select">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
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
