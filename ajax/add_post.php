<?php
require_once '../includes/function.php';
require_once '../config/database.php';
require_once '../classes/content_Post.php';

startSession();
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Validate input
$content = sanitize($_POST['content'] ?? '');
if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Post content is required']);
    exit();
}

// Initialize database and post class
$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

// Handle image upload if provided
$image_filename = null;
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/uploads/posts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $upload_result = handleFileUpload($_FILES['image'], $upload_dir);
    if ($upload_result['success']) {
        $image_filename = $upload_result['filename'];
    } else {
        echo json_encode(['success' => false, 'message' => $upload_result['message']]);
        exit();
    }
}

// Set post properties
$post->user_id = $_SESSION['user_id'];
$post->content = $content;
$post->image = $image_filename;

// Create post
if ($post->create()) {
    // Get the created post with user info
    $posts = $post->getPostsByUserId($_SESSION['user_id']);
    $latest_post = $posts; // First post is the latest
    
    echo json_encode([
        'success' => true,
        'message' => 'Post created successfully',
        'post' => $latest_post
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create post']);
}
?>
