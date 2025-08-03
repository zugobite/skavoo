<?php

/**
 * SearchController
 *
 * Handles search-related routes such as displaying
 * search results.
 *
 * @package Skavoo\Controllers
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class SearchController
{
    /**
     * Handles the search lookup functionality.
     * Searches for users by full name or email based on the query.
     * Returns a JSON response with the search results.
     */
    public function lookup()
    {
        AuthMiddleware::handle();
        global $pdo;

        // Get the query from GET parameters
        $query = isset($_GET['q']) ? trim($_GET['q']) : '';

        if (empty($query)) {
            echo json_encode([]);
            return;
        }

        // Search by full_name or email (case-insensitive)
        $stmt = $pdo->prepare("
            SELECT uuid, full_name, email
            FROM users
            WHERE full_name LIKE :query OR email LIKE :query
            LIMIT 10
        ");

        $stmt->execute([
            'query' => '%' . $query . '%'
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($results);
    }
}
