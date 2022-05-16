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
KOPOKOPO_CLIENT_ID="BOPgOCAGF0gsUNH794EfjtmLiMEJ1BfMXjTZ2FrZM8"
KOPOKOPO_CLIENT_SECRET="8KGf7aoHzHezjLpHzyF5NdJFd-T-Q1DewYyKrpiBX_s"
KOPOKOPO_API_KEY="EuavW1N-H1UMk4D-9XPKPudGGZ3yFBiygEwfkWDes_I"
# ...
```

## Usage

### First things first...Authorization

> N/B: You can skip the authorization step unless you need to carry out a number of operations in a single execution. Otherwise, the rest of the steps automatically acquire the access_token before calling the API>


Kopo Kopo uses Oauth2 to allow access to the Kopo Kopo API so before anything else, Kopokopo API expects the application `access token` to be used to make calls to the Kopo Kopo API on behalf of the application.
To acquire the token, you need to call the token service like below:
```php
//initialize kopokopo
$kopokopo=new Kopokopo();
// Get the token (optional if needed later)
$token=$kopokopo->TokenService()->getToken();
//revoke access token
$kopokopo->TokenService()->revokeToken(['accessToken' => $token])

//use kopokopo instance i.e subscribe to webhooks..
$response =$kopokopo->Webhooks()->subscribe([
    'eventType' => 'buygoods_transaction_received',
    'url' => 'https://myawesomeapplication.com/destination',
    'scope' => 'till',
    'scopeReference' => '555555', // Your till number
    'accessToken' => 'my_access_token'
]);

```

### Webhook Subscription

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
$registration_response=Kopokopo::Webhooks()->subscribe([
    'eventType' => 'buygoods_transaction_received',
    'url' => 'https://myawesomeapplication.com/destination',
    'scope' => 'till',
    'scopeReference' => '555555', // Your till number
    'accessToken' => 'my_access_token'
]);
// HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/webhook_subscriptions/d76265cd-0951-e511-80da-0aa34a9b2388
...
```

Upon successful creation of the webhook subscription, the api will return true, else you will get the exact error as
json HTTP response;

### Initiate Mpesa Payment (STK Push)

You can receive payments from M-PESA users via STK Push. You can initiate payment like this;

```php
$payment_response=Kopokopo::StkService()->initiateIncomingPayment([
  'paymentChannel' => 'M-PESA STK Push',
  'tillNumber' => 'K000000',
  'firstName' => 'Jane',
  'lastName' => 'Doe',
  'phoneNumber' => '+254999999999',
  'amount' => 3455,
  'currency' => 'KES',
  'email' => 'example@example.com',
  'callbackUrl' => 'https://callback_to_your_app.your_application.com/endpoint',
  'metadata' => [
    'customerId' => '123456789',
    'reference' => '123456',
    'notes' => 'Payment for invoice 12345'
  ],
  'accessToken' => 'myRand0mAcc3ssT0k3n',
]);
if($payment_response['status'] == 'success')
{
    echo "The resource location is:" . json_encode($response['location']);
    // => 'https://sandbox.kopokopo.com/api/v1/incoming_payments/247b1bd8-f5a0-4b71-a898-f62f67b8ae1c'
}
```

Upon a successful request a HTTP Status 201 will be returned and the location HTTP Header will contain the URL of the
newly created Incoming Payment.

`HTTP/1.1 201 Created Location: https://sandbox.kopokopo.com/api/v1/incoming_payments/247b1bd8-f5a0-4b71-a898-f62f67b8ae1c`

### Query Incoming Payment Status

>Coming Soon

### Create Payment Recipients

You can add external recipient that will be the destination of your payments in the future, sort of creating contacts.
The following are the different types of pay recipients you can create with examples.

#### (a). Mobile Wallet

Create Pay Recipient for a Mobile Wallet recipient;

```php
$create_recipient_response = Kopokopo::PayService()->addPayRecipient([
  'type' => 'mobile_wallet',
  'firstName'=> 'John',
  'lastName'=> 'Doe',
  'email'=> 'johndoe@nomail.net',
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

>Coming Soon

#### (c). External Till

>Coming Soon

#### (c). Paybill

>Coming Soon

### Make Outgoing Payments

>Coming Soon

### Query Outgoing Payment Status

>Coming Soon

### Transfer to your settling account(s)

Transfer funds to your pre-approved settlement accounts (bank accounts or mobile wallets).

>Coming Soon

### Polling

Poll Buygoods Transactions between the specified dates for a particular till or for the whole company.

>Coming Soon

### Transaction SMS Notifications API Requests

Send sms notifications to your customer after you have received a payment from them.

>Coming Soon

## Contributing

Thank you for considering contributing to `laravel-kopokopo`. Pull requests and issues welcome. Be sure to check open
issues and PRs before continuing.

## Security Vulnerabilities

If you discover a security vulnerability within laravel-kopokopo, please send an e-mail to Michael Gatuma via
mgates4410@gmail.com. All security vulnerabilities will be promptly addressed.

## License

The laravel-kopokopo package is open-source software licensed under
the [MIT license](https://opensource.org/licenses/MIT)

## Tags

laravel, kopokopo, kopo kopo, k2-connect,k2-connect-php, mpesa, package
