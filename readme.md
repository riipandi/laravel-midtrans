# Laravel Midtrans
[Midtrans-PHP](https://github.com/Midtrans/midtrans-php) library wrapped for laravel.
Forked from [firmantr3/laravel-midtrans](https://github.com/firmantr3/laravel-midtrans).

Facade for all midtrans services: Snap, VT-Direct. Easy to use and easy to create mocking
for tests.

## Quick Start
```bash
composer require riipandi/laravel-midtrans
```

### Laravel <= 5.4
Append this to Providers section array on your `config/app.php` like so:

```php
<?php
return [
    'providers': [
        // Other Providers above
        Riipandi\Midtrans\Providers\MidtransServiceProvider::class,
    ],
];
```

### Laravel >= 5.5
Automatically added by package discovery.

### Configuration
Update `config/services.php` file by adding this line:

```php
'midtrans' => [
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds'        => env('MIDTRANS_IS_3DS', true),
    'env'           => env('MIDTRANS_ENV', 'sandbox'),
    'curl_options'  => [],
],
```

Append this to your`.env` file:

```env
MIDTRANS_CLIENT_KEY=
MIDTRANS_SERVER_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
MIDTRANS_ENV=sandbox
```

If you want to use the facade, add this to your facades in `/config/app.php`:

```php
'Midtrans' => Riipandi\Midtrans\Facade\Midtrans::class,
```

## Usage
Use provided facade `Riipandi\Midtrans\Facade\Midtrans::snapOrVtDirectMethods()` or 
`app('midtrans')->snapOrVtDirectMethods()`, but you can just use the official class as well.

Official documentation can be found [here](https://github.com/Midtrans/midtrans-php)

### SNAP Example

#### Getting Snap token

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

$params = [
    'transaction_details' => [
        'order_id' => rand(),
        'gross_amount' => 10000,
    ]
];

$snapToken = Midtrans::getSnapToken($params);
```

#### Snap Redirect

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller

$params = [
    'transaction_details' => [
        'order_id' => rand(),
        'gross_amount' => 10000,
    ]
];

try {
  // Get Snap Payment Page URL
  $paymentRedirectUrl = Midtrans::createTransaction($params)->redirect_url;
  
  // Redirect to Snap Payment Page
  return redirect($paymentRedirectUrl);
}
catch (Exception $e) {
  echo $e->getMessage();
}
```

### Core API (VT-Direct)

#### Getting Client Key for front end

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

$clientKey = Midtrans::clientKey();
```

#### Full example for back end

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller

// Populate items
$items = [
    [
        'id'       => 'item1',
        'price'    => 100000,
        'quantity' => 1,
        'name'     => 'Adidas f50'
    ],
    [
        'id'       => 'item2',
        'price'    => 50000,
        'quantity' => 2,
        'name'     => 'Nike N90'
    ]
];

// Populate customer's billing address
$billing_address = [
    'first_name'   => "Andri",
    'last_name'    => "Budiman",
    'address'      => "Karet Belakang 15A, Setiabudi.",
    'city'         => "Jakarta",
    'postal_code'  => "51161",
    'phone'        => "081322311801",
    'country_code' => 'IDN'
];

// Populate customer's shipping address
$shipping_address = [
    'first_name'   => "John",
    'last_name'    => "Budiman",
    'address'      => "Bakerstreet 221B.",
    'city'         => "Jakarta",
    'postal_code'  => "51162",
    'phone'        => "081322311801",
    'country_code' => 'IDN'
];

// Populate customer's info
$customer_details = [
    'first_name'       => "Andri",
    'last_name'        => "Setiawan",
    'email'            => "test@test.com",
    'phone'            => "081322311801",
    'billing_address'  => $billing_address,
    'shipping_address' => $shipping_address
];

// Create transaction summary
$transaction_details = [
    'order_id'    => time(),
    'gross_amount'  => 200000
];

// Token ID from checkout page
$token_id = app('request')->token_id;

// Transaction data to be sent
$transaction_data = [
    'payment_type' => 'credit_card',
    'credit_card'  => [
        'token_id'      => $token_id,
        'authentication'=> true,
        // 'bank'          => 'bni', // optional to set acquiring bank
        // 'save_token_id' => true   // optional for one/two clicks feature
    ],
    'transaction_details' => $transaction_details,
    'item_details'        => $items,
    'customer_details'    => $customer_details
];

// do charge
$response = Midtrans::charge($transaction_data);

// Success
if($response->transaction_status == 'capture') {
    echo "<p>Transaksi berhasil.</p>";
    echo "<p>Status transaksi untuk order id $response->order_id: " .
        "$response->transaction_status</p>";

    echo "<h3>Detail transaksi:</h3>";
    echo "<pre>";
    var_dump($response);
    echo "</pre>";
}
// Deny
else if($response->transaction_status == 'deny') {
    echo "<p>Transaksi ditolak.</p>";
    echo "<p>Status transaksi untuk order id .$response->order_id: " .
        "$response->transaction_status</p>";

    echo "<h3>Detail transaksi:</h3>";
    echo "<pre>";
    var_dump($response);
    echo "</pre>";
}
// Challenge
else if($response->transaction_status == 'challenge') {
    echo "<p>Transaksi challenge.</p>";
    echo "<p>Status transaksi untuk order id $response->order_id: " .
        "$response->transaction_status</p>";

    echo "<h3>Detail transaksi:</h3>";
    echo "<pre>";
    var_dump($response);
    echo "</pre>";
}
// Error
else {
    echo "<p>Terjadi kesalahan pada data transaksi yang dikirim.</p>";
    echo "<p>Status message: [$response->status_code] " .
        "$response->status_message</p>";

    echo "<pre>";
    var_dump($response);
    echo "</pre>";
}
```

### Handle Incoming Notification (Web Hook)

Setup your own route and controller for handling incoming notification update from midtrans, and then:

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller

$notification = Midtrans::notification();
/**
* $notificationArray = $notification->toArray();
* $notificationObject = $notification->toObject();
**/

$transaction = $notification->transaction_status;
$fraud = $notification->fraud_status;

error_log("Order ID $notification->order_id: "."transaction status = $transaction, fraud staus = $fraud");

if ($transaction == 'capture') {
    if ($fraud == 'challenge') {
      // TODO Set payment status in merchant's database to 'challenge'
    }
    else if ($fraud == 'accept') {
      // TODO Set payment status in merchant's database to 'success'
    }
}
else if ($transaction == 'cancel') {
    if ($fraud == 'challenge') {
      // TODO Set payment status in merchant's database to 'failure'
    }
    else if ($fraud == 'accept') {
      // TODO Set payment status in merchant's database to 'failure'
    }
}
else if ($transaction == 'deny') {
      // TODO Set payment status in merchant's database to 'failure'
}
```

### Process Transaction

#### Get Transaction Status

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller
$status = Midtrans::status($orderId);
var_dump($status);
```

#### Approve Transaction

If transaction fraud_status == [CHALLENGE](https://support.midtrans.com/hc/en-us/articles/202710750-What-does-CHALLENGE-status-mean-What-should-I-do-if-there-is-a-CHALLENGE-transaction-), you can approve the transaction from Merchant Dashboard, or API :

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller
$approve = Midtrans::approve($orderId);
var_dump($approve);
```

#### Cancel Transaction

You can Cancel transaction with `fraud_status == CHALLENGE`, or credit card transaction with `transaction_status == CAPTURE` (before it become SETTLEMENT)

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller
$cancel = Midtrans::cancel($orderId);
var_dump($cancel);
```

#### Expire Transaction
You can Expire transaction with `transaction_status == PENDING` (before it become SETTLEMENT or EXPIRE)

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

// somewhere in your controller
$cancel = Midtrans::cancel($orderId);
var_dump($cancel);
```

## What is VT stands for
In case if you curious what is VT like me, the answer: `Midtrans` previous name is `Veritrans` ;).

## Test

### Mocking Midtrans Example

```php
<?php

use Riipandi\Midtrans\Facade\Midtrans;

Midtrans::shouldReceive('getSnapToken')
    ->once()
    ->with(['parameters'])
    ->andReturn('My Token');

$myToken = Midtrans::getSnapToken(['parameters'])); // returns "My Token"
```

Official laravel documentation: [https://laravel.com/docs/5.8/mocking#mocking-facades](https://laravel.com/docs/5.8/mocking#mocking-facades)

### Running Tests

```bash
vendor/bin/phpunit
```

## License
```
Copyright (c) Aris Ripandi <riipandi@gmail.com>

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
file except in compliance with the License. You may obtain a copy of the License at:
<http://www.apache.org/licenses/LICENSE-2.0>

Unless required by applicable law or agreed to in writing, software distributed under
the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
ANY KIND, either express or implied. See the License for the specific language
governing permissions and limitations under the License.
```

For more information about the license, please read [license file](./license.txt).
