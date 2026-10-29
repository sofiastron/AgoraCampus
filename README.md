# AgoraCampus

AgoraCampus is a university web platform that brings academic information and student services into one place. It provides role-oriented interfaces for learning resources, course modules, schedules, attendance, announcements, notifications, and profile management.

## Project structure

```text
AgoraCampus/
           frontend/    # Vue 3 single-page application
           backend/     # Laravel REST API
```

## Technology stack

| Area | Technologies |
| --- | --- |
| Frontend | Vue 3, Vite, Vue Router, Pinia, Axios |
| UI features | FullCalendar, Chart.js, QR-code support |
| Backend | PHP, Laravel 8, Laravel Sanctum |
| Data | MySQL |
| Quality | PHPUnit and functional/API test scenarios; Robot Framework automation contribution |

## Key capabilities

- Authentication, password recovery, and authenticated profile management.
- Student dashboard with academic information and notifications.
- Course modules, sessions, documents, and announcements.
- Calendar and attendance workflows.
- Frontend and backend separated through REST APIs.

## Development & QA contribution

This project includes both development and quality-assurance work:

- Delivered and improved application features across the Vue interface and Laravel REST API.
- Designed and executed functional test scenarios for essential academic workflows.
- Validated API behavior, authentication flows, and error handling.
- Contributed automated end-to-end checks with Robot Framework as part of the QA workflow.

## Run locally

### Prerequisites

- PHP 8+ and Composer
- MySQL
- Node.js `20.19+` or `22.12+`

### Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure the MySQL connection in `backend/.env`, then run:

```bash
php artisan migrate
php artisan serve
```

The API is available on the address displayed by Laravel (commonly `http://127.0.0.1:8000`).

### Frontend

Open a second terminal:

```bash
cd frontend
npm install
npm run dev
```

Vite displays the local URL to open in a browser. Ensure that the frontend API configuration targets the local Laravel server.

## API overview

The Laravel API exposes authentication endpoints and authenticated student routes, including:

- `POST /api/register`, `POST /api/login`
- Password recovery and reset
- `/api/etudiant/dashboard`
- Modules, sessions, calendar, documents, announcements, attendance, and notifications

Authenticated routes use Laravel Sanctum.

## Repository hygiene

Do not commit `.env`, generated dependencies, API keys, or local database credentials. Use `.env.example` as the starting point for local configuration.

## Documentation

For architecture, local setup details, API and QA guidance, see [Development & QA Documentation](docs/DEVELOPMENT_AND_QA.md).

