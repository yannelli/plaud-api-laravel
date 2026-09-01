<?php

use Yannelli\LaravelPlaud\Support\Jwt;

describe('Jwt', function () {
    it('decodes claims without verifying the signature', function () {
        $token = fakePlaudJwt(['sub' => 'user-1', 'region' => 'aws:eu-central-1', 'exp' => 1700000000]);

        expect(Jwt::decodeClaims($token)['sub'])->toBe('user-1')
            ->and(Jwt::region($token))->toBe('aws:eu-central-1')
            ->and(Jwt::expiresAt($token))->toBe(1700000000)
            ->and(Jwt::isWorkspaceToken($token))->toBeFalse();
    });

    it('detects workspace tokens via ut_ref and wid claims', function () {
        $wt = fakePlaudJwt(['ut_ref' => 'user-token-id', 'wid' => 'ws-1', 'wtype' => 'personal']);

        expect(Jwt::isWorkspaceToken($wt))->toBeTrue();
    });

    it('returns empty claims for malformed tokens', function () {
        expect(Jwt::decodeClaims('not-a-jwt'))->toBe([])
            ->and(Jwt::region('nope'))->toBeNull()
            ->and(Jwt::isWorkspaceToken('nope'))->toBeFalse();
    });
});

function fakePlaudJwt(array $claims): string
{
    $encode = function (array $data): string {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    };

    return $encode(['alg' => 'none', 'typ' => 'JWT']).'.'.$encode($claims).'.sig';
}
