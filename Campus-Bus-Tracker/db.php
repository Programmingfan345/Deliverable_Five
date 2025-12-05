<?php
// db.php — MySQL connection

$host   = "127.0.0.1";      // Docker is exposing MySQL on localhost:3306
$port = 3307;
$user   = "transit_user";   // from docker-compose.yml
$pass   = "secret123";      // from docker-compose.yml
$dbname = "transit_db";     // from docker-compose.yml

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
