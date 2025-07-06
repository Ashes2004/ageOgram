<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AgeOgram | Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 overflow-x-hidden">

  <div class="bg-white rounded-lg shadow-lg w-full max-w-5xl mx-auto my-10 max-h-[90vh] grid md:grid-cols-2 overflow-hidden">
    
    <!-- Left: Form Section -->
    <div class="p-6 lg:p-12 mt-2 flex flex-col  ">
      <div class="text-center">
        <img src="../assets/images/AgeOgram.png" alt="AgeOgram Logo" class="w-20 h-20 mx-auto mb-4" />
        <h2 class="text-2xl font-bold text-indigo-600 mb-1">Welcome to AgeOgram</h2>
         <p class="text-sm text-gray-500 mb-6 ">Create your account to explore age-relevant content</p>
      </div>

      <form method="post" class="space-y-4">
        <input type="text" name="name" id="name" placeholder="Enter your name" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />

        <input type="email" name="email" id="email" placeholder="Enter your email" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />

        <input type="password" name="password" id="password" placeholder="Enter your password" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />

        <input type="password" name="confirm-password" id="confirm-password" placeholder="Confirm your password" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />

        <input type="date" name="dob" id="dob" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-indigo-200" />


        <button type="submit" name="submit"
          class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">Register</button>
      </form>

      <p class="mt-4 text-sm text-gray-500 text-center">Already have an account? <a href="login.php" class="text-indigo-600 hover:underline">Login</a></p>
    </div>

    <!-- Right: Banner Image Section (hidden on mobile) -->
    <div class="hidden md:block h-full">
      <img src="../assets/images/banner.png" alt="AgeOgram Banner" class="w-full h-full object-cover rounded-r-lg" />
    </div>

  </div>
</body>
</html>


<?php 
require_once '../includes/db.php';

if (isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
    $confirmPassword = htmlspecialchars($_POST['confirm-password'], ENT_QUOTES, 'UTF-8');
    $dob = htmlspecialchars($_POST['dob'], ENT_QUOTES, 'UTF-8');

    // Basic validation
    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    if (empty($dob)) {
        die("Date of birth is required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        die("Email already exists. Please use a different email.");
    }

    // Default image path
    $profilePhoto = "../assets/images/defaultDP.jpg";

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, dob, profile_photo) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("sssss", $name, $email, $hashedPassword, $dob, $profilePhoto);

    if ($stmt->execute()) {
        echo "User registered successfully.";
        header("Location: register.php?success=1");
        exit();
    } else {
        echo "Error registering user: " . $stmt->error;
    }

    $stmt->close();
}
?>






<?php 
require_once '../includes/db.php';

if (isset($_POST['submit'])) {
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');
    $confirmPassword = htmlspecialchars($_POST['confirm-password'], ENT_QUOTES, 'UTF-8');
    $age = (int) htmlspecialchars($_POST['age'], ENT_QUOTES, 'UTF-8');
    $image = $_FILES['image'] ?? null;

    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }

    if ($age < 1 || $age > 120) {
        die("Invalid age. Please enter a valid age between 1 and 120.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        die("Email already exists. Please use a different email.");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, age, profile_photo) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $profilePhoto = null;
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../assets/profile_photos/";
        $profilePhoto = $targetDir . $name . '_' . basename($image['name']);
        if (!move_uploaded_file($image['tmp_name'], $profilePhoto)) {
            die("Failed to upload profile photo.");
        }
    } else {
        die("No image uploaded or upload error.");
    }
    $profilePhoto = str_replace('../', '', $profilePhoto); // Ensure the path is relative to the web root 
    $stmt->bind_param("sssis", $name, $email, $hashedPassword, $age, $profilePhoto);
    
    if ($stmt->execute()) {
        echo "User registered successfully.";
        header("Location: register.php?success=1");
        exit();
    } else {
        echo "Error registering user: " . $stmt->error;
    }

    $stmt->close();
}
?>
