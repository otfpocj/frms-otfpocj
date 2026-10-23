<?php
declare(strict_types=1);

require_once __DIR__ . '/con/config.php';
startAppSession();

if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user = $_SESSION['user'];
$username = (string) ($user['username'] ?? 'ผู้ใช้งาน');
$role = (string) ($user['role'] ?? 'ไม่ระบุบทบาท');
$image = (string) ($user['image'] ?? '');
$showImage = $image !== '' && filter_var($image, FILTER_VALIDATE_URL) !== false && stripos($image, 'https://') === 0;
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แดชบอร์ด | ทะเบียนมิตรภาพ</title>
    <style>
        :root{--ink:#172033;--muted:#65718a;--line:#dce3ee;--primary:#2457c5;--bg:#f3f6fb}
        *{box-sizing:border-box}body{margin:0;font-family:Tahoma,Arial,sans-serif;background:var(--bg);color:var(--ink)}
        header{background:#fff;border-bottom:1px solid var(--line);padding:17px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px}
        header strong{font-size:18px}.logout{color:var(--primary);text-decoration:none;font-weight:700}
        main{max-width:960px;margin:34px auto;padding:0 20px}.welcome{background:#fff;border:1px solid var(--line);border-radius:18px;padding:26px;display:flex;gap:20px;align-items:center}
        .avatar{width:76px;height:76px;border-radius:50%;object-fit:cover;border:1px solid var(--line)}.placeholder{width:76px;height:76px;border-radius:50%;background:#e8efff;display:grid;place-items:center;color:var(--primary);font-size:28px;font-weight:700}
        h1{font-size:25px;margin:0 0 8px}.muted{color:var(--muted);margin:0;line-height:1.7}.section{margin-top:22px;background:#fff;border:1px solid var(--line);border-radius:18px;padding:24px}
        .section h2{margin:0 0 10px;font-size:19px}.notice{color:var(--muted);line-height:1.7;margin:0}
        @media(max-width:520px){header{padding:15px;align-items:flex-start}header strong{font-size:15px}.welcome{padding:20px;align-items:flex-start}h1{font-size:21px}}
    </style>
</head>
<body>
<header>
    <strong>ระบบทะเบียนมิตรภาพ</strong>
    <a class="logout" href="logout.php">ออกจากระบบ</a>
</header>
<main>
    <section class="welcome">
        <?php if ($showImage): ?>
            <img class="avatar" src="<?= escapeHtml($image) ?>" alt="รูปภาพผู้ใช้งาน" referrerpolicy="no-referrer">
        <?php else: ?>
            <div class="placeholder" aria-hidden="true"><?= escapeHtml(mb_substr($username, 0, 1, 'UTF-8')) ?></div>
        <?php endif; ?>
        <div>
            <h1>ยินดีต้อนรับ <?= escapeHtml($username) ?></h1>
            <p class="muted">บทบาท: <?= escapeHtml($role) ?></p>
        </div>
    </section>

    <section class="section">
        <h2>แดชบอร์ด</h2>
        <p class="notice">เข้าสู่ระบบสำเร็จแล้ว หน้านี้เป็นหน้าเริ่มต้นหลังเข้าสู่ระบบ สามารถเพิ่มเมนูทะเบียนเพื่อน ยศ และประวัติการเปลี่ยนแปลงในขั้นตอนถัดไปได้</p>
    </section>
</main>
</body>
</html>
