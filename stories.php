<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT username, email, bio, profile_pic FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

// Fetch user posts
$stmt = $conn->prepare("SELECT id, media, caption, type FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$posts_result = $stmt->get_result();
$stmt->close();

// Fetch followers count
$stmt = $conn->prepare("SELECT COUNT(*) AS followers FROM follows WHERE following_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$followers_result = $stmt->get_result();
$followers = $followers_result->fetch_assoc()['followers'];
$stmt->close();

// Fetch following count
$stmt = $conn->prepare("SELECT COUNT(*) AS following FROM follows WHERE follower_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$following_result = $stmt->get_result();
$following = $following_result->fetch_assoc()['following'];
$stmt->close();

// Fetch stories
$stmt = $conn->prepare("SELECT s.id, s.media, s.created_at, u.username FROM stories s JOIN users u ON s.user_id = u.id WHERE s.created_at >= NOW() - INTERVAL 24 HOUR ORDER BY s.created_at DESC");
$stmt->execute();
$stories_result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Insta Clone</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <h2><?php echo htmlspecialchars($user['username']); ?>'s Profile</h2>
        <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" width="100">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?></p>
        <p><strong>Followers:</strong> <?php echo $followers; ?> | <strong>Following:</strong> <?php echo $following; ?></p>
        
        <h3>Your Posts</h3>
        <div class="posts">
            <?php while ($post = $posts_result->fetch_assoc()): ?>
                <div class="post">
                    <?php if ($post['type'] == 'image'): ?>
                        <img src="<?php echo htmlspecialchars($post['media']); ?>" width="200">
                    <?php elseif ($post['type'] == 'video'): ?>
                        <video width="200" controls>
                            <source src="<?php echo htmlspecialchars($post['media']); ?>" type="video/mp4">
                        </video>
                    <?php endif; ?>
                    <p><?php echo htmlspecialchars($post['caption']); ?></p>
                </div>
            <?php endwhile; ?>
        </div>

        <h3>Stories</h3>
        <div class="stories">
            <?php while ($story = $stories_result->fetch_assoc()): ?>
                <div class="story">
                    <p><?php echo htmlspecialchars($story['username']); ?></p>
                    <img src="<?php echo htmlspecialchars($story['media']); ?>" alt="<?php echo htmlspecialchars($story['username']); ?>'s Story" width="150">
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>