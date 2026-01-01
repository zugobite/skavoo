# Scaling

This doc captures practical scaling considerations for Skavoo.

Skavoo is currently a single-node, server-rendered PHP application using a MySQL database.

## Primary Bottlenecks

- Database read load (feed, likes/comments counts, messaging)
- Media storage (uploads)
- Notification polling (periodic JSON requests)

## Database Scaling

### Indexing

Add/verify indexes on:

- `users.email` (already unique)
- `users.uuid` (unique)
- `posts.user_id`, `posts.created_at`
- `likes.post_id`, `likes.user_id` (unique constraint helps)
- `comments.post_id`
- `messages.sender_id`, `messages.receiver_id`, `messages.created_at`
- `friends.sender_id`, `friends.receiver_id`
- `notifications.user_id`, `notifications.is_read`, `notifications.created_at`

### Query patterns

- Keep feed queries selective (visibility + friendship constraints)
- Avoid N+1 patterns when possible (batch-fetch counts)

## Caching

Simple caching opportunities:

- Notification unread count per user
- “people you may know” suggestions
- Profile summaries

A conservative approach is to introduce a cache layer only after measuring slow queries.

## Media Storage

Uploads are stored on the local filesystem under `public/uploads/*`.

To scale beyond one server:

- Move uploads to object storage (S3-compatible)
- Store only the object key/path in the DB

## Horizontal Scaling

If you run multiple PHP instances:

- Sessions must be shared (Redis, database-backed sessions) or sticky sessions must be used
- Upload storage must be shared (object storage or shared volume)

## Notifications

Polling is simple but can create load at scale.

If needed later:

- Reduce polling frequency
- Add conditional requests / caching
- Move to WebSockets or SSE

## Security and Scaling

As you scale, add:

- rate limiting
- bot protection for auth flows
- WAF rules around uploads
