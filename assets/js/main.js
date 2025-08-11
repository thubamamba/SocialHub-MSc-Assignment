// Theme Management
let currentTheme = 'light';

// DOM Event Handlers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize theme
    initializeTheme();

    // Initialization to attach the event listener for the search form
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', searchUsers);
    }

    // Initialize tooltips and popovers
    // const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    // const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    //     return new bootstrap.Tooltip(tooltipTriggerEl);
    // });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert-auto-dismiss');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Initialize smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Fade-in animation to cards
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        setTimeout(() => {
            card.classList.add('fade-in');
        }, index * 100);
    });
});

// Theme Management Functions
function initializeTheme() {
    // Get saved theme from session storage (using a simple cookie fallback)
    currentTheme = getCookie('theme') || 'light';

    // Apply theme
    document.documentElement.setAttribute('data-bs-theme', currentTheme);
    updateThemeIcon();
}

function toggleTheme() {
    currentTheme = currentTheme === 'light' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-bs-theme', currentTheme);

    // Save theme preference
    setCookie('theme', currentTheme, 365);

    // Update icon
    updateThemeIcon();

    // Show feedback
    showAlert(`Switched to ${currentTheme} mode`, 'info', 2000);
}

function updateThemeIcon() {
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        if (currentTheme === 'dark') {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        } else {
            themeIcon.classList.remove('fa-sun');
            themeIcon.classList.add('fa-moon');
        }
    }
}

// Cookie Management (fallback for theme persistence)
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Enhanced Alert Function
function showAlert(message, type = 'info', duration = 5000) {
    const alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) return;

    const alertId = 'alert-' + Date.now();
    const alertTypes = {
        'success': { icon: 'fa-check-circle', title: 'Success!' },
        'danger': { icon: 'fa-exclamation-triangle', title: 'Error!' },
        'warning': { icon: 'fa-exclamation-circle', title: 'Warning!' },
        'info': { icon: 'fa-info-circle', title: 'Info' }
    };

    const alertInfo = alertTypes[type] || alertTypes['info'];

    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert" id="${alertId}">
            <i class="fas ${alertInfo.icon} me-2"></i>
            <strong>${alertInfo.title}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    alertContainer.insertAdjacentHTML('beforeend', alertHTML);

    // Auto-remove after duration
    setTimeout(() => {
        const alert = document.getElementById(alertId);
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, duration);
}

// Logout Confirmation
function confirmLogout() {
    const logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));
    logoutModal.show();
}

// Enhanced Toggle Like Function
async function toggleLike(postId) {
    const likeBtn = document.querySelector(`[data-post-id="${postId}"] .like-btn`);
    const likesCount = likeBtn.querySelector('.likes-count');

    // Loading state
    const originalHTML = likeBtn.innerHTML;
    likeBtn.innerHTML = '<div class="loading"></div>';
    likeBtn.disabled = true;

    try {
        const response = await fetch('api/toggle_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        });

        const data = await response.json();

        if (data.success) {
            if (data.liked) {
                likeBtn.classList.add('liked');
                showAlert('Post liked!', 'success', 2000);
            } else {
                likeBtn.classList.remove('liked');
                showAlert('Post unliked!', 'info', 2000);
            }

            // Update count with animation
            likesCount.textContent = data.likes_count;
            likesCount.style.transform = 'scale(1.2)';
            setTimeout(() => {
                likesCount.style.transform = 'scale(1)';
            }, 200);
        } else {
            showAlert(data.message || 'Error updating like', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Network error occurred', 'danger');
    } finally {
        // Restore button state
        likeBtn.innerHTML = originalHTML;
        likeBtn.disabled = false;
    }
}

// Enhanced Toggle Comments Visibility
function toggleComments(postId) {
    const commentsSection = document.getElementById(`comments-${postId}`);
    const toggleBtn = document.querySelector(`[data-post-id="${postId}"] .card-footer button[onclick*="toggleComments"]`);
    const icon = toggleBtn.querySelector('i');

    const isVisible = commentsSection.style.display !== 'none';

    if (isVisible) {
        commentsSection.style.display = 'none';
        icon.classList.remove('fa-comment-slash');
        icon.classList.add('fa-comment');
    } else {
        commentsSection.style.display = 'block';
        commentsSection.classList.add('fade-in');
        icon.classList.remove('fa-comment');
        icon.classList.add('fa-comment-slash');

        // Scroll to comments section smoothly
        setTimeout(() => {
            commentsSection.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }, 100);
    }
}

// Add Comment Function
async function addComment(event, postId) {
    event.preventDefault();

    const form = event.target;
    const input = form.querySelector('input[type="text"]');
    const content = input.value.trim();

    if (!content) {
        showAlert('Please enter a comment', 'warning');
        input.focus();
        return;
    }

    if (content.length > 500) {
        showAlert('Comment is too long (max 500 characters)', 'warning');
        return;
    }

    // Show loading state
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="loading"></div>';
    submitBtn.disabled = true;

    try {
        const response = await fetch('api/add_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                post_id: postId,
                content: content
            })
        });

        const data = await response.json();

        if (data.success) {
            // Add new comment to the DOM
            const commentsList = document.querySelector(`#comments-${postId} .comments-list`);
            const newCommentHTML = `
                <div class="comment mb-2 fade-in" data-comment-id="${data.comment.id}">
                    <div class="d-flex">
                        <img src="assets/images/${data.comment.profile_picture}" 
                             alt="Profile" class="profile-img-tiny me-2"
                             onerror="this.src='assets/images/default.jpg'">
                        <div class="flex-grow-1">
                            <div class="comment-content">
                                <strong>${data.comment.username}</strong>
                                ${data.comment.content}
                            </div>
                            <small class="text-muted">Just now</small>
                            ${data.comment.can_delete ? `
                                <button class="btn btn-link btn-sm text-danger p-0 ms-2" onclick="deleteComment(${data.comment.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;

            commentsList.insertAdjacentHTML('beforeend', newCommentHTML);
            input.value = '';
            showAlert('Comment added successfully!', 'success', 2000);
        } else {
            showAlert(data.message || 'Error adding comment', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Network error occurred', 'danger');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalHTML;
        submitBtn.disabled = false;
        input.focus();
    }
}

// Enhanced Delete Comment Function
async function deleteComment(commentId) {
    if (!confirm('Are you sure you want to delete this comment?')) {
        return;
    }

    const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);

    // Add loading state
    if (commentElement) {
        commentElement.style.opacity = '0.5';
    }

    try {
        const response = await fetch('api/delete_comment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ comment_id: commentId })
        });

        const data = await response.json();

        if (data.success) {
            if (commentElement) {
                commentElement.style.opacity = '0';
                commentElement.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    commentElement.remove();
                }, 300);
            }
            showAlert('Comment deleted successfully!', 'success', 2000);
        } else {
            if (commentElement) {
                commentElement.style.opacity = '1';
            }
            showAlert(data.message || 'Error deleting comment', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        if (commentElement) {
            commentElement.style.opacity = '1';
        }
        showAlert('Network error occurred', 'danger');
    }
}

// Enhanced Delete Post Function
async function deletePost(postId) {
    if (!confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
        return;
    }

    const postElement = document.querySelector(`[data-post-id="${postId}"]`);

    // Add loading state
    if (postElement) {
        postElement.style.opacity = '0.5';
        postElement.style.pointerEvents = 'none';
    }

    try {
        const response = await fetch('api/delete_post.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ post_id: postId })
        });

        const data = await response.json();

        if (data.success) {
            if (postElement) {
                postElement.style.opacity = '0';
                postElement.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    postElement.remove();
                }, 300);
            }
            showAlert('Post deleted successfully!', 'success');
        } else {
            if (postElement) {
                postElement.style.opacity = '1';
                postElement.style.pointerEvents = 'auto';
            }
            showAlert(data.message || 'Error deleting post', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        if (postElement) {
            postElement.style.opacity = '1';
            postElement.style.pointerEvents = 'auto';
        }
        showAlert('Network error occurred', 'danger');
    }
}

async function searchUsers(event) {
    event.preventDefault();

    const searchInput = document.getElementById('searchInput');
    const query = searchInput.value.trim();

    if (!query) {
        showAlert('Please enter a search term', 'warning');
        searchInput.focus();
        return;
    }

    if (query.length < 2) {
        showAlert('Search term must be at least 2 characters long', 'warning');
        return;
    }

    // Add loading state to search button
    const searchBtn = event.target.querySelector('button[type="submit"]');
    const originalBtnHTML = searchBtn.innerHTML;
    searchBtn.innerHTML = '<div class="loading"></div>';
    searchBtn.disabled = true;

    try {
        const response = await fetch(`api/search_users.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();

        if (data.success) {
            displaySearchResults(data.users, query);
        } else {
            showAlert(data.message || 'Error searching users', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Network error occurred', 'danger');
    } finally {
        // Restore button state
        searchBtn.innerHTML = originalBtnHTML;
        searchBtn.disabled = false;
    }
}

// Enhanced Display Search Results
function displaySearchResults(users, query) {
    const searchResults = document.getElementById('searchResults');
    const searchModal = new bootstrap.Modal(document.getElementById('searchModal'));
    const modalTitle = document.querySelector('#searchModal .modal-title');

    modalTitle.textContent = `Search Results for "${query}"`;

    if (users.length === 0) {
        searchResults.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No users found</h5>
                <p class="text-muted">Try searching with different keywords</p>
            </div>
        `;
    } else {
        searchResults.innerHTML = users.map(user => `
            <div class="search-user-item d-flex align-items-center p-3" onclick="window.location.href='profile.php?id=${user.id}'" role="button">
                <img src="assets/uploads/${user.profile_picture}" 
                     alt="Profile" class="profile-img-tiny me-3"
                     onerror="this.src='assets/images/default.jpg'">
                <div class="flex-grow-1">
                    <div class="fw-bold">${user.username}</div>
                    <div class="text-muted small">${user.bio || 'No bio available'}</div>
                    <small class="badge bg-${user.user_level === 2 ? 'warning' : 'primary'}">${user.user_type}</small>
                </div>
                <i class="fas fa-chevron-right text-muted"></i>
            </div>
        `).join('');
    }

    searchModal.show();
}

// Enhanced Image Preview Function
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            showAlert('File is too large. Maximum size is 5MB.', 'warning');
            input.value = '';
            return;
        }

        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showAlert('Invalid file type. Please select a valid image.', 'warning');
            input.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.classList.add('fade-in');
            }
        };

        reader.readAsDataURL(file);
    }
}

// Enhanced Password Strength Checker
function checkPasswordStrength(password) {
    let strength = 0;
    let feedback = [];

    if (password.length >= 8) {
        strength += 1;
    } else {
        feedback.push('At least 8 characters');
    }

    if (/[A-Z]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('One uppercase letter');
    }

    if (/[a-z]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('One lowercase letter');
    }

    if (/\d/.test(password)) {
        strength += 1;
    } else {
        feedback.push('One number');
    }

    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        strength += 1;
    } else {
        feedback.push('One special character');
    }

    return {
        strength: strength,
        feedback: feedback,
        level: strength < 3 ? 'weak' : strength < 5 ? 'medium' : 'strong'
    };
}

// Update Password Strength Indicator
function updatePasswordStrength(passwordInput, strengthIndicator) {
    const password = passwordInput.value;
    const result = checkPasswordStrength(password);

    if (!strengthIndicator) return;

    strengthIndicator.className = `password-strength ${result.level}`;

    if (password.length === 0) {
        strengthIndicator.innerHTML = '';
        return;
    }

    const strengthText = result.level.charAt(0).toUpperCase() + result.level.slice(1);
    let html = `<span class="strength-level">Password Strength: ${strengthText}</span>`;

    if (result.feedback.length > 0) {
        html += `<br><small>Needs: ${result.feedback.join(', ')}</small>`;
    }

    strengthIndicator.innerHTML = html;
}

// Auto-resize textarea
function autoResizeTextarea(textarea) {
    textarea.style.height = 'auto';
    textarea.style.height = textarea.scrollHeight + 'px';
}

// Enhanced Debounce function
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

// Copy link to clipboard
async function copyToClipboard(text) {
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            document.execCommand('copy');
            textArea.remove();
        }
        showAlert('Link copied to clipboard!', 'success', 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
        showAlert('Failed to copy link', 'danger');
    }
}

// Initialize character counter for textareas
function initCharacterCounter(textareaId, counterId, maxLength) {
    const textarea = document.getElementById(textareaId);
    const counter = document.getElementById(counterId);

    if (!textarea || !counter) return;

    const updateCounter = () => {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${remaining} characters remaining`;

        counter.classList.remove('text-danger', 'text-warning', 'text-muted');

        if (remaining < 0) {
            counter.classList.add('text-danger');
        } else if (remaining < 50) {
            counter.classList.add('text-warning');
        } else {
            counter.classList.add('text-muted');
        }
    };

    textarea.addEventListener('input', updateCounter);
    updateCounter(); // Initialize
}

// Error handling for broken images
document.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG') {
        e.target.src = 'assets/images/default.jpg';
        e.target.alt = 'Default Avatar';
    }
}, true);

// Enhanced keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // Ctrl/Cmd + Shift + D for dark mode toggle
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        toggleTheme();
    }

    // Escape to close modals
    if (e.key === 'Escape') {
        const openModals = document.querySelectorAll('.modal.show');
        openModals.forEach(modal => {
            bootstrap.Modal.getInstance(modal)?.hide();
        });
    }
});

// Enhanced infinite scroll with better performance
let isLoading = false;
let currentPage = 1;
let hasMorePosts = true;

const loadMorePosts = debounce(async function() {
    if (isLoading || !hasMorePosts) return;

    isLoading = true;
    currentPage++;

    const loadingHTML = `
        <div class="text-center py-4" id="loading-indicator">
            <div class="loading"></div>
            <p class="mt-2 text-muted">Loading more posts...</p>
        </div>
    `;

    const postsContainer = document.getElementById('posts-container');
    if (postsContainer) {
        postsContainer.insertAdjacentHTML('beforeend', loadingHTML);
    }

    // Simulate API call - replace with actual implementation
    setTimeout(() => {
        const loadingIndicator = document.getElementById('loading-indicator');
        if (loadingIndicator) {
            loadingIndicator.remove();
        }
        isLoading = false;

        // Set hasMorePosts to false after certain pages to prevent infinite loading
        if (currentPage >= 5) {
            hasMorePosts = false;
        }
    }, 2000);
}, 300);

// Optimized scroll listener
let ticking = false;

function updateScrollPosition() {
    if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 1000) {
        loadMorePosts();
    }
    ticking = false;
}

window.addEventListener('scroll', function() {
    if (!ticking) {
        requestAnimationFrame(updateScrollPosition);
        ticking = true;
    }
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData) {
                console.log('Page load time:', Math.round(perfData.loadEventEnd - perfData.fetchStart), 'ms');
            }
        }, 0);
    });
}
