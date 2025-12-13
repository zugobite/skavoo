<?php

/**
 * FriendsController
 *
 * Handles all friend-related functionality including sending,
 * accepting, rejecting, and canceling friend requests.
 *
 * @package Skavoo\Controllers
 */

namespace App\Controllers;

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Helpers/DB.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\DB;
use App\Helpers\Csrf;
use AuthMiddleware;

class FriendsController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Send a friend request to another user.
     *
     * POST /friends/send
     *
     * @return void
     */
    public function send(): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $senderId = $_SESSION['user_id'];
        $receiverId = (int)($_POST['receiver_id'] ?? 0);

        if ($receiverId <= 0 || $receiverId === $senderId) {
            $_SESSION['flash_error'] = 'Invalid friend request.';
            $this->redirectBack();
            return;
        }

        // Check if receiver exists
        $stmt = $pdo->prepare("SELECT id, uuid FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $receiverId]);
        $receiver = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$receiver) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirectBack();
            return;
        }

        // Check if friendship already exists (in either direction)
        $stmt = $pdo->prepare("
            SELECT id, status FROM friends 
            WHERE (sender_id = :sender AND receiver_id = :receiver)
               OR (sender_id = :receiver AND receiver_id = :sender)
            LIMIT 1
        ");
        $stmt->execute([':sender' => $senderId, ':receiver' => $receiverId]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            if ($existing['status'] === 'accepted') {
                $_SESSION['flash_error'] = 'You are already friends.';
            } elseif ($existing['status'] === 'pending') {
                $_SESSION['flash_error'] = 'A friend request already exists.';
            } else {
                $_SESSION['flash_error'] = 'Cannot send friend request at this time.';
            }
            header('Location: /user/profile/' . urlencode($receiver['uuid']));
            exit;
        }

        // Create friend request
        $stmt = $pdo->prepare("
            INSERT INTO friends (sender_id, receiver_id, status, requested_at)
            VALUES (:sender, :receiver, 'pending', CURRENT_TIMESTAMP)
        ");
        $stmt->execute([':sender' => $senderId, ':receiver' => $receiverId]);

        // Create notification for receiver
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, content, is_read, created_at)
            VALUES (:user_id, 'friend_request', :content, FALSE, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            ':user_id' => $receiverId,
            ':content' => json_encode([
                'sender_id' => $senderId,
                'sender_name' => $_SESSION['display_name'] ?? 'Someone',
                'message' => 'sent you a friend request'
            ])
        ]);

        $_SESSION['flash_success'] = 'Friend request sent!';
        header('Location: /user/profile/' . urlencode($receiver['uuid']));
        exit;
    }

    /**
     * Accept a friend request.
     *
     * POST /friends/accept
     *
     * @return void
     */
    public function accept(): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $myId = $_SESSION['user_id'];
        $requestId = (int)($_POST['request_id'] ?? 0);

        if ($requestId <= 0) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirectBack();
            return;
        }

        // Verify this request is to me and is pending
        $stmt = $pdo->prepare("
            SELECT f.*, u.uuid as sender_uuid, u.full_name as sender_name
            FROM friends f
            JOIN users u ON u.id = f.sender_id
            WHERE f.id = :id AND f.receiver_id = :me AND f.status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([':id' => $requestId, ':me' => $myId]);
        $request = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$request) {
            $_SESSION['flash_error'] = 'Friend request not found or already handled.';
            $this->redirectBack();
            return;
        }

        // Accept the request
        $stmt = $pdo->prepare("
            UPDATE friends 
            SET status = 'accepted', responded_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");
        $stmt->execute([':id' => $requestId]);

        // Notify the sender
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, content, is_read, created_at)
            VALUES (:user_id, 'friend_accepted', :content, FALSE, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            ':user_id' => $request['sender_id'],
            ':content' => json_encode([
                'accepter_id' => $myId,
                'message' => 'accepted your friend request'
            ])
        ]);

        $_SESSION['flash_success'] = 'You are now friends with ' . htmlspecialchars($request['sender_name']) . '!';
        $this->redirectBack();
    }

    /**
     * Reject a friend request.
     *
     * POST /friends/reject
     *
     * @return void
     */
    public function reject(): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $myId = $_SESSION['user_id'];
        $requestId = (int)($_POST['request_id'] ?? 0);

        if ($requestId <= 0) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirectBack();
            return;
        }

        // Verify this request is to me and is pending
        $stmt = $pdo->prepare("
            SELECT id FROM friends 
            WHERE id = :id AND receiver_id = :me AND status = 'pending'
            LIMIT 1
        ");
        $stmt->execute([':id' => $requestId, ':me' => $myId]);
        
        if (!$stmt->fetch()) {
            $_SESSION['flash_error'] = 'Friend request not found or already handled.';
            $this->redirectBack();
            return;
        }

        // Delete the request (or update to 'rejected' if you want to keep history)
        $stmt = $pdo->prepare("DELETE FROM friends WHERE id = :id");
        $stmt->execute([':id' => $requestId]);

        $_SESSION['flash_success'] = 'Friend request declined.';
        $this->redirectBack();
    }

    /**
     * Cancel a sent friend request.
     *
     * POST /friends/cancel
     *
     * @return void
     */
    public function cancel(): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $myId = $_SESSION['user_id'];
        $receiverId = (int)($_POST['receiver_id'] ?? 0);

        if ($receiverId <= 0) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirectBack();
            return;
        }

        // Get receiver's UUID for redirect
        $stmt = $pdo->prepare("SELECT uuid FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $receiverId]);
        $receiver = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Delete the pending request I sent
        $stmt = $pdo->prepare("
            DELETE FROM friends 
            WHERE sender_id = :me AND receiver_id = :receiver AND status = 'pending'
        ");
        $stmt->execute([':me' => $myId, ':receiver' => $receiverId]);

        $_SESSION['flash_success'] = 'Friend request cancelled.';
        
        if ($receiver) {
            header('Location: /user/profile/' . urlencode($receiver['uuid']));
        } else {
            $this->redirectBack();
        }
        exit;
    }

    /**
     * Unfriend a user.
     *
     * POST /friends/remove
     *
     * @return void
     */
    public function remove(): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $myId = $_SESSION['user_id'];
        $friendId = (int)($_POST['friend_id'] ?? 0);

        if ($friendId <= 0) {
            $_SESSION['flash_error'] = 'Invalid request.';
            $this->redirectBack();
            return;
        }

        // Get friend's UUID for redirect
        $stmt = $pdo->prepare("SELECT uuid FROM users WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $friendId]);
        $friend = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Delete the friendship (in either direction)
        $stmt = $pdo->prepare("
            DELETE FROM friends 
            WHERE status = 'accepted'
              AND ((sender_id = :me AND receiver_id = :friend)
                OR (sender_id = :friend AND receiver_id = :me))
        ");
        $stmt->execute([':me' => $myId, ':friend' => $friendId]);

        $_SESSION['flash_success'] = 'Friend removed.';
        
        if ($friend) {
            header('Location: /user/profile/' . urlencode($friend['uuid']));
        } else {
            $this->redirectBack();
        }
        exit;
    }

    /**
     * Show pending friend requests (incoming).
     *
     * GET /friends/requests
     *
     * @return void
     */
    public function requests(): void
    {
        AuthMiddleware::handle();

        $pdo = DB::pdo();
        $myId = $_SESSION['user_id'];

        // Get incoming requests
        $stmt = $pdo->prepare("
            SELECT f.id as request_id, f.requested_at, u.id, u.uuid, u.full_name, u.profile_picture
            FROM friends f
            JOIN users u ON u.id = f.sender_id
            WHERE f.receiver_id = :me AND f.status = 'pending'
            ORDER BY f.requested_at DESC
        ");
        $stmt->execute([':me' => $myId]);
        $incomingRequests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get outgoing requests
        $stmt = $pdo->prepare("
            SELECT f.id as request_id, f.requested_at, u.id, u.uuid, u.full_name, u.profile_picture
            FROM friends f
            JOIN users u ON u.id = f.receiver_id
            WHERE f.sender_id = :me AND f.status = 'pending'
            ORDER BY f.requested_at DESC
        ");
        $stmt->execute([':me' => $myId]);
        $outgoingRequests = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $csrf = Csrf::token();

        require __DIR__ . '/../Views/Friends/Requests.php';
    }

    /**
     * Show all friends.
     *
     * GET /friends
     *
     * @return void
     */
    public function index(): void
    {
        AuthMiddleware::handle();

        $pdo = DB::pdo();
        $myId = $_SESSION['user_id'];

        $stmt = $pdo->prepare("
            SELECT u.id, u.uuid, u.full_name, u.profile_picture, f.responded_at as friends_since
            FROM friends f
            JOIN users u ON (
                (f.sender_id = :me AND u.id = f.receiver_id)
                OR (f.receiver_id = :me AND u.id = f.sender_id)
            )
            WHERE f.status = 'accepted'
            ORDER BY u.full_name ASC
        ");
        $stmt->execute([':me' => $myId]);
        $friends = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Count pending requests for badge
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM friends WHERE receiver_id = :me AND status = 'pending'
        ");
        $stmt->execute([':me' => $myId]);
        $pendingCount = $stmt->fetchColumn();

        $csrf = Csrf::token();

        require __DIR__ . '/../Views/Friends/Index.php';
    }

    /**
     * Redirect back to the previous page.
     *
     * @return void
     */
    private function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/feed';
        header('Location: ' . $referer);
        exit;
    }
}
