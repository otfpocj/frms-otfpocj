<?php
declare(strict_types=1);

require_once __DIR__ . '/con/config.php';
startAppSession();

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
