# نقابة أطباء الأسنان – فرع كربلاء المقدسة

Official portal for the Karbala Dental Association, built with **Laravel 13**, a **Blade** front-end, and a **Filament 5** admin dashboard.

## Requirements

- PHP 8.2+ (developed on 8.4)
- Composer 2
- Node.js 20+ / npm
- MySQL 8 / MariaDB (production). SQLite works out of the box for local development.

## Installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate

# create the MySQL database named in .env (karbala_dental), then:
php artisan migrate --seed
php artisan storage:link

npm run build      # or: npm run dev
php artisan serve
```

The public site is served at `/`, the admin dashboard at `/admin`.

### Database

The project runs on **MySQL / MariaDB** (`.env` → `DB_CONNECTION=mysql`,
database `karbala_dental`, utf8mb4). It was verified locally against XAMPP's
MariaDB 10.4 on `127.0.0.1:3306` (user `root`, empty password — the XAMPP
default). Any MySQL 8 / MariaDB 10.4+ server works; just point the `DB_*`
values in `.env` at it and run:

```bash
mysql -u root -e "CREATE DATABASE karbala_dental CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --seed
```

The automated test suite uses an in-memory SQLite connection (see `phpunit.xml`),
so `php artisan test` never touches the MySQL database.

### Admin login (from the seeder)

| Email | Password |
|-------|----------|
| `admin@karbala-dental.iq` | `password` |

> Change this immediately in any real deployment.

## Architecture

### Front-end (Blade)

- `resources/views/components/layouts/site.blade.php` — the RTL layout (header, footer, asset loading).
- `resources/views/components/site/*.blade.php` — one component per homepage section.
- `resources/views/home.blade.php` — composes the sections; `HomeController` provides the data.
- Styling and behaviour are the original template's `site.css` / `site.js`, bundled through Vite.

Sections split into two kinds:

- **Static** (hero, about, statistics, apply, transaction-search, partners, social, contact) — fixed markup.
- **Dynamic** (news, board, courses, events, regulations, discounts) — rendered from the database, and hidden automatically when empty.

Every homepage section also has its own dedicated page, so the nav and footer
link to real routes rather than page anchors. Section components take a
`:heading="false"` prop so a standalone page can show the page-header banner
instead of the component's inner heading — no markup duplication.

| Page | Route |
|------|-------|
| About / Board / Regulations / Discounts / Apply / Search / Contact / Complaint | `PageController` |
| News list + detail | `/news`, `/news/{slug}` |
| Courses list + detail | `/courses`, `/courses/{slug}` |
| Events list + detail | `/events`, `/events/{event}` |

The event **detail** page reuses the homepage `featured-event` component, so its
countdown, "add to calendar", and registration modal work identically there.

### Admin dashboard (Filament)

Resources live under `app/Filament/Resources`, grouped in the sidebar:

- **المحتوى** — News, Announcements, Courses, Events
- **النقابة** — Board members, Regulation types, Discounts
- **الوارد** (read-only inboxes) — Complaints, Event registrations

The two inboxes cannot be created by hand (they arrive from the public forms) and show an unread badge in the sidebar.

### Public form submissions

| Form | Route | Persists to |
|------|-------|-------------|
| Complaint | `POST /complaints` | `complaints` |
| Event registration | `POST /events/{event}/register` | `event_registrations` |

Both are validated server-side (`app/Http/Requests`), rate-limited, and confirm with a success banner. The transaction-search, contact, and newsletter forms remain client-side only, matching the original template.

## Testing

```bash
php artisan test          # feature tests
./vendor/bin/pint         # code style (PSR-12)
```

## The original template

The static HTML/CSS/JS this project was converted from is preserved under `bootstrap-site/` for reference.
