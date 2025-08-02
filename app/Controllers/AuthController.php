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
        header("Location: /user/profile/{$user['uuid']}");
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


        echo "Registration successful. <a href='/login'>Login here</a>";
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

        // Get submitted email and new password
        $email = trim($_POST['email'] ?? '');
        $newPassword = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm'] ?? '';

        // Basic input validation
        if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
            echo 'Please fill in all fields.';
            return;
        }

        if ($newPassword !== $confirmPassword) {
            echo 'Passwords do not match.';
            return;
        }

        require '../config/database.php';

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            echo 'Email not found.';
            return;
        }

        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password in the database
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        echo 'Password reset successful. <a href="/login">Login here</a>';
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
