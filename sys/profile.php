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
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        
        $image_base64 = '';
        $image_name = '';
        $image_mime = '';

        // ตรวจสอบว่ามีการอัปโหลดไฟล์รูปภาพมาด้วยหรือไม่
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['profile_image']['tmp_name'];
            $image_name = $_FILES['profile_image']['name'];
            $image_mime = mime_content_type($file_tmp);
            
            // ตรวจสอบนามสกุลไฟล์เบื้องต้น
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (in_array($image_mime, $allowed_types)) {
                $image_base64 = base64_encode(file_get_contents($file_tmp));
            } else {
                $error = 'รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, GIF, WEBP) เท่านั้น';
            }
        }

        if (empty($error)) {
            $payload = [
                'action' => 'update_profile',
                'user_id' => $_SESSION['user_id'],
                'username' => $new_username,
                'current_password' => $current_password,
                'new_password' => $new_password,
                'image_base64' => $image_base64,
                'image_name' => $image_name,
                'image_mime' => $image_mime
            ];

            $response = post_to_gas($payload);

            if (isset($response['status']) && $response['status'] === 'success') {
                $_SESSION['username'] = $new_username;
                if (!empty($response['photo_url'])) {
                    $_SESSION['photo_url'] = $response['photo_url'];
                }
                $message = 'อัปเดตโปรไฟล์และรูปภาพเรียบร้อยแล้ว';
            } else {
                $error = $response['message'] ?? 'ไม่สามารถอัปเดตโปรไฟล์ได้';
            }
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

                <!-- เพิ่ม enctype="multipart/form-data" เพื่อรองรับการอัปโหลดไฟล์ -->
                <form method="POST" action="profile.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-3 text-center">
                        <?php if (!empty($_SESSION['photo_url'])): ?>
                            <img src="<?= escape($_SESSION['photo_url']) ?>" alt="Profile Image" class="rounded-circle mb-2" width="100" height="100" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width: 100px; height: 100px; font-size: 2rem;">
                                <?= mb_substr(escape($_SESSION['username']), 0, 1) ?>
                            </div>
                        <?php endif; ?>
                        <p class="text-muted small">รูปโปรไฟล์ปัจจุบัน</p>
                    </div>

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
                        <label for="profile_image" class="form-label">อัปโหลดรูปภาพโปรไฟล์ใหม่จากเครื่อง</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        <div class="form-text">รองรับไฟล์ภาพ JPG, PNG, WEBP (หากไม่เลือก ระบบจะใช้รูปเดิม)</div>
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
