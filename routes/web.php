<?php

/**
 * Web Routes
 *
 * This file defines all publicly accessible HTTP routes for the Skavoo application.
 * Each route maps a specific HTTP method and URI pattern to a controller action,
 * ensuring that requests are routed to the appropriate business logic.
 *
 * @package Skavoo
 */

/**
 * ---------------------------------------------------------------
 * Home Routes
 * ---------------------------------------------------------------
 */

/**
 * Display the public home page.
 *
 * @method GET
 * @route /
 * @controller HomeController@index
 * @description Shows the application's main landing page.
 */
$router->get('/', 'HomeController@index');

/**
 * ---------------------------------------------------------------
 * Authentication Routes
 * ---------------------------------------------------------------
 */

/**
 * Display the login form for users.
 *
 * @method GET
 * @route /login
 * @controller AuthController@loginPage
 * @description Renders the login page for user authentication.
 */
$router->get('/login', 'AuthController@loginPage');

/**
 * Handle login form submission.
 *
 * @method POST
 * @route /login
 * @controller AuthController@login
 * @description Authenticates the user and starts a new session.
 */
$router->post('/login', 'AuthController@login');

/**
 * Display the user registration form.
 *
 * @method GET
 * @route /register
 * @controller AuthController@registerPage
 * @description Renders the registration page for new users.
 */
$router->get('/register', 'AuthController@registerPage');

/**
 * Process the user registration form.
 *
 * @method POST
 * @route /register
 * @controller AuthController@register
 * @description Validates and stores a new user account.
 */
$router->post('/register', 'AuthController@register');

/**
 * ---------------------------------------------------------------
 * Password Reset Routes
 * ---------------------------------------------------------------
 */

/**
 * Display the "Forgot Password" page.
 *
 * @method GET
 * @route /forgot-password
 * @controller AuthController@forgotPasswordPage
 * @description Allows users to request a password reset link via email.
 */
$router->get('/forgot-password', 'AuthController@forgotPasswordPage');

/**
 * Handle password reset link requests.
 *
 * @method POST
 * @route /forgot-password
 * @controller AuthController@sendResetLink
 * @description Sends an email containing a secure password reset link.
 */
$router->post('/forgot-password', 'AuthController@sendResetLink');

/**
 * Display the password reset form.
 *
 * @method GET
 * @route /reset-password
 * @controller AuthController@resetPasswordPage
 * @description Shows the reset password form linked from the user's email.
 */
$router->get('/reset-password', 'AuthController@resetPasswordPage');

/**
 * Handle password reset submissions.
 *
 * @method POST
 * @route /reset-password
 * @controller AuthController@handleResetPassword
 * @description Validates the reset token and updates the user's password.
 */
$router->post('/reset-password', 'AuthController@handleResetPassword');

/**
 * ---------------------------------------------------------------
 * Logout Route
 * ---------------------------------------------------------------
 */

/**
 * Log the user out of the application.
 *
 * @method GET
 * @route /logout
 * @controller AuthController@logout
 * @description Ends the user's session and redirects to the home page.
 */
$router->get('/logout', 'AuthController@logout');

/**
 * ---------------------------------------------------------------
 * User Routes
 * ---------------------------------------------------------------
 */

/**
 * Display a user's public profile.
 *
 * @method GET
 * @route /user/profile/{uuid}
 * @controller UserController@profile
 * @description Shows the profile page for a user identified by their UUID.
 *
 * @param string $uuid The unique identifier of the user whose profile to display.
 */
$router->get('/user/profile/{uuid}', 'UserController@profile');

/**
 * Create a new post for the authenticated user.
 *
 * @method POST
 * @route /user/post
 * @controller UserController@createPost
 * @description Handles new post submissions from the user's profile or feed.
 */
$router->post('/user/post', 'UserController@createPost');

/**
 * ---------------------------------------------------------------
 * Feed Routes
 * ---------------------------------------------------------------
 */

/**
 * Display the user's personalized feed.
 *
 * @method GET
 * @route /feed
 * @controller FeedController@index
 * @description Shows the aggregated feed of posts for the logged-in user.
 */
$router->get('/feed', 'FeedController@index');

/**
 * ---------------------------------------------------------------
 * Search Routes
 * ---------------------------------------------------------------
 */

/**
 * Search for users by name or email address.
 *
 * @method GET
 * @route /search/lookup
 * @controller SearchController@lookup
 * @description Enables user search functionality by name or email.
 */
$router->get('/search/lookup', 'SearchController@lookup');
