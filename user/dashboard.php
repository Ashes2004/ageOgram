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
                <!-- Get all recent posts  -->
                <?php include_once 'recentPostForDashboard.php'?>
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
