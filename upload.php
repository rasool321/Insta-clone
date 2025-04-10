<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = ''; // Initialize error variable

// Fetch user profile picture
$profile_pic = null;
if ($user_id) {
    $user_query = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $user_query->bind_param("i", $user_id);
    $user_query->execute();
    $user_result = $user_query->get_result();
    if ($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        $profile_pic = $user_data['profile_pic'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $caption = trim($_POST['caption']);
    $file_path = '';
    $type = '';

    if (!empty($_FILES['file']['name'])) {
        $target_dir = "assets/images/";
        $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed_images = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_videos = ['mp4', 'mov', 'avi', 'wmv'];

        // Validate file type
        if (in_array($file_ext, $allowed_images)) {
            $type = 'image';
        } elseif (in_array($file_ext, $allowed_videos)) {
            $type = 'video';
        } else {
            $error = "Invalid file format. Only images & videos are allowed.";
        }

        // Validate file size (Max: 10MB)
        if ($_FILES['file']['size'] > 10 * 1024 * 1024) { // 10MB
            $error = "File size too large. Max 10MB allowed.";
        }

        // If no errors, process upload
        if (empty($error)) {
            $newFileName = uniqid() . "." . $file_ext;
            $file_path = $target_dir . $newFileName;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
                // Store only the filename in the database (not full path)
                $stmt = $conn->prepare("INSERT INTO posts (user_id, file_path, caption, type) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $newFileName, $caption, $type);
                $stmt->execute();
                $stmt->close();

                header("Location: user_dashboard.php");
                exit();
            } else {
                $error = "File upload failed.";
            }
        }
    } else {
        $error = "Please select a file to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload - Insta Clone</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <link rel="stylesheet" href="assets/css/upload.css">
</head>
<body>
<header>
    <h1>Insta Clone</h1>
    <nav class="header-nav"> 
        <button class="toggle-button" id="toggleColorBtn">Black/White</button> 
        <div class="profile-picture"> 
            <?php if ($profile_pic): ?>
                <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" class="profile-pic" alt="Profile Picture" /> 
            <?php else: ?>
                <p>No Profile Picture</p> 
            <?php endif; ?> 
        </div> 
    </nav>
</header>

<div class="sidebar">
    <h2>Navigation</h2>
    <ul class="nav-list">
        <li><a href="index.php">Home</a></li>
        <li><a href="user_dashboard.php">Profile</a></li>
        <li><a href="upload.php">Upload</a></li>
        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>

<div class="container">
    <h2>Upload Post</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"> <?php echo htmlspecialchars($error); ?> </p>
        <?php endif; ?>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <textarea name="caption" placeholder="Enter caption..." required></textarea><br>
            <input type="file" name="file" accept="image/*,video/*" required><br>
            <button type="submit">Upload</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>