#  Al Rajhi Bank payment gateway API wrapper (ARB)

[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/egyjs/ARB-Payment-Gateway/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/egyjs/ARB-Payment-Gateway/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/egyjs/ARB-Payment-Gateway/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/egyjs/ARB-Payment-Gateway/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/egyjs/arb.svg?style=flat-square)](https://packagist.org/packages/egyjs/arb)


This package is a wrapper around the Al Rajhi Bank payment gateway API,
it allows you to initiate a payment request hosted on the Bank website or on the merchant website,
and also allows you to refund a payment.
## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/Arb.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/Arb)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require egyjs/arb
```

[//]: # (You can publish and run the migrations with:)

[//]: # (```bash)

[//]: # (php artisan vendor:publish --tag="arb-migrations")

[//]: # (php artisan migrate)

[//]: # (```)

You can publish the config file with:

```bash
php artisan vendor:publish --tag="arb-config"
```

This is the contents of the published config file:

```php
return [
    'mode' => env('ARB_MODE', 'test'), // test or live
    'test_merchant_endpoint' => 'https://securepayments.alrajhibank.com.sa/pg/payment/tranportal.htm',
    'live_merchant_endpoint' => 'https://digitalpayments.alrajhibank.com.sa/pg/payment/tranportal.htm',
    'test_bank_hosted_endpoint' => 'https://securepayments.alrajhibank.com.sa/pg/payment/hosted.htm',
    'live_bank_hosted_endpoint' => 'https://digitalpayments.alrajhibank.com.sa/pg/payment/hosted.htm',
    'tranportal_id' => env('ARB_TRANPORTAL_ID'),
    'tranportal_password' => env('ARB_TRANPORTAL_PASSWORD'),
    "resource_key" => env('ARB_RESOURCE_KEY'), // your resource key
    "currency_code" => env('ARB_CURRENCY_CODE', '682'),
];
```
add the following to your `.env` file
```env
ARB_MODE="test" # test or live
ARB_TRANPORTAL_ID="your_tranportal_id"
ARB_TRANPORTAL_PASSWORD="your_tranportal_password"
ARB_RESOURCE_KEY="your_resource_key"
ARB_CURRENCY_CODE="682" # "682" = SAR 
```
[//]: # (Optionally, you can publish the views using)

[//]: # ()
[//]: # (```bash)

[//]: # (php artisan vendor:publish --tag="arb-views")

[//]: # (```)

## Usage

### Bank hosted payment
to initiate a payment request hosted on the Bank website
```php
use Tahdir\Arb\Facades\Arb;
    
    
$responce = Arb::initiatePayment(100); // 100 to be paid

dd($responce);
/** @example
{#
  +"success": true
  +"url": "https://securepayments.alrajhibank.com.sa/pg/paymentpage.htm?PaymentID=?paymentId=000000000000000000"
}
*/
```
### merchant hosted payment
to initiate a payment request hosted on the merchant website, you need to create a form for the card details, and pass 
the card details to the `Arb::card()` method, then call the `Arb::initiatePayment()` method as shown below
```php
use Tahdir\Arb\Facades\Arb;
use Tahdir\Arb\Objects\Card;
    

Arb::card([
   'number' => '5105105105105100',
   'year' => '20'.'24',
   'month' => '12',
   'name' => 'AbdulRahman',
   'cvv' => '123',
   'type' => Card::CREDIT // or Card::DEBIT
]);    
$responce = Arb::initiatePayment(100); // 100 to be paid

dd($responce);
/** @example
{#
  +"success": true
  +"url": "https://securepayments.alrajhibank.com.sa/pg/payment/hosted.htm?paymentId=000000000000000000&id=000x0bAdcEF0HfZ"
}
*/
```

### Refund a payment
to refund a payment you need to call the `Arb::refund()` method as shown below

[//]: # (todo: add the payment id to the refund method)
```php
use Tahdir\Arb\Facades\Arb;

$responce = Arb::refund('000000000000000000', 100); // 100 to be refunded

dd($responce);
/** @example
{#
  +"success": true
  +"data": {}
}
*/
```

### manage data sent & received from the bank
to send custom data to the bank, you can use the `Arb::data()` method before any transaction as shown below
```php
use Tahdir\Arb\Facades\Arb;
Arb::data([
    'request_id' => 23,
    'user_id' => 43,
]);
// initiate a payment or make a refund
```
this data will be sent to the bank and will be returned in the response from the bank,
`ArbPaymentSuccessEvent` will be fired with the received data from the bank as shown below
```php
use Tahdir\Arb\Events\ArbPaymentSuccessEvent;

Event::listen(ArbPaymentSuccessEvent::class, function (ArbPaymentSuccessEvent $event) {
    $response = $event->response;
    // to get the data sent to the bank
    $data = $response->getOriginalData();
});
```
> Note: you can listen to the `ArbPaymentSuccessEvent` in both ways: `EventServiceProvider::$listen` or `Event::listen()` method

### Handle the response
egyjs/arb has a built-in [event driven architecture (EDA)](https://en.wikipedia.org/wiki/Event-driven_architecture) system to handle the response from the bank;
you can listen to the `ArbPaymentSuccessEvent` event to handle the success response,
and the `ArbPaymentFailedEvent` event to handle the fail response, 

The use of events allows for decoupling between the processing logic and the actions taken upon success or failure.
By emitting events,
the processing logic doesn't need to know about or be tightly coupled to the actions taken upon success or failure.
you can listen to the events in 2 ways:
1. using the `EventServiceProvider` class
```php
use Tahdir\Arb\Events\ArbPaymentFailedEvent;
use Tahdir\Arb\Events\ArbPaymentSuccessEvent;

protected $listen = [
    // ...
    ArbPaymentSuccessEvent::class => [
        LogSuccessArbPaymentListener::class, // add any listener classes you want to handle the success payment
    ],
    ArbPaymentFailedEvent::class => [
        LogFailedArbPaymentListener::class, // add any listener classes you want to handle the failed payment
    ],
];
```
2. using the `Event::listen()` method
```php
use Tahdir\Arb\Events\ArbPaymentFailedEvent;
use Tahdir\Arb\Events\ArbPaymentSuccessEvent;

Event::listen(ArbPaymentSuccessEvent::class, function (ArbPaymentSuccessEvent $event) {
    // handle the success payment
});

Event::listen(ArbPaymentFailedEvent::class, function (ArbPaymentFailedEvent $event) {
    // handle the failed payment
});
```

### handle the response in the controller
if you want to handle the using a custom route instead of the events,
you can pass the success and fail urls to the `Arb::successUrl()` and `Arb::failUrl()` methods as shown below
```php
use Tahdir\Arb\Facades\Arb;

Arb::successUrl('http://localhost:8000/arb/response')
    ->failUrl('http://localhost:8000/arb/response');
$responce = Arb::initiatePayment(100); // 100 to be paid

dd($responce);
/** @example
{#
  +"success": true
  +"url": "http://localhost:8000/success/handle?paymentId=000000000000000000"
}
*/
```
your `/routes/web.php` file should look like this
```php
use Illuminate\Http\Request;
Route::post('/arb/response', function (Request $request) {
    if ($request->status == 'success') {
        // handle the success payment
    } else {
        // handle the failed payment
    }
});
```

[//]: # (## Testing)

[//]: # ()
[//]: # (```bash)

[//]: # (composer test)

[//]: # (```)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [egyjs](https://github.com/egyjs)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
