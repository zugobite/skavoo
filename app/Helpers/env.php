<?php

/**
 * .env Loader for Skavoo Project
 *
 * This script reads key-value pairs from a `.env` file and sets them
 * as environment variables using `putenv()`.
 *
 * Expected format in `.env`:
 * DB_HOST=your_host
 * DB_USER=your_user
 * DB_PASS=your_password
 *
 * Comments starting with '#' are ignored.
 * Empty lines are skipped.
 *
 * @package Skavoo/Helpers
 * @see https://www.php.net/manual/en/function.getenv.php
 */

// Define path to the .env file
$envFile = __DIR__ . '/../.env';

/**
 * Load environment variables from the .env file if it exists.
 */
if (file_exists($envFile)) {
    // Read all lines, ignoring empty ones
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Loop through each line
    foreach ($lines as $line) {
        // Skip comment lines
        if (strpos(trim($line), '#') === 0) continue;

        // Split the line into key and value
        list($key, $value) = explode('=', $line, 2);

        // Set as environment variable
        putenv(trim($key) . '=' . trim($value));
    }
}
