
<?php

declare(strict_types=1);

$status = 'ยังไม่ได้ทดสอบ';
$rowCount = null;

try {
    require_once __DIR__ . 'con/config.php';

    $result = readSheet('เพื่อน_data');
    $data = $result['data'] ?? [];

    if (!is_array($data)) {
        throw new RuntimeException('รูปแบบข้อมูลไม่ถูกต้อง');
    }

    $status = 'เชื่อมต่อสำเร็จ';
    $rowCount = max(0, count($data) - 1);

} catch (Throwable $e) {
    http_response_code(503);
    $status = 'เชื่อมต่อไม่สำเร็จ';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>ระบบทะเบียนมิตรภาพ</title>
</head>
<body>
    <main>
        <h1>ระบบทะเบียนมิตรภาพ</h1>
        <p>สถานะฐานข้อมูล: <?= htmlspecialchars(
            $status,
            ENT_QUOTES,
            'UTF-8'
        ) ?></p>

        <?php if ($rowCount !== null): ?>
            <p>จำนวนแถวข้อมูล (ไม่รวมหัวตาราง):
                <?= $rowCount ?>
            </p>
        <?php else: ?>
            <p>โปรดตรวจสอบการตั้งค่าบน Render</p>
        <?php endif; ?>
    </main>
</body>
</html>
