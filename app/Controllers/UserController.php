<?php

/**
 * UserController
 *
 * Displays user profiles and provides edit/update actions for the
 * currently authenticated user's profile.
 *
 * Notes:
 *  - Uses UUIDs in URLs (/user/profile/{uuid}) to avoid exposing numeric IDs.
 *  - CSRF protection via App\Helpers\Csrf.
 *  - Image uploads validated for type/size and stored under /public/uploads/avatars.
 *
 * @package Skavoo\Controllers
 */

require_once __DIR__ . '/../Middleware/AuthMiddleware.php';
require_once __DIR__ . '/../Helpers/DB.php';
require_once __DIR__ . '/../Helpers/Csrf.php';
require_once __DIR__ . '/../Helpers/Functions.php';

use App\Helpers\DB;
use App\Helpers\Csrf;
use function App\Helpers\e;

class UserController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Show a user's public profile.
     *
     * @param string $uuid
     * @return void
     */
    public function profile(string $uuid): void
    {
        $pdo = DB::pdo();

        $stmt = $pdo->prepare("SELECT id, uuid, email, full_name, profile_picture, created_at FROM users WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $uuid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo 'Profile not found';
            return;
        }

        // (Optionally fetch posts/friends here)
        require __DIR__ . '/../Views/User/Profile.php';
    }

    /**
     * Render the edit profile page for the current user.
     *
     * GET /user/profile/{uuid}/edit
     *
     * @param string $uuid
     * @return void
     */
    public function editProfilePage(string $uuid): void
    {
        AuthMiddleware::handle();

        // Only the owner may edit
        if (!isset($_SESSION['user_uuid']) || $_SESSION['user_uuid'] !== $uuid) {
            $_SESSION['flash_error'] = 'You cannot edit another user\'s profile.';
            header('Location: /user/profile/' . urlencode($uuid));
            exit;
        }

        $pdo = DB::pdo();

        $stmt = $pdo->prepare("SELECT id, uuid, email, full_name, profile_picture, bio, birthday, gender, location, relationship_status, work, education, website, phone FROM users WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $uuid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo 'Profile not found';
            return;
        }

        // CSRF token for the form
        $csrf = Csrf::token();

        // $errors allows re-render on failed validation
        $errors = $errors ?? [];

        require __DIR__ . '/../Views/User/EditProfile.php';
    }

    /**
     * Handle edit profile submission.
     *
     * POST /user/profile/{uuid}/edit
     *
     * Accepts:
     *  - full_name (string, required, <=120)
     *  - profile_picture (image file; jpeg/png/webp; <= 2MB) optional
     *
     * @param string $uuid
     * @return void
     */
    public function updateProfile(string $uuid): void
    {
        AuthMiddleware::handle();
        Csrf::verifyOrFail();

        if (!isset($_SESSION['user_uuid']) || $_SESSION['user_uuid'] !== $uuid) {
            $_SESSION['flash_error'] = 'You cannot edit another user\'s profile.';
            header('Location: /user/profile/' . urlencode($uuid));
            exit;
        }

        $pdo = DB::pdo();

        // Load current record (to know current avatar path, etc.)
        $stmt = $pdo->prepare("SELECT id, uuid, full_name, profile_picture, bio, birthday, gender, location, relationship_status, work, education, website, phone FROM users WHERE uuid = :uuid LIMIT 1");
        $stmt->execute([':uuid' => $uuid]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(404);
            echo 'Profile not found';
            return;
        }

        // Validate inputs
        $fullName = trim($_POST['full_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $relationship_status = trim($_POST['relationship_status'] ?? '');
        $work = trim($_POST['work'] ?? '');
        $education = trim($_POST['education'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $errors = [];

        if ($fullName === '') {
            $errors['full_name'] = 'Display name is required.';
        } elseif (mb_strlen($fullName) > 120) {
            $errors['full_name'] = 'Display name must be 120 characters or fewer.';
        }
        if (mb_strlen($bio) > 1000) {
            $errors['bio'] = 'Bio must be 1000 characters or fewer.';
        }
        if ($birthday && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthday)) {
            $errors['birthday'] = 'Invalid birthday format.';
        }
        if ($gender && !in_array($gender, ['male', 'female', 'other', 'prefer_not_to_say'], true)) {
            $errors['gender'] = 'Invalid gender.';
        }
        if (mb_strlen($location) > 100) {
            $errors['location'] = 'Location must be 100 characters or fewer.';
        }
        if ($relationship_status && !in_array($relationship_status, ['single', 'in_a_relationship', 'married', 'complicated'], true)) {
            $errors['relationship_status'] = 'Invalid relationship status.';
        }
        if (mb_strlen($work) > 100) {
            $errors['work'] = 'Work must be 100 characters or fewer.';
        }
        if (mb_strlen($education) > 100) {
            $errors['education'] = 'Education must be 100 characters or fewer.';
        }
        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Invalid website URL.';
        }
        if (mb_strlen($website) > 100) {
            $errors['website'] = 'Website must be 100 characters or fewer.';
        }
        if (mb_strlen($phone) > 30) {
            $errors['phone'] = 'Phone must be 30 characters or fewer.';
        }

        $newAvatarPath = null;

        // Handle optional avatar upload
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['profile_picture'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors['profile_picture'] = 'Upload failed. Please try again.';
            } else {
                $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
                $maxBytes   = 2 * 1024 * 1024; // 2MB

                $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $mime = mime_content_type($file['tmp_name']) ?: '';

                if (!in_array($ext, $allowedExt, true)) {
                    $errors['profile_picture'] = 'Only JPG, PNG, or WEBP images are allowed.';
                } elseif (!preg_match('#^image/(jpeg|png|webp)$#', $mime)) {
                    $errors['profile_picture'] = 'Invalid image type.';
                } elseif ($file['size'] > $maxBytes) {
                    $errors['profile_picture'] = 'Image must be 2MB or smaller.';
                }
            }

            if (empty($errors['profile_picture'])) {
                $uploadDir = __DIR__ . '/../../public/uploads';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0775, true);
                }
                $avatarDir = $uploadDir . '/avatars';
                if (!is_dir($avatarDir)) {
                    @mkdir($avatarDir, 0775, true);
                }

                $basename = $uuid . '-' . bin2hex(random_bytes(4));
                $filename = $basename . '.' . $ext;
                $destPath = $avatarDir . '/' . $filename;

                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    $errors['profile_picture'] = 'Failed to save uploaded image.';
                } else {
                    // Web path
                    $newAvatarPath = '/uploads/avatars/' . $filename;

                    // Remove old avatar if it lived under /uploads/avatars/
                    if (!empty($user['profile_picture']) && str_starts_with((string)$user['profile_picture'], '/uploads/avatars/')) {
                        $oldFsPath = __DIR__ . '/../../public' . $user['profile_picture'];
                        if (is_file($oldFsPath)) {
                            @unlink($oldFsPath);
                        }
                    }
                }
            }
        }

        if (!empty($errors)) {
            // Re-render the form with old input + errors
            $csrf = Csrf::token();
            $user = array_merge($user, [
                'full_name'       => $fullName !== '' ? $fullName : $user['full_name'],
                'bio'             => $bio,
                'birthday'        => $birthday,
                'gender'          => $gender,
                'location'        => $location,
                'relationship_status' => $relationship_status,
                'work'            => $work,
                'education'       => $education,
                'website'         => $website,
                'phone'           => $phone,
                'profile_picture' => $newAvatarPath ?: $user['profile_picture'],
            ]);
            require __DIR__ . '/../Views/User/EditProfile.php';
            return;
        }

        // Persist changes
        $sql = "UPDATE users SET full_name = :name, bio = :bio, birthday = :birthday, gender = :gender, location = :location, relationship_status = :relationship_status, work = :work, education = :education, website = :website, phone = :phone, updated_at = CURRENT_TIMESTAMP";
        $params = [
            ':name' => $fullName,
            ':bio' => $bio,
            ':birthday' => $birthday ?: null,
            ':gender' => $gender ?: null,
            ':location' => $location,
            ':relationship_status' => $relationship_status ?: null,
            ':work' => $work,
            ':education' => $education,
            ':website' => $website,
            ':phone' => $phone,
            ':uuid' => $uuid
        ];
        if ($newAvatarPath) {
            $sql .= ", profile_picture = :pic";
            $params[':pic'] = $newAvatarPath;
        }
        $sql .= " WHERE uuid = :uuid LIMIT 1";
        $upd = $pdo->prepare($sql);
        $upd->execute($params);

        $_SESSION['flash_success'] = 'Profile updated.';
        header('Location: /user/profile/' . urlencode($uuid));
        exit;
    }
}
