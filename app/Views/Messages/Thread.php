<?php

/** @var array $other */
/** @var array $messages */
/** @var string $csrf */
$meUuid = $_SESSION['user_uuid'] ?? '';
$meId   = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Chat with <?= \App\Helpers\e($other['name']); ?> | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">

    <script src="/js/search.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/../Components/Header.php'; ?>

    <div class="container">
        <aside class="sidebar">
            <div class="card">
                <h2>Chatting With</h2>
                <div class="row" style="display:flex; align-items:center; gap:10px;">
                    <img class="profile-pic-chat" id="avatar-preview-mini"
                        src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($other['profile_picture'] ?? null)); ?>"
                        alt="Profile Picture">
                    <div><strong><?= \App\Helpers\e($other['name']); ?></strong></div>
                </div>
                <div style="margin-top:10px; gap:10px; display:flex;">
                    <a class="btn secondary" href="/messages">Back to Inbox</a>
                    <a class="btn" href="/user/profile/<?= \App\Helpers\e($other['uuid']); ?>">View Profile</a>
                </div>
            </div>
        </aside>

        <div class="content-wrapper">
            <main class="content">
                <div class="card">
                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="flash error"><?= \App\Helpers\e($_SESSION['flash_error']); ?><?php unset($_SESSION['flash_error']); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="flash success"><?= \App\Helpers\e($_SESSION['flash_success']); ?><?php unset($_SESSION['flash_success']); ?></div>
                    <?php endif; ?>

                    <div id="chat" class="chat-window">
                        <?php foreach ($messages as $m): ?>
                            <?php $mine = ((int)$m['sender_id'] === (int)$meId); ?>
                            <div class="bubble <?= $mine ? 'me' : 'them' ?>">
                                <div><?= nl2br(\App\Helpers\e($m['body'])); ?></div>
                                <small><?= \App\Helpers\e(date('Y-m-d H:i', strtotime($m['created_at']))); ?></small>
                            </div>
                        <?php endforeach; ?>
                        <div id="bottom"></div>
                    </div>

                    <form class="chat-form" action="/messages/<?= \App\Helpers\e($other['uuid']); ?>" method="post"
                        style="display:flex; align-items:flex-end; gap:8px;">
                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">

                        <textarea name="body"
                            placeholder="Write a message…"
                            maxlength="3000"
                            required></textarea>

                        <button type="submit"
                            style="flex:0 0 auto; height:35px; margin-bottom: 10px;">
                            Send
                        </button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Auto-scroll to bottom (once)
        const bottom = document.getElementById('bottom');
        if (bottom) bottom.scrollIntoView({
            behavior: 'instant',
            block: 'end'
        });
    </script>

</body>

</html>