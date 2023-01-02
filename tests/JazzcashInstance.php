<?php


namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use zfhassaan\jazzcash\JazzCash;

class JazzcashInstance extends TestCase
{
    /**
     * Instanciate the jazzcash
     * @return void
     */
    public function testJazzCashClassInstantiation(): void
    {
        $jazzcash = new JazzCash();
        $this->assertInstanceOf(JazzCash::class, $jazzcash);
    }
}
