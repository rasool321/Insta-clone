<?php
session_start();
require 'config.php'; // Include your database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file was uploaded
    if (isset($_FILES['story']) && $_FILES['story']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['story']['tmp_name'];
        $fileName = $_FILES['story']['name'];
        $fileSize = $_FILES['story']['size'];
        $fileType = $_FILES['story']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Specify the directory to save the uploaded file
        $uploadFileDir = 'assets/stories/';
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension; // Create a unique file name
        $dest_path = $uploadFileDir . $newFileName;

        // Check if the directory exists
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true); // Create the directory if it doesn't exist
        }

        // Move the file to the specified directory
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            // File is successfully uploaded
            // Insert the story into the database
            $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
            $query = "INSERT INTO stories (user_id, media, created_at) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $user_id, $newFileName); // Bind user_id as integer and media as string
            if ($stmt->execute()) {
                echo "File is successfully uploaded.";
            } else {
                echo "Failed to insert story into the database: " . $stmt->error;
            }
        } else {
            echo "Failed to upload file.";
        }
    } else {
        echo "No file uploaded or there was an upload error.";
    }
} else {
    echo "Invalid request method.";
}
?>