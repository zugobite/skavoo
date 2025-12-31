# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

We take the security of Skavoo seriously. If you discover a security vulnerability, please follow these steps:

### 1. Do Not Open a Public Issue

Please **do not** open a GitHub issue for security vulnerabilities, as this could expose the vulnerability to malicious actors.

### 2. Contact Us Privately

Send a detailed report to the maintainer via:

- **GitHub:** Open a private security advisory via the "Security" tab of this repository
- **Email:** Contact the repository owner directly

### 3. Include the Following Information

- A description of the vulnerability
- Steps to reproduce the issue
- Potential impact of the vulnerability
- Any suggested fixes (optional but appreciated)

### 4. Response Timeline

- **Initial Response:** Within 48 hours
- **Status Update:** Within 7 days
- **Resolution:** Depends on complexity, typically within 30 days

## Security Best Practices for Contributors

When contributing to Skavoo, please ensure:

1. **Never commit sensitive data** (passwords, API keys, tokens) to the repository
2. **Use prepared statements** for all database queries to prevent SQL injection
3. **Validate and sanitize** all user input
4. **Use CSRF tokens** for all forms
5. **Hash passwords** using `password_hash()` with `PASSWORD_DEFAULT`
6. **Escape output** to prevent XSS attacks
7. **Keep dependencies updated** to patch known vulnerabilities

## Known Security Features

Skavoo implements the following security measures:

- ✅ Password hashing with `password_hash()`
- ✅ SQL injection prevention via PDO prepared statements
- ✅ CSRF protection on all forms
- ✅ Session-based authentication
- ✅ Input validation and sanitization
- ✅ Secure file upload handling

## Acknowledgments

We appreciate security researchers who responsibly disclose vulnerabilities. Contributors who report valid security issues will be acknowledged in our release notes (with permission).
