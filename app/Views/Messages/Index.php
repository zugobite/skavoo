<?php

/** @var array $convos */
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">

    <script src="/js/search.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/../Components/Header.php'; ?>

    <div class="container">
        <aside class="sidebar">
            <div class="card">
                <h2>Conversations</h2>
                <?php if ($convos): ?>
                    <ul class="conversation-list" style="list-style:none; padding:0; margin:0;">
                        <?php foreach ($convos as $c): ?>
                            <li class="conversation-item" style="margin-bottom:10px;">
                                <a href="/messages/<?= \App\Helpers\e($c['other_uuid']); ?>"
                                    style="display:block; text-decoration:none; color:inherit; border-radius:8px; transition:background 0.2s ease;">
                                    <div class="row" style="display:flex; align-items:center; gap:10px;">
                                        <img class="profile-pic-chat" id="avatar-preview-mini"
                                            src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($c['profile_picture'] ?? null)); ?>"
                                            alt="Profile Picture">

                                        <div style="flex:1;">
                                            <div><strong><?= \App\Helpers\e($c['other_name']); ?></strong></div>
                                            <small class="text-muted">
                                                <?= \App\Helpers\e(mb_strimwidth($c['last_message'] ?? '', 0, 48, '…')); ?>
                                            </small>
                                        </div>

                                        <?php if ((int)$c['unread_count'] > 0): ?>
                                            <span class="badge-inline">
                                                <?= (int)$c['unread_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-content" style="margin-top: 0px !important; margin-bottom: 0px !important; padding-top: 0px !important; padding-bottom: 0px !important;">No conversations yet.</p>
                <?php endif; ?>
            </div>
        </aside>

        <div class="content-wrapper">
            <main class="content">
                <div>
                    <h2>Messages</h2>
                    <p class="no-content">Select a conversation on the left, or start a new one from a user's profile.</p>
                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="flash error"><?= \App\Helpers\e($_SESSION['flash_error']); ?><?php unset($_SESSION['flash_error']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="flash success"><?= \App\Helpers\e($_SESSION['flash_success']); ?><?php unset($_SESSION['flash_success']); ?></div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

</body>

</html>