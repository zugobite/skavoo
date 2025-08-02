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
 * Define a GET route for the login page.
 *
 * When the user navigates to "/login", the request will be handled by
 * the `loginPage` method of the `AuthController` class.
 */
$router->get('/login', 'AuthController@loginPage');
