<?php
require_once 'con/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'การตรวจสอบความปลอดภัยล้มเหลว';
    } else {
        $new_username = trim($_POST['username'] ?? '');
        $new_photo = trim($_POST['photo_url'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        $payload = [
            'action' => 'update_profile',
            'user_id' => $_SESSION['user_id'],
            'username' => $new_username,
            'photo_url' => $new_photo,
            'current_password' => $current_password,
            'new_password' => $new_password
        ];

        $response = post_to_gas($payload);

        if (isset($response['status']) && $response['status'] === 'success') {
            $_SESSION['username'] = $new_username;
            $_SESSION['photo_url'] = $new_photo;
            $message = 'อัปเดตโปรไฟล์เรียบร้อยแล้ว';
        } else {
            $error = $response['message'] ?? 'ไม่สามารถอัปเดตโปรไฟล์ได้';
        }
    }
}

require_once 'con/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="mb-4">จัดการโปรไฟล์ส่วนตัว</h3>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-success"><?= escape($message) ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= escape($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="profile.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3">
                        <label class="form-label">รหัสประจำตัว</label>
                        <input type="text" class="form-control" value="<?= escape($_SESSION['user_id']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">บทบาท</label>
                        <input type="text" class="form-control" value="<?= escape($_SESSION['role']) ?>" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= escape($_SESSION['username']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="photo_url" class="form-label">ลิงก์รูปโปรไฟล์ (Photo URL)</label>
                        <input type="text" class="form-control" id="photo_url" name="photo_url" value="<?= escape($_SESSION['photo_url'] ?? '') ?>">
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">เปลี่ยนรหัสผ่าน (หากไม่ต้องการเปลี่ยน ให้เว้นว่างไว้)</h5>

                    <div class="mb-3">
                        <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>

                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                    <a href="dashboard.php" class="btn btn-secondary">กลับหน้าหลัก</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'con/footer.php'; ?>
