<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'not_logged_in']);
    exit;
}

$user_id  = $_SESSION['user_id'];
$route_id = isset($_POST['route_id']) ? (int)$_POST['route_id'] : 0;

if ($route_id <= 0) {
    echo json_encode(['error' => 'invalid_route_id']);
    exit;
}

// OPTIONAL but recommended: make sure this pair is unique
// Run once in phpMyAdmin if you haven't:
// ALTER TABLE saved_routes ADD UNIQUE KEY uniq_user_route (user_id, route_id);

$stmt = $conn->prepare("
    INSERT INTO saved_routes (user_id, route_id)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE route_id = route_id
");
$stmt->bind_param("ii", $user_id, $route_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$stmt->close();
$conn->close();