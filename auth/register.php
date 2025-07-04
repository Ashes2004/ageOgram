

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgeOgram | Register</title>
</head>
<body>
  <h2>Welcome to AgeOgram</h2>
  <form  method="post" enctype="multipart/form-data">
     <input type="text" name="name" id="name" placeholder = "Enter your name" required><br><br>
     <input type="email" name="email" id="email" placeholder = "Enter your email" required><br><br>
     <input type="password" name="password" id="password" placeholder = "Enter Your PassWord" required><br><br>
     <input type="password" name="confirm-password" id="confirm-password" placeholder = "Enter Your confirm-PassWord" required><br><br>
     <input type="number" name="age" id="age" value="1"><br><br>
    <label for="image">Upload an image:</label><br>
    <input type="file" name="image" id="image" accept="image/*" required><br><br>

    <button type="submit" name = "submit">Upload</button>
  </form>


</body>
</html>


<?php 
require_once '../includes/db.php';

if(!isset($_POST['submit']))
{
    echo "Please fill the form";
} else {
    

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];
    $age = $_POST['age'];
    $image = (isset($_FILES['image']) ? $_FILES['image'] : null);

    // Check if passwords match
    if ($password !== $confirmPassword) {
        die("Passwords do not match.");
    }


    // Validate age
    if ($age < 1 || $age > 120) {
        die("Invalid age. Please enter a valid age between 1 and 120.");
    }

    //check email already exists (first check email format)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        die("Email already exists. Please use a different email.");
    }
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, age, profile_photo) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    // Handle file upload
    $profilePhoto = null;
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $targetDir = "../assets/profile_photos/";
        $profilePhoto = $targetDir .$name. basename($image['name']);
        if (!move_uploaded_file($image['tmp_name'], $profilePhoto)) {
            die("Failed to upload profile photo.");
        }
    } else {
        die("No image uploaded or upload error.");
    }

    // Bind parameters and execute
    $stmt->bind_param("sssis", $name, $email, $hashedPassword, $age, $profilePhoto);
    if ($stmt->execute()) {
        echo "User registered successfully.";
    } else {
        echo "Error registering user: " . $stmt->error;
    }
    // Close the statement
    $stmt->close();

  
   
}

?>