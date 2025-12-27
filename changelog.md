### v1.0.6 (December 2025)
- **Fixed**: HTML escaping test - properly validates user data escaping while allowing auto-submit script
- **Fixed**: Hash generation test - improved regex pattern to extract hash from HTML form
- **Fixed**: Facade resolution error - corrected namespace casing from `Zfhassaan` to `zfhassaan` to match autoload mapping
- **Fixed**: Facade directory case sensitivity - renamed `src/facade/` to `src/Facade/` to match PSR-4 autoloading on case-sensitive filesystems (Linux)
- **Fixed**: Risky test warning - added proper assertions to `test_validate_payment_data_passes_with_valid_data`
- **Added**: GitHub Actions CI workflow for testing across Laravel 10, 11, 12 with their respective PHP versions
- **Updated**: Composer.json to support testbench ^10.0 for Laravel 12 compatibility
- All 37 tests now passing with 103 assertions
- Improved test reliability and error handling

### v1.0.5
- Previous release

### v1.0.0
- Hosted Checkout
