<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import ApexCharts from 'apexcharts';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Reports & Analytics',
    },
    stats: {
        type: Object,
        default: () => ({
            total_projects: 0,
            total_tasks: 0,
            completion_rate: 0,
            pending_quality_audits: 0,
            active_resources: 0,
            quality_pass_rate: 0,
        }),
    },
    tasksByStatus: {
        type: Object,
        default: () => ({ todo: 0, in_progress: 0, review: 0, done: 0 }),
    },
    resourceUtilization: {
        type: Object,
        default: () => ({ total: 0, available: 0, allocated: 0, unavailable: 0 }),
    },
    qualityByStatus: {
        type: Object,
        default: () => ({ pending: 0, passed: 0, failed: 0 }),
    },
    projectProgress: {
        type: Array,
        default: () => [],
    },
    teamWorkloads: {
        type: Array,
        default: () => [],
    },
});

const taskChart = ref(null);
let chart;

const taskStatusRows = computed(() => [
    { label: 'To Do', key: 'todo', color: 'bg-info' },
    { label: 'In Progress', key: 'in_progress', color: 'bg-primary' },
    { label: 'Review', key: 'review', color: 'bg-warning' },
    { label: 'Done', key: 'done', color: 'bg-success' },
]);

const resourceRows = computed(() => [
    { label: 'Available', key: 'available', color: 'bg-success' },
    { label: 'Allocated', key: 'allocated', color: 'bg-primary' },
    { label: 'Unavailable', key: 'unavailable', color: 'bg-danger' },
]);

const qualityRows = computed(() => [
    { label: 'Passed', key: 'passed', color: 'bg-success' },
    { label: 'Pending', key: 'pending', color: 'bg-warning' },
    { label: 'Failed', key: 'failed', color: 'bg-danger' },
]);

const percentOf = (value, total) => {
    if (!total) {
        return 0;
    }

    return Math.round((value / total) * 100);
};

const renderTaskChart = async () => {
    await nextTick();

    if (!taskChart.value) {
        return;
    }

    chart?.destroy();
    chart = new ApexCharts(taskChart.value, {
        chart: { type: 'donut', height: 220, toolbar: { show: false } },
        series: taskStatusRows.value.map((row) => props.tasksByStatus[row.key] || 0),
        labels: taskStatusRows.value.map((row) => row.label),
        colors: ['#0ea5e9', '#6366f1', '#f59e0b', '#22c55e'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        stroke: { width: 0 },
    });
    chart.render();
};

onMounted(renderTaskChart);
watch(() => props.tasksByStatus, renderTaskChart, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Overall project, task, resource, and quality health" />

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Total Tasks</p>
                    <h3 class="mb-0">{{ stats.total_tasks }}</h3>
                    <p class="text-xs text-textmuted mt-2 mb-0">{{ stats.total_projects }} projects tracked</p>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Completion %</p>
                    <h3 class="mb-0">{{ stats.completion_rate }}%</h3>
                    <div class="progress progress-xs mt-3">
                        <div class="progress-bar bg-success" :style="{ width: stats.completion_rate + '%' }"></div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Pending Quality Audits</p>
                    <h3 class="mb-0">{{ stats.pending_quality_audits }}</h3>
                    <p class="text-xs text-textmuted mt-2 mb-0">Pass rate {{ stats.quality_pass_rate }}%</p>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Active Resources</p>
                    <h3 class="mb-0">{{ stats.active_resources }}</h3>
                    <p class="text-xs text-textmuted mt-2 mb-0">{{ resourceUtilization.total }} total in pool</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-4">
            <div class="box">
                <div class="box-header">
                    <h6 class="box-title mb-0">Task Completion by Status</h6>
                </div>
                <div class="box-body">
                    <div ref="taskChart" class="mb-3"></div>
                    <div class="space-y-3">
                    <div v-for="row in taskStatusRows" :key="row.key">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span>{{ row.label }}</span>
                            <span>{{ tasksByStatus[row.key] }}</span>
                        </div>
                        <div class="progress progress-xs">
                            <div class="progress-bar" :class="row.color" :style="{ width: percentOf(tasksByStatus[row.key], stats.total_tasks) + '%' }"></div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h6 class="box-title mb-0">Resource Utilization</h6>
                </div>
                <div class="box-body space-y-3">
                    <div v-for="row in resourceRows" :key="row.key">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span>{{ row.label }}</span>
                            <span>{{ resourceUtilization[row.key] }}</span>
                        </div>
                        <div class="progress progress-xs">
                            <div class="progress-bar" :class="row.color" :style="{ width: percentOf(resourceUtilization[row.key], resourceUtilization.total) + '%' }"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h6 class="box-title mb-0">Audit Health</h6>
                </div>
                <div class="box-body space-y-3">
                    <div v-for="row in qualityRows" :key="row.key">
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span>{{ row.label }}</span>
                            <span>{{ qualityByStatus[row.key] }}</span>
                        </div>
                        <div class="progress progress-xs">
                            <div
                                class="progress-bar"
                                :class="row.color"
                                :style="{ width: percentOf(qualityByStatus[row.key], qualityByStatus.pending + qualityByStatus.passed + qualityByStatus.failed) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="box">
                <div class="box-header">
                    <h6 class="box-title mb-0">Project Progress</h6>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Completed</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="project in projectProgress" :key="project.id">
                                    <td class="font-medium">{{ project.name }}</td>
                                    <td>{{ project.completed_tasks_count }} / {{ project.tasks_count }}</td>
                                    <td>
                                        <div class="flex items-center gap-2 min-w-[140px]">
                                            <div class="progress progress-xs flex-1">
                                                <div class="progress-bar bg-primary" :style="{ width: project.progress + '%' }"></div>
                                            </div>
                                            <span class="text-xs text-textmuted">{{ project.progress }}%</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!projectProgress.length">
                                    <td colspan="3" class="text-center text-textmuted py-6">No project data yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header">
                    <h6 class="box-title mb-0">Team Workloads</h6>
                </div>
                <div class="box-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th>Assignee</th>
                                    <th>Total</th>
                                    <th>In Progress</th>
                                    <th>Done</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="member in teamWorkloads" :key="member.name">
                                    <td class="font-medium">{{ member.name }}</td>
                                    <td>{{ member.total }}</td>
                                    <td>{{ member.in_progress }}</td>
                                    <td>{{ member.done }}</td>
                                </tr>
                                <tr v-if="!teamWorkloads.length">
                                    <td colspan="4" class="text-center text-textmuted py-6">No workload data yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
