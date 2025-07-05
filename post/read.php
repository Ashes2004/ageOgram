<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user DOB
$stmt = $conn->prepare("SELECT dob FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}

$dob = new DateTime($user['dob']);
$age = (new DateTime())->diff($dob)->y;

if ($age >= 13 && $age <= 19) $user_category = 'Teen';
elseif ($age >= 20 && $age <= 29) $user_category = 'Young Adult';
elseif ($age >= 30 && $age <= 59) $user_category = 'Adult';
else $user_category = 'Senior';

// Fetch posts with user info
$stmt = $conn->prepare("SELECT posts.*, users.name, users.profile_photo FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.age_category = ?
    ORDER BY posts.created_at DESC");
$stmt->bind_param("s", $user_category);
$stmt->execute();
$posts_result = $stmt->get_result();

$posts = [];
while ($post = $posts_result->fetch_assoc()) {
    // Fetch comments for this post
    $c_stmt = $conn->prepare("SELECT comments.*, users.name, users.profile_photo FROM comments 
        JOIN users ON comments.user_id = users.id 
        WHERE comments.post_id = ? ORDER BY comments.created_at ASC");
    $c_stmt->bind_param("i", $post['id']);
    $c_stmt->execute();
    $comments = $c_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $c_stmt->close();

    // Count likes
    $l_stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = ?");
    $l_stmt->bind_param("i", $post['id']);
    $l_stmt->execute();
    $like_result = $l_stmt->get_result()->fetch_assoc();
    $l_stmt->close();

    $post['comments'] = $comments;
    $post['like_count'] = $like_result['like_count'];
    $posts[] = $post;

  
    
}

if (empty($posts)) {
    echo "<p class='text-center text-gray-500'>No posts available for your age category.</p>";
    exit();
}
?>

<!-- Loop through posts -->
<?php foreach ($posts as $post): ?>
    <div class="bg-white p-4 rounded-xl shadow-md mb-6 max-w-2xl mx-auto">
        <!-- User -->
        <div class="flex items-center gap-3 mb-3">
            <img src="<?= htmlspecialchars($post['profile_photo']) ?>" class="w-10 h-10 rounded-full border" />
            <div>
                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($post['name']) ?></h3>
                <p class="text-sm text-gray-400"><?= date('M j, Y h:i A', strtotime($post['created_at'])) ?></p>
            </div>
        </div>

        <!-- Post Content -->
        <p class="mb-3"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

        <!-- Media -->
        <?php if ($post['media_type'] === 'image'): ?>
            <img src="/AgeOgram/<?= $post['media_url'] ?>" class="rounded-lg w-full mb-3" />
        <?php elseif ($post['media_type'] === 'video'): ?>
            <video controls class="rounded-lg w-full mb-3">
                <source src="/AgeOgram/<?= $post['media_url'] ?>" type="video/mp4">
            </video>
        <?php endif; ?>

        <!-- Tags -->
        <?php if (!empty($post['tags'])): ?>
            <div class="mb-3 text-sm text-gray-600">
                <?php foreach (explode(',', $post['tags']) as $tag): ?>
                    <span class="inline-block bg-gray-100 px-2 py-1 rounded-full mr-2">#<?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Social Buttons -->
        <div class="flex justify-between text-sm text-gray-600 mt-4 border-t pt-3">
            ❤️ <?= $post['like_count'] ?> Likes
            💬 <?= count($post['comments']) ?> Comments
        </div>

        <!-- Comments -->
        <div class="mt-3">
            <?php foreach ($post['comments'] as $comment): ?>
                <div class="flex gap-3 mb-2 items-start">
                    <img src="<?= htmlspecialchars($comment['profile_photo']) ?>" class="w-8 h-8 rounded-full object-cover" />
                    <div>
                        <p class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($comment['name']) ?></p>
                        <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                        <p class="text-xs text-gray-400"><?= date('M j, Y h:i A', strtotime($comment['created_at'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endforeach; ?>
