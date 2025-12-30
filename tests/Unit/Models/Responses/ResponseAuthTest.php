<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseAuth;

describe('ResponseAuth', function () {
    it('can be instantiated with all properties', function () {
        $response = new ResponseAuth(
            status: 200,
            msg: 'success',
            accessToken: 'token-abc-123',
            tokenType: 'Bearer',
            loginCountPerHour: 5,
            loginTotalPerHour: 10
        );

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('success')
            ->and($response->accessToken)->toBe('token-abc-123')
            ->and($response->tokenType)->toBe('Bearer')
            ->and($response->loginCountPerHour)->toBe(5)
            ->and($response->loginTotalPerHour)->toBe(10);
    });

    it('can be created from array', function () {
        $data = [
            'status' => 200,
            'msg' => 'ok',
            'access_token' => 'my-access-token',
            'token_type' => 'Bearer',
            'login_count_per_hour' => 3,
            'login_total_per_hour' => 20,
        ];

        $response = ResponseAuth::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->msg)->toBe('ok')
            ->and($response->accessToken)->toBe('my-access-token')
            ->and($response->tokenType)->toBe('Bearer')
            ->and($response->loginCountPerHour)->toBe(3)
            ->and($response->loginTotalPerHour)->toBe(20);
    });

    it('handles missing keys with default values', function () {
        $response = ResponseAuth::fromArray([]);

        expect($response->status)->toBe(0)
            ->and($response->msg)->toBe('')
            ->and($response->accessToken)->toBe('')
            ->and($response->tokenType)->toBe('')
            ->and($response->loginCountPerHour)->toBe(0)
            ->and($response->loginTotalPerHour)->toBe(0);
    });

    it('converts to array correctly', function () {
        $response = new ResponseAuth(
            status: 200,
            msg: 'success',
            accessToken: 'token-xyz',
            tokenType: 'Bearer',
            loginCountPerHour: 1,
            loginTotalPerHour: 100
        );

        $array = $response->toArray();

        expect($array)->toBe([
            'status' => 200,
            'msg' => 'success',
            'access_token' => 'token-xyz',
            'token_type' => 'Bearer',
            'login_count_per_hour' => 1,
            'login_total_per_hour' => 100,
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'status' => 200,
            'msg' => 'authenticated',
            'access_token' => 'roundtrip-token',
            'token_type' => 'Bearer',
            'login_count_per_hour' => 7,
            'login_total_per_hour' => 50,
        ];

        $response = ResponseAuth::fromArray($originalData);
        $resultArray = $response->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
