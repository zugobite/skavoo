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
        <img class="profile-pic" id="avatar-preview-mini"
            src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($user['profile_picture'] ?? null)); ?>"
            alt="Profile Picture">

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
                        src="<?= \App\Helpers\e(\App\Helpers\profilePicturePath($user['profile_picture'] ?? null)); ?>"
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

                        <div class="form-group">
                            <textarea class="form-control" id="bio" name="bio" maxlength="1000" placeholder="Bio (about you)"><?= \App\Helpers\e($user['bio'] ?? ''); ?></textarea>
                            <span class="help">Tell people about yourself. 1000 characters max.</span>
                            <?php if (!empty($errors['bio'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['bio']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="birthday">Birthday</label>
                            <input class="form-control" type="date" id="birthday" name="birthday" value="<?= \App\Helpers\e($user['birthday'] ?? ''); ?>">
                            <?php if (!empty($errors['birthday'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['birthday']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender">
                                <option value="">-- Select --</option>
                                <option value="male" <?= (isset($user['gender']) && $user['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= (isset($user['gender']) && $user['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= (isset($user['gender']) && $user['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
                                <option value="prefer_not_to_say" <?= (isset($user['gender']) && $user['gender'] === 'prefer_not_to_say') ? 'selected' : '' ?>>Prefer not to say</option>
                            </select>
                            <?php if (!empty($errors['gender'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['gender']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input class="form-control" type="text" id="location" name="location" maxlength="100" placeholder="Location (City, Country)" value="<?= \App\Helpers\e($user['location'] ?? ''); ?>">
                            <?php if (!empty($errors['location'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['location']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="relationship_status">Relationship Status</label>
                            <select class="form-control" id="relationship_status" name="relationship_status">
                                <option value="">-- Select --</option>
                                <option value="single" <?= (isset($user['relationship_status']) && $user['relationship_status'] === 'single') ? 'selected' : '' ?>>Single</option>
                                <option value="in_a_relationship" <?= (isset($user['relationship_status']) && $user['relationship_status'] === 'in_a_relationship') ? 'selected' : '' ?>>In a relationship</option>
                                <option value="married" <?= (isset($user['relationship_status']) && $user['relationship_status'] === 'married') ? 'selected' : '' ?>>Married</option>
                                <option value="complicated" <?= (isset($user['relationship_status']) && $user['relationship_status'] === 'complicated') ? 'selected' : '' ?>>It's complicated</option>
                            </select>
                            <?php if (!empty($errors['relationship_status'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['relationship_status']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input class="form-control" type="text" id="work" name="work" maxlength="100" placeholder="Work (Job/Company)" value="<?= \App\Helpers\e($user['work'] ?? ''); ?>">
                            <?php if (!empty($errors['work'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['work']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input class="form-control" type="text" id="education" name="education" maxlength="100" placeholder="Education (School/University)" value="<?= \App\Helpers\e($user['education'] ?? ''); ?>">
                            <?php if (!empty($errors['education'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['education']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input class="form-control" type="url" id="website" name="website" maxlength="100" placeholder="Website (https://...)" value="<?= \App\Helpers\e($user['website'] ?? ''); ?>">
                            <?php if (!empty($errors['website'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['website']); ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <input class="form-control" type="text" id="phone" name="phone" maxlength="30" placeholder="Phone" value="<?= \App\Helpers\e($user['phone'] ?? ''); ?>">
                            <?php if (!empty($errors['phone'])): ?>
                                <div class="error"><?= \App\Helpers\e($errors['phone']); ?></div>
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