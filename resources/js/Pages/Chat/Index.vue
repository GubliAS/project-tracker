<script setup>
import { computed, nextTick, ref, watch } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Project Chat' },
  projects: { type: Array, default: () => [] },
  selectedProjectId: { type: [Number, String], default: null },
  messages: { type: Array, default: () => [] },
})

const page = usePage()
const currentUserId = computed(() => page.props.auth?.user?.id)
const chatContainer = ref(null)

const form = useForm({
  project_id: props.selectedProjectId,
  message: '',
})

const activeProject = computed(() => props.projects.find((project) => Number(project.id) === Number(props.selectedProjectId)))

const displayMessages = computed(() => props.messages.map((message) => {
  const isMe = Number(message.user_id) === Number(currentUserId.value)
  const name = message.user?.name || 'Team member'

  return {
    id: message.id,
    user: name,
    avatar: initials(name),
    message: message.message,
    time: formatTime(message.created_at),
    isMe,
  }
}))

const onlineMembers = computed(() => {
  const seen = new Map()

  props.messages.forEach((message) => {
    if (message.user?.id && !seen.has(message.user.id)) {
      seen.set(message.user.id, message.user.name)
    }
  })

  return Array.from(seen, ([id, name]) => ({ id, name, avatar: initials(name) }))
})

function initials(name) {
  return String(name || 'TM')
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0]?.toUpperCase())
    .join('') || 'TM'
}

function formatTime(value) {
  if (!value) {
    return ''
  }

  return new Date(value).toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
}

function switchProject(projectId) {
  router.get('/chat', { project: projectId }, { preserveScroll: true })
}

function sendMessage() {
  if (!form.message.trim() || !props.selectedProjectId) {
    return
  }

  form.project_id = props.selectedProjectId
  form.post('/chat', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('message')
      nextTick(() => {
        if (chatContainer.value) {
          chatContainer.value.scrollTop = chatContainer.value.scrollHeight
        }
      })
    },
  })
}

watch(() => props.messages.length, () => {
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
})
</script>

<template>
  <AppLayout title="Project Chat">
    <div class="pm-dash">
      <PageHeader title="Project Chat" subtitle="Team communication">
        <template #actions>
          <button class="ti-btn ti-btn-light" type="button">
            <i class="ri-phone-line me-1"></i> Voice Call
          </button>
          <button class="ti-btn ti-btn-primary" type="button">
            <i class="ri-vidicon-line me-1"></i> Video Call
          </button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-3">
          <div class="box">
            <div class="box-header">
              <div class="flex items-center justify-between">
                <h5 class="box-title">Projects</h5>
              </div>
            </div>
            <div class="box-body p-0">
              <ul v-if="projects.length" class="pm-channel-list">
                <li
                  v-for="project in projects"
                  :key="project.id"
                  class="pm-channel-item"
                  :class="{ active: Number(selectedProjectId) === Number(project.id) }"
                  @click="switchProject(project.id)"
                >
                  <span class="flex items-center min-w-0 truncate">
                    <i class="ri-hashtag pm-channel-item__hash"></i>
                    {{ project.name }}
                  </span>
                </li>
              </ul>
              <p v-else class="p-6 text-sm text-textmuted">No projects yet. Create a project to start chatting.</p>
            </div>
          </div>

          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Team Online</h5>
            </div>
            <div class="box-body p-0">
              <ul v-if="onlineMembers.length" class="pm-presence-list">
                <li v-for="member in onlineMembers" :key="member.id" class="pm-presence-row">
                  <span class="avatar avatar-sm avatar-rounded bg-primary text-white">{{ member.avatar }}</span>
                  <span class="text-sm truncate">{{ member.name }}</span>
                  <span class="pm-presence-dot bg-success"></span>
                </li>
              </ul>
              <p v-else class="p-6 text-sm text-textmuted">No recent participants.</p>
            </div>
          </div>
        </div>

        <div class="col-span-12 xl:col-span-9">
          <div class="box h-[600px] flex flex-col">
            <div class="box-header border-b">
              <div class="flex items-center gap-2">
                <i class="ri-hashtag text-lg"></i>
                <h5 class="box-title mb-0">{{ activeProject?.name || 'Select a project' }}</h5>
              </div>
            </div>

            <div ref="chatContainer" class="box-body flex-1 overflow-y-auto space-y-4">
              <p v-if="!selectedProjectId" class="text-center text-textmuted py-12">Select a project to view messages.</p>
              <p v-else-if="!displayMessages.length" class="text-center text-textmuted py-12">No messages yet. Start the conversation.</p>
              <div
                v-for="msg in displayMessages"
                :key="msg.id"
                class="flex gap-3"
                :class="{ 'flex-row-reverse': msg.isMe }"
              >
                <span class="avatar avatar-sm flex-shrink-0" :class="msg.isMe ? 'bg-primary text-white' : 'bg-light text-defaulttextcolor'">
                  {{ msg.avatar }}
                </span>
                <div :class="{ 'text-right': msg.isMe }">
                  <div class="flex items-center gap-2 mb-1" :class="{ 'flex-row-reverse': msg.isMe }">
                    <span v-if="!msg.isMe" class="font-medium text-sm">{{ msg.user }}</span>
                    <span class="text-xs text-textmuted">{{ msg.time }}</span>
                  </div>
                  <div
                    class="inline-block p-3 rounded-lg max-w-md"
                    :class="msg.isMe ? 'bg-primary text-white' : 'bg-light'"
                  >
                    {{ msg.message }}
                  </div>
                </div>
              </div>
            </div>

            <div class="box-footer border-t">
              <form class="flex items-center gap-2" @submit.prevent="sendMessage">
                <input
                  v-model="form.message"
                  type="text"
                  class="ti-form-control flex-1"
                  placeholder="Type a message..."
                  :disabled="!selectedProjectId"
                >
                <button type="button" class="ti-btn ti-btn-light ti-btn-icon"><i class="ri-attachment-line"></i></button>
                <button type="button" class="ti-btn ti-btn-light ti-btn-icon"><i class="ri-emotion-line"></i></button>
                <button type="submit" class="ti-btn ti-btn-primary ti-btn-icon" :disabled="form.processing || !selectedProjectId">
                  <i class="ri-send-plane-fill"></i>
                </button>
              </form>
              <p v-if="form.errors.message" class="text-danger text-xs mt-2 mb-0">{{ form.errors.message }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
