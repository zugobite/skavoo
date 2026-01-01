# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Added

- Nothing yet

### Changed

- Nothing yet

### Fixed

- Nothing yet

---

## [1.1.0] - 2026-01-01

### Added

- Comprehensive developer documentation in `docs/` folder:
  - `001-PROJECT_OVERVIEW.md` - Architecture and codebase overview
  - `002-GETTING_STARTED.md` - Local setup and configuration guide
  - `003-API_REFERENCE.md` - Complete route and endpoint documentation
  - `004-AUTHENTICATION.md` - Auth flow, sessions, CSRF, and password reset
  - `005-DATABASE_SCHEMA.md` - Database tables, relationships, and seeding
  - `006-TESTING.md` - Manual test checklist and automated testing guidance
  - `007-DEPLOYMENT.md` - Production deployment instructions
  - `008-MONITORING.md` - Logging, health checks, and alerting
  - `009-SCALING.md` - Performance optimization and horizontal scaling
- Documentation link section in README.md

### Changed

- Updated README.md with Documentation section and project structure
- Updated CHANGELOG.md with full commit history

---

## [1.0.0] - 2026-01-01

### Added

#### Authentication System

- User registration with full name, email, password, and optional avatar
- Secure login with email and password
- Password hashing using `password_hash()` with `PASSWORD_DEFAULT`
- Forgot password functionality with email-based reset tokens
- Password reset with secure token validation
- Session-based authentication using `$_SESSION`
- Logout functionality with session destruction

#### User Profiles

- View user profiles with avatar, name, and post history
- Edit profile functionality (display name and avatar)
- Public profile viewing for other users
- Avatar upload with image validation

#### Social Feed

- Personalized feed showing posts from friends
- Create posts with text and optional image attachments
- Delete own posts
- Chronological post ordering (newest first)
- Post timestamps with relative time display

#### Social Interactions

- Like/unlike posts with toggle functionality
- Comment on posts
- Delete own comments
- Real-time like and comment counts

#### Friend System

- Send friend requests to other users
- Accept or reject incoming friend requests
- Cancel outgoing friend requests
- Remove existing friends
- View friends list
- View pending friend requests

#### Private Messaging

- One-to-one private messaging
- Conversation threads grouped by user
- Message inbox with conversation previews
- Real-time message sending
- Message timestamps

#### Notifications

- Notification system for social interactions
- Mark notifications as read
- Notification API endpoints

#### Search

- User search by name or email
- Live search with JavaScript

#### Security Features

- SQL injection prevention via PDO prepared statements
- CSRF token protection on all forms
- Input validation and sanitization
- XSS prevention through output escaping
- Secure file upload handling
- Authentication middleware for protected routes

#### Developer Experience

- Custom MVC-style architecture
- Clean routing system with GET/POST support
- Environment-based configuration via `.env`
- PHPDoc comments throughout codebase
- Organized folder structure

### Changed

- Project restructured for open-source release
- Documentation updated for portfolio presentation
- License changed to MIT for open-source compatibility

### Removed

- All academic/institutional references
- Restrictive academic license

---

## [0.1.1] - 2025-08-02

### Added

- `.env` file support for secure environment configuration
- `Helpers/env.php` to load environment variables
- PHPDoc comments for env loader and database connection

### Changed

- Updated `config/database.php` to use `getenv()` for credentials
- Moved hardcoded DB credentials out of source code

---

## [0.1.0] - 2025-08-02

### Added

- Initial project structure and boilerplate
- `public/index.php` as front controller
- Custom `Router` class for request handling
- `routes/web.php` with initial route definitions
- `AuthController` with login stubs
- `app/Views/auth/login.php` with HTML form
- `config/database.php` with PDO MySQL connection
- Initial documentation files

---

---

## Commit History

Full commit history for transparency and reference:

| Commit | Description |
| ------ | ----------- |
| `89991dd` | chore: updated README.md file |
| `c7a92fc` | chore: prepare v1.0.0 for open-source release |
| `051b0f1` | fix: updated ui for the edit page |
| `195df0a` | feature: added more details to profiles |
| `217f8e5` | fix: improved ui |
| `eff9235` | fix: updated ui |
| `64a022c` | fix: improved the ui and implemented friend requests |
| `d547b97` | chore: pixel pushing |
| `17dfa85` | fix: updated messages functionality |
| `01d1d58` | fix: edit profile functionality |
| `ea6b471` | feature: added php migrate runner |
| `d943d16` | fix: update errors in messages & edit profile |
| `b3a2cb5` | Update index.php |
| `bb78363` | feature: edit profile & messaging |
| `ffd2dc9` | feature: implement password reset email |
| `0d60382` | added forgot password scaffolding |
| `ba1940b` | Update index.php |
| `4ae5025` | feature: made minor ui edits |
| `551c6ca` | chore: combined all css files into 1 file |
| `7721849` | feature: changing password reset functionality to send email |
| `2d7d494` | feature: updated general ui & implemented search functionality |
| `71fc4db` | feature: updated general ui styling & implemented feed page |
| `3dfa824` | feature: implemented login, registration, password reset, posts, profiles |
| `b587c8d` | chore: updated changelog.md and readme.md files |
| `a0a0267` | feat: secure DB credentials using .env and enhanced config |
| `f7669e7` | chore: added db entity relationship diagram |
| `e8995a9` | chore: added .sql file to create db tables |
| `f84f5f0` | initial commit - project structure, routing system and login setup |

---

[Unreleased]: https://github.com/zugobite/skavoo/compare/v1.1.0...HEAD
[1.1.0]: https://github.com/zugobite/skavoo/compare/v1.0.0...v1.1.0
[1.0.0]: https://github.com/zugobite/skavoo/compare/v0.1.1...v1.0.0
[0.1.1]: https://github.com/zugobite/skavoo/compare/v0.1.0...v0.1.1
[0.1.0]: https://github.com/zugobite/skavoo/releases/tag/v0.1.0
