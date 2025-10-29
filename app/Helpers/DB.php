<?php

/**
 * Database Helper Functions
 *
 * This file provides a utility function for managing and retrieving
 * a persistent PDO database connection. It ensures that the same
 * PDO instance is reused across the application for efficiency.
 */

/**
 * Returns a shared PDO database connection instance.
 *
 * This function initializes and caches a PDO connection using configuration
 * details provided in `config/database.php` and environment variables loaded
 * from `env.php`. The PDO instance is configured to:
 * - Throw exceptions on errors (`PDO::ERRMODE_EXCEPTION`)
 * - Fetch associative arrays by default (`PDO::FETCH_ASSOC`)
 *
 * @throws RuntimeException If the PDO instance is not initialized properly.
 *
 * @return PDO The shared PDO database connection.
 */
function db(): PDO
{
    static $pdoRef = null;

    // Return the existing connection if already initialized
    if ($pdoRef instanceof PDO) {
        return $pdoRef;
    }

    // Load environment and configuration files
    require_once __DIR__ . '/env.php';
    require_once dirname(__DIR__, 2) . '/config/database.php'; // sets global $pdo

    // Access the globally defined PDO instance
    global $pdo;

    // Ensure the PDO instance was correctly initialized
    if (!($pdo instanceof PDO)) {
        throw new RuntimeException('PDO not initialized by config/database.php');
    }

    // Configure PDO error handling and fetch mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Cache and return the PDO reference
    $pdoRef = $pdo;
    return $pdoRef;
}
