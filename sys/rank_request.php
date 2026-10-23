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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'การตรวจสอบความปลอดภัยล้มเหลว';
    } else {
        $action_type = $_POST['action_type'] ?? '';
        
        // ผู้ถูกปกป้องส่งคำขอเลื่อนยศ
        if ($action_type === 'request' && $role === 'ผู้ถูกปกป้อง') {
            $payload = [
                'action' => 'rank_action',
                'sub_action' => 'request',
                'user_id_ref' => $_POST['user_id_ref'] ?? '',
                'target_rank' => $_POST['target_rank'] ?? '',
                'proposer_name' => $_SESSION['username'],
                'proposer_role' => $_SESSION['role'],
                'proposer_comment' => $_POST['proposer_comment'] ?? ''
            ];
            $response = post_to_gas($payload);
            if (isset($response['status']) && $response['status'] === 'success') {
                $message = 'ส่งคำขออนุมัติเลื่อนยศเรียบร้อยแล้ว';
            } else {
                $error = $response['message'] ?? 'เกิดข้อผิดพลาดในการส่งคำขอ';
            }
        }
        
        // ผู้อำนวยการพิจารณาอนุมัติ/ปฏิเสธ
        if ($action_type === 'approve' && $role === 'ผู้อำนวยการ') {
            $payload = [
                'action' => 'rank_action',
                'sub_action' => 'approve',
                'log_id' => $_POST['log_id'] ?? '',
                'decision' => $_POST['decision'] ?? '', // 'อนุมัติ' หรือ 'ไม่อนุมัติ'
                'approver_name' => $_SESSION['username'],
                'approver_role' => $_SESSION['role'],
                'approver_comment' => $_POST['approver_comment'] ?? ''
            ];
            $response = post_to_gas($payload);
            if (isset($response['status']) && $response['status'] === 'success') {
                $message = 'บันทึกการพิจารณาเรียบร้อยแล้ว';
            } else {
                $error = $response['message'] ?? 'เกิดข้อผิดพลาดในการพิจารณา';
            }
        }
    }
}

// ดึงข้อมูลรายการยศและประวัติจาก GAS (แท็บ ยศ_data และ ยศ_log)
$rank_logs_resp = post_to_gas(['action' => 'get_rank_logs']);
$rank_logs = ($rank_logs_resp && isset($rank_logs_resp['data'])) ? $rank_logs_resp['data'] : [];

require_once 'con/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>ระบบการเลื่อนยศ</h2>
    <?php if ($role === 'ผู้ถูกปกป้อง'): ?>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#requestRankModal">+ เสนอขอเลื่อนยศให้เพื่อน</button>
    <?php endif; ?>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= escape($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= escape($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">ประวัติและรายการขอเสนอเลื่อนยศ</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>รหัสเพื่อน</th>
                        <th>ยศที่เสนอ</th>
                        <th>ผู้เสนอ (ผู้ถูกปกป้อง)</th>
                        <th>ความคิดเห็นผู้เสนอ</th>
                        <th>สถานะ / ผู้พิจารณา</th>
                        <th>ความคิดเห็นผู้พิจารณา</th>
                        <?php if ($role === 'ผู้อำนวยการ'): ?>
                            <th>ดำเนินการ</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rank_logs)): ?>
                        <?php foreach ($rank_logs as $log): ?>
                            <tr>
                                <td><?= escape($log['user_id'] ?? '') ?></td>
                                <td><span class="badge bg-primary"><?= escape($log['target_rank'] ?? '') ?></span></td>
                                <td><?= escape($log['proposer'] ?? '') ?></td>
                                <td><small><?= escape($log['proposer_comment'] ?? '-') ?></small></td>
                                <td>
                                    <?php if (empty($log['approver'])): ?>
                                        <span class="badge bg-warning text-dark">รอการพิจารณา</span>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= escape($log['status'] ?? 'อนุมัติแล้ว') ?></span>
                                        <br><small class="text-muted">โดย <?= escape($log['approver']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= escape($log['approver_comment'] ?? '-') ?></small></td>
                                <?php if ($role === 'ผู้อำนวยการ'): ?>
                                    <td>
                                        <?php if (empty($log['approver'])): ?>
                                            <button class="btn btn-sm btn-success" onclick="approveRank('<?= escape($log['id']) ?>', 'อนุมัติ')">อนุมัติ</button>
                                            <button class="btn btn-sm btn-danger" onclick="approveRank('<?= escape($log['id']) ?>', 'ไม่อนุมัติ')">ไม่อนุมัติ</button>
                                        <?php else: ?>
                                            <span class="text-muted small">ดำเนินการแล้ว</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= ($role === 'ผู้อำนวยการ') ? '7' : '6' ?>" class="text-center text-muted py-4">ยังไม่มีรายการขอเสนอเลื่อนยศ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal เสนอเลื่อนยศสำหรับผู้ถูกปกป้อง -->
<?php if ($role === 'ผู้ถูกปกป้อง'): ?>
<div class="modal fade" id="requestRankModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="rank_request.php">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <input type="hidden" name="action_type" value="request">
                <div class="modal-header">
                    <h5 class="modal-title">เสนอขอเลื่อนยศให้เพื่อน</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">รหัสประจำตัวเพื่อน</label>
                        <input type="text" class="form-control" name="user_id_ref" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยศที่ต้องการเสนอ</label>
                        <input type="text" class="form-control" name="target_rank" placeholder="เช่น เกลอเอก, มิตรมหาจัตวา" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ความคิดเห็น / เหตุผลที่เสนอ</label>
                        <textarea class="form-control" name="proposer_comment" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-success">ส่งคำขอเลื่อนยศ</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once 'con/footer.php'; ?>
