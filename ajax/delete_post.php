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
$post_id = (int)($_POST['post_id'] ?? 0);
if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit();
}

// Initialize database and post class
$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

// Delete post
if ($post->delete($post_id, $_SESSION['user_id'])) {
    echo json_encode(['success' => true, 'message' => 'Post deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete post']);
}
?>
