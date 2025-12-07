# Security Best Practices

Security guidelines for using the JazzCash package safely and securely.

## Environment Variables

### Never Commit Credentials

**Bad**:
```php
// Don't hardcode credentials
$merchantId = '12345';
$password = 'secret_password';
$hashKey = 'secret_key';
```

**Good**:
```php
// Use environment variables
$merchantId = config('jazzcash.merchant_id');
$password = config('jazzcash.password');
$hashKey = config('jazzcash.hash_key');
```

### Secure .env File

1. **Never commit `.env`** to version control
2. **Use `.env.example`** for documentation
3. **Restrict file permissions**: `chmod 600 .env`
4. **Use different credentials** for sandbox and production
5. **Rotate credentials regularly**

## API Security

### Use HTTPS

Always use HTTPS for:
- API calls to JazzCash
- Payment callbacks
- Return URLs
- All payment-related URLs

```php
// Ensure HTTPS in production
if (app()->environment('production')) {
    URL::forceScheme('https');
}
```

### Validate All Inputs

Always validate user input:

```php
$request->validate([
    'amount' => 'required|numeric|min:0.01',
    'billref' => 'required|string|max:255|regex:/^[A-Za-z0-9\-_]+$/',
    'productDescription' => 'required|string|max:500',
]);
```

### Sanitize Data

Sanitize data before using:

```php
$billRef = htmlspecialchars($request->billref, ENT_QUOTES, 'UTF-8');
$description = filter_var($request->description, FILTER_SANITIZE_STRING);
```

## Hash Security

### Always Verify Hash on Callback

**Bad**:
```php
// Don't trust callback without verification
if ($request->input('pp_ResponseCode') === '000') {
    // Process payment
}
```

**Good**:
```php
// Always verify hash first
if ($this->verifyHash($request->all())) {
    if ($request->input('pp_ResponseCode') === '000') {
        // Process payment
    }
} else {
    // Reject callback
    Log::warning('Invalid hash in callback', ['ip' => $request->ip()]);
    return response()->json(['error' => 'Invalid request'], 403);
}
```

### Hash Verification Implementation

```php
private function verifyHash(array $data): bool
{
    // Rebuild hash array in same order as sent
    $hashArray = [
        $data['pp_Amount'] ?? '',
        $data['pp_BankID'] ?? '',
        $data['pp_BillReference'] ?? '',
        // ... all fields in order
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
```

## Callback Security

### Verify Callback Source

Consider implementing IP whitelisting:

```php
public function handleCallback(Request $request)
{
    $allowedIPs = [
        '203.0.113.0', // JazzCash IP 1
        '203.0.113.1', // JazzCash IP 2
        // Get actual IPs from JazzCash support
    ];

    if (!in_array($request->ip(), $allowedIPs)) {
        Log::warning('Callback from unauthorized IP', ['ip' => $request->ip()]);
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Process callback
}
```

### Disable CSRF for Callback

Add callback route to CSRF exceptions:

```php
// app/Http/Middleware/VerifyCsrfToken.php
protected $except = [
    'payment/callback',
    'api/payment/callback',
];
```

## Data Protection

### Don't Log Sensitive Data

**Bad**:
```php
Log::info('Payment data', [
    'password' => $password,
    'hash_key' => $hashKey,
]);
```

**Good**:
```php
Log::info('Payment initiated', [
    'amount' => $amount,
    'bill_reference' => $billRef,
    // Don't log sensitive data
]);
```

### Encrypt Sensitive Data

If storing payment data, encrypt it:

```php
use Illuminate\Support\Facades\Crypt;

// Encrypt
$encrypted = Crypt::encryptString($sensitiveData);

// Decrypt
$decrypted = Crypt::decryptString($encrypted);
```

## Error Handling

### Don't Expose Sensitive Information

**Bad**:
```php
catch (\Exception $e) {
    return response()->json([
        'error' => $e->getMessage(), // May contain sensitive info
        'trace' => $e->getTraceAsString(), // Security risk
    ]);
}
```

**Good**:
```php
catch (\Exception $e) {
    Log::error('Payment error', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    return response()->json([
        'error' => 'Payment processing failed. Please try again.',
    ], 500);
}
```

### Log Security Events

Log security-related events:

```php
Log::warning('Suspicious payment activity', [
    'ip' => $request->ip(),
    'user_agent' => $request->userAgent(),
    'bill_reference' => $billRef,
]);
```

## HTML Security

### HTML Escaping

The package automatically escapes HTML in form generation. However, if you're generating custom HTML:

```php
// Always escape output
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### XSS Prevention

The package includes HTML escaping in `renderPage()` method. This prevents XSS attacks if user input is used in payment data.

## Best Practices Summary

1. **Never commit credentials** - Use environment variables
2. **Use HTTPS** - Always encrypt data in transit
3. **Validate inputs** - Never trust user input
4. **Verify hash** - Always verify callback hash
5. **Log securely** - Don't log sensitive data
6. **Handle errors securely** - Don't expose sensitive info
7. **Use IP whitelisting** - For callbacks (optional)
8. **Rotate credentials** - Update keys periodically
9. **Monitor logs** - Check for suspicious activity
10. **Keep updated** - Apply security patches

## Compliance

### PCI DSS

If processing cards directly (not applicable to hosted checkout):

1. **Use secure networks** - Firewalls, encryption
2. **Protect card data** - Encryption, tokenization
3. **Vulnerability management** - Regular scans, patches
4. **Access control** - Restrict access to payment data
5. **Monitor and test** - Regular security testing

### Data Protection

1. **Encrypt personal data** - At rest and in transit
2. **Implement access controls** - Limit who can access data
3. **Log access** - Track who accesses what data
4. **Data breach notification** - Have a plan

## Next Steps

- [Troubleshooting](Troubleshooting) - Common issues
- [Configuration Guide](Configuration-Guide) - Secure configuration
- [Payment Flow](Payment-Flow) - Secure payment flow

