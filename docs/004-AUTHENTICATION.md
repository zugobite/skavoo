# Authentication & Sessions

This doc explains Skavoo’s authentication model and related security primitives.

## Authentication Model

Skavoo uses **session-based authentication**.

On successful login, the app stores user data in `$_SESSION`, including:

- `user_id`
- `user_uuid`
- `user_email`
- `display_name`
- `profile_picture`
- `full_name`

Protected routes check for `$_SESSION['user_id']`.

## Auth Middleware

File: `app/Middleware/AuthMiddleware.php`

- `AuthMiddleware::handle()` ensures the session is started and redirects to `/login` if the user is not authenticated.

Usage pattern in controllers:

- Call `AuthMiddleware::handle()` at the start of any protected action.

## Login Flow

- `GET /login` renders the login view
- `POST /login`:
  1. Reads `email` + `password`
  2. Loads the user row from `users` by email
  3. Verifies password via `password_verify()`
  4. Sets session variables
  5. Redirects to `/feed`

## Registration Flow

- `GET /register` renders the registration view
- `POST /register`:
  1. Validates required fields
  2. Checks email uniqueness
  3. Optionally stores avatar in `public/uploads/avatars/` and saves a web path like `/uploads/avatars/{file}`
  4. Hashes password via `password_hash()`
  5. Inserts into `users`

## Logout

- `GET /logout` destroys the session and redirects.

## Password Reset

Password reset is token-based.

### Storage

- Table: `password_resets`
- Fields:
  - `email`
  - `token`
  - `expires_at`

### Flow

1. User submits `POST /forgot-password` with `email`
2. App generates a secure token and stores it with an expiry time
3. App “sends” email using the file-based mailer (writes `.eml` into `storage/mail/` by default)
4. User opens `GET /reset-password?token=...`
5. User submits `POST /reset-password` with token + new password
6. Token is validated and the user password is updated

## CSRF

Skavoo uses a per-session CSRF token.

### Implementation

File: `app/Helpers/Csrf.php`

- `Csrf::token()` generates token and stores in `$_SESSION['csrf']`
- `Csrf::field()` returns `<input type="hidden" name="csrf" ...>`
- `Csrf::verifyOrFail()` checks `$_POST['csrf']` and returns HTTP 419 on mismatch

### Rule of thumb

- Every POST form should include `Csrf::field()` (or `csrf_field()`)
- Every POST handler should call `Csrf::verifyOrFail()` early

## Session Notes (Development)

- Controllers usually guard `session_start()` using `session_status()`.
- If state looks “stuck”, clear browser cookies or restart the browser.

## Hardening Ideas (Optional)

These are not required to run Skavoo, but are common production hardening steps:

- Set session cookie flags: `HttpOnly`, `Secure` (if HTTPS), and `SameSite=Lax/Strict`
- Rotate session IDs on login (`session_regenerate_id(true)`)
- Add rate limiting on login and password reset endpoints
