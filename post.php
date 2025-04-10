<?php
session_start();
require 'config.php';

if (!isset($_GET['id'])) {
    die("Invalid post ID.");
}

$post_id = intval($_GET['id']);

$query = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$query->bind_param("i", $post_id);
$query->execute();
$result = $query->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    die("Post not found.");
}

// Fetch comments
$comments = $conn->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC");
$comments->bind_param("i", $post_id);
$comments->execute();
$comment_result = $comments->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($post['username']); ?>'s Post</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <h2>Post by <?php echo htmlspecialchars($post['username']); ?></h2>
    <img src="assets/images/<?php echo htmlspecialchars($post['file_path']); ?>" alt="Post Image">
    <p><?php echo htmlspecialchars($post['caption']); ?></p>

    <h3>Comments</h3>
    <?php while ($row = $comment_result->fetch_assoc()): ?>
        <p><strong><?php echo htmlspecialchars($row['username']); ?>:</strong> <?php echo htmlspecialchars($row['comment_text']); ?></p>
    <?php endwhile; ?>

    <form method="POST" action="comment.php">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <textarea name="comment_text" placeholder="Add a comment" required></textarea>
        <button type="submit">Comment</button>
    </form>
</body>
</html>
