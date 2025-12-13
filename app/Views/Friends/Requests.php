<?php
/** @var array $incomingRequests */
/** @var array $outgoingRequests */
/** @var string $csrf */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Friend Requests | Skavoo</title>
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
                    <li><a href="/friends">👥 All Friends</a></li>
                    <li><a href="/friends/requests" class="active">📨 Friend Requests</a></li>
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
                <h2>Incoming Friend Requests</h2>
                <?php if (!empty($incomingRequests)): ?>
                    <div class="friend-requests-grid">
                        <?php foreach ($incomingRequests as $request): ?>
                            <div class="friend-request-card">
                                <a href="/user/profile/<?= \App\Helpers\e($request['uuid']); ?>" class="friend-request-avatar">
                                    <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($request['profile_picture'] ?? null)); ?>" 
                                         alt="<?= \App\Helpers\e($request['full_name']); ?>" 
                                         class="friend-avatar">
                                </a>
                                <div class="friend-request-info">
                                    <a href="/user/profile/<?= \App\Helpers\e($request['uuid']); ?>" class="friend-name">
                                        <?= \App\Helpers\e($request['full_name']); ?>
                                    </a>
                                    <small class="request-time">
                                        <?= date('M j, Y', strtotime($request['requested_at'])); ?>
                                    </small>
                                </div>
                                <div class="friend-request-actions">
                                    <form action="/friends/accept" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                        <input type="hidden" name="request_id" value="<?= (int)$request['request_id']; ?>">
                                        <button type="submit" class="btn">Accept</button>
                                    </form>
                                    <form action="/friends/reject" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                        <input type="hidden" name="request_id" value="<?= (int)$request['request_id']; ?>">
                                        <button type="submit" class="btn">Decline</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-content">No pending friend requests.</p>
                <?php endif; ?>
            </main>

            <main class="content" style="margin-top: 20px;">
                <h2>Sent Requests</h2>
                <?php if (!empty($outgoingRequests)): ?>
                    <div class="friend-requests-grid">
                        <?php foreach ($outgoingRequests as $request): ?>
                            <div class="friend-request-card">
                                <a href="/user/profile/<?= \App\Helpers\e($request['uuid']); ?>" class="friend-request-avatar">
                                    <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($request['profile_picture'] ?? null)); ?>" 
                                         alt="<?= \App\Helpers\e($request['full_name']); ?>" 
                                         class="friend-avatar">
                                </a>
                                <div class="friend-request-info">
                                    <a href="/user/profile/<?= \App\Helpers\e($request['uuid']); ?>" class="friend-name">
                                        <?= \App\Helpers\e($request['full_name']); ?>
                                    </a>
                                    <small class="request-time">
                                        Sent <?= date('M j, Y', strtotime($request['requested_at'])); ?>
                                    </small>
                                </div>
                                <div class="friend-request-actions">
                                    <form action="/friends/cancel" method="POST">
                                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                        <input type="hidden" name="receiver_id" value="<?= (int)$request['id']; ?>">
                                        <button type="submit" class="btn">❌ Cancel Request</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-content">No pending sent requests.</p>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>
