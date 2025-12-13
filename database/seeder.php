<?php
// database/seeder.php
// Usage: php database/seeder.php

require_once __DIR__ . '/../config/database.php';


// --- USERS ---
$users = [
    [
        'uuid' => uniqid('user_'),
        'full_name' => 'Alice Example',
        'display_name' => 'alice',
        'email' => 'alice@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'profile_picture' => null,
        'created_at' => date('Y-m-d H:i:s'),
    ],
    [
        'uuid' => uniqid('user_'),
        'full_name' => 'Bob Example',
        'display_name' => 'bob',
        'email' => 'bob@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'profile_picture' => null,
        'created_at' => date('Y-m-d H:i:s'),
    ],
    [
        'uuid' => uniqid('user_'),
        'full_name' => 'Charlie Example',
        'display_name' => 'charlie',
        'email' => 'charlie@example.com',
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'profile_picture' => null,
        'created_at' => date('Y-m-d H:i:s'),
    ],
];

$userIds = [];
try {
    $pdo->beginTransaction();
    foreach ($users as $user) {
        $stmt = $pdo->prepare('INSERT INTO users (uuid, full_name, display_name, email, password, profile_picture, created_at) VALUES (:uuid, :full_name, :display_name, :email, :password, :profile_picture, :created_at)');
        $stmt->execute($user);
        $userIds[] = $pdo->lastInsertId();
    }
    $pdo->commit();
    echo "Seeded users table successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed users: " . $e->getMessage() . "\n";
    exit(1);
}

// --- POSTS ---
$posts = [
    [
        'user_id' => $userIds[0],
        'content' => 'Hello world! This is Alice.',
        'image' => null,
        'visibility' => 'public',
        'created_at' => date('Y-m-d H:i:s'),
    ],
    [
        'user_id' => $userIds[1],
        'content' => 'Bob here, enjoying Skavoo!',
        'image' => null,
        'visibility' => 'friends',
        'created_at' => date('Y-m-d H:i:s'),
    ],
    [
        'user_id' => $userIds[2],
        'content' => 'Charlie posting a cool update.',
        'image' => null,
        'visibility' => 'public',
        'created_at' => date('Y-m-d H:i:s'),
    ],
];

$postIds = [];
try {
    $pdo->beginTransaction();
    foreach ($posts as $post) {
        $stmt = $pdo->prepare('INSERT INTO posts (user_id, content, image, visibility, created_at) VALUES (:user_id, :content, :image, :visibility, :created_at)');
        $stmt->execute($post);
        $postIds[] = $pdo->lastInsertId();
    }
    $pdo->commit();
    echo "Seeded posts table successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed posts: " . $e->getMessage() . "\n";
    exit(1);
}

// --- LIKES ---
$likes = [
    ['user_id' => $userIds[1], 'post_id' => $postIds[0]],
    ['user_id' => $userIds[2], 'post_id' => $postIds[0]],
    ['user_id' => $userIds[0], 'post_id' => $postIds[1]],
];
try {
    $pdo->beginTransaction();
    foreach ($likes as $like) {
        $stmt = $pdo->prepare('INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)');
        $stmt->execute($like);
    }
    $pdo->commit();
    echo "Seeded likes table successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed likes: " . $e->getMessage() . "\n";
    exit(1);
}

// --- COMMENTS ---
$comments = [
    ['user_id' => $userIds[1], 'post_id' => $postIds[0], 'comment' => 'Nice post, Alice!', 'created_at' => date('Y-m-d H:i:s')],
    ['user_id' => $userIds[2], 'post_id' => $postIds[0], 'comment' => 'Welcome!', 'created_at' => date('Y-m-d H:i:s')],
    ['user_id' => $userIds[0], 'post_id' => $postIds[1], 'comment' => 'Thanks Bob!', 'created_at' => date('Y-m-d H:i:s')],
];
try {
    $pdo->beginTransaction();
    foreach ($comments as $comment) {
        $stmt = $pdo->prepare('INSERT INTO comments (user_id, post_id, comment, created_at) VALUES (:user_id, :post_id, :comment, :created_at)');
        $stmt->execute($comment);
    }
    $pdo->commit();
    echo "Seeded comments table successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed comments: " . $e->getMessage() . "\n";
    exit(1);
}

// --- SHARES ---
$shares = [
    ['user_id' => $userIds[2], 'post_id' => $postIds[0]],
    ['user_id' => $userIds[0], 'post_id' => $postIds[2]],
];
try {
    $pdo->beginTransaction();
    foreach ($shares as $share) {
        $stmt = $pdo->prepare('INSERT INTO shares (user_id, post_id) VALUES (:user_id, :post_id)');
        $stmt->execute($share);
    }
    $pdo->commit();
    echo "Seeded shares table successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed shares: " . $e->getMessage() . "\n";
    exit(1);
}

// --- MESSAGES ---
$messages = [];
for ($i = 1; $i < count($userIds); $i++) {
    // 1st user sends a message to each other user
    $messages[] = [
        'sender_id' => $userIds[0],
        'receiver_id' => $userIds[$i],
        'message' => 'Hello ' . ucfirst($users[$i]['display_name']) . ', this is ' . ucfirst($users[0]['display_name']) . '!',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    // Each other user replies to the 1st user
    $messages[] = [
        'sender_id' => $userIds[$i],
        'receiver_id' => $userIds[0],
        'message' => 'Hi ' . ucfirst($users[0]['display_name']) . ', this is ' . ucfirst($users[$i]['display_name']) . '!',
        'created_at' => date('Y-m-d H:i:s'),
    ];
}
try {
    $pdo->beginTransaction();
    foreach ($messages as $msg) {
        $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (:sender_id, :receiver_id, :message, :created_at)');
        $stmt->execute($msg);
    }
    $pdo->commit();
    echo "Seeded messages table successfully.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed to seed messages: " . $e->getMessage() . "\n";
    exit(1);
}
