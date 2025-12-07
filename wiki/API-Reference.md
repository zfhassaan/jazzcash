# API Reference

Complete reference documentation for all JazzCash package methods and classes.

## Facade Access

All methods are accessible via the `Jazzcash` facade:

```php
use Jazzcash;
```

## Main Class: JazzCash

### Constructor

```php
$jazzcash = new JazzCash();
```

Creates a new JazzCash instance with configuration loaded from config file.

### setAmount()

Set the transaction amount.

```php
$jazzcash->setAmount(float|int|string $amount): static
```

**Parameters**:
- `$amount` (float|int|string) - Transaction amount

**Returns**: `static` - Returns self for method chaining

**Throws**: `InvalidArgumentException` if amount is negative

**Example**:
```php
$jazzcash->setAmount(1000.00);
$jazzcash->setAmount(1000);
$jazzcash->setAmount('1000.00');
```

### getAmount()

Get the transaction amount.

```php
$jazzcash->getAmount(): float|int
```

**Returns**: `float|int` - The transaction amount

**Example**:
```php
$amount = $jazzcash->getAmount();
```

### setBillReference()

Set the bill reference (order ID).

```php
$jazzcash->setBillReference(string $billref): static
```

**Parameters**:
- `$billref` (string) - Bill reference number

**Returns**: `static` - Returns self for method chaining

**Example**:
```php
$jazzcash->setBillReference('ORDER-12345');
```

### getBillReference()

Get the bill reference.

```php
$jazzcash->getBillReference(): string
```

**Returns**: `string` - Bill reference number

**Note**: This is the correctly spelled method. `getBillRefernce()` (with typo) also works for backward compatibility.

**Example**:
```php
$billRef = $jazzcash->getBillReference();
```

### getBillRefernce()

Get the bill reference (backward compatibility).

```php
$jazzcash->getBillRefernce(): string
```

**Returns**: `string` - Bill reference number

**Note**: This method has a typo but is kept for backward compatibility. Use `getBillReference()` instead.

### setProductDescription()

Set the product description.

```php
$jazzcash->setProductDescription(string $description): static
```

**Parameters**:
- `$description` (string) - Product description

**Returns**: `static` - Returns self for method chaining

**Example**:
```php
$jazzcash->setProductDescription('Product Purchase');
```

### getProductDescription()

Get the product description.

```php
$jazzcash->getProductDescription(): string
```

**Returns**: `string` - Product description

**Example**:
```php
$description = $jazzcash->getProductDescription();
```

### sendRequest()

Send payment request to JazzCash.

```php
$jazzcash->sendRequest(): \Illuminate\Http\Response
```

**Returns**: `\Illuminate\Http\Response` - HTML form response

**Throws**: 
- `InvalidArgumentException` if payment data is invalid
- `RuntimeException` if configuration is missing

**Example**:
```php
$response = $jazzcash->setAmount(1000)
    ->setBillReference('ORDER-123')
    ->setProductDescription('Product')
    ->sendRequest();

return $response;
```

## Base Class: Payment

The `Payment` class provides base functionality. Most methods are inherited by `JazzCash`.

### setApiUrl()

Set the API URL.

```php
$payment->setApiUrl(string $apiUrl): static
```

**Parameters**:
- `$apiUrl` (string) - API URL

**Returns**: `static` - Returns self for method chaining

### getApiUrl()

Get the API URL.

```php
$payment->getApiUrl(): string
```

**Returns**: `string` - API URL

### setRefundApiUrl()

Set the refund API URL.

```php
$payment->setRefundApiUrl(string $apiUrl): static
```

**Parameters**:
- `$apiUrl` (string) - Refund API URL

**Returns**: `static` - Returns self for method chaining

### getRefundApiUrl()

Get the refund API URL.

```php
$payment->getRefundApiUrl(): string
```

**Returns**: `string` - Refund API URL

### HashArray()

Generate secure hash for payment data.

```php
$payment->HashArray(array $data): string
```

**Parameters**:
- `$data` (array) - Payment data array

**Returns**: `string` - Generated hash (SHA256)

**Note**: This is an internal method but can be used for hash verification.

## Constants

### JazzCashConstants

```php
use zfhassaan\JazzCash\Constants\JazzCashConstants;

JazzCashConstants::VERSION; // '2.0'
JazzCashConstants::LANGUAGE; // 'EN'
JazzCashConstants::CURRENCY; // 'PKR'
JazzCashConstants::IS_REGISTERED_CUSTOMER; // 'No'
JazzCashConstants::DEFAULT_EXPIRY_DAYS; // 1
JazzCashConstants::TIMEZONE; // 'Asia/Karachi'
```

## Method Chaining

All setter methods support method chaining:

```php
$response = (new JazzCash())
    ->setAmount(1000)
    ->setBillReference('ORDER-123')
    ->setProductDescription('Product')
    ->sendRequest();
```

## Error Handling

### InvalidArgumentException

Thrown when:
- Amount is negative
- Required data is missing

```php
try {
    $jazzcash->setAmount(-100);
} catch (\InvalidArgumentException $e) {
    echo $e->getMessage(); // "Amount must be positive"
}
```

### RuntimeException

Thrown when:
- Configuration is missing
- API URL is not set

```php
try {
    $jazzcash = new JazzCash();
} catch (\RuntimeException $e) {
    echo $e->getMessage(); // "JazzCash configuration missing: ..."
}
```

## Response Format

The `sendRequest()` method returns an HTML form:

```html
<div id="header">
    <form id="jc-params" action="https://sandbox.jazzcash.com.pk/..." method="post">
        <input type="hidden" name="pp_Version" value="2.0" />
        <input type="hidden" name="pp_Amount" value="100000" />
        <!-- ... more fields ... -->
        <input type="hidden" name="pp_SecureHash" value="..." />
        <input style="display:none;" type="submit" value="Submit" />
    </form>
    <script>
        window.addEventListener("DOMContentLoaded", function() {
            document.getElementById("jc-params").submit();
        });
    </script>
</div>
```

## Best Practices

1. **Always validate input** before setting values
2. **Use method chaining** for cleaner code
3. **Handle exceptions** properly
4. **Verify hash** on callback
5. **Store transaction references** for tracking

## Next Steps

- [Payment Flow](Payment-Flow) - Understand payment processing
- [Understanding Hosted Checkout](Understanding-Hosted-Checkout) - Detailed checkout guide
- [Troubleshooting](Troubleshooting) - Common issues

