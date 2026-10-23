<?php
require_once 'con/config.php';
require_once 'con/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? '';

// โหลดหน้าตา UI แยกตามบทบาทจากโฟลเดอร์ tem/
if ($role === 'ผู้อำนวยการ') {
    include 'tem/director_view.php';
} elseif ($role === 'ผู้ถูกปกป้อง') {
    include 'tem/protector_view.php';
} else {
    include 'tem/friend_view.php';
}

require_once 'con/footer.php';
?>
