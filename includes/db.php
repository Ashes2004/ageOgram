<?php
$envPath = dirname(__DIR__) . '/.env'; 

if (!file_exists($envPath)) {
    die("❌ .env file not found at: $envPath");
}

$config = parse_ini_file($envPath);
if (!$config) {
    die("❌ Failed to read .env file.");
}

$host = $config['DB_HOST'];
$port = $config['DB_PORT'];
$user = $config['DB_USER'];
$pass = $config['DB_PASS'];
$dbName = $config['DB_NAME'];

// Tables definition
$tables = [
  "users" => "
    CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    bio TEXT DEFAULT NULL,
    dob DATE,
    is_verified BOOLEAN DEFAULT FALSE,
    profile_photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  );
 ",
  "posts" => "
    CREATE TABLE posts (
      id INT AUTO_INCREMENT PRIMARY KEY,
      user_id INT,
      content TEXT,
      media_url VARCHAR(255),
      media_type ENUM('image', 'video', 'none') DEFAULT 'none',
      tags VARCHAR(255),
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
  ",
  "comments" => "
    CREATE TABLE comments (
      id INT AUTO_INCREMENT PRIMARY KEY,
      post_id INT,
      user_id INT,
      comment TEXT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )
  ",
  "follows" => "
    CREATE TABLE follows (
      id INT AUTO_INCREMENT PRIMARY KEY,
      follower_id INT,
      followed_id INT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
      FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
    )
    " , 
    "likes" => "
    CREATE TABLE likes (
      id INT AUTO_INCREMENT PRIMARY KEY,
      post_id INT,
      user_id INT,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
      FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"

];

// 1. Connect without DB first
$conn = new mysqli($host, $user, $pass, "", $port);
if ($conn->connect_error) {
    die("❌ DB Connection failed: " . $conn->connect_error);
}

// 2. Check if DB exists
$dbCheck = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbName'");
if ($dbCheck->num_rows === 0) {
    if (!$conn->query("CREATE DATABASE $dbName")) {
        die("❌ Failed to create DB: " . $conn->error);
    }
}

// 3. Select DB
$conn->select_db($dbName);

// 4. Check and create tables
foreach ($tables as $name => $sql) {
    $exists = $conn->query("SHOW TABLES LIKE '$name'");
    if ($exists->num_rows == 0) {
        if (!$conn->query($sql)) {
            die("❌ Failed to create table '$name': " . $conn->error);
        }
    }
}

?>
