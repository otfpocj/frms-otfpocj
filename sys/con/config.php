<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_gas_url() {
    $url = getenv('GAS_URL');
    return trim($url ? $url : '');
}

function post_to_gas($data) {
    $url = get_gas_url();
    if (empty($url)) {
        return ['status' => 'error', 'message' => 'ยังไม่ได้กำหนดค่า GAS_URL ใน Environment Variables บน Render'];
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ cURL: ' . $error];
    }
    curl_close($ch);
    
    return json_decode($response, true);
}

function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>
