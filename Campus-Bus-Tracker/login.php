<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header('Location: index.php?error=invalid');
    exit;
}

$sql = "SELECT id, full_name, email, password_hash 
        FROM users 
        WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    if (password_verify($password, $row['password_hash'])) {
        // ✅ success – store in session
        $_SESSION['user_id']    = $row['id'];
        $_SESSION['user_name']  = $row['full_name'];
        $_SESSION['user_email'] = $row['email'];

        header('Location: bus-navigation-app.php');
        exit;
    }
}

// ❌ invalid login
header('Location: index.php?error=invalid');
exit;
