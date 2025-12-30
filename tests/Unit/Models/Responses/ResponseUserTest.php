<?php

use Yannelli\LaravelPlaud\Models\Responses\ResponseUser;
use Yannelli\LaravelPlaud\Models\DataUser;
use Yannelli\LaravelPlaud\Models\DataState;

describe('ResponseUser', function () {
    it('can be instantiated with status only', function () {
        $response = new ResponseUser(status: 200);

        expect($response->status)->toBe(200)
            ->and($response->dataUser)->toBeNull()
            ->and($response->dataState)->toBeNull();
    });

    it('can be instantiated with all properties', function () {
        $dataUser = new DataUser(
            id: 'user-123',
            nickname: 'John Doe',
            email: 'john@example.com'
        );

        $dataState = new DataState(
            isBind: 1,
            isMembership: 1,
            autorenewStatusIos: true,
            autorenewStatusAndroid: false,
            autorenewStatusWeb: true,
            membershipFlag: 'pro',
            membershipType: 'annual'
        );

        $response = new ResponseUser(
            status: 200,
            dataUser: $dataUser,
            dataState: $dataState
        );

        expect($response->status)->toBe(200)
            ->and($response->dataUser)->toBeInstanceOf(DataUser::class)
            ->and($response->dataUser->nickname)->toBe('John Doe')
            ->and($response->dataState)->toBeInstanceOf(DataState::class)
            ->and($response->dataState->membershipType)->toBe('annual');
    });

    it('can be created from array with full data', function () {
        $data = [
            'status' => 200,
            'data_user' => [
                'id' => 'user-456',
                'nickname' => 'Jane Doe',
                'email' => 'jane@example.com',
                'email_verified' => true,
                'membership_id' => 2,
                'seconds_left' => 3600,
                'seconds_total' => 7200,
            ],
            'data_state' => [
                'is_bind' => 1,
                'is_membership' => 1,
                'autorenew_status_ios' => false,
                'autorenew_status_android' => true,
                'autorenew_status_web' => false,
                'membership_flag' => 'premium',
                'membership_type' => 'monthly',
            ],
        ];

        $response = ResponseUser::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->dataUser)->toBeInstanceOf(DataUser::class)
            ->and($response->dataUser->id)->toBe('user-456')
            ->and($response->dataUser->email)->toBe('jane@example.com')
            ->and($response->dataState)->toBeInstanceOf(DataState::class)
            ->and($response->dataState->isMembership)->toBe(1);
    });

    it('handles missing nested objects', function () {
        $data = ['status' => 200];

        $response = ResponseUser::fromArray($data);

        expect($response->status)->toBe(200)
            ->and($response->dataUser)->toBeNull()
            ->and($response->dataState)->toBeNull();
    });

    it('converts to array correctly', function () {
        $dataUser = new DataUser(
            id: 'user-789',
            nickname: 'Test User',
            email: 'test@example.com'
        );

        $response = new ResponseUser(
            status: 200,
            dataUser: $dataUser,
            dataState: null
        );

        $array = $response->toArray();

        expect($array)->toHaveKey('status')
            ->and($array)->toHaveKey('data_user')
            ->and($array)->toHaveKey('data_state')
            ->and($array['status'])->toBe(200)
            ->and($array['data_user']['nickname'])->toBe('Test User')
            ->and($array['data_state'])->toBeNull();
    });
});
