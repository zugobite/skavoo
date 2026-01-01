# Database Schema

Skavoo uses MySQL and PDO.

The schema is defined in:

- `database/migrations/create_tables.sql`

## Setup

Create DB + apply schema:

```bash
mysql -u root -p -e "CREATE DATABASE social_db;"
mysql -u root -p social_db < database/migrations/create_tables.sql
```

## Tables

### `users`

Purpose: user accounts + profile information.

Key columns:

- `id` (PK)
- `uuid` (unique)
- `full_name`, `display_name`, `email` (unique)
- `password` (hashed)
- `profile_picture` (string path)
- profile fields: `bio`, `birthday`, `gender`, `location`, `relationship_status`, `work`, `education`, `website`, `phone`
- `created_at`, `updated_at`

### `posts`

Purpose: user posts for feed and profiles.

- `user_id` → `users.id` (FK)
- `content` (text)
- `image` (optional; path to uploaded media)
- `visibility` enum: `public|private|friends`

### `likes`

Purpose: mapping users → liked posts.

- `user_id` → `users.id`
- `post_id` → `posts.id`
- Unique constraint on `(user_id, post_id)` to prevent duplicates

### `comments`

Purpose: comments on posts.

- `user_id` → `users.id`
- `post_id` → `posts.id`
- `comment` text

### `shares`

Purpose: post shares.

- `user_id` → `users.id`
- `post_id` → `posts.id`

### `messages`

Purpose: 1:1 direct messages.

- `sender_id` → `users.id`
- `receiver_id` → `users.id`
- `message` text
- `seen` boolean

### `password_resets`

Purpose: password reset tokens.

- `email`
- `token`
- `expires_at`

### `friends`

Purpose: friend requests and friend relationships.

- `sender_id` → `users.id`
- `receiver_id` → `users.id`
- `status` enum: `pending|accepted|rejected`
- `requested_at`, `responded_at`
- Unique constraint on `(sender_id, receiver_id)`

Note: application logic checks for existing friendships “in either direction”.

### `notifications`

Purpose: activity notifications.

- `user_id` — recipient
- `actor_id` — initiator (nullable)
- `type` — string
- `content` — text (often JSON-encoded payload)
- `reference_id` — optional link to entity
- `is_read` boolean

### `audit_logs`

Purpose: a place to log security/audit events.

- `user_id` (nullable)
- `action`
- `ip_address`, `user_agent`

## Seeding

Seeder file:

- `database/seeder.php`

Run:

```bash
php database/seeder.php
```

What it seeds:

- users
- posts
- likes
- comments
- shares
- messages

## Notes on UUIDs

The schema uses `uuid CHAR(36)` for users, but the current code may generate UUID-like values using different strategies in different places. If you standardize this later, keep it consistent (ideally `bin2hex(random_bytes(16))` or a proper RFC 4122 UUID generator).
