<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const pageProps = defineProps({ title: { type: String, default: 'Documents' } })

const documents = ref([
  { id: 1, name: 'Project Charter.pdf', type: 'pdf', size: '2.4 MB', project: 'Website Redesign', uploadedBy: 'John Doe', date: '2024-11-15' },
  { id: 2, name: 'Technical Specs.docx', type: 'doc', size: '1.8 MB', project: 'Mobile App', uploadedBy: 'Jane Smith', date: '2024-11-20' },
  { id: 3, name: 'UI Mockups.figma', type: 'design', size: '15.2 MB', project: 'Website Redesign', uploadedBy: 'Mike Johnson', date: '2024-11-25' },
  { id: 4, name: 'Budget Report.xlsx', type: 'excel', size: '890 KB', project: 'Data Migration', uploadedBy: 'Sarah Wilson', date: '2024-11-28' },
  { id: 5, name: 'Meeting Notes.pdf', type: 'pdf', size: '456 KB', project: 'CRM Integration', uploadedBy: 'David Brown', date: '2024-12-01' }
])

const getFileIcon = (type) => ({
  'pdf': 'ri-file-pdf-line text-danger',
  'doc': 'ri-file-word-line text-primary',
  'excel': 'ri-file-excel-line text-success',
  'design': 'ri-palette-line text-purple-500'
})[type] || 'ri-file-line text-secondary'

const getFileIconBg = (type) => ({
  'pdf': 'bg-danger/10',
  'doc': 'bg-primary/10',
  'excel': 'bg-success/10',
  'design': 'bg-purple-500/10'
})[type] || 'bg-secondary/10'

const searchQuery = ref('')
const categoryFilter = ref('all')
</script>

<template>
  <AppLayout title="Documents">
<div class="pm-dash">
    <PageHeader title="Documents" subtitle="Manage project documents and files">
      <template #actions>
        <button class="ti-btn ti-btn-primary">
          <i class="ri-upload-cloud-line me-1"></i> Upload Document
        </button>
      </template>
    </PageHeader>

    <div class="box">
      <div class="box-header flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="relative">
            <input v-model="searchQuery" type="text" class="ti-form-control !ps-10" placeholder="Search documents...">
            <i class="ri-search-line absolute start-3 top-1/2 -translate-y-1/2 text-textmuted"></i>
          </div>
          <select v-model="categoryFilter" class="ti-form-select w-auto">
            <option value="all">All Types</option>
            <option value="pdf">PDF</option>
            <option value="doc">Documents</option>
            <option value="excel">Spreadsheets</option>
            <option value="design">Design Files</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button class="ti-btn ti-btn-light ti-btn-icon"><i class="ri-list-unordered"></i></button>
          <button class="ti-btn ti-btn-primary ti-btn-icon"><i class="ri-grid-fill"></i></button>
        </div>
      </div>

      <div class="box-body">
        <div class="grid grid-cols-12 gap-4">
          <div v-for="doc in documents" :key="doc.id" class="col-span-12 md:col-span-6 lg:col-span-4 xl:col-span-3">
            <div class="box h-full mb-0 hover:shadow-lg transition-shadow cursor-pointer">
              <div class="box-body flex flex-col h-full">
                <span class="avatar avatar-lg avatar-rounded pm-icon-tile mb-3" :class="getFileIconBg(doc.type)">
                  <i :class="getFileIcon(doc.type)" class="text-2xl"></i>
                </span>
                <h6 class="font-medium text-sm mb-1 truncate" :title="doc.name">{{ doc.name }}</h6>
                <p class="text-xs text-textmuted mb-2">{{ doc.project }}</p>
                <div class="flex items-center justify-between text-xs text-textmuted mb-3">
                  <span>{{ doc.size }}</span>
                  <span>{{ doc.date }}</span>
                </div>
                <div class="flex items-center gap-2 mt-auto pt-3 border-t border-defaultborder dark:border-defaultborder/10">
                  <div class="hs-tooltip ti-main-tooltip">
                    <button class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-primary !m-0" type="button">
                      <i class="ri-eye-line"></i>
                      <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">View</span>
                    </button>
                  </div>
                  <div class="hs-tooltip ti-main-tooltip">
                    <button class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-success !m-0" type="button">
                      <i class="ri-download-line"></i>
                      <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">Download</span>
                    </button>
                  </div>
                  <div class="hs-tooltip ti-main-tooltip ms-auto">
                    <button class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-danger !m-0" type="button">
                      <i class="ri-delete-bin-line"></i>
                      <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">Delete</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>

