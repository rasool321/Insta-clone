<?php
session_start();
include_once __DIR__ . "/config.php"; // Ensure this file exists

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'not_logged_in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Ensure database connection exists
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'database_connection_failed']);
    exit();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0; // Ensure post_id is an integer
    $comment = trim($_POST['comment']);
    $parent_comment_id = isset($_POST['parent_comment_id']) ? intval($_POST['parent_comment_id']) : null;

    if (!empty($comment)) {
        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, comment, parent_comment_id) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iisi", $post_id, $user_id, $comment, $parent_comment_id);
            
            // Execute the statement
            if ($stmt->execute()) {
                $comment_id = $stmt->insert_id; // Get the ID of the newly inserted comment
                echo json_encode(['status' => 'success', 'comment_id' => $comment_id, 'comment' => $comment, 'user_id' => $user_id]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'database_error']);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'stmt_error']); // Statement preparation error
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'empty_comment']); // Comment is empty
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'invalid_request']); // Not a POST request
}
?>