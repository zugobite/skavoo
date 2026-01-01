# Deployment

This document outlines practical deployment guidance for Skavoo.

Skavoo is a vanilla PHP app with a `public/` front controller; deploy it like a standard PHP site.

## Requirements

- PHP 8.0+
- MySQL 5.7+
- Web server (Nginx or Apache)

## Environment Configuration

1. Copy `.env.example` to `.env`
2. Set DB credentials:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`
3. Set `APP_URL` to your production base URL

Important:

- Do not commit `.env`
- Ensure the process can read `.env` at runtime

## File Permissions

Ensure the PHP process can write to:

- `public/uploads/avatars/`
- `public/uploads/posts/`
- `storage/mail/` (if you keep the file-mailer enabled)

## Web Server Root

Point your server document root to:

- `{repo}/public`

This ensures requests go through `public/index.php`.

## Nginx Notes (Conceptual)

Typical configuration should:

- Serve static files under `public/` directly
- Forward all other routes to `public/index.php`

## Apache Notes (Conceptual)

If using Apache, you typically want:

- `DocumentRoot` set to `public/`
- URL rewriting to route non-file requests to `index.php`

## Mail in Production

The current mail implementation is file-based (writes `.eml` to disk). For production you likely want SMTP or a third-party email provider.

Minimum change approach:

- Replace `send_mail()` implementation in `app/Helpers/Mail.php` with a real SMTP sender
- Keep the same function signature so controllers don’t need modification

## Security Checklist

- Use HTTPS
- Set secure session cookie flags
- Consider rate limiting on auth endpoints
- Ensure uploads are scanned/validated and served safely
