<?php
/**
 * Router
 *
 * A simple HTTP router class that maps incoming GET and POST requests
 * to controller methods. It supports basic route registration and dispatching.
 *
 * @package Skavoo\Core
 */
class Router
{
    /**
     * Stores registered routes organized by request method.
     *
     * @var array
     */
    private array $routes = [];

    /**
     * Register a GET route.
     *
     * @param string $uri The URI path (e.g., '/login')
     * @param callable|string $callback The controller action or closure
     * @return void
     */
    public function get(string $uri, $callback): void
    {
        $this->routes['GET'][$uri] = $callback;
    }

    /**
     * Register a POST route.
     *
     * @param string $uri The URI path (e.g., '/login')
     * @param callable|string $callback The controller action or closure
     * @return void
     */
    public function post(string $uri, $callback): void
    {
        $this->routes['POST'][$uri] = $callback;
    }

    /**
     * Match the incoming HTTP request to a registered route
     * and dispatch the associated controller method.
     *
     * @return void
     */
    public function dispatch(): void
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        $callback = $this->routes[$requestMethod][$requestUri] ?? null;

        if (!$callback) {
            http_response_code(404);
            echo "404 Not Found";
            return;
        }

        $this->callAction($callback);
    }

    /**
     * Resolve the callback into a controller and method, or run a closure.
     *
     * @param callable|string $callback Either a closure or "Controller@method" string
     * @return void
     */
    private function callAction($callback): void
    {
        if (is_string($callback)) {
            list($controllerName, $method) = explode('@', $callback);

            require_once "../app/Controllers/{$controllerName}.php";

            $controller = new $controllerName();
            call_user_func([$controller, $method]);
        } else {
            call_user_func($callback);
        }
    }
}
