<?php

/**
 * @var array  $partner
 * @var array  $messages
 * @var string $csrf
 */

use function App\Helpers\e;

include __DIR__ . '/../Components/Header.php';
$meId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
?>
<div class="container">
    <div class="thread-header">
        <img class="avatar" src="<?= e($partner['profile_picture'] ? $partner['profile_picture'] : '/images/avatar-default.png'); ?>" alt="">
        <div>
            <h1><?= e($partner['full_name']); ?></h1>
            <small class="text-muted">@<?= e($partner['uuid']); ?></small>
        </div>
    </div>

    <div class="thread-messages" id="thread-messages">
        <?php foreach ($messages as $m): ?>
            <?php $isMine = intval($m['sender_id']) === intval($meId); ?>
            <div class="bubble <?= $isMine ? 'mine' : 'theirs' ?>">
                <div class="content"><?= nl2br(e($m['content'])); ?></div>
                <div class="time"><?= e(date('Y-m-d H:i', strtotime($m['created_at']))); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form class="send-box" action="/messages/<?= e($partner['uuid']); ?>" method="post" id="send-message-form" novalidate>
        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
        
        <textarea name="content" id="content" rows="2" maxlength="2000" placeholder="Write a message…" required></textarea>
        <button type="submit" class="btn">Send</button>
    </form>
</div>

<script>
    (function() {
        var el = document.getElementById('thread-messages');
        if (el) el.scrollTop = el.scrollHeight;
    })();
</script>

<style>
    .container {
        max-width: 880px;
        margin: 24px auto;
        padding: 0 12px;
    }

    .thread-header {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 12px;
    }

    .avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f3f3;
    }

    .thread-messages {
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 12px;
        height: 54vh;
        overflow: auto;
        background: #fff;
    }

    .bubble {
        max-width: 70%;
        padding: 10px 12px;
        margin: 8px 0;
        border-radius: 12px;
        position: relative;
    }

    .bubble.mine {
        margin-left: auto;
        background: #111;
        color: #fff;
        border-top-right-radius: 4px;
    }

    .bubble.theirs {
        margin-right: auto;
        background: #f6f6f6;
        color: #111;
        border-top-left-radius: 4px;
    }

    .bubble .time {
        font-size: 0.8rem;
        color: #999;
        margin-top: 4px;
        text-align: right;
    }

    .send-box {
        margin-top: 12px;
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }

    .send-box textarea {
        flex: 1;
        resize: vertical;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
    }

    .btn {
        padding: 10px 16px;
        border-radius: 8px;
        background: #111;
        color: #fff;
        border: none;
        cursor: pointer;
    }

    .text-muted {
        color: #888;
    }
</style>