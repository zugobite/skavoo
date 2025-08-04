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
