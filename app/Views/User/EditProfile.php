<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['full_name']) ?> – Profile | Skavoo</title>
    <meta name="description" content="View the profile of <?= htmlspecialchars($user['full_name']) ?> on Skavoo.">
    <link rel="stylesheet" href="/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Pixelify+Sans:wght@400;700&display=swap" rel="stylesheet">

    <script src="/js/search.js" defer></script>

    <style>
        .container {
            max-width: 720px;
            margin: 24px auto;
            padding: 16px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            background: #111;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .btn-secondary {
            background: #666;
            color: #fff;
            text-decoration: none;
            margin-left: 8px;
        }

        .alert {
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .alert-danger {
            background: #fee;
            border: 1px solid #fcc;
        }

        .alert-success {
            background: #efe;
            border: 1px solid #cfc;
        }

        .text-muted {
            color: #888;
        }
    </style>
</head>

<body>
    <?php include __DIR__ . '/../Components/Header.php'; ?>
    <div class="container">
        <h1>Edit Profile</h1>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger"><?= \App\Helpers\e($_SESSION['flash_error']);
                                            unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success"><?= \App\Helpers\e($_SESSION['flash_success']);
                                                unset($_SESSION['flash_success']); ?></div>
        <?php endif; ?>

        <form action="/user/profile/<?= \App\Helpers\e($user['uuid']); ?>" method="post" enctype="multipart/form-data" id="edit-profile-form" novalidate>
            <input type="hidden" name="csrf" value="<?= \App\Helpers\e($csrf); ?>">

            <div class="form-group">
                <label for="full_name">Display Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= \App\Helpers\e($user['full_name']); ?>" maxlength="120" required>
                <small class="text-muted">Required, max 120 characters.</small>
            </div>

            <div class="form-group">
                <label for="profile_picture">Profile Picture (JPEG, PNG, WEBP, ≤ 2MB)</label>
                <input type="file" id="profile_picture" name="profile_picture" accept=".jpg,.jpeg,.png,.webp">
                <div class="image-preview" id="avatar-preview" style="margin-top:8px;">
                    <img src="<?= \App\Helpers\e($user['profile_picture'] ? $user['profile_picture'] : '/images/avatar-default.png'); ?>"
                        alt="Current avatar" id="avatar-preview-img"
                        style="max-width:120px;border-radius:50%;display:block;">
                </div>
            </div>

            <button type="submit" class="btn">Save Changes</button>
            <a href="/user/profile/<?= \App\Helpers\e($user['uuid']); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script src="/js/forms.js" defer></script>
</body>

</html>