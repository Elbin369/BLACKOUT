<?php
/**
 * PROJECT: BLACKOUT — E Corp Enterprise Security Policy & Audit API
 * Implements A02 (Zero Trust — Authorization Bypass)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/db.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$headers = getallheaders();

// Low privilege authentication simulation
$currentUser = 'johndoe';
$currentRole = 'user'; // Low privilege standard user

// VULNERABILITY A02: Trusting Client-Supplied Role Headers / Parameters
// Flawed microservice gateway trusts X-User-Role or X-Role-Override without validating against server session
$suppliedRole = null;
if (isset($headers['X-User-Role'])) {
    $suppliedRole = trim($headers['X-User-Role']);
} elseif (isset($headers['x-user-role'])) {
    $suppliedRole = trim($headers['x-user-role']);
} elseif (isset($headers['X-Role-Override'])) {
    $suppliedRole = trim($headers['X-Role-Override']);
} elseif (isset($_GET['role'])) {
    $suppliedRole = trim($_GET['role']);
}

$effectiveRole = $suppliedRole ?: $currentRole;

$action = $_GET['action'] ?? $_GET['view'] ?? 'status';

if ($action === 'status') {
    json_response([
        'status' => 'operational',
        'enforcement_engine' => 'Zero-Trust Distributed Policy Gateway v1.9',
        'authenticated_user' => $currentUser,
        'effective_role' => $effectiveRole,
        'actions' => ['status', 'policy_summary', 'executive_audit']
    ]);
}

if ($action === 'policy_summary') {
    json_response([
        'status' => 'success',
        'policy' => [
            'mfa_enforced' => true,
            'session_ttl' => 3600,
            'default_role' => 'user',
            'required_role_for_audit' => 'SecurityCouncil'
        ]
    ]);
}

if ($action === 'executive_audit') {
    $authorizedRoles = ['securitycouncil', 'admin', 'executive', 'director'];
    if (!in_array(strtolower($effectiveRole), $authorizedRoles)) {
        json_response([
            'status' => 'denied',
            'error' => '403 Forbidden: Zero-Trust Policy Violation',
            'message' => 'Role "' . htmlspecialchars($effectiveRole) . '" lacks clearance for executive audit trails.',
            'hint' => 'Privilege level must match "SecurityCouncil" or "Admin" in gateway policy evaluation.'
        ], 403);
    }

    $a02_flag = get_challenge_flag('A02') ?: 'Flagfound{zero_trust_946}';

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM admin_audit_logs ORDER BY id DESC');
        $logs = $stmt->fetchAll();
    } catch (Exception $e) {
        $logs = [];
    }

    json_response([
        'status' => 'authorized',
        'message' => 'Zero-Trust authorization bypass successful: Role header evaluated without cryptographic assertion.',
        'challenge' => 'A02',
        'flag' => $a02_flag,
        'role_used' => $effectiveRole,
        'audit_logs' => $logs
    ]);
}
