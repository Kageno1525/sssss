<?php
include 'db.php';

// Start session
session_start();

// Database configuration
$host = "sql308.infinityfree.com"; // استبدل بالقيم الفعلية من InfinityFree
$username = "if0_38628268";
$password = "ضع_كلمة_المرور_هنا";
$dbname = "if0_38628268_kageno";

// Connect to MySQL database
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debug connection
// echo "Connected successfully to MySQL";

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set default timezone
date_default_timezone_set('UTC');

// Define constants
define('BASE_URL', '/');
define('ADMIN_EMAIL', 'admin@example.com');
