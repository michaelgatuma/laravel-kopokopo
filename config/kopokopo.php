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
