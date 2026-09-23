<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import { useCurrency } from '@/composables/useCurrency'

const props = defineProps({
  title: { type: String, default: 'Projects List' },
  projects: { type: Array, default: () => [] },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const searchQuery = ref('')
const statusFilter = ref('all')

const statusLabels = {
  planning: 'Planning',
  active: 'In Progress',
  on_hold: 'On Hold',
  completed: 'Completed',
}

const projects = computed(() => props.projects.map((project) => ({
  ...project,
  team: project.team || 'Unassigned',
  priority: project.priority || 'medium',
  dueDate: project.end_date,
  budget: Number(project.budget || 0),
  spent: Number(project.spent || 0),
  progress: project.tasks_count
    ? Math.round((project.completed_tasks_count / project.tasks_count) * 100)
    : 0,
})))

const filteredProjects = computed(() => projects.value.filter((project) => {
  const haystack = `${project.name} ${project.description || ''} ${project.team}`.toLowerCase()
  const matchesSearch = haystack.includes(searchQuery.value.toLowerCase())
  const matchesStatus = statusFilter.value === 'all' || project.status === statusFilter.value

  return matchesSearch && matchesStatus
}))

const getStatusClass = (status) => {
  const classes = {
    planning: 'bg-info/10 text-info',
    active: 'bg-primary/10 text-primary',
    on_hold: 'bg-warning/10 text-warning',
    completed: 'bg-success/10 text-success',
  }

  return classes[status] || 'bg-secondary/10 text-secondary'
}

const getPriorityClass = (priority) => {
  const classes = {
    high: 'bg-danger/10 text-danger',
    medium: 'bg-warning/10 text-warning',
    low: 'bg-success/10 text-success',
  }

  return classes[priority] || 'bg-secondary/10 text-secondary'
}

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

const { formatCurrency } = useCurrency()
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Manage and track all your projects">
        <template v-if="abilities.write_projects" #actions>
          <Link href="/projects/create" class="ti-btn ti-btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> New Project
          </Link>
        </template>
      </PageHeader>

      <div class="box">
        <div class="box-header flex flex-wrap items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                class="ti-form-control !ps-10"
                placeholder="Search projects..."
              >
              <i class="ri-search-line absolute start-3 top-1/2 -translate-y-1/2 text-textmuted"></i>
            </div>
            <select v-model="statusFilter" class="ti-form-select w-auto">
              <option value="all">All Status</option>
              <option value="planning">Planning</option>
              <option value="active">In Progress</option>
              <option value="on_hold">On Hold</option>
              <option value="completed">Completed</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <button type="button" class="ti-btn ti-btn-light ti-btn-sm">
              <i class="ri-download-line me-1"></i> Export
            </button>
          </div>
        </div>

        <div class="box-body p-0">
          <div v-if="!filteredProjects.length" class="p-12 text-center text-textmuted">
            No projects match this search yet.
          </div>
          <div v-else class="table-responsive">
            <table class="table table-hover whitespace-nowrap">
              <thead>
                <tr>
                  <th>
                    <input type="checkbox" class="ti-form-check-input">
                  </th>
                  <th>Project</th>
                  <th>Status</th>
                  <th>Progress</th>
                  <th>Priority</th>
                  <th>Budget</th>
                  <th>Due Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="project in filteredProjects" :key="project.id">
                  <td>
                    <input type="checkbox" class="ti-form-check-input">
                  </td>
                  <td>
                    <div class="flex items-center gap-3">
                      <span class="avatar avatar-md bg-primary/10 text-primary avatar-rounded inline-flex items-center justify-center">
                        <i class="ri-folder-line text-lg leading-none"></i>
                      </span>
                      <div>
                        <Link :href="`/projects/${project.id}`" class="font-medium text-defaulttextcolor hover:text-primary">
                          {{ project.name }}
                        </Link>
                        <p class="text-textmuted text-xs mb-0">{{ project.team }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="getStatusClass(project.status)">
                      {{ statusLabels[project.status] || project.status }}
                    </span>
                  </td>
                  <td>
                    <div class="flex items-center gap-2 min-w-[120px]">
                      <div class="progress progress-xs flex-1">
                        <div class="progress-bar bg-primary" :style="{ width: project.progress + '%' }"></div>
                      </div>
                      <span class="text-xs text-textmuted">{{ project.progress }}%</span>
                    </div>
                  </td>
                  <td>
                    <span class="badge" :class="getPriorityClass(project.priority)">
                      {{ project.priority }}
                    </span>
                  </td>
                  <td>
                    <div>
                      <span class="font-medium">{{ formatCurrency(project.spent, { currency: project.currency }) }}</span>
                      <span class="text-textmuted text-xs"> / {{ formatCurrency(project.budget, { currency: project.currency }) }}</span>
                    </div>
                  </td>
                  <td>{{ formatDate(project.dueDate) }}</td>
                  <td>
                    <div class="flex gap-1">
                      <Link :href="`/projects/${project.id}`" class="ti-btn ti-btn-soft-primary ti-btn-icon ti-btn-sm">
                        <i class="ri-eye-line"></i>
                      </Link>
                      <Link :href="`/projects/${project.id}`" class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm">
                        <i class="ri-edit-line"></i>
                      </Link>
                      <button type="button" class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm">
                        <i class="ri-delete-bin-line"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="box-footer flex items-center justify-between">
          <div class="text-textmuted text-sm">
            Showing {{ filteredProjects.length }} of {{ projects.length }} projects
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
