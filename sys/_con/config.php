<?php
declare(strict_types=1);

/**
 * Server-side configuration.
 * Set GAS_URL and GAS_API_KEY in Render Environment Variables.
 * Never put the shared API key in browser-side JavaScript or HTML.
 */

function appEnv(string $name): string
{
    $value = getenv($name);
    return is_string($value) ? trim($value) : '';
}

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Calls the Apps Script Web App from the PHP server.
 * Returns a normalized user array on success, otherwise null.
 */
function authenticateUser(string $username, string $password): ?array
{
    $gasUrl = appEnv('GAS_URL');
    $apiKey = appEnv('GAS_API_KEY');

    if ($gasUrl === '' || $apiKey === '') {
        error_log('Login configuration missing: GAS_URL or GAS_API_KEY.');
        return null;
    }

    if (!filter_var($gasUrl, FILTER_VALIDATE_URL) || stripos($gasUrl, 'https://') !== 0) {
        error_log('GAS_URL must be a valid HTTPS URL.');
        return null;
    }

    $postData = http_build_query([
        'action' => 'login',
        'api_key' => $apiKey,
        'username' => $username,
        'password' => $password,
    ], '', '&', PHP_QUERY_RFC3986);

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                        "Accept: application/json\r\n",
            'content' => $postData,
            'timeout' => 15,
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $response = @file_get_contents($gasUrl, false, $context);
    if (!is_string($response) || $response === '') {
        error_log('Apps Script login request failed or returned an empty response.');
        return null;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || ($data['success'] ?? false) !== true || !isset($data['user']) || !is_array($data['user'])) {
        return null;
    }

    $user = $data['user'];
    $id = isset($user['id']) ? trim((string) $user['id']) : '';
    $returnedUsername = isset($user['username']) ? trim((string) $user['username']) : '';

    if ($id === '' || $returnedUsername === '') {
        error_log('Apps Script returned an incomplete user record.');
        return null;
    }

    return [
        'id' => $id,
        'username' => $returnedUsername,
        'role' => isset($user['role']) ? trim((string) $user['role']) : '',
        'image' => isset($user['image']) ? trim((string) $user['image']) : '',
    ];
}
