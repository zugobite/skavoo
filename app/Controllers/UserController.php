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
require_once __DIR__ . '/../Helpers/DB.php';

USE App\Helpers\DB;

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

    /**
     * Show the edit profile form for the authenticated user (owner-only).
     *
     * @param array $params
     * @return void
     */
    public function editProfile($params)
    {
        AuthMiddleware::handle();

        $pdo   = \App\Helpers\DB::pdo();
        $meUuid = isset($params['uuid']) ? $params['uuid'] : null;

        $meId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        $stmt = $pdo->prepare("SELECT id, uuid, full_name, profile_picture FROM users WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $meUuid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || intval($user['id']) !== intval($meId)) {
            http_response_code(403);
            return view('Errors/403', ['message' => 'You cannot edit this profile.']);
        }

        return view('User/EditProfile', [
            'user' => $user,
            'csrf' => \App\Helpers\Csrf::token()
        ]);
    }

    /**
     * Update display name and optional profile picture (owner-only).
     *
     * @param array $params
     * @return void
     */
    public function updateProfile($params)
    {
        \App\Helpers\Csrf::verifyOrFail();
        AuthMiddleware::handle();

        $pdo  = \App\Helpers\DB::pdo();
        $meId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $uuid = isset($params['uuid']) ? $params['uuid'] : null;

        $stmt = $pdo->prepare("SELECT id, uuid, full_name, profile_picture FROM users WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $uuid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || intval($user['id']) !== intval($meId)) {
            http_response_code(403);
            return view('Errors/403', ['message' => 'You cannot edit this profile.']);
        }

        $fullName = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
        if ($fullName === '' || mb_strlen($fullName) > 120) {
            $_SESSION['flash_error'] = 'Display name is required and must be ≤ 120 characters.';
            return redirect('/user/profile/' . $uuid . '/edit');
        }

        $avatarPath = $user['profile_picture'];

        if (isset($_FILES['profile_picture']) && !empty($_FILES['profile_picture']['name'])) {
            $file     = $_FILES['profile_picture'];
            $maxBytes = 2 * 1024 * 1024; // 2MB
            $allowed  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

            if ($file['error'] === UPLOAD_ERR_OK && $file['size'] <= $maxBytes) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->file($file['tmp_name']);
                if (isset($allowed[$mime])) {
                    $ext  = $allowed[$mime];
                    $name = bin2hex(random_bytes(8)) . '.' . $ext;

                    $destDir = __DIR__ . '/../../public/uploads/';
                    if (!is_dir($destDir)) {
                        @mkdir($destDir, 0775, true);
                    }

                    $destPath = $destDir . $name;
                    if (move_uploaded_file($file['tmp_name'], $destPath)) {
                        $avatarPath = '/uploads/' . $name;
                    } else {
                        $_SESSION['flash_error'] = 'Failed to save uploaded image.';
                        return redirect('/user/profile/' . $uuid . '/edit');
                    }
                } else {
                    $_SESSION['flash_error'] = 'Invalid image type. Use JPEG, PNG, or WEBP.';
                    return redirect('/user/profile/' . $uuid . '/edit');
                }
            } else {
                $_SESSION['flash_error'] = 'Image too large (max 2MB) or upload error.';
                return redirect('/user/profile/' . $uuid . '/edit');
            }
        }

        $upd = $pdo->prepare("UPDATE users SET full_name = :n, profile_picture = :p WHERE id = :id");
        $upd->execute([
            ':n'  => $fullName,
            ':p'  => $avatarPath,
            ':id' => $meId
        ]);

        $_SESSION['flash_success'] = 'Profile updated.';
        return redirect('/user/profile/' . $uuid);
    }
}
