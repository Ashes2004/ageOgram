<?php
require_once './includes/db.php';


if(!isset($_SESSION['user_id'])) {
    header("Location: /AgeOgram/auth/login.php");
    exit();
}


// $username = "john_doe_das";
// $email = "johndas@example.com";
// $age = 22;
// $profile_photo = "uploads/profile_photos/john.jpg";

// $stmt = $conn->prepare("INSERT INTO users (username, email, age, profile_photo) VALUES (?, ?, ?, ?)");

// if ($stmt === false) {
//     die("Prepare failed: " . $conn->error);
// }

// $stmt->bind_param("ssis", $username, $email, $age, $profile_photo);

// if ($stmt->execute()) {
//     echo "User inserted successfully.";
// } else {
//     echo "Error inserting user: " . $stmt->error;
// }

// $stmt->close();


// $getData = $conn->query('SELECT * FROM users where age < 40 ; ');

// $users = [];

// while ($row = $getData->fetch_assoc()) {
//     $users[] = $row;
// }
// echo "<pre>";
// print_r($users); // Print as PHP array
// echo "</pre>";

?>
