<?php
// You must have $posts, $user_id, and $current_user_id available in the parent file
?>

<div class="max-w-4xl mx-auto">
    <?php if (count($posts) > 0): ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-1">
            <?php foreach ($posts as $post): ?>
                <div class="aspect-square bg-gray-200 overflow-hidden hover:opacity-90 transition-opacity cursor-pointer">
                    <?php if ($post['media_type'] === 'image'): ?>
                        <img 
                            src="../<?= htmlspecialchars($post['media_url']); ?>" 
                            alt="Post Image" 
                            class="w-full h-full object-cover"
                        >
                    <?php elseif ($post['media_type'] === 'video'): ?>
                        <div class="relative w-full h-full">
                            <video class="w-full h-full object-cover" ?>">
                                <source src="../<?= htmlspecialchars($post['media_url']); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            <div class="absolute top-3 right-3">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-16">
            <div class="w-24 h-24 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-semibold text-gray-900 mb-3">No Posts Yet</h3>
            <p class="text-gray-600 mb-8 max-w-sm mx-auto">Start sharing your moments with the world!</p>
            <?php if ($user_id === $current_user_id): ?>
                <a href="/AgeOgram/posts/create.php" 
                   class="inline-flex items-center justify-center px-8 py-3 bg-gray-900 text-white rounded-lg font-medium hover:bg-gray-800 transition-colors duration-200">
                    Create Your First Post
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
