# Understanding Hosted Checkout

## Introduction

The Hosted Checkout process for JazzCash provides a secure and convenient method for merchants to accept online payments. This guide explains how the hosted checkout works and how to implement it using the JazzCash Laravel package.

## Overview

Hosted Checkout redirects customers to JazzCash's secure payment page where they complete the payment. This method is ideal for merchants who want to avoid PCI DSS compliance requirements as card data is never handled on their servers.

## How It Works

```
1. Customer initiates payment on your website
   ↓
2. Your application creates payment request
   ↓
3. Generate secure hash
   ↓
4. Redirect customer to JazzCash payment page
   ↓
5. Customer completes payment on JazzCash
   ↓
6. JazzCash redirects back to your callback URL
   ↓
7. Verify payment and update order status
```

## Step-by-Step Implementation

### Step 1: Collect Payment Information

```php
$amount = 1000.00; // Transaction amount
$billReference = 'ORDER-' . time(); // Unique order reference
$productDescription = 'Product Purchase'; // Product description
```

### Step 2: Create JazzCash Instance

```php
use zfhassaan\JazzCash\JazzCash;

$jazzcash = new JazzCash();
```

### Step 3: Set Payment Details

```php
$jazzcash->setAmount($amount)
    ->setBillReference($billReference)
    ->setProductDescription($productDescription);
```

### Step 4: Send Request

```php
return $jazzcash->sendRequest();
```

The `sendRequest()` method:
1. Validates payment data
2. Builds payment parameters
3. Generates secure hash
4. Creates HTML form with auto-submit
5. Returns response with form

## Complete Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use zfhassaan\JazzCash\JazzCash;

class PaymentController extends Controller
{
    /**
     * Initiate payment
     */
    public function initiatePayment(Request $request)
    {
        // Validate request
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'billref' => 'required|string|max:255',
            'productDescription' => 'required|string|max:500',
        ]);

        try {
            // Create JazzCash instance
            $jazzcash = new JazzCash();
            
            // Set payment details
            $jazzcash->setAmount($request->amount)
                ->setBillReference($request->billref)
                ->setProductDescription($request->productDescription);

            // Send request - returns HTML form
            return $jazzcash->sendRequest();
            
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['payment' => $e->getMessage()]);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['payment' => 'Configuration error: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle payment callback
     */
    public function handleCallback(Request $request)
    {
        // Get response data
        $responseCode = $request->input('pp_ResponseCode');
        $responseMessage = $request->input('pp_ResponseMessage');
        $txnRefNo = $request->input('pp_TxnRefNo');
        $amount = $request->input('pp_Amount');
        $billReference = $request->input('pp_BillReference');
        $secureHash = $request->input('pp_SecureHash');

        // Verify hash (recommended for security)
        if (!$this->verifyHash($request->all())) {
            return view('payment.error', [
                'message' => 'Invalid payment response',
            ]);
        }

        // Process based on response code
        if ($responseCode === '000') {
            // Payment successful
            // Update order status
            // Send confirmation email
            // etc.
            
            return view('payment.success', [
                'transaction_id' => $txnRefNo,
                'amount' => $amount / 100, // Convert from paisa
                'bill_reference' => $billReference,
            ]);
        } else {
            // Payment failed
            return view('payment.failure', [
                'message' => $responseMessage,
                'code' => $responseCode,
            ]);
        }
    }

    /**
     * Verify callback hash
     */
    private function verifyHash(array $data): bool
    {
        // Rebuild hash array (same order as sent)
        $hashArray = [
            $data['pp_Amount'] ?? '',
            $data['pp_BankID'] ?? '',
            $data['pp_BillReference'] ?? '',
            $data['pp_Description'] ?? '',
            $data['pp_IsRegisteredCustomer'] ?? '',
            $data['pp_Language'] ?? '',
            $data['pp_MerchantID'] ?? '',
            $data['pp_Password'] ?? '',
            $data['pp_ProductID'] ?? '',
            $data['pp_ReturnURL'] ?? '',
            $data['pp_TxnCurrency'] ?? '',
            $data['pp_TxnDateTime'] ?? '',
            $data['pp_TxnExpiryDateTime'] ?? '',
            $data['pp_TxnRefNo'] ?? '',
            $data['pp_TxnType'] ?? '',
            $data['pp_Version'] ?? '',
            $data['ppmpf_1'] ?? '',
            $data['ppmpf_2'] ?? '',
            $data['ppmpf_3'] ?? '',
            $data['ppmpf_4'] ?? '',
            $data['ppmpf_5'] ?? '',
        ];

        $sortedArray = config('jazzcash.hash_key');
        foreach ($hashArray as $value) {
            if ($value !== 'undefined' && $value !== null && $value !== '') {
                $sortedArray .= '&' . $value;
            }
        }

        $expectedHash = hash_hmac('sha256', $sortedArray, config('jazzcash.hash_key'));
        
        return hash_equals($expectedHash, $data['pp_SecureHash'] ?? '');
    }
}
```

## Routes Setup

Add routes to `routes/web.php`:

```php
Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment']);
Route::get('/payment/callback', [PaymentController::class, 'handleCallback']);
Route::post('/payment/callback', [PaymentController::class, 'handleCallback']); // Some gateways use POST
```

## Payment Parameters

The package automatically generates the following parameters:

| Parameter | Description | Example |
|-----------|-------------|---------|
| `pp_Version` | API version | `2.0` |
| `pp_Language` | Language | `EN` |
| `pp_MerchantID` | Merchant ID | From config |
| `pp_Password` | Password | From config |
| `pp_TxnRefNo` | Transaction reference | `TR20250115120000123` |
| `pp_Amount` | Amount in paisa | `100000` (for 1000.00) |
| `pp_TxnCurrency` | Currency | `PKR` |
| `pp_TxnDateTime` | Transaction date/time | `20250115120000` |
| `pp_BillReference` | Bill reference | Your order ID |
| `pp_Description` | Description | Product description |
| `pp_IsRegisteredCustomer` | Registered customer | `No` |
| `pp_TxnExpiryDateTime` | Expiry date/time | `20250116120000` |
| `pp_ReturnURL` | Return URL | From config |
| `pp_SecureHash` | Secure hash | Generated hash |

## Response Codes

JazzCash returns the following response codes:

| Code | Status | Description |
|------|--------|-------------|
| `000` | Success | Payment successful |
| `001` | Failed | Payment failed |
| `002` | Cancelled | Payment cancelled by user |
| `003` | Pending | Payment pending |

## Security Considerations

1. **Always verify hash** - Verify the secure hash in callback
2. **Use HTTPS** - Ensure all payment URLs use HTTPS
3. **Validate all inputs** - Sanitize and validate callback data
4. **Log transactions** - Keep audit trail of all payment attempts
5. **Handle errors gracefully** - Don't expose sensitive information

## Testing

### Sandbox Testing

```env
JAZZCASH_PAYMENTMODE=sandbox
```

Use sandbox credentials for testing. Test cards and credentials are provided by JazzCash.

### Production

```env
JAZZCASH_PAYMENTMODE=production
```

Switch to production mode when ready. Ensure all credentials are correct.

## Best Practices

1. **Generate unique bill references** - Use order IDs or UUIDs
2. **Store transaction references** - Save `pp_TxnRefNo` for tracking
3. **Handle timeouts** - Set appropriate expiry times
4. **Verify callbacks** - Always verify hash on callback
5. **Update order status** - Mark orders as paid/failed appropriately

## Troubleshooting

### Form Not Submitting

- Check JavaScript is enabled
- Verify form HTML is correct
- Check browser console for errors

### Hash Verification Fails

- Ensure hash key is correct
- Verify parameter order matches
- Check for empty/null values

### Payment Not Processing

- Verify credentials are correct
- Check API URLs are correct
- Ensure return URL is accessible

## Next Steps

- [API Reference](API-Reference) - Complete API documentation
- [Payment Flow](Payment-Flow) - Payment flow diagram
- [Troubleshooting](Troubleshooting) - Common issues and solutions

