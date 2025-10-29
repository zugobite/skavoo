<?php

/**
 * Entry point for the application.
 * 
 * This file acts as the front controller, handling all incoming HTTP requests.
 * It initializes the router, loads route definitions, and dispatches requests
 * to the appropriate controller actions.
 *
 * PHP version 8+
 *
 * @package Skavoo
 * @author  Zascia Hugo
 * @license MIT
 */

require_once __DIR__ . '/../app/Core/Router.php';
require_once __DIR__ . '/../app/Support/global_helpers.php';
require_once __DIR__ . '/../app/Support/namespace_aliases.php';
require_once __DIR__ . '/../app/Support/app_facades.php';

// Instantiate the Router
$router = new Router();

/**
 * Load application routes.
 * 
 * This file maps URIs to specific controller methods using the Router instance.
 */
require_once '../routes/web.php';

// Dispatch the current request to the appropriate controller and method
$router->dispatch();
