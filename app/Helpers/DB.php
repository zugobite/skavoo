<?php

namespace App\Helpers;

use PDO;
use RuntimeException;

/**
 * Database Helper Class
 *
 * Provides a shared PDO database connection accessible via DB::pdo().
 * Internally uses the same config and environment logic as before.
 *
 * Also includes the legacy global function db() for backward compatibility.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class DB
{
    /**
     * Cached PDO instance.
     *
     * @var PDO|null
     */
    private static ?PDO $pdoRef = null;

    /**
     * Get (and cache) a shared PDO database connection instance.
     *
     * @throws RuntimeException If the PDO instance is not initialized properly.
     * @return PDO
     */
    public static function pdo(): PDO
    {
        // Return cached connection if already initialized
        if (self::$pdoRef instanceof PDO) {
            return self::$pdoRef;
        }

        // Load environment and configuration files
        require_once __DIR__ . '/env.php';
        require_once dirname(__DIR__, 2) . '/config/database.php'; // defines global $pdo

        // Access the globally defined PDO instance
        global $pdo;

        // Verify initialization
        if (!($pdo instanceof PDO)) {
            throw new RuntimeException('PDO not initialized by config/database.php');
        }

        // Configure PDO options
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Cache and return
        self::$pdoRef = $pdo;
        return self::$pdoRef;
    }
}

/**
 * --------------------------------------------------------------------------
 * Backwards Compatibility (optional)
 * --------------------------------------------------------------------------
 * Retains the original global db() function for any existing procedural code.
 */

if (!function_exists('db')) {
    /**
     * Returns the shared PDO database connection (alias for DB::pdo()).
     *
     * @return PDO
     */
    function db(): PDO
    {
        return DB::pdo();
    }
}
