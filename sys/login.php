<?php
declare(strict_types=1);

require_once __DIR__ . '/con/config.php';
startAppSession();

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = isset($_POST['csrf_token']) ? (string) $_POST['csrf_token'] : '';
    if (!hash_equals((string) $_SESSION['csrf_token'], $postedToken)) {
        $error = 'คำขอไม่ถูกต้อง กรุณารีเฟรชหน้าแล้วลองใหม่';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน';
        } elseif (strlen($username) > 200 || strlen($password) > 2000) {
            $error = 'ข้อมูลเข้าสู่ระบบไม่ถูกต้อง';
        } else {
            $user = authenticateUser($username, $password);
            if ($user !== null) {
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                unset($_SESSION['csrf_token']);
                header('Location: dashboard.php');
                exit;
            }
            $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง หรือระบบเชื่อมต่อไม่สำเร็จ';
        }
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>เข้าสู่ระบบ | ทะเบียนมิตรภาพ</title>
    <style>
        :root{color-scheme:light;--ink:#172033;--muted:#65718a;--line:#dce3ee;--primary:#2457c5;--bg:#f3f6fb}
        *{box-sizing:border-box}body{margin:0;font-family:Tahoma,Arial,sans-serif;background:var(--bg);color:var(--ink);min-height:100vh;display:grid;place-items:center;padding:24px}
        .panel{width:min(100%,420px);background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 14px 40px rgba(24,45,80,.08)}
        h1{font-size:24px;margin:0 0 8px}.sub{color:var(--muted);margin:0 0 24px;line-height:1.6}
        label{display:block;font-weight:700;margin:16px 0 7px}input{width:100%;font:inherit;padding:12px 13px;border:1px solid #cbd5e1;border-radius:10px;background:#fff}
        input:focus{outline:3px solid #dbe7ff;border-color:var(--primary)}button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:10px;background:var(--primary);color:#fff;font:inherit;font-weight:700;cursor:pointer}
        button:hover{filter:brightness(.94)}.error{padding:11px 12px;background:#fff0f0;color:#a52222;border:1px solid #ffd1d1;border-radius:10px;line-height:1.5}
        .foot{margin-top:20px;color:var(--muted);font-size:12px;line-height:1.6}
    </style>
</head>
<body>
<main class="panel">
    <h1>เข้าสู่ระบบ</h1>
    <p class="sub">ระบบทะเบียนมิตรภาพ</p>

    <?php if ($error !== ''): ?>
        <p class="error" role="alert"><?= escapeHtml($error) ?></p>
    <?php endif; ?>

    <form method="post" action="login.php" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= escapeHtml((string) $_SESSION['csrf_token']) ?>">
        <label for="username">ชื่อผู้ใช้</label>
        <input id="username" name="username" type="text" value="<?= escapeHtml($username) ?>" maxlength="200" autocomplete="username" required autofocus>
        <label for="password">รหัสผ่าน</label>
        <input id="password" name="password" type="password" maxlength="2000" autocomplete="current-password" required>
        <button type="submit">เข้าสู่ระบบ</button>
    </form>
    <p class="foot">หากเข้าสู่ระบบไม่ได้ ให้ตรวจสอบการตั้งค่า GAS_URL และ GAS_API_KEY ใน Render</p>
</main>
</body>
</html>
