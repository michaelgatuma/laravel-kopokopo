<?php

namespace Michaelgatuma\Kopokopo;

use GuzzleHttp\Client;
use JetBrains\PhpStorm\Pure;
use Michaelgatuma\Kopokopo\Requests\K2InitialiseRequest;
use PhpOffice\PhpSpreadsheet\Calculation\Web;

class Kopokopo
{
    private string $base_domain;
    public mixed $baseUrl;
    protected array $options;
    private string $version;
    protected Client $client;
    protected Client $tokenClient;

    public function __construct()
    {
        if (config('kopokopo.sandbox')) {
            $this->baseUrl = 'https://sandbox.kopokopo.com';
        } else {
            $this->baseUrl = 'https://api.kopokopo.com';
        }
        $options=[
            'clientId' => config('kopokopo.client_id'),
            'clientSecret' => config('kopokopo.client_secret'),
            'apiKey' => config('kopokopo.api_key'),
            'baseUrl' => $this->baseUrl
        ];
        $k2InitialiseRequest = new K2InitialiseRequest($options);

        $this->baseUrl=$k2InitialiseRequest->getBaseUrl();
        $this->options = $k2InitialiseRequest->getOptions();

        $this->version = 'v1/';

        $this->client = new Client([
            'verify' => config('kopokopo.curl_ssl_verify',true),
            'base_uri' => $this->baseUrl . "/api/" . $this->version,
        ]);

        $this->tokenClient = new Client([
            'verify' => config('kopokopo.curl_ssl_verify',true),
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Application authorization
     * @return TokenService
     */
    #[Pure] public function TokenService(): TokenService
    {
        return new TokenService($this->tokenClient, $this->options);
    }

    /**
     * Webhooks are a means of getting notified of events in the Kopo Kopo application.
     * @return Webhooks
     */
    #[Pure] public function Webhooks(): Webhooks
    {
        return new Webhooks($this->client, $this->options);
    }

    /**
     * Receive payments from M-PESA users via STK Push.
     * @return StkService
     */
    #[Pure] public function StkService(): StkService
    {
        return new StkService($this->client, $this->options);
    }

    /**
     * Send money (PAY)
     * @return PayService
     */
    #[Pure] public function PayService(): PayService
    {
        return new PayService($this->client, $this->options);
    }

    /**
     * Transfer funds to your pre-approved settlement accounts (bank accounts or mobile wallets).
     * @return SettlementTransferService
     */
    #[Pure] public function SettlementTransferService(): SettlementTransferService
    {
        return new SettlementTransferService($this->client, $this->options);
    }

    /**
     * Poll Buygoods Transactions between the specified dates for a particular till or for the whole company
     * @return PollingService
     */
    #[Pure] public function PollingService(): PollingService
    {
        return new PollingService($this->client, $this->options);
    }

    /**
     * Send sms notifications to your customer after you have received a payment from them.
     * @return SmsNotificationService
     */
    #[Pure] public function SmsNotificationService(): SmsNotificationService
    {
        return new SmsNotificationService($this->client, $this->options);
    }
}
