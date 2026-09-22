# Project Tracker — PostgreSQL + Real Data Migration Guide

Partner handoff. Goal: run the app on **local PostgreSQL (pgAdmin)**, replace SQLite, seed demo data that currently lives in Vue, then **wire every page to the database** so mock `ref([...])` arrays go away.

Stack: Laravel 13, PHP 8.3+, Vue 3 + Inertia v3, Windows.

---

## 0. How the app works today (read this first)

- **Database now:** SQLite at `database/database.sqlite` (`DB_CONNECTION=sqlite` in `.env` / `.env.example`).
- **Backend already exists** for many modules: models, migrations, factories, and `store` / `update` / `destroy` routes.
- **UI is still mock.** Most pages receive Inertia props from the controller and **ignore them**, rendering hardcoded arrays instead.
- **Seeder today** (`database/seeders/DatabaseSeeder.php`) only creates `test@example.com`. There is almost no real app data in SQLite.
- Switching to Postgres **does not** make the UI real by itself. You must (1) point `.env` at Postgres, (2) migrate + seed, (3) bind Vue pages to props / forms.

Tests stay on SQLite in-memory (`phpunit.xml`). Do **not** point PHPUnit at Postgres.

---

## 1. Local project setup

```bash
cd project-tracker
composer install
copy .env.example .env
php artisan key:generate
npm install
```

Run both:

```bash
php artisan serve          # http://127.0.0.1:8000
npm run dev                # Vite
```

Confirm the app boots on SQLite before changing the database.

Optional checks:

```bash
php -v                     # 8.3+
php artisan --version
php artisan migrate:status
```

---

## 2. Install PostgreSQL + pgAdmin

1. Install PostgreSQL 16 or 17 for Windows (include **Command Line Tools**).
2. Install **pgAdmin 4** (usually bundled).
3. Remember the `postgres` superuser password.
4. Default port: **5432**.

Create the database in pgAdmin:

1. Connect to **PostgreSQL 16/17** → localhost.
2. Right-click **Databases** → **Create** → **Database**.
3. Name: `project_tracker`
4. Owner: `postgres` (or a dedicated user).
5. Encoding: UTF8.

Or SQL (Query Tool / psql):

```sql
CREATE DATABASE project_tracker
  WITH OWNER = postgres
       ENCODING = 'UTF8'
       TEMPLATE = template0;
```

Optional dedicated role:

```sql
CREATE USER tracker WITH PASSWORD 'choose_a_password';
GRANT ALL PRIVILEGES ON DATABASE project_tracker TO tracker;
```

On Postgres 15+, also grant schema rights after connecting to `project_tracker`:

```sql
GRANT ALL ON SCHEMA public TO tracker;
```

---

## 3. Enable PHP `pdo_pgsql`

Laravel’s `pgsql` driver is already in `config/database.php`. PHP must load the extension.

```bash
php --ri pdo_pgsql
```

If it is missing:

1. Open the `php.ini` used by CLI (`php --ini`).
2. Uncomment or add:

   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```

3. Restart the terminal (and `php artisan serve`).
4. Confirm `php --ri pdo_pgsql` prints the module.

---

## 4. Point Laravel at Postgres

Update **`.env`** (keep this out of git):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=project_tracker
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Remove or ignore SQLite-only lines. Do **not** set `DB_DATABASE` to a file path.

Then:

```bash
php artisan config:clear
php artisan config:show database.default
```

Expected: `pgsql`.

Leave `phpunit.xml` as:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

---

## 5. Run migrations (create tables in Postgres)

Existing migrations already define the schema. Fresh local DB:

```bash
php artisan migrate:fresh --seed
```

`--seed` runs `database/seeders/DatabaseSeeder.php` (today: one user).

Useful commands:

```bash
php artisan migrate              # apply pending only
php artisan migrate:status       # see what ran
php artisan db:show              # confirm pgsql + table list
php artisan db:table users       # inspect a table
```

In pgAdmin: refresh `project_tracker` → **Schemas** → **public** → **Tables**. You should see:

| Table | Used by |
|---|---|
| `users` | assignees, chat authors |
| `projects` | almost every module |
| `tasks` | tasks, gantt, reports |
| `resources` | team, budget, resource pool |
| `time_entries` | time tracking |
| `milestones` | milestones, gantt |
| `quality_checks` | QA / quality |
| `risks` | risks |
| `changelogs` | change log |
| `documents` | documents (files on disk) |
| `lesson_learneds` | lessons |
| `chat_messages` | chat |
| `cache`, `jobs`, `sessions`, … | Laravel internals |

### Enums

Several migrations use `$table->enum(...)`. Laravel creates native Postgres enums on a **fresh** migrate. That is fine. Avoid `->change()` on enum columns later without a careful new migration.

### Do not copy SQLite

SQLite only has the test user plus whatever was posted in testing. Start clean with `migrate:fresh --seed`. Do not use pgloader unless you later have production SQLite you must keep.

---

## 6. Seed data that currently lives in Vue

**Problem:** demo rows (John Doe, Website Redesign, Sprint 12, etc.) are hardcoded in `resources/js/Pages/**/*.vue`. Refreshing the page resets them. They never hit Postgres.

**Approach:** expand `DatabaseSeeder` (and child seeders) so they **create the same demo world in Postgres**. Then the UI reads it from the DB.

### 6.1 Seeder layout (suggested)

```
database/seeders/
  DatabaseSeeder.php          # calls others in order
  UserSeeder.php
  ProjectSeeder.php
  TaskSeeder.php
  ResourceSeeder.php
  TimeEntrySeeder.php
  MilestoneSeeder.php
  QualityCheckSeeder.php
  RiskSeeder.php
  ChangelogSeeder.php
  LessonSeeder.php
  ChatMessageSeeder.php
```

Create with:

```bash
php artisan make:seeder ProjectSeeder --no-interaction
```

### 6.2 Order (FKs)

1. Users
2. Projects
3. Resources (humans used as team)
4. Tasks (need `project_id`, `user_id`)
5. Time entries, milestones, quality checks, risks, documents metadata, lessons, chat messages

Use `firstOrCreate` on unique emails so `db:seed` can be re-run without unique errors (`users.email`).

### 6.3 Map mock fields → real columns

Controllers/models **do not match** the Vue mock shapes. Seed **database columns**, then adapt Vue to those names (section 7).

**Project** (`projects`) fillable: `name`, `description`, `status`  
Mock also has: `team`, `progress`, `priority`, `dueDate`, `budget`, `spent`.

- **Short path:** seed `name` / `description` / `status` (`planning` and later values you add). Compute progress from tasks in the controller.
- **Better path:** add a migration for `priority`, `due_date`, `budget`, `spent` (and maybe `client`) **before** wiring Projects pages.

**Task** — DB enums: `status`: `todo`, `in_progress`, `review`, `done`; `priority`: `low`, `medium`, `high`, `urgent`.  
Mock uses `pending` / `in-progress` / `completed` and `assignee` as a string. Seed `user_id` and `project_id` instead.

**Resource** — `name`, `type` (`human|hardware|software|material`), `role_or_category`, `cost_per_hour`, `availability_status` (`available|allocated|unavailable`).  
Team mock uses `email`, numeric `availability` %, `projects` count — **not in the table**. Either add columns or drop those UI fields.

**Risk** — `title`, `impact`, `probability`, `status` (`open|monitoring|mitigated|closed`), `mitigation_plan`, `project_id`.  
Mock uses `category`, `owner`, `mitigating`. Align UI to DB statuses.

**Changelog** — `version`, `title`, `description`, `type` (`feature|improvement|fix|security`), `release_date`.  
Visible page is **change requests** (`requestor`, `approved`). Either change the Vue to a release log, or add a `change_requests` table. Do not pretend they are the same.

**Lesson** — `title`, `category`, `impact_level` (`low|medium|high`), `recommendation`, `project_id`.  
Mock uses `impact: positive|negative`. Bind to `impact_level` and `recommendation`.

**Chat** — `user_id`, `project_id`, `message`.  
Mock uses channel names and avatars. Treat **projects as channels** (controller already does `?project=`).

**Quality check** — `project_id`, `title`, `check_type` (`code_review|testing|security_audit|compliance`), `status` (`pending|passed|failed`), `notes`.

**Milestone** — `project_id`, `title`, `due_date`, `status` (`upcoming|in_progress|completed|delayed`).

**Time entry** — `project_id`, `resource_id`, `task_id`, `entry_date`, `hours`, `description`.

**Document** — `name`, `file_path`, `category` (`planning|design|technical|financial|quality|other`), `size`, `project_id`. Files live in `storage/app/public`. Seed metadata only, or skip files until upload UI is wired. Run `php artisan storage:link`.

### 6.4 Example seed snippet

```php
$user = User::query()->firstOrCreate(
    ['email' => 'test@example.com'],
    ['name' => 'Test User', 'password' => 'password'],
);

$project = Project::query()->firstOrCreate(
    ['name' => 'Website Redesign'],
    ['description' => 'Complete redesign of corporate website', 'status' => 'planning'],
);

Task::query()->create([
    'project_id' => $project->id,
    'user_id' => $user->id,
    'title' => 'Design homepage mockup',
    'status' => 'done',
    'priority' => 'high',
    'due_date' => '2026-12-05',
]);
```

Prefer factories for volume:

```php
Task::factory()->count(8)->create(['project_id' => $project->id]);
```

### 6.5 Modules with **no tables yet**

These pages are 100% mock and have **no model**:

- Kickoff, Stakeholders (`InitiationController` only renders)
- Sprints, Backlog, DoR/DoD (`AgileController` only renders)
- Workflows (`TaskController::workflows` hardcodes a PHP array)
- Dashboard charts / team widget / project summary widget (`/` and `/dashboard` are closures with no props)

**Decision for the partner:**

| Option | When |
|---|---|
| **A. Defer** — leave those pages mock until a later sprint | Fastest to ship “core tracker” |
| **B. Add migrations** — `sprints`, `backlog_items`, `stakeholders`, `kickoffs` | Needed if those screens must be real |

Do not invent silent localStorage hacks.

After seeders exist:

```bash
php artisan migrate:fresh --seed
```

Confirm in pgAdmin: rows in `users`, `projects`, `tasks`, etc.

---

## 7. Wire pages — kill mock data

### 7.1 Pattern (every page)

**Before (mock):**

```js
const tasks = ref([{ id: 1, title: 'Design homepage mockup', ... }])
```

**After (Inertia props from Laravel):**

```js
const props = defineProps({
  title: String,
  tasks: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  users: { type: Array, default: () => [] },
})
```

Template uses `props.tasks` (or destructure). Empty DB → empty table + one empty-state sentence, **not** fake rows.

**Writes:** `useForm` from `@inertiajs/vue3` → `form.post('/tasks')` matching existing routes. Do not add Axios.

Keep imports **inside** `<script setup>` or `AppLayout` will not render.

### 7.2 Controllers already pass props — bind the Vue

| Route | Controller already sends | Vue still mocks | Action |
|---|---|---|---|
| `GET /tasks` | `tasks`, `projects`, `users` | `Tasks/Index.vue` | Use props; map `task.user.name`, `task.project.name`; statuses `todo` / `in_progress` / `review` / `done` |
| `GET /tasks/kanban` | same | `Tasks/Kanban.vue` | Group `tasks` by `status` into columns |
| `GET /tasks/workflows` | hardcoded PHP `workflows` | mock | Either real table later, or keep as static **config** (not fake tasks) |
| `GET /resources/team` | `resources` (type=human) | `Team.vue` `teamMembers` | Render `resources`; drop email/% if not in DB |
| `GET /resources/time-tracking` | `entries`, `projects`, `resources`, `tasks` | mock entries | Table from `entries`; Log Time → `POST /resources/time-tracking` |
| `GET /resources/budget` | `resources`, `summary` | `$500k` mock | Show hourly cost summary **or** add a budget table |
| `GET /resources/milestones` | `milestones`, `projects` | mock timeline | Bind `title` / `due_date` / `status`; forms already exist |
| `GET /resources/gantt` | `tasks`, `milestones` with due dates | CSS mock gantt | Derive bars from `due_date` (simplified) |
| `GET /quality/qa-testing` | `testCases`, `projects`, `summary` | mock stats | Use `summary` + `testCases` |
| `GET /quality/risks` | `risks`, `projects` | mock | Bind props; POST/PUT/DELETE already exist |
| `GET /quality/change-log` | `changes` | change-request mock | Align UI with changelog fields **or** new table |
| `GET /reports/analytics` | **no DB props** (`analytics()` renders template only) | report-type cards | Either restore `ReportController::index` aggregates into this page, or keep cards as **navigation** only |
| `GET /reports/documents` | `documents`, `projects` | mock files | Bind props; upload via existing `POST /reports/documents` |
| `GET /reports/lessons-learned` | `lessons`, `projects` | mock | Bind `LessonsLearned.vue` **or** switch route to `Reports/Lessons.vue` (already wired) |
| `GET /chat` | `projects`, `selectedProjectId`, `messages` | mock channels | Sidebar = `projects`; messages from props; send `POST /chat` |

### 7.3 Controllers not implemented — add them

| Page | Gap | Work |
|---|---|---|
| Dashboard | Closures `Inertia::render('Dashboard')`, no props | Point `/` and `/dashboard` at `DashboardController@index`: counts, recent tasks, team snippet from DB; replace hardcoded KPIs/charts |
| Projects list | `GET /projects` is a closure that renders `Projects` (not `ProjectController`) | Use `ProjectController@index`: `Project::withCount('tasks')->get()` |
| Create project | No `POST` | Add `store` + validation; expand `projects` columns if the form needs budget/dates |
| Project show | Only `projectId` string | `show(Project $project)` + tasks; route model binding |
| Kickoff / stakeholders / agile | No models | Defer (6.5) or new migrations + CRUD |

`ProjectController` today has **no `store`**. Tasks/risks/chat cannot belong to real projects until this exists. **Do projects first.**

### 7.4 Already-wired Vue files (optional reuse)

These **do** use props (CRUD UI, not the Ynex mock):

- `Resources/Index.vue`
- `Quality/Index.vue`
- `Reports/Index.vue`
- `Reports/Lessons.vue`

Nav currently **redirects away** from the first three (`/resources` → team, `/quality` → qa-testing, `/reports` → analytics). You can either:

- keep the template look and bind props (preferred for visual match), or
- point routes back at these CRUD pages (faster, different look).

### 7.5 Empty states

Every list: `v-if="!items.length"` → “No tasks yet” (or equivalent). Seeded data should fill the main path; empty state is the fallback after `migrate:fresh` without seed.

### 7.6 Suggested implementation order

1. Postgres + `migrate:fresh`
2. `ProjectSeeder` + `UserSeeder` + **Project CRUD** (list/create/show)
3. Tasks (list + kanban) — highest value
4. Risks, lessons, documents, chat
5. Resources / time / milestones
6. QA + dashboard aggregates
7. Agile / initiation **if** you add tables

One module per PR: seeder rows → Vue props → form POST/PUT/DELETE → feature test.

---

## 8. Verification checklist

```bash
php artisan config:show database.default    # pgsql
php artisan migrate:status
php artisan db:show
php artisan test --compact                  # still sqlite memory
```

pgAdmin: tables + seeded rows.

Browser:

- `/projects` lists seeded names, not only “Website Redesign” from a Vue `ref`
- Create project → row appears in pgAdmin `projects`
- `/tasks` matches `tasks` table; New Task persists after refresh
- `/chat` messages persist after refresh
- `migrate:fresh --seed` then `/tasks` is empty-of-mocks: only seeded rows

If a page still shows John Doe after truncating `users` in pgAdmin, that page is **still mock**.

---

## 9. Common pitfalls

| Pitfall | Fix |
|---|---|
| `could not find driver` | Enable `pdo_pgsql` |
| Unique `users.email` on seed | `firstOrCreate` |
| Vue `in-progress` vs DB `in_progress` | Use DB enums in filters/badges |
| Changelog UI ≠ changelog table | Pick one domain |
| AppLayout missing | `import AppLayout` inside `<script setup>` |
| File uploads 404 | `php artisan storage:link` |
| Tests hitting Postgres | Keep `phpunit.xml` on sqlite `:memory:` |
| `SESSION_DRIVER=database` | `sessions` table comes from default Laravel migrations; migrate them too |

---

## 10. Out of scope (unless product asks)

- Auth / login (web routes are currently public)
- Hosting Postgres in Docker (optional later: `postgres:16` + port 5432)
- Migrating existing SQLite bytes
- Replacing Inertia with a separate API

---

## Quick command card

```bash
# one-time
composer install && npm install
# .env → pgsql + credentials
php artisan config:clear
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
npm run dev

# after seeder/page work
php artisan db:seed
php artisan test --compact tests/Feature/CoreModulesTest.php
```
