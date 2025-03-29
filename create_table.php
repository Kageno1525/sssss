<?php
include 'db.php'; // استدعاء الاتصال بقاعدة البيانات

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "تم إنشاء الجدول بنجاح!";
} else {
    echo "خطأ في إنشاء الجدول: " . $conn->error;
}

$conn->close();
?>
<?php
include 'db.php'; // استدعاء الاتصال بقاعدة البيانات

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "تم إنشاء الجدول بنجاح!";
} else {
    echo "خطأ في إنشاء الجدول: " . $conn->error;
}

$conn->close();
?>
