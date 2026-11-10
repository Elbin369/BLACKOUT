<?php
/**
 * PROJECT: BLACKOUT — E Corp Internal Debug Probe API
 * Implements M04 (JavaScript Secrets)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$key = $_GET['key'] ?? '';

if ($key !== 'EC_DEV_DEBUG_KEY_8412') {
    json_response([
        'status' => 'unauthorized',
        'message' => 'Invalid or missing debug key. Inspect client-side application scripts for valid staging keys.'
    ], 403);
}

$m04_flag = get_challenge_flag('M04') ?: 'Flagfound{javascript_secrets_841}';

json_response([
    'status' => 'success',
    'message' => 'Staging debug probe access authorized.',
    'challenge' => 'M04',
    'flag' => $m04_flag,
    'telemetry' => [
        'source' => 'Client Script Inspection',
        'key_verified' => true,
        'flag' => $m04_flag
    ]
]);
