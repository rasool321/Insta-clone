document.addEventListener("DOMContentLoaded", () => {
    // Like post function
    window.likePost = function(post_id) {
        fetch("like_post.php", {
            method: "POST",
            body: new URLSearchParams({ post_id }),
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        })
        .then(response => response.json())  // Handle the response as JSON
        .then(data => {
            let likeCountElem = document.getElementById(`like-count-${post_id}`);
            let likeButton = document.getElementById(`like-btn-${post_id}`);

            if (data.status === "liked") {
                likeButton.innerText = "Unlike";
                likeCountElem.innerText = data.likeCount; // Update the like count dynamically
            } else {
                likeButton.innerText = "Like";
                likeCountElem.innerText = data.likeCount;
            }
        });
    };

    // Toggle comments section
    window.toggleComments = function(post_id) {
        let commentsDiv = document.getElementById(`comments-${post_id}`);
        commentsDiv.style.display = (commentsDiv.style.display === "none") ? "block" : "none";
    };

    // Add comment function
    window.addComment = function(post_id) {
        let input = document.getElementById(`comment-input-${post_id}`);
        let commentSection = document.getElementById(`comment-section-${post_id}`);

        if (input.value.trim() === "") return;

        fetch("comment_post.php", {
            method: "POST",
            body: new URLSearchParams({ post_id, comment_text: input.value }),
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        })
        .then(response => response.json())  // Handle the response as JSON
        .then(data => {
            if (data.status === "success") {
                commentSection.innerHTML += `<p><strong>You:</strong> ${input.value}</p>`;
                input.value = ""; // Clear input
            } else {
                alert(data.message);  // Alert if something goes wrong
            }
        });
    };

    // Add reply function
    window.addReply = function(comment_id) {
        let replyInput = document.getElementById(`reply-input-${comment_id}`);
        let replySection = document.getElementById(`comment-section-${comment_id}`);
        if (replyInput.value.trim() === "") return;

        fetch("reply_post.php", {
            method: "POST",
            body: new URLSearchParams({ comment_id, reply_text: replyInput.value }),
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
        })
        .then(response => response.json())  // Handle the response as JSON
        .then(data => {
            if (data.status === "success") {
                replySection.innerHTML += `<div><strong>You:</strong> ${replyInput.value}</div>`;
                replyInput.value = ""; // Clear input
            } else {
                alert(data.message);  // Alert if something goes wrong
            }
        });
    };
});
