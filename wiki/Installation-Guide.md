# Installation Guide

This guide will walk you through installing and setting up the JazzCash Payment Gateway package in your Laravel application.

## Prerequisites

Before installing the package, ensure you have:

- PHP 8.1 or higher
- Laravel 9.0, 10.0, 11.0, or 12.0
- Composer installed
- A JazzCash merchant account with:
  - Merchant ID
  - Password
  - Hash Key
  - Sandbox URL
  - Production URL

## Step 1: Install via Composer

Install the package using Composer:

```bash
composer require zfhassaan/jazzcash
```

## Step 2: Publish Configuration

Publish the configuration file to your `config` directory:

```bash
php artisan vendor:publish --tag=jazzcash-config
```

This will create `config/jazzcash.php` in your application.

Alternatively, you can use the interactive publish command:

```bash
php artisan vendor:publish
```

Then select the JazzCash service provider option (usually option 9 or the number shown for `zfhassaan\jazzcash\provider\ServiceProvider`).

## Step 3: Configure Environment Variables

Add the following environment variables to your `.env` file:

```env
# JazzCash Configuration
JAZZCASH_PAYMENTMODE=sandbox
JAZZCASH_MERCHANTID=your_merchant_id
JAZZCASH_PASSWORD=your_password
JAZZCASH_HASHKEY=your_hash_key
JAZZCASH_MPIN=your_mpin
JAZZCASH_PRODUCTION_URL=https://payments.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
JAZZCASH_SANDBOX_URL=https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform
JAZZCASH_RETURNURL=https://yourdomain.com/payment/callback
```

### Environment Variables Explained

| Variable | Description | Required |
|----------|-------------|----------|
| `JAZZCASH_PAYMENTMODE` | `sandbox` or `production` | Yes |
| `JAZZCASH_MERCHANTID` | Your JazzCash merchant ID | Yes |
| `JAZZCASH_PASSWORD` | Your JazzCash password | Yes |
| `JAZZCASH_HASHKEY` | Your JazzCash hash key | Yes |
| `JAZZCASH_MPIN` | Your JazzCash MPIN | Optional |
| `JAZZCASH_PRODUCTION_URL` | Production API URL | Yes |
| `JAZZCASH_SANDBOX_URL` | Sandbox API URL | Yes |
| `JAZZCASH_RETURNURL` | URL to redirect after payment | Yes |

## Step 4: Register Service Provider (Laravel < 11)

For Laravel 8, 9, and 10, you need to manually register the service provider in `config/app.php`:

```php
'providers' => [
    // ... other providers
    \zfhassaan\jazzcash\provider\ServiceProvider::class,
],
```

For Laravel 11+, the service provider is auto-discovered.

## Step 5: Register Facade (Laravel < 11)

For Laravel 8, 9, and 10, add the facade alias in `config/app.php`:

```php
'aliases' => Facade::defaultAliases()->merge([
    'Jazzcash' => \Zfhassaan\JazzCash\Facade\JazzcashFacade::class,
])->toArray(),
```

For Laravel 11+, the facade is auto-discovered.

## Verification

To verify the installation, you can test the service provider registration:

```php
// In tinker or a test route
php artisan tinker

// Test facade
use zfhassaan\JazzCash\JazzCash;
$jazzcash = new JazzCash();
$jazzcash->setAmount(100);
echo $jazzcash->getAmount(); // Should output: 100
```

## Troubleshooting

### Issue: Service Provider Not Found

**Solution**: Ensure you've registered the service provider in `config/app.php` (Laravel < 11) or that auto-discovery is enabled.

### Issue: Facade Not Found

**Solution**: Ensure you've registered the facade alias in `config/app.php` (Laravel < 11).

### Issue: Configuration Not Found

**Solution**: 
- Run `php artisan config:clear`
- Ensure `.env` file has all required variables
- Verify `config/jazzcash.php` exists

### Issue: Class Not Found

**Solution**: 
- Run `composer dump-autoload`
- Clear config cache: `php artisan config:clear`

## Next Steps

After installation, proceed to:

1. [Configuration Guide](Configuration-Guide) - Configure the package
2. [Getting Started](Getting-Started) - Learn basic usage
3. [Understanding Hosted Checkout](Understanding-Hosted-Checkout) - Understand payment processing

## Additional Resources

- [JazzCash Official Documentation](https://sandbox.jazzcash.com.pk/Sandbox/)
- [Laravel Documentation](https://laravel.com/docs)
- [Package README](../README.md)

