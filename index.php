<?php
echo "<h1>กำลังทดสอบเชื่อมต่อฐานข้อมูล MySQL...</h1>";

$host = "ใส่_Host_ของ_MySQL_ที่นี่";
$port = "3306";
$dbname = "ใส่_ชื่อฐานข้อมูล_ที่นี่";
$user = "ใส่_Username_ที่นี่";
$password = "ใส่_Password_ที่นี่";

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<p style='color: green; font-size: 20px;'><b>✅ เย้! เชื่อมต่อฐานข้อมูล MySQL สำเร็จแล้ว!</b></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'><b>❌ เชื่อมต่อไม่สำเร็จ:</b> " . $e->getMessage() . "</p>";
}
?>
