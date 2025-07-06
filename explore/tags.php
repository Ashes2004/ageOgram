<?php
 $query_tag = $_GET['tag'];
 echo $query_tag;
session_start();
 if (!isset($_SESSION['user_id'])) {
        header("Location: /AgeOgram/auth/login.php");
        exit();
    }
include_once '../includes/db.php';
    $user_id = $_SESSION['user_id'];

    // Fetch current user's DOB
    $stmt = $conn->prepare("SELECT dob FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        echo "User not found.";
        exit();
    }

    // Get current user's age category
    $dob = new DateTime($user['dob']);
    $age = (new DateTime())->diff($dob)->y;

    if ($age >= 13 && $age <= 19) $user_category = 'Teen';
    elseif ($age >= 20 && $age <= 29) $user_category = 'Young Adult';
    elseif ($age >= 30 && $age <= 59) $user_category = 'Adult';
    else $user_category = 'Senior';

    // Fetch all posts with user info
    $stmt = $conn->prepare("SELECT posts.*, users.name, users.profile_photo, users.dob FROM posts
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts_result = $stmt->get_result();

    $posts = [];
    while ($post = $posts_result->fetch_assoc()) {
    $poster_dob = new DateTime($post['dob']);
    $poster_age = (new DateTime())->diff($poster_dob)->y;

    if ($poster_age >= 13 && $poster_age <= 19) $poster_category = 'Teen';
    elseif ($poster_age >= 20 && $poster_age <= 29) $poster_category = 'Young Adult';
    elseif ($poster_age >= 30 && $poster_age <= 59) $poster_category = 'Adult';
    else $poster_category = 'Senior';

    if ($poster_category !== $user_category) {
        continue;
    }

    // Filter by tag
    $post_tags = array_map('trim', explode(',', $post['tags']));
    if ($query_tag && !in_array($query_tag, $post_tags)) {
        continue;
    }

    // Fetch comments
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

    // Final post list
    $post['comments'] = $comments;
    $post['like_count'] = $like_result['like_count'];
    $posts[] = $post;
}


    $stmt->close();


  echo '<pre>' ;
  print_r($posts);
  echo '</pre>';

?>


<!DOCTYPE html>
<html lang="en">
    <head>

        <meta charset="UTF-8" />
        <title>Dashboard | AgeOgram</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-white pt-16">
        <?php include_once '../includes/header.php'; ?>
</body> 
</html>    