<?php

use Yannelli\LaravelPlaud\Models\DataUser;

describe('DataUser', function () {
    it('can be instantiated with minimal properties', function () {
        $user = new DataUser(id: 'user-123');

        expect($user->id)->toBe('user-123')
            ->and($user->nickname)->toBe('')
            ->and($user->email)->toBe('')
            ->and($user->emailVerified)->toBeFalse()
            ->and($user->membershipId)->toBe(0)
            ->and($user->secondsLeft)->toBe(0);
    });

    it('can be instantiated with all properties', function () {
        $user = new DataUser(
            id: 'user-456',
            avatar: 'https://example.com/avatar.jpg',
            nickname: 'John Doe',
            birthday: '1990-01-15',
            gender: 'male',
            country: 'US',
            address: '123 Main St',
            email: 'john@example.com',
            emailVerified: true,
            phone: '+1234567890',
            phoneVerified: true,
            membershipId: 2,
            startTime: 1704067200000,
            expireTime: 1735689600000,
            resetTime: 1706745600000,
            secondsLeft: 3600,
            secondsTotal: 7200,
            membershipIdTraffic: 1,
            startTimeTraffic: 1704067200000,
            expireTimeTraffic: 1735689600000,
            secondsLeftTraffic: 1800,
            secondsTotalTraffic: 3600
        );

        expect($user->nickname)->toBe('John Doe')
            ->and($user->email)->toBe('john@example.com')
            ->and($user->emailVerified)->toBeTrue()
            ->and($user->membershipId)->toBe(2)
            ->and($user->secondsLeft)->toBe(3600);
    });

    it('can be created from array', function () {
        $data = [
            'id' => 'user-789',
            'avatar' => 'https://cdn.example.com/avatar.png',
            'nickname' => 'Jane Doe',
            'email' => 'jane@example.com',
            'email_verified' => true,
            'membership_id' => 3,
            'seconds_left' => 5400,
            'seconds_total' => 10800,
            'membership_id_traffic' => 2,
            'seconds_left_traffic' => 2700,
            'seconds_total_traffic' => 5400,
        ];

        $user = DataUser::fromArray($data);

        expect($user->id)->toBe('user-789')
            ->and($user->nickname)->toBe('Jane Doe')
            ->and($user->email)->toBe('jane@example.com')
            ->and($user->emailVerified)->toBeTrue()
            ->and($user->membershipId)->toBe(3)
            ->and($user->secondsLeft)->toBe(5400);
    });

    it('handles missing keys with default values', function () {
        $user = DataUser::fromArray([]);

        expect($user->id)->toBe('')
            ->and($user->nickname)->toBe('')
            ->and($user->email)->toBe('')
            ->and($user->emailVerified)->toBeFalse()
            ->and($user->membershipId)->toBe(0)
            ->and($user->secondsLeft)->toBe(0);
    });

    it('converts to array correctly', function () {
        $user = new DataUser(
            id: 'arr-user',
            nickname: 'Array User',
            email: 'array@example.com',
            emailVerified: true,
            membershipId: 1,
            secondsLeft: 1000,
            secondsTotal: 2000
        );

        $array = $user->toArray();

        expect($array['id'])->toBe('arr-user')
            ->and($array['nickname'])->toBe('Array User')
            ->and($array['email'])->toBe('array@example.com')
            ->and($array['email_verified'])->toBeTrue()
            ->and($array['membership_id'])->toBe(1);
    });

    it('includes all expected keys in array output', function () {
        $user = new DataUser(id: 'test-user');
        $array = $user->toArray();

        expect($array)->toHaveKey('id')
            ->and($array)->toHaveKey('avatar')
            ->and($array)->toHaveKey('nickname')
            ->and($array)->toHaveKey('birthday')
            ->and($array)->toHaveKey('gender')
            ->and($array)->toHaveKey('country')
            ->and($array)->toHaveKey('address')
            ->and($array)->toHaveKey('email')
            ->and($array)->toHaveKey('email_verified')
            ->and($array)->toHaveKey('phone')
            ->and($array)->toHaveKey('phone_verified')
            ->and($array)->toHaveKey('membership_id')
            ->and($array)->toHaveKey('start_time')
            ->and($array)->toHaveKey('expire_time')
            ->and($array)->toHaveKey('reset_time')
            ->and($array)->toHaveKey('seconds_left')
            ->and($array)->toHaveKey('seconds_total')
            ->and($array)->toHaveKey('membership_id_traffic')
            ->and($array)->toHaveKey('start_time_traffic')
            ->and($array)->toHaveKey('expire_time_traffic')
            ->and($array)->toHaveKey('seconds_left_traffic')
            ->and($array)->toHaveKey('seconds_total_traffic');
    });
});
