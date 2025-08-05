<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feed | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">

    <script src="/js/search.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/Components/Header.php'; ?>

    <div class="feed-layout">
        <!-- Sidebar -->
        <aside class="people-sidebar">
            <h1>People You May Know</h1>
            <ul class="people-list">
                <?php if (!empty($people) && is_array($people)): ?>
                    <?php foreach ($people as $person): ?>
                        <li>
                            <div class="person-entry">
                                <!-- Profile Picture -->
                                <?php if (!empty($person['profile_picture'])): ?>
                                    <img src="/uploads/<?= htmlspecialchars($person['profile_picture']) ?>" alt="Profile Picture" class="person-avatar">
                                <?php else: ?>
                                    <div class="person-avatar placeholder"></div>
                                <?php endif; ?>

                                <!-- Name and Add Friend -->
                                <strong>
                                    <a href="/user/profile/<?= htmlspecialchars($person['uuid'] ?? '') ?>">
                                        <?= htmlspecialchars($person['full_name'] ?? 'Unknown User') ?>
                                    </a>
                                </strong>

                                <form action="/friends/send" method="POST">
                                    <input type="hidden" name="receiver_id" value="<?= $person['id'] ?>">
                                    <button type="submit">Add Friend</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>
                        <p>No suggestions at this time.</p>
                    </li>
                <?php endif; ?>
            </ul>
        </aside>

        <!-- Post Feed -->
        <main class="post-feed">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-block">
                        <div class="post-inner-body">
                            <div class="post-header-layout">
                                <div class="post-user-info">
                                    <?php if (!empty($post['profile_picture'])): ?>
                                        <img src="/uploads/<?= htmlspecialchars($post['profile_picture']) ?>" alt="Profile" class="post-avatar" />
                                    <?php else: ?>
                                        <div class="post-avatar placeholder"></div>
                                    <?php endif; ?>
                                    <div class="post-user-text">
                                        <strong>
                                            <a href="/user/profile/<?= htmlspecialchars($post['uuid'] ?? '') ?>">
                                                <?= htmlspecialchars($post['full_name'] ?? 'Unknown User') ?>
                                            </a>
                                        </strong>
                                        <small class="post-date"><?= date('Y-m-d H:i', strtotime($post['created_at'])) ?></small>
                                    </div>
                                </div>
                                <div class="post-options">
                                    <span title="More options">&#8942;</span>
                                </div>
                            </div>

                            <hr class="post-divider" />

                            <div class="post-body">
                                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

                                <?php if (!empty($post['image'])): ?>
                                    <?php
                                    $ext = pathinfo($post['image'], PATHINFO_EXTENSION);
                                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif'])):
                                    ?>
                                        <img src="/uploads/<?= htmlspecialchars($post['image']) ?>" alt="Post media" class="post-media" />
                                    <?php elseif (in_array(strtolower($ext), ['mp4', 'webm'])): ?>
                                        <video controls class="post-media">
                                            <source src="/uploads/<?= htmlspecialchars($post['image']) ?>" type="video/<?= $ext ?>" />
                                        </video>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </main>
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