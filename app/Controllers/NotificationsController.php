<?php

/**
 * NotificationsController
 *
 * Handles notification-related functionality including displaying
 * and marking notifications as read.
 *
 * @package Skavoo\Controllers
 */

namespace App\Controllers;

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Helpers/DB.php';
require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\DB;
use AuthMiddleware;

class NotificationsController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Get notifications for the current user (JSON API).
     *
     * GET /api/notifications
     *
     * @return void
     */
    public function getNotifications(): void
    {
        AuthMiddleware::handle();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];

        // Get recent notifications
        $stmt = $pdo->prepare("
            SELECT n.*, u.full_name as actor_name, u.profile_picture as actor_picture, u.uuid as actor_uuid
            FROM notifications n
            LEFT JOIN users u ON (
                CASE 
                    WHEN n.type = 'friend_request' THEN JSON_UNQUOTE(JSON_EXTRACT(n.content, '$.sender_id'))
                    WHEN n.type = 'friend_accepted' THEN JSON_UNQUOTE(JSON_EXTRACT(n.content, '$.accepter_id'))
                    WHEN n.type = 'like' THEN JSON_UNQUOTE(JSON_EXTRACT(n.content, '$.liker_id'))
                    WHEN n.type = 'comment' THEN JSON_UNQUOTE(JSON_EXTRACT(n.content, '$.commenter_id'))
                    ELSE NULL
                END = u.id
            )
            WHERE n.user_id = :user_id
            ORDER BY n.created_at DESC
            LIMIT 20
        ");
        $stmt->execute([':user_id' => $userId]);
        $notifications = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Count unread
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = FALSE");
        $stmt->execute([':user_id' => $userId]);
        $unreadCount = $stmt->fetchColumn();

        // Format notifications
        $formatted = [];
        foreach ($notifications as $n) {
            $content = json_decode($n['content'], true) ?: [];
            $formatted[] = [
                'id' => (int)$n['id'],
                'type' => $n['type'],
                'message' => $content['message'] ?? '',
                'actor_name' => $n['actor_name'] ?? 'Someone',
                'actor_picture' => \App\Helpers\profilePicturePath($n['actor_picture'] ?? null),
                'actor_uuid' => $n['actor_uuid'] ?? '',
                'is_read' => (bool)$n['is_read'],
                'created_at' => $n['created_at'],
                'time_ago' => $this->timeAgo($n['created_at'])
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'notifications' => $formatted,
            'unread_count' => (int)$unreadCount
        ]);
        exit;
    }

    /**
     * Mark all notifications as read.
     *
     * POST /api/notifications/read
     *
     * @return void
     */
    public function markAllRead(): void
    {
        AuthMiddleware::handle();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Mark a single notification as read.
     *
     * POST /api/notifications/{id}/read
     *
     * @param string $id
     * @return void
     */
    public function markRead(string $id): void
    {
        AuthMiddleware::handle();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];
        $notificationId = (int)$id;

        $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE id = :id AND user_id = :user_id");
        $stmt->execute([':id' => $notificationId, ':user_id' => $userId]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Get unread notification count.
     *
     * GET /api/notifications/count
     *
     * @return void
     */
    public function getCount(): void
    {
        AuthMiddleware::handle();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = FALSE");
        $stmt->execute([':user_id' => $userId]);
        $count = $stmt->fetchColumn();

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => (int)$count
        ]);
        exit;
    }

    /**
     * Helper function to format time ago.
     */
    private function timeAgo(string $datetime): string
    {
        $time = strtotime($datetime);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return date('M j', $time);
        }
    }
}
