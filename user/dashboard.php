<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location:/AgeOgram/auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard | AgeOgram</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
  <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
    <h2 class="text-2xl font-bold text-indigo-600 mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> 👋</h2>
    <p class="text-gray-600 mb-6">You're now logged in to AgeOgram.</p>

    <form method="post">
      <button type="submit" name="logout" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md">
        Logout
      </button>
    </form>
  </div>
</body>
</html>

<?php
if (isset($_POST['logout'])) {
    require_once '../auth/logout.php';
    logout();
}
?>
