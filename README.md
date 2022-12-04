# Laravel Kopokopo Package

A simple package for laravel developers to consume Kopokopo API for laravel 8.

## Installation

You can easily install this package using composer and an installation command

```sh
composer require michaelgatuma/laravel-kopokopo
```

## Configuration

Next, after the package has been installed run this to publish config;

```
php artisan kopokopo:install
```

This will help in publishing `config/kopokopo.php` file. This *kopokopo config* file is where you will add all
configurations for Kopokopo APIs. This includes the environment your application is running in(sandbox or production),
callback URLs and required credentials.

```php
<?php

return [
    // Set this to false if you cannot authenticate due to SSL certificate problem.
    'curl_ssl_verify' => false,

    // When you are testing in sandbox environment this remains true, to go to a live environment set it to false
    'sandbox' => false,

    /* The below values should not be hardcoded for security reason. Add these variables in .env
    *  i.e KOPOKOPO_CLIENT_ID, KOPOKOPO_CLIENT_SECRET, KOPOKOPO_API_KEY
    */
    # This is the application id acquired after you create an Authorization application on the kopokopo dashboard
    'client_id' => env('KOPOKOPO_CLIENT_ID', 'EXAMPLE_CLIENT_ID'),

    # The kopokopo application client secret
    'client_secret' => env('KOPOKOPO_CLIENT_SECRET', 'EXAMPLE_CLIENT_SECRET'),

    # The kopokopo application api key
    'api_key' => env('KOPOKOPO_API_KEY', 'EXAMPLE_APIKEY'),

    // Define the scope of your applications control on kopokopo transaction. Using company will control transactions for all till numbers regardless
    'scope' => 'till', //i.e company, till

    // The business till number given to you by Kopokopo
    'till_number' => '0000000',

    // The business till number given to you by Kopokopo
    'stk_till_number' => 'K000000',

    // Preferred transacting currency i.e KES, USD, AUD
    'currency' => 'KES',

    // Webhooks are a means of getting notified on your laravel application of events in the Kopokopo application. i.e https://api-docs.kopokopo.com/#webhooks
    // Below values will be used to register your webhooks on Kopokopo. For it to work, update the values and use Kopokopo::subscribeRegisteredWebhooks() to register
    'webhooks' => [
        'buygoods_transaction_received' => 'https://example.com/k2transrec',
        'buygoods_transaction_reversed' => 'https://example.com/k2transrev',
        'b2b_transaction_received' => 'https://example.com/k2b2btransrec',
        'm2m_transaction_received' => 'https://example.com/k2m2mtransrec',
        'settlement_transfer_completed' => 'https://example.com/k2settcomp',
        'customer_created' => 'https://example.com/k2custcr'
    ],

    // This webhook is used to get notified of a successful Mpesa stk payment
    'stk_payment_received_webhook' => 'https://example.com/mobile-stk-received',

];


```

For production you need to replace with production credentials and also set `'sandbox' => false`.

For security reasons you may want to define your API credentials in `env` file. For example;

```php
  // config/kopokopo.php
  ...
  'client_id' => env('KOPOKOPO_CLIENT_ID'),
  'client_secret'   => env('KOPOKOPO_CLIENT_SECRET'),
  'api_key' => env('KOPOKOPO_API_KEY'),
  ...
```

```dotenv
# .env
# ...
KOPOKOPO_CLIENT_ID="BOPgOFAKE0gsUNH794EfjtmLiKTJ1BfMXjTZ2FrZM8"
KOPOKOPO_CLIENT_SECRET="8KGfakeHzHezjLpHzyP3NdJFd-T-Q1DewYyKrpiBX_s"
KOPOKOPO_API_KEY="EuavW1N-FAKEk4D-9XRLPudGGZ3yFBiygEwfkWDes_I"
# ...
```

## Usage

### First things first...Authorization

> N/B: You can skip the authorization step unless you need to carry out a number of operations in a single execution.
> Otherwise, the rest of the steps automatically acquire the access_token before calling the API>


Kopo Kopo uses Oauth2 to allow access to the Kopo Kopo API so before anything else, Kopokopo API expects the
application `access token` to be used to make calls to the Kopo Kopo API on behalf of the application.
To acquire the token, you need to call the token service like below:

```php
<?php
use Kopokopo;
...
// Get the token
$token=Kopokopo::getToken();

//introspect token
Kopokopo::introspectToken($token);

//revoke access token
Kopokopo::revokeToken($token);
```

### Webhook Subscription

This API enables you to register the callback URLs via which you will receive payment notifications for payments and
transaction that happen in your Kopo kopo app. To register Urls ensure all urls under webhooks are filled with valid
urls in `config/kopokopo.php`.
Checkout [https://developers.kopokopo.com/guides/webhooks](https://developers.kopokopo.com/guides/webhooks/) to
understand what each webhook does.  
When testing on sandbox you can use [ngrok](https://ngrok.com/) to expose your callbacks to the internet. Then
call `registerWebhooks()` on `Kopokopo` facade. i.e

#### Webhook subscription via terminal commands:
Publish any webhook by running the command below in your terminal:
```shell
php artisan kopokopo:subscribe
```
Example:
```shell
$ php artisan kopokopo:subscribe

  Enter Event Type:
  > stk_payment_received_webhook
  Callback URL i.e https://example.com/b2c-callback:
  > https://example.com/api//b2c-callback
  Scope: i.e till,business [till]:
  > till
  STK Enabled Till Number [K000000]:
  > K000000
```
You may use the --all option to subscribe to all webhook specified in kopokopo webhooks config i.e
```shell
php artisan kopokopo:subscribe --all
```
#### Alternatively:


> Remember to first import Kopokopo facade with:\
> `use Kopokopo;`

```php
...
$token=Kopokopo::getToken();

// subscribe all webhooks registered in config file
$response = Kopokopo::authenticate($token)->subscribeRegisteredWebhooks();
 
 //subscribe a specific webhook
 $res = Kopokopo::authenticate($token)->subscribeWebhook(
    event_type: 'buygoods_transaction_received',
    url: 'https://myawesomeapplication.com/destination',
    scope: 'till',
    till: 7777777,
 )
 
// HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/webhook_subscriptions/d76265cd-0951-e522-80da-0aa34a9b2388
...
```

Upon successful creation of the webhook subscription, the api will return true, else you will get the exact error as
json HTTP response;

### Initiate Mpesa Payment (STK Push)

You can receive payments from M-PESA users via STK Push. You can initiate payment like this;

```php
$res= Kopokopo::authenticate('my_access_token')->stkPush(
    amount:  2230,
    phone: '+254799999999',
    first_name: 'Michael',//optional
    last_name: 'Gatuma',//optional
    email: 'clientemail@gmail.com',//optional
    metadata: [
        'user_id'=>1,
        'action'=>'deposit'
    ]//optional
);

if($res['status'] == 'success')
{
    dump ("The resource location is:" . json_encode($res['location']));
    // => 'https://sandbox.kopokopo.com/api/v1/incoming_payments/247b1bd8-f5a0-4b71-a898-f62f67b8ae1c'
}
```

Upon a successful request a HTTP Status 201 will be returned and the location HTTP Header will contain the URL of the
newly created Incoming Payment.

`HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/incoming_payments/247b1bd8-f5a0-4b71-a898-f62f67b8ae1c`

### Query Incoming Payment Status

> Coming Soon
```php
$res=Kopokopo::getStatus($location);
```

### Create Payment Recipients

You can add external recipient that will be the destination of your payments in the future, sort of creating contacts.
The following are the different types of pay recipients you can create with examples.

#### (a). Mobile Wallet

Create Pay Recipient for a Mobile Wallet recipient;

```php
$res=Kopokopo::addPaymentRecipient(
$access_token,$first_name,$last_name,$email,$phone
);
$create_recipient_response = Kopokopo::PayService()->addPayRecipient([
  'type' => 'mobile_wallet',
  'firstName'=> 'Michael',
  'lastName'=> 'Gatuma',
  'email'=> 'example@nomail.net',
  'phoneNumber'=> '+254999999999',
  'network'=> 'Safaricom',
  'accessToken' => 'myRand0mAcc3ssT0k3n'
]);

if($create_recipient_response['status'] == 'success')
{
    echo "The resource location is:" . json_encode($create_recipient_response['location']);
}
```

A HTTP response code of 201 is returned upon successful creation of the PAY recipient. The URL of the recipient resource
is also returned in the HTTP Location Header

`HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/pay_recipients/c7f300c0-f1ef-4151-9bbe-005005aa3747`

#### (b). Bank Account

> Coming Soon

#### (c). External Till

> Coming Soon

#### (c). Paybill

> Coming Soon

### Make Outgoing Payments

> Coming Soon

### Query Outgoing Payment Status

> Coming Soon

### Transfer to your settling account(s)

Transfer funds to your pre-approved settlement accounts (bank accounts or mobile wallets).

> Coming Soon

### Polling

Poll Buygoods Transactions between the specified dates for a particular till or for the whole company.

> Coming Soon

### Transaction SMS Notifications API Requests

Send sms notifications to your customer after you have received a payment from them.

> Coming Soon

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Thank you for considering contributing to `laravel-kopokopo`. Pull requests and issues welcome. Be sure to check open
issues and PRs before continuing. Take a look at [contributing.md](contributing.md) to see a to do list.

## Security Vulnerabilities

If you discover a security vulnerability within laravel-kopokopo, please send an e-mail to Michael Gatuma via
mgates4410@gmail.com. All security vulnerabilities will be promptly addressed.

## License

The laravel-kopokopo package is open-source software licensed under
the [MIT license](https://opensource.org/licenses/MIT)

## Tags

laravel, mpesa, kopokopo, k2-connect,k2-connect-php, laravel-mpesa, php, stk-push

[ico-version]: https://img.shields.io/packagist/v/michaelgatuma/kopokopo.svg?style=flat-square

[ico-downloads]: https://img.shields.io/packagist/dt/michaelgatuma/kopokopo.svg?style=flat-square

[ico-travis]: https://img.shields.io/travis/michaelgatuma/kopokopo/master.svg?style=flat-square

[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/michaelgatuma/kopokopo

[link-downloads]: https://packagist.org/packages/michaelgatuma/kopokopo

[link-travis]: https://travis-ci.org/michaelgatuma/kopokopo

[link-styleci]: https://styleci.io/repos/12345678

[link-author]: https://github.com/michaelgatuma

[link-contributors]: ../../contributors
