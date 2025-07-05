<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/auth/login.php");
    exit();
}

$message = "";

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user_id'];
    $age_category = $_POST['age_category'] ?? 'Teen';

    // Clean tags
    $tags_input = $_POST['tags'] ?? '';
    $tags_cleaned = implode(',', array_filter(array_map(function ($tag) {
        return ltrim(trim($tag), '#');
    }, preg_split('/[\s,]+/', $tags_input))));

    $media_url = '';
    $media_type = 'none';

    // Handle media upload
    if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === 0) {
        $file = $_FILES['media'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $type = mime_content_type($file['tmp_name']);

        if (str_starts_with($type, 'image')) {
            $media_type = 'image';
            $target_dir = '../storage/image/';
        } elseif (str_starts_with($type, 'video')) {
            $media_type = 'video';
            $target_dir = '../storage/video/';
        }

        if ($media_type !== 'none') {
            $filename = uniqid() . "." . $ext;
            $full_path = $target_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $full_path)) {
                $media_url = str_replace('../', '', $full_path);
            }
        }
    }

    // Insert post
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, media_url, media_type, tags) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $content, $media_url, $media_type, $tags_cleaned);

    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } else {
        $message = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

// Success message
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "✅ Post uploaded successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Create Post | AgeOgram</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
  <?php require_once '../includes/header.php'; ?>

  <div class="flex ">
    <!-- Sidebar -->
    <aside class="w-1/4 hidden md:block p-4 mt-14 left-0 h-[50vh]">
      <?php require_once '../includes/sidebar.php'; ?>
    </aside>

    <!-- Main Content -->
    <main class="flex justify-center items-start md:ml-[25%] p-6 mt-20">
      <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4 text-indigo-600">Create a New Post</h2>

        <?php if ($message): ?>
          <div class="mb-4 p-3 rounded text-sm bg-blue-100 text-blue-700"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
          <!-- Content -->
          <textarea name="content" rows="4" placeholder="What's on your mind?" class="w-full p-3 border border-gray-300 rounded" required></textarea>

          <!-- Media Upload -->
          <input type="file" name="media" accept="image/*,video/*" class="w-full" onchange="previewMedia(event)">

          <!-- Tags -->
          <input type="text" name="tags" placeholder="Use #tags e.g. #fun #travel" class="w-full p-3 border border-gray-300 rounded">

          <!-- Media Preview -->
          <div id="media-preview" class="mt-4 hidden">
            <p class="text-sm text-gray-500 mb-1">Preview:</p>
            <img id="preview-image" class="max-w-full rounded hidden" />
            <video id="preview-video" controls class="max-w-full rounded hidden"></video>
          </div>

          <!-- Submit -->
          <button type="submit" class="w-full py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Post</button>
        </form>
      </div>
    </main>
  </div>

  <script>
    function previewMedia(event) {
      const file = event.target.files[0];
      const img = document.getElementById('preview-image');
      const video = document.getElementById('preview-video');
      const previewBox = document.getElementById('media-preview');

      if (!file) return;

      img.classList.add('hidden');
      video.classList.add('hidden');
      previewBox.classList.remove('hidden');

      const url = URL.createObjectURL(file);
      if (file.type.startsWith('image')) {
        img.src = url;
        img.classList.remove('hidden');
      } else if (file.type.startsWith('video')) {
        video.src = url;
        video.classList.remove('hidden');
      }
    }
  </script>
</body>
</html>
