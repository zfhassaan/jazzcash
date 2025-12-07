# Configuration Guide

This guide covers all configuration options available in the JazzCash package.

## Configuration File

The configuration file is located at `config/jazzcash.php` after publishing. You can publish it using:

```bash
php artisan vendor:publish --tag=jazzcash-config
```

## Environment Variables

All configuration values are loaded from your `.env` file. Here's a complete list:

### Required Configuration

```env
# API URLs
JAZZCASH_PRODUCTION_URL=https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
JAZZCASH_SANDBOX_URL=https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform

# Authentication
JAZZCASH_MERCHANTID=your_merchant_id
JAZZCASH_PASSWORD=your_password
JAZZCASH_HASHKEY=your_hash_key

# Application Settings
JAZZCASH_RETURNURL=https://yourdomain.com/payment/callback
JAZZCASH_PAYMENTMODE=sandbox
```

### Optional Configuration

```env
# MPIN (if required)
JAZZCASH_MPIN=your_mpin

# Timezone (defaults to Asia/Karachi)
JAZZCASH_TIMEZONE=Asia/Karachi
```

## Configuration Structure

The configuration file structure:

```php
return [
    'api_url' => env('JAZZCASH_PRODUCTION_URL', ''),
    'sandbox_api_url' => env('JAZZCASH_SANDBOX_URL', ''),
    'merchant_id' => env('JAZZCASH_MERCHANTID', ''),
    'password' => env('JAZZCASH_PASSWORD', ''),
    'hash_key' => env('JAZZCASH_HASHKEY', ''),
    'return_url' => env('JAZZCASH_RETURNURL', ''),
    'mode' => env('JAZZCASH_PAYMENTMODE', 'sandbox'),
    'mpin' => env('JAZZCASH_MPIN', ''),
    'timezone' => env('JAZZCASH_TIMEZONE', 'Asia/Karachi'),
];
```

## Mode Configuration

The package supports two modes:

### Sandbox Mode

```env
JAZZCASH_PAYMENTMODE=sandbox
```

- Uses `JAZZCASH_SANDBOX_URL` for API calls
- Safe for testing
- Uses test credentials
- No real transactions processed

### Production Mode

```env
JAZZCASH_PAYMENTMODE=production
```

- Uses `JAZZCASH_PRODUCTION_URL` for API calls
- Real transactions processed
- Requires production credentials
- **Use with caution**

## Runtime Configuration

You can modify configuration at runtime:

```php
// Change mode
config(['jazzcash.mode' => 'production']);

// Update merchant ID
config(['jazzcash.merchant_id' => 'new_merchant_id']);

// Update return URL
config(['jazzcash.return_url' => 'https://newdomain.com/callback']);
```

## Accessing Configuration

### Using Config Helper

```php
$merchantId = config('jazzcash.merchant_id');
$mode = config('jazzcash.mode');
$apiUrl = config('jazzcash.api_url');
```

## Environment-Specific Configuration

### Development Environment

```env
JAZZCASH_PAYMENTMODE=sandbox
JAZZCASH_PRODUCTION_URL=https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
JAZZCASH_SANDBOX_URL=https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
```

### Staging Environment

```env
JAZZCASH_PAYMENTMODE=sandbox
JAZZCASH_PRODUCTION_URL=https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
JAZZCASH_SANDBOX_URL=https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
```

### Production Environment

```env
JAZZCASH_PAYMENTMODE=production
JAZZCASH_PRODUCTION_URL=https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
JAZZCASH_SANDBOX_URL=https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
```

## Security Best Practices

1. **Never commit `.env` file** - Keep credentials secure
2. **Use environment variables** - Don't hardcode values
3. **Rotate credentials regularly** - Update keys periodically
4. **Use different credentials** - Separate sandbox and production
5. **Restrict access** - Limit who can access configuration

## Validation

The package validates configuration on service initialization. Missing required values will throw exceptions:

```php
// Required values
- merchant_id
- password
- hash_key
- return_url
- api_url or sandbox_api_url (based on mode)
```

## Testing Configuration

Test your configuration:

```php
use zfhassaan\JazzCash\JazzCash;

try {
    $jazzcash = new JazzCash();
    $jazzcash->setAmount(100)
        ->setBillReference('TEST-123')
        ->setProductDescription('Test');
    
    // Configuration is correct if no exception is thrown
    echo "Configuration is valid!";
} catch (\RuntimeException $e) {
    echo "Configuration error: " . $e->getMessage();
}
```

## Troubleshooting

### Configuration Not Loading

**Solution**: Clear config cache:

```bash
php artisan config:clear
php artisan config:cache
```

### Wrong API URL Being Used

**Solution**: Check `JAZZCASH_PAYMENTMODE` in `.env`:

```bash
# Verify mode
php artisan tinker
>>> config('jazzcash.mode')
```

### Credentials Not Working

**Solution**: 
- Verify credentials in JazzCash dashboard
- Check for extra spaces in `.env` values
- Ensure you're using correct mode (sandbox vs production)

## Next Steps

- [Getting Started](Getting-Started) - Start using the package
- [Understanding Hosted Checkout](Understanding-Hosted-Checkout) - Understand payment processing
- [API Reference](API-Reference) - Explore available methods

