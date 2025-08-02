<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['full_name']) ?> – Profile | Skavoo</title>
    <meta name="description" content="View the profile of <?= htmlspecialchars($user['full_name']) ?> on Skavoo.">
    <link rel="stylesheet" href="/css/profile.css">
</head>

<body>

    <header>
        <div class="logo">SKAVOO</div>

        <div>
            <button onclick="window.location.href='/user/profile/<?php echo $_SESSION['user_uuid']; ?>'">Profile</button>
            <button onclick="window.location.href='/logout'">Logout</button>
        </div>
    </header>

    <div class="cover">
        <?php if ($user['profile_picture']): ?>
            <img src="/uploads/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <div class="profile-pic placeholder"></div>
        <?php endif; ?>

        <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>

        <?php if ($_SESSION['user_uuid'] !== $user['uuid']): ?>
            <div style="margin-left: auto; display: flex; flex-direction: column; gap: 10px;">
                <section id="friendship-status">
                    <?php
                    $stmt = $pdo->prepare("SELECT status FROM friends WHERE 
                        (sender_id = :me AND receiver_id = :other)
                        OR (sender_id = :other AND receiver_id = :me)
                        LIMIT 1");
                    $stmt->execute([
                        ':me' => $_SESSION['user_id'],
                        ':other' => $user['id']
                    ]);
                    $friendship = $stmt->fetch();
                    ?>

                    <?php if (!$friendship): ?>
                        <form action="/friends/send" method="POST">
                            <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
                            <button type="submit">Send Friend Request</button>
                        </form>
                    <?php elseif ($friendship['status'] === 'pending'): ?>
                        <p>Friend request pending</p>
                    <?php elseif ($friendship['status'] === 'accepted'): ?>
                        <p>You are friends</p>
                    <?php endif; ?>
                </section>

                <form action="/messages/compose" method="GET">
                    <input type="hidden" name="to" value="<?= $user['uuid'] ?>">
                    <button type="submit">Message</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <nav class="nav-tabs">
        <a href="#">Timeline</a>
        <a href="#">Friends</a>
        <a href="#">Photos</a>
        <?php if ($_SESSION['user_uuid'] === $user['uuid']): ?>
            <a href="#">Edit Profile</a>
        <?php endif; ?>

    </nav>

    <div class="container">
        <aside class="sidebar">
            <?php
            $is_own_profile = $_SESSION['user_uuid'] === $user['uuid'];
            $first_name = explode(' ', htmlspecialchars($user['full_name']))[0];

            $stmt = $pdo->prepare("
                SELECT u.full_name, u.uuid
                FROM friends f
                JOIN users u ON (
                    (f.sender_id = :user_id AND f.receiver_id = u.id)
                    OR (f.receiver_id = :user_id AND f.sender_id = u.id)
                )
                WHERE f.status = 'accepted'
            ");
            $stmt->execute(['user_id' => $user['id']]);
            $friends = $stmt->fetchAll();
            ?>

            <div class="friends-list">
                <h2>Friends</h2>
                <?php if ($friends): ?>
                    <ul>
                        <?php foreach ($friends as $friend): ?>
                            <li><a href="/user/<?= htmlspecialchars($friend['uuid']) ?>"><?= htmlspecialchars($friend['full_name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><?= $is_own_profile ? 'You have no friends yet.' : "{$first_name} has no friends yet." ?></p>
                <?php endif; ?>
            </div>
        </aside>

        <main class="content">
            <?php
            $viewer_id = $_SESSION['user_id'];
            $owner_id = $user['id'];
            $is_own_profile = $_SESSION['user_uuid'] === $user['uuid'];

            if ($is_own_profile) {
                $visibility_condition = "visibility IN ('public', 'friends', 'private')";
                $query = "
            SELECT posts.*, users.full_name, users.uuid, users.profile_picture
            FROM posts
            JOIN users ON users.id = posts.user_id
            WHERE posts.user_id = :owner AND $visibility_condition
            ORDER BY posts.created_at DESC
        ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':owner' => $owner_id
                ]);
            } else {
                $visibility_condition = "(
            visibility = 'public'
            OR (
                visibility = 'friends'
                AND EXISTS (
                    SELECT 1 FROM friends
                    WHERE status = 'accepted' AND (
                        (sender_id = :viewer AND receiver_id = :owner)
                        OR (sender_id = :owner AND receiver_id = :viewer)
                    )
                )
            )
        )";
                $query = "
            SELECT posts.*, users.full_name, users.uuid, users.profile_picture
            FROM posts
            JOIN users ON users.id = posts.user_id
            WHERE posts.user_id = :owner AND $visibility_condition
            ORDER BY posts.created_at DESC
        ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([
                    ':owner' => $owner_id,
                    ':viewer' => $viewer_id
                ]);
            }

            $posts = $stmt->fetchAll();
            ?>

            <?php if ($is_own_profile): ?>
                <section id="create-post">
                    <h2>Create Post</h2>
                    <form method="POST" action="/user/post" enctype="multipart/form-data">
                        <textarea name="content" rows="5" placeholder="What's on your mind?" required></textarea>
                        <div class="file-input-wrapper">
                            <label for="media">Choose File</label>
                            <input type="file" name="media" id="media" accept="image/*,video/*" onchange="document.getElementById('media-name').textContent = this.files[0]?.name || 'No file chosen';">
                            <span id="media-name" class="file-name">No file chosen</span>
                        </div>
                        <script>
                            document.getElementById('media').addEventListener('change', function() {
                                const fileName = this.files.length > 0 ? this.files[0].name : "No file chosen";
                                document.getElementById('media-name').textContent = fileName;
                            });
                        </script>
                        <button type="submit" style="width: 100%;">Post</button>
                    </form>
                </section>
            <?php endif; ?>

            <section id="user-posts">
                <h2>Posts</h2>
                <?php if ($posts): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="post-header">
                                <strong><?= htmlspecialchars($post['full_name']) ?></strong>
                                <small style="color: gray; float: right;"><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></small>
                            </div>
                            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                            <?php if (!empty($post['image'])): ?>
                                <?php
                                $ext = pathinfo($post['image'], PATHINFO_EXTENSION);
                                if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                    <img src="/uploads/<?= htmlspecialchars($post['image']) ?>" alt="Post media" style="max-width: 100%; margin-top: 10px; border-radius: 3px;">
                                <?php elseif (in_array(strtolower($ext), ['mp4', 'webm'])): ?>
                                    <video controls style="max-width: 100%; margin-top: 10px; border-radius: 3px;">
                                        <source src="/uploads/<?= htmlspecialchars($post['image']) ?>" type="video/<?= $ext ?>">
                                    </video>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No posts to display.</p>
                <?php endif; ?>
            </section>
        </main>

    </div>

    <footer>
        <button onclick="window.location.href='/user/all'">View All Users</button>
    </footer>

</body>

</html>