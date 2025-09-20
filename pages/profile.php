<?php
require_once '../includes/function.php';
require_once __DIR__ . '/../config/database.php';
require_once '../classes/User.php';
require_once '../classes/content_Post.php';

startSession();
requireLogin();

// Initialize database and classes
$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$post = new Post($db);

// Get current user data
$user_data = $user->getUserById($_SESSION['user_id']);
if (!$user_data) {
    header('Location: logout.php');
    exit();
}

// Get user posts
$posts = $post->getPostsByUserId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Social Network</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1>Social Network</h1>
                <div class="header-actions">
                    <span>Welcome, <?php echo htmlspecialchars($user_data['full_name']); ?>!</span>
                    <a href="logout.php" class="btn btn-secondary">Logout</a>
                </div>
            </div>
        </header>

        <div class="profile-container">
            <!-- Profile Section -->
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-picture-container">
                        <img src="../assets/uploads/profile_pics/<?php echo htmlspecialchars($user_data['profile_picture']); ?>" 
                             alt="Profile Picture" class="profile-picture" id="currentProfilePic">
                        <div class="profile-picture-overlay">
                            <i class="fas fa-camera"></i>
                            <input type="file" id="profilePictureInput" accept="image/*" style="display: none;">
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h2 class="editable-field" data-field="full_name">
                            <?php echo htmlspecialchars($user_data['full_name']); ?>
                            <i class="fas fa-edit edit-icon"></i>
                        </h2>
                        <p class="email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p class="editable-field" data-field="age">
                            Age: <?php echo htmlspecialchars($user_data['age']); ?>
                            <i class="fas fa-edit edit-icon"></i>
                        </p>
                        <button class="btn btn-primary" id="shareProfileBtn">Share Profile</button>
                    </div>
                </div>
            </div>

            <!-- Add Post Section -->
            <div class="add-post-section">
                <h3>Add Post</h3>
                <form id="addPostForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <textarea id="postContent" name="content" placeholder="What's on your mind?" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="postImage" class="file-label">
                            <i class="fas fa-image"></i> Add Image
                        </label>
                        <input type="file" id="postImage" name="image" accept="image/*" style="display: none;">
                        <div id="imagePreview" class="image-preview" style="display: none;">
                            <img id="previewImg" src="" alt="Preview">
                            <button type="button" id="removeImage" class="remove-btn">&times;</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Post</button>
                </form>
            </div>

            <!-- Posts Section -->
            <div class="posts-section">
                <h3>Your Posts</h3>
                <div id="postsContainer">
                    <?php if (empty($posts)): ?>
                        <p class="no-posts">No posts yet. Share something with your network!</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post_item): ?>
                            <div class="post" data-post-id="<?php echo $post_item['id']; ?>">
                                <div class="post-header">
                                    <img src="../assets/uploads/profile_pics/<?php echo htmlspecialchars($post_item['profile_picture']); ?>" 
                                         alt="Profile" class="post-author-pic">
                                    <div class="post-author-info">
                                        <h4><?php echo htmlspecialchars($post_item['full_name']); ?></h4>
                                        <span class="post-date">Posted on <?php echo date('M d, Y', strtotime($post_item['created_at'])); ?></span>
                                    </div>
                                    <button class="delete-post-btn" data-post-id="<?php echo $post_item['id']; ?>">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                
                                <div class="post-content">
                                    <p><?php echo nl2br(htmlspecialchars($post_item['content'])); ?></p>
                                    <?php if (!empty($post_item['image'])): ?>
                                        <img src="../assets/uploads/posts/<?php echo htmlspecialchars($post_item['image']); ?>" 
                                             alt="Post Image" class="post-image">
                                    <?php endif; ?>
                                </div>
                                
                                <div class="post-actions">
                                    <button class="action-btn like-btn" data-post-id="<?php echo $post_item['id']; ?>" data-type="like">
                                        <i class="fas fa-thumbs-up"></i> 
                                        <span class="like-count"><?php echo $post_item['likes']; ?></span>
                                    </button>
                                    <button class="action-btn dislike-btn" data-post-id="<?php echo $post_item['id']; ?>" data-type="dislike">
                                        <i class="fas fa-thumbs-down"></i> 
                                        <span class="dislike-count"><?php echo $post_item['dislikes']; ?></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Edit Profile</h3>
            <form id="editProfileForm">
                <input type="hidden" id="editField" name="field">
                <div class="form-group">
                    <label id="editLabel">Field</label>
                    <input type="text" id="editValue" name="value" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" id="cancelEdit">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
