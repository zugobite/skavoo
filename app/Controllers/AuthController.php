<?php

/**
 * AuthController
 *
 * Handles user authentication-related routes such as displaying
 * the login form and processing login credentials.
 *
 * @package Skavoo\Controllers
 */
class AuthController
{

    /**
     * Display the login page.
     *
     * This method includes the login view, which presents a form
     * for users to enter their email and password.
     *
     * @return void
     */
    public function loginPage()
    {
        include '../app/Views/Auth/Login.php';
    }

    /**
     * Handle the login form submission.
     *
     * This method will process the login form, validate credentials,
     * initiate user sessions, and redirect appropriately.
     *
     * @return void
     */
    public function login()
    {
        require_once __DIR__ . '/../../config/database.php';

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo 'Please fill in all fields.';
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            echo 'Invalid email or password.';
            return;
        }

        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_uuid'] = $user['uuid']; // Save UUID for routing
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['display_name'] = $user['display_name'];

        // Redirect to user profile via UUID
        // header("Location: /user/profile/{$user['uuid']}");
        header("Location: /feed");
        exit;
    }


    /**
     * Display the registration page.
     *
     * This method includes the registration view, which presents a form
     * for new users to create an account by providing their details.
     *
     * @return void
     */
    public function registerPage()
    {
        include '../app/Views/Auth/Register.php';
    }

    /**
     * Handle the registration form submission.
     *
     * This method will process the registration form, validate input,
     * create a new user, and handle file uploads if necessary.
     *
     * @return void
     */
    public function register()
    {
        session_start();

        // Basic sanitization and required field checks
        $fullName = trim($_POST['full_name']);
        $displayName = trim($_POST['display_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $profilePicture = $_FILES['profile_picture'] ?? null;

        if (!$fullName || !$displayName || !$email || !$password) {
            echo "All fields are required.";
            return;
        }

        require '../config/database.php';

        // Check if email is already taken
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo "Email already in use.";
            return;
        }

        // Handle profile picture upload
        $filename = null;
        if ($profilePicture && $profilePicture['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($profilePicture['name'], PATHINFO_EXTENSION);
            $filename = uniqid('pf_') . '.' . $ext;
            $uploadDir = '../public/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            move_uploaded_file($profilePicture['tmp_name'], $uploadDir . $filename);
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $uuid = bin2hex(random_bytes(16));

        $stmt = $pdo->prepare("INSERT INTO users (uuid, full_name, display_name, email, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uuid, $fullName, $displayName, $email, $hashedPassword, $filename]);


        header('Location: /login');
        exit;
    }

    /**
     * Display the forgot password page.
     *
     * This method includes the forgot password view, which presents a form
     * for users to enter their email address to receive a password reset link.
     *
     * @return void
     */
    public function forgotPasswordPage()
    {
        include '../app/Views/Auth/ForgotPassword.php';
    }

    /**
     * Handle the forgot password form submission.
     *
     * This method will process the forgot password form, validate the email,
     * and send a password reset link to the user's email address.
     *
     * @return void
     */
    public function sendResetLink()
    {
        session_start();
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            http_response_code(419);
            echo "Invalid CSRF token.";
            return;
        }

        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = "Please enter a valid email address.";
            header('Location: /forgot-password');
            return;
        }

        $pdo = db();

        // Check that the user exists (don’t leak whether email exists via wording)
        $stmt = $pdo->prepare("SELECT id, email FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Always show success flash to avoid enumeration
        $_SESSION['flash_success'] = "If that email exists, a reset link has been sent.";

        if (!$user) {
            header('Location: /forgot-password');
            return;
        }

        // Invalidate existing tokens for this user
        $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ?")
            ->execute([$user['id']]);

        // Create selector + token pair
        $selector = bin2hex(random_bytes(8));         // public part
        $token    = bin2hex(random_bytes(32));        // secret part sent to user
        $hashed   = hash('sha256', $token);
        $expires  = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            INSERT INTO password_resets (user_id, email, selector, hashed_token, expires_at, used, created_at, ip_address)
            VALUES (?, ?, ?, ?, ?, 0, NOW(), ?)
        ");
        $stmt->execute([
            $user['id'],
            $user['email'],
            $selector,
            $hashed,
            $expires,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        // Build reset URL
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
        $resetUrl = $scheme . '://' . $host . '/reset-password?selector=' . urlencode($selector) . '&token=' . urlencode($token);

        // Email content (plain + HTML)
        $subject = "Reset your Skavoo password";
        $text = "We received a request to reset your password.\n\n"
            . "Use this link within 30 minutes:\n$resetUrl\n\n"
            . "If you didn’t request this, you can ignore this email.";
        $html = "<p>We received a request to reset your password.</p>"
            . "<p><a href=\"$resetUrl\">Reset your password</a> (valid for 30 minutes)</p>"
            . "<p>If you didn’t request this, you can ignore this email.</p>";

        // Send email (uses PHP mail() with proper headers in helper)
        send_mail($user['email'], $subject, $html, $text);

        header('Location: /forgot-password');
    }

    /**
     * Display the reset password page.
     *
     * This method will render a page where users can enter a new password
     * after clicking the reset link sent to their email.
     *
     * @return void
     */
    public function resetPasswordPage()
    {
        $selector = $_GET['selector'] ?? '';
        $token    = $_GET['token'] ?? '';

        // Render a neutral page if params are missing
        if (!$selector || !$token) {
            http_response_code(400);
            echo "Invalid password reset link.";
            return;
        }

        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT id, user_id, hashed_token, expires_at, used
            FROM password_resets
            WHERE selector = ?
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([$selector]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $valid = false;
        if ($row && !$row['used'] && new DateTime($row['expires_at']) > new DateTime()) {
            $calc = hash('sha256', $token);
            if (hash_equals($row['hashed_token'], $calc)) {
                $valid = true;
            }
        }

        if (!$valid) {
            http_response_code(400);
            echo "This reset link is invalid or has expired.";
            return;
        }

        // Show reset form with hidden selector + token
        require __DIR__ . '/../Views/Auth/ResetPassword.php';
    }

    /**
     * Handle the reset password form submission.
     *
     * This method will process the reset password form, validate the token,
     * update the user's password, and handle any errors.
     *
     * @return void
     */
    public function handleResetPassword()
    {
        session_start();
        if (!csrf_verify($_POST['csrf'] ?? '')) {
            http_response_code(419);
            echo "Invalid CSRF token.";
            return;
        }

        $selector = $_POST['selector'] ?? '';
        $token    = $_POST['token'] ?? '';
        $pass1    = $_POST['password'] ?? '';
        $pass2    = $_POST['password_confirm'] ?? '';

        if (!$selector || !$token) {
            $_SESSION['flash_error'] = "Invalid request.";
            header("Location: /forgot-password");
            return;
        }

        // Basic password rules (customize as you like)
        if (strlen($pass1) < 8 || !preg_match('/[A-Za-z]/', $pass1) || !preg_match('/\d/', $pass1)) {
            $_SESSION['flash_error'] = "Password must be at least 8 chars and include a letter and a number.";
            header("Location: /reset-password?selector=$selector&token=$token");
            return;
        }
        if ($pass1 !== $pass2) {
            $_SESSION['flash_error'] = "Passwords do not match.";
            header("Location: /reset-password?selector=$selector&token=$token");
            return;
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            // Fetch & validate token record
            $stmt = $pdo->prepare("
                SELECT pr.id, pr.user_id, pr.hashed_token, pr.expires_at, pr.used,
                       u.id AS u_id
                FROM password_resets pr
                JOIN users u ON u.id = pr.user_id
                WHERE pr.selector = ?
                ORDER BY pr.id DESC
                LIMIT 1
            ");
            $stmt->execute([$selector]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['used'] || new DateTime($row['expires_at']) <= new DateTime()) {
                throw new Exception("Invalid or expired token.");
            }

            $calc = hash('sha256', $token);
            if (!hash_equals($row['hashed_token'], $calc)) {
                throw new Exception("Invalid token.");
            }

            // Update user password
            $newHash = password_hash($pass1, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $up->execute([$newHash, $row['user_id']]);

            // Mark token used and invalidate any other outstanding tokens for this user
            $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = ?")->execute([$row['id']]);
            $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND id <> ?")->execute([$row['user_id'], $row['id']]);

            $pdo->commit();

            $_SESSION['flash_success'] = "Password updated. You can sign in now.";
            header("Location: /login");
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['flash_error'] = "Unable to reset password. Please request a new link.";
            header("Location: /forgot-password");
        }
    }

    /**
     * Handle the forgot password form submission.
     *
     * This method will process the forgot password form, validate the email,
     * and send a password reset link to the user's email address.
     *
     * @return void
     */
    public function forgotPassword()
    {
        session_start();

        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            echo 'Please provide an email address.';
            return;
        }

        require '../config/database.php';

        // Check if the email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            echo 'Email not found.';
            return;
        }

        // Generate token and expiry
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Insert into password_resets
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$email, $token, $expiresAt]);

        // Build reset link
        $resetLink = "http://localhost:8000/reset-password?token=$token";

        // Send email
        $subject = "Reset your password";
        $message = "Click the link below to reset your password:\n$resetLink";
        $headers = "From: no-reply@skavoo.co.za";

        mail($email, $subject, $message, $headers);

        // Redirect or show message
        header("Location: /login");
        exit;
    }

    /**
     * Logout the user.
     *
     * This method will clear the session and redirect the user to the home page.
     *
     * @return void
     */
    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header('Location: /');
        exit;
    }
}
