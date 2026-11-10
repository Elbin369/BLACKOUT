<?php
/**
 * PROJECT: BLACKOUT — E Corp Enterprise GraphQL Gateway
 * Implements A03 (GraphQL Mystery — GraphQL Security Misconfiguration)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$input = get_json_input();
if (empty($input)) {
    $input = $_POST;
}

$query = $input['query'] ?? $_GET['query'] ?? '';

if (empty($query)) {
    json_response([
        'data' => null,
        'errors' => [
            ['message' => 'GraphQL query is required. Send POST JSON: {"query": "..."}']
        ],
        'hint' => 'Full GraphQL introspection is enabled on this gateway.'
    ], 400);
}

// 1. Handle Introspection Queries (__schema, __type)
if (strpos($query, '__schema') !== false || strpos($query, '__type') !== false) {
    json_response([
        'data' => [
            '__schema' => [
                'queryType' => ['name' => 'Query'],
                'mutationType' => null,
                'subscriptionType' => null,
                'types' => [
                    [
                        'name' => 'Query',
                        'kind' => 'OBJECT',
                        'fields' => [
                            [
                                'name' => 'employees',
                                'description' => 'List public corporate staff',
                                'args' => [],
                                'type' => ['name' => 'EmployeeList']
                            ],
                            [
                                'name' => 'systemStatus',
                                'description' => 'Cluster operational status',
                                'args' => [],
                                'type' => ['name' => 'ClusterStatus']
                            ],
                            [
                                'name' => '_internalSecurityVault',
                                'description' => 'RESTRICTED: Internal operational vault node. Misconfigured resolver.',
                                'args' => [
                                    ['name' => 'secretKey', 'type' => ['name' => 'String']],
                                    ['name' => 'authBypass', 'type' => ['name' => 'Boolean']]
                                ],
                                'type' => ['name' => 'SecurityVault']
                            ]
                        ]
                    ],
                    [
                        'name' => 'SecurityVault',
                        'kind' => 'OBJECT',
                        'fields' => [
                            ['name' => 'vaultId', 'type' => ['name' => 'String']],
                            ['name' => 'classification', 'type' => ['name' => 'String']],
                            ['name' => 'vaultKey', 'type' => ['name' => 'String']],
                            ['name' => 'flag', 'type' => ['name' => 'String']]
                        ]
                    ]
                ]
            ]
        ]
    ]);
}

// 2. Resolver Logic
$data = [];

if (strpos($query, '_internalSecurityVault') !== false) {
    // VULNERABILITY A03: Misconfigured resolver returns sensitive field without authentication
    $a03_flag = get_challenge_flag('A03') ?: 'Flagfound{graphql_mystery_512}';
    $data['_internalSecurityVault'] = [
        'vaultId' => 'VAULT-ALPHA-99',
        'classification' => 'TOP SECRET // PROJECT BLACKOUT',
        'vaultKey' => 'GQL_RESOLVER_UNRESTRICTED_ACCESS',
        'flag' => $a03_flag,
        'message' => 'GraphQL field-level authorization check omitted in resolver configuration.'
    ];
}

if (strpos($query, 'employees') !== false) {
    $data['employees'] = [
        ['id' => 1, 'name' => 'John Doe', 'role' => 'Support Engineer'],
        ['id' => 2, 'name' => 'Sarah Connor', 'role' => 'Systems Analyst'],
        ['id' => 3, 'name' => 'Alden Cross', 'role' => 'Infrastructure Director']
    ];
}

if (strpos($query, 'systemStatus') !== false) {
    $data['systemStatus'] = [
        'cluster' => 'ecorp-graphql-primary',
        'nodes' => 12,
        'version' => 'Apollo-Federation-v3'
    ];
}

if (empty($data)) {
    json_response([
        'data' => null,
        'errors' => [
            ['message' => 'Cannot query field. Introspect schema via { __schema { types { name } } }']
        ]
    ]);
}

json_response(['data' => $data]);
