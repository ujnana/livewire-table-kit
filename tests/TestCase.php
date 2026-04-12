<?php

declare(strict_types=1);

namespace Unlab\LivewireTableKit\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Unlab\LivewireTableKit\LivewireTableKitServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireTableKitServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
    }
}
