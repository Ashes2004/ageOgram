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

    // Debug: Check if file was uploaded
    if (isset($_FILES['media'])) {
        error_log("File upload attempt - Error code: " . $_FILES['media']['error']);
        error_log("File details: " . print_r($_FILES['media'], true));
    }

    // Handle media upload
    if (!empty($_FILES['media']['name']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['media'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Get MIME type safely
        $type = '';
        if (function_exists('mime_content_type') && file_exists($file['tmp_name'])) {
            $type = mime_content_type($file['tmp_name']);
        }

        // Debug: Log file info
        error_log("File upload - Name: " . $file['name'] . ", Type: " . $type . ", Extension: " . $ext . ", Size: " . $file['size']);

        // Check file type and extension
        $allowed_images = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $allowed_videos = ['mp4', 'mov', 'avi', 'mkv', 'webm', 'flv'];

        if (in_array($ext, $allowed_images) || str_starts_with($type, 'image')) {
            $media_type = 'image';
            $target_dir = dirname(__FILE__) . '/../storage/image/';
        } elseif (in_array($ext, $allowed_videos) || str_starts_with($type, 'video') || $ext === 'mp4') {
            $media_type = 'video';
            $target_dir = dirname(__FILE__) . '/../storage/video/';
        }

        error_log("Detected media type: " . $media_type);

        if ($media_type !== 'none') {
            // Create directory if it doesn't exist
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    error_log("Failed to create directory: " . $target_dir);
                    $message = "❌ Error: Cannot create upload directory.";
                } else {
                    error_log("Created directory: " . $target_dir);
                }
            }

            if (is_dir($target_dir)) {
                $filename = uniqid() . "_" . time() . "." . $ext;
                $full_path = $target_dir . $filename;
                
                // Debug: Log paths
                error_log("Target directory: " . $target_dir);
                error_log("Full path: " . $full_path);
                error_log("Directory exists: " . (is_dir($target_dir) ? 'Yes' : 'No'));
                error_log("Directory writable: " . (is_writable($target_dir) ? 'Yes' : 'No'));
                error_log("Temp file exists: " . (file_exists($file['tmp_name']) ? 'Yes' : 'No'));

                if (move_uploaded_file($file['tmp_name'], $full_path)) {
                    $media_url = str_replace(dirname(__FILE__) . '/../', '', $full_path);
                    error_log("File uploaded successfully. Media URL: " . $media_url);
                } else {
                    $last_error = error_get_last();
                    error_log("Failed to move uploaded file. Last error: " . ($last_error ? $last_error['message'] : 'Unknown error'));
                    error_log("Source: " . $file['tmp_name'] . " -> Destination: " . $full_path);
                    $message = "❌ Error: Failed to upload file. Check permissions.";
                }
            }
        } else {
            error_log("Unsupported file type. Extension: " . $ext . ", MIME: " . $type);
            $message = "❌ Error: Unsupported file type. Please upload images (JPG, PNG, GIF) or videos (MP4, MOV, AVI).";
        }
    } elseif (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File too large (exceeds upload_max_filesize)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds MAX_FILE_SIZE)',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        $error_code = $_FILES['media']['error'];
        $error_message = $upload_errors[$error_code] ?? 'Unknown upload error';
        error_log("Upload error: " . $error_message . " (Code: " . $error_code . ")");
        $message = "❌ Upload Error: " . $error_message;
    }

    // Insert post - only if no upload errors occurred
    if (empty($message)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, media_url, media_type, tags) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $user_id, $content, $media_url, $media_type, $tags_cleaned);

        if ($stmt->execute()) {
            // Debug: Log successful insertion
            error_log("Post inserted successfully. ID: " . $stmt->insert_id . ", Media URL: " . $media_url . ", Media Type: " . $media_type);
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } else {
            $message = "❌ Database Error: " . $stmt->error;
            error_log("Database error: " . $stmt->error);
        }

        $stmt->close();
    }
}

// Success message
if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = "✅ Post uploaded successfully!";

    header("Location: /AgeOgram/user/dashboard.php");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Create Post | AgeOgram</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
   
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="relative flex size-full min-h-screen flex-col bg-white group/design-root overflow-x-hidden" style='font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;'>
        <div class="layout-container flex h-full grow flex-col">
            <?php require_once '../includes/header.php'; ?>
            
            <div class="px-40 flex flex-1 justify-center py-5 mt-20">
                <div class="layout-content-container flex flex-col w-[512px] max-w-[512px] py-5 max-w-[960px] flex-1">
                    <h1 class="text-[#111418] text-[22px] font-bold leading-tight tracking-[-0.015em] px-4 text-left pb-3 pt-5">Create new post</h1>
                    
                    <?php if ($message): ?>
                        <div class="mb-4 p-3 rounded text-sm mx-4 <?= strpos($message, '✅') !== false ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <!-- Content Textarea -->
                        <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                            <label class="flex flex-col min-w-40 flex-1">
                                <textarea
                                    name="content"
                                    placeholder="Write a caption..."
                                    class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-0 border border-[#dbe0e6] bg-white focus:border-[#dbe0e6] min-h-36 placeholder:text-[#60758a] p-[15px] text-base font-normal leading-normal"
                                    required
                                ></textarea>
                            </label>
                        </div>

                        <!-- Tags Input -->
                        <div class="flex max-w-[480px] flex-wrap items-end gap-4 px-4 py-3">
                            <label class="flex flex-col min-w-40 flex-1">
                                <input
                                    name="tags"
                                    placeholder="Add tags (e.g. #fun #travel)"
                                    class="form-input flex w-full min-w-0 flex-1 resize-none overflow-hidden rounded-lg text-[#111418] focus:outline-0 focus:ring-0 border border-[#dbe0e6] bg-white focus:border-[#dbe0e6] h-14 placeholder:text-[#60758a] p-[15px] text-base font-normal leading-normal"
                                />
                            </label>
                        </div>

                        <!-- Media Upload -->
                        <div class="flex flex-col p-4">
                            <div class="flex flex-col items-center gap-6 rounded-lg border-2 border-dashed border-[#dbe0e6] px-6 py-14" id="upload-area">
                                <div class="flex max-w-[480px] flex-col items-center gap-2">
                                    <p class="text-[#111418] text-lg font-bold leading-tight tracking-[-0.015em] max-w-[480px] text-center">Drag photos and videos here</p>
                                    <p class="text-[#111418] text-sm font-normal leading-normal max-w-[480px] text-center">Or</p>
                                </div>
                                <button
                                    type="button"
                                    onclick="document.getElementById('media-input').click()"
                                    class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#f0f2f5] text-[#111418] text-sm font-bold leading-normal tracking-[0.015em]"
                                >
                                    <span class="truncate">Select from computer</span>
                                </button>
                                <input
                                    type="file"
                                    id="media-input"
                                    name="media"
                                    accept="image/*,video/*"
                                    class="hidden"
                                    onchange="previewMedia(event)"
                                />
                            </div>
                        </div>

                        <!-- Media Preview -->
                        <div id="media-preview" class="flex flex-col w-full grow bg-white @container p-4 hidden">
                            <div class="flex justify-between items-center mb-2">
                                <p class="text-[#111418] text-sm font-medium">Media Preview</p>
                                <button
                                    type="button"
                                    onclick="cancelMedia()"
                                    class="flex items-center justify-center w-8 h-8 rounded-full bg-red-100 hover:bg-red-200 transition-colors"
                                    title="Remove media"
                                >
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="w-full gap-1 overflow-hidden bg-white @[480px]:gap-2 aspect-[3/2] rounded-lg flex">
                                <div class="w-full bg-center bg-no-repeat bg-cover aspect-auto rounded-none flex-1" id="preview-container">
                                    <img id="preview-image" class="w-full h-full object-cover rounded-lg hidden" />
                                    <video id="preview-video" controls class="w-full h-full object-cover rounded-lg hidden"></video>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex px-4 py-3 justify-end">
                            <button
                                type="submit"
                                class="flex min-w-[84px] max-w-[480px] cursor-pointer items-center justify-center overflow-hidden rounded-lg h-10 px-4 bg-[#0c7ff2] text-white text-sm font-bold leading-normal tracking-[0.015em]"
                            >
                                <span class="truncate">Post</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewMedia(event) {
            const file = event.target.files[0];
            const img = document.getElementById('preview-image');
            const video = document.getElementById('preview-video');
            const previewBox = document.getElementById('media-preview');
            const uploadArea = document.getElementById('upload-area');

            if (!file) {
                previewBox.classList.add('hidden');
                uploadArea.classList.remove('hidden');
                return;
            }

            // Hide upload area and show preview
            uploadArea.classList.add('hidden');
            previewBox.classList.remove('hidden');

            // Hide both preview elements first
            img.classList.add('hidden');
            video.classList.add('hidden');

            const url = URL.createObjectURL(file);
            if (file.type.startsWith('image')) {
                img.src = url;
                img.classList.remove('hidden');
            } else if (file.type.startsWith('video')) {
                video.src = url;
                video.classList.remove('hidden');
            }
        }

        function cancelMedia() {
            const img = document.getElementById('preview-image');
            const video = document.getElementById('preview-video');
            const previewBox = document.getElementById('media-preview');
            const uploadArea = document.getElementById('upload-area');
            const mediaInput = document.getElementById('media-input');

            // Clear the file input
            mediaInput.value = '';

            // Revoke the object URL to free memory
            if (img.src) {
                URL.revokeObjectURL(img.src);
                img.src = '';
            }
            if (video.src) {
                URL.revokeObjectURL(video.src);
                video.src = '';
            }

            // Hide preview and show upload area
            previewBox.classList.add('hidden');
            uploadArea.classList.remove('hidden');
        }

        // Drag and drop functionality
        const uploadArea = document.getElementById('upload-area');
        const mediaInput = document.getElementById('media-input');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('border-[#0c7ff2]', 'bg-blue-50');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('border-[#0c7ff2]', 'bg-blue-50');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                mediaInput.files = files;
                previewMedia({ target: { files: files } });
            }
        }
    </script>
</body>
</html>