<?php

namespace App\Controllers;

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Helpers/DB.php';

use App\Helpers\DB;
use AuthMiddleware;

class MessagesController
{
    public function __construct()
    {
        AuthMiddleware::handle();
    }

    /**
     * Resolve a user row by UUID. Returns array or null.
     */
    private function getUserByUuid(?string $uuid): ?array
    {
        if (!$uuid) return null;
        $pdo = DB::pdo();
        $stmt = $pdo->prepare("SELECT id, uuid, full_name FROM users WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $uuid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }

    /**
     * GET /messages or /messages/{user_uuid}
     * - If $userUuid === 'compose', read ?to={uuid} and use that as receiver.
     * - Else, $userUuid is the receiver’s uuid.
     */
    public function thread(string $userUuid = ''): void
    {
        $pdo = DB::pdo();

        // current user id
        $me = $_SESSION['user']['id'] ?? null;
        if (!$me) {
            http_response_code(401);
            echo "Unauthorized";
            return;
        }

        // Determine target user (“them”)
        if ($userUuid === 'compose') {
            $toParam = isset($_GET['to']) ? trim((string)$_GET['to']) : '';
            $them = $this->getUserByUuid($toParam);
            if (!$them) {
                http_response_code(400);
                echo "Invalid or missing 'to' parameter.";
                return;
            }
        } else {
            $them = $this->getUserByUuid($userUuid);
            if (!$them) {
                http_response_code(404);
                echo "User not found.";
                return;
            }
        }

        // Load the conversation (both directions)
        $sql = "
            SELECT m.id, m.sender_id, m.receiver_id, m.message, m.seen, m.created_at
            FROM messages m
            WHERE (m.sender_id = :me AND m.receiver_id = :them)
               OR (m.sender_id = :them AND m.receiver_id = :me)
            ORDER BY m.created_at ASC, m.id ASC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':me'   => $me,
            ':them' => $them['id'],
        ]);
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Mark as seen for messages sent to me
        $upd = $pdo->prepare("
            UPDATE messages SET seen = 1
            WHERE receiver_id = :me AND sender_id = :them AND seen = 0
        ");
        $upd->execute([':me' => $me, ':them' => $them['id']]);

        // Render your view (adjust to your view layer)
        require __DIR__ . '/../Views/Messages/Thread.php';
    }

    /**
     * POST /messages/{user_uuid}
     * - Handles both /messages/{uuid} and /messages/compose?to={uuid}
     */
    public function send(string $userUuid = ''): void
    {
        $pdo = DB::pdo();

        $me = $_SESSION['user']['id'] ?? null;
        if (!$me) {
            http_response_code(401);
            echo "Unauthorized";
            return;
        }

        // Resolve target
        if ($userUuid === 'compose') {
            $toParam = isset($_GET['to']) ? trim((string)$_GET['to']) : '';
            $them = $this->getUserByUuid($toParam);
        } else {
            $them = $this->getUserByUuid($userUuid);
        }
        if (!$them) {
            http_response_code(400);
            echo "Invalid recipient.";
            return;
        }

        $content = isset($_POST['message']) ? trim((string)$_POST['message']) : '';
        if ($content === '') {
            // Redirect back with an error or re-render
            http_response_code(422);
            echo "Message cannot be empty.";
            return;
        }

        $ins = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, message, created_at)
            VALUES (:s, :r, :m, NOW())
        ");
        $ins->execute([
            ':s' => $me,
            ':r' => $them['id'],
            ':m' => $content,
        ]);

        // Redirect back to the thread
        $redirectUuid = $them['uuid'];
        header("Location: /messages/{$redirectUuid}");
        exit;
    }

    /**
     * GET /messages
     * Simple inbox listing, if you have one.
     */
    public function index(): void
    {
        $pdo = DB::pdo();
        $me = $_SESSION['user']['id'] ?? null;
        if (!$me) {
            http_response_code(401);
            echo "Unauthorized";
            return;
        }

        // Example inbox query (distinct counterparts)
        $sql = "
            SELECT
                u.uuid as user_uuid,
                u.full_name,
                MAX(m.created_at) as last_at,
                SUBSTRING_INDEX(
                    SUBSTRING(
                        GROUP_CONCAT(m.message ORDER BY m.created_at DESC SEPARATOR '\n'),
                        1, 200
                    ), '\n', 1
                ) as last_message
            FROM messages m
            JOIN users u
              ON u.id = CASE
                WHEN m.sender_id = :me THEN m.receiver_id
                ELSE m.sender_id
              END
            WHERE m.sender_id = :me OR m.receiver_id = :me
            GROUP BY u.id, u.uuid, u.full_name
            ORDER BY last_at DESC
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':me' => $me]);
        $threads = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/Messages/Inbox.php';
    }
}
