<?php
require_once '../includes/function.php';
require_once '../config/database.php';
require_once '../classes/User.php';

startSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: profile.php');
    exit();
}

$error_message = '';
$success_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Invalid request. Please try again.';
    } else {
        // Sanitize input
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $age = (int)sanitize($_POST['age']);
        
        // Validate input
        $errors = [];
        
        if (empty($full_name)) {
            $errors[] = 'Full name is required';
        }
        
        if (empty($email) || !validateEmail($email)) {
            $errors[] = 'Valid email is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } else {
            $password_validation = validatePassword($password);
            if (!$password_validation['valid']) {
                $errors = array_merge($errors, $password_validation['errors']);
            }
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        if ($age < 13 || $age > 120) {
            $errors[] = 'Age must be between 13 and 120';
        }
        
        if (!empty($errors)) {
            $error_message = implode('<br>', $errors);
        } else {
            // Initialize database and user class
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);
            
            // Check if email already exists
            if ($user->emailExists($email)) {
                $error_message = 'Email already exists. Please use a different email.';
            } else {
                // Handle profile picture upload
                $profile_picture = 'default.jpg';
                if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../assets/uploads/profile_pics/';
                    $upload_result = handleFileUpload($_FILES['profile_picture'], $upload_dir);
                    
                    if ($upload_result['success']) {
                        $profile_picture = $upload_result['filename'];
                    } else {
                        $error_message = $upload_result['message'];
                    }
                }
                
                if (empty($error_message)) {
                    // Set user properties
                    $user->full_name = $full_name;
                    $user->email = $email;
                    $user->password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $user->age = $age;
                    $user->profile_picture = $profile_picture;
                    
                    // Create user
                    if ($user->create()) {
                        $success_message = 'Account created successfully! You can now log in.';
                        // Clear form data
                        $_POST = [];
                    } else {
                        $error_message = 'Failed to create account. Please try again.';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Social Network</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Join Social Network</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <form id="signupForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                           required>
                    <span class="error" id="full_name_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                    <span class="error" id="email_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" min="13" max="120"
                           value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" 
                           required>
                    <span class="error" id="age_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small>Use A-Z, a-z, 0-9, !@#$%^&* in password</small>
                    <span class="error" id="password_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <span class="error" id="confirm_password_error"></span>
                </div>
                
                <div class="form-group">
                    <label for="profile_picture">Upload Profile Picture</label>
                    <input type="file" id="profile_picture" name="profile_picture" 
                           accept="image/jpeg,image/png,image/gif">
                    <span class="error" id="profile_picture_error"></span>
                </div>
                
                <button type="submit" class="btn btn-primary">Sign Up</button>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
