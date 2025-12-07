# Testing Guide

This guide covers testing the JazzCash package, including unit tests, feature tests, and best practices.

## Overview

The JazzCash package includes a comprehensive test suite covering:
- Payment class functionality
- JazzCash class functionality
- Service provider registration
- Complete payment flows
- Error handling
- Validation

## Test Structure

```
tests/
├── TestCase.php                    # Base test case
├── Unit/
│   ├── PaymentTest.php            # Payment class tests
│   ├── JazzCashTest.php           # JazzCash class tests
│   └── ServiceProviderTest.php    # Service provider tests
└── Feature/
    └── PaymentFlowTest.php         # Integration tests
```

## Running Tests

### Run All Tests

```bash
cd packages/zfhassaan/jazzcash
composer install
./vendor/bin/phpunit
```

### Run Specific Test Suite

```bash
# Unit tests only
./vendor/bin/phpunit tests/Unit

# Feature tests only
./vendor/bin/phpunit tests/Feature
```

### Run Specific Test Class

```bash
./vendor/bin/phpunit tests/Unit/PaymentTest.php
```

### Run with Coverage

```bash
./vendor/bin/phpunit --coverage
```

## Test Categories

### Unit Tests

#### PaymentTest

Tests for the base `Payment` class:

- `test_can_set_and_get_amount()` - Set and get amount
- `test_can_set_amount_as_string()` - String amount conversion
- `test_can_set_amount_as_integer()` - Integer amount
- `test_set_amount_throws_exception_for_negative_values()` - Negative amount validation
- `test_can_set_and_get_bill_reference()` - Bill reference
- `test_get_bill_reference_alias_works()` - Backward compatibility
- `test_can_set_and_get_product_description()` - Product description
- `test_can_set_and_get_api_url()` - API URL
- `test_can_set_and_get_refund_api_url()` - Refund URL
- `test_method_chaining_works()` - Method chaining
- `test_hash_array_generates_hash()` - Hash generation
- `test_validate_payment_data_*()` - Validation tests

#### JazzCashTest

Tests for the main `JazzCash` class:

- `test_can_set_amount()` - Amount setting
- `test_can_set_bill_reference()` - Bill reference
- `test_can_set_product_description()` - Product description
- `test_send_request_throws_exception_*()` - Error handling
- `test_send_request_returns_response_with_valid_data()` - Success case
- `test_render_page_generates_html_form()` - HTML generation
- `test_render_page_escapes_html_special_characters()` - XSS protection
- `test_render_page_includes_auto_submit_script()` - Auto-submit
- `test_generate_transaction_reference_format()` - Reference format
- `test_build_payment_data_includes_all_required_fields()` - Data building

#### ServiceProviderTest

Tests for service provider:

- `test_service_provider_registers_jazzcash_singleton()` - Registration
- `test_service_provider_merges_config()` - Config merging
- `test_facade_resolves_correctly()` - Facade access
- `test_config_can_be_published()` - Config publishing

### Feature Tests

#### PaymentFlowTest

Integration tests for complete payment flow:

- `test_complete_payment_flow()` - Full flow test
- `test_payment_flow_with_method_chaining()` - Method chaining
- `test_payment_form_contains_all_required_fields()` - Form validation
- `test_payment_hash_is_generated()` - Hash generation

## Test Configuration

The test suite uses `phpunit.xml` for configuration:

```xml
<php>
    <env name="APP_ENV" value="testing"/>
    <env name="JAZZCASH_PAYMENTMODE" value="sandbox"/>
    <env name="JAZZCASH_MERCHANTID" value="test_merchant_id"/>
    <!-- ... -->
</php>
```

## Writing New Tests

### Unit Test Example

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use zfhassaan\JazzCash\JazzCash;

class MyTest extends TestCase
{
    public function test_my_feature(): void
    {
        // Arrange
        $jazzcash = new JazzCash();
        
        // Act
        $result = $jazzcash->setAmount(100);
        
        // Assert
        $this->assertSame($jazzcash, $result);
        $this->assertEquals(100, $jazzcash->getAmount());
    }
}
```

### Feature Test Example

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use zfhassaan\JazzCash\JazzCash;

class MyFeatureTest extends TestCase
{
    public function test_complete_flow(): void
    {
        $jazzcash = new JazzCash();
        $jazzcash->setAmount(1000)
            ->setBillReference('TEST-123')
            ->setProductDescription('Test');
        
        $response = $jazzcash->sendRequest();
        
        $this->assertNotNull($response);
        $this->assertInstanceOf(\Illuminate\Http\Response::class, $response);
    }
}
```

## Test Data

Test data is configured in `TestCase::setUp()`:

```php
config([
    'jazzcash.mode' => 'sandbox',
    'jazzcash.merchant_id' => 'test_merchant_id',
    'jazzcash.password' => 'test_password',
    'jazzcash.hash_key' => 'test_hash_key',
    'jazzcash.return_url' => 'https://example.com/callback',
    'jazzcash.sandbox_api_url' => 'https://sandbox.jazzcash.com.pk',
    'jazzcash.api_url' => 'https://jazzcash.com.pk',
]);
```

## Mocking

For testing without making real API calls, you can mock responses:

```php
use Mockery;

$mock = Mockery::mock(SomeService::class);
$mock->shouldReceive('method')
    ->once()
    ->andReturn(['result' => 'data']);
```

## Best Practices

1. **Use descriptive test names**: `test_can_set_amount_with_valid_value()`
2. **Follow AAA pattern**: Arrange, Act, Assert
3. **Test edge cases**: Invalid inputs, failures, etc.
4. **Keep tests isolated**: Each test should be independent
5. **Use setUp/tearDown**: For common setup/cleanup
6. **Test both success and failure**: Cover all scenarios

## Continuous Integration

Tests should pass in CI/CD pipelines. Ensure:

- All dependencies are installed
- Environment variables are set
- No external API calls are made (use mocks)
- Database is not required (package doesn't use DB)

## Troubleshooting

### Tests failing due to configuration

Ensure test configuration is set in `TestCase::setUp()`:

```php
protected function setUp(): void
{
    parent::setUp();
    // Set test config here
}
```

### Mock not working

Ensure you call `Mockery::close()` in `tearDown()`:

```php
protected function tearDown(): void
{
    Mockery::close();
    parent::tearDown();
}
```

## Test Coverage

Current test coverage includes:

- All public methods
- Error handling
- Validation
- Hash generation
- HTML generation
- Service provider
- Facade access

## Next Steps

- [API Reference](API-Reference) - Method documentation
- [Payment Flow](Payment-Flow) - Payment processing
- [Troubleshooting](Troubleshooting) - Common issues

