<?php

namespace App\Controllers;

use App\Helpers\Csrf;
use App\Helpers\DB;
use AuthMiddleware;
use PDO;

/**
 * Handles private messaging (1-to-1).
 */
class MessagesController
{
    /**
     * Enforce authentication for all actions.
     */
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    /**
     * Inbox: show latest message per conversation partner.
     *
     * @return void
     */
    public function index()
    {
        $pdo = DB::pdo();
        $me  = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $sql = "
            SELECT m.*,
                   CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END AS partner_id,
                   u.full_name AS partner_name,
                   u.uuid AS partner_uuid,
                   u.profile_picture AS partner_avatar
            FROM messages m
            JOIN users u ON u.id = CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END
            WHERE (m.sender_id = :me OR m.receiver_id = :me)
              AND m.id IN (
                SELECT MAX(id) FROM messages
                WHERE sender_id = :me OR receiver_id = :me
                GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
              )
            ORDER BY m.created_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':me' => $me]);
        $threads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('Messages/Inbox', ['threads' => $threads]);
    }

    /**
     * Open/start a thread with a specific user identified by UUID.
     *
     * @param array $params route params
     * @return void
     */
    public function thread($params)
    {
        $pdo = DB::pdo();
        $me  = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Load target user
        $userStmt = $pdo->prepare("SELECT id, uuid, full_name, profile_picture FROM users WHERE uuid = :uuid LIMIT 1");
        $userStmt->execute([':uuid' => $params['user_uuid']]);
        $them = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$them) {
            http_response_code(404);
            return view('Errors/404', ['message' => 'User not found']);
        }

        // Load message history (only visible to participants)
        $msgSql = "
            SELECT m.*,
                   s.full_name AS sender_name, s.profile_picture AS sender_avatar,
                   r.full_name AS receiver_name, r.profile_picture AS receiver_avatar
            FROM messages m
            JOIN users s ON s.id = m.sender_id
            JOIN users r ON r.id = m.receiver_id
            WHERE (m.sender_id = :me AND m.receiver_id = :them)
               OR (m.sender_id = :them AND m.receiver_id = :me)
            ORDER BY m.created_at ASC
        ";
        $stmt = $pdo->prepare($msgSql);
        $stmt->execute([':me' => $me, ':them' => $them['id']]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        view('Messages/Thread', [
            'partner'  => $them,
            'messages' => $messages,
            'csrf'     => Csrf::token()
        ]);
    }

    /**
     * Send a message in a thread to user UUID.
     *
     * @param array $params route params
     * @return void
     */
    public function send($params)
    {
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $me  = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Validate partner existence
        $userStmt = $pdo->prepare("SELECT id FROM users WHERE uuid = :uuid LIMIT 1");
        $userStmt->execute([':uuid' => $params['user_uuid']]);
        $them = $userStmt->fetch(PDO::FETCH_ASSOC);

        if (!$them) {
            $_SESSION['flash_error'] = 'User not found.';
            return redirect('/messages');
        }

        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if ($content === '' || mb_strlen($content) > 2000) {
            $_SESSION['flash_error'] = 'Message must be between 1 and 2000 characters.';
            return redirect('/messages/' . $params['user_uuid']);
        }

        $ins = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content, created_at) VALUES (:s, :r, :c, NOW())");
        $ins->execute([
            ':s' => $me,
            ':r' => $them['id'],
            ':c' => $content
        ]);

        return redirect('/messages/' . $params['user_uuid']);
    }
}
