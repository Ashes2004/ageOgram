 <?php
 include_once '../includes/db.php';
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: /AgeOgram/auth/login.php');
    exit();
}

$current_user_id = $_SESSION['user_id'];

$user_id = $_GET['id'];

// Corrected line here
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}


$stmt = $conn->prepare("SELECT * From posts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $posts = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $posts = [];
}

 // Debugging line to check user data

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgeOgram | Profile</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="" />
    
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class=" min-h-screen" style='font-family: "Plus Jakarta Sans", "Noto Sans", sans-serif;'>
    <!-- Include header.php here -->
    <?php include_once '../includes/header.php'; ?>
    
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-16">
        <!-- Profile Header -->
        <div class="text-center mb-8">
            <!-- Profile Photo -->
            <div class="relative inline-block mb-6">
                <img 
                    src="../<?php echo htmlspecialchars($user['profile_photo']); ?>" 
                    alt="Profile Photo" 
                    class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg mx-auto"
                >
                <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white"></div>
            </div>
            
            <!-- User Info -->
                <h1 class="text-3xl font-bold text-gray-900 mb-1"><?php echo htmlspecialchars($user['name']); ?></h1>
                <p class="text-gray-600 text-md mb-1 opacity-70"><?php $age = date_diff(date_create($user['dob']), date_create('today'))->y; echo $age; ?> years old</p>
                <p class="text-gray-700 max-w-2xl mx-auto leading-relaxed mb-6">
                <?php echo htmlspecialchars($user['bio']); ?>
                </p>
            
            <!-- Action Buttons -->
            <div class="flex justify-center gap-4 mb-8">
                <?php if ($user_id == $current_user_id): ?>
                    <a href="/AgeOgram/user/updateProfile.php" 
                       class="px-8 py-3 bg-gray-300 text-gray-800 opacity-70 rounded-lg font-bold hover:bg-gray-500 transition-colors duration-200">
                        Edit Profile
                    </a>
                    <a href="/AgeOgram/auth/logout.php" 
                       class="px-10 py-3 bg-gray-300 text-gray-800 opacity-70 rounded-lg font-bold hover:bg-gray-500 transition-colors duration-200">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="/AgeOgram/user/follow.php?user_id=<?php echo $user_id; ?>" 
                       class="px-8 py-3 bg-gray-900 text-white rounded-lg font-medium hover:bg-gray-800 transition-colors duration-200">
                        Follow
                    </a>
                    <a href="/AgeOgram/user/message.php?user_id=<?php echo $user_id; ?>" 
                       class="px-8 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                        Message
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
    <!-- Navigation Tabs -->
        <div class="flex justify-center mb-8">
            <div class="flex space-x-8">
                <button data-tab="posts" class="tab-button py-3 px-1 border-b-2 border-gray-900 font-medium text-gray-900">
                    Posts
                </button>
                <button data-tab="followers" class="tab-button py-3 px-1 font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    Followers
                </button>
                <button data-tab="following" class="tab-button py-3 px-1 font-medium text-gray-500 hover:text-gray-700 transition-colors">
                    Following
                </button>
            </div>
        </div>

        
        <!-- Tab Content -->
        <div class="max-w-4xl mx-auto">
            <!-- Posts Tab -->
            <div id="tab-posts" class="tab-content">
                <?php require_once 'user_post.php'; ?>
            </div>

            <!-- Followers Tab -->
            <div id="tab-followers" class="tab-content hidden">
                <?php require_once 'followers.php'; ?>
            </div>

            <!-- Following Tab -->
            <div id="tab-following" class="tab-content hidden">
                <?php require_once 'following.php'; ?>
            </div>
        </div>

    </div>
    
    <!-- Footer Spacer -->
    <div class="h-16"></div>
    <script>
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");

    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            const target = button.getAttribute("data-tab");

            // Toggle active tab content
            tabContents.forEach(content => {
                content.classList.add("hidden");
            });
            document.getElementById(`tab-${target}`).classList.remove("hidden");

            // Update active button styles
            tabButtons.forEach(btn => {
                btn.classList.remove("border-b-2", "border-gray-900", "text-gray-900");
                btn.classList.add("text-gray-500");
            });
            button.classList.add("border-b-2", "border-gray-900", "text-gray-900");
            button.classList.remove("text-gray-500");
        });
    });
</script>

</body>
</html>