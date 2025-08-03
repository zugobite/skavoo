<?php

/**
 * FeedController
 *
 * Handles feed-related routes such as displaying
 * the user feed.
 *
 * @package Skavoo\Controllers
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class FeedController
{
    /**
     * Display the user feed.
     *
     * This method retrieves posts from the database and includes
     * the feed view to display them.
     *
     * @return void
     */
    public function index()
    {
        AuthMiddleware::handle();
        global $pdo;

        $stmt = $pdo->prepare("
        SELECT posts.*, users.full_name, users.uuid, users.profile_picture
        FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC
    ");
        $stmt->execute();
        $posts = $stmt->fetchAll();

        $current_user_id = $_SESSION['user_id'];

        $peopleStmt = $pdo->prepare("
        SELECT id, uuid, profile_picture, full_name
        FROM users
        WHERE id != :me
        ORDER BY RAND()
        LIMIT 10
    ");
        $peopleStmt->execute(['me' => $current_user_id]);
        $people = $peopleStmt->fetchAll();

        include '../app/Views/Feed.php';
    }
}
