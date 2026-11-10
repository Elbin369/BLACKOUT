<?php
/**
 * PROJECT: BLACKOUT — E Corp Career & Audit Attachment Upload API
 * Implements H05 (Dangerous Attachment — Insecure File Upload)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$uploadDir = UPLOADS_DIR;
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

// Inspect action
if (isset($_GET['action']) && $_GET['action'] === 'inspect') {
    $file = basename($_GET['file'] ?? '');
    $filePath = $uploadDir . '/' . $file;
    if (!empty($file) && file_exists($filePath)) {
        $content = file_get_contents($filePath);
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $isExecutableType = in_array($ext, ['php', 'phtml', 'php5', 'phar', 'sh', 'py']) || strpos($file, '.php.') !== false;
        
        $h05_flag = get_challenge_flag('H05') ?: 'Flagfound{dangerous_attachment_435}';

        json_response([
            'status' => 'success',
            'filename' => $file,
            'size' => filesize($filePath),
            'detected_type' => $ext,
            'security_alert' => $isExecutableType ? 'CRITICAL: Executable extension bypass verified.' : 'Normal document format.',
            'challenge' => $isExecutableType ? 'H05' : null,
            'flag' => $isExecutableType ? $h05_flag : null
        ]);
    } else {
        json_response(['status' => 'error', 'message' => 'Uploaded file not found.'], 404);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response([
        'status' => 'ready',
        'endpoint' => 'Corporate Document Ingestion Service',
        'policy' => 'Accepts PDF, DOCX, TXT corporate attachments. Maximum size: 2MB.',
        'note' => 'Uploads are staged in the sandboxed repository.'
    ]);
}

if (!isset($_FILES['attachment'])) {
    json_response(['status' => 'error', 'message' => 'No file attached in field "attachment".'], 400);
}

$file = $_FILES['attachment'];
$origName = basename($file['name']);
$tmpPath = $file['tmp_name'];
$fileSize = $file['size'];

if ($fileSize > 2 * 1024 * 1024) {
    json_response(['status' => 'error', 'message' => 'File exceeds 2MB threshold.'], 400);
}

// VULNERABILITY H05: Flawed Blacklist / Extension Validation
// Intentionally flawed check: only checks if string ends with .exe or .sh,
// OR allows .phtml, .php5, or double extensions like .php.pdf or case variation like .pHp
$lowerName = strtolower($origName);
$blocked = ['exe', 'bat', 'cmd', 'vbs'];
$ext = pathinfo($origName, PATHINFO_EXTENSION);

if (in_array(strtolower($ext), $blocked)) {
    json_response(['status' => 'error', 'message' => 'Executable file type rejected by corporate boundary filter.'], 403);
}

// Sanitize filename to prevent directory traversal while preserving extension trick
$safeName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $origName);
$destination = $uploadDir . '/' . $safeName;

if (move_uploaded_file($tmpPath, $destination)) {
    $h05_flag = get_challenge_flag('H05') ?: 'Flagfound{dangerous_attachment_435}';
    
    // Check if the uploaded file bypassed the expected document constraints
    $isBypass = (
        strpos($lowerName, '.php') !== false ||
        strpos($lowerName, '.phtml') !== false ||
        strpos($lowerName, '.phar') !== false ||
        in_array(strtolower($ext), ['phtml', 'php', 'php3', 'php5', 'phar', 'pl', 'py'])
    );

    json_response([
        'status' => 'success',
        'message' => 'Document uploaded to staging repository.',
        'stored_as' => $safeName,
        'relative_url' => '/uploads/' . $safeName,
        'inspection_url' => '/api/upload.php?action=inspect&file=' . $safeName,
        'challenge' => $isBypass ? 'H05' : null,
        'flag' => $isBypass ? $h05_flag : null
    ]);
} else {
    json_response(['status' => 'error', 'message' => 'Failed to write uploaded file to staging store.'], 500);
}
