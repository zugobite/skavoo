# Testing

This project is a vanilla PHP codebase without an automated test runner committed by default.

This document describes practical testing approaches for contributors.

## Current State

- No PHPUnit configuration is included by default.
- Most behavior is verified through manual browser testing.

## Manual Test Checklist

### Authentication

- Register a new account
- Login with correct credentials
- Verify login fails with incorrect password
- Logout and confirm protected pages redirect to `/login`

### CSRF

- Submit a POST form normally (should succeed)
- Remove/alter the CSRF token and submit (should return HTTP 419)

### Posts

- Create post (text-only)
- Create post (with allowed image/video types)
- Upload a file > 10MB (should be blocked)
- Like/unlike a post
- Comment on a post
- Delete own post

### Friends

- Send request
- Accept request
- Reject request
- Cancel outgoing request
- Remove friend

### Messaging

- Open inbox
- Send message to another user
- Verify unread count updates for receiver after refresh
- Verify messages are marked seen after opening thread

### Notifications

- Trigger notifications (friend request, likes/comments, etc.)
- Confirm `/api/notifications/count` returns expected unread count
- Mark notifications read and confirm count drops

## Adding Automated Tests (Suggested Path)

If you want to add automated tests later, the least disruptive path is:

1. Introduce PHPUnit via Composer
2. Add a small set of integration tests that boot the app and call controller actions with mocked `$_SERVER`, `$_POST`, and sessions
3. Add DB test setup using a separate test database

Keep in mind:

- The app currently relies on globals and includes, so you’ll likely want a lightweight “bootstrap for tests” wrapper.

## Smoke Tests via PHP Built-in Server

Start the app:

```bash
cd public
php -S localhost:8000
```

Then validate core routes:

- `/`
- `/login`
- `/register`
- `/feed` (should redirect when logged out)
