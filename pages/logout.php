<?php
require_once '../includes/function.php';

startSession();

// Destroy session and redirect to login
session_destroy();
header('Location: login.php');
exit();
?>
