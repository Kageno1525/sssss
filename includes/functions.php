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

// Create tables if they don't exist
function create_database_tables($conn) {
    // Create users table
    $users_table = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_notification_check TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        role VARCHAR(20) DEFAULT 'admin'
    )";
    
    // Create submissions table
    $submissions_table = "
    CREATE TABLE IF NOT EXISTS submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        gender VARCHAR(10) NOT NULL,
        occupation VARCHAR(100) NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        read_by VARCHAR(50) DEFAULT NULL,
        read_at TIMESTAMP DEFAULT NULL,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        notes TEXT DEFAULT NULL,
        priority VARCHAR(20) DEFAULT 'normal'
    )";
    
    // Create activity log table
    $activity_log_table = "
    CREATE TABLE IF NOT EXISTS activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        username VARCHAR(50) NOT NULL,
        action VARCHAR(100) NOT NULL,
        action_details TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )";
    
    // Create submission notes table
    $submission_notes_table = "
    CREATE TABLE IF NOT EXISTS submission_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        submission_id INT NOT NULL,
        note_text TEXT NOT NULL,
        added_by VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
    )";
    
    // Execute the SQL statements
    $conn->query($users_table);
    $conn->query($submissions_table);
    $conn->query($activity_log_table);
    $conn->query($submission_notes_table);
    
    // Create default admin user (username: kageno, password: 152500)
    create_default_admin($conn);
}

// Create default admin user if not exists
function create_default_admin($conn) {
    $username = 'kageno';
    $password = '152500';
    
    // Check if default admin exists
    $check_sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // If admin doesn't exist, create it
    if ($result->num_rows == 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
    }
}

// Initialize database
create_database_tables($conn);
