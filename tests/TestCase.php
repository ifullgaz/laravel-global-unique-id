<?php

namespace Ifullgaz\GlobalUniqueId\Tests;

use Ifullgaz\GlobalUniqueId\GlobalUniqueIdServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            GlobalUniqueIdServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}