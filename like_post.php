<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'];

    if ($action === 'like') {
        // Check if the like already exists
        $stmt = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert like into the database
            $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $post_id, $user_id);
            if ($stmt->execute()) {
                echo 'success'; // Return success message
            } else {
                echo 'error';
            }
        } else {
            echo 'already liked'; // User already liked the post
        }
        $stmt->close();
    } elseif ($action === 'unlike') {
        // Remove like from the database
        $stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
        if ($stmt->execute()) {
            echo 'success'; // Return success message
        } else {
            echo 'error';
        }
        $stmt->close();
    }
}
?>