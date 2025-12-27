<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use zfhassaan\jazzcash\provider\ServiceProvider;

/**
 * Base Test Case for JazzCash Package
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Set test configuration
        config([
            'jazzcash.mode' => 'sandbox',
            'jazzcash.merchant_id' => 'test_merchant_id',
            'jazzcash.password' => 'test_password',
            'jazzcash.hash_key' => 'test_hash_key',
            'jazzcash.return_url' => 'https://example.com/callback',
            'jazzcash.sandbox_api_url' => 'https://sandbox.jazzcash.com.pk',
            'jazzcash.api_url' => 'https://jazzcash.com.pk',
        ]);
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Jazzcash' => \zfhassaan\JazzCash\Facade\JazzcashFacade::class,
        ];
    }
}

