<?php
// Enhanced tag-based post filtering with better security and UX
session_start();

// Security check - redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/auth/login.php");
    exit();
}

include_once '../includes/db.php';

// Sanitize and validate input
$query_tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$user_id = $_SESSION['user_id'];

// Function to determine age category
function getAgeCategory($dob) {
    $age = (new DateTime())->diff(new DateTime($dob))->y;
    
    if ($age >= 13 && $age <= 19) return 'Teen';
    elseif ($age >= 20 && $age <= 29) return 'Young Adult';
    elseif ($age >= 30 && $age <= 59) return 'Adult';
    else return 'Senior';
}

try {
    // Fetch current user's information
    $stmt = $conn->prepare("SELECT dob, name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        throw new Exception("User not found.");
    }

    $user_category = getAgeCategory($user['dob']);

    // Build query with optional tag filter
    $sql = "SELECT posts.*, users.name, users.profile_photo, users.dob 
            FROM posts
            JOIN users ON posts.user_id = users.id";
    
    $params = [];
    $types = "";
    
    if ($query_tag) {
        $sql .= " WHERE posts.tags LIKE ?";
        $params[] = "%{$query_tag}%";
        $types .= "s";
    }
    
    $sql .= " ORDER BY posts.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $posts_result = $stmt->get_result();

    $posts = [];
    $total_posts = 0;
    
    while ($post = $posts_result->fetch_assoc()) {
        $total_posts++;
        
        // Check if poster is in same age category
        $poster_category = getAgeCategory($post['dob']);
        if ($poster_category !== $user_category) {
            continue;
        }

        // Additional tag filtering (more precise)
        if ($query_tag) {
            $post_tags = array_map('trim', explode(',', $post['tags']));
            if (!in_array($query_tag, $post_tags)) {
                continue;
            }
        }

        // Fetch comments for this post
        $c_stmt = $conn->prepare("SELECT comments.*, users.name, users.profile_photo 
                                  FROM comments 
                                  JOIN users ON comments.user_id = users.id 
                                  WHERE comments.post_id = ? 
                                  ORDER BY comments.created_at ASC");
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

        // Check if current user liked this post
        $user_liked_stmt = $conn->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
        $user_liked_stmt->bind_param("ii", $post['id'], $user_id);
        $user_liked_stmt->execute();
        $user_liked = $user_liked_stmt->get_result()->num_rows > 0;
        $user_liked_stmt->close();

        $post['comments'] = $comments;
        $post['like_count'] = $like_result['like_count'];
        $post['user_liked'] = $user_liked;
        $post['poster_category'] = $poster_category;
        $posts[] = $post;
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("Error in tag posts: " . $e->getMessage());
    $error_message = "Sorry, something went wrong. Please try again.";
}

// Get popular tags for suggestions
$popular_tags = [];
try {
    $tag_stmt = $conn->prepare("SELECT tags FROM posts WHERE tags IS NOT NULL AND tags != '' LIMIT 100");
    $tag_stmt->execute();
    $tag_results = $tag_stmt->get_result();
    
    $tag_counts = [];
    while ($row = $tag_results->fetch_assoc()) {
        $tags = array_map('trim', explode(',', $row['tags']));
        foreach ($tags as $tag) {
            if (!empty($tag)) {
                $tag_counts[$tag] = ($tag_counts[$tag] ?? 0) + 1;
            }
        }
    }
    
    arsort($tag_counts);
    $popular_tags = array_slice(array_keys($tag_counts), 0, 10);
    $tag_stmt->close();
} catch (Exception $e) {
    error_log("Error fetching popular tags: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $query_tag ? "Posts tagged with #{$query_tag}" : "All Posts"; ?> | AgeOgram</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .post-overlay {
            background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 50%);
        }
        .tag-pill {
            transition: all 0.2s ease;
        }
        .tag-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include_once '../includes/header.php'; ?>
    
    <div class="pt-20 pb-8">
        <div class="max-w-6xl mx-auto px-4">
            
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <?php if ($query_tag): ?>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                                <i class="fas fa-hashtag text-blue-500"></i>
                                <?php echo htmlspecialchars($query_tag); ?>
                            </h1>
                            <p class="text-gray-600">
                                Posts from <?php echo htmlspecialchars($user_category); ?>s
                                <?php if (count($posts) > 0): ?>
                                    • <?php echo count($posts); ?> post<?php echo count($posts) !== 1 ? 's' : ''; ?>
                                <?php endif; ?>
                            </p>
                        <?php else: ?>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Explore Posts</h1>
                            <p class="text-gray-600">Discover content from your age group</p>
                        <?php endif; ?>
                    </div>
                    
                
                </div>

              
            </div>

            <!-- Error Message -->
            <?php if (isset($error_message)): ?>
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-2 text-red-700">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span><?php echo htmlspecialchars($error_message); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Posts Grid -->
            <?php if (count($posts) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($posts as $post): ?>
                        <div class="bg-white rounded-xl shadow-sm border hover:shadow-md transition-shadow duration-200 overflow-hidden">
                            <!-- Post Media -->
                            <div class="relative aspect-square bg-gray-100 overflow-hidden group">
                                <?php if ($post['media_type'] === 'image'): ?>
                                    <img 
                                        src="../<?php echo htmlspecialchars($post['media_url']); ?>" 
                                        alt="Post by <?php echo htmlspecialchars($post['name']); ?>" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        loading="lazy"
                                    >
                                <?php elseif ($post['media_type'] === 'video'): ?>
                                    <video 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        preload="metadata"
                                    >
                                        <source src="../<?php echo htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                    <div class="absolute top-3 right-3 bg-black bg-opacity-50 rounded-full p-2">
                                        <i class="fas fa-play text-white text-sm"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Post Overlay -->
                                <div class="absolute inset-0 post-overlay opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <div class="absolute bottom-4 left-4 right-4 text-white">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <button class="flex items-center gap-1 hover:text-red-400 transition-colors">
                                                    <i class="<?php echo $post['user_liked'] ? 'fas fa-heart text-red-500' : 'far fa-heart'; ?>"></i>
                                                    <span class="text-sm"><?php echo $post['like_count']; ?></span>
                                                </button>
                                                <button class="flex items-center gap-1 hover:text-blue-400 transition-colors">
                                                    <i class="far fa-comment"></i>
                                                    <span class="text-sm"><?php echo count($post['comments']); ?></span>
                                                </button>
                                            </div>
                                            <span class="text-xs bg-black bg-opacity-50 px-2 py-1 rounded">
                                                <?php echo $post['poster_category']; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Post Info -->
                            <div class="p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    <img 
                                        src="../<?php echo htmlspecialchars($post['profile_photo'] ?: 'assets/default-avatar.png'); ?>" 
                                        alt="<?php echo htmlspecialchars($post['name']); ?>"
                                        class="w-8 h-8 rounded-full object-cover"
                                    >
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($post['name']); ?> - <span class = "text-gray-400 text-sm"> <?php  $age = (new DateTime())->diff(new DateTime($post['dob']))->y; echo $age; ?></span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('M j, Y', strtotime($post['created_at'])); ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if ($post['content']): ?>
                                    <p class="text-sm text-gray-700 mb-3 line-clamp-2">
                                        <?php echo htmlspecialchars($post['content']); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Tags -->
                                <?php if ($post['tags']): ?>
                                    <div class="flex flex-wrap gap-1 mb-3">
                                        <?php 
                                        $tags = array_map('trim', explode(',', $post['tags']));
                                        foreach (array_slice($tags, 0, 3) as $tag): 
                                        ?>
                                            <a href="?tag=<?php echo urlencode($tag); ?>" 
                                               class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200 transition-colors">
                                                #<?php echo htmlspecialchars($tag); ?>
                                            </a>
                                        <?php endforeach; ?>
                                        <?php if (count($tags) > 3): ?>
                                            <span class="text-xs text-gray-500 px-2 py-1">
                                                +<?php echo count($tags) - 3; ?> more
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons -->
                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                        <?php if ($query_tag): ?>
                            <i class="fas fa-hashtag text-4xl text-gray-400"></i>
                        <?php else: ?>
                            <i class="fas fa-images text-4xl text-gray-400"></i>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-3">
                        <?php if ($query_tag): ?>
                            No posts found for #<?php echo htmlspecialchars($query_tag); ?>
                        <?php else: ?>
                            No posts yet
                        <?php endif; ?>
                    </h3>
                    <p class="text-gray-600 mb-8 max-w-sm mx-auto">
                        <?php if ($query_tag): ?>
                            Try searching for a different tag or create the first post with this tag!
                        <?php else: ?>
                            Be the first to share your moments with your age group!
                        <?php endif; ?>
                    </p>
                    <div class="flex gap-4 justify-center">
                        <?php if ($query_tag): ?>
                            <a href="?" 
                               class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                                View All Posts
                            </a>
                        <?php endif; ?>
                        <a href="../posts/create.php" 
                           class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            Create First Post
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

   
</body>
</html>