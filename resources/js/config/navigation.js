export const navigation = [
    { label: 'Dashboard', href: '/' },
    {
        label: 'Projects',
        children: [
            { label: 'Projects List', href: '/projects' },
            { label: 'Create Project', href: '/projects/create' },
            { label: 'Project Details', href: '/projects/1' },
        ],
    },
    {
        label: 'Initiation',
        children: [
            { label: 'Kick-Off', href: '/initiation/kickoff' },
            { label: 'Stakeholders', href: '/initiation/stakeholders' },
        ],
    },
    {
        label: 'Agile',
        children: [
            { label: 'Sprints', href: '/agile/sprints' },
            { label: 'Backlog', href: '/agile/backlog' },
            { label: 'DoR / DoD', href: '/agile/definitions' },
        ],
    },
    {
        label: 'Tasks',
        children: [
            { label: 'Kanban Board', href: '/tasks' },
            { label: 'Task List', href: '/tasks/kanban' },
            { label: 'Workflows', href: '/tasks/workflows' },
        ],
    },
    {
        label: 'Resources',
        children: [
            { label: 'Resource Pool', href: '/resources' },
            { label: 'Team', href: '/resources/team' },
            { label: 'Time Tracking', href: '/resources/time-tracking' },
            { label: 'Budget', href: '/resources/budget' },
            { label: 'Milestones', href: '/resources/milestones' },
            { label: 'Gantt Chart', href: '/resources/gantt' },
        ],
    },
    {
        label: 'Quality',
        children: [
            { label: 'Quality Control', href: '/quality' },
            { label: 'QA & Testing', href: '/quality/testing' },
            { label: 'Risks & Issues', href: '/quality/risks' },
            { label: 'Change Log', href: '/quality/changelog' },
        ],
    },
    {
        label: 'Reports',
        children: [
            { label: 'Analytics', href: '/reports' },
            { label: 'Documents', href: '/reports/documents' },
            { label: 'Lessons Learned', href: '/reports/lessons' },
        ],
    },
    { label: 'Chat', href: '/chat' },
];
