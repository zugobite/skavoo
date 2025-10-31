<?php

/**
 * MessagesController
 *
 * Handles the messaging inbox, conversation threads, and sending messages.
 *
 * Routes (defined in routes/web.php):
 *  - GET  /messages                 -> index()
 *  - GET  /messages/{user_uuid}     -> thread($userUuid)
 *  - POST /messages/{user_uuid}     -> send($userUuid)
 *
 * Security:
 *  - Requires authentication via AuthMiddleware.
 *  - CSRF protection on POST send().
 *
 * DB schema (relevant columns):
 *  - messages: id, sender_id, receiver_id, message (TEXT), seen (BOOLEAN/TINYINT), created_at, updated_at
 */

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Helpers/DB.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\DB;
use App\Helpers\Csrf;
use function App\Helpers\e;

class MessagesController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * GET /messages
     * Show recent conversations (inbox).
     *
     * Lists distinct conversations for the logged-in user, showing the latest
     * message preview, timestamp, and an unread counter per counterpart.
     */
    public function index(): void
    {
        AuthMiddleware::handle();
        $pdo = DB::pdo();

        $meId = (int)($_SESSION['user_id'] ?? 0);
        if ($meId <= 0) {
            header('Location: /login');
            exit;
        }

        // Derived-table approach (no CTE) to fetch the latest message per counterpart
        $sql = "
            SELECT
                u.id   AS other_id,
                u.uuid AS other_uuid,
                COALESCE(NULLIF(u.display_name,''), u.full_name) AS other_name,
                u.profile_picture,
                lm.message    AS last_message,
                lm.created_at AS last_time,
                (
                    SELECT COUNT(*)
                    FROM messages mu
                    WHERE mu.receiver_id = :me
                      AND mu.sender_id   = u.id
                      AND mu.seen = 0
                ) AS unread_count
            FROM users u
            JOIN (
                SELECT
                    CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END AS other_id,
                    MAX(m.created_at) AS last_time
                FROM messages m
                WHERE (m.sender_id = :me OR m.receiver_id = :me)
                GROUP BY other_id
            ) t ON t.other_id = u.id
            JOIN messages lm
              ON (
                   (lm.sender_id = :me AND lm.receiver_id = u.id) OR
                   (lm.sender_id = u.id AND lm.receiver_id = :me)
                 )
             AND lm.created_at = t.last_time
            ORDER BY last_time DESC
            LIMIT 50
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':me' => $meId]);
        $convos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        require __DIR__ . '/../Views/Messages/Index.php';
    }

    /**
     * GET /messages/{user_uuid}
     * Open the 1:1 conversation thread with a specific user.
     *
     * @param string $userUuid
     */
    public function thread(string $userUuid): void
    {
        AuthMiddleware::handle();
        $pdo = DB::pdo();

        $meId = (int)($_SESSION['user_id'] ?? 0);
        if ($meId <= 0) {
            header('Location: /login');
            exit;
        }

        // Resolve other participant
        $stmt = $pdo->prepare("
            SELECT id, uuid, COALESCE(NULLIF(display_name,''), full_name) AS name, profile_picture
            FROM users
            WHERE uuid = :u
            LIMIT 1
        ");
        $stmt->execute([':u' => $userUuid]);
        $other = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$other) {
            http_response_code(404);
            echo 'User not found';
            return;
        }

        $otherId = (int)$other['id'];

        // Load last 100 messages (chronological)
        $stmt = $pdo->prepare("
            SELECT
                m.id, m.sender_id, m.receiver_id,
                m.message AS body,
                m.seen, m.created_at,
                su.uuid AS sender_uuid,
                ru.uuid AS receiver_uuid
            FROM messages m
            JOIN users su ON su.id = m.sender_id
            JOIN users ru ON ru.id = m.receiver_id
            WHERE (m.sender_id = :me AND m.receiver_id = :other)
               OR (m.sender_id = :other AND m.receiver_id = :me)
            ORDER BY m.created_at ASC
            LIMIT 100
        ");
        $stmt->execute([':me' => $meId, ':other' => $otherId]);
        $messages = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Mark incoming messages as seen
        $mark = $pdo->prepare("
            UPDATE messages
            SET seen = 1
            WHERE receiver_id = :me AND sender_id = :other AND seen = 0
        ");
        $mark->execute([':me' => $meId, ':other' => $otherId]);

        $csrf = Csrf::token();
        require __DIR__ . '/../Views/Messages/Thread.php';
    }

    /**
     * POST /messages/{user_uuid}
     * Send a message to the specified user.
     *
     * @param string $userUuid
     */
    public function send(string $userUuid): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();
        $pdo = DB::pdo();

        $meId = (int)($_SESSION['user_id'] ?? 0);
        if ($meId <= 0) {
            header('Location: /login');
            exit;
        }

        // Resolve receiver
        $stmt = $pdo->prepare("SELECT id FROM users WHERE uuid = :u LIMIT 1");
        $stmt->execute([':u' => $userUuid]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$row) {
            $_SESSION['flash_error'] = 'Recipient not found.';
            header('Location: /messages');
            exit;
        }

        $receiverId = (int)$row['id'];

        // Validate message
        $body = trim($_POST['body'] ?? '');
        if ($body === '') {
            $_SESSION['flash_error'] = 'Message cannot be empty.';
            header('Location: /messages/' . rawurlencode($userUuid));
            exit;
        }
        if (mb_strlen($body) > 3000) {
            $_SESSION['flash_error'] = 'Message is too long (max 3000 characters).';
            header('Location: /messages/' . rawurlencode($userUuid));
            exit;
        }

        // Insert
        $sql = "INSERT INTO messages (sender_id, receiver_id, message, seen, created_at)
                VALUES (:s, :r, :b, 0, NOW())";
        $ins = $pdo->prepare($sql);
        $ins->execute([
            ':s' => $meId,
            ':r' => $receiverId,
            ':b' => $body,
        ]);

        $_SESSION['flash_success'] = 'Message sent.';
        header('Location: /messages/' . rawurlencode($userUuid) . '#bottom');
        exit;
    }
}
