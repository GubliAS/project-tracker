<script setup>
import { computed, ref, watch } from 'vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CurrencyPrefix from '@/Components/ui/CurrencyPrefix.vue'
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  title: { type: String, default: 'Project Details' },
  project: { type: Object, required: true },
  members: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const canUploadDocuments = computed(() => Boolean(abilities.value.write_member))
const canDeleteDocuments = computed(() => Boolean(abilities.value.write_ops))
const documents = computed(() => props.project.documents || [])
const tabFromUrl = computed(() => {
  const url = page.url || ''
  const query = url.includes('?') ? url.slice(url.indexOf('?') + 1) : ''

  return new URLSearchParams(query).get('tab')
})
const isDragging = ref(false)

const statusLabels = {
  planning: 'Planning',
  active: 'In Progress',
  on_hold: 'On Hold',
  completed: 'Completed',
}

const taskStatusLabels = {
  todo: 'Pending',
  in_progress: 'In Progress',
  review: 'Review',
  done: 'Completed',
}

const project = computed(() => {
  const current = props.project
  const tasks = current.tasks || []
  const done = tasks.filter((task) => task.status === 'done').length

  return {
    ...current,
    team: current.team || 'Unassigned',
    client: current.client || '—',
    priority: current.priority || 'medium',
    startDate: current.start_date,
    endDate: current.end_date,
    budget: Number(current.budget || 0),
    spent: Number(current.spent || 0),
    progress: current.progress_percent ?? (tasks.length ? Math.round((done / tasks.length) * 100) : 0),
  }
})

const tasks = computed(() => (props.project.tasks || []).map((task) => ({
  ...task,
  assignee: task.user?.name || 'Unassigned',
  label: taskStatusLabels[task.status] || task.status,
})))

const activeTab = ref(tabFromUrl.value === 'files' ? 'files' : 'overview')

watch(tabFromUrl, (tab) => {
  if (tab === 'files') {
    activeTab.value = 'files'
  }
})
const uploadForm = useForm({
  file: null,
  category: 'other',
  project_id: props.project.id,
  return_to_project: true,
})
const assignees = computed(() => (props.members.length ? props.members : props.users))
const showAddTaskModal = ref(false)
const showEditModal = ref(false)
const emptyTask = () => ({
  title: '',
  user_id: '',
  status: 'todo',
  priority: 'medium',
  weight: 1,
})
const newTask = ref(emptyTask())
const editForm = ref({
  name: '',
  description: '',
  status: 'planning',
  team: '',
  client: '',
  priority: 'medium',
  start_date: '',
  end_date: '',
  budget: 0,
  spent: 0,
  currency: '',
})

const formatDate = (dateStr) => {
  if (!dateStr) {
    return '—'
  }

  return new Date(dateStr).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

const { formatCurrency, currencies } = useCurrency(computed(() => props.project.currency))

const navigateToStakeholders = () => {
  router.visit(`/initiation/stakeholders?projectId=${props.project.id}`)
}

const openAddTaskModal = () => {
  activeTab.value = 'tasks'
  showAddTaskModal.value = true
}

const closeAddTaskModal = () => {
  showAddTaskModal.value = false
  newTask.value = emptyTask()
}

const openEditModal = () => {
  editForm.value = {
    name: props.project.name,
    description: props.project.description || '',
    status: props.project.status,
    team: props.project.team || '',
    client: props.project.client || '',
    priority: props.project.priority || 'medium',
    start_date: props.project.start_date || '',
    end_date: props.project.end_date || '',
    budget: props.project.budget || 0,
    spent: props.project.spent || 0,
    currency: props.project.currency || '',
  }
  showEditModal.value = true
}

const saveProject = () => {
  router.put(`/projects/${props.project.id}`, editForm.value, {
    onSuccess: () => {
      showEditModal.value = false
    },
  })
}

const deleteProject = () => {
  if (confirm(`Delete “${props.project.name}”? This cannot be undone.`)) {
    router.delete(`/projects/${props.project.id}`)
  }
}

const saveTask = () => {
  if (!newTask.value.title.trim()) {
    return
  }

  router.post('/tasks', {
    title: newTask.value.title.trim(),
    project_id: props.project.id,
    user_id: newTask.value.user_id || null,
    status: newTask.value.status,
    priority: newTask.value.priority,
    weight: newTask.value.weight || 1,
  }, {
    onSuccess: () => {
      closeAddTaskModal()
      router.visit(`/projects/${props.project.id}`)
    },
  })
}

const formatBytes = (bytes) => {
  if (!bytes) {
    return '—'
  }

  if (bytes < 1024) {
    return `${bytes} B`
  }

  if (bytes < 1024 * 1024) {
    return `${Math.round(bytes / 1024)} KB`
  }

  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
}

const assignUploadFile = (fileList) => {
  uploadForm.file = fileList?.[0] || null
}

const submitUpload = () => {
  if (!uploadForm.file) {
    return
  }

  uploadForm.post('/reports/documents', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      uploadForm.reset('file')
      activeTab.value = 'files'
    },
  })
}

const deleteDocument = (document) => {
  if (!confirm(`Delete “${document.name}”?`)) {
    return
  }

  router.delete(`/reports/documents/${document.id}`, {
    data: { return_to_project: true },
    preserveScroll: true,
    onSuccess: () => {
      activeTab.value = 'files'
    },
  })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="project.name" :subtitle="project.team">
        <template #actions>
          <Link href="/projects" class="ti-btn ti-btn-light">
            <i class="ri-arrow-left-line me-1"></i> Back
          </Link>
          <button type="button" class="ti-btn ti-btn-light" @click="openEditModal">
            <i class="ri-edit-line me-1"></i> Edit
          </button>
          <button type="button" class="ti-btn ti-btn-danger" @click="deleteProject">
            <i class="ri-delete-bin-line me-1"></i> Delete
          </button>
          <button type="button" class="ti-btn ti-btn-primary" @click="openAddTaskModal">
            <i class="ri-add-line me-1"></i> Add Task
          </button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-8">
          <div class="box">
            <div class="box-header border-b">
              <nav class="flex gap-4">
                <button
                  type="button"
                  class="pb-2 px-1 border-b-2 transition-colors"
                  :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-textmuted hover:text-defaulttextcolor'"
                  @click="activeTab = 'overview'"
                >
                  Overview
                </button>
                <button
                  type="button"
                  class="pb-2 px-1 border-b-2 transition-colors"
                  :class="activeTab === 'tasks' ? 'border-primary text-primary' : 'border-transparent text-textmuted hover:text-defaulttextcolor'"
                  @click="activeTab = 'tasks'"
                >
                  Tasks
                </button>
                <button
                  type="button"
                  class="pb-2 px-1 border-b-2 transition-colors"
                  :class="activeTab === 'files' ? 'border-primary text-primary' : 'border-transparent text-textmuted hover:text-defaulttextcolor'"
                  @click="activeTab = 'files'"
                >
                  Files
                </button>
                <button
                  type="button"
                  class="pb-2 px-1 border-b-2 transition-colors"
                  :class="activeTab === 'activity' ? 'border-primary text-primary' : 'border-transparent text-textmuted hover:text-defaulttextcolor'"
                  @click="activeTab = 'activity'"
                >
                  Activity
                </button>
              </nav>
            </div>
            <div class="box-body">
              <div v-if="activeTab === 'overview'">
                <p class="text-textmuted mb-4">{{ project.description || 'No description yet.' }}</p>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="text-xs text-textmuted uppercase">Start Date</label>
                    <p class="font-medium">{{ formatDate(project.startDate) }}</p>
                  </div>
                  <div>
                    <label class="text-xs text-textmuted uppercase">End Date</label>
                    <p class="font-medium">{{ formatDate(project.endDate) }}</p>
                  </div>
                  <div>
                    <label class="text-xs text-textmuted uppercase">Client</label>
                    <p class="font-medium">{{ project.client }}</p>
                  </div>
                  <div>
                    <label class="text-xs text-textmuted uppercase">Team</label>
                    <p class="font-medium">{{ project.team }}</p>
                  </div>
                </div>
              </div>

              <div v-if="activeTab === 'tasks'">
                <div v-if="!tasks.length" class="text-center py-8 text-textmuted">
                  <i class="ri-checkbox-circle-line text-4xl mb-2"></i>
                  <p>No tasks have been added.</p>
                </div>
                <ul v-else class="space-y-3">
                  <li v-for="task in tasks" :key="task.id" class="flex items-center justify-between p-3 bg-light rounded-lg">
                    <div class="flex items-center gap-3">
                      <input type="checkbox" class="ti-form-check-input" :checked="task.status === 'done'" disabled>
                      <div>
                        <span class="font-medium">{{ task.title }}</span>
                        <span class="block text-xs text-textmuted">{{ task.assignee }}</span>
                      </div>
                    </div>
                    <div class="flex items-center gap-2">
                      <span
                        class="badge"
                        :class="{
                          'bg-danger/10 text-danger': task.priority === 'high',
                          'bg-warning/10 text-warning': task.priority === 'medium',
                          'bg-success/10 text-success': task.priority === 'low',
                        }"
                      >{{ task.priority }}</span>
                      <span
                        class="badge"
                        :class="{
                          'bg-success/10 text-success': task.status === 'done',
                          'bg-primary/10 text-primary': task.status === 'in_progress',
                          'bg-warning/10 text-warning': task.status === 'todo' || task.status === 'review',
                        }"
                      >
                        {{ task.label }}
                      </span>
                    </div>
                  </li>
                </ul>
              </div>

              <div v-if="activeTab === 'files'" class="space-y-5">
                <form v-if="canUploadDocuments" class="space-y-3" @submit.prevent="submitUpload">
                  <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 md:col-span-4">
                      <label class="ti-form-label">Category</label>
                      <select v-model="uploadForm.category" class="ti-form-select">
                        <option value="planning">Planning</option>
                        <option value="design">Design</option>
                        <option value="technical">Technical</option>
                        <option value="financial">Financial</option>
                        <option value="quality">Quality</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <label class="ti-form-label">Upload a document</label>
                      <label
                        class="pm-project-form__drop"
                        :class="{ 'is-dragover': isDragging }"
                        @dragover.prevent="isDragging = true"
                        @dragleave.prevent="isDragging = false"
                        @drop.prevent="isDragging = false; assignUploadFile($event.dataTransfer.files)"
                      >
                        <input
                          type="file"
                          class="pm-project-form__drop-input"
                          @change="assignUploadFile($event.target.files)"
                        >
                        <i class="ri-upload-cloud-2-line" aria-hidden="true"></i>
                        <p>{{ uploadForm.file ? uploadForm.file.name : 'Drag & drop a file here or click to browse' }}</p>
                        <span class="pm-project-form__drop-hint">PDF, Office, images, text, CSV, or zip — up to 20 MB</span>
                      </label>
                      <p v-if="uploadForm.errors.file" class="pm-project-form__error">{{ uploadForm.errors.file }}</p>
                    </div>
                  </div>
                  <button type="submit" class="ti-btn ti-btn-primary" :disabled="uploadForm.processing || !uploadForm.file">
                    <i class="ri-upload-2-line me-1"></i>
                    {{ uploadForm.processing ? 'Uploading…' : 'Upload document' }}
                  </button>
                </form>

                <div v-if="!documents.length" class="text-center py-8 text-textmuted">
                  <i class="ri-folder-open-line text-4xl mb-2"></i>
                  <p>No files uploaded yet</p>
                </div>
                <ul v-else class="space-y-3">
                  <li v-for="document in documents" :key="document.id" class="flex items-center justify-between gap-3 p-3 bg-light rounded-lg">
                    <div>
                      <p class="font-medium">{{ document.name }}</p>
                      <p class="text-xs text-textmuted">
                        {{ document.category }} · {{ formatBytes(document.size) }}
                        <span v-if="document.user?.name"> · {{ document.user.name }}</span>
                      </p>
                    </div>
                    <div class="flex gap-1">
                      <a class="ti-btn ti-btn-soft-primary ti-btn-icon ti-btn-sm" :href="`/reports/documents/${document.id}/preview`" target="_blank" rel="noreferrer">
                        <i class="ri-eye-line"></i>
                      </a>
                      <a class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" :href="`/reports/documents/${document.id}/download`">
                        <i class="ri-download-line"></i>
                      </a>
                      <button
                        v-if="canDeleteDocuments"
                        type="button"
                        class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm"
                        @click="deleteDocument(document)"
                      >
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </li>
                </ul>
              </div>

              <div v-if="activeTab === 'activity'" class="text-center py-8 text-textmuted">
                <i class="ri-history-line text-4xl mb-2"></i>
                <p>Activity log will appear here</p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-span-12 xl:col-span-4">
          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Progress</h5>
            </div>
            <div class="box-body">
              <div class="text-center mb-4">
                <span class="text-4xl font-bold text-primary">{{ project.progress }}%</span>
              </div>
              <div class="progress progress-lg mb-4">
                <div class="progress-bar bg-primary" :style="{ width: project.progress + '%' }"></div>
              </div>
              <div class="flex justify-between text-sm text-textmuted">
                <span>Started: {{ formatDate(project.startDate) }}</span>
                <span>Due: {{ formatDate(project.endDate) }}</span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Budget</h5>
            </div>
            <div class="box-body">
              <div class="flex justify-between mb-2">
                <span class="text-textmuted">Total Budget</span>
                <span class="font-medium">{{ formatCurrency(project.budget) }}</span>
              </div>
              <div class="flex justify-between mb-2">
                <span class="text-textmuted">Spent</span>
                <span class="font-medium text-warning">{{ formatCurrency(project.spent) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-textmuted">Remaining</span>
                <span class="font-medium text-success">{{ formatCurrency(project.budget - project.spent) }}</span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Status</h5>
            </div>
            <div class="box-body">
              <div class="flex items-center gap-3 mb-3">
                <span class="badge bg-primary/10 text-primary">{{ statusLabels[project.status] || project.status }}</span>
                <span class="badge bg-danger/10 text-danger">{{ project.priority }} priority</span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Project Areas</h5>
            </div>
            <div class="box-body">
              <div class="grid grid-cols-2 gap-3">
                <button type="button" class="group p-4 bg-light rounded-lg border border-defaultborder hover:border-primary hover:bg-primary/5 transition-all duration-200 text-center" @click="navigateToStakeholders">
                  <div class="mb-2">
                    <i class="ri-user-line text-3xl text-primary group-hover:scale-110 transition-transform"></i>
                  </div>
                  <p class="text-sm font-medium text-defaulttextcolor">Stakeholders</p>
                  <p class="text-xs text-textmuted mt-1">Manage stakeholders</p>
                </button>
                <Link href="/resources/team" class="group p-4 bg-light rounded-lg border border-defaultborder hover:border-primary hover:bg-primary/5 transition-all duration-200 text-center">
                  <div class="mb-2">
                    <i class="ri-team-line text-3xl text-primary group-hover:scale-110 transition-transform"></i>
                  </div>
                  <p class="text-sm font-medium text-defaulttextcolor">Resources</p>
                  <p class="text-xs text-textmuted mt-1">Team & allocation</p>
                </Link>
                <Link href="/quality/risks" class="group p-4 bg-light rounded-lg border border-defaultborder hover:border-primary hover:bg-primary/5 transition-all duration-200 text-center">
                  <div class="mb-2">
                    <i class="ri-shield-cross-line text-3xl text-primary group-hover:scale-110 transition-transform"></i>
                  </div>
                  <p class="text-sm font-medium text-defaulttextcolor">Risks</p>
                  <p class="text-xs text-textmuted mt-1">Risk management</p>
                </Link>
                <Link href="/chat" class="group p-4 bg-light rounded-lg border border-defaultborder hover:border-primary hover:bg-primary/5 transition-all duration-200 text-center">
                  <div class="mb-2">
                    <i class="ri-message-3-line text-3xl text-primary group-hover:scale-110 transition-transform"></i>
                  </div>
                  <p class="text-sm font-medium text-defaulttextcolor">Chat</p>
                  <p class="text-xs text-textmuted mt-1">Team communication</p>
                </Link>
                <Link href="/resources/gantt" class="group p-4 bg-light rounded-lg border border-defaultborder hover:border-primary hover:bg-primary/5 transition-all duration-200 text-center">
                  <div class="mb-2">
                    <i class="ri-bar-chart-line text-3xl text-primary group-hover:scale-110 transition-transform"></i>
                  </div>
                  <p class="text-sm font-medium text-defaulttextcolor">Gantt</p>
                  <p class="text-xs text-textmuted mt-1">Timeline view</p>
                </Link>
                <Link href="/reports/analytics" class="group p-4 bg-light rounded-lg border border-defaultborder hover:border-primary hover:bg-primary/5 transition-all duration-200 text-center">
                  <div class="mb-2">
                    <i class="ri-file-chart-line text-3xl text-primary group-hover:scale-110 transition-transform"></i>
                  </div>
                  <p class="text-sm font-medium text-defaulttextcolor">Reports</p>
                  <p class="text-xs text-textmuted mt-1">Analytics & docs</p>
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showEditModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <div class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">Edit Project</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showEditModal = false">
              <i class="ri-close-line"></i>
            </button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Name</label>
              <input v-model="editForm.name" type="text" class="ti-form-control">
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Description</label>
              <textarea v-model="editForm.description" class="ti-form-control" rows="3"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Status</label>
                <select v-model="editForm.status" class="ti-form-select">
                  <option value="planning">Planning</option>
                  <option value="active">In Progress</option>
                  <option value="on_hold">On Hold</option>
                  <option value="completed">Completed</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Priority</label>
                <select v-model="editForm.priority" class="ti-form-select">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Team</label>
                <input v-model="editForm.team" type="text" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Client</label>
                <input v-model="editForm.client" type="text" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Start date</label>
                <input v-model="editForm.start_date" type="date" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">End date</label>
                <input v-model="editForm.end_date" type="date" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Currency</label>
                <select v-model="editForm.currency" class="ti-form-select">
                  <option v-for="option in currencies" :key="option.code" :value="option.code">
                    {{ option.symbol }} — {{ option.label }}
                  </option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Budget</label>
                <div class="input-group">
                  <CurrencyPrefix :code="editForm.currency || project.currency" />
                  <input v-model="editForm.budget" type="number" min="0" class="ti-form-control">
                </div>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Spent</label>
                <div class="input-group">
                  <CurrencyPrefix :code="editForm.currency || project.currency" />
                  <input v-model="editForm.spent" type="number" min="0" class="ti-form-control">
                </div>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showEditModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" type="button" @click="saveProject">Save Project</button>
          </div>
        </div>
      </div>

      <div v-if="showAddTaskModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <div class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-md mx-4">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">Add Task</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="closeAddTaskModal">
              <i class="ri-close-line"></i>
            </button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Task Title <span class="text-danger">*</span></label>
              <input v-model="newTask.title" type="text" class="ti-form-control" placeholder="Enter task title">
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Assignee</label>
              <select v-model="newTask.user_id" name="user_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="member in assignees" :key="member.id" :value="member.id">{{ member.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Status</label>
                <select v-model="newTask.status" class="ti-form-select">
                  <option value="todo">Pending</option>
                  <option value="in_progress">In Progress</option>
                  <option value="done">Completed</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Priority</label>
                <select v-model="newTask.priority" class="ti-form-select">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Weight</label>
                <input v-model.number="newTask.weight" type="number" min="1" max="8" class="ti-form-control">
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="closeAddTaskModal">Cancel</button>
            <button class="ti-btn ti-btn-primary" type="button" :disabled="!newTask.title.trim()" @click="saveTask">Save Task</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
