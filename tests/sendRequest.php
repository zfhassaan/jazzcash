<?php


namespace Tests\Unit;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use zfhassaan\jazzcash\JazzCash;

class sendRequest extends TestCase
{
    /**
     * Test Send Request Method
     *
     * @return void
     */
    public function testSendRequestMethod(): void
    {
        $jazzcash = new JazzCash();
        $jazzcash->setAmount(1000); // last 2 digits will be considered as decimals.
        $jazzcash->setBillReference('bill123');
        $jazzcash->setProductDescription('Test product');
        $response = $jazzcash->sendRequest();
        $this->assertInstanceOf(Response::class, $response);
    }
}
