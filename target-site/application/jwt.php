<?php
/**
 * PROJECT: BLACKOUT — Synthetic JWT Library with Deliberate Flaws (H04)
 */

class SimpleJWT {
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public static function sign(array $payload, string $secret = JWT_SECRET_KEY, string $alg = 'HS256'): string {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        if (strtolower($alg) === 'none') {
            return "{$headerEncoded}.{$payloadEncoded}.";
        }

        $signature = hash_hmac('sha256', "{$headerEncoded}.{$payloadEncoded}", $secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "{$headerEncoded}.{$payloadEncoded}.{$signatureEncoded}";
    }

    /**
     * VULNERABLE VALIDATION:
     * Deliberately accepts 'none' algorithm if specified in header,
     * OR falls back to weak verification if secret matches 'ecorp_secret_2026'.
     */
    public static function verify(string $token, string $secret = JWT_SECRET_KEY): ?array {
        $parts = explode('.', $token);
        if (count($parts) < 2) {
            return null;
        }

        $headerJson = self::base64UrlDecode($parts[0]);
        $payloadJson = self::base64UrlDecode($parts[1]);
        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!$header || !$payload) {
            return null;
        }

        $alg = $header['alg'] ?? 'HS256';

        // VULNERABILITY H04: Broken Crown
        // 1. If alg is 'none' (case-insensitive), signature verification is bypassed!
        if (strtolower($alg) === 'none') {
            return $payload;
        }

        // 2. If signature part exists, check HMAC
        $signature = $parts[2] ?? '';
        $expectedSig = self::base64UrlEncode(hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $secret, true));

        if (hash_equals($expectedSig, $signature)) {
            return $payload;
        }

        return null;
    }
}
