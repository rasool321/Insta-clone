<?php
session_start();
include_once __DIR__ . "/config.php"; // Ensure this file exists

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ensure database connection exists
if (!$conn) {
    die("Database connection failed.");
}

// Fetch user details
$query = $conn->prepare("SELECT username, email, bio, profile_pic FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->close();


// Fetch user stories
$stories_query = $conn->prepare("
    SELECT stories.media, users.username 
    FROM stories 
    JOIN users ON stories.user_id = users.id 
    WHERE stories.user_id = ?
");
$stories_query->bind_param("i", $user_id);
$stories_query->execute();
$stories_result = $stories_query->get_result();
$stories_query->close();

// Fetch user posts
$post_query = $conn->prepare("
    SELECT posts.id, posts.file_path, posts.caption, posts.type, 
           (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count,
           (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count,
           (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = ?) AS user_liked
    FROM posts 
    WHERE posts.user_id = ?
    ORDER BY posts.created_at DESC
");
$post_query->bind_param("ii", $user_id, $user_id);
$post_query->execute();
$post_result = $post_query->get_result();
$post_query->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['username']); ?>'s Dashboard</title>
    <link rel="stylesheet" href="assets/css/styles.css"> <!-- Link to external CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="assets/js/script.js" defer></script>
</head>
<body>
<header>
     <h1>Dashboard</h1> 
     <nav class="header-nav"> 
        <button class="toggle-button" id="toggleColorBtn">Black/White</button> 
        <div class="profile-picture"> 
            <?php if ($user['profile_pic']): ?>
                <img src="uploads/<?php echo htmlspecialchars($user['profile_pic']); ?>" class="profile-pic" alt="Profile Picture" /> 
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

<main>
<h1><?php echo htmlspecialchars($user['username']); ?>'s Dashboard</h1>
<section class="profile-info">
<form action="update_user_dashboard.php" method="POST" enctype="multipart/form-data" class="profile-section">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

    <label for="bio">Description :</label>
    <textarea name="bio" id="bio" required><?php echo htmlspecialchars($user['bio']); ?></textarea>

    <label for="profile_pic">Choose Profile Picture:</label>
    <input type="file" name="profile_pic" id="profile_pic">

    <button type="submit" class="update-button">Update Profile</button>
</form>
</section>

<h3>Stories</h3>
<div class="stories"> 
    <?php while ($story = $stories_result->fetch_assoc()): ?> 
        <div class="story"> 
            <p><?php echo htmlspecialchars($story['username']); ?></p> 
            <?php 
            $fileExtension = pathinfo($story['media'], PATHINFO_EXTENSION); 
            if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])): 
            ?> 
                <img src="assets/stories/<?php echo htmlspecialchars($story['media']); ?>" alt="Story Image"> 
            <?php 
            elseif (in_array($fileExtension, ['mp4', 'mov', 'avi'])): 
            ?> 
                <video width="150" controls> 
                    <source src="assets/stories/<?php echo htmlspecialchars($story['media']); ?>" type="video/mp4"> 
                    Your browser does not support the video tag. 
                </video> 
            <?php 
            else: 
            ?> 
                <p>Unsupported media type.</p> 
            <?php 
            endif; 
            ?> 
        </div> 
    <?php endwhile; ?> 
</div>
<h3>Posts</h3>
<button onclick="window.location.href='upload.php'" class="upload-button">Upload New Post</button>
<div class="posts">
    <?php if ($post_result->num_rows > 0): ?>
        <?php while ($post = $post_result->fetch_assoc()): ?>
            <div class="post">
                <p><strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                <p><?php echo htmlspecialchars($post['caption']); ?></p>

                <?php if ($post['type'] == 'image'): ?>
                    <img src="assets/images/<?php echo htmlspecialchars($post['file_path']); ?>" alt="Post Image">
                <?php elseif ($post['type'] == 'video'): ?>
                    <video controls>
                        <source src="assets/images/<?php echo htmlspecialchars($post['file_path']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php endif; ?>

                <span class="like-count"><?php echo $post['like_count'] . ' ' . ($post['like_count'] === 1 ? 'Like' : 'Likes'); ?></span>
                <button class="like-button" data-post-id="<?php echo $post['id']; ?>" data-user-liked="<?php echo $post['user_liked']; ?>">
                    <?php echo $post['user_liked'] > 0 ? 'Unlike' : 'Like'; ?>
                </button>

                <form class="comment-form" action="comment_post.php" method="POST">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <input type="text" name="comment" placeholder="Add a comment..." required>
                    <button type="submit">Comment</button>
                </form>

                <div class="comments-section">
                    <?php
                    $comment_query = $conn->prepare("SELECT comments.comment, comments.id AS comment_id FROM comments WHERE comments.post_id = ?");
                    $comment_query->bind_param("i", $post['id']);
                    $comment_query->execute();
                    $comment_result = $comment_query->get_result();

                    while ($comment = $comment_result->fetch_assoc()):
                    ?>
                        <div class="comment" data-comment-id="<?php echo $comment['comment_id']; ?>">
                            <?php echo htmlspecialchars($comment['comment']); ?>
                            <span class="reply-text" style="color: #007bff; cursor: pointer;">Reply</span>
                            <div class="reply-form" style="display:none;">
                                <input type="text" placeholder="Add a reply..." required>
                                <button class="reply-submit">Reply</button>
                            </div>
                            <div class="replies-section">
                                <!-- Replies will be appended here -->
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No posts available. Be the first to upload!</p>
    <?php endif; ?>
</div>
    </main>
<script>
    // Toggle dark mode functionality
    const toggleColorBtn = document.getElementById('toggleColorBtn');
    toggleColorBtn.addEventListener('click', function() {
        document.body.classList.toggle('dark-mode'); // Toggle the dark mode class
    });

    // Handle like/unlike functionality
    document.querySelectorAll('.like-button').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.getAttribute('data-post-id');
            const userLiked = this.getAttribute('data-user-liked') === '1' ? 'unlike' : 'like';

            fetch('like_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_id=${postId}&action=${userLiked}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    const likeCountSpan = this.previousElementSibling; // Assuming the like count is before the button
                    let likeCount = parseInt(likeCountSpan.textContent);
                    likeCount += (userLiked === 'like') ? 1 : -1; // Update like count
                    likeCountSpan.textContent = `${likeCount} ${likeCount === 1 ? 'Like' : 'Likes'}`; // Update text
                    this.textContent = (userLiked === 'like') ? 'Unlike' : 'Like'; // Toggle button text
                    this.setAttribute('data-user-liked', userLiked === 'like' ? '1' : '0'); // Update data attribute
                } else {
                    console.error('Error:', data);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

// Handle comment submission
document.addEventListener('submit', function(e) {
    if (e.target && e.target.classList.contains('comment-form')) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch('comment_post.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const commentInput = form.querySelector('input[name="comment"]');
                const postId = form.querySelector('input[name="post_id"]').value;
                const commentsSection = form.closest('.post').querySelector('.comments-section');

                // Create a new comment element
                const newComment = document.createElement('div');
                newComment.classList.add('comment');
                newComment.setAttribute('data-comment-id', data.comment_id);
                newComment.innerHTML = `
                    <strong>You:</strong> ${data.comment} 
                    <span class="reply-text" style="color: #007bff; cursor: pointer;">Reply</span>
                    <div class="reply-form" style="display:none;">
                        <input type="text" placeholder="Add a reply..." required>
                        <button class="reply-submit">Reply</button>
                    </div>
                    <div class="replies-section"></div>
                `;
                commentsSection.appendChild(newComment);
                commentInput.value = ''; // Clear input

                // Attach reply event listener to the new comment
                attachReplyListener(newComment.querySelector('.reply-text'));
            } else {
                alert(data.message); // Show error message
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

// Handle reply submission
document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('reply-submit')) {
        e.preventDefault();
        const replyButton = e.target;
        const replyForm = replyButton.closest('.reply-form');
        const replyInput = replyForm.querySelector('input[type="text"]');
        const commentDiv = replyForm.closest('.comment');
        const postId = commentDiv.closest('.post').querySelector('input[name="post_id"]').value;
        const parentCommentId = commentDiv.getAttribute('data-comment-id');

        if (replyInput.value.trim() !== '') {
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('comment', replyInput.value);
            formData.append('parent_comment_id', parentCommentId);

            fetch('comment_post.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const newReply = document.createElement('div');
                    newReply.classList.add('reply');
                    newReply.innerHTML = `<strong>You:</strong> ${data.comment}`;
                    commentDiv.querySelector('.replies-section').appendChild(newReply);
                    replyInput.value = ''; // Clear input
                    replyForm.style.display = 'none'; // Hide reply form
                } else {
                    alert(data.message); // Show error message
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
});

// Attach reply event listener to a specific element
function attachReplyListener(element) {
    element.addEventListener('click', function() {
        const replyForm = this.nextElementSibling;
        replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
    });
}

// Attach reply event listeners to all existing reply buttons
document.querySelectorAll('.reply-text').forEach(text => {
    attachReplyListener(text);
});
</script>
<footer>
    <p>Insta Clone &copy; 2025</p>
    <div class="social-icons">
        <a href="https://github.com/rasool321" target="_blank" class="social-icon">
            <i class="fab fa-github"></i>
        </a>
        <a href="www.linkedin.com/in/sk-rasool-basha-119364284" target="_blank" class="social-icon">
            <i class="fab fa-linkedin"></i>
        </a>
        <a href="https://www.instagram.com/rasool._3/" target="_blank" class="social-icon">
            <i class="fab fa-instagram"></i>
        </a>
    </div>
</footer>
</body>
</html>