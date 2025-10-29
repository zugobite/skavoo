<?php

/**
 * @var array $threads
 */

use function App\Helpers\e;

include __DIR__ . '/../Components/Header.php';
?>
<div class="container">
    <h1>Messages</h1>

    <?php if (empty($threads)): ?>
        <p class="text-muted">No conversations yet. Use search to find a user and start chatting.</p>
    <?php else: ?>
        <ul class="thread-list">
            <?php foreach ($threads as $t): ?>
                <li class="thread-item">
                    <a href="/messages/<?= e($t['partner_uuid']); ?>" class="thread-link">
                        <img class="avatar" src="<?= e($t['partner_avatar'] ? $t['partner_avatar'] : '/images/avatar-default.png'); ?>" alt="">
                        <div class="meta">
                            <div class="name"><?= e($t['partner_name']); ?></div>
                            <div class="snippet">
                                <?= e(mb_strimwidth(isset($t['content']) ? $t['content'] : '', 0, 80, '…')); ?>
                            </div>
                            <div class="time"><?= e(date('Y-m-d H:i', strtotime($t['created_at']))); ?></div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<style>
    .container {
        max-width: 880px;
        margin: 24px auto;
        padding: 0 12px;
    }

    .thread-list {
        list-style: none;
        padding: 0;
        margin: 12px 0;
    }

    .thread-item {
        border-bottom: 1px solid #eee;
    }

    .thread-link {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        text-decoration: none;
        color: inherit;
    }

    .avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f3f3;
    }

    .meta .name {
        font-weight: 600;
    }

    .meta .snippet {
        color: #555;
        font-size: 0.95rem;
    }

    .meta .time {
        color: #999;
        font-size: 0.85rem;
    }

    .text-muted {
        color: #888;
    }
</style>