<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location:/AgeOgram/auth/login.php");
    exit();
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $dob = htmlspecialchars($_POST['dob'], ENT_QUOTES, 'UTF-8');
    $bio = htmlspecialchars($_POST['bio'], ENT_QUOTES, 'UTF-8');
    $profile_photo = '';
    $update_photo = false;

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $file = $_FILES['profile_photo'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        // Validate file type
        if (!in_array($ext, $allowed_types)) {
            $error_message = "Invalid file type. Please upload JPG, PNG, GIF, or WebP images only.";
        } else {
            $type = mime_content_type($file['tmp_name']);
            if (str_starts_with($type, 'image')) {
                $target_dir = '../storage/profile_photos/';
                
                // Create directory if it doesn't exist
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }
                
                $filename = uniqid() . "." . $ext;
                $full_path = $target_dir . $filename;

                if (move_uploaded_file($file['tmp_name'], $full_path)) {
                    $profile_photo = $full_path;
                    $update_photo = true;
                } else {
                    $error_message = "Failed to upload image. Please try again.";
                }
            } else {
                $error_message = "Invalid file type. Please upload an image.";
            }
        }
    }

    // Update user profile in the database
    if (empty($error_message)) {
        if ($update_photo) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, dob = ?, bio = ?, profile_photo = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $name, $dob, $bio, $profile_photo, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET name = ?, dob = ?, bio = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $dob, $bio, $user_id);
        }

        if ($stmt->execute()) {
            $success_message = "Profile updated successfully!";
        } else {
            $error_message = "Error updating profile: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch user data for pre-filling the form
$stmt = $conn->prepare("SELECT name, dob, bio, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);   
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}

$name = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
$dob = htmlspecialchars($user['dob'], ENT_QUOTES, 'UTF-8');
$bio = htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8');
$profile_photo = htmlspecialchars($user['profile_photo'], ENT_QUOTES, 'UTF-8');

// Include header

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile | AgeOgram</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body>
    
<div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
     <?php include '../includes/header.php'; ?>
     <div  class = " mt-24 flex">
    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="flex-1 ml-64 p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Page Header -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg p-6 mb-8 text-white">
                <h1 class="text-3xl font-bold mb-2">Update Profile</h1>
                <p class="text-blue-100">Keep your information up to date</p>
            </div>

            <!-- Success/Error Messages -->
            <?php if (!empty($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <!-- Profile Update Form -->
            <div class="bg-white rounded-lg shadow-lg p-8">
                <form action="" method="POST" enctype="multipart/form-data" id="profileForm">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Profile Photo Section -->
                        <div class="lg:col-span-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Profile Photo</h3>
                            <div class="text-center">
                                <div class="mb-6">
                                    <img id="photoPreview" 
                                         src="<?php echo !empty($profile_photo) ?  $profile_photo : '/AgeOgram/images/default-avatar.png'; ?>" 
                                         alt="Profile Photo" 
                                         class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-gray-200 shadow-lg hover:shadow-xl transition-shadow duration-300">
                                </div>
                                <div class="upload-area border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 hover:bg-blue-50 transition-colors duration-300 cursor-pointer" 
                                     onclick="document.getElementById('profile_photo').click();">
                                    <i class="fas fa-camera text-3xl text-gray-400 mb-3"></i>
                                    <p class="text-gray-600 mb-2">Click to upload new photo</p>
                                    <p class="text-sm text-gray-500">JPG, PNG, GIF, WebP (Max 5MB)</p>
                                </div>
                                <input type="file" 
                                       class="hidden" 
                                       id="profile_photo" 
                                       name="profile_photo" 
                                       accept="image/*"
                                       onchange="previewImage(this)">
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="lg:col-span-2">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Personal Information</h3>
                            
                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-user mr-2"></i>Full Name
                                    </label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="<?php echo $name; ?>" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-300"
                                           placeholder="Enter your full name">
                                </div>

                                <div>
                                    <label for="dob" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-birthday-cake mr-2"></i>Date of Birth
                                    </label>
                                    <input type="date" 
                                           id="dob" 
                                           name="dob" 
                                           value="<?php echo $dob; ?>" 
                                           required
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-300">
                                </div>

                                <div>
                                    <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-info-circle mr-2"></i>Bio
                                    </label>
                                    <textarea id="bio" 
                                              name="bio" 
                                              rows="4" 
                                              required
                                              maxlength="500"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-300 resize-none"
                                              placeholder="Tell us about yourself..."><?php echo $bio; ?></textarea>
                                    <div class="flex justify-between items-center mt-2">
                                        <p class="text-sm text-gray-500">Maximum 500 characters</p>
                                        <span id="charCount" class="text-sm text-gray-500"><?php echo strlen($bio); ?>/500</span>
                                    </div>
                                </div>

                                <div class="pt-4">
                                    <button type="submit" 
                                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 px-6 rounded-lg font-medium hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-300">
                                        <i class="fas fa-save mr-2"></i>Update Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
    </div>
 </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('photoPreview');
    const file = input.files[0];
    
    if (file) {
        // Validate file size (5MB limit)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            input.value = '';
            return;
        }
        
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, PNG, GIF, WebP)');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
}

// Drag and drop functionality
const uploadArea = document.querySelector('.upload-area');
const fileInput = document.getElementById('profile_photo');

uploadArea.addEventListener('dragover', function(e) {
    e.preventDefault();
    uploadArea.classList.add('border-blue-400', 'bg-blue-50');
});

uploadArea.addEventListener('dragleave', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
});

uploadArea.addEventListener('drop', function(e) {
    e.preventDefault();
    uploadArea.classList.remove('border-blue-400', 'bg-blue-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        previewImage(fileInput);
    }
});

// Character counter for bio
const bioTextarea = document.getElementById('bio');
const charCount = document.getElementById('charCount');

bioTextarea.addEventListener('input', function() {
    const currentLength = this.value.length;
    charCount.textContent = currentLength + '/500';
    
    if (currentLength > 450) {
        charCount.classList.add('text-red-500');
        charCount.classList.remove('text-gray-500');
    } else {
        charCount.classList.remove('text-red-500');
        charCount.classList.add('text-gray-500');
    }
});
</script>


</body>
</html>