<?php
require_once '../includes/db.php';
session_start();
 if(isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/user/dashboard.php");
    exit();
 }
$error = '';

if (isset($_POST['submit'])) {
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    header("Location: /AgeOgram/user/dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No user found with that email.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AgeOgram | Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 overflow-x-hidden">

  <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl mx-auto my-10 max-h-[90vh] grid md:grid-cols-2 overflow-hidden">
    
    <!-- Left: Form Section -->
    <div class="p-8 lg:p-12 mt-10 flex flex-col">
      <div class="text-center">
        <img src="../assets/images/AgeOgram.png" alt="AgeOgram Logo" class="w-20 h-20 mx-auto mb-4" />
        <h2 class="text-2xl font-bold text-indigo-600 mb-1">Welcome Back to AgeOgram</h2>
        <p class="text-sm text-gray-500 mb-6">Login to your account to continue</p>
      </div>

      <!-- Error Message -->
      <?php if (!empty($error)) : ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 rounded-md mb-4 text-sm text-center">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <input type="email" name="email" id="email" placeholder="Enter your email" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />

        <input type="password" name="password" id="password" placeholder="Enter your password" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />

        <button type="submit" name="submit"
          class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">Login</button>
      </form>
      <p class="mt-4 text-sm text-gray-500 text-center">Forgot your password? <a href="#" class="text-indigo-600 hover:underline">Reset it</a></p>
      
      <p class="mt-2 text-sm text-gray-500 text-center">Don't have an account? <a href="register.php" class="text-indigo-600 hover:underline">Register</a></p>
    </div>

    <!-- Right: Banner Image Section -->
    <div class="hidden md:block h-full">
      <img src="../assets/images/banner.png" alt="AgeOgram Banner" class="w-full h-full object-cover rounded-r-lg" />
    </div>
  </div>

</body>
</html>
