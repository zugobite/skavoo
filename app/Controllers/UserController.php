<?php

/**
 * UserController
 *
 * Handles user-related routes such as displaying
 * the user profile.
 *
 * @package Skavoo\Controllers
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class UserController
{

    /**
     * Display the user profile.
     *
     * This method includes the user profile view, which shows
     * the details of the currently logged-in user.
     *
     * @return void
     */
    public function profile($uuid)
    {
        AuthMiddleware::handle();
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM users WHERE uuid = ?");
        $stmt->execute([$uuid]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(404);
            echo "User not found.";
            return;
        }

        $isOwner = $_SESSION['user_id'] == $user['id'];

        include '../app/Views/User/Profile.php';
    }

    /**
     * Handle post creation for the authenticated user.
     *
     * Accepts content and optional media file, saves post to DB.
     *
     * @return void
     */
    public function createPost()
    {
        AuthMiddleware::handle();
        global $pdo;

        $user_id = $_SESSION['user_id'];
        $user_uuid = $_SESSION['user_uuid'];
        $content = trim($_POST['content'] ?? '');
        $visibility = 'public'; // Expandable
        $image = null;

        // Handle file upload
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../public/uploads/';
            $file_tmp = $_FILES['media']['tmp_name'];
            $file_name = basename($_FILES['media']['name']);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'mp4', 'webm'];

            if (in_array($file_ext, $allowed)) {
                $new_filename = uniqid() . '.' . $file_ext;
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                    $image = $new_filename;
                }
            }
        }

        if ($content !== '' || $image !== null) {
            $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, image, visibility) VALUES (:user_id, :content, :image, :visibility)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':content' => $content,
                ':image' => $image,
                ':visibility' => $visibility
            ]);
        }

        header("Location: /user/profile/$user_uuid");
        exit;
    }
}
