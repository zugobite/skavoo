<?php

namespace App\Helpers;

/**
 * CSRF Helper Class
 *
 * Provides methods to generate, output, and verify CSRF tokens.
 * Can be used statically (Csrf::token(), Csrf::field(), Csrf::verifyOrFail()).
 *
 * Backwards compatible with procedural functions (csrf_token(), csrf_field(), csrf_verify()).
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Csrf
{
    /**
     * Generate or retrieve the current CSRF token.
     *
     * @return string
     */
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    /**
     * Render a hidden CSRF input field for forms.
     *
     * @param string|null $token
     * @return string
     */
    public static function field(?string $token = null): string
    {
        $token = $token ?: self::token();
        return '<input type="hidden" name="csrf" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify a provided CSRF token string against the stored one.
     *
     * @param string $token
     * @return bool
     */
    public static function verify(string $token): bool
    {
        return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
    }

    /**
     * Verify current request token or exit with 419.
     *
     * @return void
     */
    public static function verifyOrFail(): void
    {
        $token = $_POST['csrf'] ?? '';
        if (!self::verify($token)) {
            http_response_code(419);
            echo 'CSRF token mismatch.';
            exit;
        }
    }
}

/**
 * --------------------------------------------------------------------------
 * Backwards Compatibility (optional)
 * --------------------------------------------------------------------------
 * These global functions mirror the previous implementation, so any legacy
 * code that calls csrf_token(), csrf_field(), or csrf_verify() still works.
 */

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return Csrf::field();
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(string $token): bool
    {
        return Csrf::verify($token);
    }
}
