<?php

use Yannelli\LaravelPlaud\Models\NextDatum;

describe('NextDatum', function () {
    it('can be instantiated with all properties', function () {
        $datum = new NextDatum(
            membershipType: 'premium',
            membershipMonths: 12,
            membershipSeconds: 31536000,
            periodCurr: 3,
            periodLeft: 9,
            secondsLeft: 7200000,
            secondsTotal: 10000000,
            autorenewStatus: 1
        );

        expect($datum->membershipType)->toBe('premium')
            ->and($datum->membershipMonths)->toBe(12)
            ->and($datum->membershipSeconds)->toBe(31536000)
            ->and($datum->periodCurr)->toBe(3)
            ->and($datum->periodLeft)->toBe(9)
            ->and($datum->secondsLeft)->toBe(7200000)
            ->and($datum->secondsTotal)->toBe(10000000)
            ->and($datum->autorenewStatus)->toBe(1);
    });

    it('can be created from array', function () {
        $data = [
            'membership_type' => 'enterprise',
            'membership_months' => 24,
            'membership_seconds' => 63072000,
            'period_curr' => 6,
            'period_left' => 18,
            'seconds_left' => 14400000,
            'seconds_total' => 20000000,
            'autorenew_status' => 0,
        ];

        $datum = NextDatum::fromArray($data);

        expect($datum->membershipType)->toBe('enterprise')
            ->and($datum->membershipMonths)->toBe(24)
            ->and($datum->membershipSeconds)->toBe(63072000)
            ->and($datum->periodCurr)->toBe(6)
            ->and($datum->periodLeft)->toBe(18)
            ->and($datum->autorenewStatus)->toBe(0);
    });

    it('handles missing keys with default values', function () {
        $datum = NextDatum::fromArray([]);

        expect($datum->membershipType)->toBe('')
            ->and($datum->membershipMonths)->toBe(0)
            ->and($datum->membershipSeconds)->toBe(0)
            ->and($datum->periodCurr)->toBe(0)
            ->and($datum->periodLeft)->toBe(0)
            ->and($datum->secondsLeft)->toBe(0)
            ->and($datum->secondsTotal)->toBe(0)
            ->and($datum->autorenewStatus)->toBe(0);
    });

    it('converts to array correctly', function () {
        $datum = new NextDatum(
            membershipType: 'basic',
            membershipMonths: 1,
            membershipSeconds: 2592000,
            periodCurr: 1,
            periodLeft: 0,
            secondsLeft: 3600,
            secondsTotal: 7200,
            autorenewStatus: 1
        );

        $array = $datum->toArray();

        expect($array)->toBe([
            'membership_type' => 'basic',
            'membership_months' => 1,
            'membership_seconds' => 2592000,
            'period_curr' => 1,
            'period_left' => 0,
            'seconds_left' => 3600,
            'seconds_total' => 7200,
            'autorenew_status' => 1,
        ]);
    });

    it('roundtrips from array to array correctly', function () {
        $originalData = [
            'membership_type' => 'pro',
            'membership_months' => 6,
            'membership_seconds' => 15552000,
            'period_curr' => 2,
            'period_left' => 4,
            'seconds_left' => 5000000,
            'seconds_total' => 8000000,
            'autorenew_status' => 1,
        ];

        $datum = NextDatum::fromArray($originalData);
        $resultArray = $datum->toArray();

        expect($resultArray)->toBe($originalData);
    });
});
