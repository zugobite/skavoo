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

        // Search by full_name, email, or display_name (case-insensitive)
        $stmt = $pdo->prepare("
            SELECT uuid, full_name, display_name, email, profile_picture
            FROM users
            WHERE (full_name LIKE :query1 OR email LIKE :query2 OR (display_name IS NOT NULL AND display_name LIKE :query3))
            ORDER BY 
                CASE 
                    WHEN full_name LIKE :exact1 OR (display_name IS NOT NULL AND display_name LIKE :exact2) THEN 1
                    WHEN full_name LIKE :starts1 OR (display_name IS NOT NULL AND display_name LIKE :starts2) THEN 2
                    ELSE 3
                END,
                full_name ASC
            LIMIT 10
        ");

        $queryPattern = '%' . $query . '%';
        $exactPattern = $query;
        $startsPattern = $query . '%';

        $stmt->execute([
            'query1' => $queryPattern,
            'query2' => $queryPattern,
            'query3' => $queryPattern,
            'exact1' => $exactPattern,
            'exact2' => $exactPattern,
            'starts1' => $startsPattern,
            'starts2' => $startsPattern
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($results);
    }
}
