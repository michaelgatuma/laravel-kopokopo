<?php

namespace Michaelgatuma\Kopokopo;

use GuzzleHttp\Client;
use  Michaelgatuma\Kopokopo\Requests\K2InitialiseRequest;

/**
 * @deprecated
 */
class K2
{
    protected $options;

    protected $client;
    protected $token_client;

    private $api_version;
    //
    private $base_url;
    private $client_id;
    private $client_secret;
    private $api_key;

    public function __construct()
    {
        if (config('kopokopo.sandbox')) {
            $this->base_url = 'https://sandbox.kopokopo.com/';
        } else {
            $this->base_url = 'https://api.kopokopo.com/';
        }

        $this->client_id = config('kopokopo.client_id');
        $this->client_secret = config('kopokopo.client_secret');
        $this->api_key = config('kopokopo.api_key');

        $this->options = [
            'baseUrl' => $this->base_url,
            'clientId' => $this->client_id,
            'clientSecret' => $this->client_secret,
            'apiKey' => $this->api_key,
        ];
        $this->api_version = 'v1/';

        $this->client = new Client([
            'verify' => config('kopokopo.curl_ssl_verify'),
            'base_uri' => $this->base_url . "/api/" . $this->api_version,
        ]);

        $this->token_client = new Client([
            'verify' => config('kopokopo.curl_ssl_verify'),
            'base_uri' => $this->base_url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function TokenService()
    {
        $token = new TokenService($this->token_client, $this->options);

        return $token;
    }

    public function Webhooks()
    {
        $webhooks = new Webhooks($this->client, $this->options);

        return $webhooks;
    }

    public function StkService()
    {
        $stk = new StkService($this->client, $this->options);

        return $stk;
    }

    public function PayService()
    {
        $pay = new PayService($this->client, $this->options);

        return $pay;
    }

    public function SettlementTransferService()
    {
        $transfer = new SettlementTransferService($this->client, $this->options);

        return $transfer;
    }

    public function PollingService()
    {
        $poll = new PollingService($this->client, $this->options);

        return $poll;
    }

    public function SmsNotificationService()
    {
        $smsNotify = new SmsNotificationService($this->client, $this->options);

        return $smsNotify;
    }
}
