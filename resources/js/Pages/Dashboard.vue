<script setup>
import { computed, onMounted, onBeforeUnmount, nextTick, ref } from 'vue'
import ApexCharts from 'apexcharts'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Dashboard' },
  kpis: { type: Object, default: () => ({}) },
  runningProjects: { type: Array, default: () => [] },
  dailyTasks: { type: Array, default: () => [] },
  summaryProjects: { type: Array, default: () => [] },
  teamMembers: { type: Array, default: () => [] },
})

let charts = []

const formatNumber = (value) => Number(value || 0).toLocaleString()
const formatCurrency = (value) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value || 0)

const kpiTiles = computed(() => [
  { id: 'Projects-2', label: 'New Projects', value: formatNumber(props.kpis.new_projects), badge: 'Planning', badgeClass: 'bg-primary/10 text-primary', icon: 'ri-pages-line', iconBg: 'bg-primary' },
  { id: 'Projects-1', label: 'Completed', value: formatNumber(props.kpis.completed), badge: 'Done', badgeClass: 'bg-success/10 text-success', icon: 'ri-check-double-line', iconBg: 'bg-primarytint1color' },
  { id: 'Projects-3', label: 'Ongoing Projects', value: formatNumber(props.kpis.ongoing), badge: 'Active', badgeClass: 'bg-info/10 text-info', icon: 'ri-loop-left-fill', iconBg: 'bg-primarytint2color' },
  { id: 'Projects-4', label: 'Pending Projects', value: formatNumber(props.kpis.pending), badge: 'On hold', badgeClass: 'bg-warning/10 text-warning', icon: 'ri-time-line', iconBg: 'bg-primarytint3color' },
])

const summaryAvatars = ['/assets/img/8.jpg', '/assets/img/4.jpg', '/assets/img/6.jpg', '/assets/img/7.jpg']
const heroArtMissing = ref(false)

const statsPeriod = ref('Last Week')
const statsTotals = computed(() => ({
  revenue: formatCurrency(props.kpis.total_budget),
  projects: formatNumber(props.kpis.total_projects),
  revenueDelta: `${props.kpis.task_completion_rate || 0}%`,
  projectsDelta: `${props.kpis.ongoing || 0} live`,
  revenueUp: true,
  projectsUp: (props.kpis.ongoing || 0) > 0,
}))

const statsRanges = {
  Today: {
    categories: ['8am', '10am', '12pm', '2pm', '4pm', '6pm', '8pm'],
    projects: [6, 9, 7, 12, 10, 8, 11],
    revenue: [8, 10, 9, 14, 11, 9, 13],
    totals: { revenue: '$18,240', projects: '63', revenueDelta: '2.1%', projectsDelta: '0.8%', revenueUp: true, projectsUp: true },
  },
  'Last Week': {
    categories: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
    projects: [18, 24, 21, 32, 28, 36, 30],
    revenue: [22, 28, 25, 34, 31, 40, 33],
    totals: { revenue: '$475,896', projects: '75,896', revenueDelta: '5.6%', projectsDelta: '1.6%', revenueUp: true, projectsUp: false },
  },
  'Last Month': {
    categories: ['W1', 'W2', 'W3', 'W4'],
    projects: [42, 55, 48, 61],
    revenue: [50, 58, 52, 67],
    totals: { revenue: '$1.9M', projects: '206', revenueDelta: '4.2%', projectsDelta: '3.1%', revenueUp: true, projectsUp: true },
  },
  'Last Year': {
    categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    projects: [15, 28, 23, 23, 41, 58, 48, 50, 22, 31, 40, 45],
    revenue: [20, 29, 37, 35, 44, 43, 50, 20, 20, 45, 45, 52],
    totals: { revenue: '$5.4M', projects: '428', revenueDelta: '8.4%', projectsDelta: '2.7%', revenueUp: true, projectsUp: true },
  },
}

let projectStatsChart = null

const isDarkMode = () => document.documentElement.classList.contains('dark')

const currentStatsRange = () => statsRanges[statsPeriod.value] || statsRanges['Last Week']

const getPrimaryColor = () => {
  const primaryRgb = getComputedStyle(document.documentElement).getPropertyValue('--primary-rgb').trim()
  return primaryRgb ? `rgb(${primaryRgb})` : 'rgb(92, 103, 247)'
}

const buildProjectStatsOptions = (range) => {
  const dark = isDarkMode()
  const primaryColor = getPrimaryColor()

  return {
    series: [
      { name: 'Projects', data: range.projects },
      { name: 'Revenue', data: range.revenue },
    ],
    chart: {
        type: 'area',
        height: 360,
        fontFamily: 'inherit',
        background: 'transparent',
        foreColor: dark ? '#c8d0e8' : '#6b7280',
      toolbar: {
        show: true,
        offsetY: -6,
        tools: {
          download: true,
          selection: true,
          zoom: true,
          zoomin: true,
          zoomout: true,
          pan: true,
          reset: true,
        },
      },
      zoom: { enabled: true },
      animations: {
        enabled: true,
        easing: 'easeinout',
        speed: 1100,
        animateGradually: { enabled: true, delay: 140 },
        dynamicAnimation: { enabled: true, speed: 650 },
      },
      dropShadow: {
        enabled: true,
        enabledOnSeries: [0, 1],
        top: 8,
        left: 0,
        blur: 6,
        color: [primaryColor, 'rgb(227, 84, 212)'],
        opacity: 0.18,
      },
    },
    colors: [primaryColor, 'rgb(227, 84, 212)'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 3 },
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: dark ? 0.28 : 0.42,
          opacityTo: dark ? 0.02 : 0.04,
          stops: [0, 88, 100],
        },
      },
    markers: {
      size: 0,
      strokeWidth: 2,
      hover: { size: 6 },
    },
    grid: {
      borderColor: dark ? 'rgba(255,255,255,0.08)' : '#f1f1f1',
      strokeDashArray: 4,
      padding: { left: 8, right: 8, top: 8 },
    },
    xaxis: {
      categories: range.categories,
      overwriteCategories: range.categories,
      axisBorder: { show: false },
      axisTicks: { show: false },
      labels: { style: { fontSize: '12px' } },
    },
    yaxis: {
      labels: {
        formatter: function (value) {
          return Math.round(value)
        },
      },
    },
    legend: {
      show: true,
      position: 'top',
      horizontalAlign: 'left',
      fontSize: '13px',
      markers: { size: 6, offsetX: -3 },
      itemMargin: { horizontal: 12 },
    },
    tooltip: {
      shared: true,
      intersect: false,
      theme: dark ? 'dark' : 'light',
      y: {
        formatter: function (value) {
          return value.toLocaleString()
        },
      },
    },
    theme: { mode: dark ? 'dark' : 'light' },
  }
}

const renderProjectStatsChart = () => {
  const element = document.querySelector('#project-statistics')
  if (!element) {
    return
  }

  if (projectStatsChart) {
    charts = charts.filter((chart) => chart !== projectStatsChart)
    projectStatsChart.destroy()
    projectStatsChart = null
    element.innerHTML = ''
  }

  projectStatsChart = new ApexCharts(element, buildProjectStatsOptions(currentStatsRange()))
  projectStatsChart.render()
  charts.push(projectStatsChart)
}

const setStatsPeriod = (period) => {
  if (!statsRanges[period]) {
    return
  }

  statsPeriod.value = period
  renderProjectStatsChart()
}

let themeObserver = null

onMounted(() => {
  nextTick(() => {
    initializeCharts()
  })

  themeObserver = new MutationObserver(() => {
    nextTick(() => {
      initializeCharts()
    })
  })
  themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] })
})

onBeforeUnmount(() => {
  themeObserver?.disconnect()
  themeObserver = null
  charts.forEach((chart) => chart.destroy())
  charts = []
  projectStatsChart = null
})

const initializeCharts = () => {
  charts.forEach((chart) => {
    chart.destroy()
  })
  charts = []
  projectStatsChart = null

  const getPrimaryColor = () => {
    const root = document.documentElement
    const primaryRgb = getComputedStyle(root).getPropertyValue('--primary-rgb').trim()
    return primaryRgb ? `rgb(${primaryRgb})` : 'rgb(92, 103, 247)'
  }

  const primaryColor = getPrimaryColor()

  const sparklineOptions = [
    { id: 'Projects-1', color: primaryColor },
    { id: 'Projects-2', color: 'rgb(227, 84, 212)' },
    { id: 'Projects-3', color: 'rgb(255, 93, 159)' },
    { id: 'Projects-4', color: 'rgb(255, 142, 111)' },
  ]

  sparklineOptions.forEach((config) => {
    const element = document.querySelector(`#${config.id}`)
    if (element) {
      const options = {
        series: [{ data: [12, 14, 18, 47, 42, 15, 47, 75, 65, 19, 14, 50] }],
        chart: { type: 'bar', width: 70, height: 40, sparkline: { enabled: true }, background: 'transparent' },
        plotOptions: { bar: { columnWidth: '80%', borderRadius: 2 } },
        stroke: { curve: 'smooth', width: 2 },
        labels: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
        colors: [config.color],
        tooltip: {
          fixed: { enabled: false },
          x: { show: false },
          y: { title: { formatter: function () { return '' } } },
        },
      }
      const chart = new ApexCharts(element, options)
      chart.render()
      charts.push(chart)
    }
  })

  renderProjectStatsChart()

  const monthlyTargetElement = document.querySelector('#monthly-target')
  if (monthlyTargetElement) {
    const monthlyTargetChart = new ApexCharts(monthlyTargetElement, {
      series: [86, 80, 60],
      chart: { height: 220, type: 'radialBar', background: 'transparent' },
      plotOptions: {
        radialBar: {
          dataLabels: {
            name: { fontSize: '16px', offsetY: 0 },
            value: { fontSize: '13px', offsetY: 5 },
            total: { show: true, label: 'Total', formatter: function () { return 249 } },
          },
        },
      },
      stroke: { lineCap: 'round' },
      grid: { padding: { bottom: -10, top: -10 } },
      colors: [primaryColor, 'rgba(227, 84, 212, 0.5)', 'rgba(255, 93, 159, 0.4)'],
      labels: ['New Projects', 'Completed', 'Pending'],
    })
    monthlyTargetChart.render()
    charts.push(monthlyTargetChart)
  }

  const tasksReportElement = document.querySelector('#tasks-report')
  if (tasksReportElement) {
    const dark = isDarkMode()
    const tasksReportChart = new ApexCharts(tasksReportElement, {
      series: [
        { name: 'This Week', data: [44, 42, 57, 86, 58, 55, 70] },
        { name: 'Last Week', data: [34, 22, 42, 56, 21, 86, 60] },
      ],
      chart: {
        type: 'bar',
        height: 250,
        fontFamily: 'inherit',
        background: 'transparent',
        foreColor: dark ? '#c8d0e8' : '#6b7280',
        toolbar: { show: false },
        animations: {
          enabled: true,
          easing: 'easeinout',
          speed: 900,
          animateGradually: { enabled: true, delay: 80 },
          dynamicAnimation: { enabled: true, speed: 450 },
        },
      },
      plotOptions: {
        bar: {
          horizontal: false,
          columnWidth: '42%',
          borderRadius: 6,
          borderRadiusApplication: 'end',
          borderRadiusWhenStacked: 'last',
        },
      },
      grid: {
        borderColor: dark ? 'rgba(255,255,255,0.08)' : '#f1f1f1',
        strokeDashArray: 4,
        padding: { left: 4, right: 4 },
      },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      colors: [primaryColor, 'rgb(227, 84, 212)'],
      dataLabels: { enabled: false },
      legend: {
        show: true,
        position: 'top',
        horizontalAlign: 'left',
        fontSize: '13px',
        markers: { size: 6, offsetX: -3 },
        itemMargin: { horizontal: 12 },
      },
      tooltip: {
        shared: true,
        intersect: false,
        theme: dark ? 'dark' : 'light',
        y: {
          formatter: function (value) {
            return `${value} tasks`
          },
        },
      },
      yaxis: {
        labels: {
          formatter: function (y) {
            if (y === null || y === undefined) {
              return '0'
            }
            return y.toFixed(0)
          },
        },
      },
      xaxis: {
        type: 'category',
        categories: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { rotate: 0 },
      },
      theme: { mode: dark ? 'dark' : 'light' },
    })
    tasksReportChart.render()
    charts.push(tasksReportChart)
  }
}
</script>

<template>
  <AppLayout>
    <div class="pm-dash">
      <PageHeader title="Dashboard" subtitle="Portfolio overview and performance" />

      <!-- Row 1: statistics + activity -->
      <div class="grid grid-cols-12 gap-6">
        <div class="xxl:col-span-8 col-span-12">
          <div class="box h-full pm-stats-card">
            <div class="box-header justify-between">
              <div class="box-title">Project Statistics</div>
              <div class="ti-dropdown hs-dropdown">
                <a
                  aria-expanded="false"
                  aria-label="Select statistics period"
                  class="ti-btn ti-btn-sm bg-light"
                  data-bs-toggle="dropdown"
                  href="javascript:void(0);"
                >
                  {{ statsPeriod }} <i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                </a>
                <ul class="ti-dropdown-menu hs-dropdown-menu hidden">
                  <li v-for="period in Object.keys(statsRanges)" :key="period">
                    <a
                      class="ti-dropdown-item"
                      :class="{ 'is-active': statsPeriod === period }"
                      href="javascript:void(0);"
                      @click.prevent="setStatsPeriod(period)"
                    >{{ period }}</a>
                  </li>
                </ul>
              </div>
            </div>
            <div class="box-body">
              <div class="pm-stat-pills">
                <div class="pm-stat-pill">
                  <span class="avatar avatar-md avatar-rounded bg-primary/10 text-primary">
                    <i class="ri-stack-line text-xl"></i>
                  </span>
                  <div>
                    <span class="text-xs text-textmuted">Total Revenue</span>
                    <div class="flex items-center gap-2">
                      <h4 class="mb-0">{{ statsTotals.revenue }}</h4>
                      <span
                        class="badge leading-none text-white"
                        :class="statsTotals.revenueUp ? 'bg-success' : 'bg-danger'"
                      >
                        {{ statsTotals.revenueDelta }}
                        <i class="ms-1" :class="statsTotals.revenueUp ? 'ri-arrow-up-line' : 'ri-arrow-down-line'"></i>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="pm-stat-pill">
                  <span class="avatar avatar-md avatar-rounded bg-primarytint1color/10 text-primarytint1color">
                    <i class="ri-briefcase-line text-xl"></i>
                  </span>
                  <div>
                    <span class="text-xs text-textmuted">Total Projects</span>
                    <div class="flex items-center gap-2">
                      <h4 class="mb-0">{{ statsTotals.projects }}</h4>
                      <span
                        class="badge leading-none text-white"
                        :class="statsTotals.projectsUp ? 'bg-success' : 'bg-danger'"
                      >
                        {{ statsTotals.projectsDelta }}
                        <i class="ms-1" :class="statsTotals.projectsUp ? 'ri-arrow-up-line' : 'ri-arrow-down-line'"></i>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div id="project-statistics" class="pm-stats-chart mt-2"></div>
            </div>
          </div>
        </div>

        <div class="xxl:col-span-4 col-span-12">
          <div class="pm-focus-hero">
            <span class="pm-focus-hero__glow" aria-hidden="true"></span>
            <div class="pm-focus-hero__copy">
              <span class="pm-focus-pill">
                <i class="ri-flashlight-fill" aria-hidden="true"></i>
                On track
              </span>
              <h2>Manage Projects</h2>
              <p>One place for progress, owners, and what is due next.</p>
              <div class="pm-focus-metrics">
                <div class="pm-focus-chip">
                  <i class="ri-add-circle-fill" aria-hidden="true"></i>
                  <div>
                    <span>New</span>
                  <b>{{ formatNumber(kpis.new_projects) }}</b>
                </div>
              </div>
              <div class="pm-focus-chip">
                <i class="ri-checkbox-circle-fill" aria-hidden="true"></i>
                <div>
                  <span>Done</span>
                  <b>{{ formatNumber(kpis.completed) }}</b>
                  </div>
                </div>
              </div>
              <Link class="pm-focus-cta" href="/projects">
                Manage Now
                <i class="ri-arrow-right-line" aria-hidden="true"></i>
              </Link>
            </div>
            <div class="pm-focus-hero__art">
              <img
                v-show="!heroArtMissing"
                alt=""
                src="/assets/img/manage-projects.png"
                @error="heroArtMissing = true"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Row 2: activity · today's tasks · projects-worked donut -->
      <div class="grid grid-cols-12 gap-6">
        <div class="xxl:col-span-4 lg:col-span-5 col-span-12">
          <div class="box h-full">
            <div class="box-header justify-between">
              <div class="box-title">Activity</div>
              <Link class="ti-btn ti-btn-sm bg-light" href="/tasks">View All</Link>
            </div>
            <div class="box-body">
              <div class="pm-activity-head">
                <div>
                  <p class="text-xs text-textmuted mb-1">Tasks completed rate</p>
                  <h3 class="mb-0">{{ kpis.task_completion_rate || 0 }}%</h3>
                </div>
                <span class="badge leading-none bg-success/10 text-success">+1.5%</span>
              </div>
              <div id="tasks-report"></div>
            </div>
          </div>
        </div>

        <div class="xxl:col-span-5 lg:col-span-7 col-span-12">
          <div class="box h-full">
            <div class="box-header justify-between">
              <div class="box-title">Today’s tasks</div>
              <Link class="ti-btn ti-btn-sm bg-light" href="/tasks">View All</Link>
            </div>
            <div class="box-body pt-2">
              <ul v-if="dailyTasks.length" class="pm-task-list">
                <li v-for="task in dailyTasks" :key="task.id || task.title" :class="['pm-task-row', task.accent]">
                  <span class="pm-task-time">{{ task.time }}</span>
                  <span class="pm-task-icon" :class="task.iconClass">
                    <i :class="task.icon"></i>
                  </span>
                  <div class="min-w-0 grow">
                    <div class="flex items-center justify-between gap-2">
                      <p class="font-medium mb-1 truncate">{{ task.title }}</p>
                      <div class="hs-tooltip ti-main-tooltip">
                        <a aria-label="anchor" class="text-[1rem]" :class="task.iconClass.split(' ')[0]" href="javascript:void(0);">
                          <i class="ri-add-circle-fill"></i>
                          <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                            View Details
                          </span>
                        </a>
                      </div>
                    </div>
                    <div class="flex flex-wrap gap-1.5 items-center">
                      <span v-for="badge in task.badges" :key="badge.label" class="badge leading-none" :class="badge.class">{{ badge.label }}</span>
                      <div class="avatar-list-stacked ms-auto">
                        <span v-for="(src, idx) in task.avatars" :key="idx" class="avatar avatar-xs avatar-rounded">
                          <img alt="" :src="src" />
                        </span>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
              <p v-else class="py-8 text-center text-textmuted mb-0">No tasks have been created yet.</p>
            </div>
          </div>
        </div>

        <div class="xxl:col-span-3 col-span-12">
          <div class="box h-full">
            <div class="box-header justify-between">
              <div class="box-title">Projects worked</div>
              <Link class="ti-btn ti-btn-sm bg-light" href="/projects">View All</Link>
            </div>
            <div class="box-body">
              <div id="monthly-target"></div>
              <ul class="pm-legend">
                <li>
                  <i class="ri-circle-fill text-[8px] text-primary"></i>
                  <span>New Projects</span>
                  <b>{{ formatNumber(kpis.new_projects) }}</b>
                </li>
                <li>
                  <i class="ri-circle-fill text-[8px] text-primarytint1color"></i>
                  <span>Completed</span>
                  <b>{{ formatNumber(kpis.completed) }}</b>
                </li>
                <li>
                  <i class="ri-circle-fill text-[8px] text-primarytint2color"></i>
                  <span>Pending</span>
                  <b>{{ formatNumber(kpis.pending) }}</b>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 3: KPI strip -->
      <div class="grid grid-cols-12 gap-6">
        <div v-for="tile in kpiTiles" :key="tile.id" class="xxl:col-span-3 md:col-span-6 col-span-12">
          <div class="box overflow-hidden pm-stat-tile">
            <div class="box-body">
              <div class="mb-4 flex items-start justify-between">
                <span class="avatar avatar-sm avatar-rounded text-white" :class="tile.iconBg">
                  <i :class="[tile.icon, 'text-[1rem]']"></i>
                </span>
                <span class="badge leading-none" :class="tile.badgeClass">{{ tile.badge }}</span>
              </div>
              <div class="flex items-end justify-between flex-wrap gap-2">
                <div>
                  <div class="text-textmuted dark:text-textmuted/50 mb-1 text-xs">{{ tile.label }}</div>
                  <h4 class="mb-0 text-2xl font-semibold">{{ tile.value }}</h4>
                </div>
                <div class="flex-shrink-0 text-end ms-auto" :id="tile.id"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 4: running projects as cards -->
      <div class="box">
        <div class="box-header justify-between">
          <div class="box-title">Running Projects</div>
          <Link class="ti-btn ti-btn-sm bg-primary/10 text-primary" href="/projects">View All</Link>
        </div>
        <div class="box-body">
          <div class="grid grid-cols-12 gap-4">
            <p v-if="!runningProjects.length" class="col-span-12 text-center text-textmuted py-8 mb-0">No running projects yet.</p>
            <div v-for="project in runningProjects" :key="project.id || project.title" class="xxl:col-span-4 md:col-span-6 col-span-12">
              <div class="pm-run-card">
                <div class="flex items-start justify-between gap-3 mb-3">
                  <div>
                    <div class="flex items-center gap-1">
                      <p class="font-medium mb-1">{{ project.title }}</p>
                      <a class="text-info hs-tooltip ti-main-tooltip" href="javascript:void(0);">
                        <i class="ri-information-2-line text-[13px] opacity-70"></i>
                        <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm" role="tooltip">
                          Get Info
                        </span>
                      </a>
                    </div>
                    <p class="text-textmuted dark:text-textmuted/50 text-xs mb-0">{{ project.description }}</p>
                  </div>
                  <span class="text-[11px] text-textmuted whitespace-nowrap">
                    <i class="ri-time-line me-1"></i>{{ project.time }}
                  </span>
                </div>
                <div class="flex items-center justify-between mb-3">
                  <span class="text-xs" :class="project.statusClass">{{ project.statusLabel }}</span>
                  <div class="avatar-list-stacked">
                    <span v-for="src in project.avatars" :key="src" class="avatar avatar-sm avatar-rounded">
                      <img alt="" :src="src" />
                    </span>
                    <a v-if="project.extra" class="avatar avatar-sm bg-primary border-2 avatar-rounded text-white" href="javascript:void(0);">
                      {{ project.extra }}+
                    </a>
                  </div>
                </div>
                <div
                  class="progress progress-lg !rounded-full p-1"
                  :class="project.trackClass"
                  role="progressbar"
                  :aria-valuenow="project.progress"
                  aria-valuemin="0"
                  aria-valuemax="100"
                >
                  <div class="progress-bar progress-bar-striped progress-bar-animated !rounded-full" :class="project.barClass" :style="{ width: project.progress + '%' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Row 5: summary table + team rail -->
      <div class="grid grid-cols-12 gap-6">
        <div class="xxl:col-span-8 col-span-12">
          <div class="box h-full">
            <div class="box-header justify-between">
              <div class="box-title">Projects Summary</div>
              <div class="flex flex-wrap items-center gap-2">
                <input
                  aria-label=".form-control-sm example"
                  class="ti-form-control form-control-sm"
                  placeholder="Search Here"
                  type="text"
                />
                <div class="ti-dropdown hs-dropdown">
                  <a
                    aria-expanded="false"
                    class="ti-btn bg-primary !m-0 text-white ti-btn-sm ti-dropdown-toggle hs-dropdown-toggle"
                    data-bs-toggle="dropdown"
                    href="javascript:void(0);"
                  >
                    Sort By<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                  </a>
                  <ul class="ti-dropdown-menu hs-dropdown-menu hidden" role="menu">
                    <li><a class="ti-dropdown-item" href="javascript:void(0);">New</a></li>
                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Popular</a></li>
                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Relevant</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="box-body">
              <div class="table-responsive overflow-auto">
                <table class="table table-hover whitespace-nowrap">
                  <thead>
                    <tr class="border-b border-defaultborder dark:border-defaultborder/10">
                      <th scope="col">S.No</th>
                      <th scope="col">Project Title</th>
                      <th scope="col">Tasks</th>
                      <th scope="col">Progress</th>
                      <th scope="col">Assigned Team</th>
                      <th scope="col">Status</th>
                      <th scope="col">Due Date</th>
                      <th scope="col">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-if="!summaryProjects.length">
                      <td colspan="8" class="text-center text-textmuted py-8">No projects have been created yet.</td>
                    </tr>
                    <tr v-for="row in summaryProjects" :key="row.id || row.no">
                      <td>{{ row.no }}</td>
                      <td><span class="font-medium">{{ row.title }}</span></td>
                      <td>{{ row.tasks }} <span class="opacity-70">/{{ row.tasksTotal }}</span></td>
                      <td>
                        <div class="flex items-center">
                          <div class="progress progress-sm w-full" role="progressbar" :aria-valuenow="row.progress" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar bg-primary" :style="{ width: row.progress + '%' }"></div>
                          </div>
                          <div class="ms-2">{{ row.progress }}%</div>
                        </div>
                      </td>
                      <td>
                        <div class="avatar-list-stacked">
                          <span v-for="src in summaryAvatars" :key="src" class="avatar avatar-xs avatar-rounded">
                            <img alt="" :src="src" />
                          </span>
                        </div>
                      </td>
                      <td><span class="badge leading-none" :class="row.statusClass">{{ row.status }}</span></td>
                      <td>{{ row.due }}</td>
                      <td>
                        <div class="flex items-center gap-2">
                          <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                            <Link :href="`/projects/${row.id}`" aria-label="View project" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-primary !m-0">
                              <i class="ri-eye-line"></i>
                              <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">View</span>
                            </Link>
                          </div>
                          <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                            <a aria-label="anchor" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-secondary !m-0" href="javascript:void(0);">
                              <i class="ri-pencil-line"></i>
                              <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">Edit</span>
                            </a>
                          </div>
                          <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                            <a aria-label="anchor" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-danger !m-0" href="javascript:void(0);">
                              <i class="ri-delete-bin-line"></i>
                              <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">Delete</span>
                            </a>
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="xxl:col-span-4 col-span-12">
          <div class="box h-full">
            <div class="box-header justify-between">
              <div class="box-title">Team</div>
              <Link class="ti-btn ti-btn-sm bg-light" href="/resources/team">View All</Link>
            </div>
            <div class="box-body pt-2">
              <p v-if="!teamMembers.length" class="py-8 text-center text-textmuted mb-0">No team members yet.</p>
              <div v-for="member in teamMembers" :key="member.id || member.name" class="pm-team-row">
                <span class="avatar avatar-sm avatar-rounded">
                  <img alt="" :src="member.avatar" />
                </span>
                <div class="min-w-0 grow">
                  <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold truncate">{{ member.name }}</span>
                    <span class="badge leading-none" :class="member.status === 'Online' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger'">
                      {{ member.status }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between text-xs text-textmuted dark:text-textmuted/50">
                    <a href="javascript:void(0);">{{ member.role }}</a>
                    <span>{{ member.works }} works · {{ member.tasks }}/{{ member.tasksTotal }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-1">
                  <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                    <a aria-label="anchor" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-primary !m-0" href="javascript:void(0);">
                      <i class="ri-user-add-line align-middle"></i>
                      <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">Assign</span>
                    </a>
                  </div>
                  <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                    <a aria-label="anchor" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-info !m-0" href="javascript:void(0);">
                      <i class="ri-at-line align-middle"></i>
                      <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">Mail</span>
                    </a>
                  </div>
                  <div class="hs-tooltip ti-main-tooltip [--placement:top]">
                    <a aria-label="anchor" class="hs-tooltip-toggle ti-btn ti-btn-icon ti-btn-sm !rounded-full ti-btn-soft-primary2 !m-0" href="javascript:void(0);">
                      <i class="ri-eye-line align-middle"></i>
                      <span class="hs-tooltip-content ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm dark:bg-slate-700" role="tooltip">View</span>
                    </a>
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
