<?php

use Yannelli\LaravelPlaud\Models\DataState;
use Yannelli\LaravelPlaud\Models\NextDatum;

describe('DataState', function () {
    it('can be instantiated with required properties', function () {
        $state = new DataState(
            isBind: 1,
            isMembership: 1,
            autorenewStatusIos: true,
            autorenewStatusAndroid: false,
            autorenewStatusWeb: true,
            membershipFlag: 'pro',
            membershipType: 'annual'
        );

        expect($state->isBind)->toBe(1)
            ->and($state->isMembership)->toBe(1)
            ->and($state->autorenewStatusIos)->toBeTrue()
            ->and($state->autorenewStatusAndroid)->toBeFalse()
            ->and($state->autorenewStatusWeb)->toBeTrue()
            ->and($state->membershipFlag)->toBe('pro')
            ->and($state->membershipType)->toBe('annual')
            ->and($state->nextData)->toBe([]);
    });

    it('can be instantiated with next data', function () {
        $nextData = [
            new NextDatum(
                membershipType: 'premium',
                membershipMonths: 12,
                membershipSeconds: 31536000,
                periodCurr: 1,
                periodLeft: 11,
                secondsLeft: 2880000,
                secondsTotal: 3600000,
                autorenewStatus: 1
            ),
        ];

        $state = new DataState(
            isBind: 1,
            isMembership: 1,
            autorenewStatusIos: false,
            autorenewStatusAndroid: false,
            autorenewStatusWeb: true,
            membershipFlag: 'premium',
            membershipType: 'monthly',
            nextData: $nextData
        );

        expect($state->nextData)->toHaveCount(1)
            ->and($state->nextData[0]->membershipType)->toBe('premium');
    });

    it('can be created from array', function () {
        $data = [
            'is_bind' => 1,
            'is_membership' => 2,
            'autorenew_status_ios' => true,
            'autorenew_status_android' => true,
            'autorenew_status_web' => false,
            'membership_flag' => 'enterprise',
            'membership_type' => 'yearly',
            'next_data' => [
                [
                    'membership_type' => 'enterprise',
                    'membership_months' => 24,
                    'membership_seconds' => 63072000,
                    'period_curr' => 0,
                    'period_left' => 24,
                    'seconds_left' => 5000000,
                    'seconds_total' => 10000000,
                    'autorenew_status' => 1,
                ],
            ],
            'is_free_trial_now' => 0,
            'is_free_trial_history' => 1,
            'is_inner' => true,
            'is_outer' => false,
            'is_gray' => false,
            'is_virtual' => false,
            'created_at' => 1704067200000,
        ];

        $state = DataState::fromArray($data);

        expect($state->isBind)->toBe(1)
            ->and($state->isMembership)->toBe(2)
            ->and($state->autorenewStatusIos)->toBeTrue()
            ->and($state->membershipType)->toBe('yearly')
            ->and($state->nextData)->toHaveCount(1)
            ->and($state->nextData[0])->toBeInstanceOf(NextDatum::class)
            ->and($state->isFreeTrialHistory)->toBe(1)
            ->and($state->isInner)->toBeTrue();
    });

    it('handles missing keys with default values', function () {
        $state = DataState::fromArray([]);

        expect($state->isBind)->toBe(0)
            ->and($state->isMembership)->toBe(0)
            ->and($state->autorenewStatusIos)->toBeFalse()
            ->and($state->membershipFlag)->toBe('')
            ->and($state->membershipType)->toBe('')
            ->and($state->nextData)->toBe([])
            ->and($state->isFreeTrialNow)->toBe(0);
    });

    it('converts to array correctly', function () {
        $state = new DataState(
            isBind: 1,
            isMembership: 1,
            autorenewStatusIos: false,
            autorenewStatusAndroid: true,
            autorenewStatusWeb: true,
            membershipFlag: 'basic',
            membershipType: 'monthly',
            isFreeTrialNow: 1,
            isInner: true
        );

        $array = $state->toArray();

        expect($array['is_bind'])->toBe(1)
            ->and($array['is_membership'])->toBe(1)
            ->and($array['autorenew_status_ios'])->toBeFalse()
            ->and($array['autorenew_status_android'])->toBeTrue()
            ->and($array['membership_flag'])->toBe('basic')
            ->and($array['membership_type'])->toBe('monthly')
            ->and($array['is_free_trial_now'])->toBe(1)
            ->and($array['is_inner'])->toBeTrue()
            ->and($array['next_data'])->toBe([]);
    });

    it('converts nested next_data to array', function () {
        $nextData = [
            new NextDatum(
                membershipType: 'test',
                membershipMonths: 1,
                membershipSeconds: 2592000,
                periodCurr: 1,
                periodLeft: 0,
                secondsLeft: 1000,
                secondsTotal: 2000,
                autorenewStatus: 0
            ),
        ];

        $state = new DataState(
            isBind: 1,
            isMembership: 1,
            autorenewStatusIos: false,
            autorenewStatusAndroid: false,
            autorenewStatusWeb: false,
            membershipFlag: '',
            membershipType: '',
            nextData: $nextData
        );

        $array = $state->toArray();

        expect($array['next_data'])->toHaveCount(1)
            ->and($array['next_data'][0])->toBeArray()
            ->and($array['next_data'][0]['membership_type'])->toBe('test');
    });
});
