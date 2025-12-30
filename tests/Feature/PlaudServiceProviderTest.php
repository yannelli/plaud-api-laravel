<?php

use Yannelli\LaravelPlaud\PlaudClient;
use Yannelli\LaravelPlaud\PlaudService;
use Yannelli\LaravelPlaud\PlaudServiceProvider;
use Yannelli\LaravelPlaud\Facades\Plaud;
use Illuminate\Support\Facades\Config;
use Yannelli\LaravelPlaud\Tests\TestCase;

uses(TestCase::class);

describe('PlaudServiceProvider', function () {
    it('registers PlaudClient', function () {
        $client = app(PlaudClient::class);

        expect($client)->toBeInstanceOf(PlaudClient::class);
    });

    it('registers PlaudService', function () {
        $service = app(PlaudService::class);

        expect($service)->toBeInstanceOf(PlaudService::class);
    });

    it('registers plaud alias', function () {
        $service = app('plaud');

        expect($service)->toBeInstanceOf(PlaudService::class);
    });

    it('injects PlaudClient into PlaudService', function () {
        $service = app(PlaudService::class);

        expect($service->getClient())->toBeInstanceOf(PlaudClient::class);
    });

    it('configures access token from config', function () {
        // The TestCase sets the config value to 'test-token'
        $client = app(PlaudClient::class);

        expect($client->getAccessToken())->toBe('test-token');
    });

    it('provides expected services', function () {
        $provider = new PlaudServiceProvider(app());
        $provides = $provider->provides();

        expect($provides)->toContain(PlaudClient::class)
            ->and($provides)->toContain(PlaudService::class)
            ->and($provides)->toContain('plaud');
    });
});

describe('Plaud Facade', function () {
    it('resolves to PlaudService', function () {
        $resolvedClass = Plaud::getFacadeRoot();

        expect($resolvedClass)->toBeInstanceOf(PlaudService::class);
    });

    it('can access PlaudClient through facade', function () {
        $client = Plaud::getClient();

        expect($client)->toBeInstanceOf(PlaudClient::class);
    });

    it('can get access token through facade', function () {
        // The TestCase sets the config value to 'test-token'
        $token = Plaud::getAccessToken();

        expect($token)->toBe('test-token');
    });
});
