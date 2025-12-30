<?php

namespace Yannelli\LaravelPlaud\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Yannelli\LaravelPlaud\PlaudServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            PlaudServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Plaud' => \Yannelli\LaravelPlaud\Facades\Plaud::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('plaud.access_token', 'test-token');
    }
}
