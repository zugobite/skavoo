<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['full_name']) ?> – Profile | Skavoo</title>
    <meta name="description" content="View the profile of <?= htmlspecialchars($user['full_name']) ?> on Skavoo.">
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">

    <script src="/js/search.js" defer></script>
    <script src="/js/feed.js" defer></script>
</head>

<body>
    <?php 
    include __DIR__ . '/../Components/Header.php';
    $csrf = \App\Helpers\Csrf::token();
    ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="flash success container-flash"><?= \App\Helpers\e($_SESSION['flash_success']); ?><?php unset($_SESSION['flash_success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash error container-flash"><?= \App\Helpers\e($_SESSION['flash_error']); ?><?php unset($_SESSION['flash_error']); ?></div>
    <?php endif; ?>

    <div class="cover">
        <img class="profile-pic" id="avatar-preview-mini"
            src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($user['profile_picture'] ?? null)); ?>"
            alt="Profile Picture">

        <div class="profile-name"><?= htmlspecialchars($user['full_name']) ?></div>

        <?php if ($_SESSION['user_uuid'] !== $user['uuid']): ?>
            <div class="friend-msg-actions">
                <section id="friendship-status">
                    <?php
                    // Get friendship details including who sent the request
                    $stmt = $pdo->prepare("SELECT id, sender_id, receiver_id, status FROM friends WHERE 
                        (sender_id = :me AND receiver_id = :other)
                        OR (sender_id = :other AND receiver_id = :me)
                        LIMIT 1");
                    $stmt->execute([
                        ':me' => $_SESSION['user_id'],
                        ':other' => $user['id']
                    ]);
                    $friendship = $stmt->fetch(\PDO::FETCH_ASSOC);
                    ?>

                    <?php if (!$friendship): ?>
                        <!-- No friendship exists - show Add Friend button -->
                        <form action="/friends/send" method="POST">
                            <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                            <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn">➕ Add Friend</button>
                        </form>

                    <?php elseif ($friendship['status'] === 'pending'): ?>
                        <?php if ((int)$friendship['sender_id'] === (int)$_SESSION['user_id']): ?>
                            <!-- I sent the request - show Cancel button -->
                            <form action="/friends/cancel" method="POST">
                                <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                <input type="hidden" name="receiver_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn-secondary">Cancel Request</button>
                            </form>
                        <?php else: ?>
                            <!-- They sent me a request - show Accept/Decline buttons -->
                            <div class="friend-request-buttons">
                                <form action="/friends/accept" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                    <input type="hidden" name="request_id" value="<?= (int)$friendship['id']; ?>">
                                    <button type="submit" class="btn">Accept</button>
                                </form>
                                <form action="/friends/reject" method="POST" style="display:inline;">
                                    <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                    <input type="hidden" name="request_id" value="<?= (int)$friendship['id']; ?>">
                                    <button type="submit" class="btn">Decline</button>
                                </form>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($friendship['status'] === 'accepted'): ?>
                        <!-- Already friends - show Friends badge and Unfriend option -->
                        <div class="friends-badge">✓ Friends</div>
                        <form action="/friends/remove" method="POST" style="margin-top:5px;"
                              onsubmit="return confirm('Are you sure you want to unfriend <?= \App\Helpers\e($user['full_name']); ?>?');">
                            <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                            <input type="hidden" name="friend_id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn-unfriend-small">Unfriend</button>
                        </form>
                    <?php endif; ?>
                </section>

                <form action="/messages/<?= \App\Helpers\e($user['uuid']); ?>" method="GET" style="margin:0;">
                    <button type="submit" style="width: 100%;">💬 Message</button>
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
        <a href="/friends">Friends</a>
        <a href="#">Photos</a>
        <?php if (isset($_SESSION['user_uuid']) && $_SESSION['user_uuid'] === $user['uuid']): ?>
            <a href="/user/profile/<?php echo htmlspecialchars($_SESSION['user_uuid'], ENT_QUOTES, 'UTF-8'); ?>/edit"
                class="btn-edit-profile">
                Edit Profile
            </a>
        <?php endif; ?>
        <a href="/messages">Messages</a>
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
                    <p class="no-content"><?= $is_own_profile ? 'You have no friends yet.' : "{$first_name} has no friends yet." ?></p>
                <?php endif; ?>
            </div>
        </aside>

        <div class="content-wrapper">
            <?php if ($is_own_profile): ?>
                <!-- Create Post Card -->
                <div class="post-block create-post-card">
                    <div class="card">
                        <form action="/posts/create" method="POST" enctype="multipart/form-data" id="create-post-form">
                            <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                            
                            <textarea name="content" 
                                      placeholder="What's on your mind?" 
                                      class="post-textarea" 
                                      rows="2"
                                      required
                                      maxlength="5000"></textarea>

                            <!-- Image Preview -->
                            <div id="media-preview" class="media-preview" style="display:none;">
                                <img id="preview-image" src="" alt="Preview" style="display:none;">
                                <video id="preview-video" controls style="display:none;"></video>
                                <button type="button" class="btn remove-media-pos" onclick="removeMediaPreview()">✕</button>
                            </div>

                            <div class="create-post-actions">
                                <div class="post-options-left">
                                    <label class="btn" title="Add Photo/Video">
                                        Photo/Video
                                        <input type="file" name="media" id="media-input" accept="image/*,video/*" 
                                               onchange="previewMedia(this)" style="display:none;">
                                    </label>
                                    
                                    <select name="visibility" class="visibility-select">
                                        <option value="public">🌍 Public</option>
                                        <option value="friends">👥 Friends</option>
                                        <option value="private">🔒 Only Me</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn">Post</button>
                            </div>
                        </form>
                    </div>
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
                                        <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($post['profile_picture'] ?? null)); ?>" alt="Profile" class="post-avatar">
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
                        <p class="no-content">No posts to display.</p>
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