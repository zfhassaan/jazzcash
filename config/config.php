<?php


return [
    'api_url'    => env('JAZZCASH_PRODUCTION_URL', ''),
    'sandbox_api_url'=>env('JAZZCASH_SANDBOX_URL',''),
    'merchant_id'=> env('JAZZCASH_MERCHANTID', ''),
    'password'=> env('JAZZCASH_PASSWORD', ''),
    'hash_key'   => env('JAZZCASH_HASHKEY',''),
    'return_url' => env('JAZZCASH_RETURNURL', ''),
    'mode'       => env('JAZZCASH_PAYMENTMODE', 'sandbox')
];
