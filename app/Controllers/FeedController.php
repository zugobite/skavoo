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
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\Csrf;

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

        $current_user_id = $_SESSION['user_id'];

        // Get posts with like counts and user's like status
        $stmt = $pdo->prepare("
            SELECT 
                posts.*, 
                users.full_name, 
                users.uuid, 
                users.profile_picture,
                (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count,
                (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) as comment_count,
                (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = :user_id) as user_liked
            FROM posts
            JOIN users ON posts.user_id = users.id
            WHERE posts.visibility = 'public'
               OR posts.user_id = :user_id
               OR (posts.visibility = 'friends' AND EXISTS (
                   SELECT 1 FROM friends 
                   WHERE status = 'accepted' 
                   AND ((sender_id = :user_id AND receiver_id = posts.user_id)
                        OR (sender_id = posts.user_id AND receiver_id = :user_id))
               ))
            ORDER BY posts.created_at DESC
            LIMIT 50
        ");
        $stmt->execute(['user_id' => $current_user_id]);
        $posts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get comments for each post (limited to 3 most recent)
        foreach ($posts as &$post) {
            $commentStmt = $pdo->prepare("
                SELECT comments.*, users.full_name, users.uuid, users.profile_picture
                FROM comments
                JOIN users ON comments.user_id = users.id
                WHERE comments.post_id = :post_id
                ORDER BY comments.created_at DESC
                LIMIT 3
            ");
            $commentStmt->execute(['post_id' => $post['id']]);
            $post['comments'] = array_reverse($commentStmt->fetchAll(\PDO::FETCH_ASSOC));
        }
        unset($post);

        // People you may know (not already friends)
        $peopleStmt = $pdo->prepare("
            SELECT id, uuid, profile_picture, full_name
            FROM users
            WHERE id != :me
            AND id NOT IN (
                SELECT receiver_id FROM friends WHERE sender_id = :me AND status = 'accepted'
                UNION
                SELECT sender_id FROM friends WHERE receiver_id = :me AND status = 'accepted'
            )
            ORDER BY RAND()
            LIMIT 5
        ");
        $peopleStmt->execute(['me' => $current_user_id]);
        $people = $peopleStmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get pending friend request count for header
        $pendingStmt = $pdo->prepare("
            SELECT COUNT(*) FROM friends WHERE receiver_id = :me AND status = 'pending'
        ");
        $pendingStmt->execute(['me' => $current_user_id]);
        $pendingFriendRequests = $pendingStmt->fetchColumn();

        // CSRF token for forms
        $csrf = Csrf::token();

        include '../app/Views/Feed.php';
    }
}
