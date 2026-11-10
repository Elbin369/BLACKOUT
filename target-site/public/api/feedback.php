<?php
/**
 * PROJECT: BLACKOUT — E Corp Feedback & Comments System
 * Implements H03 (Black Mirror — Stored XSS)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/db.php';
require_once __DIR__ . '/../../application/helpers.php';

$action = $_GET['action'] ?? ($_SERVER['REQUEST_METHOD'] === 'POST' ? 'submit' : 'list');

if ($action === 'submit') {
    $input = get_json_input();
    if (empty($input)) {
        $input = $_POST;
    }

    $name = trim($input['name'] ?? 'Anonymous Contractor');
    $email = trim($input['email'] ?? 'contractor@ecorp.corp');
    $comment = $input['comment'] ?? $input['message'] ?? '';

    if (empty($comment)) {
        json_response(['status' => 'error', 'message' => 'Comment text cannot be empty'], 400);
    }

    try {
        $pdo = Database::getConnection();
        // VULNERABILITY H03: Stored without output encoding or sanitization
        $stmt = $pdo->prepare('INSERT INTO comments (author_name, author_email, comment_text, status) VALUES (:n, :e, :c, "pending")');
        $stmt->execute([':n' => $name, ':e' => $email, ':c' => $comment]);
        $id = $pdo->lastInsertId();

        json_response([
            'status' => 'success',
            'message' => 'Feedback submitted. Awaiting automated SOC moderator review.',
            'comment_id' => $id,
            'moderator_endpoint' => '/api/feedback.php?action=moderator_review'
        ]);
    } catch (Exception $e) {
        json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

if ($action === 'list') {
    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT id, author_name, comment_text, created_at FROM comments ORDER BY id DESC LIMIT 20');
        $comments = $stmt->fetchAll();
        json_response(['status' => 'success', 'comments' => $comments]);
    } catch (Exception $e) {
        json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}

// Sandboxed Mock Moderator Review Endpoint
if ($action === 'moderator_review') {
    $h03_flag = get_challenge_flag('H03') ?: 'Flagfound{black_mirror_853}';

    try {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT id, author_name, author_email, comment_text, status FROM comments WHERE status = "pending" ORDER BY id DESC');
        $pending = $stmt->fetchAll();

        $xssTriggered = false;
        $triggeredId = null;

        foreach ($pending as $c) {
            $txt = strtolower($c['comment_text']);
            if (
                strpos($txt, '<script') !== false ||
                strpos($txt, 'onerror=') !== false ||
                strpos($txt, 'onload=') !== false ||
                strpos($txt, '<svg') !== false ||
                strpos($txt, '<img') !== false ||
                strpos($txt, 'javascript:') !== false
            ) {
                $xssTriggered = true;
                $triggeredId = $c['id'];
                // Update status to reviewed
                $upd = $pdo->prepare('UPDATE comments SET status = "executed_xss_verified", reviewed_by = "SOC_BOT_SANDBOX" WHERE id = :id');
                $upd->execute([':id' => $c['id']]);
                break;
            }
        }

        // Return HTML or JSON view of moderator console
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            json_response([
                'status' => 'success',
                'moderator_view' => 'active',
                'pending_count' => count($pending),
                'stored_xss_executed' => $xssTriggered,
                'flag' => $xssTriggered ? $h03_flag : null
            ]);
        }

        header('Content-Type: text/html; charset=utf-8');
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>SOC Sandboxed Review Console — E Corp</title>
            <link rel="stylesheet" href="/assets/css/style.css">
            <link rel="stylesheet" href="/assets/css/terminal.css">
        </head>
        <body class="bg-black text-gray-200 font-mono p-8">
            <div class="max-w-4xl mx-auto border border-gray-800 p-6 rounded bg-neutral-950">
                <h1 class="text-xl text-yellow-400 font-bold mb-4">[INTERNAL SOC REVIEW SANDBOX] — FEEDBACK QUEUE</h1>
                <p class="text-xs text-gray-400 mb-6">Automated headless environment simulating moderator content triage.</p>

                <?php if ($xssTriggered): ?>
                    <div class="p-4 mb-6 border border-green-500 bg-green-950/40 rounded">
                        <p class="text-green-400 font-bold">[+] STORED XSS PROOF-OF-EXECUTION DETECTED IN MODERATOR DOM CONTEXT</p>
                        <p class="text-sm mt-1 text-gray-300">Comment #<?php echo $triggeredId; ?> payload evaluated in virtual moderator browser instance.</p>
                        <p class="mt-2 text-green-300 font-bold">RECOVERED FLAG: <code class="bg-black px-2 py-1 border border-green-400"><?php echo $h03_flag; ?></code></p>
                    </div>
                <?php endif; ?>

                <div class="space-y-4">
                    <?php foreach ($pending as $c): ?>
                        <div class="p-3 border border-gray-800 rounded bg-black/50">
                            <span class="text-xs text-blue-400 font-bold"><?php echo htmlspecialchars($c['author_name']); ?></span>
                            <!-- Raw rendering inside the mock sandboxed view -->
                            <div class="mt-2 text-sm text-gray-300 comment-body"><?php echo $c['comment_text']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;

    } catch (Exception $e) {
        json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
}
