<?php
/**
 * Helper functions for the social network application
 */

/**
 * Start session if not already started
 */
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Check if user is logged in
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Redirect to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Sanitize input data
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Validate email format
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 * @param string $password Password to validate
 * @return array Validation result and message
 */
function validatePassword($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number";
    }
    
    if (!preg_match('/[!@#$%^&*]/', $password)) {
        $errors[] = "Password must contain at least one special character (!@#$%^&*)";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Handle file upload for profile pictures
 * @param array $file $_FILES array for the uploaded file
 * @param string $upload_dir Upload directory path
 * @return array Upload result
 */
function handleFileUpload($file, $upload_dir) {
    $result = ['success' => false, 'message' => '', 'filename' => ''];
    
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'No file uploaded or upload error';
        return $result;
    }


    // Validate file type properly
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

$detected_type = mime_content_type($file['tmp_name']);
if (!in_array($detected_type, $allowed_types)) {
    $result['message'] = 'Please select a valid image file (JPG, PNG, GIF)';
    return $result;
}


    // Validate MIME type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $detected_type = mime_content_type($file['tmp_name']);
    if (!in_array($detected_type, $allowed_types)) {
        $result['message'] = 'Please select a valid image file (JPG, PNG, GIF)';
        return $result;
    }

    // Validate extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($extension, $allowed_exts)) {
        $result['message'] = 'Invalid file extension';
        return $result;
    }

    // Validate size
    if ($file['size'] >= 5 * 1024 * 1024) {
        $result['message'] = 'File size must be less than or equal 5MB';
        return $result;
    }

    // Generate unique name and save
    $filename = uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $result['success'] = true;
        $result['filename'] = $filename;
        $result['message'] = 'File uploaded successfully';
    } else {
        $result['message'] = 'Failed to upload file';
    }

    return $result;
}


/**
 * Format time ago
 * @param string $datetime Datetime string
 * @return string Formatted time ago
 */
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M d, Y', strtotime($datetime));
}

/**
 * Generate CSRF token
 * @return string CSRF token
 */
function generateCSRFToken() {
    startSession();
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid, false otherwise
 */
function verifyCSRFToken($token) {
    startSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
