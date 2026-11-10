<?php
/**
 * PROJECT: BLACKOUT — E Corp Employee Perks & Reward Credits API
 * Implements A04 (Race Against Time — TOCTOU / Race Condition)
 */

require_once __DIR__ . '/../../application/config.php';
require_once __DIR__ . '/../../application/db.php';
require_once __DIR__ . '/../../application/helpers.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? 'status';
$userId = 1; // Default synthetic participant session (John Doe)

try {
    $pdo = Database::getConnection();

    // Reset action for quick retry
    if ($action === 'reset') {
        $pdo->prepare('UPDATE rewards SET credits = 10, coupons_claimed = 0, unlocked_secret = NULL WHERE user_id = :u')->execute([':u' => $userId]);
        json_response([
            'status' => 'success',
            'message' => 'Reward account balance reset to initial state: 10 credits, 0 bonus claims.'
        ]);
    }

    // Status action
    if ($action === 'status') {
        $stmt = $pdo->prepare('SELECT credits, coupons_claimed, vip_status, unlocked_secret FROM rewards WHERE user_id = :u');
        $stmt->execute([':u' => $userId]);
        $row = $stmt->fetch();

        if (!$row) {
            $pdo->prepare('INSERT INTO rewards (user_id, credits, coupons_claimed, vip_status) VALUES (:u, 10, 0, "Standard")')->execute([':u' => $userId]);
            $row = ['credits' => 10, 'coupons_claimed' => 0, 'vip_status' => 'Standard', 'unlocked_secret' => null];
        }

        json_response([
            'status' => 'success',
            'user_id' => $userId,
            'balance_credits' => intval($row['credits']),
            'bonus_claimed' => intval($row['coupons_claimed']) > 0,
            'available_actions' => [
                'claim_bonus' => 'Redeem one-time corporate welcome voucher (+10 credits)',
                'purchase_dossier' => 'Unlock Executive Security Dossier (Requires 20 credits)',
                'reset' => 'Reset challenge balance'
            ],
            'unlocked_secret' => $row['unlocked_secret']
        ]);
    }

    // VULNERABILITY A04: Non-Atomic Check-Then-Act (TOCTOU Race Condition)
    if ($action === 'claim_bonus') {
        // Step 1: Read current claim state
        $stmt = $pdo->prepare('SELECT credits, coupons_claimed FROM rewards WHERE user_id = :u');
        $stmt->execute([':u' => $userId]);
        $state = $stmt->fetch();

        if (!$state) {
            json_response(['status' => 'error', 'message' => 'User account not initialized.'], 404);
        }

        // Flawed check: read without row lock or transaction isolation
        if (intval($state['coupons_claimed']) > 0) {
            json_response([
                'status' => 'rejected',
                'message' => 'Bonus coupon has already been redeemed for this account.',
                'current_credits' => intval($state['credits'])
            ], 409);
        }

        // DELIBERATE RACE WINDOW: 120ms artificial processing latency
        usleep(120000);

        // Step 2: Increment credits and set claimed
        $newCredits = intval($state['credits']) + 10;
        $upd = $pdo->prepare('UPDATE rewards SET credits = :c, coupons_claimed = coupons_claimed + 1 WHERE user_id = :u');
        $upd->execute([':c' => $newCredits, ':u' => $userId]);

        json_response([
            'status' => 'success',
            'message' => 'Welcome coupon redeemed! +10 credits awarded.',
            'new_balance' => $newCredits
        ]);
    }

    // Purchase Dossier Action (Requires 20 credits)
    if ($action === 'purchase_dossier') {
        $stmt = $pdo->prepare('SELECT credits, coupons_claimed FROM rewards WHERE user_id = :u');
        $stmt->execute([':u' => $userId]);
        $state = $stmt->fetch();
        $credits = intval($state['credits'] ?? 0);

        if ($credits < 20) {
            json_response([
                'status' => 'insufficient_funds',
                'message' => "Insufficient credits: 20 required, current balance is {$credits}.",
                'hint' => 'You start with 10 credits and the one-time bonus voucher only gives 10... Unless you can claim it concurrently!'
            ], 402);
        }

        // Unlock dossier
        $a04_flag = get_challenge_flag('A04') ?: 'Flagfound{race_condition_374}';
        $pdo->prepare('UPDATE rewards SET credits = credits - 20, unlocked_secret = :s, vip_status = "Executive" WHERE user_id = :u')
            ->execute([':s' => $a04_flag, ':u' => $userId]);

        json_response([
            'status' => 'success',
            'message' => 'Executive Security Dossier purchased and unlocked.',
            'challenge' => 'A04',
            'flag' => $a04_flag,
            'dossier' => [
                'title' => 'Project Blackout Infrastructure Defense Dossier',
                'author' => 'SecOps Sovereign Governance Council',
                'flag' => $a04_flag
            ]
        ]);
    }

} catch (Exception $e) {
    json_response(['status' => 'error', 'message' => $e->getMessage()], 500);
}
