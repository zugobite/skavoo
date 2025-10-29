<?php

/**
 * Global helper shims for controllers & views.
 * These live in the ROOT namespace (no namespace) so that calls like view() and redirect() resolve.
 */

if (!function_exists('view')) {
    /**
     * Render a view with data.
     *
     * @param string $path  Dot- or slash-based path relative to app/Views (e.g., 'Messages/Inbox')
     * @param array  $data
     * @return void
     */
    function view($path, array $data = [])
    {
        // allow both dot and slash notation
        $file = str_replace(['.', '\\'], ['/', '/'], $path);
        $full = __DIR__ . '/../Views/' . $file . '.php';

        if (!is_file($full)) {
            http_response_code(500);
            echo "View not found: " . htmlspecialchars($full, ENT_QUOTES, 'UTF-8');
            exit;
        }

        // expose $data as variables
        extract($data, EXTR_OVERWRITE);
        include $full;
    }
}

if (!function_exists('redirect')) {
    /**
     * Simple redirect helper.
     *
     * @param string $path
     * @return void
     */
    function redirect($path)
    {
        if (!headers_sent()) {
            header('Location: ' . $path, true, 302);
        } else {
            echo '<script>window.location.href=' . json_encode($path) . ';</script>';
        }
        exit;
    }
}
