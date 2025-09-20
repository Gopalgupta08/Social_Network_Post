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

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Invalid request. Please try again.';
    } else {
        $email = sanitize($_POST['email']);
        $password = $_POST['password'];
        
        if (empty($email) || empty($password)) {
            $error_message = 'Please fill in all fields.';
        } else {
            // Initialize database and user class
            $database = new Database();
            $db = $database->getConnection();
            $user = new User($db);
            
            // Attempt login
            $user_data = $user->login($email, $password);
            
            if ($user_data) {
                // Set session variables
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['user_name'] = $user_data['full_name'];
                $_SESSION['user_email'] = $user_data['email'];
                
                // Redirect to profile
                header('Location: profile.php');
                exit();
            } else {
                $error_message = 'Invalid email or password.';
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
    <title>Login - Social Network</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Social Network Login</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <form id="loginForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="signup.php">Create Account</a></p>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
