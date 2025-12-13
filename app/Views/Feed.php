<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Feed | Skavoo</title>
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">
    <script src="/js/search.js" defer></script>
    <script src="/js/feed.js" defer></script>
</head>

<body>
    <?php include __DIR__ . '/Components/Header.php'; ?>

    <div class="feed-layout">
        <!-- Left Sidebar -->
        <aside class="people-sidebar">
            <!-- Quick Navigation -->
            <div class="card">
                <h2>Quick Links</h2>
                <ul class="quick-links">
                    <li>
                        <a href="/user/profile/<?= \App\Helpers\e($_SESSION['user_uuid']); ?>">
                            👤 My Profile
                        </a>
                    </li>
                    <li>
                        <a href="/friends">
                            👥 Friends
                        </a>
                    </li>
                    <li>
                        <a href="/friends/requests">
                            📬 Friend Requests
                            <?php if (!empty($pendingFriendRequests) && $pendingFriendRequests > 0): ?>
                                <span class="badge-inline"><?= (int)$pendingFriendRequests; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="/messages">
                            💬 Messages
                        </a>
                    </li>
                </ul>
            </div>

            <!-- People You May Know -->
            <div class="card">
                <h2>People You May Know</h2>
                <ul class="people-list">
                    <?php if (!empty($people) && is_array($people)): ?>
                        <?php foreach ($people as $person): ?>
                            <li class="person-item">
                                <a href="/user/profile/<?= \App\Helpers\e($person['uuid'] ?? ''); ?>" class="person-link">
                                    <img class="person-avatar-small" 
                                         src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($person['profile_picture'] ?? null)); ?>"
                                         alt="<?= \App\Helpers\e($person['full_name'] ?? 'User'); ?>">
                                    <span class="person-name"><?= \App\Helpers\e($person['full_name'] ?? 'Unknown User'); ?></span>
                                </a>
                                <a href="/user/profile/<?= \App\Helpers\e($person['uuid'] ?? ''); ?>" class="btn-add-small" title="View Profile">
                                    👁
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="no-suggestions">
                            <p>No suggestions at this time.</p>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </aside>

        <!-- Main Feed -->
        <main class="post-feed">
            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['flash_success'])): ?>
                <div class="flash success"><?= \App\Helpers\e($_SESSION['flash_success']); ?><?php unset($_SESSION['flash_success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['flash_error'])): ?>
                <div class="flash error"><?= \App\Helpers\e($_SESSION['flash_error']); ?><?php unset($_SESSION['flash_error']); ?></div>
            <?php endif; ?>

            <!-- Create Post Card -->
            <div class="post-block create-post-card">
                <div class="card">
                    <form action="/posts/create" method="POST" enctype="multipart/form-data" id="create-post-form">
                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                        
                        <div class="create-post-header">
                            <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($_SESSION['profile_picture'] ?? null)); ?>" 
                                 alt="Your avatar" class="post-avatar">
                            <textarea name="content" 
                                      placeholder="What's on your mind?" 
                                      class="post-textarea" 
                                      rows="2"
                                      required
                                      maxlength="5000"></textarea>
                        </div>

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

            <!-- Posts -->
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-block" id="post-<?= (int)$post['id']; ?>">
                        <div class="card">
                            <!-- Post Header -->
                            <div class="post-header-layout">
                                <div class="post-user-info">
                                    <a href="/user/profile/<?= \App\Helpers\e($post['uuid'] ?? ''); ?>">
                                        <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($post['profile_picture'] ?? null)); ?>" 
                                             alt="Profile" class="post-avatar" />
                                    </a>
                                    <div class="post-user-text">
                                        <strong>
                                            <a href="/user/profile/<?= \App\Helpers\e($post['uuid'] ?? ''); ?>">
                                                <?= \App\Helpers\e($post['full_name'] ?? 'Unknown User'); ?>
                                            </a>
                                        </strong>
                                        <small class="post-date">
                                            <?= \App\Helpers\e(timeAgo($post['created_at'])); ?>
                                            <?php if ($post['visibility'] === 'friends'): ?>
                                                <span title="Friends only">👥</span>
                                            <?php elseif ($post['visibility'] === 'private'): ?>
                                                <span title="Only you">🔒</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                
                                <?php if ((int)$post['user_id'] === (int)$_SESSION['user_id']): ?>
                                    <div class="post-options dropdown">
                                        <button class="btn btn-sm" onclick="toggleDropdown(this)">⋮</button>
                                        <div class="dropdown-menu">
                                            <form action="/posts/<?= (int)$post['id']; ?>/delete" method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this post?');">
                                                <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                                <button type="submit" class="dropdown-item text-danger">🗑 Delete Post</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <hr class="post-divider" />

                            <!-- Post Content -->
                            <div class="post-body">
                                <p><?= nl2br(\App\Helpers\e($post['content'])); ?></p>

                                <?php if (!empty($post['image'])): ?>
                                    <?php
                                    $ext = strtolower(pathinfo($post['image'], PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
                                    ?>
                                        <img src="/uploads/<?= \App\Helpers\e($post['image']); ?>" alt="Post media" class="post-media" />
                                    <?php elseif (in_array($ext, ['mp4', 'webm'])): ?>
                                        <video controls class="post-media">
                                            <source src="/uploads/<?= \App\Helpers\e($post['image']); ?>" type="video/<?= $ext ?>" />
                                        </video>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Post Stats -->
                            <div class="post-stats">
                                <?php if ((int)$post['like_count'] > 0): ?>
                                    <span class="stat-item">❤️ <?= (int)$post['like_count']; ?></span>
                                <?php endif; ?>
                                <?php if ((int)$post['comment_count'] > 0): ?>
                                    <span class="stat-item"><?= (int)$post['comment_count']; ?> comment<?= (int)$post['comment_count'] !== 1 ? 's' : ''; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- Post Actions -->
                            <div class="post-actions">
                                <form action="/posts/<?= (int)$post['id']; ?>/like" method="POST" class="like-form">
                                    <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                    <button type="submit" class="btn <?= (int)$post['user_liked'] ? 'liked' : ''; ?>">
                                        <?= (int)$post['user_liked'] ? '❤️' : '🤍'; ?> Like
                                    </button>
                                </form>
                                <button type="button" class="btn" onclick="toggleComments(<?= (int)$post['id']; ?>)">
                                    💬 Comment
                                </button>
                                <button type="button" class="btn" onclick="sharePost(<?= (int)$post['id']; ?>)">
                                    🔗 Share
                                </button>
                            </div>

                            <!-- Comments Section -->
                            <div class="comments-section" id="comments-<?= (int)$post['id']; ?>" style="display:none;">
                                <hr class="comment-divider">
                                
                                <!-- Existing Comments -->
                                <?php if (!empty($post['comments'])): ?>
                                    <div class="comments-list">
                                        <?php foreach ($post['comments'] as $comment): ?>
                                            <div class="comment-item">
                                                <a href="/user/profile/<?= \App\Helpers\e($comment['uuid']); ?>">
                                                    <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($comment['profile_picture'] ?? null)); ?>" 
                                                         alt="<?= \App\Helpers\e($comment['full_name']); ?>" 
                                                         class="comment-avatar">
                                                </a>
                                                <div class="comment-content">
                                                    <div class="comment-bubble">
                                                        <a href="/user/profile/<?= \App\Helpers\e($comment['uuid']); ?>" class="comment-author">
                                                            <?= \App\Helpers\e($comment['full_name']); ?>
                                                        </a>
                                                        <p class="comment-text"><?= nl2br(\App\Helpers\e($comment['comment'])); ?></p>
                                                    </div>
                                                    <div class="comment-meta">
                                                        <span class="comment-time"><?= \App\Helpers\e(timeAgo($comment['created_at'])); ?></span>
                                                        <?php if ((int)$comment['user_id'] === (int)$_SESSION['user_id']): ?>
                                                            <form action="/comments/<?= (int)$comment['id']; ?>/delete" method="POST" style="display:inline;">
                                                                <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                                                <button type="submit" class="btn">Delete</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <?php if ((int)$post['comment_count'] > 3): ?>
                                        <a href="#" class="view-all-comments">View all <?= (int)$post['comment_count']; ?> comments</a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <!-- Add Comment Form -->
                                <form action="/posts/<?= (int)$post['id']; ?>/comment" method="POST" class="comment-form">
                                    <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">
                                    <img src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($_SESSION['profile_picture'] ?? null)); ?>" 
                                         alt="Your avatar" class="comment-avatar">
                                    <input type="text" name="comment" placeholder="Write a comment..." 
                                           class="comment-input" required maxlength="1000">
                                    <button type="submit" class="btn">Post</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="post-block">
                    <div>
                        <h2>No Posts Yet</h2>
                        <p class="no-content">Be the first to share something! Create a post above or find friends to see their posts.</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>
