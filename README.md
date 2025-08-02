# Skavoo – Social Media Platform

**Skavoo** is a custom-built social media platform developed for the Internet Programming 622 module. This system simulates core functionality of modern social networks such as user registration, login, posting, user profiles, and private messaging - all implemented **from scratch** using **PHP**, **MySQL**, **HTML5**, **CSS3**, and **JavaScript**.

This project strictly avoids the use of any prebuilt frameworks or CMS platforms (e.g., Laravel, Bootstrap, WordPress), following the assignment’s technical constraints.

---

## Features

### Core Requirements

- User Registration (Full Name, Email, Password, Optional Profile Picture)
- Password hashing using `password_hash()`
- Login with email and password
- Forgot Password functionality (via email input)
- Session handling using `$_SESSION` for authentication state
- Secure MySQL data storage using PDO and prepared statements

### User Dashboard

- Personal timeline showing all posts (reverse chronological)
- Create post (text + optional image)
- Timestamps for every post

### User Profile Page

- Display profile image, full name, and post summary
- Edit display name and profile image
- Public profiles visible (read-only)

### Private Messaging System

- One-to-one messaging using searchable user input (by name or email)
- Only sender/receiver can view conversations
- Messages stored and displayed in conversation format

### Front-End Interactivity

- JavaScript-based form validation
- Image previews before upload
- Live search for users
- Notification system

---

## Security Features

- SQL injection protection via **prepared statements (PDO)**
- Passwords are securely hashed and never stored in plaintext
- Form validation and sanitization to prevent XSS and tampering
- Access control to restrict protected pages to logged-in users only

---

## Folder Structure

```
skavoo/
├── app/
│   ├── Controllers/                # Route logic (e.g., AuthController.php)
│   ├── Core/                       # Custom Router, DB classes, etc.
│   └── Views/
│       └── auth/                   # Login and related UI templates
├── config/
│   └── database.php                # PDO connection configuration
├── public/
│   ├── index.php                   # Main entry point (front controller)
│   └── .htaccess                   # Clean URL rewrites and security headers
├── routes/
│   └── web.php                     # Application routes
├── database/
│   └── migrations/
│       └── create_tables.sql       # DB schema definition
├── LICENSE.md                      # Custom academic license
├── CHANGELOG.md                    # Development changelog
├── CONTRIBUTING.md                 # (Optional) Contribution guidelines
└── README.md                       # You're reading it
```

---

## Setup Instructions

### Step 1: Database

1. Create a new database called `social_db`.
2. Import the SQL file located at:
   ```
   /database/migrations/create_tables.sql
   ```

### Step 2: Configure DB Credentials

Edit `config/database.php` and set your local environment settings:

```php
$host = 'localhost';
$db   = 'social_db';
$user = 'root';
$pass = '';
```

### Step 3: Start Local PHP Server

From the `public/` directory, run:

```bash
php -S localhost:8000
```

Then open your browser at:

```
http://localhost:8000/login
```

---

## Test Account Details

Use the registration form to create a test account.  
Make sure to include your own **name, picture, and post content** for grading purposes.

---

## License

This project is protected by a custom academic license.  
See [`LICENSE.md`](./LICENSE.txt) for details.  
All intellectual property rights are retained by **Zascia Hugo**.  
Unauthorized use, redistribution, or reproduction is strictly prohibited.

---

## Changelog

See [`CHANGELOG.md`](./CHANGELOG.md) for version history.

---

## Author Information

- **Name:** Zascia Hugo
- **Module:** Internet Programming 622
- **Institution:** Richfield Graduate Institute of Technology
- **Year:** 2025
