<?php
// tem/friend_view.php - มุมมองเพื่อนในทะเบียนมิตรภาพ
?>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="alert alert-info">
            <h4 class="alert-heading">ยินดีต้อนรับ สู่ทะเบียนมิตรภาพ!</h4>
            <p class="mb-0">ท่านสามารถตรวจสอบข้อมูลส่วนตัวและสถานะยศของท่านได้จากที่นี่</p>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">ข้อมูลส่วนตัวของฉัน</h5>
            </div>
            <div class="card-body">
                <p><strong>ชื่อผู้ใช้:</strong> <?= escape($_SESSION['username']) ?></p>
                <p><strong>บทบาท:</strong> <?= escape($_SESSION['role']) ?></p>
                <p class="text-muted">ข้อมูลรายละเอียดสถานะและยศปัจจุบันของคุณจะแสดงที่นี่โดยดึงจากแท็บ เพื่อน_data[cite: 3]</p>
                <a href="profile.php" class="btn btn-primary">แก้ไขโปรไฟล์ / เปลี่ยนรหัสผ่าน</a>
            </div>
        </div>
    </div>
</div>
