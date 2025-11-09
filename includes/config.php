<?php
// Database credentials
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kurwa_db";

// Create a new MySQLi connection
$conn = new mysqli($host, $user, $pass, $db);

// Check database connection
if ($conn->connect_error) {
  die("Database connection failed: " . $conn->connect_error);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Optional: Set default timezone (for timestamps, logs, etc.)
date_default_timezone_set('Asia/Kathmandu');
?>
