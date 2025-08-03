<?php

/**
 * Web Routes
 *
 * This file defines all the web-accessible routes for the application.
 * Each route maps a URI to a specific controller and method.
 *
 * @package Skavoo
 */

/**
 * Define a GET route for the home page.
 *
 * When the user navigates to "/", the request will be handled by
 * the `index` method of the `HomeController` class.
 * This route is used to display the home page.
 */
$router->get('/', 'HomeController@index');

/**
 * Define a GET route for the login page.
 *
 * When the user navigates to "/login", the request will be handled by
 * the `loginPage` method of the `AuthController` class.
 * This route is used to display the login form for users.
 */
$router->get('/login', 'AuthController@loginPage');

/**
 * Define a POST route for the login action.
 *
 * When the user submits the login form, the request will be handled by
 * the `login` method of the `AuthController` class.
 * This route is used to process the login credentials.
 */
$router->post('/login', 'AuthController@login');

/**
 * Define a GET route for the register page.
 * 
 * When the user navigates to "/register", the request will be handled by
 * the `registerPage` method of the `AuthController` class.
 * This route is used to display the registration form for new users.
 */
$router->get('/register', 'AuthController@registerPage');
/**
 * Define a POST route for the registration action.
 *
 * When the user submits the registration form, the request will be handled by
 * the `register` method of the `AuthController` class.
 * This route is used to process new user registrations.
 */
$router->post('/register', 'AuthController@register');

/**
 * Define a GET route for the password reset page.
 *
 * When the user navigates to "/forgot-password", the request will be handled by
 * the `forgotPasswordPage` method of the `AuthController` class.
 * This route is used to display the password reset form for users.
 */
$router->get('/forgot-password', 'AuthController@forgotPasswordPage');

/**
 * Define a POST route for the password reset action.
 *
 * When the user submits the password reset form, the request will be handled by
 * the `forgotPassword` method of the `AuthController` class.
 * This route is used to process password reset requests.
 */
$router->post('/forgot-password', 'AuthController@forgotPassword');

/**
 * Define a GET route for the logout action.
 *
 * When the user navigates to "/logout", the request will be handled by
 * the `logout` method of the `AuthController` class.
 * This route is used to log out the user from the application.
 */
$router->get('/logout', 'AuthController@logout');

/**
 * Define a GET route for the user profile page.
 *
 * When the user navigates to "/user/profile", the request will be handled by
 * the `profile` method of the `UserController` class.
 * This route is used to display the user's profile information.
 */
$router->get('/user/profile/{uuid}', 'UserController@profile');

/**
 * Define a POST route for creating a new post.
 *
 * When the user submits the new post form, the request will be handled by
 * the `createPost` method of the `UserController` class.
 * This route is used to process the creation of new posts.
 */
$router->post('/user/post', 'UserController@createPost');

/**
 * Define a GET route for the user feed.
 *
 * When the user navigates to "/feed", the request will be handled by
 * the `index` method of the `FeedController` class.
 * This route is used to display the user's feed with posts.
 */
$router->get('/feed', 'FeedController@index');