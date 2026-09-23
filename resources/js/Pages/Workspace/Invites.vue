<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
  title: { type: String, default: 'Pending Invites' },
  workspace: { type: Object, default: null },
  invites: { type: Array, default: () => [] },
  app_url: { type: String, default: '' },
})

const copiedId = ref(null)
const invitePathHint = '/invitations/{token}'

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

function formatDate(value) {
  if (!value) {
    return '—'
  }

  return new Date(value).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

function copyLink(url, id) {
  navigator.clipboard.writeText(url)
  copiedId.value = id
  window.setTimeout(() => {
    if (copiedId.value === id) {
      copiedId.value = null
    }
  }, 1600)
}

function resend(invite) {
  router.post(`/workspace/invites/${invite.token}/resend`, {}, { preserveScroll: true })
}

function revoke(invite) {
  if (!confirm(`Revoke invite for ${invite.email}?`)) {
    return
  }

  router.delete(`/workspace/invites/${invite.token}`, { preserveScroll: true })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" :subtitle="workspace?.name">
        <template #actions>
          <Link href="/workspace/members" class="ti-btn ti-btn-primary btn-wave">Invite someone</Link>
        </template>
      </PageHeader>

      <div class="box">
        <div class="box-body p-0">
          <div v-if="!invites.length" class="p-12 text-center text-textmuted">
            No pending invitations. Invite someone from Members.
          </div>
          <p v-else class="text-textmuted text-sm px-4 pt-4 mb-0">
            Share <code>{{ invitePathHint }}</code> on the environment that has this code. Email buttons use APP_URL (currently <code>{{ app_url }}</code>). If that is localhost, the partner should open the path on their own app (e.g. <code>http://THEIR-IP:8000/invitations/TOKEN</code>).
          </p>
          <div v-if="invites.length" class="table-responsive">
            <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
              <thead>
                <tr>
                  <th>Invite</th>
                  <th>Role</th>
                  <th>Expires</th>
                  <th>Path</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="invite in invites" :key="invite.id">
                  <td>
                    <div class="font-medium">{{ invite.name || invite.email }}</div>
                    <div class="text-textmuted text-xs">{{ invite.email }}</div>
                  </td>
                  <td>
                    <span class="badge pm-role-pill" :class="roleClass(invite.role)">{{ roleLabel(invite.role) }}</span>
                  </td>
                  <td class="text-textmuted text-sm">{{ formatDate(invite.expires_at) }}</td>
                  <td>
                    <div class="pm-copy-input min-w-[14rem]">
                      <input type="text" class="ti-form-control" readonly :value="invite.invite_path">
                      <button type="button" class="ti-btn ti-btn-soft-primary ti-btn-sm" @click="copyLink(invite.invite_path, invite.id)">
                        {{ copiedId === invite.id ? 'Copied' : 'Copy' }}
                      </button>
                    </div>
                    <div class="text-textmuted text-xs mt-1">Email button: {{ invite.invite_url }}</div>
                  </td>
                  <td>
                    <div class="flex gap-1">
                      <button type="button" class="ti-btn ti-btn-soft-info ti-btn-sm" @click="resend(invite)">Resend</button>
                      <button type="button" class="ti-btn ti-btn-soft-danger ti-btn-sm" @click="revoke(invite)">Revoke</button>
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
