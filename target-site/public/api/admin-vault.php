<?php
/**
 * PROJECT: BLACKOUT — E Corp Phantom Executive Vault API
 * Implements A05 (The Phantom Admin — Multi-step Privilege Escalation)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Auth-Required: ECorp-Federation-v2');

$headers = getallheaders();
$badgeHeader = $headers['X-Auth-Badge'] ?? $headers['x-auth-badge'] ?? $_GET['badge'] ?? null;
$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

if (!$badgeHeader) {
    json_response([
        'status' => 'unauthorized',
        'code' => 401,
        'message' => 'Executive console requires valid Director clearance badge in X-Auth-Badge header.',
        'hint' => 'Locate executive personnel badge credentials in corporate employee records.'
    ], 401);
}

// Step 1 check: Verify Director Badge (Alden Cross BDG-1003)
if (trim($badgeHeader) !== 'BDG-1003') {
    json_response([
        'status' => 'forbidden',
        'code' => 403,
        'message' => 'Badge ID "' . htmlspecialchars($badgeHeader) . '" does not hold Tier-4 Infrastructure Director clearance.',
        'hint' => 'Audit employee records for Infrastructure Director clearance.'
    ], 403);
}

// Step 2: Elevation Request
if ($action === 'request_elevation') {
    $role = $_GET['role'] ?? $_POST['role'] ?? '';
    if (strtoupper($role) !== 'ROLE_SECOPS_DIR' && strtolower($role) !== 'infrastructure director') {
        json_response([
            'status' => 'pending_verification',
            'message' => 'Director badge recognized. Provide target elevation role parameter ?role=ROLE_SECOPS_DIR',
            'badge_verified' => 'BDG-1003 (Alden Cross)'
        ]);
    }

    // Generate elevation token
    $elevationToken = 'ELEV-ALDEN-BDG1003-' . hash('sha256', 'BDG-1003:ROLE_SECOPS_DIR:ECORP_2026');

    json_response([
        'status' => 'elevation_granted',
        'message' => 'Director elevation capability grant approved.',
        'elevation_token' => $elevationToken,
        'next_step' => 'Submit elevation_token with action=unlock_vault and your X-Auth-Badge header to access the Phantom Console.'
    ]);
}

// Step 3: Unlock Vault
if ($action === 'unlock_vault') {
    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    $expectedToken = 'ELEV-ALDEN-BDG1003-' . hash('sha256', 'BDG-1003:ROLE_SECOPS_DIR:ECORP_2026');

    if ($token !== $expectedToken) {
        json_response([
            'status' => 'invalid_token',
            'message' => 'Elevation token invalid or expired. Obtain an active elevation token via ?action=request_elevation'
        ], 403);
    }

    // VULNERABILITY A05 Complete!
    $a05_flag = get_challenge_flag('A05') ?: 'Flagfound{phantom_admin_187}';

    json_response([
        'status' => 'authorized',
        'message' => 'PHANTOM ADMIN ROOT CONSOLE ACCESSED. Multi-step privilege escalation verified.',
        'challenge' => 'A05',
        'flag' => $a05_flag,
        'phantom_admin_console' => [
            'governance_node' => 'PHANTOM-ROOT-EXEC-01',
            'access_level' => 'SOVEREIGN_ROOT_ADMIN',
            'escalation_chain' => [
                '1. Employee Record Reconnaissance (BDG-1003)',
                '2. Header Spoofing (X-Auth-Badge)',
                '3. Role Capability Elevation (ROLE_SECOPS_DIR)',
                '4. Executive Vault Cryptographic Unlock'
            ],
            'system_directive' => 'Directive: Flagfound{phantom_admin_187}'
        ]
    ]);
}

// Default response if badge provided
json_response([
    'status' => 'identified',
    'badge' => $badgeHeader,
    'identity' => 'Alden Cross (Infrastructure Director)',
    'available_actions' => [
        'request_elevation' => 'Request privilege elevation token (?action=request_elevation&role=ROLE_SECOPS_DIR)',
        'unlock_vault' => 'Unlock master console (?action=unlock_vault&token=...)'
    ]
]);
