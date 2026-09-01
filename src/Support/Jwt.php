<?php

namespace Yannelli\LaravelPlaud\Support;

/**
 * Decode Plaud JWTs without verifying the signature.
 *
 * Used only to read diagnostic claims (expiry, region, UT vs WT).
 * Never treat a successful decode as authentication.
 */
class Jwt
{
    /**
     * @return array<string, mixed>
     */
    public static function decodeClaims(string $token): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return [];
        }

        $payload = strtr($parts[1], '-_', '+/');
        $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);

        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            return [];
        }

        $claims = json_decode($decoded, true);

        return is_array($claims) ? $claims : [];
    }

    /**
     * Workspace tokens (WT) are short-lived (~24h) and scoped with `ut_ref` / `wid`.
     * User tokens (UT) are what mint WTs and should be stored long-term.
     */
    public static function isWorkspaceToken(string $token): bool
    {
        $claims = self::decodeClaims($token);

        return isset($claims['ut_ref']) || isset($claims['wid']);
    }

    public static function region(string $token): ?string
    {
        $region = self::decodeClaims($token)['region'] ?? null;

        return is_string($region) && $region !== '' ? $region : null;
    }

    public static function expiresAt(string $token): ?int
    {
        $exp = self::decodeClaims($token)['exp'] ?? null;

        return is_numeric($exp) ? (int) $exp : null;
    }
}
