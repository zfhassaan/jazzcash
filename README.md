<p align="center">
  <img src="logo_JazzCash.png" alt="JazzCash Payment Gateway" width="150"/><br/>
  <!-- <h3 align="center">Payfast</h3> -->
</p>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/zfhassaan/jazzcash.svg?style=flat-square)](https://packagist.org/packages/zfhassaan/jazzcash)
[![Total Downloads](https://img.shields.io/packagist/dt/zfhassaan/jazzcash.svg?style=flat-square)](https://packagist.org/packages/zfhassaan/jazzcash)

<h4> Disclaimer </h4>
This is unofficial Jazzcash API Payment Gateway. This repository  is only created to help developers in streamlining the integration process. You can Review the Official Payment Gateway <a href="https://sandbox.jazzcash.com.pk/Sandbox/" >here.</a> 

This Package only hosted checkout process. There's no Subscription option enabled yet.


#### About
This document contains detailed explanation about how to integrate with Jazzcash Hosted Checkout.
<small>v1.0.0</small>

#### Intended Audience
This document is for merchants acquires and developers who want to integrate with Jazzcash to perform a HostedCheckout.

#### Integration Scope
The merchant will implement all ecommerce functionality. Jazzcash service (Jazzcash) will be used only payment processing with hosted checkout.

#### API End Points
This package only contains the hosted checkout process, there's no API Endpoint specified for direct checkout.

#### Integration Prerequisites
Merchants will be registered on Jazzcash prior to integration. After merchant sign up for Jazzcash account, following two unique values will be provided to merchant to operate: *Merchant_ID* , *Password*, *Hashkey*, *Sandbox url* and *Production url* will be provided by jazzcash, these keys are used to get a one-time authentication token, which is used to authenticate payment requests to the "Jazzcash"payment gateway.

#### Installation
You can install the package via composer

````
composer require zfhassaan/jazzcash
````

#### Set .env configurations

```
JAZZCASH_PAYMENTMODE=sandbox
JAZZCASH_MERCHANTID=
JAZZCASH_PASSWORD=
JAZZCASH_HASHKEY=
JAZZCASH_PRODUCTION_URL=
JAZZCASH_SANDBOX_URL=
JAZZCASH_RETURNURL=
```

#### configuration
Add These files in `app/config.php`

```php 
    /*
    * Package Service Providers...
    */

    \zfhassaan\jazzcash\provider\ServiceProvider::class,
```


and also in alias in `app/config.php`

```php 
  'aliases' => Facade::defaultAliases()->merge([
        'Jazzcash' => \zfhassaan\jazzcash\facade\JazzcashFacade::class,
    ])->toArray(),
```
#### Publish Vendor:
Once it's done then publish the jazzcash assets by using the following command: 

```bash
php artisan vendor:publish 
```
This will show the following response in terminal:
![img.png](img.png)

press 9 to publish ```zfhassaan\jazzcash\provider\ServiceProvider```

#### Steps:
##### Hosted Checkout
Send a Post Request with following params: 

```json
{
    "amount":"1",
    "billref":"bill-reference",
    "productDescription": "Product Description"
}
```

and in controller:

```php
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $jazzcash = new JazzCash();
        $jazzcash->setAmount($request->amount);
        $jazzcash->setBillReference($request->billref);
        $jazzcash->setProductDescription($request->productDescription);
        return $jazzcash->sendRequest();
    }
```
The index function is called and a new instance of the JazzCash class is created.

The setAmount, setBillReference, and setProductDescription methods are called on the JazzCash object, passing in the amount, billref, and productDescription values from the request as arguments. These methods set the corresponding properties of the JazzCash object to the specified values.

The sendRequest method is called on the JazzCash object. This method sends a request to the JazzCash API to initiate the checkout process and returns the response from the API.

The response from the API is returned by the index function. This response can be used to display the hosted checkout form or process the transaction in some other way.

#### Changelog
Please see Changelog for more information what has changed recently.

#### Security
The following lines are taken from [briandk](https://gist.github.com/briandk/3d2e8b3ec8daf5a27a62) repository for contributing in an open source projects.

**Great Bug Reports** tend to have:

- A quick summary and/or background
- Steps to reproduce
    - Be specific!
    - Give sample code if you can. An issue includes sample code that *anyone* with a base R setup can run to reproduce what I was seeing
- What you expected would happen
- What actually happens
- Notes (possibly including why you think this might be happening, or stuff you tried that didn't work)


#### License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
