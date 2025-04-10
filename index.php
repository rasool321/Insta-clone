<?php 
session_start(); 
require 'config.php'; 

// Check if user is logged in 
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; 
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; 

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

// Fetch posts from database 
$query = " 
    SELECT posts.*, users.username, 
    (SELECT COUNT(*) FROM likes WHERE post_id = posts.id) AS like_count, 
    (SELECT COUNT(*) FROM comments WHERE post_id = posts.id) AS comment_count, 
    (SELECT COUNT(*) FROM likes WHERE post_id = posts.id AND user_id = ?) AS user_liked 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC 
"; 

$stmt = $conn->prepare($query); 
$stmt->bind_param("i", $user_id); 
$stmt->execute(); 
$result = $stmt->get_result(); 

// Fetch stories from database 
$story_query = " 
    SELECT stories.*, users.username 
    FROM stories 
    JOIN users ON stories.user_id = users.id 
    WHERE stories.created_at >= NOW() - INTERVAL 24 HOUR 
    ORDER BY stories.created_at DESC 
"; 

$story_stmt = $conn->prepare($story_query); 
$story_stmt->execute(); 
$stories_result = $story_stmt->get_result(); 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <title>Insta Clone</title> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css"> 
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

    <main>
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

        <h3>Stories</h3>
        <div class="stories"> 
            <?php while ($story = $stories_result->fetch_assoc()): ?> 
                <div class="story"> 
                    <p><?php echo htmlspecialchars($story['username']); ?></p> 
                    <?php 
                    $fileExtension = pathinfo($story['media'], PATHINFO_EXTENSION); 
                    if (in_array($fileExtension , ['jpg', 'jpeg', 'png', 'gif'])): 
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
        <div class="posts">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($post = $result->fetch_assoc()): ?>
                    <div class="post">
                        <p><strong><?php echo htmlspecialchars($post['username']); ?></strong></p>
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
                        const likeCountSpan = this.previousElementSibling; 
                        let likeCount = parseInt(likeCountSpan.textContent);
                        likeCount += (userLiked === 'like') ? 1 : -1; 
                        likeCountSpan.textContent = `${likeCount} ${likeCount === 1 ? 'Like' : 'Likes'}`; 
                        this.textContent = (userLiked === 'like') ? 'Unlike' : 'Like'; 
                        this.setAttribute('data-user-liked', userLiked === 'like' ? '1' : '0'); 
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
                        commentInput.value = '';

                        attachReplyListener(newComment.querySelector('.reply-text'));
                    } else {
                        alert(data.message);
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
                            replyInput.value = '';
                            replyForm.style.display = 'none';
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }
            }
        });

        function attachReplyListener(element) {
            element.addEventListener('click', function() {
                const replyForm = this.nextElementSibling;
                replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            });
        }

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
            <a href="https://www.linkedin.com/in/sk-rasool-basha-119364284" target="_blank" class="social-icon">
                <i class="fab fa-linkedin"></i>
            </a>
            <a href="https://www.instagram.com/rasool._3/" target="_blank" class="social-icon">
                <i class="fab fa-instagram"></i>
            </a>
        </div>
    </footer>
</body> 
</html>