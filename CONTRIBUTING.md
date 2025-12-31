# Contributing to Skavoo

First off, thank you for considering contributing to Skavoo! It's people like you that make Skavoo such a great tool.

## Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Style Guidelines](#style-guidelines)
- [Commit Messages](#commit-messages)
- [Pull Request Process](#pull-request-process)

## Code of Conduct

This project and everyone participating in it is governed by our commitment to providing a welcoming and inclusive environment. By participating, you are expected to uphold this standard. Please report unacceptable behavior to the repository maintainers.

## Getting Started

Before you begin:

- Make sure you have a [GitHub account](https://github.com/signup)
- Familiarize yourself with the [project structure](#project-structure)
- Check existing [issues](../../issues) to see if your contribution is already being discussed

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues as you might find that the bug has already been reported. When you are creating a bug report, please include as many details as possible using our [bug report template](.github/ISSUE_TEMPLATE/bug_report.md).

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. Create an issue using our [feature request template](.github/ISSUE_TEMPLATE/feature_request.md) and provide:

- A clear and descriptive title
- A detailed description of the proposed enhancement
- Explain why this enhancement would be useful
- List any alternatives you've considered

### Pull Requests

1. Fork the repository
2. Create a new branch from `main`
3. Make your changes
4. Submit a pull request

## Development Setup

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- A local development server (PHP built-in server works fine)

### Local Setup

1. **Clone your fork:**

   ```bash
   git clone https://github.com/YOUR_USERNAME/skavoo.git
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

4. **Create the database:**

   ```bash
   mysql -u root -p -e "CREATE DATABASE social_db;"
   ```

5. **Run migrations:**

   ```bash
   mysql -u root -p social_db < database/migrations/create_tables.sql
   ```

6. **Start the development server:**

   ```bash
   cd public
   php -S localhost:8000
   ```

7. **Visit:** http://localhost:8000

## Project Structure

```
skavoo/
├── app/
│   ├── Controllers/     # Request handlers
│   ├── Core/            # Router and core classes
│   ├── Helpers/         # Utility functions
│   ├── Middleware/      # Authentication middleware
│   ├── Support/         # Global helpers and aliases
│   └── Views/           # PHP templates
├── config/              # Configuration files
├── database/            # Migrations and seeders
├── public/              # Web root (entry point)
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── uploads/         # User uploads
└── routes/              # Route definitions
```

## Style Guidelines

### PHP Code Style

- **PSR-12** coding standard
- Use **meaningful variable names** (not `$x`, `$temp`)
- **PHPDoc comments** for all classes, methods, and functions
- **Type hints** for parameters and return types where possible

```php
/**
 * Retrieves a user by their ID.
 *
 * @param int $userId The user's unique identifier
 * @return array|null The user data or null if not found
 */
public function getUserById(int $userId): ?array
{
    // Implementation
}
```

### Security Guidelines

- Always use **prepared statements** for database queries
- **Validate and sanitize** all user input
- Use **CSRF tokens** on all forms
- **Hash passwords** with `password_hash()`
- **Escape output** to prevent XSS

### CSS Guidelines

- Use **meaningful class names** (BEM naming is encouraged)
- Keep specificity low
- Mobile-first responsive design

### JavaScript Guidelines

- Use **vanilla JavaScript** (no frameworks required)
- Use `const` and `let` (never `var`)
- Add comments for complex logic

## Commit Messages

Follow the [Conventional Commits](https://www.conventionalcommits.org/) specification:

```
<type>(<scope>): <description>

[optional body]

[optional footer]
```

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation only changes
- `style`: Code style changes (formatting, semicolons, etc.)
- `refactor`: Code changes that neither fix a bug nor add a feature
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```
feat(auth): add password reset functionality

fix(messages): resolve thread ordering issue

docs(readme): update installation instructions

style(css): improve button hover states
```

## Pull Request Process

1. **Update documentation** if you're changing functionality
2. **Update the CHANGELOG.md** with your changes under "Unreleased"
3. **Ensure your code passes** any existing tests
4. **Fill out the PR template** completely
5. **Request a review** from a maintainer
6. **Address feedback** promptly

### PR Checklist

- [ ] Code follows the project's style guidelines
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated (if applicable)
- [ ] No new warnings generated
- [ ] Changes tested locally

## Questions?

Feel free to open an issue with the `question` label if you need help getting started.
