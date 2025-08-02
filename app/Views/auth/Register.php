<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register | Skavoo</title>
    <link rel="stylesheet" href="/css/auth.css">
</head>

<body class="xp-bg">
    <div class="login-window">
        <div class="window-header">
            <span class="window-title">Register</span>
            <span class="window-close">✖</span>
        </div>
        <div class="window-body">
            <form method="POST" action="/register" enctype="multipart/form-data" autocomplete="off">
                <label for="full_name">Full Name:</label>
                <input type="text" id="full_name" name="full_name" required>

                <label for="display_name">Display Name:</label>
                <input type="text" id="display_name" name="display_name" required>

                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="profile_picture">Profile Picture (optional):</label>
                <div class="file-input-wrapper">
                    <label for="profile_picture">Choose File</label>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" onchange="updateFileName(this)">
                    <span class="file-name" id="file-name">No file chosen</span>
                </div>

                <button type="submit">Register</button>
            </form>

            <div class="links">
                <p>Already have an account? <a href="/login">Login</a></p>
            </div>
        </div>
    </div>

    <script>
        function updateFileName(input) {
            const fileName = input.files.length > 0 ? input.files[0].name : "No file chosen";
            document.getElementById("file-name").textContent = fileName;
        }
    </script>
</body>

</html>