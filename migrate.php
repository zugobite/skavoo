<?php
/**
 * Migration Runner Script
 *
 * This script automates the execution of all SQL migration files located in the
 * `/database/migrations` directory. Each `.sql` file is executed in sorted order
 * (alphabetically), ensuring that migrations run in a predictable and consistent
 * sequence.
 *
 * It uses the `DB` helper class to establish a PDO connection to the configured
 * MySQL database and executes each SQL statement contained in the migration files.
 *
 * Usage (from terminal):
 *   php migrate.php
 *
 * @package Skavoo
 * @category Database
 * @version 1.0.0
 */

require_once __DIR__ . '/app/Helpers/DB.php';

use App\Helpers\DB;

/**
 * Establish a PDO database connection using the custom DB helper.
 *
 * @var PDO $pdo The PDO connection instance to the application's database.
 */
$pdo = DB::pdo();

/**
 * Directory path containing SQL migration files.
 *
 * @var string $dir
 */
$dir = __DIR__ . '/database/migrations';

/**
 * Retrieve all `.sql` files from the migration directory and sort them.
 *
 * Sorting ensures migrations execute in the correct sequence
 * (usually chronological or incremental naming convention).
 *
 * @var array $files List of sorted SQL migration file paths.
 */
$files = glob($dir . '/*.sql');
sort($files);

echo "Running migrations...\n";

/**
 * Loop through all migration files and execute their SQL content.
 *
 * For each file:
 * - Display its name in the console.
 * - Read and execute the SQL statements.
 * - Catch and log any PDO exceptions that occur during execution.
 *
 * @throws PDOException If an SQL statement fails to execute.
 */
foreach ($files as $file) {
    echo "- " . basename($file) . "\n";
    $sql = file_get_contents($file);

    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        echo "  Error: " . $e->getMessage() . "\n";
    }
}

echo "All migrations attempted.\n";