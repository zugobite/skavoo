<?php

/**
 * PostController
 *
 * Handles post-related functionality including creating posts,
 * liking/unliking posts, and commenting.
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

class PostController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Create a new post.
     *
     * POST /posts/create
     *
     * @return void
     */
    public function create(): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];

        $content = trim($_POST['content'] ?? '');
        $visibility = $_POST['visibility'] ?? 'public';

        // Validate content
        if (empty($content)) {
            $_SESSION['flash_error'] = 'Post content cannot be empty.';
            $this->redirectBack();
            return;
        }

        if (mb_strlen($content) > 5000) {
            $_SESSION['flash_error'] = 'Post content is too long (max 5000 characters).';
            $this->redirectBack();
            return;
        }

        // Validate visibility
        $allowedVisibility = ['public', 'friends', 'private'];
        if (!in_array($visibility, $allowedVisibility, true)) {
            $visibility = 'public';
        }

        $imagePath = null;

        // Handle media upload
        if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['media'];

            if ($file['error'] === UPLOAD_ERR_OK) {
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];
                $maxBytes = 10 * 1024 * 1024; // 10MB

                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $mime = mime_content_type($file['tmp_name']) ?: '';

                if (!in_array($ext, $allowedExt, true)) {
                    $_SESSION['flash_error'] = 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP, MP4, WEBM.';
                    $this->redirectBack();
                    return;
                }

                if ($file['size'] > $maxBytes) {
                    $_SESSION['flash_error'] = 'File too large (max 10MB).';
                    $this->redirectBack();
                    return;
                }

                $uploadDir = __DIR__ . '/../../public/uploads/posts/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0775, true);
                }

                $filename = uniqid('post_') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destPath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $destPath)) {
                    $imagePath = 'posts/' . $filename;
                } else {
                    $_SESSION['flash_error'] = 'Failed to upload media.';
                    $this->redirectBack();
                    return;
                }
            }
        }

        // Insert post
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, content, image, visibility, created_at, updated_at)
            VALUES (:user_id, :content, :image, :visibility, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':content' => $content,
            ':image' => $imagePath,
            ':visibility' => $visibility
        ]);

        $_SESSION['flash_success'] = 'Post created successfully!';
        $this->redirectBack();
    }

    /**
     * Like or unlike a post.
     *
     * POST /posts/{id}/like
     *
     * @param string $postId
     * @return void
     */
    public function toggleLike(string $postId): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];
        $postId = (int)$postId;

        // Check if post exists
        $stmt = $pdo->prepare("SELECT id, user_id FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $postId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            $_SESSION['flash_error'] = 'Post not found.';
            $this->redirectBack();
            return;
        }

        // Check if already liked
        $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = :user AND post_id = :post LIMIT 1");
        $stmt->execute([':user' => $userId, ':post' => $postId]);
        $existingLike = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existingLike) {
            // Unlike
            $stmt = $pdo->prepare("DELETE FROM likes WHERE id = :id");
            $stmt->execute([':id' => $existingLike['id']]);
            $action = 'unliked';
        } else {
            // Like
            $stmt = $pdo->prepare("
                INSERT INTO likes (user_id, post_id, created_at)
                VALUES (:user, :post, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([':user' => $userId, ':post' => $postId]);
            $action = 'liked';

            // Create notification for post owner (if not self)
            if ((int)$post['user_id'] !== $userId) {
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, content, is_read, created_at)
                    VALUES (:user_id, 'like', :content, FALSE, CURRENT_TIMESTAMP)
                ");
                $stmt->execute([
                    ':user_id' => $post['user_id'],
                    ':content' => json_encode([
                        'liker_id' => $userId,
                        'post_id' => $postId,
                        'message' => 'liked your post'
                    ])
                ]);
            }
        }

        // For AJAX requests
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            // Get updated like count
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = :post");
            $stmt->execute([':post' => $postId]);
            $likeCount = $stmt->fetchColumn();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'action' => $action,
                'likes' => (int)$likeCount
            ]);
            exit;
        }

        $this->redirectBack();
    }

    /**
     * Add a comment to a post.
     *
     * POST /posts/{id}/comment
     *
     * @param string $postId
     * @return void
     */
    public function addComment(string $postId): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];
        $postId = (int)$postId;
        $comment = trim($_POST['comment'] ?? '');

        if (empty($comment)) {
            $_SESSION['flash_error'] = 'Comment cannot be empty.';
            $this->redirectBack();
            return;
        }

        if (mb_strlen($comment) > 1000) {
            $_SESSION['flash_error'] = 'Comment is too long (max 1000 characters).';
            $this->redirectBack();
            return;
        }

        // Check if post exists
        $stmt = $pdo->prepare("SELECT id, user_id FROM posts WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $postId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            $_SESSION['flash_error'] = 'Post not found.';
            $this->redirectBack();
            return;
        }

        // Insert comment
        $stmt = $pdo->prepare("
            INSERT INTO comments (user_id, post_id, comment, created_at, updated_at)
            VALUES (:user_id, :post_id, :comment, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':post_id' => $postId,
            ':comment' => $comment
        ]);

        // Create notification for post owner (if not self)
        if ((int)$post['user_id'] !== $userId) {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, type, content, is_read, created_at)
                VALUES (:user_id, 'comment', :content, FALSE, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([
                ':user_id' => $post['user_id'],
                ':content' => json_encode([
                    'commenter_id' => $userId,
                    'post_id' => $postId,
                    'comment_preview' => mb_substr($comment, 0, 50),
                    'message' => 'commented on your post'
                ])
            ]);
        }

        $_SESSION['flash_success'] = 'Comment added!';
        $this->redirectBack();
    }

    /**
     * Delete a comment.
     *
     * POST /comments/{id}/delete
     *
     * @param string $commentId
     * @return void
     */
    public function deleteComment(string $commentId): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];
        $commentId = (int)$commentId;

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = :id AND user_id = :user LIMIT 1");
        $stmt->execute([':id' => $commentId, ':user' => $userId]);

        if (!$stmt->fetch()) {
            $_SESSION['flash_error'] = 'Comment not found or you cannot delete it.';
            $this->redirectBack();
            return;
        }

        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = :id");
        $stmt->execute([':id' => $commentId]);

        $_SESSION['flash_success'] = 'Comment deleted.';
        $this->redirectBack();
    }

    /**
     * Delete a post.
     *
     * POST /posts/{id}/delete
     *
     * @param string $postId
     * @return void
     */
    public function delete(string $postId): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        $pdo = DB::pdo();
        $userId = $_SESSION['user_id'];
        $postId = (int)$postId;

        // Verify ownership
        $stmt = $pdo->prepare("SELECT id, image FROM posts WHERE id = :id AND user_id = :user LIMIT 1");
        $stmt->execute([':id' => $postId, ':user' => $userId]);
        $post = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$post) {
            $_SESSION['flash_error'] = 'Post not found or you cannot delete it.';
            $this->redirectBack();
            return;
        }

        // Delete associated media file
        if (!empty($post['image'])) {
            $filePath = __DIR__ . '/../../public/uploads/' . $post['image'];
            if (is_file($filePath)) {
                @unlink($filePath);
            }
        }

        // Delete post (cascade will handle likes/comments)
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([':id' => $postId]);

        $_SESSION['flash_success'] = 'Post deleted.';
        $this->redirectBack();
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
