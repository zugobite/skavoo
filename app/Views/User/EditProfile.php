<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['full_name']) ?> – Profile | Skavoo</title>
    <meta name="description" content="Edit the profile of <?= htmlspecialchars($user['full_name']) ?> on Skavoo.">
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
        <a href="/messages">Messages</a>
    </nav>

    <div class="container">
        <aside class="sidebar">
            <div class="card">
                <h2>Profile Picture</h2>
                <div class="avatar-input">
                    <img class="profile-pic" id="avatar-preview-mini"
                        src="<?= \App\Helpers\e($user['profile_picture'] ?: '/images/avatar-default.png'); ?>"
                        alt="Preview">
                    <div>
                        <div class="file-input-wrapper">
                            <label for="profile_picture">Choose File</label>
                            <input type="file"
                                name="profile_picture"
                                id="profile_picture"
                                form="edit-profile-form"
                                accept=".jpg,.jpeg,.png,.webp"
                                onchange="document.getElementById('avatar-file-name').textContent = this.files[0]?.name || 'No file chosen';">
                            <span id="avatar-file-name" class="file-name">No file chosen</span>
                        </div>

                        <span class="help">JPEG, PNG or WEBP. Max 2MB.</span>
                        <?php if (!empty($errors['profile_picture'])): ?>
                            <div class="error"><?= \App\Helpers\e($errors['profile_picture']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </aside>

        <div class="content-wrapper">
            <main class="content">
                <div class="card">
                    <h2>Profile Details</h2>

                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="flash success">
                            <?= \App\Helpers\e($_SESSION['flash_success']); ?>
                            <?php unset($_SESSION['flash_success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($_SESSION['flash_error'])): ?>
                        <div class="flash error">
                            <?= \App\Helpers\e($_SESSION['flash_error']); ?>
                            <?php unset($_SESSION['flash_error']); ?>
                        </div>
                    <?php endif; ?>

                    <form id="edit-profile-form"
                        action="/user/profile/<?= \App\Helpers\e($user['uuid']); ?>/edit"
                        method="post"
                        enctype="multipart/form-data"
                        novalidate>
                        <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">

                        <div class="form-group">
                            <input class="form-control" type="text" id="full_name" name="full_name"
                                value="<?= \App\Helpers\e($user['full_name']); ?>" maxlength="120"
                                placeholder="Full Name" required>
                            <span class="help">Required. 120 characters max.</span>
                            <?php if (!empty($errors['full_name'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['full_name']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="actions" style="margin-top: 10px !important;">
                            <button type="submit" class="btn">Save Changes</button>
                            <a href="/user/profile/<?= \App\Helpers\e($user['uuid']); ?>">Cancel</a>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Live avatar preview
        document.addEventListener('DOMContentLoaded', function() {
            const fileInput = document.getElementById('profile_picture');
            const targets = [document.getElementById('avatar-preview-img'), document.getElementById('avatar-preview-mini')];

            if (!fileInput) return;
            fileInput.addEventListener('change', function(e) {
                const file = fileInput.files && fileInput.files[0];
                if (!file) return;

                const valid = ['image/jpeg', 'image/png', 'image/webp'];
                if (!valid.includes(file.type)) {
                    alert('Please select a JPEG, PNG, or WEBP image.');
                    fileInput.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    alert('Image must be 2MB or smaller.');
                    fileInput.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(evt) {
                    targets.forEach(t => {
                        if (t) t.src = evt.target.result;
                    });
                };
                reader.readAsDataURL(file);
            });
        });
    </script>

    <script src="/js/forms.js" defer></script>
</body>

</html>