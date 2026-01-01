# Skavoo — Developer Documentation

Welcome to the Skavoo developer docs.

Skavoo is a vanilla PHP social media platform showcasing a simple MVC-ish structure (controllers + views + helpers), a custom router, session-based authentication, and core social features (feed, posts, friends, messaging, notifications).

## Who These Docs Are For

- Developers onboarding to the codebase
- Contributors adding new features or endpoints
- Reviewers wanting to understand architecture/security

## Documentation Map

- **Project overview (this doc)**
- [Getting started](002-GETTING_STARTED.md)
- [API reference](003-API_REFERENCE.md)
- [Authentication & sessions](004-AUTHENTICATION.md)
- [Database schema](005-DATABASE_SCHEMA.md)
- [Testing](006-TESTING.md)
- [Deployment](007-DEPLOYMENT.md)
- [Monitoring](008-MONITORING.md)
- [Scaling](009-SCALING.md)

## High-Level Architecture

### Request Lifecycle

1. Web server routes requests to `public/index.php` (front controller)
2. `public/index.php` bootstraps:
   - router (`app/Core/Router.php`)
   - helper shims (`app/Support/global_helpers.php`)
   - namespace aliases (`app/Support/namespace_aliases.php`)
   - helper functions (`app/Helpers/Functions.php`)
3. Routes are registered via `routes/web.php`
4. `Router::dispatch()` matches the current request method + path and invokes the mapped controller action

### Code Organization

- `public/` — entry point and static assets
- `routes/` — route registration
- `app/Controllers/` — request handlers (business logic)
- `app/Views/` — server-rendered HTML templates
- `app/Helpers/` — shared utilities (DB, CSRF, env, mail, escape helpers)
- `app/Middleware/` — Auth middleware
- `config/` — PDO setup
- `database/` — schema migration SQL + seeder

### Router Design

The router supports `{param}` placeholders in paths. Internally it converts routes to a regex and captures values:

- Route example: `/posts/{id}/like`
- Pattern used: `{param}` → `([a-zA-Z0-9_]+)`

The router dispatches `"Controller@method"` strings by requiring `app/Controllers/{Controller}.php` and instantiating either:

- `App\Controllers\{Controller}` if present
- `{Controller}` (global namespace) as a fallback

### Namespaces and Compatibility

Some controllers/helpers are namespaced (`App\Controllers`, `App\Helpers`) while others are not. The project includes `app/Support/namespace_aliases.php` to smooth over name differences using `class_alias()`.

## Core Features (Developer View)

- **Auth**: registration, login/logout, password reset via token
- **Feed**: visibility-aware posts (public/friends/private)
- **Posts**: create posts with optional media upload; like/unlike; comments; delete
- **Friends**: request/accept/reject/cancel/remove
- **Messages**: 1:1 messaging threads with unread tracking
- **Notifications**: JSON API endpoints to retrieve and mark-as-read
- **Search**: user lookup endpoint returning JSON

## Security Highlights

- Password hashing via `password_hash()` / `password_verify()`
- SQL injection prevention via PDO prepared statements
- CSRF protection via session token + form field + verification
- Basic XSS prevention via escape helpers
- Upload validation for post media (extension allow-list + size limit)

See [Authentication](004-AUTHENTICATION.md) and [Security](003-API_REFERENCE.md#security-notes) notes for more detail.

## Conventions

- Controllers typically start a session in `__construct()` (guarded by `session_status()`)
- Protected actions call `AuthMiddleware::handle()`
- State-changing POST routes should call CSRF verification (`Csrf::verifyOrFail()`)
- UUIDs are used in URLs for user-facing identifiers (profiles, message threads)

## Where to Start

- New to the project? Read [Getting started](002-GETTING_STARTED.md)
- Adding/adjusting endpoints? Use [API reference](003-API_REFERENCE.md)
- Working on auth? Read [Authentication & sessions](004-AUTHENTICATION.md)
- Modifying schema? Read [Database schema](005-DATABASE_SCHEMA.md)
