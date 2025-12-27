<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use zfhassaan\JazzCash\JazzCash;

class PaymentFlowTest extends TestCase
{
    public function test_complete_payment_flow(): void
    {
        $jazzcash = new JazzCash();

        // Set payment details
        $jazzcash->setAmount(1000.50)
            ->setBillReference('ORDER-12345')
            ->setProductDescription('Test Product Purchase');

        // Send request
        $response = $jazzcash->sendRequest();

        // Verify response
        $this->assertNotNull($response);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);

        // Verify response contains form
        $content = $response->getContent();
        $this->assertStringContainsString('<form', $content);
        $this->assertStringContainsString('ORDER-12345', $content);
        $this->assertStringContainsString('100050', $content); // Amount * 100
    }

    public function test_payment_flow_with_method_chaining(): void
    {
        $response = (new JazzCash())
            ->setAmount(500)
            ->setBillReference('BILL-001')
            ->setProductDescription('Chained Method Test')
            ->sendRequest();

        $this->assertNotNull($response);
        $content = $response->getContent();
        $this->assertStringContainsString('BILL-001', $content);
    }

    public function test_payment_form_contains_all_required_fields(): void
    {
        $jazzcash = new JazzCash();
        $jazzcash->setAmount(100)
            ->setBillReference('TEST-123')
            ->setProductDescription('Test');

        $response = $jazzcash->sendRequest();
        $content = $response->getContent();

        // Check for required fields in form
        $this->assertStringContainsString('pp_Version', $content);
        $this->assertStringContainsString('pp_MerchantID', $content);
        $this->assertStringContainsString('pp_Amount', $content);
        $this->assertStringContainsString('pp_BillReference', $content);
        $this->assertStringContainsString('pp_Description', $content);
        $this->assertStringContainsString('pp_SecureHash', $content);
    }

    public function test_payment_hash_is_generated(): void
    {
        $jazzcash = new JazzCash();
        $jazzcash->setAmount(100)
            ->setBillReference('TEST-123')
            ->setProductDescription('Test');

        $response = $jazzcash->sendRequest();
        $content = $response->getContent();

        // Check that pp_SecureHash field exists in the HTML
        $this->assertStringContainsString('pp_SecureHash', $content, 'Secure hash field should be present in HTML');
        
        // Extract hash from HTML - handle both escaped and unescaped values
        // The hash is a hex string, so htmlspecialchars shouldn't change it, but let's be safe
        if (preg_match('/name="pp_SecureHash"[^>]*value="([^"]+)"/', $content, $matches)) {
            $hash = $matches[1];
            $this->assertNotEmpty($hash, 'Secure hash value should not be empty');
            $this->assertEquals(64, strlen($hash), 'Hash should be 64 characters (SHA256)');
        } else {
            // Try alternative pattern in case of different HTML structure
            $this->assertStringContainsString('id="pp_SecureHash"', $content, 'Secure hash input field should exist');
            // If we can't extract with regex, at least verify the field exists
            $this->fail('Could not extract hash value from HTML, but field exists');
        }
    }
}

