<?php
/**
 * PROJECT: BLACKOUT — E Corp Telemetry & Diagnostics API
 * Implements M06 (Forgotten Endpoint)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Telemetry-Build: v2.1.8-alpha-deprecated');

$mode = $_GET['mode'] ?? 'summary';

$flag_m06 = get_challenge_flag('M06') ?: 'Flagfound{forgotten_endpoint_318}';

json_response([
    'status' => 'success',
    'system' => 'E Corp Distributed Gateway Telemetry Node',
    'cluster' => 'us-east-datacenter-9',
    'timestamp' => time(),
    'deprecated_endpoint_notice' => 'This legacy telemetry collector is pending decommissioning in Q4.',
    'challenge' => 'M06',
    'flag' => $flag_m06,
    'telemetry' => [
        'uptime_seconds' => 1482910,
        'active_workers' => 16,
        'memory_usage_mb' => 412.8,
        'network_ingress_mbps' => 842.1,
        'unresolved_debug_tokens' => [
            'LEGACY_SYS_REPAIR' => $flag_m06
        ]
    ]
]);
