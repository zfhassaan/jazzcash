# Getting Started

This guide will help you get started with the JazzCash package by walking through basic usage examples.

## Basic Setup

After installation and configuration, you can start using the package. The package provides a simple API:

```php
use zfhassaan\JazzCash\JazzCash;
```

## Step 1: Create Payment Instance

Create a new instance of the JazzCash class:

```php
$jazzcash = new JazzCash();
```

Or use the facade (if registered):

```php
use Jazzcash;

$jazzcash = Jazzcash::setAmount(100);
```

## Step 2: Set Payment Details

Set the required payment information using method chaining:

```php
$jazzcash->setAmount(1000.00)
    ->setBillReference('ORDER-12345')
    ->setProductDescription('Product Purchase');
```

### Required Parameters

- **Amount**: Transaction amount (float, int, or string)
- **Bill Reference**: Unique order/bill reference (string)
- **Product Description**: Description of the product/service (string)

## Step 3: Send Payment Request

Send the payment request to JazzCash:

```php
return $jazzcash->sendRequest();
```

This will return an HTML form that automatically submits to JazzCash payment gateway.

## Complete Example

### Controller Implementation

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use zfhassaan\JazzCash\JazzCash;

class PaymentController extends Controller
{
    public function initiatePayment(Request $request)
    {
        // Validate request
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'billref' => 'required|string',
            'productDescription' => 'required|string',
        ]);

        // Create JazzCash instance
        $jazzcash = new JazzCash();
        
        // Set payment details
        $jazzcash->setAmount($request->amount)
            ->setBillReference($request->billref)
            ->setProductDescription($request->productDescription);

        // Send request and return HTML form
        return $jazzcash->sendRequest();
    }
}
```

### Route Setup

Add route to `routes/web.php`:

```php
Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment']);
```

### Frontend Form

Create a form to collect payment details:

```html
<form action="/payment/initiate" method="POST">
    @csrf
    <div>
        <label>Amount</label>
        <input type="number" name="amount" step="0.01" required>
    </div>
    <div>
        <label>Bill Reference</label>
        <input type="text" name="billref" required>
    </div>
    <div>
        <label>Product Description</label>
        <textarea name="productDescription" required></textarea>
    </div>
    <button type="submit">Pay with JazzCash</button>
</form>
```

## Using Facade

If you've registered the facade, you can use it directly:

```php
use Jazzcash;

public function initiatePayment(Request $request)
{
    return Jazzcash::setAmount($request->amount)
        ->setBillReference($request->billref)
        ->setProductDescription($request->productDescription)
        ->sendRequest();
}
```

## Handling Callback

After payment, JazzCash will redirect to your return URL. Handle the callback:

```php
public function handleCallback(Request $request)
{
    // Verify the response from JazzCash
    $ppResponseCode = $request->input('pp_ResponseCode');
    $ppResponseMessage = $request->input('pp_ResponseMessage');
    $ppTxnRefNo = $request->input('pp_TxnRefNo');
    $ppAmount = $request->input('pp_Amount');
    $ppBillReference = $request->input('pp_BillReference');
    $ppSecureHash = $request->input('pp_SecureHash');

    // Verify hash (recommended)
    // ... hash verification logic ...

    if ($ppResponseCode === '000') {
        // Payment successful
        // Update order status, send confirmation email, etc.
        return view('payment.success', [
            'transaction_id' => $ppTxnRefNo,
            'amount' => $ppAmount / 100, // Amount is in paisa
        ]);
    } else {
        // Payment failed
        return view('payment.failure', [
            'message' => $ppResponseMessage,
        ]);
    }
}
```

Add callback route:

```php
Route::get('/payment/callback', [PaymentController::class, 'handleCallback']);
```

## Method Chaining

The package supports fluent method chaining:

```php
$response = (new JazzCash())
    ->setAmount(1000)
    ->setBillReference('ORDER-123')
    ->setProductDescription('Product Purchase')
    ->sendRequest();
```

## Error Handling

Always handle potential errors:

```php
try {
    $jazzcash = new JazzCash();
    $jazzcash->setAmount($request->amount)
        ->setBillReference($request->billref)
        ->setProductDescription($request->productDescription);
    
    return $jazzcash->sendRequest();
} catch (\InvalidArgumentException $e) {
    return back()->withErrors(['payment' => $e->getMessage()]);
} catch (\RuntimeException $e) {
    return back()->withErrors(['payment' => 'Configuration error: ' . $e->getMessage()]);
}
```

## Response Format

The `sendRequest()` method returns an `Illuminate\Http\Response` containing an HTML form that automatically submits to JazzCash.

The form includes:
- All payment parameters as hidden fields
- Auto-submit JavaScript
- Secure hash for verification

## Next Steps

- [Understanding Hosted Checkout](Understanding-Hosted-Checkout) - Detailed checkout process
- [API Reference](API-Reference) - Complete API documentation
- [Payment Flow](Payment-Flow) - Payment flow diagram

