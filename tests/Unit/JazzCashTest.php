<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use Tests\TestCase;
use zfhassaan\JazzCash\JazzCash;

class JazzCashTest extends TestCase
{
    protected JazzCash $jazzcash;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jazzcash = new JazzCash();
    }

    public function test_can_set_amount(): void
    {
        $result = $this->jazzcash->setAmount(100);
        $this->assertSame($this->jazzcash, $result);
        $this->assertEquals(100, $this->jazzcash->getAmount());
    }

    public function test_can_set_bill_reference(): void
    {
        $result = $this->jazzcash->setBillReference('BILL-123');
        $this->assertSame($this->jazzcash, $result);
        $this->assertEquals('BILL-123', $this->jazzcash->getBillReference());
    }

    public function test_can_set_product_description(): void
    {
        $result = $this->jazzcash->setProductDescription('Test Product');
        $this->assertSame($this->jazzcash, $result);
        $this->assertEquals('Test Product', $this->jazzcash->getProductDescription());
    }

    public function test_send_request_throws_exception_without_amount(): void
    {
        $this->jazzcash->setBillReference('BILL-123');
        $this->jazzcash->setProductDescription('Test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $this->jazzcash->sendRequest();
    }

    public function test_send_request_throws_exception_without_bill_reference(): void
    {
        $this->jazzcash->setAmount(100);
        $this->jazzcash->setProductDescription('Test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bill reference is required');

        $this->jazzcash->sendRequest();
    }

    public function test_send_request_throws_exception_without_product_description(): void
    {
        $this->jazzcash->setAmount(100);
        $this->jazzcash->setBillReference('BILL-123');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product description is required');

        $this->jazzcash->sendRequest();
    }

    public function test_send_request_returns_response_with_valid_data(): void
    {
        $this->jazzcash->setAmount(100);
        $this->jazzcash->setBillReference('BILL-123');
        $this->jazzcash->setProductDescription('Test Product');

        $response = $this->jazzcash->sendRequest();

        $this->assertNotNull($response);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
    }

    public function test_render_page_generates_html_form(): void
    {
        $data = [
            'pp_Version' => '2.0',
            'pp_Amount' => '10000',
            'pp_MerchantID' => 'test_merchant',
        ];

        $html = $this->jazzcash->renderPage($data);

        $this->assertStringContainsString('<form', $html);
        $this->assertStringContainsString('id="jc-params"', $html);
        $this->assertStringContainsString('pp_Version', $html);
        $this->assertStringContainsString('pp_Amount', $html);
        $this->assertStringContainsString('pp_MerchantID', $html);
    }

    public function test_render_page_escapes_html_special_characters(): void
    {
        $data = [
            'pp_Description' => 'Test & Description <script>alert("xss")</script>',
        ];

        $html = $this->jazzcash->renderPage($data);

        // Check that user-provided script tag is escaped (not the auto-submit script)
        $this->assertStringNotContainsString('value="Test & Description <script>alert("xss")</script>"', $html);
        $this->assertStringContainsString('&amp;', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('&quot;xss&quot;', $html);
        // Verify the auto-submit script is still present (it should be)
        $this->assertStringContainsString('window.addEventListener("DOMContentLoaded"', $html);
    }

    public function test_render_page_includes_auto_submit_script(): void
    {
        $data = ['pp_Version' => '2.0'];
        $html = $this->jazzcash->renderPage($data);

        $this->assertStringContainsString('DOMContentLoaded', $html);
        $this->assertStringContainsString('getElementById("jc-params")', $html);
        $this->assertStringContainsString('.submit()', $html);
    }

    public function test_generate_transaction_reference_format(): void
    {
        $reflection = new \ReflectionClass($this->jazzcash);
        $method = $reflection->getMethod('generateTransactionReference');
        $method->setAccessible(true);

        $reference = $method->invoke($this->jazzcash);

        $this->assertStringStartsWith('TR', $reference);
        $this->assertGreaterThanOrEqual(15, strlen($reference));
        $this->assertLessThanOrEqual(20, strlen($reference));
    }

    public function test_get_transaction_date_time_format(): void
    {
        $reflection = new \ReflectionClass($this->jazzcash);
        $method = $reflection->getMethod('getTransactionDateTime');
        $method->setAccessible(true);

        $dateTime = $method->invoke($this->jazzcash);

        $this->assertEquals(14, strlen($dateTime));
        $this->assertMatchesRegularExpression('/^\d{14}$/', $dateTime);
    }

    public function test_get_transaction_expiry_date_time_format(): void
    {
        $reflection = new \ReflectionClass($this->jazzcash);
        $method = $reflection->getMethod('getTransactionExpiryDateTime');
        $method->setAccessible(true);

        $expiryDateTime = $method->invoke($this->jazzcash);

        $this->assertEquals(14, strlen($expiryDateTime));
        $this->assertMatchesRegularExpression('/^\d{14}$/', $expiryDateTime);
    }

    public function test_build_payment_data_includes_all_required_fields(): void
    {
        $this->jazzcash->setAmount(100);
        $this->jazzcash->setBillReference('BILL-123');
        $this->jazzcash->setProductDescription('Test Product');

        $reflection = new \ReflectionClass($this->jazzcash);
        $method = $reflection->getMethod('buildPaymentData');
        $method->setAccessible(true);

        $data = $method->invoke($this->jazzcash);

        $requiredFields = [
            'pp_Version',
            'pp_Language',
            'pp_MerchantID',
            'pp_Password',
            'pp_TxnRefNo',
            'pp_Amount',
            'pp_TxnCurrency',
            'pp_TxnDateTime',
            'pp_BillReference',
            'pp_Description',
            'pp_IsRegisteredCustomer',
            'pp_TxnExpiryDateTime',
            'pp_ReturnURL',
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $data, "Missing required field: {$field}");
        }

        $this->assertEquals('2.0', $data['pp_Version']);
        $this->assertEquals('EN', $data['pp_Language']);
        $this->assertEquals('PKR', $data['pp_TxnCurrency']);
        $this->assertEquals('No', $data['pp_IsRegisteredCustomer']);
        $this->assertEquals(10000, $data['pp_Amount']); // Amount * 100
        $this->assertEquals('BILL-123', $data['pp_BillReference']);
    }
}

