<?php
/**
 * PROJECT: BLACKOUT — E Corp Authentication API
 * Implements M07 (Broken Login) & H04 (Broken Crown JWT)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/db.php';
require_once __DIR__ . '/../../application/jwt.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'status';

if ($action === 'login') {
    $input = get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }

    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($username)) {
        json_response(['status' => 'error', 'message' => 'Username is required'], 400);
    }

    // M07 VULNERABILITY: Broken Login
    // Flaw 1: Type juggling / loose comparison check on password parameter
    // If client sends JSON with password: true or boolean true, loose check against hash or secret passes
    // Flaw 2: Magic contractor contractor_eval / any password when debug header or contractor account is used
    // Flaw 3: strcmp bypass when password is an array
    $isM07Bypass = false;

    if ($username === 'admin' && ($password === true || (is_array($password) && empty($password)))) {
        $isM07Bypass = true;
    } elseif ($username === 'contractor_eval' && ($password === 'eval2026' || $password === true || (isset($_SERVER['HTTP_X_DEBUG_MODE']) && $_SERVER['HTTP_X_DEBUG_MODE'] === '1'))) {
        $isM07Bypass = true;
    } elseif ($username === 'system_operator' && ($password === true || (is_string($password) && hash_equals('0e839281734918234', '0e' . $password)))) {
        $isM07Bypass = true;
    }

    if ($isM07Bypass) {
        $m07_flag = get_challenge_flag('M07') ?: 'Flagfound{broken_login_572}';
        $token = SimpleJWT::sign([
            'sub' => $username,
            'role' => 'admin',
            'department' => 'SecOps',
            'iat' => time(),
            'exp' => time() + 3600
        ]);

        json_response([
            'status' => 'success',
            'message' => 'Authentication successful via override evaluation vector.',
            'challenge' => 'M07',
            'flag' => $m07_flag,
            'token' => $token,
            'user' => [
                'username' => $username,
                'role' => 'admin',
                'department' => 'Executive SecOps'
            ]
        ]);
    }

    // Standard login check
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role, department FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        // Synthetic demo credentials check
        if ($user && ($password === 'ecorp2026' || $password === 'password123' || password_verify($password, $user['password_hash']))) {
            $token = SimpleJWT::sign([
                'sub' => $user['username'],
                'role' => $user['role'],
                'department' => $user['department'],
                'iat' => time(),
                'exp' => time() + 3600
            ]);

            setcookie('ecorp_jwt_auth', $token, time() + 3600, '/');

            json_response([
                'status' => 'success',
                'message' => 'Logged in successfully.',
                'token' => $token,
                'user' => [
                    'username' => $user['username'],
                    'role' => $user['role'],
                    'department' => $user['department']
                ]
            ]);
        }
    } catch (Exception $e) {
        // Fallback demo user
        if ($username === 'johndoe' && ($password === 'ecorp2026' || $password === 'password123')) {
            $token = SimpleJWT::sign([
                'sub' => 'johndoe',
                'role' => 'user',
                'department' => 'Operations',
                'iat' => time(),
                'exp' => time() + 3600
            ]);
            json_response([
                'status' => 'success',
                'token' => $token,
                'user' => ['username' => 'johndoe', 'role' => 'user', 'department' => 'Operations']
            ]);
        }
    }

    json_response(['status' => 'error', 'message' => 'Invalid credentials.'], 401);
}

// H04: Protected resource requiring valid JWT with admin role
if ($action === 'verify_crown' || $action === 'protected_vault') {
    $token = get_bearer_token();
    if (!$token) {
        json_response([
            'status' => 'error',
            'message' => 'Missing authorization token.',
            'hint' => 'Provide Bearer token in Authorization header or ecorp_jwt_auth cookie.'
        ], 401);
    }

    // Vulnerable verify accepts alg: 'none'
    $payload = SimpleJWT::verify($token);
    if (!$payload) {
        json_response(['status' => 'error', 'message' => 'Invalid or signature verification failed.'], 403);
    }

    // Check if role is admin
    if (isset($payload['role']) && (strtolower($payload['role']) === 'admin' || strtolower($payload['role']) === 'crown_administrator')) {
        $h04_flag = get_challenge_flag('H04') ?: 'Flagfound{broken_crown_691}';
        json_response([
            'status' => 'success',
            'message' => 'Access Granted: Crown Administrator Authorization Verified.',
            'challenge' => 'H04',
            'flag' => $h04_flag,
            'vault_data' => [
                'crown_level' => 'TIER-0-SOVEREIGN',
                'encryption_root' => 'RSA-4096-CORP-MASTER',
                'vault_key' => 'CROWN_VAULT_OPEN'
            ]
        ]);
    } else {
        json_response([
            'status' => 'denied',
            'message' => 'Insufficient privileges. Role "admin" required in verified token payload.',
            'current_role' => $payload['role'] ?? 'user'
        ], 403);
    }
}

// Default status
json_response([
    'status' => 'online',
    'auth_service' => 'E Corp SSO & Identity Engine v4.2',
    'actions' => ['login', 'verify_crown', 'protected_vault']
]);
