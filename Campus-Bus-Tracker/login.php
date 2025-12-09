<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        // missing fields
        header('Location: index.php?error=invalid');
        exit;
    }

    $stmt = $conn->prepare("
        SELECT user_id, full_name, email, password, role
        FROM users
        WHERE email = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // PLAIN-TEXT comparison (school project only)
        if ($password === $row['password']) {
            // correct credentials – store user in session
            $_SESSION['user_id']   = $row['user_id'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['email']     = $row['email'];
            $_SESSION['role']      = $row['role'];

            header('Location: bus-navigation-app.php');
            exit;
        }
    }

    // if we reach here, email or password was wrong
    header('Location: index.php?error=invalid');
    exit;
}

// if someone opens login.php directly, just send them to login screen
header('Location: index.php');
exit;
