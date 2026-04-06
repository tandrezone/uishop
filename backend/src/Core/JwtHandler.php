<?php

declare(strict_types=1);

namespace App\Core;

use stdClass;

/**
 * JWT (JSON Web Token) handler
 * Simple JWT implementation (no external libraries)
 */
final class JwtHandler
{
    /**
     * Create a JWT token
     */
    public static function encode(array $payload): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + (int) Environment::get('JWT_EXPIRY', '86400');

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            Environment::get('JWT_SECRET', 'secret')
        );
        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    /**
     * Decode and verify a JWT token
     */
    public static function decode(string $token): ?stdClass
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;

        // Verify signature
        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            Environment::get('JWT_SECRET', 'secret')
        );
        $expectedSignature = self::base64UrlEncode($signature);

        if (!hash_equals($signatureEncoded, $expectedSignature)) {
            return null;
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded));

        if (!is_object($payload)) {
            return null;
        }

        // Check expiration
        if (isset($payload->exp) && $payload->exp < time()) {
            return null;
        }

        return $payload;
    }

    /**
     * Base64 URL encode
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     */
    private static function base64UrlDecode(string $data): string
    {
        $padding = 4 - (strlen($data) % 4);
        if ($padding !== 4) {
            $data .= str_repeat('=', $padding);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
