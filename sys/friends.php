<?php
require_once 'con/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$role = $_SESSION['role'] ?? '';
$message = '';
$error = '';

// การจัดการการเพิ่ม/แก้ไข/ลบ ข้อมูลเพื่อน (เฉพาะ ผู้อำนวยการ และ ผู้ถูกปกป้อง)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'การตรวจสอบความปลอดภัยล้มเหลว';
    } else {
        $action_type = $_POST['action_type'] ?? '';
        
        $payload = [
            'action' => 'manage_friend',
            'action_type' => $action_type,
            'user_id_ref' => $_POST['user_id_ref'] ?? '',
            'prefix' => $_POST['prefix'] ?? '',
            'fullname' => $_POST['fullname'] ?? '',
            'school' => $_POST['school'] ?? '',
            'grade' => $_POST['grade'] ?? '',
            'room' => $_POST['room'] ?? '',
            'recorder_name' => $_SESSION['username'],
            'recorder_role' => $_SESSION['role']
        ];

        $response = post_to_gas($payload);
        if (isset($response['status']) && $response['status'] === 'success') {
            $message = 'ดำเนินการเรียบร้อยแล้ว';
        } else {
            $error = $response['message'] ?? 'เกิดข้อผิดพลาดในการจัดการข้อมูล';
        }
    }
}

// ดึงข้อมูลเพื่อนจาก Google Apps Script (แท็บ เพื่อน_data)
$friends_response = post_to_gas(['action' => 'get_friends']);
$friends = ($friends_response && isset($friends_response['data'])) ? $friends_response['data'] : [];

require_once 'con/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>ทะเบียนมิตรภาพ</h2>
    <?php if (in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFriendModal">+ เพิ่มข้อมูลเพื่อน</button>
    <?php endif; ?>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= escape($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>รหัสประจำตัว</th>
                        <th>คำนำหน้า</th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ยศปัจจุบัน</th>
                        <th>โรงเรียน</th>
                        <th>ชั้น/ห้อง</th>
                        <th>ผู้บันทึก</th>
                        <?php if (in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])): ?>
                            <th>จัดการ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($friends)): ?>
                        <?php foreach ($friends as $f): ?>
                            <tr>
                                <td><?= escape($f['user_id'] ?? '') ?></td>
                                <td><?= escape($f['prefix'] ?? '') ?></td>
                                <td><?= escape($f['fullname'] ?? '') ?></td>
                                <td><span class="badge bg-info text-dark"><?= escape($f['rank'] ?? 'ไม่มียศ') ?></span></td>
                                <td><?= escape($f['school'] ?? '') ?></td>
                                <td><?= escape($f['grade'] ?? '') ?> / <?= escape($f['room'] ?? '') ?></td>
                                <td><small class="text-muted"><?= escape($f['recorder'] ?? '') ?></small></td>
                                <?php if (in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])): ?>
                                    <td>
                                        <button class="btn btn-sm btn-warning">แก้ไข</button>
                                        <button class="btn btn-sm btn-danger">ลบ</button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง']) ? '8' : '7' ?>" class="text-center text-muted py-4">ไม่พบข้อมูลในทะเบียนมิตรภาพ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal สำหรับเพิ่มข้อมูลเพื่อน -->
<?php if (in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])): ?>
<div class="modal fade" id="addFriendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="friends.php">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action_type" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มข้อมูลเพื่อนในทะเบียน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">รหัสประจำตัวผู้ใช้</label>
                        <input type="text" class="form-control" name="user_id_ref" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">คำนำหน้า</label>
                        <input type="text" class="form-control" name="prefix" placeholder="นาย / นางสาว">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล</label>
                        <input type="text" class="form-control" name="fullname" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">โรงเรียน</label>
                        <input type="text" class="form-control" name="school">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ระดับชั้น</label>
                            <input type="text" class="form-control" name="grade">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ห้องเรียน</label>
                            <input type="text" class="form-control" name="room">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'con/footer.php'; ?>
