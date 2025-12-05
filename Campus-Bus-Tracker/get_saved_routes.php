<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
SELECT 
    r.route_id,
    r.route_name AS name,
    CONCAT(COALESCE(r.start_point, 'Unknown'), ' → ', COALESCE(r.end_point, 'Unknown')) AS description,
    CONCAT('Avg ', COALESCE(r.estimated_time, 0), ' min') AS stats
FROM saved_routes sr
JOIN routes r ON sr.route_id = r.route_id
WHERE sr.user_id = ?
ORDER BY sr.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$routes = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($routes);

$stmt->close();
$conn->close();