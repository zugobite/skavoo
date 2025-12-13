<?php
/** @var array $friends */
/** @var int $pendingCount */
/** @var string $csrf */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Friends | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">
    <script src="/js/search.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/../Components/Header.php'; ?>

    <div class="container">
        <aside class="sidebar">
            <div class="card">
                <h2>Quick Links</h2>
                <ul class="quick-links">
                    <li><a href="/friends" class="active">👥 All Friends</a></li>
                    <li>
                        <a href="/friends/requests">
                            📨 Friend Requests
                            <?php if ($pendingCount > 0): ?>
                                <span class="badge-inline"><?= (int)$pendingCount; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="/feed">🏠 Back to Feed</a></li>
                </ul>
            </div>
        </aside>

        <div class="content-wrapper">
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flash success"><?= \App\Helpers\e($_SESSION['flash_success']); ?><?php unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flash error"><?= \App\Helpers\e($_SESSION['flash_error']); ?><?php unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <main class="content">
                <h2>My Friends (<?= count($friends); ?>)</h2>
                <?php if (!empty($friends)): ?>
                    <div class="friends-grid">
                        <?php foreach ($friends as $friend): ?>
                            <div class="friend-card">
                                <a href="/user/profile/<?= \App\Helpers\e($friend['uuid']); ?>" class="friend-card-avatar">
                                    <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($friend['profile_picture'] ?? null)); ?>" 
                                         alt="<?= \App\Helpers\e($friend['full_name']); ?>" 
                                         class="friend-avatar-lg">
                                </a>
                                <div class="friend-card-info">
                                    <a href="/user/profile/<?= \App\Helpers\e($friend['uuid']); ?>" class="friend-name">
                                        <?= \App\Helpers\e($friend['full_name']); ?>
                                    </a>
                                    <?php if (!empty($friend['friends_since'])): ?>
                                        <small class="friends-since">
                                            Friends since <?= date('M Y', strtotime($friend['friends_since'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <div class="friend-card-actions">
                                    <a href="/messages/<?= \App\Helpers\e($friend['uuid']); ?>" class="btn-message" title="Message">
                                        💬
                                    </a>
                                    <form action="/friends/remove" method="POST" style="display:inline;" 
                                          onsubmit="return confirm('Are you sure you want to unfriend <?= \App\Helpers\e($friend['full_name']); ?>?');">
                                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                        <input type="hidden" name="friend_id" value="<?= (int)$friend['id']; ?>">
                                        <button type="submit" class="btn-unfriend" title="Unfriend">✕</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-content">You haven't added any friends yet.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>
