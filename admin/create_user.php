<?php
include 'db.php';

// بدء الجلسة
session_start();

// إعدادات قاعدة البيانات (يفضل نقلها إلى ملف .env أو config.php)
$host = "sql308.infinityfree.com"; 
$db_username = "if0_38628268";
$db_password = "ضع_كلمة_المرور_هنا";
$dbname = "if0_38628268_kageno";

// الاتصال بقاعدة البيانات باستخدام try-catch
try {
    $conn = new mysqli($host, $db_username, $db_password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("خطأ: " . $e->getMessage());
}

// تصحيح أخطاء PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

// تعيين المنطقة الزمنية
date_default_timezone_set('UTC');

// تعريف ثوابت الموقع
define('BASE_URL', '/');
define('ADMIN_EMAIL', 'admin@example.com');

// إنشاء الجداول إذا لم تكن موجودة
function create_database_tables($conn) {
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_notification_check TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            role ENUM('admin', 'superadmin', 'user') DEFAULT 'admin'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS submissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            gender ENUM('male', 'female') NOT NULL,
            occupation VARCHAR(100) NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            read_by VARCHAR(50) DEFAULT NULL,
            read_at TIMESTAMP DEFAULT NULL,
            submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT DEFAULT NULL,
            priority ENUM('high', 'normal', 'low') DEFAULT 'normal'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            username VARCHAR(50) NOT NULL,
            action VARCHAR(100) NOT NULL,
            action_details TEXT,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS submission_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            submission_id INT NOT NULL,
            note_text TEXT NOT NULL,
            added_by VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];

    foreach ($queries as $query) {
        if (!$conn->query($query)) {
            error_log("خطأ في إنشاء الجداول: " . $conn->error);
        }
    }

    // إنشاء المستخدم الافتراضي
    create_default_admin($conn);
}

// إنشاء المستخدم الافتراضي إذا لم يكن موجودًا
function create_default_admin($conn) {
    $username = 'kageno';
    $password = '152500'; // يفضل تغييرها لاحقًا

    $check_sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, 'superadmin')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hashed_password);
        $stmt->execute();
    }
}

// تنفيذ إنشاء الجداول
create_database_tables($conn);
?>
