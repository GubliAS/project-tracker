<script setup>
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import StatsCard from '@/Components/ui/StatsCard.vue'

defineProps({
  title: { type: String, default: 'Platform' },
  stats: { type: Object, default: () => ({ workspaces: 0, users: 0, projects: 0, pending_invites: 0 }) },
  workspaces: { type: Array, default: () => [] },
  activity: { type: Array, default: () => [] },
})

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

function openWorkspace(workspaceId) {
  router.post('/workspace/switch', { workspace_id: workspaceId })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Workspaces, people, and the trail of changes across the platform">
        <template #actions>
          <Link href="/admin/workspaces" class="ti-btn ti-btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> New workspace
          </Link>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="xxl:col-span-3 md:col-span-6 col-span-12">
          <StatsCard title="Workspaces" :value="stats.workspaces" icon="ri-building-2-line" icon-bg="bg-primary" badge="Tenants" badge-class="bg-primary/10 text-primary" />
        </div>
        <div class="xxl:col-span-3 md:col-span-6 col-span-12">
          <StatsCard title="People" :value="stats.users" icon="ri-team-line" icon-bg="bg-primarytint1color" badge="Accounts" badge-class="bg-info/10 text-info" />
        </div>
        <div class="xxl:col-span-3 md:col-span-6 col-span-12">
          <StatsCard title="Projects" :value="stats.projects" icon="ri-folder-line" icon-bg="bg-primarytint2color" badge="Portfolio" badge-class="bg-success/10 text-success" />
        </div>
        <div class="xxl:col-span-3 md:col-span-6 col-span-12">
          <StatsCard title="Pending invites" :value="stats.pending_invites" icon="ri-mail-send-line" icon-bg="bg-primarytint3color" badge="Waiting" badge-class="bg-warning/10 text-warning" />
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6">
        <div class="xl:col-span-7 col-span-12">
          <div class="box">
            <div class="box-header justify-between">
              <h6 class="box-title mb-0">Recent workspaces</h6>
              <Link href="/admin/workspaces" class="ti-btn ti-btn-sm bg-primary/10 text-primary">View all</Link>
            </div>
            <div class="box-body p-0">
              <div v-if="!workspaces.length" class="p-12 text-center text-textmuted">No workspaces yet.</div>
              <div v-else class="table-responsive">
                <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
                  <thead>
                    <tr>
                      <th>Workspace</th>
                      <th>Members</th>
                      <th>Projects</th>
                      <th>Created</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="workspace in workspaces" :key="workspace.id">
                      <td class="font-medium">{{ workspace.name }}</td>
                      <td>{{ workspace.users_count }}</td>
                      <td>{{ workspace.projects_count }}</td>
                      <td class="text-textmuted text-sm">{{ formatDate(workspace.created_at) }}</td>
                      <td>
                        <button type="button" class="ti-btn ti-btn-soft-primary ti-btn-sm" @click="openWorkspace(workspace.id)">
                          Open
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="xl:col-span-5 col-span-12">
          <div class="box">
            <div class="box-header justify-between">
              <h6 class="box-title mb-0">Latest activity</h6>
              <Link href="/admin/audit" class="ti-btn ti-btn-sm bg-primary/10 text-primary">Audit</Link>
            </div>
            <div class="box-body">
              <p v-if="!activity.length" class="text-textmuted text-center py-8 mb-0">No events yet.</p>
              <div v-for="log in activity" :key="log.id" class="pm-team-row">
                <span class="pm-initials">{{ (log.user || 'S').slice(0, 1) }}</span>
                <div class="min-w-0">
                  <div class="font-medium capitalize truncate">{{ actionLabel(log.action) }}</div>
                  <div class="text-textmuted text-xs truncate">{{ log.user || 'System' }} · {{ log.workspace || 'Platform' }}</div>
                </div>
                <span class="text-textmuted text-xs whitespace-nowrap">{{ formatWhen(log.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
