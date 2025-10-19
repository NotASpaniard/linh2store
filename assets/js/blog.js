/**
 * Blog JavaScript Functions
 * Linh2Store - Blog interactions and theme toggle
 */

// Like functionality
function likePost(postId) {
    if (!isLoggedIn()) {
        alert('Vui lòng đăng nhập để like bài viết');
        return;
    }

    const likeBtn = document.querySelector(`[data-post-id="${postId}"]`);
    const likeCount = likeBtn.querySelector('.like-count');

    fetch('api/blog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'like',
                post_id: postId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                likeBtn.classList.toggle('liked', data.liked);
                likeCount.textContent = data.like_count;
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi like bài viết');
        });
}

// Comment functionality
function submitComment(postId, content, parentId = null) {
    if (!isLoggedIn()) {
        alert('Vui lòng đăng nhập để bình luận');
        return;
    }

    if (!content.trim()) {
        alert('Vui lòng nhập nội dung bình luận');
        return;
    }

    fetch('api/blog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add_comment',
                post_id: postId,
                content: content,
                parent_id: parentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload comments
                loadComments(postId);
                // Clear form
                const form = document.getElementById('comment-form');
                if (form) {
                    form.reset();
                }
            } else {
                alert(data.error || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi gửi bình luận');
        });
}

// Load comments
function loadComments(postId) {
    fetch('api/blog.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'get_comments',
                post_id: postId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCommentsList(data.comments);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Update comments list
function updateCommentsList(comments) {
    const commentsList = document.getElementById('comments-list');
    if (!commentsList) return;

    commentsList.innerHTML = '';

    comments.forEach(comment => {
        const commentElement = createCommentElement(comment);
        commentsList.appendChild(commentElement);
    });
}

// Create comment element
function createCommentElement(comment) {
    const commentDiv = document.createElement('div');
    commentDiv.className = 'comment-item';

    commentDiv.innerHTML = `
        <div class="comment-avatar">
            <img src="${comment.avatar || '../images/product_1.jpg'}" alt="${comment.username}">
        </div>
        <div class="comment-content">
            <div class="comment-header">
                <span class="comment-author">${comment.username}</span>
                <span class="comment-date">${formatDate(comment.created_at)}</span>
            </div>
            <div class="comment-text">
                ${comment.content.replace(/\n/g, '<br>')}
            </div>
        </div>
    `;

    return commentDiv;
}

// Share functionality
function sharePost() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        copyLink();
    }
}

// Copy link functionality
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('Đã copy link bài viết!');
    }).catch(() => {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = window.location.href;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Đã copy link bài viết!');
    });
}

// Theme toggle functionality
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);

    // Update toggle button icon
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        const icon = toggleBtn.querySelector('i');
        if (icon) {
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
}

// Load theme on page load
function loadTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    // Update toggle button icon
    const toggleBtn = document.getElementById('theme-toggle');
    if (toggleBtn) {
        const icon = toggleBtn.querySelector('i');
        if (icon) {
            icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        }
    }
}

// Utility functions
function isLoggedIn() {
    // Check if user is logged in (you can implement this based on your auth system)
    return document.body.classList.contains('logged-in') ||
        document.querySelector('.user-actions .user-icon[href*="user"]') !== null;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Load theme
    loadTheme();

    // Handle comment form submission
    const commentForm = document.getElementById('comment-form');
    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = this.querySelector('textarea[name="content"]').value;
            const postId = getPostIdFromUrl();
            if (postId) {
                submitComment(postId, content);
            }
        });
    }

    // Handle like button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn')) {
            e.preventDefault();
            const postId = e.target.closest('.like-btn').dataset.postId;
            if (postId) {
                likePost(postId);
            }
        }
    });

    // Handle share button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.share-btn')) {
            e.preventDefault();
            sharePost();
        }
    });

    // Handle copy button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.copy-btn')) {
            e.preventDefault();
            copyLink();
        }
    });

    // Handle theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            toggleTheme();
        });
    }
});

// Get post ID from URL (for comment submission)
function getPostIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    const slug = urlParams.get('slug');
    // You might need to get the actual post ID from the page data
    // For now, we'll try to get it from a data attribute on the article
    const article = document.querySelector('.blog-article');
    return article ? article.dataset.postId : null;
}