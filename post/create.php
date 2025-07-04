<?php
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/auth/login.php");
    exit();
}

$message = "";

// POST form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = htmlspecialchars($_POST['content'], ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user_id'];
    $age_category = $_POST['age_category'] ?? 'Teen';

    // Clean tags (strip #)
    $tags_input = $_POST['tags'] ?? '';
    $tags_cleaned = implode(',', array_filter(array_map(function ($tag) {
        return ltrim(trim($tag), '#');
    }, preg_split('/[\s,]+/', $tags_input))));

    $media_url = '';
    $media_type = 'none';

    // File Upload
    if (isset($_FILES['media']) && $_FILES['media']['error'] === 0) {
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

    // Insert into posts table
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, media_url, media_type, tags, age_category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $content, $media_url, $media_type, $tags_cleaned, $age_category);

    if ($stmt->execute()) {
        // ✅ Prevent form resubmission on refresh
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Show success message if redirected
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "Post uploaded successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Post | AgeOgram</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 min-h-screen">
  <div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-indigo-600">Create a New Post</h2>

    <?php if ($message): ?>
      <p class="mb-4 text-blue-600"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
      <!-- Post Content -->
      <textarea name="content" rows="3" placeholder="What's on your mind?" class="w-full p-3 border border-gray-300 rounded" required></textarea>

      <!-- Media Upload -->
      <input type="file" name="media" accept="image/*,video/*" class="w-full" onchange="previewMedia(event)">

      <!-- Tag Input -->
      <input type="text" name="tags" placeholder="Use #tags e.g. #fun #travel #memes" class="w-full p-3 border border-gray-300 rounded">

      <!-- Age Category Dropdown -->
      <select name="age_category" class="w-full p-3 border border-gray-300 rounded" required>
        <option value="Teen">Teen</option>
        <option value="Young Adult">Young Adult</option>
        <option value="Adult">Adult</option>
        <option value="Senior">Senior</option>
      </select>

      <!-- Live Preview -->
      <div id="media-preview" class="mt-4 hidden">
        <p class="text-sm text-gray-500 mb-1">Preview:</p>
        <img id="preview-image" class="max-w-md rounded hidden" />
        <video id="preview-video" controls class="max-w-md rounded hidden"></video>
      </div>

      <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Post</button>
    </form>
  </div>

  <script>
    function previewMedia(event) {
      const file = event.target.files[0];
      const previewBox = document.getElementById('media-preview');
      const img = document.getElementById('preview-image');
      const video = document.getElementById('preview-video');

      img.classList.add('hidden');
      video.classList.add('hidden');
      previewBox.classList.add('hidden');

      if (!file) return;

      const url = URL.createObjectURL(file);
      const type = file.type;

      if (type.startsWith('image')) {
        img.src = url;
        img.classList.remove('hidden');
      } else if (type.startsWith('video')) {
        video.src = url;
        video.classList.remove('hidden');
      }

      previewBox.classList.remove('hidden');
    }
  </script>
</body>
</html>
