<script setup>
import { Form, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Workspace Settings' },
  workspace: { type: Object, default: null },
  currencies: { type: Array, default: () => [] },
})

function deleteWorkspace() {
  if (!confirm(`Delete workspace "${props.workspace?.name}"? Projects and records stay attached to this workspace id and cannot be undone from here.`)) {
    return
  }

  router.delete('/workspace')
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" :subtitle="workspace?.name">
        <template #actions>
          <Link href="/workspace" class="ti-btn ti-btn-light btn-wave">Overview</Link>
        </template>
      </PageHeader>

      <div class="box mb-4">
        <div class="box-header">
          <h6 class="box-title mb-0">Workspace settings</h6>
        </div>
        <div class="box-body">
          <Form action="/workspace/settings" method="put" #default="{ errors, processing }">
            <div class="max-w-xl space-y-4">
              <div>
                <label class="ti-form-label" for="workspace-name">Name</label>
                <input id="workspace-name" name="name" type="text" class="ti-form-control" :value="workspace?.name" required>
                <p v-if="errors.name" class="text-danger text-xs mt-1">{{ errors.name }}</p>
                <p class="text-textmuted text-xs mt-2 mb-0">Renaming also refreshes the workspace slug used in the switcher.</p>
              </div>
              <div>
                <label class="ti-form-label" for="workspace-currency">Currency</label>
                <select id="workspace-currency" name="currency" class="ti-form-select" :value="workspace?.currency" required>
                  <option v-for="option in currencies" :key="option.code" :value="option.code">
                    {{ option.symbol }} — {{ option.label }}
                  </option>
                </select>
                <p v-if="errors.currency" class="text-danger text-xs mt-1">{{ errors.currency }}</p>
                <p class="text-textmuted text-xs mt-2 mb-0">Default for new projects. Existing projects keep their own currency.</p>
              </div>
              <button type="submit" class="ti-btn ti-btn-primary btn-wave" :disabled="processing">Save settings</button>
            </div>
          </Form>
        </div>
      </div>

      <div class="box pm-danger-zone">
        <div class="box-header">
          <h6 class="box-title mb-0 text-danger">Delete this workspace</h6>
        </div>
        <div class="box-body">
          <p class="text-textmuted mb-3">
            This removes <strong>{{ workspace?.name }}</strong> from the switcher for everyone. Existing projects and records stay attached to the deleted workspace and will no longer appear in the app.
          </p>
          <button type="button" class="ti-btn ti-btn-danger btn-wave" @click="deleteWorkspace">
            Delete workspace
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
