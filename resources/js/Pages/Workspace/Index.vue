<script setup>
import { computed, ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import StatsCard from '@/Components/ui/StatsCard.vue'
import CreateWorkspaceModal from '@/Components/ui/CreateWorkspaceModal.vue'

defineProps({
  title: { type: String, default: 'Workspace' },
  workspace: { type: Object, default: null },
  counts: { type: Object, default: () => ({ projects: 0, members: 0, pending_invites: 0 }) },
  members_by_role: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  activity: { type: Array, default: () => [] },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const isCreateWorkspaceOpen = ref(false)

const statusLabels = {
  planning: 'Planning',
  active: 'In Progress',
  on_hold: 'On Hold',
  completed: 'Completed',
}

function roleLabel(role) {
  const labels = {
    workspace_admin: 'Workspace admin',
    project_manager: 'Project manager',
    member: 'Member',
    viewer: 'Viewer',
  }

  return labels[role] || role
}

function roleClass(role) {
  const classes = {
    workspace_admin: 'bg-primary/10 text-primary',
    project_manager: 'bg-info/10 text-info',
    member: 'bg-success/10 text-success',
    viewer: 'bg-secondary/10 text-secondary',
  }

  return classes[role] || 'bg-secondary/10 text-secondary'
}

function statusClass(status) {
  const classes = {
    planning: 'bg-info/10 text-info',
    active: 'bg-primary/10 text-primary',
    on_hold: 'bg-warning/10 text-warning',
    completed: 'bg-success/10 text-success',
  }

  return classes[status] || 'bg-secondary/10 text-secondary'
}

function formatDate(value) {
  if (!value) {
    return '—'
  }

  return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function formatWhen(value) {
  if (!value) {
    return '—'
  }

  return new Date(value).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })
}

function actionLabel(action) {
  return String(action || '').replaceAll('.', ' · ').replaceAll('_', ' ')
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="workspace?.name || title" subtitle="Projects, people, and recent activity in this workspace">
        <template #actions>
          <button type="button" class="ti-btn ti-btn-light btn-wave" @click="isCreateWorkspaceOpen = true">
            <i class="ri-add-line me-1" aria-hidden="true"></i>
            Create workspace
          </button>
          <Link v-if="abilities.manage_members" href="/workspace/members" class="ti-btn ti-btn-primary btn-wave">
            <i class="ri-user-add-line me-1"></i> Members
          </Link>
          <Link v-if="abilities.manage_workspace" href="/workspace/settings" class="ti-btn ti-btn-light btn-wave">
            Settings
          </Link>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="xxl:col-span-4 md:col-span-4 col-span-12">
          <StatsCard title="Projects" :value="counts.projects" icon="ri-folder-line" icon-bg="bg-primary" badge="Active set" badge-class="bg-primary/10 text-primary" />
        </div>
        <div class="xxl:col-span-4 md:col-span-4 col-span-12">
          <StatsCard title="Members" :value="counts.members" icon="ri-team-line" icon-bg="bg-primarytint1color" badge="Seats" badge-class="bg-info/10 text-info" />
        </div>
        <div class="xxl:col-span-4 md:col-span-4 col-span-12">
          <StatsCard title="Pending invites" :value="counts.pending_invites" icon="ri-mail-send-line" icon-bg="bg-primarytint3color" badge="Waiting" badge-class="bg-warning/10 text-warning" />
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-8 col-span-12">
          <div class="box">
            <div class="box-header justify-between">
              <h6 class="box-title mb-0">Projects</h6>
              <Link href="/projects" class="ti-btn ti-btn-sm bg-primary/10 text-primary">All projects</Link>
            </div>
            <div class="box-body p-0">
              <div v-if="!projects.length" class="p-12 text-center text-textmuted">No projects in this workspace yet.</div>
              <div v-else class="table-responsive">
                <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
                  <thead>
                    <tr>
                      <th>Project</th>
                      <th>Status</th>
                      <th>Due</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="project in projects" :key="project.id">
                      <td>
                        <Link :href="`/projects/${project.id}`" class="font-medium hover:text-primary">{{ project.name }}</Link>
                      </td>
                      <td>
                        <span class="badge" :class="statusClass(project.status)">
                          {{ statusLabels[project.status] || project.status }}
                        </span>
                      </td>
                      <td class="text-textmuted text-sm">{{ formatDate(project.end_date) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="xl:col-span-4 col-span-12">
          <div class="box mb-6">
            <div class="box-header">
              <h6 class="box-title mb-0">Members by role</h6>
            </div>
            <div class="box-body">
              <p v-if="!members_by_role.length" class="text-textmuted mb-0">No members yet.</p>
              <div v-for="group in members_by_role" :key="group.role" class="flex items-center justify-between py-2">
                <span class="badge pm-role-pill" :class="roleClass(group.role)">{{ roleLabel(group.role) }}</span>
                <span class="font-semibold">{{ group.count }}</span>
              </div>
            </div>
          </div>

          <div class="box">
            <div class="box-header">
              <h6 class="box-title mb-0">Activity</h6>
            </div>
            <div class="box-body">
              <p v-if="!activity.length" class="text-textmuted text-center py-6 mb-0">No recent activity.</p>
              <div v-for="log in activity" :key="log.id" class="pm-team-row">
                <div class="min-w-0">
                  <div class="font-medium capitalize truncate">{{ actionLabel(log.action) }}</div>
                  <div class="text-textmuted text-xs">{{ log.user || 'System' }} · {{ formatWhen(log.created_at) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <CreateWorkspaceModal v-model="isCreateWorkspaceOpen" />
  </AppLayout>
</template>
