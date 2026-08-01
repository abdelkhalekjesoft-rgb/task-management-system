# Task Management System API

A RESTful Laravel API for managing user-owned projects and tasks. The API uses Laravel Sanctum for token authentication, Eloquent relationships, resource responses, form request validation, soft deletes, factories, seeders, and feature tests.

## Requirements

- PHP 8.3+
- Composer
- MySQL
- Node.js and npm

## Installation

Clone the repository and install dependencies:

```bash
git clone https://github.com/abdelkhalekjesoft-rgb/task-management-system.git
cd task-management-system
composer install
npm install
```

Create the environment file and application key:

```bash
cp .env.example .env
php artisan key:generate
```

Create a local MySQL database named `task`, then confirm these values in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed sample data:

```bash
php artisan migrate --seed
```

Start the API server:

```bash
php artisan serve
```

The API will be available at:

```text
http://127.0.0.1:8000/api
```

## Demo Account

The database seeder creates a demo user:

```text
email: test@example.com
password: password
```

## Testing

Run the automated test suite:

```bash
php artisan test
```

Run Laravel Pint formatting:

```bash
./vendor/bin/pint
```

## Postman Collection

Import the Postman collection and local environment from:

```text
docs/postman_collection.json
docs/postman_environment.json
```

Select the `Task Management System API - Local` environment in Postman. It includes:

```text
base_url=http://127.0.0.1:8000/api
token=
project_id=1
task_id=1
```

Run the `Login` request first; it stores the returned Sanctum token in `token` for authenticated requests.

## Authentication

Send JSON requests with:

```text
Accept: application/json
Content-Type: application/json
```

Protected endpoints require:

```text
Authorization: Bearer YOUR_TOKEN
```

### Register

```http
POST /api/register
```

Request:

```json
{
  "name": "Ahmed Abdelkhalek",
  "email": "ahmed@example.com",
  "password": "password",
  "password_confirmation": "password"
}
```

### Login

```http
POST /api/login
```

Request:

```json
{
  "email": "test@example.com",
  "password": "password"
}
```

### Logout

```http
POST /api/logout
```

Requires authentication.

## Response Format

Successful responses use a consistent envelope:

```json
{
  "success": true,
  "message": "Success message.",
  "data": {}
}
```

Paginated list responses also include `links` and `meta`.

Validation errors use Laravel's default JSON validation format with HTTP `422`.

## Projects API

All project endpoints require authentication. Users can only access their own projects.

### List Projects

```http
GET /api/projects
```

### Create Project

```http
POST /api/projects
```

Request:

```json
{
  "name": "API Development",
  "description": "Build and document the task management REST API.",
  "status": "active"
}
```

Allowed statuses:

```text
active, completed, archived
```

### View Project

```http
GET /api/projects/{project}
```

### Update Project

```http
PUT /api/projects/{project}
PATCH /api/projects/{project}
```

### Delete Project

```http
DELETE /api/projects/{project}
```

Projects are soft deleted.

## Tasks API

All task endpoints require authentication. Tasks belong to projects, and project ownership is enforced.

### List Tasks

```http
GET /api/projects/{project}/tasks
```

Supported filters:

```text
status=todo|in_progress|done
priority=low|medium|high
search=title text
```

Example:

```http
GET /api/projects/1/tasks?status=todo&priority=high&search=api
```

### Create Task

```http
POST /api/projects/{project}/tasks
```

Request:

```json
{
  "title": "Implement task filters",
  "description": "Support filtering tasks by status, priority, and title search.",
  "priority": "high",
  "status": "todo",
  "due_date": "2026-08-10"
}
```

Allowed priorities:

```text
low, medium, high
```

Allowed statuses:

```text
todo, in_progress, done
```

### Update Task

```http
PUT /api/tasks/{task}
PATCH /api/tasks/{task}
```

### Delete Task

```http
DELETE /api/tasks/{task}
```

Tasks are soft deleted.

## Dashboard API

```http
GET /api/dashboard
```

Requires authentication.

Returns:

```json
{
  "success": true,
  "message": "Dashboard statistics retrieved successfully.",
  "data": {
    "total_projects": 3,
    "active_projects": 1,
    "total_tasks": 10,
    "completed_tasks": 5,
    "pending_tasks": 5,
    "overdue_tasks": 1
  }
}
```

## Main Project Structure

```text
app/
├── Enums/
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Policies/
└── Services/
```

## Notes

- Authentication is implemented with Laravel Sanctum.
- Projects and tasks use soft deletes.
- Project and task access is protected by policies.
- API list endpoints use pagination.
- Seeders provide realistic sample users, projects, and tasks.
