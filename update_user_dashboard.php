<?php
session_start();
include_once __DIR__ . "/config.php"; // Ensure this file exists

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $profile_pic = $_FILES['profile_pic'];

    // Prepare the SQL statement
    $query = "UPDATE users SET username = ?, email = ?, bio = ?";

    // Check if a new profile picture is uploaded
    if ($profile_pic['name']) {
        // Handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($profile_pic["name"]);
        
        // Move the uploaded file to the target directory
        if (move_uploaded_file($profile_pic["tmp_name"], $target_file)) {
            $query .= ", profile_pic = ?";
        } else {
            echo "Error uploading file.";
            exit();
        }
    }

    // Prepare the statement for execution
    $stmt = $conn->prepare($query . " WHERE id = ?");
    if ($profile_pic['name']) {
        $stmt->bind_param("ssssi", $username, $email, $bio, $profile_pic["name"], $user_id);
    } else {
        $stmt->bind_param("sssi", $username, $email, $bio, $user_id);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Update session variables if necessary
        $_SESSION['username'] = $username;
        if ($profile_pic['name']) {
            $_SESSION['profile_pic'] = $profile_pic['name']; // Update the session with the new profile picture
        }
        $_SESSION['profile_updated'] = true; // Set a flag to indicate the profile has been updated
        header("Location: user_dashboard.php");
        exit();
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}
?>