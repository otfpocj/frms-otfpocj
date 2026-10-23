<?php
// tem/director_view.php - มุมมองผู้อำนวยการ
?>
<div class="row">
    <div class="col-md-12 mb-4">
        <div class="alert alert-primary">
            <h4 class="alert-heading">ยินดีต้อนรับ ท่านผู้อำนวยการ!</h4>
            <p class="mb-0">ท่านมีสิทธิ์ในการตรวจสอบภาพรวมของระบบ และพิจารณาอนุมัติ (หรือปฏิเสธ) การขอเลื่อนยศที่เสนอมาจากผู้ถูกปกป้อง</p>
        </div>
    </div>

    <!-- ส่วนพิจารณาอนุมัติการเลื่อนยศ -->
    <div class="col-md-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">รายการรออนุมัติเลื่อนยศ (จากผู้ถูกปกป้อง)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">กำลังดึงข้อมูลคำขอเลื่อนยศจาก Google Sheets...</p>
                <!-- ตารางแสดงรายการขอเลื่อนยศจากแท็บ ยศ_log -->
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ลำดับ</th>
                                <th>รหัสประจำตัว</th>
                                <th>ชื่อเพื่อน</th>
                                <th>ยศที่เสนอ</th>
                                <th>ผู้เสนอ</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center text-muted">ยังไม่มีรายการรออนุมัติในขณะนี้</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
