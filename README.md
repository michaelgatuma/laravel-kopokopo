# Kopokopo Laravel Package

A simple package for laravel developers to consume Kopokopo Mpesa API for laravel 8

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
    'curl_ssl_verify' => true,

    // When you are testing in sandbox environment this remains true, to go to a live environment set it to false
    'sandbox' => true,

    /* The below values should not be hardcoded for security reason. Add these variables in .env
    *  i.e KOPOKOPO_CLIENT_ID, KOPOKOPO_CLIENT_SECRET, KOPOKOPO_API_KEY
    */
    # This is the application id acquired after you create an Authorization application on the kopokopo dashboard
    'client_id' => env('KOPOKOPO_CLIENT_ID', 'BOPgOCAGF0gsUNH794EfjtmLiMEJ1BfMXjTZ2FrZM8'),

    # The kopokopo application client secret
    'client_secret' => env('KOPOKOPO_CLIENT_SECRET', '8KGf7aoHzHezjLpHzyF5NdJFd-T-Q1DewYyKrpiBX_s'),

    # The kopokopo application api key
    'api_key' => env('KOPOKOPO_API_KEY', 'EuavW1N-H1UMk4D-9XPKPudGGZ3yFBiygEwfkWDes_I'),

    // Define the scope of your applications control on kopokopo transaction. Using company will control transactions for all till numbers regardless
    'scope' => 'till', //i.e company, till

    // The business till number given to you by Kopokopo
    'till_number' => '8293219',

    // Preferred transacting currency i.e KES, USD, AUD
    'currency' => 'KES',

    // Webhooks are a means of getting notified on your laravel application of events in the Kopo Kopo application. i.e https://api-docs.kopokopo.com/#webhooks
    // Below values will be used to register your webhooks on Kopokopo. For it to work, update the values and use Kopokopo::registerWebhooks() to register
    'webhooks' => [
        'buygoods_transaction_received' => 'https://example.com/k2transrec',
        'buygoods_transaction_reversed' => 'https://example.com/k2transrev',
        'b2b_transaction_received' => 'https://example.com/k2b2btransrec',
        'm2m_transaction_received' => 'https://example.com/k2m2mtransrec',
        'settlement_transfer_completed' => 'https://example.com/k2settcomp',
        'customer_created' => 'https://example.com/k2custcr'
    ],

    // This webhook is used to get notified of a successfull mpesa stk payment
    'stk_payment_received_webhook' => 'https://example.com/k2stkreceiv',

];

```

For production you need to replace with production credentials and also set `'sandbox' => false`.

For security reasons you may want to define your API credentials in `env` file. For example;

```php
  ...
  'client_id' => env('KOPOKOPO_CLIENT_ID'),
  'client_secret'   => env('KOPOKOPO_CLIENT_SECRET'),
  'api_key' => env('KOPOKOPO_API_KEY'),
  ...
```

## Usage

### Register Notification Webhooks

This API enables you to register the callback URLs via which you will receive payment notifications for payments and
transaction that happen in your Kopo kopo app. To register Urls ensure all urls under webhooks are filled with valid
urls in `config/kopokopo.php`.
Checkout [https://developers.kopokopo.com/guides/webhooks](https://developers.kopokopo.com/guides/webhooks/) to
understand what each webhook does.  
When testing on sandbox you can use [ngrok](https://ngrok.com/) to expose your callbacks to the internet. Then
call `registerWebhooks()` on `Kopokopo` facade. i.e

> Remember to import `Kopokopo facade`
>> `use Kopokopo;`

```php
...
$register_response=Kopokopo::registerWebhooks();
// true
...
```

Upon successful creation of the webhook subscription, the api will return true, else you will get the exact error as
json HTTP response;

### Initiate Mpesa Payment (STK Push)

You can receive payments from M-PESA users via STK Push. You can initiate payment like this;

```php
$payment_response=Kopokopo::initiateMpesaPayment(50, '+254716076053', 'Michael', 'Gatuma', 'mgates4410@gmail.com', 'KES')
```

Upon a successful request a HTTP Status 201 will be returned and the location HTTP Header will contain the URL of the
newly created Incoming Payment.

`HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/incoming_payments/247b1bd8-f5a0-4b71-a898-f62f67b8ae1c`

### Query Incoming Payment Status

Coming soon!

### Create Payment Recipients
You can add external recipient that will be the destination of your payments in the future, sort of creating contacts. The following are the different types of pay recipients you can create with examples.
#### (a). Mobile Wallet
Create Pay Recipient for a Mobile Wallet recipient;
```php
$create_recipient_response = Kopokopo::addPaymentRecipient('Michael','Gatuma','mgates4410@gmail.com','+254716076053','Safaricom');
```
A HTTP response code of 201 is returned upon successful creation of the PAY recipient. The URL of the recipient resource is also returned in the HTTP Location Header

`HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/pay_recipients/c7f300c0-f1ef-4151-9bbe-005005aa3747`

#### (b). Bank Account

Coming soon!

#### (c). External Till

Coming soon!

#### (c). Paybill

Coming soon!

### Make Outgoing Payments

Coming soon!

### Query Outgoing Payment Status

Coming soon!

### Transfer to your settling account(s)
Transfer funds to your pre-approved settlement accounts (bank accounts or mobile wallets).

Coming soon!

### Polling
Poll Buygoods Transactions between the specified dates for a particular till or for the whole company.

Coming soon!

### Transaction SMS Notifications API Requests
Send sms notifications to your customer after you have received a payment from them.

Coming soon!

## Contributing

Thank you for considering contributing to `laravel-kopokopo`. Pull requests and issues welcome. Be sure to check open
issues and PRs before continuing.

## Security Vulnerabilities

If you discover a security vulnerability within laravel-kopokopo, please send an e-mail to Michael Gatuma via
mgates4410@gmail.com. All security vulnerabilities will be promptly addressed.

## License

The laravel-kopokopo package is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT)

## Tags
laravel, kopokopo, kopo kopo, mpesa, package
