<?php
// Database Configuration

$host = "localhost";
$username = "root";
$password = "";
$database = "examshield";

// Create Database Connection
$conn = new mysqli($host, $username, $password, $database);

// Check Connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Optional: Set Character Encoding
$conn->set_charset("utf8mb4");
?>