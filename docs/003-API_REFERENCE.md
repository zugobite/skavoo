# API Reference

Skavoo is primarily server-rendered HTML, but it still exposes a clear set of HTTP endpoints.

This document lists all routes in `routes/web.php` and notes common request/response behavior.

## Conventions

- **HTML pages** return rendered PHP views.
- **Mutating routes** are `POST` and should be CSRF-protected.
- **UUIDs** are used for user-facing identifiers.
- **Auth-required routes** require a valid session (`$_SESSION['user_id']`).

## Public Routes

### GET `/`

- Controller: `HomeController@index`
- Purpose: public landing page

### GET `/login`

- Controller: `AuthController@loginPage`
- Purpose: login form

### POST `/login`

- Controller: `AuthController@login`
- Purpose: authenticate and set session
- Form fields:
  - `email` (string)
  - `password` (string)

### GET `/register`

- Controller: `AuthController@registerPage`

### POST `/register`

- Controller: `AuthController@register`
- Form fields:
  - `full_name`
  - `display_name`
  - `email`
  - `password`
  - `profile_picture` (file, optional)

### GET `/forgot-password`

- Controller: `AuthController@forgotPasswordPage`

### POST `/forgot-password`

- Controller: `AuthController@sendResetLink`
- Form fields:
  - `email`
- Side effect:
  - creates a token in `password_resets`
  - writes a local `.eml` email via the file-based mailer

### GET `/reset-password`

- Controller: `AuthController@resetPasswordPage`
- Query:
  - `token` (required)

### POST `/reset-password`

- Controller: `AuthController@handleResetPassword`
- Form fields:
  - `token`
  - `password`
  - `confirm_password`

### GET `/logout`

- Controller: `AuthController@logout`
- Purpose: destroy session and redirect

## Protected Routes (Session Required)

### GET `/feed`

- Controller: `FeedController@index`
- Purpose: show the main feed with visibility rules applied

### User profiles

#### GET `/user/profile/{uuid}`

- Controller: `UserController@profile`
- Purpose: show a user profile

#### GET `/user/profile/{uuid}/edit`

- Controller: `UserController@editProfilePage`
- Purpose: edit form (owner-only)

#### POST `/user/profile/{uuid}/edit`

- Controller: `UserController@updateProfile`
- Purpose: update profile fields + optional avatar upload
- Form fields (common):
  - `full_name`, `display_name`, `bio`
  - `birthday`, `gender`, `location`
  - `relationship_status`, `work`, `education`
  - `website`, `phone`
  - `profile_picture` (file, optional)

### Posts

#### POST `/posts/create`

- Controller: `PostController@create`
- CSRF: required
- Form fields:
  - `content` (required)
  - `visibility` (`public|friends|private`)
  - `media` (file, optional)
- Upload constraints:
  - allow-list extensions: `jpg, jpeg, png, gif, webp, mp4, webm`
  - size limit: 10MB

#### POST `/posts/{id}/like`

- Controller: `PostController@toggleLike`
- CSRF: required
- Notes:
  - toggles like/unlike
  - may return JSON for AJAX flows

#### POST `/posts/{id}/comment`

- Controller: `PostController@addComment`
- CSRF: required
- Form fields:
  - `comment`

#### POST `/posts/{id}/delete`

- Controller: `PostController@delete`
- CSRF: required
- Authorization:
  - only post owner should be able to delete

#### POST `/comments/{id}/delete`

- Controller: `PostController@deleteComment`
- CSRF: required
- Authorization:
  - only comment owner should be able to delete

### Friends

#### GET `/friends`

- Controller: `FriendsController@index`

#### GET `/friends/requests`

- Controller: `FriendsController@requests`

#### POST `/friends/send`

- Controller: `FriendsController@send`
- CSRF: required
- Form fields:
  - `receiver_id` (int)

#### POST `/friends/accept`

- Controller: `FriendsController@accept`
- CSRF: required
- Form fields:
  - `request_id` (int)

#### POST `/friends/reject`

- Controller: `FriendsController@reject`
- CSRF: required
- Form fields:
  - `request_id` (int)

#### POST `/friends/cancel`

- Controller: `FriendsController@cancel`
- CSRF: required

#### POST `/friends/remove`

- Controller: `FriendsController@remove`
- CSRF: required

### Messages

#### GET `/messages`

- Controller: `MessagesController@index`
- Purpose: inbox view with recent conversations + unread counts

#### GET `/messages/{user_uuid}`

- Controller: `MessagesController@thread`
- Purpose: open 1:1 thread and mark incoming messages as seen

#### POST `/messages/{user_uuid}`

- Controller: `MessagesController@send`
- CSRF: required
- Form fields:
  - `body` (message text)

### Search

#### GET `/search/lookup`

- Controller: `SearchController@lookup`
- Response: JSON
- Query:
  - `q` (string)

## JSON API Routes

### GET `/api/notifications`

- Controller: `NotificationsController@getNotifications`
- Response: JSON list of notifications

### GET `/api/notifications/count`

- Controller: `NotificationsController@getCount`
- Response: JSON
  - `count` (int)

### POST `/api/notifications/read`

- Controller: `NotificationsController@markAllRead`
- Response: JSON

### POST `/api/notifications/{id}/read`

- Controller: `NotificationsController@markRead`
- Response: JSON

## Security Notes

- CSRF verification is implemented via `App\Helpers\Csrf::verifyOrFail()` (or legacy `csrf_verify()` depending on the controller).
- Auth is session-based via `AuthMiddleware::handle()`.
- For user-facing pages, always escape output (see `App\Helpers\e()` helper).
