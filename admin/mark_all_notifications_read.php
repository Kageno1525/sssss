<?php
include 'db.php';

// Start session
session_start();

// Load environment variables
require_once 'config.php';

// Connect to MySQL database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
    $users_table = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_notification_check TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        role VARCHAR(20) DEFAULT 'admin'
    )";
    
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
    
    $submission_notes_table = "
    CREATE TABLE IF NOT EXISTS submission_notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        submission_id INT NOT NULL,
        note_text TEXT NOT NULL,
        added_by VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
    )";
    
    $conn->query($users_table);
    $conn->query($submissions_table);
    $conn->query($activity_log_table);
    $conn->query($submission_notes_table);
    
    create_default_admin($conn);
}

// Create default admin user if not exists
function create_default_admin($conn) {
    $username = 'kageno';
    $password = '152500';
    
    $check_sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
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

// Mark all unread submissions as read
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $sql = "UPDATE submissions SET is_read = TRUE, read_by = ?, read_at = NOW() WHERE is_read = FALSE";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $success = $stmt->execute();
    
    // Log activity
    if ($success) {
        $log_sql = "INSERT INTO activity_log (username, action, action_details, created_at) VALUES (?, 'تأشير جميع الإشعارات كمقروءة', 'تم تأشير جميع الإشعارات كمقروءة', NOW())";
        $log_stmt = $conn->prepare($log_sql);
        $log_stmt->bind_param("s", $username);
        $log_stmt->execute();
    }
    
    // Return JSON response
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'تم تأشير جميع الإشعارات كمقروءة بنجاح' : 'حدث خطأ أثناء تأشير الإشعارات كمقروءة'
    ]);
}
