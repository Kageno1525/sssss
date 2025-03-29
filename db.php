<?php
$servername = "sql308.infinityfree.com"; 
$username = "if0_38628268"; 
$password = "vishnusubraa15"; 
$dbname = "if0_38628268_kageno"; 

// الاتصال بقاعدة البيانات MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("❌ فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تم الاتصال بنجاح
echo "✅ تم الاتصال بقاعدة البيانات بنجاح!";
?>
