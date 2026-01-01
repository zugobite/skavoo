# Skavoo

A modern, full-featured social media platform built from scratch with vanilla PHP, demonstrating MVC architecture, secure authentication, and complete social networking capabilities.

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1.svg)](https://mysql.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

## Features

- **User Authentication** - Complete auth flow with secure password hashing and session management
- **CSRF Protection** - Token-based protection on all forms
- **SQL Injection Prevention** - PDO prepared statements throughout
- **Social Posts** - Create, like, and comment on posts with image uploads
- **Friend System** - Send, accept, and manage friend requests
- **Private Messaging** - One-to-one direct messaging with conversation threads
- **User Profiles** - Customizable profiles with avatars and post history
- **Real-time Notifications** - Activity notifications with mark-as-read functionality
- **Password Recovery** - Email-based password reset with secure tokens
- **User Search** - Find and connect with other users

## Table of Contents

- [Quick Start](#quick-start)
- [Documentation](#documentation)
- [Routes & Endpoints](#routes--endpoints)
- [Usage Examples](#usage-examples)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Environment Variables](#environment-variables)
- [Development](#development)
- [Contributing](#contributing)
- [Disclaimer](#disclaimer)
- [License](#license)

## Quick Start

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- A web server (Apache/Nginx) or PHP built-in server

### Installation

```bash
# Clone the repository
git clone https://github.com/zugobite/skavoo.git
cd skavoo

# Copy environment template
cp .env.example .env
# Edit .env with your database configuration

# Create database and run migrations
mysql -u root -p -e "CREATE DATABASE social_db;"
mysql -u root -p social_db < database/migrations/create_tables.sql

# (Optional) Seed the database with sample data
php database/seeder.php

# Start the development server
cd public
php -S localhost:8000
```

The application will be available at `http://localhost:8000`.

## Documentation

Extensive developer documentation is available in the [`docs/`](docs/) folder:

| Document                                                | Description                                                 |
| ------------------------------------------------------- | ----------------------------------------------------------- |
| [001-PROJECT_OVERVIEW.md](docs/001-PROJECT_OVERVIEW.md) | Architecture, request lifecycle, and codebase overview      |
| [002-GETTING_STARTED.md](docs/002-GETTING_STARTED.md)   | Local setup, environment configuration, and troubleshooting |
| [003-API_REFERENCE.md](docs/003-API_REFERENCE.md)       | Complete route documentation with request/response details  |
| [004-AUTHENTICATION.md](docs/004-AUTHENTICATION.md)     | Auth flow, sessions, CSRF protection, and password reset    |
| [005-DATABASE_SCHEMA.md](docs/005-DATABASE_SCHEMA.md)   | Table definitions, relationships, and seeding               |
| [006-TESTING.md](docs/006-TESTING.md)                   | Manual test checklist and automated testing guidance        |
| [007-DEPLOYMENT.md](docs/007-DEPLOYMENT.md)             | Production deployment and server configuration              |
| [008-MONITORING.md](docs/008-MONITORING.md)             | Logging, health checks, and alerting                        |
| [009-SCALING.md](docs/009-SCALING.md)                   | Database optimization, caching, and horizontal scaling      |

## Routes & Endpoints

### Public Routes

| Method | Route              | Description            |
| ------ | ------------------ | ---------------------- |
| `GET`  | `/`                | Home page              |
| `GET`  | `/login`           | Login page             |
| `POST` | `/login`           | Process login          |
| `GET`  | `/register`        | Registration page      |
| `POST` | `/register`        | Process registration   |
| `GET`  | `/forgot-password` | Forgot password page   |
| `POST` | `/forgot-password` | Send reset email       |
| `GET`  | `/reset-password`  | Reset password page    |
| `POST` | `/reset-password`  | Process password reset |

### Protected Routes (Requires Authentication)

| Method | Route     | Description                |
| ------ | --------- | -------------------------- |
| `GET`  | `/feed`   | User feed (friends' posts) |
| `POST` | `/logout` | Log out user               |

### Post Routes

| Method | Route                 | Description         |
| ------ | --------------------- | ------------------- |
| `POST` | `/posts`              | Create new post     |
| `POST` | `/posts/{id}/like`    | Like a post         |
| `POST` | `/posts/{id}/unlike`  | Unlike a post       |
| `POST` | `/posts/{id}/comment` | Add comment to post |
| `POST` | `/posts/{id}/delete`  | Delete a post       |

### User & Profile Routes

| Method | Route           | Description       |
| ------ | --------------- | ----------------- |
| `GET`  | `/user/{id}`    | View user profile |
| `GET`  | `/profile/edit` | Edit profile page |
| `POST` | `/profile/edit` | Update profile    |
| `GET`  | `/search`       | Search users      |

### Friend Routes

| Method | Route                  | Description             |
| ------ | ---------------------- | ----------------------- |
| `GET`  | `/friends`             | Friends list            |
| `GET`  | `/friends/requests`    | Pending friend requests |
| `POST` | `/friends/add/{id}`    | Send friend request     |
| `POST` | `/friends/accept/{id}` | Accept friend request   |
| `POST` | `/friends/reject/{id}` | Reject friend request   |
| `POST` | `/friends/remove/{id}` | Remove friend           |

### Message Routes

| Method | Route            | Description         |
| ------ | ---------------- | ------------------- |
| `GET`  | `/messages`      | Message inbox       |
| `GET`  | `/messages/{id}` | Conversation thread |
| `POST` | `/messages/{id}` | Send message        |

### Notification Routes

| Method | Route                      | Description        |
| ------ | -------------------------- | ------------------ |
| `GET`  | `/notifications`           | View notifications |
| `POST` | `/notifications/{id}/read` | Mark as read       |
| `POST` | `/notifications/read-all`  | Mark all as read   |

## Usage Examples

### Complete User Flow

```bash
# 1. Register a new account
# Navigate to http://localhost:8000/register
# Fill in: username, email, password

# 2. Login
# Navigate to http://localhost:8000/login
# Enter your credentials

# 3. Create a post
# On the feed page, use the post form
# Add text and optionally upload an image

# 4. Search for users
# Use the search bar to find other users
# Send friend requests to connect

# 5. Send a message
# Navigate to a user's profile
# Click "Message" to start a conversation
```

### Database Seeding

```bash
# Populate the database with sample users and content
php database/seeder.php
```

## Project Structure

```
skavoo/
├── app/
│   ├── Controllers/                    # Request handlers
│   │   ├── AuthController.php
│   │   ├── FeedController.php
│   │   ├── FriendsController.php
│   │   ├── HomeController.php
│   │   ├── MessagesController.php
│   │   ├── NotificationsController.php
│   │   ├── PostController.php
│   │   ├── SearchController.php
│   │   └── UserController.php
│   ├── Core/                           # Framework core
│   │   └── Router.php
│   ├── Helpers/                        # Utility classes
│   │   ├── Csrf.php
│   │   ├── DB.php
│   │   ├── env.php
│   │   ├── Functions.php
│   │   └── Mail.php
│   ├── Middleware/                     # Request middleware
│   │   └── AuthMiddleware.php
│   ├── Support/                        # Bootstrap files
│   │   ├── global_helpers.php
│   │   └── namespace_aliases.php
│   └── Views/                          # PHP templates
│       ├── Auth/                       # Authentication views
│       ├── Components/                 # Reusable components
│       ├── Emails/                     # Email templates
│       ├── Friends/                    # Friend management views
│       ├── Messages/                   # Messaging views
│       └── User/                       # Profile views
├── config/
│   └── database.php                    # Database configuration
├── database/
│   ├── migrations/                     # SQL schema files
│   │   └── create_tables.sql
│   ├── seeder.php                      # Database seeder
│   └── ERD.md                          # Entity relationship docs
├── public/
│   ├── index.php                       # Application entry point
│   ├── css/                            # Stylesheets
│   ├── js/                             # JavaScript files
│   └── uploads/                        # User uploads
│       ├── avatars/
│       └── posts/
├── routes/
│   └── web.php                         # Route definitions
├── docs/                               # Developer documentation
│   ├── 001-PROJECT_OVERVIEW.md
│   ├── 002-GETTING_STARTED.md
│   ├── 003-API_REFERENCE.md
│   ├── 004-AUTHENTICATION.md
│   ├── 005-DATABASE_SCHEMA.md
│   ├── 006-TESTING.md
│   ├── 007-DEPLOYMENT.md
│   ├── 008-MONITORING.md
│   └── 009-SCALING.md
├── .env.example                        # Environment template
├── CHANGELOG.md                        # Version history
├── CONTRIBUTING.md                     # Contribution guidelines
├── LICENSE                             # MIT License
└── README.md
```

## Database Schema

The application uses the following database tables:

| Table             | Description                         |
| ----------------- | ----------------------------------- |
| `users`           | User accounts and profile data      |
| `posts`           | User posts with optional media      |
| `likes`           | Post likes (user-post relationship) |
| `comments`        | Comments on posts                   |
| `friends`         | Friend relationships and requests   |
| `messages`        | Private messages between users      |
| `notifications`   | User activity notifications         |
| `password_resets` | Password reset tokens               |

For the complete schema, see [database/migrations/create_tables.sql](database/migrations/create_tables.sql).

For entity relationships, see [database/ERD.md](database/ERD.md).

## Environment Variables

| Variable    | Description       | Required |
| ----------- | ----------------- | -------- |
| `DB_HOST`   | Database host     | Yes      |
| `DB_NAME`   | Database name     | Yes      |
| `DB_USER`   | Database username | Yes      |
| `DB_PASS`   | Database password | Yes      |
| `APP_ENV`   | Environment mode  | No       |
| `APP_DEBUG` | Enable debug mode | No       |
| `MAIL_HOST` | SMTP server host  | No       |
| `MAIL_PORT` | SMTP server port  | No       |
| `MAIL_USER` | SMTP username     | No       |
| `MAIL_PASS` | SMTP password     | No       |

See [.env.example](.env.example) for a complete template.

## Development

```bash
# Start development server
cd public && php -S localhost:8000

# Run migrations
mysql -u root -p social_db < database/migrations/create_tables.sql

# Seed database
php database/seeder.php
```

### Architecture

This project follows an MVC (Model-View-Controller) architecture:

- **Controllers** handle incoming requests and return responses
- **Views** are PHP templates that render the HTML
- **Helpers** provide database access and utility functions
- **Middleware** handles authentication and request preprocessing
- **Router** maps URLs to controller actions

### Security Features

| Feature          | Implementation                |
| ---------------- | ----------------------------- |
| Password Hashing | `password_hash()` with bcrypt |
| SQL Injection    | PDO prepared statements       |
| CSRF Protection  | Token-based form protection   |
| Session Security | Secure session configuration  |
| XSS Prevention   | Output escaping in views      |

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Guidelines

- Follow the existing code style
- Add PHPDoc comments for new functions
- Update documentation as needed
- Keep commits atomic and well-described

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines.

## Disclaimer

This project is created **purely for educational and portfolio demonstration purposes** to showcase PHP development skills and software architecture knowledge.

**Important notices:**

- It implements **industry-standard patterns** documented in publicly available resources including:
  - Laravel's architectural patterns (implemented from scratch)
  - OWASP security guidelines
  - PHP-FIG PSR standards
- This is **not intended for production use** without proper security audits and additional hardening
- The codebase demonstrates MVC patterns, secure authentication, and database design principles

## Security

If you discover a security vulnerability, please report it privately rather than opening a public issue. See [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
