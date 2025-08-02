<?php
/**
 * Database Configuration
 *
 * This file establishes a connection to the MySQL database using PDO.
 * It sets up the DSN (Data Source Name) and handles connection errors gracefully.
 *
 * @package Skavoo
 * @see https://www.php.net/manual/en/book.pdo.php
 */

// Database host (typically 'localhost' for local development)
$host = 'localhost';

// Name of the database
$db = 'social_db';

// MySQL username
$user = 'root';

// MySQL password (leave empty for default MAMP/XAMPP installations)
$pass = '';

// Create DSN (Data Source Name) for PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=UTF8";

try {
    /**
     * Create a new PDO instance for database interaction.
     *
     * @var PDO $pdo Database connection instance
     */
    $pdo = new PDO($dsn, $user, $pass);

    // Optional: Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    /**
     * Handle connection error by outputting the error message.
     * In production, consider logging this instead of displaying it.
     */
    echo 'Connection failed: ' . $e->getMessage();
}
