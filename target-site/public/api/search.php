<?php
/**
 * PROJECT: BLACKOUT — E Corp Search Service
 * Implements M05 (Reflected Nightmare — Reflected XSS)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

$query = $_GET['q'] ?? '';
$format = $_GET['format'] ?? (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false ? 'json' : 'html');

// Proof-of-Execution detection for safe CTF flag delivery
$isXssProof = false;
if (!empty($query)) {
    $lower = strtolower($query);
    if (
        strpos($lower, '<script') !== false ||
        strpos($lower, 'onerror=') !== false ||
        strpos($lower, 'onload=') !== false ||
        strpos($lower, '<svg') !== false ||
        strpos($lower, '<img') !== false ||
        strpos($lower, 'javascript:') !== false ||
        strpos($lower, 'alert(') !== false
    ) {
        $isXssProof = true;
    }
}

$m05_flag = get_challenge_flag('M05') ?: 'Flagfound{reflected_nightmare_963}';

if ($format === 'json') {
    json_response([
        'status' => 'success',
        'query_raw' => $query, // Intentionally unescaped reflection
        'results_count' => 0,
        'results' => [],
        'xss_proof_detected' => $isXssProof,
        'flag' => $isXssProof ? $m05_flag : null,
        'rendered_html' => '<div class="search-term">Search results for: ' . $query . '</div>'
    ]);
}

// Return rendered HTML with unescaped reflection
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results — E Corp Global Search</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/terminal.css">
</head>
<body class="bg-black text-white font-mono p-8">
    <div class="max-w-4xl mx-auto">
        <header class="border-b border-green-500 pb-4 mb-6 flex justify-between items-center">
            <h1 class="text-2xl text-green-400 font-bold tracking-wider">E CORP // GLOBAL SEARCH ENGINE</h1>
            <a href="/" class="text-xs text-gray-400 hover:text-green-400">[RETURN TO MAIN]</a>
        </header>

        <form action="/api/search.php" method="GET" class="mb-8 flex gap-2">
            <input type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Search corporate directories, press releases, reports..." class="flex-1 bg-black border border-green-500/50 px-4 py-2 text-green-300 focus:outline-none focus:border-green-400">
            <button type="submit" class="bg-green-900 border border-green-400 px-6 py-2 text-green-200 hover:bg-green-700">SEARCH</button>
        </form>

        <div class="results-container border border-gray-800 p-6 bg-black/60 rounded">
            <!-- VULNERABILITY M05: Deliberately reflected without output encoding -->
            <p class="text-gray-400 mb-4">Query reflection: <span id="query-reflection" class="text-yellow-400"><?php echo $query; ?></span></p>

            <?php if ($isXssProof): ?>
                <div class="p-4 my-4 border border-red-500 bg-red-950/40 text-red-300 rounded" id="xss-proof-banner">
                    <p class="font-bold text-red-400">[!] CONTROLLED XSS PROOF-OF-EXECUTION CONFIRMED</p>
                    <p class="text-sm mt-1">Reflected Cross-Site Scripting payload successfully rendered into document DOM without sanitization.</p>
                    <p class="mt-2 text-green-400 font-bold">RECOVERED FLAG: <code class="bg-black px-2 py-1 border border-green-500 select-all"><?php echo $m05_flag; ?></code></p>
                </div>
            <?php endif; ?>

            <?php if (empty($query)): ?>
                <p class="text-gray-500 italic">Enter search keywords to query the E Corp public information database.</p>
            <?php else: ?>
                <p class="text-gray-400">0 records matched your exact criteria in public archives.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
