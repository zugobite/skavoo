<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">
</head>

<body class="xp-bg">
    <div>
        <div class="logo" style="margin-left: 110px; margin-bottom: 25px;">SKAVOO</div>

        <div class="login-window">
            <div class="window-header">
                <span class="window-title">Login</span>
                <span class="window-close">✖</span>
            </div>
            <div class="window-body">
                <form method="POST" action="/login" autocomplete="off">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>

                    <button type="submit">Login</button>
                </form>

                <div class="links">
                    <p>Don't have an account? <a href="/register">Register</a></p>
                    <p><a href="/forgot-password">Forgot Password?</a></p>
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