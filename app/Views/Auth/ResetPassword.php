<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="xp-bg">
    <div>
        <div class="logo" style="margin-left: 110px; margin-bottom: 25px;">SKAVOO</div>

        <div class="login-window">
            <div class="window-header">
                <span class="window-title">Reset Password</span>
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

                <form method="POST" action="/reset-password" autocomplete="off">
                    <?= function_exists('csrf_field') ? csrf_field() : '<input type="hidden" name="csrf" value="' . htmlspecialchars($_SESSION['csrf'] ?? '', ENT_QUOTES) . '">' ?>

                    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? $token ?? '', ENT_QUOTES) ?>">

                    <label for="password">New password</label>
                    <input type="password" id="password" name="password" required minlength="8">

                    <label for="password_confirm">Confirm new password</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

                    <button type="submit">Update Password</button>
                </form>

                <div class="links">
                    <p><a href="/login">Back to login</a></p>
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