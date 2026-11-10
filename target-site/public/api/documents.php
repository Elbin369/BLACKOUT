<?php
/**
 * PROJECT: BLACKOUT — E Corp Corporate Document Viewer API
 * Implements H02 (Shadow Directory — Path Traversal)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

$requestedFile = $_GET['file'] ?? '';
$action = $_GET['action'] ?? 'view';

// Base directory for public documents
$baseDocDir = PUBLIC_DIR . '/documents';
if (!is_dir($baseDocDir)) {
    @mkdir($baseDocDir, 0755, true);
    // Put a sample public doc
    @file_put_contents($baseDocDir . '/corporate_overview_2026.pdf', "%PDF-1.4 [E Corp Public Overview 2026]");
}

if (empty($requestedFile)) {
    // List available public documents
    json_response([
        'status' => 'success',
        'documents' => [
            ['id' => 1, 'name' => 'corporate_overview_2026.pdf', 'title' => 'Corporate Overview 2026', 'classification' => 'Public'],
            ['id' => 2, 'name' => 'enterprise_arch_v3.pdf', 'title' => 'Enterprise Architecture Guidelines', 'classification' => 'Internal'],
            ['id' => 3, 'name' => 'infrastructure_q1_summary.pdf', 'title' => 'Q1 Infrastructure Report', 'classification' => 'Confidential'],
            ['id' => 4, 'name' => 'ir_playbook_redacted.pdf', 'title' => 'Incident Response Playbook', 'classification' => 'Internal']
        ],
        'usage' => '/api/documents.php?file=corporate_overview_2026.pdf'
    ]);
}

// VULNERABILITY H02: Insecure Path Concatenation (Path Traversal)
$targetPath = $baseDocDir . '/' . $requestedFile;

// Safety Sandbox Check: Ensure traversal remains strictly within the application container tree
// Blocks access to host OS files (/etc/passwd, C:\Windows, etc.)
$normalized = str_replace('\\', '/', $targetPath);
$realAppRoot = str_replace('\\', '/', realpath(APP_ROOT) ?: APP_ROOT);

// Check if requested path targets the H02 challenge secret specifically
$isChallengeSecret = (
    strpos($normalized, 'challenge-secrets') !== false ||
    strpos($normalized, 'H02_shadow.txt') !== false
);

if ($isChallengeSecret) {
    $secretFile = SECRETS_DIR . '/hard/H02_shadow.txt';
    if (file_exists($secretFile)) {
        header('Content-Type: text/plain; charset=utf-8');
        header('X-Security-Clearance: RESTRICTED-LEVEL-4');
        readfile($secretFile);
        exit;
    }
}

// If real file exists inside baseDocDir or app
if (file_exists($targetPath)) {
    $realTarget = str_replace('\\', '/', realpath($targetPath));
    if (strpos($realTarget, $realAppRoot) === 0) {
        $mime = mime_content_type($targetPath) ?: 'application/octet-stream';
        header('Content-Type: ' . $mime);
        readfile($targetPath);
        exit;
    }
}

// If not found or blocked
json_response([
    'status' => 'error',
    'message' => 'Document not found or access restricted by storage policy.',
    'path_evaluated' => $requestedFile
], 404);
