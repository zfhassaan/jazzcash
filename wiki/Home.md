# JazzCash Payment Gateway Package

<p align="center">
  <img src="../logo_JazzCash.png" alt="JazzCash Payment Gateway" width="150"/><br/>
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zfhassaan/jazzcash.svg?style=flat-square)](https://packagist.org/packages/zfhassaan/jazzcash)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](../LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/zfhassaan/jazzcash.svg?style=flat-square)](https://packagist.org/packages/zfhassaan/jazzcash)

## Overview

The JazzCash Payment Gateway Package is a Laravel package that simplifies integration with JazzCash payment gateway. This package provides a secure and convenient method for merchants to accept online payments through JazzCash's hosted checkout feature.

## Quick Navigation

### Getting Started
- [Installation Guide](Installation-Guide) - Step-by-step installation instructions
- [Configuration Guide](Configuration-Guide) - Environment and configuration setup
- [Getting Started](Getting-Started) - Quick start tutorial with code examples

### Core Features
- [Understanding Hosted Checkout](Understanding-Hosted-Checkout) - Complete guide for hosted checkout integration
- [API Reference](API-Reference) - Complete API method documentation
- [Payment Flow](Payment-Flow) - Detailed payment flow documentation

### Advanced Topics
- [Testing Guide](Testing-Guide) - Testing documentation
- [Troubleshooting](Troubleshooting) - Common issues and solutions
- [Security Best Practices](Security-Best-Practices) - Security guidelines
- [Contributing](Contributing) - Contribution guidelines

## Features

- **Hosted Checkout** - Secure redirect-based payment processing
- **Easy Integration** - Simple API with method chaining
- **Sandbox Support** - Test payments in sandbox mode
- **Production Ready** - Secure hash generation and validation
- **Type Safe** - Full type declarations for better IDE support
- **Well Tested** - Comprehensive test suite included
- **Security** - HTML escaping and input validation

## Installation

```bash
composer require zfhassaan/jazzcash
```

## Quick Start

```php
use zfhassaan\JazzCash\JazzCash;

$jazzcash = new JazzCash();
$jazzcash->setAmount(1000.00)
    ->setBillReference('ORDER-12345')
    ->setProductDescription('Product Purchase');

return $jazzcash->sendRequest();
```

## Requirements

- PHP 8.1 or higher
- Laravel 9.0, 10.0, 11.0, or 12.0
- JazzCash merchant account with:
  - Merchant ID
  - Password
  - Hash Key
  - Sandbox/Production URLs

## Disclaimer

This is an **unofficial** JazzCash API Payment Gateway package. This repository is created to help developers streamline the integration process. You can review the official JazzCash documentation [here](https://sandbox.jazzcash.com.pk/Sandbox/).

**Note**: This package currently covers Hosted Checkout process only. Direct checkout and subscription functionality may be added in future releases.

## Support

- **Issues**: [GitHub Issues](https://github.com/zfhassaan/jazzcash/issues)
- **Email**: zfhassaan@gmail.com

## License

The MIT License (MIT). Please see [LICENSE.md](../LICENSE.md) for more information.

---

Thank you for using the JazzCash Payment Gateway Package! If you have any questions or need assistance, please refer to the documentation pages above or contact support.

