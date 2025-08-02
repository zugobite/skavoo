<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login | Skavoo</title>
    <link rel="stylesheet" href="/css/auth.css">
</head>

<body class="xp-bg">
    <div class="login-window">
        <div class="window-header">
            <span class="window-title">Login</span>
            <span class="window-close">✖</span>
        </div>
        <div class="window-body">
            <form method="POST" action="/login" autocomplete="off">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Login</button>
            </form>

            <div class="links">
                <p>Don't have an account? <a href="/register">Register</a></p>
                <p><a href="/forgot-password">Forgot Password?</a></p>
            </div>
        </div>
    </div>

</body>

</html>