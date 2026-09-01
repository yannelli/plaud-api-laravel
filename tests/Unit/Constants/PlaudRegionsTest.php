<?php

use Yannelli\LaravelPlaud\Constants\PlaudRegions;

describe('PlaudRegions', function () {
    it('resolves short names and AWS region claims', function () {
        expect(PlaudRegions::resolve('eu'))->toBe(PlaudRegions::EU)
            ->and(PlaudRegions::resolve('apse1'))->toBe(PlaudRegions::APSE1)
            ->and(PlaudRegions::resolve('aws:eu-central-1'))->toBe(PlaudRegions::EU)
            ->and(PlaudRegions::resolve('https://api-euc1.plaud.ai/'))->toBe(PlaudRegions::EU);
    });

    it('maps JWT region claims', function () {
        expect(PlaudRegions::fromJwtRegion('aws:eu-central-1'))->toBe(PlaudRegions::EU)
            ->and(PlaudRegions::fromJwtRegion('aws:ap-southeast-1'))->toBe(PlaudRegions::APSE1)
            ->and(PlaudRegions::fromJwtRegion(null))->toBeNull();
    });

    it('accepts only plaud.ai hosts as redirect targets', function () {
        expect(PlaudRegions::isPlaudHost('https://api-euc1.plaud.ai'))->toBeTrue()
            ->and(PlaudRegions::isPlaudHost('evil.example.com'))->toBeFalse();
    });
});
