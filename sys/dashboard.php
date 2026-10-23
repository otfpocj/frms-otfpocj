<?php
require_once 'con/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'con/header.php';

$role = $_SESSION['role'] ?? '';
?>

<div class="mb-4">
    <h2>ยินดีต้อนรับ, <?= escape($_SESSION['username']) ?></h2>
    <p class="text-muted">บทบาทปัจจุบันของคุณ: <strong><?= escape($role) ?></strong></p>
</div>

<!-- ส่วนสำหรับเรียกไฟล์เนื้อหาตามบทบาทจากโฟลเดอร์ tem/ ในขั้นตอนถัดไป -->
<div class="card shadow-sm">
    <div class="card-body">
        <?php
        if ($role === 'ผู้อำนวยการ' && file_exists('tem/director_view.php')) {
            include 'tem/director_view.php';
        } elseif ($role === 'ผู้ถูกปกป้อง' && file_exists('tem/protector_view.php')) {
            include 'tem/protector_view.php';
        } elseif (file_exists('tem/friend_view.php')) {
            include 'tem/friend_view.php';
        } else {
            echo '<div class="alert alert-info mb-0">โครงสร้างไฟล์ในโฟลเดอร์ <code>tem/</code> กำลังจะถูกสร้างขึ้นในขั้นตอนถัดไปตามที่คุณผู้ใช้กำหนดครับ</div>';
        }
        ?>
    </div>
</div>

<?php require_once 'con/footer.php'; ?>
