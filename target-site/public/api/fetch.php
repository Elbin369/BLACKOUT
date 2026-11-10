<?php
/**
 * PROJECT: BLACKOUT — E Corp Webhook & URL Integration Service
 * Implements A01 (Internal Affairs — SSRF)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$url = $_GET['url'] ?? '';
if (empty($url)) {
    $input = get_json_input();
    $url = $input['url'] ?? '';
}

if (empty($url)) {
    json_response([
        'status' => 'ready',
        'service' => 'E Corp Distributed Webhook & Resource Ingestion Proxy',
        'usage' => '/api/fetch.php?url=http://example.com/webhook',
        'policy' => 'Fetches remote health checks and partner telemetry payloads.'
    ]);
}

// Parse requested URL
$parsed = parse_url($url);
if (!$parsed || !isset($parsed['host'])) {
    json_response(['status' => 'error', 'message' => 'Malformed URL provided.'], 400);
}

$host = strtolower($parsed['host']);
$port = $parsed['port'] ?? ($parsed['scheme'] === 'https' ? 443 : 80);
$path = $parsed['path'] ?? '/';
if (isset($parsed['query'])) {
    $path .= '?' . $parsed['query'];
}

// Safe check: Enforce isolated challenge boundaries (Prevents touching real AWS/GCP metadata or external Internet)
$allowedSSRFHosts = ['127.0.0.1', 'localhost', 'mock-internal', MOCK_SSRF_HOST];

$isInternalTarget = in_array($host, $allowedSSRFHosts);

if (!$isInternalTarget) {
    // If participant tries to query external internet or real cloud 169.254.169.254
    json_response([
        'status' => 'blocked',
        'message' => 'Egress policy restriction: Only internal infrastructure cluster nodes are routable.',
        'hint' => 'Investigate internal microservice endpoints on port 8080 (e.g. mock-internal:8080 or 127.0.0.1:8080)'
    ], 403);
}

// Target is mock internal service!
// Make HTTP request to the internal microservice on port 8080
$targetServiceUrl = "http://" . MOCK_SSRF_HOST . ":" . MOCK_SSRF_PORT . $path;

// Try cURL or file_get_contents to the internal microservice
$ctx = stream_context_create([
    'http' => [
        'timeout' => 3,
        'user_agent' => 'ECorp-Internal-Webhook-Agent/2.4'
    ]
]);

$responseContent = @file_get_contents("http://127.0.0.1:" . MOCK_SSRF_PORT . $path, false, $ctx);
if ($responseContent === false && MOCK_SSRF_HOST !== '127.0.0.1') {
    $responseContent = @file_get_contents("http://" . MOCK_SSRF_HOST . ":" . MOCK_SSRF_PORT . $path, false, $ctx);
}

// If internal service is unreachable (e.g. running in offline test harness without the background daemon)
// fallback to internal mock response generator so the challenge works seamlessly in any test mode!
if ($responseContent === false) {
    if (strpos($path, 'flag') !== false) {
        $a01_flag = get_challenge_flag('A01') ?: 'Flagfound{internal_affairs_728}';
        $responseContent = json_encode([
            'status' => 'success',
            'node' => 'core-internal-cluster-alpha',
            'clearance' => 'RESTRICTED',
            'message' => 'Authorized internal cluster access verified.',
            'flag' => $a01_flag,
            'token' => 'INT-AFF-728-ROOT'
        ], JSON_PRETTY_PRINT);
    } elseif ($path === '/' || strpos($path, 'status') !== false) {
        $responseContent = json_encode([
            'service' => 'E Corp Internal Infrastructure Gateway',
            'status' => 'OPERATIONAL',
            'endpoints' => ['/internal/status', '/internal/system/flag', '/metadata/instance']
        ], JSON_PRETTY_PRINT);
    } else {
        $responseContent = json_encode([
            'service' => 'E Corp Internal Infrastructure Gateway',
            'error' => 'Path not found',
            'hint' => 'Check /internal/system/flag'
        ], JSON_PRETTY_PRINT);
    }
}

json_response([
    'status' => 'success',
    'fetched_url' => $url,
    'upstream_status' => 200,
    'data' => json_decode($responseContent, true) ?: $responseContent
]);
