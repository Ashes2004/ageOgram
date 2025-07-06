<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location:/AgeOgram/auth/login.php");
    exit();
}

require_once '../functions/suggestPost.php';
require_once '../includes/db.php';

$posts = suggestPostForDashboard($conn);
$message = "";
if (!$posts) {
    $message = "No posts found.";
}
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

        <div class="grid grid-cols-2 gap-4 gap-x-20 w-screen mt-6">
            <!-- Posts Section -->
            <div class="post w-full  ml-24  p-4 h-screen">
                <h2 class="text-3xl font-bold mb-4 text-gray-800">Recent Posts</h2>

                <?php if ($message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow mb-6 text-center">
                    <strong class="font-semibold">Notice:</strong>
                    <?= htmlspecialchars($message) ?>
                </div>
                <?php else: ?>
               
                  <!-- Post Modal -->
                  <div id="postModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden">
                      <div class="flex items-center justify-center min-h-screen p-4">
                          <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-2xl">
                              <div class="flex">
                                  <!-- Post Content -->
                                  <div class="flex-1">
                                      <div id="modalMedia" class="w-full h-96 bg-gray-100 flex items-center justify-center">
                                          <!-- Media will be loaded here -->
                                      </div>
                                  </div>
                                  <!-- Comments Section -->
                                  <div class="w-80 border-l border-gray-200 flex flex-col">
                                      <div class="p-4 border-b border-gray-200">
                                          <div class="flex items-center justify-between">
                                              <h3 class="font-semibold text-lg">Comments</h3>
                                              <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                                                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                  </svg>
                                              </button>
                                          </div>
                                      </div>
                                      <div id="modalComments" class="flex-1 overflow-y-auto p-4 space-y-4">
                                          <!-- Comments will be loaded here -->
                                      </div>
                                      <div class="p-4 border-t border-gray-200">
                                          <div class="flex items-center gap-2">
                                              <input type="text" placeholder="Add a comment..." class="flex-1 px-4 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                                              <button class="px-4 py-2 bg-blue-500 text-white rounded-full hover:bg-blue-600 transition-colors">Post</button>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>

                  <?php foreach ($posts as $post): ?>
                  <div class="bg-white   mb-6 mx-auto   transition-shadow">
                      <!-- Post Header -->
                      <div class="flex items-center justify-between p-4">
                          <div class="flex items-center gap-3">
                              <div class="relative">
                                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 via-gray-500 to-blue-500 p-0.5">
                                      <img src="../<?= htmlspecialchars($post['profile_photo']) ?>" class="w-full h-full rounded-full border-2 border-white object-cover" alt="User" />
                                  </div>
                              </div>
                              <div>
                                  <div class="flex items-center gap-1">
                                      <h3 class="font-semibold text-sm text-gray-900"><?= htmlspecialchars($post['name']) ?></h3>
                                      <span class="text-gray-500 text-sm">•</span>
                                      <span class="text-gray-500 text-sm"><?php $age = date_diff(date_create($post['dob']), date_create('today'))->y; echo $age; ?></span>
                                  </div>
                                  <p class="text-xs text-gray-500"><?= date('M j, Y', strtotime($post['created_at'])) ?></p>
                              </div>
                          </div>
                          <button class="text-gray-500 hover:text-gray-700">
                              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                  <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                              </svg>
                          </button>
                      </div>

                      <!-- Post Media -->
                      <div class="post-media cursor-pointer" onclick="openModal(<?= $post['id'] ?>)">
                          <?php if ($post['media_type'] === 'video'): ?>
                              <div class="relative group w-full ">
                      <!-- Video Element -->
                      <video 
                        class="w-full h-auto max-h-96 object-cover bg-gray-100 rounded-md" 
                        id="premiumVideo"
                        loop
                        muted
                        playsinline
                      >
                        <source src="../<?= htmlspecialchars($post['media_url']) ?>" type="video/mp4">
                      </video>
                      
                      <!-- Tap to play overlay -->
                      <div class="absolute inset-0 bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                        <div id="playIcon" class="text-white opacity-0 transition-opacity duration-200">
                          <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                          </svg>
                        </div>
                      </div>
                      
                      <!-- Mute/Unmute Button -->
                      <div class="absolute bottom-3 right-3 bg-black/60 text-white p-2 rounded-full backdrop-blur-sm cursor-pointer hover:bg-black/80 transition-colors" id="muteBtn">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" id="muteIcon">
                          <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>
                        </svg>
                      </div>
                      
                      <!-- Progress indicator -->
                      <div class="absolute bottom-0 left-0 right-0 h-1 bg-white/20">
                        <div id="progressBar" class="bg-white h-full transition-all duration-100" style="width: 0%"></div>
                      </div>
                    </div>

                    <script>
                    const video = document.getElementById('premiumVideo');
                    const playIcon = document.getElementById('playIcon');
                    const progressBar = document.getElementById('progressBar');
                    const muteBtn = document.getElementById('muteBtn');
                    const muteIcon = document.getElementById('muteIcon');
                    const container = video.parentElement;

                    let isPlaying = false;

                    // Tap to play/pause
                    container.addEventListener('click', (e) => {
                      e.preventDefault();
                      // Don't toggle play/pause if clicking on mute button
                      if (e.target.closest('#muteBtn')) return;
                      
                      if (isPlaying) {
                        video.pause();
                        playIcon.style.opacity = '1';
                        setTimeout(() => playIcon.style.opacity = '0', 1000);
                      } else {
                        video.play();
                        playIcon.style.opacity = '0';
                      }
                      isPlaying = !isPlaying;
                    });

                    // Mute/Unmute functionality
                    muteBtn.addEventListener('click', (e) => {
                      e.stopPropagation();
                      video.muted = !video.muted;
                      updateMuteIcon();
                    });

                    function updateMuteIcon() {
                      if (video.muted) {
                        muteIcon.innerHTML = '<path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>';
                      } else {
                        muteIcon.innerHTML = '<path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02z"/>';
                      }
                    }

                    // Update progress bar
                    video.addEventListener('timeupdate', () => {
                      const progress = (video.currentTime / video.duration) * 100;
                      progressBar.style.width = progress + '%';
                    });

                    // Reset progress when video ends
                    video.addEventListener('ended', () => {
                      progressBar.style.width = '0%';
                      isPlaying = false;
                    });

                    // Pause video when scrolling
                    let scrollTimeout;
                    window.addEventListener('scroll', () => {
                      if (isPlaying) {
                        video.pause();
                        playIcon.style.opacity = '1';
                        setTimeout(() => playIcon.style.opacity = '0', 1000);
                        isPlaying = false;
                      }
                    });

                    // Auto-play when video comes into view
                    const observer = new IntersectionObserver((entries) => {
                      entries.forEach(entry => {
                        if (entry.isIntersecting && entry.intersectionRatio > 0.5) {
                          video.play();
                          isPlaying = true;
                          playIcon.style.opacity = '0';
                        } else if (!entry.isIntersecting && isPlaying) {
                          video.pause();
                          isPlaying = false;
                        }
                      });
                    }, { threshold: 0.5 });

                    observer.observe(video);

                    // Hover to play/pause
                    container.addEventListener('mouseenter', () => {
                      if (!isPlaying) {
                        video.play();
                        isPlaying = true;
                        playIcon.style.opacity = '0';
                      }
                    });

                    container.addEventListener('mouseleave', () => {
                      if (isPlaying) {
                        video.pause();
                        isPlaying = false;
                      }
                    });

                    // Initialize mute icon
                    updateMuteIcon();
                    </script>
                          <?php elseif ($post['media_type'] === 'image'): ?>
                              <img src="../<?= htmlspecialchars($post['media_url']) ?>" class="w-full h-auto max-h-96 object-cover bg-gray-100 rounded-md" alt="Post image" />
                          <?php endif; ?>
                      </div>

                      <!-- Post Actions -->
                      <div class="p-4">
                          <div class="flex items-center justify-between mb-3">
                              <div class="flex items-center gap-4">
                                  <button class="like-btn hover:scale-110 transition-transform" onclick="toggleLike(<?= $post['id'] ?>)">
                                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                      </svg>
                                  </button>
                                  <button class="hover:scale-110 transition-transform" onclick="openModal(<?= $post['id'] ?>)">
                                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                      </svg>
                                  </button>
                                  <button class="hover:scale-110 transition-transform">
                                      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                      </svg>
                                  </button>
                              </div>
                              <button class="hover:scale-110 transition-transform">
                                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"></path>
                                  </svg>
                              </button>
                          </div>

                          <!-- Likes Count -->
                          <div class="mb-2">
                              <span class="font-semibold text-sm text-gray-900" id="like-count-<?= $post['id'] ?>"><?= $post['like_count'] ?> likes</span>
                          </div>

                          <!-- Post Caption -->
                          <div class="mb-2">
                              <span class="font-semibold text-sm text-gray-900"><?= htmlspecialchars($post['name']) ?></span>
                              <span class="text-sm text-gray-900 ml-2"><?= nl2br(htmlspecialchars($post['content'])) ?></span>
                          </div>

                          <!-- Comments Preview -->
                          <div class="cursor-pointer" onclick="openModal(<?= $post['id'] ?>)">
                              <?php if (count($post['comments']) > 0): ?>
                                  <p class="text-sm text-gray-500 mb-2">View all <?= count($post['comments']) ?> comments</p>
                                  <div class="space-y-1">
                                      <?php foreach (array_slice($post['comments'], 0, 2) as $comment): ?>
                                          <div class="flex items-start gap-2">
                                              <span class="font-semibold text-sm text-gray-900"><?= htmlspecialchars($comment['name'] ?? 'User') ?></span>
                                              <span class="text-sm text-gray-900"><?= htmlspecialchars($comment['content'] ?? $comment['comment']) ?></span>
                                          </div>
                                      <?php endforeach; ?>
                                  </div>
                              <?php else: ?>
                                  <p class="text-sm text-gray-500">No comments yet</p>
                              <?php endif; ?>
                          </div>

                          <!-- Time -->
                          <p class="text-xs text-gray-400 mt-2"><?= date('M j, Y', strtotime($post['created_at'])) ?></p>
                      </div>
                  </div>
                  <?php endforeach; ?>

                  <script>
                      const postsData = <?= json_encode($posts) ?>;
                      function toggleLike(postId) {
                          const likeBtn = document.querySelector(`[onclick="toggleLike(${postId})"]`);
                          const likeCount = document.getElementById(`like-count-${postId}`);
                          
                          likeBtn.classList.toggle('liked');
                          
                          // Add like animation
                          likeBtn.style.animation = 'likeAnimation 0.5s ease';
                          setTimeout(() => {
                              likeBtn.style.animation = '';
                          }, 500);
                          
                          // Update like count (this would normally be an AJAX call)
                          const currentCount = parseInt(likeCount.textContent);
                          if (likeBtn.classList.contains('liked')) {
                              likeCount.textContent = `${currentCount + 1} likes`;
                              likeBtn.querySelector('svg').setAttribute('fill', 'red');
                          } else {
                              likeCount.textContent = `${currentCount - 1} likes`;
                              likeBtn.querySelector('svg').setAttribute('fill', 'none');
                          }
                      }

                      function openModal(postId) {
                          const modal = document.getElementById('postModal');
                          const modalMedia = document.getElementById('modalMedia');
                          const modalComments = document.getElementById('modalComments');
                          
                          // Find the clicked post data
                          const post = postsData.find(p => p.id == postId);
                          
                          if (!post) {
                              console.error('Post not found');
                              return;
                          }
                          
                          // Load post media into modal
                          if (post.media_type === 'video') {
                              modalMedia.innerHTML = `
                                  <video class="w-full h-96 object-cover bg-gray-100" controls>
                                      <source src="../${post.media_url}" type="video/mp4">
                                  </video>
                              `;
                          } else if (post.media_type === 'image') {
                              modalMedia.innerHTML = `
                                  <img src="../${post.media_url}" class="w-full h-96 object-cover bg-gray-100" alt="Post image" />
                              `;
                          }
                          
                          // Load comments into modal
                          let commentsHTML = '';
                          if (post.comments && post.comments.length > 0) {
                              post.comments.forEach(comment => {
                                  const commentContent = comment.content || comment.comment || '';
                                  const commentAuthor = comment.name || 'User';
                                  commentsHTML += `
                                      <div class="flex items-start gap-3 p-2">
                                          <div class="w-8 h-8 rounded-full bg-gray-300 flex-shrink-0"></div>
                                          <div class="flex-1">
                                              <div class="flex items-center gap-2">
                                                  <span class="font-semibold text-sm text-gray-900">${commentAuthor}</span>
                                                  <span class="text-xs text-gray-500">now</span>
                                              </div>
                                              <p class="text-sm text-gray-900 mt-1">${commentContent}</p>
                                          </div>
                                      </div>
                                  `;
                              });
                          } else {
                              commentsHTML = '<div class="text-center text-gray-500 py-8">No comments yet</div>';
                          }
                          
                          modalComments.innerHTML = commentsHTML;
                          
                          // Show modal
                          modal.classList.remove('hidden');
                          
                          // Store current post ID for comment submission
                          modal.dataset.postId = postId;
                      }

                      function closeModal() {
                          document.getElementById('postModal').classList.add('hidden');
                      }

                      // Close modal when clicking outside
                      document.getElementById('postModal').addEventListener('click', function(e) {
                          if (e.target === this) {
                              closeModal();
                          }
                      });
                  </script>

                  <style>
                      .like-btn.liked svg {
                          fill: #e53e3e;
                          stroke: #e53e3e;
                      }
                      
                      @keyframes likeAnimation {
                          0% { transform: scale(1); }
                          50% { transform: scale(1.3); }
                          100% { transform: scale(1); }
                      }
                  </style>

                <?php endif; ?>
            </div>

            <!-- Other Section -->
       <div class="other flex h-[80vh]  rounded-lg overflow-hidden ml-20">



  <!-- Main Content -->
  <div class="w-2/3 flex flex-col overflow-y-auto bg-white w-full rounded-lg p-4">
    <h2 class="text-[#111418] text-3xl font-bold px-4 pb-3 ">Trending Tags</h2>
    <div class="flex gap-3 p-3 flex-wrap pr-4">
      <?php
         require_once '../functions/trendingTags.php';
         $user_id_for_tags = $_SESSION['user_id'];
     
    
        $tags = [];
          
        $tags = getTrendingTagsByAgeCategory($conn , $user_id_for_tags );
        foreach ($tags as $tag):
      ?>
        <div class="flex h-8 items-center justify-center rounded-lg bg-[#f0f2f5] px-4 cursor-pointer" onclick = "window.location.href = '/AgeOgram/explore/tags.php?tag=<?php echo str_replace('#' , '' , $tag) ;?>'">
          <p class="text-[#111418] text-md font-bold">#<?= $tag ?></p>
        </div>
      <?php endforeach; ?>
    </div>

    <h2 class="text-[#111418] text-[22px] font-bold px-4 pb-3 pt-5">Suggested Users</h2>

    <?php
 $users = [
    ["name" => "Liam, 30", "mutual" => "32", "img" => "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&h=150&fit=crop&crop=face"],
    ["name" => "Chloe, 35", "mutual" => "15", "img" => "https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=150&h=150&fit=crop&crop=face"],
    ["name" => "Noah, 40", "mutual" => "28", "img" => "https://images.unsplash.com/photo-1560250097-0b93528c311a?w=150&h=150&fit=crop&crop=face"]
];
      foreach ($users as $user):
    ?>
      <div class="flex items-center gap-4 px-4 min-h-[72px] py-2">
        <div class="bg-cover bg-center aspect-square rounded-full h-14 w-14" style="background-image: url('<?= $user['img'] ?>');"></div>
        <div class="flex flex-col justify-center">
          <p class="text-[#111418] text-base font-medium"><?= $user["name"] ?></p>
          <p class="text-[#60758a] text-sm font-normal">
            <?= $user["mutual"] ?> mutual friends
          </p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
    <div class="w-1/3 h-full px-8">
    <?php require_once '../includes/sidebar.php'; ?>
  </div>
</div>

        <?php
  if (isset($_POST['logout'])) {
      require_once '../auth/logout.php';
      logout();
  }
  ?>
    </body>
</html>
