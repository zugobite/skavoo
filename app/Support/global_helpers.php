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

if (!function_exists('profilePicturePath')) {
    /**
     * Global wrapper for profile picture path helper.
     *
     * @param string|null $profilePicture
     * @param string $default
     * @return string
     */
    function profilePicturePath(?string $profilePicture = null, string $default = '/images/avatar-default.svg'): string
    {
        return \App\Helpers\profilePicturePath($profilePicture, $default);
    }
}

if (!function_exists('timeAgo')) {
    /**
     * Format a datetime as a relative time string.
     *
     * @param string $datetime
     * @return string
     */
    function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}
