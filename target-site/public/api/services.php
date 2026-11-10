<?php
/**
 * PROJECT: BLACKOUT — E Corp Services & Solutions Catalog API
 * Implements M02 (Parameter Manipulator — URL Parameter Tampering)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$tier = strtolower($_GET['tier'] ?? 'standard');
$format = $_GET['format'] ?? 'json';

$servicesCatalog = [
    'standard' => [
        'name' => 'Standard Corporate Cloud Fabric',
        'sla' => '99.9% Uptime SLA',
        'security' => 'Standard TLS 1.3, DDoS Mitigation Layer 4',
        'storage' => '10 TB Encrypted Object Store',
        'support' => '8x5 Business Day Response'
    ],
    'enterprise' => [
        'name' => 'Enterprise High-Availability Grid',
        'sla' => '99.99% Multi-Region Failover',
        'security' => 'Dedicated Hardware HSM, Automated Intrusion Prevention',
        'storage' => '100 TB Geographically Replicated NVMe Fabric',
        'support' => '24/7/365 Dedicated SRE Pod'
    ],
    'government' => [
        'name' => 'Federal & Defense Sovereign Enclave',
        'sla' => '99.999% Air-Gapped Resiliency',
        'security' => 'FIPS 140-3 Level 4 Cryptographic Boundaries',
        'storage' => 'Petabyte Sovereign Data Vault',
        'support' => 'Top-Secret Cleared Operational Engineers'
    ],
    // M02 VULNERABILITY: Parameter Tampering Target
    'classified' => [
        'name' => 'Classified Darknet Defense Enclave (PROJECT BLACKOUT)',
        'sla' => 'Autonomous Quantum Real-Time Isolation',
        'security' => 'BLACKOUT Automated Countermeasure System',
        'flag' => get_challenge_flag('M02') ?: 'Flagfound{parameter_master_719}',
        'challenge' => 'M02',
        'directive' => 'Directive 9921: Parameter validation failed to restrict confidential tier enumeration.',
        'clearance' => 'LEVEL-5-EYES-ONLY'
    ]
];

// Also allow alias parameters like 'internal', 'executive', 'secret'
if (in_array($tier, ['internal', 'executive', 'secret', 'blackout'])) {
    $tier = 'classified';
}

if (!isset($servicesCatalog[$tier])) {
    json_response([
        'status' => 'error',
        'message' => 'Requested service tier unrecognized.',
        'available_public_tiers' => ['standard', 'enterprise', 'government']
    ], 404);
}

json_response([
    'status' => 'success',
    'requested_tier' => $tier,
    'details' => $servicesCatalog[$tier]
]);
