# Changelog

All notable changes to this project will be documented in this file.

This project uses **Semantic Versioning**:  
`MAJOR.MINOR.PATCH`  
See [semver.org](https://semver.org) for more details.

---

## Version Control Table

| Version | Description                        | Author      | Date       |
| ------- | ---------------------------------- | ----------- | ---------- |
| 0.1.0   | Initial boilerplate and core setup | Zascia Hugo | 2025-08-02 |

---

## **[0.1.0] - 2025-08-02**

### Added

- `public/index.php` as the front controller for routing all HTTP requests
- `Router` class to handle GET and POST routes and dispatch controller methods
- `routes/web.php` with initial route definition for `/login`
- `AuthController` with `loginPage()` and `login()` stubs
- `app/Views/auth/login.php` with HTML login form
- `config/database.php` with PDO-based MySQL connection
- `README.md` with setup instructions tailored for MacBook users
- `LICENSE.txt` restricting use and reproduction by Richfield Graduate Institute of Technology
- `CHANGELOG.md` formatted with semantic versioning

### Notes

- Project is still in development; this is the initial stable boilerplate foundation
