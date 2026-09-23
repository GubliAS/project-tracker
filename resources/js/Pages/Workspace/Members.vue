<script setup>
import { computed, ref } from 'vue'
import { Form, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

defineProps({
  title: { type: String, default: 'Workspace Members' },
  workspace: { type: Object, default: null },
  members: { type: Array, default: () => [] },
  invites: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const currentUserId = computed(() => page.props.auth?.user?.id)
const copiedId = ref(null)

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

function changeRole(memberId, event) {
  router.put(`/workspace/members/${memberId}`, { role: event.target.value }, { preserveScroll: true })
}

function removeMember(member) {
  if (!confirm(`Remove ${member.name} from this workspace?`)) {
    return
  }

  router.delete(`/workspace/members/${member.id}`, { preserveScroll: true })
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
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" :subtitle="workspace?.name">
        <template #actions>
          <Link href="/workspace/invites" class="ti-btn ti-btn-light btn-wave">Pending invites</Link>
        </template>
      </PageHeader>

      <CreateHero title="Invite a teammate" subtitle="Send a workspace invite with the right role." pill="Invite" />

      <div v-if="abilities.manage_members" class="box mb-4">
        <div class="box-header">
          <h6 class="box-title mb-0">Invite member</h6>
        </div>
        <div class="box-body">
          <Form action="/workspace/members/invite" method="post" reset-on-success #default="{ errors, processing }">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
              <div>
                <label class="ti-form-label" for="invite-name">Name</label>
                <input id="invite-name" name="name" type="text" class="ti-form-control" autocomplete="name" placeholder="Optional">
                <p v-if="errors.name" class="text-danger text-xs mt-1">{{ errors.name }}</p>
              </div>
              <div>
                <label class="ti-form-label" for="invite-email">Email</label>
                <input id="invite-email" name="email" type="email" class="ti-form-control" required>
                <p v-if="errors.email" class="text-danger text-xs mt-1">{{ errors.email }}</p>
              </div>
              <div>
                <label class="ti-form-label" for="invite-role">Role</label>
                <select id="invite-role" name="role" class="ti-form-select">
                  <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                </select>
                <p v-if="errors.role" class="text-danger text-xs mt-1">{{ errors.role }}</p>
              </div>
              <button type="submit" class="ti-btn ti-btn-primary btn-wave" :disabled="processing">Send invite</button>
            </div>
          </Form>
        </div>
      </div>

      <div v-if="abilities.manage_members && invites.length" class="box mb-4">
        <div class="box-header">
          <h6 class="box-title mb-0">Set-password links</h6>
        </div>
        <div class="box-body p-0">
          <div class="table-responsive">
            <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
              <thead>
                <tr>
                  <th>Invite</th>
                  <th>Link</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="invite in invites" :key="invite.id">
                  <td>
                    <div class="font-medium">{{ invite.name || invite.email }}</div>
                    <div class="text-textmuted text-xs">{{ invite.email }}</div>
                  </td>
                  <td>
                    <div class="pm-copy-input">
                      <input type="text" class="ti-form-control" readonly :value="invite.invite_url">
                      <button type="button" class="ti-btn ti-btn-soft-primary ti-btn-sm" @click="copyLink(invite.invite_url, invite.id)">
                        {{ copiedId === invite.id ? 'Copied' : 'Copy' }}
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="box">
        <div class="box-body p-0">
          <div class="table-responsive">
            <table class="table table-hover whitespace-nowrap mb-0 pm-admin-table">
              <thead>
                <tr>
                  <th>Member</th>
                  <th>Role</th>
                  <th v-if="abilities.manage_members"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="member in members" :key="member.id">
                  <td>
                    <div class="flex items-center gap-3">
                      <span class="pm-initials">{{ initials(member.name) }}</span>
                      <div>
                        <div class="font-medium">{{ member.name }}</div>
                        <div class="text-textmuted text-xs">{{ member.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <select
                      v-if="abilities.manage_members"
                      class="ti-form-select w-auto"
                      :value="member.role"
                      @change="changeRole(member.id, $event)"
                    >
                      <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                    </select>
                    <span v-else class="badge pm-role-pill" :class="roleClass(member.role)">{{ roleLabel(member.role) }}</span>
                  </td>
                  <td v-if="abilities.manage_members">
                    <button
                      v-if="member.id !== currentUserId"
                      type="button"
                      class="ti-btn ti-btn-soft-danger ti-btn-sm"
                      @click="removeMember(member)"
                    >
                      Remove
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
