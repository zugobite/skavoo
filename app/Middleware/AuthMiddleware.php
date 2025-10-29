<?php

/**
 * AuthMiddleware
 *
 * Ensures the user is authenticated before accessing protected routes.
 *
 * Usage:
 *   include '../app/Middleware/AuthMiddleware.php';
 *   AuthMiddleware::handle();
 *
 * @package Skavoo\Middleware
 */
class AuthMiddleware
{
    /**
     * Redirects to login if user is not authenticated.
     *
     * @return void
     */
    public static function handle(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }
}
