# AgoraCampus â€” Development & QA Documentation

## 1. Purpose

AgoraCampus is a university-oriented web application for centralizing academic services. The application supports authenticated user workflows around academic dashboards, modules, sessions, calendars, attendance, documents, announcements, notifications, and profile management.

The repository follows a separated frontend/backend design: a Vue single-page application consumes a Laravel REST API.

## 2. Architecture

```text
Browser
  â”‚
  â–¼
Vue 3 + Vite frontend
  â”‚  HTTP requests through Axios
  â–¼
Laravel REST API
  â”‚  Laravel Sanctum authentication
  â–¼
MySQL database
```

### Frontend

Location: `frontend/`

- Vue 3 is used to build the user interface.
- Vite provides the local development server and production build.
- Vue Router manages client-side navigation.
- Pinia manages client-side state.
- Axios communicates with the REST API.
- FullCalendar and Chart.js support calendar and dashboard features.

### Backend

Location: `backend/`

- Laravel 8 exposes REST endpoints.
- Laravel Sanctum protects authenticated routes.
- MySQL stores application data.
- Controllers cover authentication, student dashboard data, modules, sessions, attendance, documents, announcements, notifications, and profile actions.

## 3. Prerequisites

- Node.js `20.19+` or `22.12+`
- npm
- PHP 8+ and Composer
- MySQL

## 4. Local installation

### 4.1 Start the backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database variables in `backend/.env`. Create an empty local MySQL database, then run:

```bash
php artisan migrate
php artisan serve
```

Laravel displays the API URL in the terminal; it is commonly `http://127.0.0.1:8000`.

### 4.2 Start the frontend

Open a second terminal:

```bash
cd frontend
npm install
npm run dev
```

Open the Vite URL shown in the terminal. The frontend must be configured to target the URL of the local Laravel API.

### 4.3 Production frontend build

```bash
cd frontend
npm run build
```

The generated static files are placed in `frontend/dist/`.

## 5. API overview

The API routes are defined in `backend/routes/api.php`.

### Public authentication routes

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/api/register` | Create an account |
| POST | `/api/login` | Authenticate a user |
| POST | `/api/forgot-password` | Request a password-reset link |
| POST | `/api/reset-password` | Reset a password |

### Authenticated student routes

The `/api/etudiant/*` routes require Laravel Sanctum authentication. They include profile, dashboard, modules, sessions, calendar, attendance, documents, announcements, and notifications resources.

Examples:

- `GET /api/etudiant/dashboard`
- `GET /api/etudiant/modules`
- `GET /api/etudiant/calendar`
- `GET /api/etudiant/notifications`

## 6. Development guidelines

- Keep frontend and backend changes focused and independently runnable.
- Put API validation and authorization rules in the Laravel layer; do not rely only on UI restrictions.
- Use environment variables for local URLs, secrets, mail settings, and database credentials.
- Do not commit `.env` files, keys, database exports, or generated dependency directories such as `node_modules/`.
- Run the frontend build and relevant backend tests before opening a pull request.

## 7. Quality assurance

### Functional and API validation

Essential workflows to validate include:

- Login, logout, password recovery, and password reset.
- Access control for authenticated API routes.
- Dashboard loading and error states.
- Viewing modules, sessions, documents, announcements, and notifications.
- Calendar and attendance actions.
- Profile and password update validation.

### Backend tests

Laravel includes PHPUnit as a development dependency. After configuring the local environment, run the available backend suite with:

```bash
cd backend
php artisan test
```

### Robot Framework automation

Robot Framework is used as part of the QA contribution for automated end-to-end scenarios. Keep its test data and credentials outside the repository, configure the target URL through variables, and run the suite only against an approved environment. Typical automated coverage should focus on stable user journeys such as authentication and protected dashboard access.

## 8. Security checklist

- Protect non-public endpoints with Sanctum middleware.
- Validate request payloads on the server.
- Keep database, mail, and application secrets in local environment files.
- Test unauthorized and expired-session behavior.
- Use separate non-production accounts for automated QA.

## 9. Troubleshooting

| Symptom | Check |
| --- | --- |
| Frontend cannot reach the API | Confirm the Laravel server URL, frontend API configuration, and CORS settings. |
| Database connection fails | Check MySQL is running and verify the `DB_*` values in `backend/.env`. |
| `php artisan` fails | Run `composer install`, then confirm `.env` and the application key are configured. |
| Vite does not start | Confirm the supported Node.js version, run `npm install`, then retry `npm run dev`. |
| Protected route returns 401 | Authenticate first and verify that the Sanctum token/session is sent with the request. |

## 10. Contribution workflow

1. Create a focused branch from `main`.
2. Implement the change with no secrets or generated dependencies committed.
3. Run relevant frontend builds and backend/QA checks.
4. Describe functional and QA impact in the pull request.
5. Request review before merging.
