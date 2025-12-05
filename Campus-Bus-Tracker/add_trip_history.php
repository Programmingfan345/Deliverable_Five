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
$notes    = isset($_POST['notes']) ? trim($_POST['notes']) : null;

if ($route_id <= 0) {
    echo json_encode(['error' => 'invalid_route_id']);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO trip_history (user_id, route_id, notes)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iis", $user_id, $route_id, $notes);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $conn->error]);
}

$stmt->close();
$conn->close();