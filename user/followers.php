<?php

   $stmt = $conn->prepare("SELECT u.name, u.bio, u.profile_photo FROM users u JOIN follows f ON u.id = f.follower_id where f.followed_id = ? "); 
    $stmt->bind_param("i", $user_id); 
    $stmt->execute();

    $result = $stmt->get_result();
    $followers = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();



 
 
?>

<div class="w-full  bg-white  p-4">


  <?php if (!empty($followers)) { ?>
    <div class="space-y-4">
      <?php foreach ($followers as $f) { ?>
        <div class="flex items-center gap-4 p-3 border border-gray-300 rounded-xl">
          <img src="../<?= htmlspecialchars($f['profile_photo']) ?>" 
               alt="Profile" 
               class="w-12 h-12 rounded-full object-cover border">
          
          <div class="flex flex-col">
            <span class="font-semibold text-gray-800">
              <?= htmlspecialchars($f['name']) ?>
            </span>
            <span class="text-sm text-gray-500">
              <?= htmlspecialchars($f['bio']) ?>
            </span>
          </div>

          <!-- Optional follow button -->
          <div class="ml-auto">
            <button class="px-3 py-1 text-sm bg-blue-500 text-white rounded-full hover:bg-blue-600 transition">
              Follow
            </button>
          </div>
        </div>
      <?php } ?>
    </div>
  <?php } else { ?>
    <p class="text-gray-500 text-center">No following found.</p>
  <?php } ?>
</div>

