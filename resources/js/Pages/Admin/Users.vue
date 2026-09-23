<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
  title: { type: String, default: 'Users' },
  users: { type: Array, default: () => [] },
})

const page = usePage()
const currentUserId = computed(() => page.props.auth?.user?.id)
const formError = computed(() => page.props.errors?.user)

function initials(name) {
  const parts = String(name || '?').split(' ').filter(Boolean).slice(0, 2)

  return parts.map((part) => part[0]).join('').toUpperCase() || '?'
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

function togglePlatform(user) {
  if (user.id === currentUserId.value) {
    return
  }

  router.put(`/admin/users/${user.id}/platform-admin`, {}, { preserveScroll: true })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Accounts, memberships, and platform admin access">
        <template #actions>
          <Link href="/admin" class="ti-btn ti-btn-light btn-wave">Overview</Link>
          <Link href="/admin/workspaces" class="ti-btn ti-btn-light btn-wave">Workspaces</Link>
        </template>
      </PageHeader>

      <p v-if="formError" class="pm-flash text-danger">{{ formError }}</p>

      <div class="box">
        <div class="box-body p-0">
          <div v-if="!users.length" class="p-12 text-center text-textmuted">No users yet.</div>
          <div v-else class="table-responsive">
            <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
              <thead>
                <tr>
                  <th>Person</th>
                  <th>Status</th>
                  <th>Memberships</th>
                  <th>Platform</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in users" :key="user.id">
                  <td>
                    <div class="flex items-center gap-3">
                      <span class="pm-initials">{{ initials(user.name) }}</span>
                      <div>
                        <div class="font-medium">{{ user.name }}</div>
                        <div class="text-textmuted text-xs">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="flex flex-wrap gap-1">
                      <span v-if="user.must_set_password" class="badge bg-warning/10 text-warning">Set password</span>
                      <span v-else-if="user.email_verified_at" class="badge bg-success/10 text-success">Verified</span>
                      <span v-else class="badge bg-secondary/10 text-secondary">Unverified</span>
                    </div>
                  </td>
                  <td>
                    <div v-if="!user.memberships.length" class="text-textmuted text-sm">None</div>
                    <div v-else class="flex flex-col gap-1">
                      <div v-for="membership in user.memberships" :key="membership.id" class="flex items-center gap-2">
                        <span class="text-sm">{{ membership.name }}</span>
                        <span class="badge pm-role-pill" :class="roleClass(membership.role)">{{ roleLabel(membership.role) }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <span class="badge" :class="user.is_platform_admin ? 'bg-primary/10 text-primary' : 'bg-secondary/10 text-secondary'">
                        {{ user.is_platform_admin ? 'Admin' : 'Member' }}
                      </span>
                      <button
                        type="button"
                        class="ti-btn ti-btn-soft-primary ti-btn-sm"
                        :disabled="user.id === currentUserId"
                        :title="user.id === currentUserId ? 'You cannot change your own platform admin access' : ''"
                        @click="togglePlatform(user)"
                      >
                        {{ user.is_platform_admin ? 'Revoke' : 'Grant' }}
                      </button>
                    </div>
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
