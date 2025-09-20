/**
 * Social Network JavaScript Functions
 * Handles AJAX requests, form validation, and UI interactions
 */

$(document).ready(function() {
    
    // Form validation for signup
    $('#signupForm').on('submit', function(e) {
        if (!validateSignupForm()) {
            e.preventDefault();
        }
    });
    
    // Form validation for login
    $('#loginForm').on('submit', function(e) {
        if (!validateLoginForm()) {
            e.preventDefault();
        }
    });
    
    // Profile picture upload
    $('#profilePictureInput').on('change', function() {
        const file = this.files;
        if (file) {
            uploadProfilePicture(file);
        }
    });
    
    // Profile picture click to upload
    $('.profile-picture-overlay').on('click', function() {
        $('#profilePictureInput').click();
    });
    
    // Add post form submission
    $('#addPostForm').on('submit', function(e) {
        e.preventDefault();
        addPost();
    });
    
    // Post image preview
    $('#postImage').on('change', function() {
        previewPostImage(this);
    });
    
    // Remove image preview
    $('#removeImage').on('click', function() {
        removeImagePreview();
    });
    
    // Like/Dislike buttons
    $(document).on('click', '.like-btn, .dislike-btn', function() {
        const postId = $(this).data('post-id');
        const type = $(this).data('type');
        updateLikeDislike(postId, type, $(this));
    });
    
    // Delete post
    $(document).on('click', '.delete-post-btn', function() {
        const postId = $(this).data('post-id');
        if (confirm('Are you sure you want to delete this post?')) {
            deletePost(postId, $(this).closest('.post'));
        }
    });
    
    // Edit profile fields
    $('.editable-field').on('click', function() {
        const field = $(this).data('field');
        const currentValue = $(this).clone().children().remove().end().text().trim();
        showEditModal(field, currentValue);
    });
    
    // Edit profile form
    $('#editProfileForm').on('submit', function(e) {
        e.preventDefault();
        updateProfileField();
    });
    
    // Modal close
    $('.close, #cancelEdit').on('click', function() {
        $('#editModal').hide();
    });
    
    // Share profile button
    $('#shareProfileBtn').on('click', function() {
        shareProfile();
    });
    
    // File input label interaction
    $('.file-label').on('click', function() {
        $(this).siblings('input[type="file"]').click();
    });
});

/**
 * Validate signup form
 * @returns {boolean} True if valid, false otherwise
 */
function validateSignupForm() {
    let isValid = true;
    clearErrors();
    
    // Full name validation
    const fullName = $('#full_name').val().trim();
    if (fullName.length < 2) {
        showError('full_name_error', 'Full name must be at least 2 characters');
        isValid = false;
    }
    
    // Email validation
    const email = $('#email').val().trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showError('email_error', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Age validation
    const age = parseInt($('#age').val());
    if (age < 13 || age > 120 || isNaN(age)) {
        showError('age_error', 'Age must be between 13 and 120');
        isValid = false;
    }
    
    // Password validation
    const password = $('#password').val();
    if (!validatePassword(password)) {
        showError('password_error', 'Password must contain: A-Z, a-z, 0-9, !@#$%^&* (min 8 chars)');
        isValid = false;
    }
    
    // Confirm password validation
    const confirmPassword = $('#confirm_password').val();
    if (password !== confirmPassword) {
        showError('confirm_password_error', 'Passwords do not match');
        isValid = false;
    }
    
    // Profile picture validation
    const profilePic = $('#profile_picture').files;
    if (profilePic) {
        if (!validateImageFile(profilePic)) {
            showError('profile_picture_error', 'Please select a valid image file (JPG, PNG, GIF) under 5MB');
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Validate login form
 * @returns {boolean} True if valid, false otherwise
 */
function validateLoginForm() {
    let isValid = true;
    
    const email = $('#email').val().trim();
    const password = $('#password').val();
    
    if (!email || !password) {
        showNotification('Please fill in all fields', 'error');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate password strength
 * @param {string} password Password to validate
 * @returns {boolean} True if valid, false otherwise
 */
function validatePassword(password) {
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*]/.test(password);
    const hasLength = password.length >= 8;
    
    return hasUpper && hasLower && hasNumber && hasSpecial && hasLength;
}

/**
 * Validate image file
 * @param {File} file File to validate
 * @returns {boolean} True if valid, false otherwise
 */
function validateImageFile(file) {
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    return allowedTypes.includes(file.type) && file.size <= maxSize;
}

/**
 * Show error message
 * @param {string} elementId Error element ID
 * @param {string} message Error message
 */
function showError(elementId, message) {
    $('#' + elementId).text(message).show();
}

/**
 * Clear all error messages
 */
function clearErrors() {
    $('.error').text('').hide();
}

/**
 * Upload profile picture via AJAX
 * @param {File} file File to upload
 */
function uploadProfilePicture(file) {
    if (!validateImageFile(file)) {
        showNotification('Please select a valid image file (JPG, PNG, GIF) under 5MB', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('profile_picture', file);
    
    $.ajax({
        url: '../ajax/update_profile.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            showLoadingOverlay();
        },
        success: function(response) {
            if (response.success) {
                $('#currentProfilePic').attr('src', '../assets/uploads/profile_pics/' + response.filename);
                showNotification(response.message, 'success');
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Failed to upload profile picture', 'error');
        },
        complete: function() {
            hideLoadingOverlay();
        }
    });
}

/**
 * Add new post via AJAX
 */
function addPost() {
    const content = $('#postContent').val().trim();
    
    if (!content) {
        showNotification('Please enter post content', 'error');
        return;
    }
    
    const formData = new FormData();
    formData.append('content', content);
    
    const imageFile = $('#postImage').files;
    if (imageFile) {
        if (!validateImageFile(imageFile)) {
            showNotification('Please select a valid image file', 'error');
            return;
        }
        formData.append('image', imageFile);
    }
    
    $.ajax({
        url: '../ajax/add_post.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        beforeSend: function() {
            showLoadingOverlay();
        },
        success: function(response) {
            if (response.success) {
                // Clear form
                $('#postContent').val('');
                removeImagePreview();
                
                // Add post to the beginning of posts container
                const postHtml = createPostHtml(response.post);
                if ($('#postsContainer .no-posts').length) {
                    $('#postsContainer').html(postHtml);
                } else {
                    $('#postsContainer').prepend(postHtml);
                }
                
                showNotification(response.message, 'success');
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Failed to add post', 'error');
        },
        complete: function() {
            hideLoadingOverlay();
        }
    });
}

/**
 * Create HTML for a post
 * @param {Object} post Post data
 * @returns {string} HTML string
 */
function createPostHtml(post) {
    const postDate = new Date(post.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    const imageHtml = post.image ? 
        `<img src="../assets/uploads/posts/${post.image}" alt="Post Image" class="post-image">` : '';
    
    return `
        <div class="post" data-post-id="${post.id}">
            <div class="post-header">
                <img src="../assets/uploads/profile_pics/${post.profile_picture}" 
                     alt="Profile" class="post-author-pic">
                <div class="post-author-info">
                    <h4>${escapeHtml(post.full_name)}</h4>
                    <span class="post-date">Posted on ${postDate}</span>
                </div>
                <button class="delete-post-btn" data-post-id="${post.id}">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="post-content">
                <p>${escapeHtml(post.content).replace(/\n/g, '<br>')}</p>
                ${imageHtml}
            </div>
            
            <div class="post-actions">
                <button class="action-btn like-btn" data-post-id="${post.id}" data-type="like">
                    <i class="fas fa-thumbs-up"></i> 
                    <span class="like-count">${post.likes || 0}</span>
                </button>
                <button class="action-btn dislike-btn" data-post-id="${post.id}" data-type="dislike">
                    <i class="fas fa-thumbs-down"></i> 
                    <span class="dislike-count">${post.dislikes || 0}</span>
                </button>
            </div>
        </div>
    `;
}

/**
 * Preview post image
 * @param {HTMLInputElement} input File input element
 */
function previewPostImage(input) {
    const file = input.files;
    if (file && validateImageFile(file)) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#previewImg').attr('src', e.target.result);
            $('#imagePreview').show();
        };
        reader.readAsDataURL(file);
    } else {
        showNotification('Please select a valid image file', 'error');
        input.value = '';
    }
}

/**
 * Remove image preview
 */
function removeImagePreview() {
    $('#postImage').val('');
    $('#imagePreview').hide();
    $('#previewImg').attr('src', '');
}

/**
 * Update like/dislike via AJAX
 * @param {number} postId Post ID
 * @param {string} type 'like' or 'dislike'
 * @param {jQuery} button Button element
 */
function updateLikeDislike(postId, type, button) {
    $.ajax({
        url: '../ajax/like_dislike.php',
        type: 'POST',
        data: {
            post_id: postId,
            type: type
        },
        beforeSend: function() {
            button.prop('disabled', true);
        },
        success: function(response) {
            if (response.success) {
                // Update counts
                button.closest('.post-actions').find('.like-count').text(response.likes);
                button.closest('.post-actions').find('.dislike-count').text(response.dislikes);
                
                // Visual feedback
                button.addClass('active').siblings('.action-btn').removeClass('active');
                setTimeout(() => button.removeClass('active'), 300);
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Failed to update reaction', 'error');
        },
        complete: function() {
            button.prop('disabled', false);
        }
    });
}

/**
 * Delete post via AJAX
 * @param {number} postId Post ID
 * @param {jQuery} postElement Post element
 */
function deletePost(postId, postElement) {
    $.ajax({
        url: '../ajax/delete_post.php',
        type: 'POST',
        data: {
            post_id: postId
        },
        beforeSend: function() {
            showLoadingOverlay();
        },
        success: function(response) {
            if (response.success) {
                postElement.fadeOut(300, function() {
                    $(this).remove();
                    
                    // Show no posts message if no posts left
                    if ($('#postsContainer .post').length === 0) {
                        $('#postsContainer').html('<p class="no-posts">No posts yet. Share something with your network!</p>');
                    }
                });
                showNotification(response.message, 'success');
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Failed to delete post', 'error');
        },
        complete: function() {
            hideLoadingOverlay();
        }
    });
}

/**
 * Show edit modal
 * @param {string} field Field name
 * @param {string} currentValue Current value
 */
function showEditModal(field, currentValue) {
    // Clean current value (remove "Age: " prefix if present)
    if (field === 'age' && currentValue.startsWith('Age: ')) {
        currentValue = currentValue.replace('Age: ', '');
    }
    
    $('#editField').val(field);
    $('#editValue').val(currentValue);
    
    // Set label
    const fieldLabels = {
        'full_name': 'Full Name',
        'age': 'Age'
    };
    $('#editLabel').text(fieldLabels[field] || field);
    
    // Set input type
    if (field === 'age') {
        $('#editValue').attr('type', 'number').attr('min', '13').attr('max', '120');
    } else {
        $('#editValue').attr('type', 'text').removeAttr('min').removeAttr('max');
    }
    
    $('#editModal').show();
    $('#editValue').focus();
}

/**
 * Update profile field via AJAX
 */
function updateProfileField() {
    const field = $('#editField').val();
    const value = $('#editValue').val().trim();
    
    if (!value) {
        showNotification('Please enter a value', 'error');
        return;
    }
    
    // Additional validation for age
    if (field === 'age') {
        const age = parseInt(value);
        if (age < 13 || age > 120 || isNaN(age)) {
            showNotification('Age must be between 13 and 120', 'error');
            return;
        }
    }
    
    $.ajax({
        url: '../ajax/update_profile.php',
        type: 'POST',
        data: {
            field: field,
            value: value
        },
        beforeSend: function() {
            showLoadingOverlay();
        },
        success: function(response) {
            if (response.success) {
                // Update display value
                const displayValue = field === 'age' ? 'Age: ' + value : value;
                $(`.editable-field[data-field="${field}"]`).contents().first().replaceWith(displayValue + ' ');
                
                $('#editModal').hide();
                showNotification(response.message, 'success');
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function() {
            showNotification('Failed to update profile', 'error');
        },
        complete: function() {
            hideLoadingOverlay();
        }
    });
}

/**
 * Share profile functionality
 */
function shareProfile() {
    const profileUrl = window.location.href;
    
    if (navigator.share) {
        navigator.share({
            title: 'Check out my profile',
            text: 'View my profile on Social Network',
            url: profileUrl
        }).catch(console.error);
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(profileUrl).then(function() {
            showNotification('Profile link copied to clipboard!', 'success');
        }).catch(function() {
            showNotification('Unable to copy link', 'error');
        });
    }
}

/**
 * Show notification
 * @param {string} message Notification message
 * @param {string} type 'success' or 'error'
 */
function showNotification(message, type) {
    // Remove existing notifications
    $('.notification').remove();
    
    const notification = $(`
        <div class="notification ${type}">
            ${escapeHtml(message)}
            <button class="notification-close">&times;</button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
    
    // Manual close
    notification.find('.notification-close').on('click', function() {
        notification.fadeOut(300, function() {
            $(this).remove();
        });
    });
}

/**
 * Show loading overlay
 */
function showLoadingOverlay() {
    if (!$('.loading-overlay').length) {
        $('body').append(`
            <div class="loading-overlay">
                <div class="spinner"></div>
            </div>
        `);
    }
}

/**
 * Hide loading overlay
 */
function hideLoadingOverlay() {
    $('.loading-overlay').fadeOut(200, function() {
        $(this).remove();
    });
}

/**
 * Escape HTML to prevent XSS
 * @param {string} text Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Add notification and loading overlay styles
const additionalStyles = `
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            z-index: 1001;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
            max-width: 300px;
        }
        
        .notification.success {
            background-color: #28a745;
        }
        
        .notification.error {
            background-color: #dc3545;
        }
        
        .notification-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: auto;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1002;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
`;

$(document).ready(function() {
    $('head').append(additionalStyles);
});
