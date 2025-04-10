// Modal functionality
const uploadStoryBtn = document.getElementById('uploadStoryBtn');
const uploadStoryModal = document.getElementById('uploadStoryModal');
const closeModal = document.getElementById('closeModal');

// Show modal
uploadStoryBtn.onclick = function() {
    uploadStoryModal.style.display = "block";
}

// Close modal
closeModal.onclick = function() {
    uploadStoryModal.style.display = "none";
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target === uploadStoryModal) {
        uploadStoryModal.style.display = "none";
    }
}

// Handle like/unlike functionality
function handleLikeUnlike(button) {
    const postId = button.getAttribute('data-post-id');
    const userLiked = button.getAttribute('data-user-liked') === '1' ? 'unlike' : 'like';

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
            const likeCountSpan = button.previousElementSibling; // Assuming the like count is before the button
            let likeCount = parseInt(likeCountSpan.textContent);
            likeCount += (userLiked === 'like') ? 1 : -1; // Update like count
            likeCountSpan.textContent = `${likeCount} ${likeCount === 1 ? 'Like' : 'Likes'}`; // Update text
            button.textContent = (userLiked === 'like') ? 'Unlike' : 'Like'; // Toggle button text
            button.setAttribute('data-user-liked', userLiked === 'like' ? '1' : '0'); // Update data attribute
        } else {
            console.error('Error:', data);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Attach event listeners to like buttons
document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', function() {
        handleLikeUnlike(this);
    });
});

// Handle comment submission
function handleCommentSubmission(form) {
    const formData = new FormData(form);
    const postId = formData.get('post_id');

    fetch('comment_post.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            const commentInput = form.querySelector('input[name="comment"]');
            const commentsSection = form.closest('.post').querySelector('.comments-section');

            // Update comment count
            const commentCountSpan = form.closest('.post').querySelector('.comment-count');
            let commentCount = parseInt(commentCountSpan.textContent);
            commentCount++;
            commentCountSpan.textContent = `${commentCount} ${commentCount === 1 ? 'Comment' : 'Comments'}`;

            // Add new comment to the comments section
            const newComment = document.createElement('div');
            newComment.classList.add('comment');
            newComment.innerHTML = `${commentInput.value} <button class="reply-button">Reply</button>
                                    <div class="reply-form" style="display:none;">
                                        <input type="text" placeholder="Add a reply..." required>
                                        <button class="reply-submit">Reply</button>
                                    </div>
                                    <div class="replies-section"></div>`;
            commentsSection.appendChild(newComment);
            commentInput.value = ''; // Clear input
        } else {
            console.error('Error:', data);
        }
    })
    .catch(error => console.error('Error:', error));
}

// Attach event listeners to comment forms
document.querySelectorAll('.comment-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        handleCommentSubmission(this);
    });
});

// Handle reply functionality
function handleReplySubmission(replyInput, commentDiv) {
    const postId = commentDiv.closest('.post').querySelector('input[name="post_id"]').value;
    const commentId = commentDiv.getAttribute('data-comment-id');

    fetch('comment_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `post_id=${postId}&comment=${replyInput.value}&parent_comment_id=${commentId}`
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            const newReply = document.createElement('div');
            newReply.classList.add('reply');
            newReply.innerHTML = replyInput.value;
            commentDiv.querySelector('.replies-section').appendChild(newReply);
            replyInput.value = ''; // Clear input
            replyInput.closest('.reply-form').style.display = 'none'; // Hide reply form
        }
    })
    .catch(error => console.error('Error:', error));
}

// Attach event listeners to reply buttons
document.querySelectorAll('.reply-button').forEach(button => {
    button.addEventListener('click', function() {
        const replyForm = this.nextElementSibling;
        replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
    });
});

// Attach event listeners to reply submit buttons
document.querySelectorAll('.reply-submit').forEach(button => {
    button.addEventListener('click', function() {
        const replyInput = this.previousElementSibling;
        const commentDiv = this.closest('.comment');

        if (replyInput.value.trim() !== '') {
            handleReplySubmission(replyInput, commentDiv);
        }
    });
});

// Toggle dark/light mode
const toggleButton = document.getElementById('toggleButton');
const usernameDisplay = document.getElementById('usernameDisplay');
const profileButton = document.getElementById('profileButton');

toggleButton.addEventListener('click', function() {
    document.body.classList.toggle('dark-mode');
    document.querySelector('header').classList.toggle('dark-mode');
    usernameDisplay.classList.toggle('dark-mode');
});

// Show/hide username on profile button click
profileButton.addEventListener('click', function() {
    usernameDisplay.style.display = usernameDisplay.style.display === 'none' ? 'block' : 'none';
});

// Toggle sidebar visibility
const sidebarToggleButton = document.getElementById('sidebarToggleButton');
const sidebar = document.querySelector('.sidebar');

sidebarToggleButton.addEventListener('click', () => {
    sidebar.classList.toggle('open');
});