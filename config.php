<?php
$host = "localhost"; // Change if necessary
$user = "root"; // Your database username
$password = ""; // Your database password (leave empty for XAMPP)
$database = "insta_clone"; // Your database name

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");
?>
