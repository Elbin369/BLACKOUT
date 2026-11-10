<?php
/**
 * PROJECT: BLACKOUT — E Corp Application Configuration
 */

define('BLACKOUT_EXEC', true);

// Application paths
define('APP_ROOT', dirname(__DIR__));
define('SECRETS_DIR', APP_ROOT . '/challenge-secrets');
define('PUBLIC_DIR', APP_ROOT . '/public');
define('UPLOADS_DIR', PUBLIC_DIR . '/uploads');

// Load flags registry
$flagsRegistry = [];
if (file_exists(SECRETS_DIR . '/flags.php')) {
    $flagsRegistry = require SECRETS_DIR . '/flags.php';
}

function get_challenge_flag(string $challengeId): ?string {
    global $flagsRegistry;
    return $flagsRegistry[$challengeId]['flag'] ?? null;
}

// Database configuration
$dbConfig = [
    'driver' => getenv('DB_DRIVER') ?: 'mysql',
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => getenv('DB_PORT') ?: '3306',
    'dbname' => getenv('DB_NAME') ?: 'ecorp_db',
    'user' => getenv('DB_USER') ?: 'ecorp_user',
    'pass' => getenv('DB_PASS') ?: 'EcorpSec2026!Pass',
    'sqlite_path' => APP_ROOT . '/database/ecorp.sqlite'
];

// Challenge specific constants
define('JWT_SECRET_KEY', 'ecorp_weak_jwt_secret_key_2026');
define('MOCK_SSRF_HOST', getenv('SSRF_SERVICE_HOST') ?: 'mock-internal');
define('MOCK_SSRF_PORT', getenv('SSRF_SERVICE_PORT') ?: '8080');

// Set E03 Cookie Monster cookie on every public response if not set
if (!headers_sent() && empty($_COOKIE['ecorp_session_token'])) {
    // E03: Cookie Monster flag directly in challenge-specific cookie
    $e03_flag = get_challenge_flag('E03') ?: 'Flagfound{cookie_monster_583}';
    setcookie('ecorp_session_token', 'SEC-SESS-9821-' . base64_encode($e03_flag), [
        'expires' => time() + 86400 * 30,
        'path' => '/',
        'httponly' => false, // Accessible to client-side devtools / JS inspection
        'samesite' => 'Lax'
    ]);
}
