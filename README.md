<div align="center">

# �� Skavoo

**A modern social media platform built from scratch with vanilla PHP**

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen?style=for-the-badge)](CONTRIBUTING.md)

[Features](#-features) •
[Quick Start](#-quick-start) •
[Documentation](#-documentation) •
[Contributing](#-contributing) •
[License](#-license)

</div>

---

## 📋 Overview

**Skavoo** is a full-featured social media platform demonstrating modern web development practices using vanilla PHP. Built without frameworks to showcase core programming fundamentals, it implements the complete social networking experience: user authentication, profiles, posts, messaging, friends, and real-time notifications.

> **⚠️ Disclaimer:** This project is designed for educational and portfolio purposes. It demonstrates PHP development patterns similar to Laravel's architecture but implemented from scratch.

### Why Skavoo?

- 🎯 **Framework-Free** – Pure PHP showcasing MVC architecture without dependencies
- 🔒 **Security-First** – PDO prepared statements, CSRF protection, password hashing
- 📱 **Full-Featured** – Complete social platform with all core features
- 🧩 **Extensible** – Clean codebase designed for easy modifications
- 📚 **Educational** – Well-documented code with PHPDoc comments

---

## ✨ Features

### Authentication & Security
- ✅ User registration with email validation
- ✅ Secure login with password hashing (`password_hash`)
- ✅ Password reset via email tokens
- ✅ Session-based authentication
- ✅ CSRF protection on all forms
- ✅ SQL injection prevention (PDO prepared statements)

### Social Features
- ✅ Create posts with text and images
- ✅ Like and comment on posts
- ✅ Personalized feed from friends
- ✅ User search functionality

### User Profiles
- ✅ Customizable profiles with avatars
- ✅ Edit display name and profile picture
- ✅ View other users' public profiles
- ✅ Post history on profiles

### Friend System
- ✅ Send/accept/reject friend requests
- ✅ View friends list
- ✅ Remove friends

### Private Messaging
- ✅ One-to-one direct messaging
- ✅ Conversation threads
- ✅ Message inbox

### Notifications
- ✅ Real-time notification system
- ✅ Mark as read functionality

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- A web server (Apache/Nginx) or PHP built-in server

### Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/zugobite/skavoo.git
   cd skavoo
   ```

2. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

3. **Configure your database credentials in `.env`:**
   ```env
   DB_HOST=localhost
   DB_NAME=social_db
   DB_USER=root
   DB_PASS=your_password
   ```

4. **Create the database and run migrations:**
   ```bash
   mysql -u root -p -e "CREATE DATABASE social_db;"
   mysql -u root -p social_db < database/migrations/create_tables.sql
   ```

5. **Start the development server:**
   ```bash
   cd public
   php -S localhost:8000
   ```

6. **Visit the application:**
   ```
   http://localhost:8000
   ```

---

## 📁 Project Structure

```
skavoo/
├── app/
│   ├── Controllers/         # Request handlers (Auth, Post, User, etc.)
│   ├── Core/                # Router and core framework classes
│   ├── Helpers/             # Utility functions (DB, CSRF, Mail, etc.)
│   ├── Middleware/          # Authentication middleware
│   ├── Support/             # Global helpers and namespace aliases
│   └── Views/               # PHP template files
│       ├── Auth/            # Login, register, password reset
│       ├── Components/      # Reusable UI components
│       ├── Emails/          # Email templates
│       ├── Friends/         # Friends list and requests
│       ├── Messages/        # Inbox and conversation threads
│       └── User/            # Profile and edit profile
├── config/
│   └── database.php         # Database configuration
├── database/
│   ├── migrations/          # SQL schema files
│   └── seeder.php           # Database seeder
├── public/
│   ├── index.php            # Front controller (entry point)
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── uploads/             # User-uploaded files
├── routes/
│   └── web.php              # Route definitions
├── .env.example             # Environment template
├── .gitignore               # Git ignore rules
├── CHANGELOG.md             # Version history
├── CONTRIBUTING.md          # Contribution guidelines
├── LICENSE                  # MIT License
└── README.md                # This file
```

---

## 📖 Documentation

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_NAME` | Database name | `social_db` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | - |
| `APP_ENV` | Environment mode | `development` |
| `APP_DEBUG` | Debug mode | `true` |

### Database Schema

The application uses the following main tables:

- `users` – User accounts and profiles
- `posts` – User posts with optional media
- `likes` – Post likes
- `comments` – Post comments
- `friends` – Friend relationships and requests
- `messages` – Private messages
- `notifications` – User notifications
- `password_resets` – Password reset tokens

For the complete schema, see [database/migrations/create_tables.sql](database/migrations/create_tables.sql).

### Routes

All routes are defined in [routes/web.php](routes/web.php). The application uses a custom router supporting:

- GET and POST methods
- Dynamic route parameters (`/user/{id}`)
- Middleware for protected routes

---

## 🤝 Contributing

Contributions are welcome! Please read our [Contributing Guidelines](CONTRIBUTING.md) before submitting a pull request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'feat: add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 🔒 Security

For security concerns, please review our [Security Policy](.github/SECURITY.md).

If you discover a security vulnerability, please report it privately rather than opening a public issue.

---

## �� License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed version history.

---

## 👤 Author

**Zascia Hugo**

- GitHub: [@zugobite](https://github.com/zugobite)

---

<div align="center">

**⭐ Star this repo if you find it helpful!**

Made with ❤️ by Zascia Hugo

</div>
