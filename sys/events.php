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

// บันทึกเหตุการณ์ใหม่ลงแท็บ เหตุการณ์_log (เฉพาะผู้ถูกปกป้อง และผู้อำนวยการ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'การตรวจสอบความปลอดภัยล้มเหลว';
    } else {
        $payload = [
            'action' => 'add_event',
            'user_id_ref' => $_POST['user_id_ref'] ?? '',
            'event_date' => $_POST['event_date'] ?? date('Y-m-d'),
            'title' => $_POST['title'] ?? '',
            'details' => $_POST['details'] ?? '',
            'recorder' => $_SESSION['username']
        ];

        $response = post_to_gas($payload);
        if (isset($response['status']) && $response['status'] === 'success') {
            $message = 'บันทึกเหตุการณ์เรียบร้อยแล้ว';
        } else {
            $error = $response['message'] ?? 'เกิดข้อผิดพลาดในการบันทึกเหตุการณ์';
        }
    }
}

// ดึงรายการเหตุการณ์จาก Google Apps Script (แท็บ เหตุการณ์_log)[cite: 7]
$events_resp = post_to_gas(['action' => 'get_events']);
$events = ($events_resp && isset($events_resp['data'])) ? $events_resp['data'] : [];

require_once 'con/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>บันทึกเหตุการณ์ (Event Log)</h2>
    <?php if (in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])): ?>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addEventModal">+ บันทึกเหตุการณ์ใหม่</button>
    <?php endif; ?>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= escape($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<div class="row">
    <?php if (!empty($events)): ?>
        <?php foreach ($events as $ev): ?>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100 border-start border-4 border-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h5 class="card-title text-primary"><?= escape($ev['title'] ?? 'ไม่มีหัวข้อ') ?></h5>
                            <span class="badge bg-secondary"><?= escape($ev['event_date'] ?? '') ?></span>
                        </div>
                        <h6 class="card-subtitle mb-2 text-muted">ผู้เกี่ยวข้อง (รหัส): <?= escape($ev['user_id'] ?? '-') ?></h6>
                        <p class="card-text mt-3"><?= nl2br(escape($ev['details'] ?? '')) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-md-12">
            <div class="card shadow-sm p-4 text-center text-muted">
                ยังไม่มีการบันทึกเหตุการณ์ในขณะนี้
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal บันทึกเหตุการณ์ -->
<?php if (in_array($role, ['ผู้อำนวยการ', 'ผู้ถูกปกป้อง'])): ?>
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="events.php">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title">บันทึกเหตุการณ์ใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">รหัสผู้เกี่ยวข้อง (รหัสประจำตัว)</label>
                        <input type="text" class="form-control" name="user_id_ref">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">วันที่ของเหตุการณ์</label>
                        <input type="date" class="form-control" name="event_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หัวข้อเหตุการณ์</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด</label>
                        <textarea class="form-control" name="details" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกเหตุการณ์</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'con/footer.php'; ?>
