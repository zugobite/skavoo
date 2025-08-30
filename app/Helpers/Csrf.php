<?php

/**
 * CSRF Helper Functions
 *
 * This file contains functions to handle CSRF protection in forms.
 * It generates a CSRF token, provides a hidden input field for forms,
 * and verifies the token on form submission.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify(string $token): bool
{
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}
