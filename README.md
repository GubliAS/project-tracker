# Project Tracker

Laravel + Vue 3 + Inertia base app for converting the Project Tracker HTML/Vue template into a full-stack application.

**No template conversion is included yet** — only routes, placeholders, layout shell, and conventions for the team.

## Reference template (do not modify for base setup)

The original reference UI lives alongside this repo:

```
../Project Tracker/vue-app/
```

Use it as the source when converting pages into `resources/js/Pages/`.

## Stack

- Laravel 13
- Vue 3 (`<script setup>`)
- Inertia.js
- Vite

## Setup

```bash
cd project-tracker
composer install
cp .env.example .env   # skip if .env exists
php artisan key:generate
php artisan migrate
npm install
```

Run dev servers:

```bash
php artisan serve
npm run dev
```

Open http://127.0.0.1:8000

## Routes (match reference vue-app)

| Route | Page component | Controller |
|-------|----------------|------------|
| `/` | `Pages/Dashboard.vue` | `DashboardController@index` |
| `/projects` | `Pages/Projects/Index.vue` | `ProjectController@index` |
| `/projects/create` | `Pages/Projects/Create.vue` | `ProjectController@create` |
| `/projects/{id}` | `Pages/Projects/Show.vue` | `ProjectController@show` |
| `/initiation/kickoff` | `Pages/Initiation/Kickoff.vue` | `InitiationController@kickoff` |
| `/initiation/stakeholders` | `Pages/Initiation/Stakeholders.vue` | `InitiationController@stakeholders` |
| `/agile/sprints` | `Pages/Agile/Sprints.vue` | `AgileController@sprints` |
| `/agile/backlog` | `Pages/Agile/Backlog.vue` | `AgileController@backlog` |
| `/agile/definitions` | `Pages/Agile/Definitions.vue` | `AgileController@definitions` |
| `/tasks` | `Pages/Tasks/Index.vue` | `TaskController@index` |
| `/tasks/kanban` | `Pages/Tasks/Kanban.vue` | `TaskController@kanban` |
| `/tasks/workflows` | `Pages/Tasks/Workflows.vue` | `TaskController@workflows` |
| `/resources/team` | `Pages/Resources/Team.vue` | `ResourceController@team` |
| `/resources/time-tracking` | `Pages/Resources/TimeTracking.vue` | `ResourceController@timeTracking` |
| `/resources/budget` | `Pages/Resources/Budget.vue` | `ResourceController@budget` |
| `/resources/milestones` | `Pages/Resources/Milestones.vue` | `ResourceController@milestones` |
| `/resources/gantt` | `Pages/Resources/Gantt.vue` | `ResourceController@gantt` |
| `/quality/qa-testing` | `Pages/Quality/QaTesting.vue` | `QualityController@qaTesting` |
| `/quality/risks` | `Pages/Quality/Risks.vue` | `QualityController@risks` |
| `/quality/change-log` | `Pages/Quality/ChangeLog.vue` | `QualityController@changeLog` |
| `/reports/analytics` | `Pages/Reports/Analytics.vue` | `ReportController@analytics` |
| `/reports/documents` | `Pages/Reports/Documents.vue` | `ReportController@documents` |
| `/reports/lessons-learned` | `Pages/Reports/LessonsLearned.vue` | `ReportController@lessonsLearned` |
| `/chat` | `Pages/Communication/Chat.vue` | `CommunicationController@chat` |

## Project structure

```
project-tracker/
├── app/Http/Controllers/       # One controller per section
├── public/assets/              # Template static files (css, js, img, fonts)
├── resources/js/
│   ├── Pages/                  # Inertia pages (placeholders)
│   ├── Layouts/AppLayout.vue     # Shared layout shell
│   ├── Components/Partials/    # Header, sidebar, footer stubs
│   └── config/navigation.js    # Sidebar links (matches routes)
├── routes/web.php
└── .cursor/rules/              # Team AI rules
```

## Conversion workflow (when you start)

1. Copy static assets from the reference app into `public/assets/`.
2. Wire CSS/JS in `resources/views/app.blade.php`.
3. Replace placeholder markup in your assigned `Pages/*` and `Components/Partials/*` files.
4. Keep backend data in controllers; use Inertia props in Vue pages.
5. Use Inertia `<Link>` instead of vue-router.

## Git / team notes

- Do **not** commit `.env`, `vendor/`, or `node_modules/`.
- `.cursor/rules/` is committed for shared AI conventions.
- Assign sections by route group to avoid merge conflicts.
