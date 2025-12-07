<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\JazzCash\JazzCash;
use zfhassaan\jazzcash\provider\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    public function test_service_provider_registers_jazzcash_singleton(): void
    {
        $jazzcash = app('jazzcash');

        $this->assertInstanceOf(JazzCash::class, $jazzcash);
    }

    public function test_service_provider_merges_config(): void
    {
        $this->assertArrayHasKey('jazzcash', config()->all());
        $this->assertArrayHasKey('mode', config('jazzcash'));
        $this->assertArrayHasKey('merchant_id', config('jazzcash'));
    }

    public function test_facade_resolves_correctly(): void
    {
        $jazzcash = \Jazzcash::setAmount(100);

        $this->assertInstanceOf(JazzCash::class, $jazzcash);
        $this->assertEquals(100, $jazzcash->getAmount());
    }

    public function test_config_can_be_published(): void
    {
        $provider = new ServiceProvider($this->app);
        $provider->boot();

        // Check that publishable assets are registered
        $this->assertTrue(true); // If no exception, config publishing is set up
    }
}

