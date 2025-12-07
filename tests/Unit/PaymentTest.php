<?php

declare(strict_types=1);

namespace Tests\Unit;

use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;
use zfhassaan\jazzcash\Payment;

class PaymentTest extends TestCase
{
    protected Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->payment = new Payment();
    }

    public function test_can_set_and_get_amount(): void
    {
        $this->payment->setAmount(100.50);
        $this->assertEquals(100.50, $this->payment->getAmount());
    }

    public function test_can_set_amount_as_string(): void
    {
        $this->payment->setAmount('100.50');
        $this->assertEquals(100.50, $this->payment->getAmount());
    }

    public function test_can_set_amount_as_integer(): void
    {
        $this->payment->setAmount(100);
        $this->assertEquals(100, $this->payment->getAmount());
    }

    public function test_set_amount_throws_exception_for_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive');
        $this->payment->setAmount(-100);
    }

    public function test_can_set_and_get_bill_reference(): void
    {
        $billRef = 'BILL-12345';
        $this->payment->setBillReference($billRef);
        $this->assertEquals($billRef, $this->payment->getBillReference());
    }

    public function test_get_bill_reference_alias_works(): void
    {
        $billRef = 'BILL-12345';
        $this->payment->setBillReference($billRef);
        // Test backward compatibility alias
        $this->assertEquals($billRef, $this->payment->getBillRefernce());
        $this->assertEquals($billRef, $this->payment->getBillReference());
    }

    public function test_can_set_and_get_product_description(): void
    {
        $description = 'Test Product';
        $this->payment->setProductDescription($description);
        $this->assertEquals($description, $this->payment->getProductDescription());
    }

    public function test_can_set_and_get_api_url(): void
    {
        $url = 'https://api.example.com';
        $this->payment->setApiUrl($url);
        $this->assertEquals($url, $this->payment->getApiUrl());
    }

    public function test_can_set_and_get_refund_api_url(): void
    {
        $url = 'https://refund.example.com';
        $this->payment->setRefundApiUrl($url);
        $this->assertEquals($url, $this->payment->getRefundApiUrl());
    }

    public function test_method_chaining_works(): void
    {
        $result = $this->payment
            ->setAmount(100)
            ->setBillReference('BILL-123')
            ->setProductDescription('Test');

        $this->assertSame($this->payment, $result);
        $this->assertEquals(100, $this->payment->getAmount());
        $this->assertEquals('BILL-123', $this->payment->getBillReference());
        $this->assertEquals('Test', $this->payment->getProductDescription());
    }

    public function test_hash_array_generates_hash(): void
    {
        $data = [
            'pp_Amount' => '10000',
            'pp_BankID' => '',
            'pp_BillReference' => 'BILL-123',
            'pp_Description' => 'Test',
            'pp_IsRegisteredCustomer' => 'No',
            'pp_Language' => 'EN',
            'pp_MerchantID' => 'test_merchant',
            'pp_Password' => 'test_password',
            'pp_ProductID' => '',
            'pp_ReturnURL' => 'https://example.com',
            'pp_TxnCurrency' => 'PKR',
            'pp_TxnDateTime' => '20250115120000',
            'pp_TxnExpiryDateTime' => '20250116120000',
            'pp_TxnRefNo' => 'TR123',
            'pp_TxnType' => '',
            'pp_Version' => '2.0',
            'ppmpf_1' => '',
            'ppmpf_2' => '',
            'ppmpf_3' => '',
            'ppmpf_4' => '',
            'ppmpf_5' => '',
        ];

        $hash = $this->payment->HashArray($data);
        $this->assertNotEmpty($hash);
        $this->assertIsString($hash);
        $this->assertEquals(64, strlen($hash)); // SHA256 produces 64 character hex string
    }

    public function test_validate_payment_data_passes_with_valid_data(): void
    {
        $this->payment->setAmount(100);
        $this->payment->setBillReference('BILL-123');
        $this->payment->setProductDescription('Test Product');

        // Should not throw exception
        $reflection = new \ReflectionClass($this->payment);
        $method = $reflection->getMethod('validatePaymentData');
        $method->setAccessible(true);
        $method->invoke($this->payment);
    }

    public function test_validate_payment_data_throws_for_zero_amount(): void
    {
        $this->payment->setAmount(0);
        $this->payment->setBillReference('BILL-123');
        $this->payment->setProductDescription('Test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');

        $reflection = new \ReflectionClass($this->payment);
        $method = $reflection->getMethod('validatePaymentData');
        $method->setAccessible(true);
        $method->invoke($this->payment);
    }

    public function test_validate_payment_data_throws_for_empty_bill_reference(): void
    {
        $this->payment->setAmount(100);
        $this->payment->setBillReference('');
        $this->payment->setProductDescription('Test');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Bill reference is required');

        $reflection = new \ReflectionClass($this->payment);
        $method = $reflection->getMethod('validatePaymentData');
        $method->setAccessible(true);
        $method->invoke($this->payment);
    }

    public function test_validate_payment_data_throws_for_empty_product_description(): void
    {
        $this->payment->setAmount(100);
        $this->payment->setBillReference('BILL-123');
        $this->payment->setProductDescription('');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Product description is required');

        $reflection = new \ReflectionClass($this->payment);
        $method = $reflection->getMethod('validatePaymentData');
        $method->setAccessible(true);
        $method->invoke($this->payment);
    }
}

