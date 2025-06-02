<?php
session_start(); // Start the session

// Check if a session is active before destroying it
if (session_status() === PHP_SESSION_ACTIVE) {
    session_unset();  // Unset all session variables
    session_destroy(); // Destroy the session
}

// Redirect to the login page (change as needed)
header("Location: index.php");
exit();
?>