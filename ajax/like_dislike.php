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
$type = sanitize($_POST['type'] ?? '');

if ($post_id <= 0 || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit();
}

// Initialize database and post class
$database = new Database();
$db = $database->getConnection();
$post = new Post($db);

// Update like/dislike
if ($post->updateLikeDislike($post_id, $_SESSION['user_id'], $type)) {
    // Get updated counts
    $counts = $post->getLikeCounts($post_id);
    
    echo json_encode([
        'success' => true,
        'message' => 'Updated successfully',
        'likes' => $counts['likes'],
        'dislikes' => $counts['dislikes']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}
?>
