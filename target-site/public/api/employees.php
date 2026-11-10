<?php
/**
 * PROJECT: BLACKOUT — E Corp Employee Directory & Profile API
 * Implements M01 (Hidden Identity — IDOR)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/db.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'view';
$requestedId = $_GET['id'] ?? '1';

try {
    $pdo = Database::getConnection();

    if ($action === 'list') {
        // Public sanitized staff directory (does not expose executive vault records)
        $stmt = $pdo->query('SELECT id, employee_code, first_name, last_name, department, title FROM employees WHERE id < 50 ORDER BY id ASC');
        $records = $stmt->fetchAll();
        json_response([
            'status' => 'success',
            'count' => count($records),
            'employees' => $records,
            'note' => 'Use /api/employees.php?id=<id> to view full profile dossiers.'
        ]);
    }

    // M01 VULNERABILITY: IDOR (Broken Object Level Authorization)
    // No ownership check between the authenticated session and $requestedId.
    // Standard employee is ID 1 or 2. ID 99 is Alexander Vance (Executive Security Council).
    $id = intval($requestedId);
    $stmt = $pdo->prepare('SELECT id, employee_code, first_name, last_name, department, title, email, phone, badge_id, profile_bio, clearance_level, salary_tier FROM employees WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $employee = $stmt->fetch();

    if (!$employee) {
        json_response(['status' => 'error', 'message' => 'Employee profile not found.'], 404);
    }

    // Flag response if accessing ID 99
    $response = [
        'status' => 'success',
        'profile' => $employee
    ];

    if ($id === 99) {
        $response['challenge'] = 'M01';
        $response['flag'] = get_challenge_flag('M01') ?: 'Flagfound{identity_exposed_402}';
    }

    json_response($response);

} catch (Exception $e) {
    // Fallback static profile if DB is offline
    if (intval($requestedId) === 99) {
        json_response([
            'status' => 'success',
            'challenge' => 'M01',
            'flag' => get_challenge_flag('M01') ?: 'Flagfound{identity_exposed_402}',
            'profile' => [
                'id' => 99,
                'employee_code' => 'EC-9901',
                'first_name' => 'Alexander',
                'last_name' => 'Vance',
                'department' => 'Executive Security Council',
                'title' => 'Chief Security Architect',
                'email' => 'a.vance@ecorp.corp',
                'badge_id' => 'BDG-EXEC-99',
                'profile_bio' => 'Clearance Token: Flagfound{identity_exposed_402} - Note: Audit all profile endpoints for missing ownership validations.',
                'clearance_level' => 5
            ]
        ]);
    } else {
        json_response([
            'status' => 'success',
            'profile' => [
                'id' => 1,
                'employee_code' => 'EC-1001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'department' => 'Customer Operations',
                'title' => 'Support Engineer',
                'email' => 'j.doe@ecorp.corp',
                'badge_id' => 'BDG-1001',
                'profile_bio' => 'Specializes in level 1 enterprise support and system monitoring.',
                'clearance_level' => 1
            ]
        ]);
    }
}
