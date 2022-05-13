<?php

return [
    'curl_ssl_verify' => false,
    'sandbox' => true,
    'client_id' => 'BOPgOCAGF0gsUNHG794EfjtmLiMEJ1BfMXjTZ2FrZM8',
    'client_secret' => '8KGf7aoHzHezjLpHzyF5NdJFd-T-Q1DewYyKrpiBX_s',
    'api_key' => 'EuavW1N-H1UMk4D-9XPKPudGGZ3yFBiygEwfkWDes_I',

    'till_number' => '8293219',
    'scope' => 'till',//company or till
    'webhooks' => [
        'buygoods_transaction_received' => 'https://upworkstation.com/k2transrec',
        'buygoods_transaction_reversed' => 'https://upworkstation.com/k2transrev',
        'b2b_transaction_received' => 'https://upworkstation.com/k2b2btransrec',
        'm2m_transaction_received' => 'https://upworkstation.com/k2m2mtransrec',
        'settlement_transfer_completed' => 'https://upworkstation.com/k2settcomp',
        'customer_created' => 'https://upworkstation.com/k2custcr'
    ],
    'stk_payment_received_webhook'=>'https://upworkstation.com/k2stkreceiv',
//    'service_name' => 'M-PESA',
//    'business_number' => '888555',
//// 'transaction_reference' => '',
//    'internal_transaction_id' => '',
//    'transaction_timestamp' => date('c'),
//    'transaction_type' => 'Paybill',
//// 'account_number' => '',
//// 'sender_phone' => '',
//// 'first_name' => '',
//// 'middle_name' => '',
//// 'last_name' => '',
////'amount' => '',
//    'currency' => 'KES',
//    'api_key' => '',
//    'api_link' => '',
];
