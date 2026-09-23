<script setup>
import { Form, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
  title: { type: String, default: 'Workspaces' },
  workspaces: { type: Array, default: () => [] },
})

function formatDate(value) {
  if (!value) {
    return '—'
  }

  return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function openWorkspace(workspaceId) {
  router.post('/workspace/switch', { workspace_id: workspaceId })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Every tenant workspace on the platform">
        <template #actions>
          <Link href="/admin" class="ti-btn ti-btn-light btn-wave">Overview</Link>
          <Link href="/admin/users" class="ti-btn ti-btn-light btn-wave">Users</Link>
        </template>
      </PageHeader>

      <div class="box mb-4">
        <div class="box-header">
          <h6 class="box-title mb-0">Create workspace</h6>
        </div>
        <div class="box-body">
          <Form action="/admin/workspaces" method="post" reset-on-success #default="{ errors, processing }">
            <div class="flex flex-wrap gap-3 items-end">
              <div class="min-w-[16rem] grow">
                <label class="ti-form-label" for="admin-workspace-name">Name</label>
                <input id="admin-workspace-name" name="name" type="text" class="ti-form-control" placeholder="Acme Delivery" required>
                <p v-if="errors.name" class="text-danger text-xs mt-1">{{ errors.name }}</p>
              </div>
              <button type="submit" class="ti-btn ti-btn-primary btn-wave" :disabled="processing">
                <i class="ri-add-line me-1"></i> Create
              </button>
            </div>
          </Form>
        </div>
      </div>

      <div class="box">
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
                  <td>
                    <div class="font-medium">{{ workspace.name }}</div>
                    <div class="text-textmuted text-xs">{{ workspace.slug }}</div>
                  </td>
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
  </AppLayout>
</template>
