## Minutor API

Meeting scheduling and collaboration backend built with Laravel and Sanctum.

## Tech Stack
- PHP (Laravel)
- Laravel Sanctum (API auth)
- PostgreSQL (or compatible)
- Pest/PHPUnit (tests)

## Setup
1) Copy env and configure DB
- Duplicate .env.example → .env
- Set DB credentials and APP_URL

2) Install dependencies
- composer install
- npm install (if frontend assets are used)

3) App key, migrations, seed
- php artisan key:generate
- php artisan migrate

4) Run
- php artisan serve

## Docker
- Build and run API, queue, scheduler, and Redis:
	- docker compose up --build
- If you also want a local PostgreSQL container, enable the `local-db` profile:
	- docker compose --profile local-db up --build

Notes:
- `api`, `queue`, and `scheduler` are built from the repository `Dockerfile`.
- The app source is bind-mounted for local development (`./:/app`).

## Auth
- Login: POST /api/auth/login (email, password)
- Logout: POST /api/auth/logout
- Current user: GET /api/user
- Sanctum bearer token must be sent with authenticated requests.

## Roles & Permissions
- Admin: can manage users, rooms, features; delete any user; manage attendees for any meeting.
- Scheduler (meeting owner): can update/delete their meetings, manage their attendees, create action items on their meetings.
- Attendee: can join/leave meetings.

## Data Model (high-level)
- users: is_admin, is_active
- rooms, features, feature_room (pivot)
- meetings: date, start_time, end_time, status, scheduled_by, room_id
- meeting_user: pivot with status (invited/accepted/...)
- agendas, agenda_topics
- mom_entries (MoM)
- action_items: linked to mom_entries; assigned_to nullable (Everyone)
- notifications
- comments: user_id, meeting_id, text

## API Reference (selected)

Auth
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/user

Users
- GET /api/users
- GET /api/users/{id}
- POST /api/users
- PUT/PATCH /api/users/{id}
- DELETE /api/users/{id}
- PUT /api/users/{id}/lock
- PUT /api/users/{id}/unlock

Rooms
- GET /api/rooms
- GET /api/rooms/{id}
- GET /api/rooms/available
- (Admin) POST/PUT/PATCH/DELETE /api/rooms

Features
- GET /api/features
- GET /api/features/{id}
- (Admin) POST/PUT/PATCH/DELETE /api/features
- (Admin) POST /api/features/{id}/attach-room
- (Admin) POST /api/features/{id}/detach-room

Meetings
- GET /api/meetings
- POST /api/meetings
- GET /api/meetings/{id}
- PUT/PATCH /api/meetings/{id}
- DELETE /api/meetings/{id}
- GET /api/meetings/my
- GET /api/meetings/upcoming
- GET /api/meetings/past
- POST /api/meetings/{id}/join
- POST /api/meetings/{id}/leave
- POST /api/meetings/{id}/attendees
- DELETE /api/meetings/{id}/attendees
- PATCH /api/meetings/{id}/status
- POST /api/meetings/{id}/attendee

Agendas
- GET /api/meetings/{meetingId}/agenda
- POST /api/meetings/{meetingId}/agenda
- CRUD /api/agendas

Agenda Topics
- GET /api/agenda-topics/my
- GET /api/agendas/{agendaId}/topics
- POST /api/agendas/{agendaId}/topics
- POST /api/agendas/{agendaId}/topics/reorder
- PATCH /api/agenda-topics/{id}/assign
- GET /api/agenda-topics/{id}
- PUT /api/agenda-topics/{id}
- DELETE /api/agenda-topics/{id}

Minutes of Meeting (MoM) & Action Items
- On meeting creation, an empty MoM entry is created.
- Action Items attach to a provided MoM entry (mom_entry_id) and can be assigned to a user or Everyone (null).

Action Items
- GET/POST/GET{id}/PUT/PATCH/DELETE /api/action-items
- PATCH /api/action-items/{id}/status
- PATCH /api/action-items/{id}/assign

Comments
- GET /api/meetings/{meetingId}/comments
- POST /api/meetings/{meetingId}/comments
- PUT /api/comments/{id}
- DELETE /api/comments/{id}

## Conventions
- All times in 24h format (H:i) and dates as YYYY-MM-DD.
- Status enums include scheduled, in_progress, completed, cancelled (meetings) and open, in_progress, completed, cancelled (action items).

## Testing
- php artisan test

## Troubleshooting
- Migration class errors: ensure duplicate stub migrations are no-ops.
- DB connection: verify .env and run migrations.
