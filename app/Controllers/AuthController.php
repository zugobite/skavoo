<?php

/**
 * AuthController
 *
 * Handles user authentication-related routes such as displaying
 * the login form and processing login credentials.
 *
 * @package Skavoo\Controllers
 */
class AuthController
{

    /**
     * Display the login page.
     *
     * This method includes the login view, which presents a form
     * for users to enter their email and password.
     *
     * @return void
     */
    public function loginPage()
    {
        include '../app/Views/auth/login.php';
    }

    /**
     * Handle the login form submission.
     *
     * This method will process the login form, validate credentials,
     * initiate user sessions, and redirect appropriately.
     *
     * @return void
     */
    public function login()
    {
        // Login logic here (e.g., check email/password, set $_SESSION)
    }
}
