<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Tests\CreatesApplication;
use Tests\TestCase;

//uses(\PHPUnit\Framework\TestCase::class,CreatesApplication::class)->in(__DIR__);

beforeEach(function (){
    $this->config = [
        'clientId' => 'your_client_id',
        'clientSecret' => 'your_client_secret',
        'apiKey' => 'your_api_key',
        'baseUrl' => 'https://9284bede-d6e9f8d86aff.mock.pstmn.io',
        'scope'=>'till',
        'till'=>'0000000',
        'stkTill'=>'K000000',
        'currency'=>'KES',
        'webhooks'=>[],
        'stkWebhook'=>'https://9284bede-d6e9f8d86aff.mock.pstmn.io/stkwebhook',
    ];

    /*
    *    initiateIncomingPayment() setup
    */

    // initiateIncomingPayment() response headers
    $incomingPaymentRequestHeaders = file_get_contents(__DIR__.'/Mocks/incomingPaymentHeaders.json');

    // Create an instance of MockHandler for returning responses for initiateIncomingPayment()
    $incomingPaymentRequestMock = new MockHandler([
        new \GuzzleHttp\Psr7\Response(200, json_decode($incomingPaymentRequestHeaders, true)),
        new RequestException('Error Communicating with Server', new \GuzzleHttp\Psr7\Request('GET', 'test')),
    ]);

    // Assign the instance of MockHandler to a HandlerStack
    $incomingPaymentRequestHandler = HandlerStack::create($incomingPaymentRequestMock);

    // Create a new instance of client using the initiateIncomingPayment() handler
    $this->incomingPaymentRequestClient = new Client(['handler' => $incomingPaymentRequestHandler]);

    // Use $incomingPaymentRequestClient to create an instance of the StkService() class
//    $this->incomingPaymentRequestClient = new StkService($incomingPaymentRequestClient, $options);
//    $this->incomingPaymentRequestClient = Kopokopo::stkPush();

    /*
    *    getStatus() setup
    */

//    // json response to be returned
//    $statusBody = file_get_contents(__DIR__.'/Mocks/stk-status.json');
//
//    // Create an instance of MockHandler for returning responses for getStatus()
//    $statusMock = new MockHandler([
//        new Response(200, [], $statusBody),
//        new RequestException('Error Communicating with Server', new Request('GET', 'test')),
//    ]);
//
//    // Assign the instance of MockHandler to a HandlerStack
//    $statusHandler = HandlerStack::create($statusMock);
//
//    // Create a new instance of client using the getStatus() handler
//    $statusClient = new Client(['handler' => $statusHandler]);
//
//    // Use the $statusClient to create an instance of the StkService() class
//    $this->statusClient = new StkService($statusClient, $options);
});

test('incoming payment request succeeds',function (){
    $kopokopo=new \Michaelgatuma\Kopokopo\Kopokopo($this->config,$this->incomingPaymentRequestClient);
    expect(
        $kopokopo->stkPush(2455,'+254712345678','John','Doe','example@example.com','M-PESA')
    )->toMatchArray(['status' => 'success']);
});
