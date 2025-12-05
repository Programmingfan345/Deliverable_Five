<?php
session_start();
require 'db.php';

$email    = $_POST['email']    ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo "Please enter both email and password.";
    exit;
}

// Look up user by email
$stmt = $conn->prepare("SELECT user_id, full_name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Invalid email or password.";
    exit;
}

$user = $result->fetch_assoc();

// For this project we compare plain text (password = '1234')
if ($password !== $user['password']) {
    echo "Invalid email or password.";
    exit;
}

// Store session data
$_SESSION['user_id']   = $user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email']     = $user['email'];

// Redirect back to the main app page
header("Location: index.php");  // or index.php if you rename later
exit;
?>

