<script setup>
import { Form } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

defineProps({
  title: { type: String, default: 'Accept Invitation' },
  invitation: { type: Object, required: true },
})
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Join a workspace you were invited to" />

      <div class="box max-w-xl">
        <div class="box-body">
          <p class="mb-2">You were invited to <strong>{{ invitation.workspace?.name }}</strong> as <strong>{{ invitation.role }}</strong>.</p>
          <p class="text-textmuted text-sm mb-4">This invite was sent to {{ invitation.email }}.</p>
          <Form :action="`/invitations/${invitation.token}/accept`" method="post" #default="{ processing }">
            <button type="submit" class="ti-btn ti-btn-primary" :disabled="processing">Accept invitation</button>
          </Form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
