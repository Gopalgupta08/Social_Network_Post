<?php
require_once '../includes/function.php';
require_once '../config/database.php';
require_once '../classes/User.php';

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

// Initialize database and user class
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Handle profile picture update
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../assets/uploads/profile_pics/';
    $upload_result = handleFileUpload($_FILES['profile_picture'], $upload_dir);
    
    if ($upload_result['success']) {
        // Get current user data
        $current_user = $user->getUserById($_SESSION['user_id']);
        
        // Set user properties
        $user->id = $_SESSION['user_id'];
        $user->full_name = $current_user['full_name'];
        $user->age = $current_user['age'];
        $user->profile_picture = $upload_result['filename'];
        
        if ($user->updateProfile()) {
            echo json_encode([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'filename' => $upload_result['filename']
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile picture']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => $upload_result['message']]);
    }
    exit();
}

// Handle text field updates
$field = sanitize($_POST['field'] ?? '');
$value = sanitize($_POST['value'] ?? '');

if (!in_array($field, ['full_name', 'age']) || empty($value)) {
    echo json_encode(['success' => false, 'message' => 'Invalid field or value']);
    exit();
}

// Additional validation
if ($field === 'age') {
    $value = (int)$value;
    if ($value < 13 || $value > 120) {
        echo json_encode(['success' => false, 'message' => 'Age must be between 13 and 120']);
        exit();
    }
}

// Get current user data
$current_user = $user->getUserById($_SESSION['user_id']);

// Set user properties
$user->id = $_SESSION['user_id'];
$user->full_name = ($field === 'full_name') ? $value : $current_user['full_name'];
$user->age = ($field === 'age') ? $value : $current_user['age'];
$user->profile_picture = $current_user['profile_picture'];

// Update profile
if ($user->updateProfile()) {
    // Update session if name changed
    if ($field === 'full_name') {
        $_SESSION['user_name'] = $value;
    }
    
    echo json_encode([
        'success' => true,
        'message' => ucfirst($field) . ' updated successfully',
        'value' => $value
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
}
?>
