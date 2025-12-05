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
    th.trip_date,
    r.route_name,
    CONCAT(COALESCE(r.start_point, 'Unknown'), ' → ', COALESCE(r.end_point, 'Unknown')) AS description,
    CONCAT(COALESCE(r.estimated_time, 0), ' minutes') AS duration
FROM trip_history th
JOIN routes r ON th.route_id = r.route_id
WHERE th.user_id = ?
ORDER BY th.trip_date DESC
LIMIT 10
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while ($row = $result->fetch_assoc()) {
    $row['time_label'] = date("M j, g:i A", strtotime($row['trip_date']));
    $trips[] = $row;
}

echo json_encode($trips);

$stmt->close();
$conn->close();