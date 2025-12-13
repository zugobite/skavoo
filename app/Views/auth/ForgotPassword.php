<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="xp-bg">
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!function_exists('csrf_field')) {
        function csrf_field() {
            // ensure a CSRF token exists in session
            if (empty($_SESSION['csrf'])) {
                // generate a random token
                $_SESSION['csrf'] = bin2hex(random_bytes(32));
            }
            return '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf'], ENT_QUOTES) . '">';
        }
    }
    ?>
    <div class="center-logo-container">
        <div class="logo">SKAVOO</div>

        <div class="login-window">
            <div class="window-header">
                <span class="window-title">Forgot Password</span>
                <span class="window-close">✖</span>
            </div>

            <div class="window-body">
                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert success" style="margin-bottom:12px;">
                        <?= htmlspecialchars($_SESSION['flash_success']) ?>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="alert error" style="margin-bottom:12px;">
                        <?= htmlspecialchars($_SESSION['flash_error']) ?>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <form method="POST" action="/forgot-password" autocomplete="off">
                    <?php if (!function_exists('csrf_field')): ?>
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES) ?>">
                    <?php else: ?>
                        <?= csrf_field() ?> 
                    <?php endif; ?>

                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Email Address" required>

                    <button type="submit">Reset Password</button>
                </form>

                <?php if (!empty($_SESSION['dev_reset_url']) && (strtolower(getenv('APP_ENV') ?: 'production') !== 'production')): ?>
                    <div class="alert warning" style="margin-top:12px;">
                        <strong>DEV ONLY:</strong>
                        <a href="<?= htmlspecialchars($_SESSION['dev_reset_url']) ?>" target="_blank" rel="noopener">
                            <?= htmlspecialchars($_SESSION['dev_reset_url']) ?>
                        </a>
                    </div>
                    <?php unset($_SESSION['dev_reset_url']); ?>
                <?php endif; ?>

                <div class="links">
                    <p>Remembered your password? <a href="/login">Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?= date('Y') ?> Skavoo. All rights reserved.</p>
        <nav class="footer-nav">
            <a href="/terms">Terms of Service</a> |
            <a href="/privacy">Privacy Policy</a> |
            <a href="/help">Help</a>
        </nav>
    </footer>
</body>

</html>