<?php
// db.php — MySQL connection for Docker setup

$host   = "db";           // 👈 service name from docker-compose.yml
$port   = 3306;          // default MySQL port inside the Docker network
$user   = "transit_user"; // from docker-compose.yml
$pass   = "secret123";    // from docker-compose.yml
$dbname = "transit_db";   // from docker-compose.yml

$conn = new mysqli($host, $user, $pass, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
