<?php
include 'db.php';

$sql = "SELECT * FROM users";
$result = $conn->query($sql);

echo "<h2>قائمة المستخدمين</h2>";
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - الاسم: " . $row["name"]. " - البريد الإلكتروني: " . $row["email"]. "<br>";
    }
} else {
    echo "لا يوجد مستخدمون!";
}

$conn->close();
?>
