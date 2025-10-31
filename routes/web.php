<?php

/**
 * Web Routes
 *
 * This file defines all publicly accessible HTTP routes for the Skavoo application.
 * Each route maps a specific HTTP method and URI pattern to a controller action,
 * ensuring that requests are routed to the appropriate business logic.
 *
 * Conventions:
 * - Use RESTful verbs where possible.
 * - POST routes that mutate state should be CSRF-protected.
 * - UUIDs are used for user-facing identifiers.
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
 *
 * Security:
 * - Requires authentication.
 * - Must be CSRF-protected.
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
 *
 * Security:
 * - Requires authentication.
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
 *
 * Query Params:
 * - q: string (search term)
 */
$router->get('/search/lookup', 'SearchController@lookup');


/**
 * ---------------------------------------------------------------
 * Profile edit & update
 * ---------------------------------------------------------------
 */

/**
 * Show the profile edit form for the authenticated owner.
 *
 * @method GET
 * @route /user/profile/{uuid}/edit
 * @controller UserController@editProfilePage
 * @description Displays the edit profile page (owner-only).
 *
 * @param string $uuid The UUID of the profile being edited.
 *
 * Security:
 * - Requires authentication.
 * - Access limited to the profile owner.
 */
$router->get('/user/profile/{uuid}/edit', 'UserController@editProfilePage');

/**
 * Update profile (display name and optional profile picture).
 *
 * @method POST
 * @route /user/profile/{uuid}/edit
 * @controller UserController@updateProfile
 * @description Updates display name and optional profile picture for the authenticated owner.
 *
 * @param string $uuid The UUID of the profile being updated.
 *
 * Security:
 * - Requires authentication.
 * - Must be CSRF-protected.
 * - Access limited to the profile owner.
 */
$router->post('/user/profile/{uuid}/edit', 'UserController@updateProfile');

/**
 * ---------------------------------------------------------------
 * Private messages
 * ---------------------------------------------------------------
 */

/**
 * Show recent conversations (inbox).
 *
 * @method GET
 * @route /messages
 * @controller MessagesController@index
 * @description Lists the user's recent message threads.
 *
 * Security:
 * - Requires authentication.
 */
$router->get('/messages', 'MessagesController@index');

/**
 * Open or create a conversation thread with a specific user.
 *
 * @method GET
 * @route /messages/{user_uuid}
 * @controller MessagesController@thread
 * @description Displays the conversation with the specified user.
 *
 * @param string $user_uuid The UUID of the other participant.
 *
 * Security:
 * - Requires authentication.
 */
$router->get('/messages/{user_uuid}', 'MessagesController@thread');

/**
 * Send a private message to the specified user.
 *
 * @method POST
 * @route /messages/{user_uuid}
 * @controller MessagesController@send
 * @description Sends a new message in the conversation.
 *
 * @param string $user_uuid The UUID of the recipient.
 *
 * Security:
 * - Requires authentication.
 * - Must be CSRF-protected.
 */
$router->post('/messages/{user_uuid}', 'MessagesController@send');
