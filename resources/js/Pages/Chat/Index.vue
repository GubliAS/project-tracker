<script setup>
import { ref, nextTick, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const pageProps = defineProps({ title: { type: String, default: 'Project Chat' } })

const channels = ref([
  { id: 1, name: 'general', unread: 2 },
  { id: 2, name: 'website-redesign', unread: 5 },
  { id: 3, name: 'mobile-app', unread: 0 },
  { id: 4, name: 'random', unread: 1 }
])

const activeChannel = ref('general')

const messages = ref([
  { id: 1, user: 'John Doe', avatar: 'JD', message: 'Hey team, the new designs are ready for review!', time: '10:30 AM', isMe: false },
  { id: 2, user: 'Jane Smith', avatar: 'JS', message: 'Great! I\'ll take a look this afternoon.', time: '10:32 AM', isMe: false },
  { id: 3, user: 'You', avatar: 'ME', message: 'Perfect, let me know if you have any feedback.', time: '10:35 AM', isMe: true },
  { id: 4, user: 'Mike Johnson', avatar: 'MJ', message: 'The API endpoints are also ready for integration. Check the docs in #mobile-app channel.', time: '10:45 AM', isMe: false },
  { id: 5, user: 'You', avatar: 'ME', message: 'Thanks Mike! Will start integration tomorrow.', time: '10:50 AM', isMe: true }
])

const newMessage = ref('')
const chatContainer = ref(null)

const showAddChannelModal = ref(false)
const newChannel = ref({ name: '' })

const openAddChannelModal = () => {
  showAddChannelModal.value = true
}

const closeAddChannelModal = () => {
  showAddChannelModal.value = false
  newChannel.value = { name: '' }
}

const saveChannel = () => {
  const name = newChannel.value.name.trim().toLowerCase().replace(/\s+/g, '-')
  if (!name) return

  const nextId = channels.value.length ? Math.max(...channels.value.map(c => c.id)) + 1 : 1

  channels.value.push({
    id: nextId,
    name,
    unread: 0
  })

  activeChannel.value = name
  closeAddChannelModal()
}

const sendMessage = () => {
  if (!newMessage.value.trim()) return
  
  messages.value.push({
    id: messages.value.length + 1,
    user: 'You',
    avatar: 'ME',
    message: newMessage.value,
    time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
    isMe: true
  })
  newMessage.value = ''
  
  nextTick(() => {
    if (chatContainer.value) {
      chatContainer.value.scrollTop = chatContainer.value.scrollHeight
    }
  })
}
</script>

<template>
  <AppLayout title="Project Chat">
<div class="pm-dash">
    <PageHeader title="Project Chat" subtitle="Team communication">
      <template #actions>
        <button class="ti-btn ti-btn-light">
          <i class="ri-phone-line me-1"></i> Voice Call
        </button>
        <button class="ti-btn ti-btn-primary">
          <i class="ri-vidicon-line me-1"></i> Video Call
        </button>
      </template>
    </PageHeader>

    <div class="grid grid-cols-12 gap-6">
      <!-- Channels Sidebar -->
      <div class="col-span-12 xl:col-span-3">
        <div class="box">
          <div class="box-header">
            <div class="flex items-center justify-between">
              <h5 class="box-title">Channels</h5>
              <button class="ti-btn ti-btn-sm ti-btn-soft-primary ti-btn-icon" type="button" @click="openAddChannelModal">
                <i class="ri-add-line"></i>
              </button>
            </div>
          </div>
          <div class="box-body p-0">
            <ul class="pm-channel-list">
              <li
                v-for="channel in channels"
                :key="channel.id"
                class="pm-channel-item"
                :class="{ active: activeChannel === channel.name }"
                @click="activeChannel = channel.name"
              >
                <span class="flex items-center min-w-0 truncate">
                  <i class="ri-hashtag pm-channel-item__hash"></i>
                  {{ channel.name }}
                </span>
                <span v-if="channel.unread > 0" class="badge bg-primary text-white rounded-full">{{ channel.unread }}</span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Team Members -->
        <div class="box">
          <div class="box-header">
            <h5 class="box-title">Team Online</h5>
          </div>
          <div class="box-body p-0">
            <ul class="pm-presence-list">
              <li class="pm-presence-row">
                <span class="avatar avatar-sm avatar-rounded bg-primary text-white">JD</span>
                <span class="text-sm truncate">John Doe</span>
                <span class="pm-presence-dot bg-success"></span>
              </li>
              <li class="pm-presence-row">
                <span class="avatar avatar-sm avatar-rounded bg-info text-white">JS</span>
                <span class="text-sm truncate">Jane Smith</span>
                <span class="pm-presence-dot bg-success"></span>
              </li>
              <li class="pm-presence-row">
                <span class="avatar avatar-sm avatar-rounded bg-warning text-white">MJ</span>
                <span class="text-sm truncate">Mike Johnson</span>
                <span class="pm-presence-dot bg-gray-300"></span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Chat Area -->
      <div class="col-span-12 xl:col-span-9">
        <div class="box h-[600px] flex flex-col">
          <div class="box-header border-b">
            <div class="flex items-center gap-2">
              <i class="ri-hashtag text-lg"></i>
              <h5 class="box-title mb-0">{{ activeChannel }}</h5>
            </div>
          </div>
          
          <!-- Messages -->
          <div ref="chatContainer" class="box-body flex-1 overflow-y-auto space-y-4">
            <div 
              v-for="msg in messages" 
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

          <!-- Input -->
          <div class="box-footer border-t">
            <form @submit.prevent="sendMessage" class="flex items-center gap-2">
              <input
                v-model="newMessage"
                type="text"
                class="ti-form-control flex-1"
                placeholder="Type a message..."
              >
              <button type="button" class="ti-btn ti-btn-light ti-btn-icon"><i class="ri-attachment-line"></i></button>
              <button type="button" class="ti-btn ti-btn-light ti-btn-icon"><i class="ri-emotion-line"></i></button>
              <button type="submit" class="ti-btn ti-btn-primary ti-btn-icon"><i class="ri-send-plane-fill"></i></button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Channel Modal -->
    <div
      v-if="showAddChannelModal"
      class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40"
    >
      <div class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-sm mx-4">
        <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
          <h3 class="text-base font-semibold">Add Channel</h3>
          <button
            class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light"
            type="button"
            @click="closeAddChannelModal"
          >
            <i class="ri-close-line"></i>
          </button>
        </div>

        <div class="px-6 py-5 space-y-4">
          <div>
            <label class="ti-form-label text-sm mb-1">Channel Name <span class="text-danger">*</span></label>
            <input
              v-model="newChannel.name"
              type="text"
              class="ti-form-control"
              placeholder="e.g. product-launch"
              @keyup.enter="saveChannel"
            >
          </div>

          <p v-if="!newChannel.name.trim()" class="text-xs text-warning mt-1">
            Enter a channel name to enable save.
          </p>
        </div>

        <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
          <button class="ti-btn ti-btn-light" type="button" @click="closeAddChannelModal">
            Cancel
          </button>
          <button
            class="ti-btn ti-btn-primary"
            type="button"
            :disabled="!newChannel.name.trim()"
            @click="saveChannel"
          >
            Add Channel
          </button>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

