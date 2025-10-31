<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['full_name']) ?> – Profile | Skavoo</title>
    <meta name="description" content="View the profile of <?= htmlspecialchars($user['full_name']) ?> on Skavoo.">
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">

    <script src="/js/search.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/../Components/Header.php'; ?>


    <div class="cover">
        <?php if ($user['profile_picture']): ?>
            <img class="profile-pic" id="avatar-preview-mini"
                src="<?= \App\Helpers\e($user['profile_picture'] ?: '/images/avatar-default.png'); ?>"
                alt="Profile Picture" class="profile-pic">
        <?php else: ?>
            <div class="profile-pic placeholder"></div>
        <?php endif; ?>

        <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>

        <?php if ($_SESSION['user_uuid'] !== $user['uuid']): ?>
            <div class="friend-msg-actions">
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
                            <button type="submit">Add Friend</button>
                        </form>
                    <?php elseif ($friendship['status'] === 'pending'): ?>
                        <p>Friend request pending</p>
                    <?php elseif ($friendship['status'] === 'accepted'): ?>
                        <p>You are friends</p>
                    <?php endif; ?>
                </section>

                <form action="/messages/compose" method="GET">
                    <input type="hidden" name="to" value="<?= $user['uuid'] ?>">
                    <button type="submit" style="width: 100%;">Message</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <nav class="nav-tabs">
        <?php if (isset($_SESSION['user_uuid']) && $_SESSION['user_uuid'] === $user['uuid']): ?>
            <a href="/user/profile/<?php echo htmlspecialchars($_SESSION['user_uuid'], ENT_QUOTES, 'UTF-8'); ?>"
                class="btn-edit-profile">
                Timeline
            </a>
        <?php endif; ?>
        <a href="#">Friends</a>
        <a href="#">Photos</a>
        <?php if (isset($_SESSION['user_uuid']) && $_SESSION['user_uuid'] === $user['uuid']): ?>
            <a href="/user/profile/<?php echo htmlspecialchars($_SESSION['user_uuid'], ENT_QUOTES, 'UTF-8'); ?>/edit"
                class="btn-edit-profile">
                Edit Profile
            </a>
        <?php endif; ?>
    </nav>

    <div class="container">
        <aside class="sidebar">
            <?php
            $is_own_profile = $_SESSION['user_uuid'] === $user['uuid'];
            $first_name = explode(' ', htmlspecialchars($user['full_name']))[0];

            $stmt = $pdo->prepare("SELECT u.full_name, u.uuid FROM friends f JOIN users u ON ((f.sender_id = :user_id AND f.receiver_id = u.id) OR (f.receiver_id = :user_id AND f.sender_id = u.id)) WHERE f.status = 'accepted'");
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

        <div class="content-wrapper">
            <?php if ($is_own_profile): ?>
                <div class="content">
                    <section id="create-post">
                        <h2>Create Post</h2>
                        <form method="POST" action="/user/post" enctype="multipart/form-data">
                            <textarea name="content" rows="5" placeholder="What's on your mind?" required></textarea>
                            <div class="file-input-wrapper">
                                <label for="media">Choose File</label>
                                <input type="file" name="media" id="media" accept="image/*,video/*" onchange="document.getElementById('media-name').textContent = this.files[0]?.name || 'No file chosen';">
                                <span id="media-name" class="file-name">No file chosen</span>
                            </div>
                            <button type="submit" class="post-btn">Post</button>
                        </form>
                    </section>
                </div>
            <?php endif; ?>

            <main class="content">
                <?php
                $viewer_id = $_SESSION['user_id'];
                $owner_id = $user['id'];
                $is_own_profile = $_SESSION['user_uuid'] === $user['uuid'];

                if ($is_own_profile) {
                    $visibility_condition = "visibility IN ('public', 'friends', 'private')";
                    $query = "SELECT posts.*, users.full_name, users.uuid, users.profile_picture FROM posts JOIN users ON users.id = posts.user_id WHERE posts.user_id = :owner AND $visibility_condition ORDER BY posts.created_at DESC";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([':owner' => $owner_id]);
                } else {
                    $visibility_condition = "(visibility = 'public' OR (visibility = 'friends' AND EXISTS (SELECT 1 FROM friends WHERE status = 'accepted' AND ((sender_id = :viewer AND receiver_id = :owner) OR (sender_id = :owner AND receiver_id = :viewer)))))";
                    $query = "SELECT posts.*, users.full_name, users.uuid, users.profile_picture FROM posts JOIN users ON users.id = posts.user_id WHERE posts.user_id = :owner AND $visibility_condition ORDER BY posts.created_at DESC";
                    $stmt = $pdo->prepare($query);
                    $stmt->execute([':owner' => $owner_id, ':viewer' => $viewer_id]);
                }

                $posts = $stmt->fetchAll();
                ?>

                <section id="user-posts">
                    <h2>Posts</h2>
                    <?php if ($posts): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post">
                                <div class="post-header-layout">
                                    <div class="post-user-info">
                                        <?php if (!empty($post['profile_picture'])): ?>
                                            <img src="/uploads/<?= htmlspecialchars($post['profile_picture']) ?>" alt="Profile" class="post-avatar">
                                        <?php else: ?>
                                            <div class="post-avatar placeholder"></div>
                                        <?php endif; ?>
                                        <div class="post-user-text">
                                            <strong><?= htmlspecialchars($post['full_name']) ?></strong>
                                            <small class="post-date"><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></small>
                                        </div>
                                    </div>
                                    <div class="post-options">
                                        <span title="More options">&#8942;</span>
                                    </div>
                                </div>

                                <hr class="post-divider">

                                <div class="post-body">
                                    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                                    <?php if (!empty($post['image'])): ?>
                                        <?php
                                        $ext = pathinfo($post['image'], PATHINFO_EXTENSION);
                                        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                            <img src="/uploads/<?= htmlspecialchars($post['image']) ?>" alt="Post media" class="post-media">
                                        <?php elseif (in_array(strtolower($ext), ['mp4', 'webm'])): ?>
                                            <video controls class="post-media">
                                                <source src="/uploads/<?= htmlspecialchars($post['image']) ?>" type="video/<?= $ext ?>">
                                            </video>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No posts to display.</p>
                    <?php endif; ?>
                </section>
            </main>
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