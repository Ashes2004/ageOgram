
<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/auth/login.php");
    exit();
}


 $user_id = $_SESSION['user_id'];
require_once '../includes/db.php';
// Fetch user data
$stmt = $conn->prepare("SELECT  profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}






?>



<header class="flex items-center justify-between bg-white whitespace-nowrap border-b border-gray-200 px-10 py-3   fixed top-0 z-50 w-screen">
  <!-- Left: Logo + Brand -->
  <div class="flex items-center gap-3 text-[#111418]">
    <div class=" text-blue-600">
      <img src="../assets/images/AgeOgram.png" alt="AgeOgram Logo" class="w-10 h-9" />
    </div>
    <h2 class="text-xl font-bold tracking-tight">AgeOgram</h2>
  </div>

  <!-- Right Section -->
  <div class="flex items-center gap-6">
    <!-- Search Bar -->
    <label class="flex items-center bg-[#f0f2f5] rounded-lg overflow-hidden h-10 max-w-xs w-full">
      <span class="pl-3 pr-2 text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 256 256">
          <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z" />
        </svg>
      </span>
      <input
        type="text"
        placeholder="Search"
        class="bg-[#f0f2f5] w-full h-full px-2 text-sm text-gray-800 outline-none placeholder:text-gray-500"
      />
    </label>

    <!-- Bell Icon -->
<button class="flex items-center justify-center bg-[#f0f2f5] rounded-lg h-10 w-10 text-gray-700 hover:bg-gray-200 transition">
  <svg xmlns="http://www.w3.org/2000/svg" class="w-9 h-5" fill="currentColor" viewBox="0 0 256 256">
    <path d="M221.8,175.94C216.25,166.38,208,139.33,208,104a80,80,0,1,0-160,0c0,35.34-8.26,62.38-13.81,71.94A16,16,0,0,0,48,200H88.81a40,40,0,0,0,78.38,0H208a16,16,0,0,0,13.8-24.06ZM128,216a24,24,0,0,1-22.62-16h45.24A24,24,0,0,1,128,216ZM48,184c7.7-13.24,16-43.92,16-80a64,64,0,1,1,128,0c0,36.05,8.28,66.73,16,80Z" />
  </svg>
</button>

    <!-- User Profile Avatar -->
     <?php if(isset($_SESSION['user_id'])): ?>
    <div class="relative">
      <div
        class="w-10 h-10 rounded-full bg-center bg-cover bg-no-repeat border-2 border-gray-300 shadow-sm hover:ring-2 hover:ring-blue-500 transition cursor-pointer"
        style='background-image: url("<?php echo $user['profile_photo']; ?>");'
        title="View Profile"
     ></div>
    </div>
    <?php endif; ?>
  </div>
</header>
