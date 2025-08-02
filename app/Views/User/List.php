<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>All Users | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <h1>All Users</h1>
    <ul>
        <?php foreach ($users as $user): ?>
            <li>
                <a href="/user/profile/<?php echo htmlspecialchars($user['uuid']); ?>">
                    <?php if ($user['profile_picture']): ?>
                        <img src="/uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" width="50">
                    <?php endif; ?>
                    <?php echo htmlspecialchars($user['display_name'] ?? $user['full_name']); ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <button onclick="window.location.href='/user/profile/<?php echo $_SESSION['user_uuid']; ?>'">Back to My Profile</button>
</body>

</html>