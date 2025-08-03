<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password | Skavoo</title>
    <link rel="stylesheet" href="/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="xp-bg">
    <div>
        <div class="logo">SKAVOO</div>

        <div class="login-window">
            <div class="window-header">
                <span class="window-title">Forgot Password</span>
                <span class="window-close">✖</span>
            </div>

            <div class="window-body">
                <form method="POST" action="/forgot-password" autocomplete="off">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required>

                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" required>

                    <button type="submit">Reset Password</button>
                </form>

                <div class="links">
                    <p>Remembered your password? <a href="/login">Login</a></p>
                </div>
            </div>
        </div>
    </div>

    <!-- <footer>
        <p>&copy; <?= date('Y') ?> Skavoo. All rights reserved.</p>
        <nav class="footer-nav">
            <a href="/terms">Terms of Service</a> |
            <a href="/privacy">Privacy Policy</a> |
            <a href="/help">Help</a>
        </nav>
    </footer> -->
</body>

</html>