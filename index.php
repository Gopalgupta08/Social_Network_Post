<?php
require_once 'includes/function.php';

startSession();

// Redirect based on login status
if (isLoggedIn()) {
    header('Location: pages/profile.php');
} else {
    header('Location: pages/login.php');
}
exit();
?>
