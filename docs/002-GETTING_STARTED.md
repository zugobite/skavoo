# Getting Started

This guide gets Skavoo running locally for development.

## Requirements

- PHP 8.0+
- MySQL 5.7+
- A web server (Apache/Nginx) or PHP built-in server

## Quick Setup

### 1) Clone and configure environment

```bash
git clone https://github.com/zugobite/skavoo.git
cd skavoo

cp .env.example .env
```

Edit `.env` and set:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Optional but recommended:

- `APP_URL` (used by password reset email generation)

### 2) Create database and schema

Create the database and run the SQL migration.

```bash
mysql -u root -p -e "CREATE DATABASE social_db;"
mysql -u root -p social_db < database/migrations/create_tables.sql
```

### 3) (Optional) Seed sample data

```bash
php database/seeder.php
```

Default sample users:

- `alice@example.com` / `password123`
- `bob@example.com` / `password123`
- `charlie@example.com` / `password123`

### 4) Run the app

Skavoo uses `public/index.php` as the front controller.

```bash
cd public
php -S localhost:8000
```

Open:

- `http://localhost:8000`

## Folder Orientation

- `public/index.php` — entry point
- `routes/web.php` — route registration
- `app/Core/Router.php` — dispatch + controller invocation
- `app/Controllers/` — controllers
- `app/Views/` — templates
- `app/Helpers/` — DB/CSRF/env/mail/utilities
- `config/database.php` — PDO initialization using `.env`
- `database/migrations/create_tables.sql` — schema

## Common Dev Tasks

### Running password reset locally

Skavoo’s mailer is file-based for local development.

- Emails are written as `.eml` files in `storage/mail/` by default.
- Configure with `MAIL_LOG_DIR` if you want a custom location.

### Clearing sessions

If you run into unexpected auth state during development, clear the session cookie in your browser, or restart the browser.

### Upload directories

- Avatars: `public/uploads/avatars/`
- Post media: `public/uploads/posts/`

Ensure these are writable by your web server/PHP process.

## Troubleshooting

### DB connection fails

- Confirm `.env` exists at repo root
- Confirm `.env` values match your MySQL credentials
- Check `config/database.php` is reachable and loads `app/Helpers/env.php`

### 404 on all routes

Make sure you are serving from the `public/` directory (the router expects that structure).

### CSRF token mismatch (HTTP 419)

- Ensure your form includes a CSRF hidden field
- Ensure cookies/sessions are enabled

See [Authentication & sessions](004-AUTHENTICATION.md#csrf) for details.
