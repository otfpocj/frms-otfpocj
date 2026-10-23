<?php

declare(strict_types=1);

$gasUrl = getenv('GAS_URL') ?: '';
$gasToken = getenv('GAS_TOKEN') ?: '';

if ($gasUrl === '' || $gasToken === '') {
    throw new RuntimeException(
        'กรุณาตั้งค่า GAS_URL และ GAS_TOKEN'
    );
}

function readSheet(string $sheetName): array
{
    global $gasUrl, $gasToken;

    $allowedSheets = [
        'ผู้ใช้งาน',
        'เพื่อน_data',
        'ยศ_data',
        'ยศ_log',
        'ชื่อ_log',
        'เหตุการณ์_log'
    ];

    if (!in_array($sheetName, $allowedSheets, true)) {
        throw new InvalidArgumentException(
            'ไม่อนุญาตให้อ่านแท็บนี้'
        );
    }

    $query = http_build_query([
        'action' => 'read',
        'sheet' => $sheetName,
        'key' => $gasToken
    ]);

    $url = $gasUrl . '?' . $query;

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'ignore_errors' => true
        ]
    ]);

    $response = file_get_contents($url, false, $context);

    if ($response === false) {
        throw new RuntimeException(
            'ไม่สามารถเชื่อมต่อ Google Apps Script ได้'
        );
    }

    $result = json_decode($response, true);


if (!is_array($result)) {
    throw new RuntimeException(
        'คำตอบไม่ใช่ JSON ที่ถูกต้อง'
    );
}

if (($result['success'] ?? false) !== true) {
    $message = $result['message'] ?? 'ไม่ทราบสาเหตุ';

    throw new RuntimeException(
        'Apps Script: ' . $message
    );
}


    return $result;
}

