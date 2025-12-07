# Troubleshooting

Common issues and solutions when using the JazzCash package.

## Installation Issues

### Service Provider Not Found

**Error**: `Class 'zfhassaan\jazzcash\provider\ServiceProvider' not found`

**Solution**:
1. Ensure package is installed: `composer require zfhassaan/jazzcash`
2. Run `composer dump-autoload`
3. For Laravel < 11, register service provider in `config/app.php`

### Facade Not Found

**Error**: `Class 'Jazzcash' not found`

**Solution**:
1. Ensure facade is registered in `config/app.php` (Laravel < 11)
2. Check namespace: `use Jazzcash;` or `\Jazzcash::`
3. Run `php artisan config:clear`

### Configuration Not Loading

**Error**: Configuration values are null or empty

**Solution**:
1. Clear config cache: `php artisan config:clear`
2. Verify `.env` file has all required variables
3. Check `config/jazzcash.php` exists
4. Run `php artisan config:cache` after changes

## Configuration Issues

### Wrong API URL Being Used

**Error**: Requests going to wrong endpoint

**Solution**:
1. Check `JAZZCASH_PAYMENTMODE` in `.env` (sandbox vs production)
2. Verify `JAZZCASH_PRODUCTION_URL` and `JAZZCASH_SANDBOX_URL` are correct
3. Clear config cache: `php artisan config:clear`

### Credentials Not Working

**Error**: Authentication failures or invalid credentials

**Solution**:
1. Verify credentials in JazzCash dashboard
2. Check for extra spaces in `.env` values
3. Ensure you're using correct mode (sandbox vs production)
4. Verify merchant ID, password, and hash key are correct

### Configuration Validation Errors

**Error**: `JazzCash configuration missing: ...`

**Solution**:
1. Check all required environment variables are set
2. Verify no typos in variable names
3. Ensure values are not empty
4. Check `.env` file syntax

## Payment Issues

### Amount Validation Error

**Error**: `Amount must be positive`

**Solution**:
1. Ensure amount is greater than 0
2. Check amount format (can be float, int, or string)
3. Verify amount is not negative

### Bill Reference Required

**Error**: `Bill reference is required`

**Solution**:
1. Ensure bill reference is set before calling `sendRequest()`
2. Check bill reference is not empty string
3. Verify `setBillReference()` is called

### Product Description Required

**Error**: `Product description is required`

**Solution**:
1. Ensure product description is set
2. Check description is not empty
3. Verify `setProductDescription()` is called

### Form Not Submitting

**Error**: Form doesn't auto-submit to JazzCash

**Solution**:
1. Check JavaScript is enabled in browser
2. Verify form HTML is correct
3. Check browser console for errors
4. Ensure form action URL is correct

### Hash Verification Fails

**Error**: Hash verification fails on callback

**Solution**:
1. Ensure hash key is correct
2. Verify parameter order matches JazzCash requirements
3. Check for empty/null values in hash array
4. Ensure hash generation uses same order as verification

## Response Issues

### No Response Received

**Error**: No response from `sendRequest()`

**Solution**:
1. Check all required data is set
2. Verify configuration is correct
3. Check for exceptions (wrap in try-catch)
4. Verify API URL is accessible

### Invalid Response Format

**Error**: Response format is unexpected

**Solution**:
1. Check response is HTML form
2. Verify all required fields are present
3. Check for JavaScript errors
4. Ensure form structure is correct

## Callback Issues

### Callback Not Received

**Error**: JazzCash doesn't redirect to callback URL

**Solution**:
1. Check callback URL is configured correctly
2. Verify URL is publicly accessible
3. Check URL uses HTTPS (required for production)
4. Verify URL in JazzCash dashboard

### Callback Hash Verification Fails

**Error**: Hash verification fails on callback

**Solution**:
1. Rebuild hash using same order as sent
2. Verify hash key is correct
3. Check all parameters are included
4. Ensure empty values are handled correctly

### Response Code Not Recognized

**Error**: Unknown response code

**Solution**:
1. Check JazzCash documentation for response codes
2. Handle all possible response codes
3. Log unknown codes for investigation
4. Default to failed status for unknown codes

## General Debugging

### Enable Debugging

Add to `.env`:

```env
APP_DEBUG=true
LOG_LEVEL=debug
```

### Check Logs

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Check for JazzCash related errors
grep -i jazzcash storage/logs/laravel.log
```

### Use Tinker

```bash
php artisan tinker

# Test configuration
config('jazzcash.mode');
config('jazzcash.merchant_id');

# Test instance creation
$jazzcash = new \zfhassaan\JazzCash\JazzCash();
$jazzcash->setAmount(100);
$jazzcash->getAmount();
```

## Common Error Messages

### "Amount must be positive"

**Cause**: Amount is zero or negative

**Solution**: Ensure amount is greater than 0

### "Bill reference is required"

**Cause**: Bill reference not set or empty

**Solution**: Call `setBillReference()` with a non-empty value

### "Product description is required"

**Cause**: Product description not set or empty

**Solution**: Call `setProductDescription()` with a non-empty value

### "JazzCash configuration missing: ..."

**Cause**: Required configuration value is missing

**Solution**: Set the missing environment variable in `.env`

### "JazzCash API URL is not configured"

**Cause**: API URL not set based on mode

**Solution**: Set `JAZZCASH_PRODUCTION_URL` or `JAZZCASH_SANDBOX_URL` in `.env`

## Getting Help

If you're still experiencing issues:

1. **Check Documentation**: Review all wiki pages
2. **Check Issues**: Search GitHub issues
3. **Review Logs**: Check application logs
4. **Test in Sandbox**: Verify in sandbox mode first
5. **Contact Support**: Email zfhassaan@gmail.com

## Response Codes

### JazzCash Response Codes

| Code | Status | Description |
|------|--------|-------------|
| `000` | Success | Payment successful |
| `001` | Failed | Payment failed |
| `002` | Cancelled | Payment cancelled |
| `003` | Pending | Payment pending |

### Package Error Codes

- `INVALID_AMOUNT` - Amount validation failed
- `MISSING_BILL_REFERENCE` - Bill reference not set
- `MISSING_DESCRIPTION` - Product description not set
- `CONFIG_ERROR` - Configuration error

## Next Steps

- [Configuration Guide](Configuration-Guide) - Verify configuration
- [API Reference](API-Reference) - Check method usage
- [Payment Flow](Payment-Flow) - Understand flow

