# Payment Flow

This document explains the complete payment flow for JazzCash hosted checkout.

## Payment Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Customer Initiates Payment               │
│                    (On Your Website)                        │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Your Application Collects Data                 │
│  - Amount                                                   │
│  - Bill Reference                                           │
│  - Product Description                                      │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Create JazzCash Instance                       │
│  $jazzcash = new JazzCash();                               │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Set Payment Details                            │
│  $jazzcash->setAmount()                                    │
│  $jazzcash->setBillReference()                             │
│  $jazzcash->setProductDescription()                        │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Validate Payment Data                          │
│  - Amount > 0                                              │
│  - Bill Reference not empty                                 │
│  - Product Description not empty                           │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Build Payment Parameters                       │
│  - Generate transaction reference                          │
│  - Set transaction date/time                               │
│  - Set expiry date/time                                    │
│  - Calculate amount in paisa                               │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Generate Secure Hash                           │
│  - Build hash array                                         │
│  - Generate SHA256 HMAC hash                               │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Generate HTML Form                             │
│  - Create form with all parameters                         │
│  - Add auto-submit JavaScript                              │
│  - Escape HTML for security                                │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Return HTML Form                               │
│  - Form auto-submits to JazzCash                           │
│  - Customer redirected to JazzCash                         │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Customer on JazzCash Page                      │
│  - Enter payment details                                    │
│  - Complete payment                                         │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              JazzCash Processes Payment                     │
│  - Validates payment                                        │
│  - Processes transaction                                    │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Redirect to Callback URL                       │
│  - With response parameters                                │
│  - With secure hash                                         │
└───────────────────────┬─────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────────────┐
│              Your Application Handles Callback             │
│  - Verify hash                                              │
│  - Check response code                                      │
│  - Update order status                                      │
│  - Send confirmation                                        │
└─────────────────────────────────────────────────────────────┘
```

## Step-by-Step Flow

### Step 1: Customer Initiates Payment

Customer clicks "Pay with JazzCash" button on your website.

### Step 2: Collect Payment Data

```php
$amount = 1000.00;
$billReference = 'ORDER-' . time();
$productDescription = 'Product Purchase';
```

### Step 3: Create JazzCash Instance

```php
$jazzcash = new JazzCash();
```

### Step 4: Set Payment Details

```php
$jazzcash->setAmount($amount)
    ->setBillReference($billReference)
    ->setProductDescription($productDescription);
```

### Step 5: Send Request

```php
return $jazzcash->sendRequest();
```

This internally:
1. Validates payment data
2. Builds payment parameters
3. Generates secure hash
4. Creates HTML form
5. Returns response

### Step 6: Form Auto-Submits

The returned HTML form automatically submits to JazzCash payment gateway.

### Step 7: Customer Completes Payment

Customer enters payment details on JazzCash secure page.

### Step 8: JazzCash Processes Payment

JazzCash validates and processes the payment.

### Step 9: Callback Received

JazzCash redirects to your callback URL with response data.

### Step 10: Handle Callback

```php
public function handleCallback(Request $request)
{
    // Verify hash
    if (!$this->verifyHash($request->all())) {
        return view('payment.error');
    }

    // Check response code
    if ($request->input('pp_ResponseCode') === '000') {
        // Payment successful
        // Update order, send email, etc.
    } else {
        // Payment failed
    }
}
```

## Payment States

### Initial State
- Payment initiated
- Data collected
- Form generated

### Processing State
- Customer on JazzCash page
- Payment being processed

### Completed State
- Payment successful (code: 000)
- Order updated
- Confirmation sent

### Failed State
- Payment failed (code: 001)
- Order marked as failed
- Customer notified

### Cancelled State
- Payment cancelled (code: 002)
- Order marked as cancelled

## Transaction Reference Format

Transaction references are automatically generated:

```
Format: TR + YYYYMMDDHHMMSS + Random(10-100)
Example: TR20250115120000123
```

- Maximum 20 alphanumeric characters
- Unique per transaction
- Used for tracking

## Amount Format

Amounts are converted to paisa (smallest currency unit):

```
1000.00 PKR → 100000 (paisa)
```

Last two digits represent decimal places.

## Hash Generation

The secure hash is generated using:

1. Build hash array in specific order
2. Filter out empty/null values
3. Concatenate with hash key
4. Generate SHA256 HMAC hash

```php
$hash = hash_hmac('sha256', $sortedArray, $hashKey);
```

## Response Codes

| Code | Status | Action |
|------|--------|--------|
| `000` | Success | Update order as paid |
| `001` | Failed | Mark order as failed |
| `002` | Cancelled | Mark order as cancelled |
| `003` | Pending | Wait for confirmation |

## Security Flow

1. **Hash Generation**: Secure hash generated before sending
2. **HTTPS**: All communication over HTTPS
3. **Hash Verification**: Hash verified on callback
4. **Input Validation**: All inputs validated
5. **HTML Escaping**: All output escaped

## Error Handling Flow

```
Payment Request
    ↓
Validation
    ↓
[Invalid] → Return Error
    ↓
[Valid]
    ↓
Build Parameters
    ↓
Generate Hash
    ↓
Create Form
    ↓
Return Response
```

## Best Practices

1. **Generate unique bill references** - Use UUIDs or order IDs
2. **Store transaction references** - For tracking and reconciliation
3. **Verify hash on callback** - Never trust unverified callbacks
4. **Handle all response codes** - Not just success
5. **Log all transactions** - For audit trail
6. **Set appropriate expiry** - Default is 1 day
7. **Use HTTPS** - Always use HTTPS for callbacks

## Next Steps

- [Understanding Hosted Checkout](Understanding-Hosted-Checkout) - Detailed implementation
- [API Reference](API-Reference) - Method documentation
- [Troubleshooting](Troubleshooting) - Common issues

