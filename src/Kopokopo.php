<?php

namespace Michaelgatuma\Kopokopo;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Michaelgatuma\Kopokopo\Data\DataHandler;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Michaelgatuma\Kopokopo\Data\TokenData;
use Michaelgatuma\Kopokopo\Traits\HasFormattedPayload;

class Kopokopo
{
    use HasFormattedPayload;

    private string $version = "v1";
    /**
     * Endpoint base url for the specific api environment
     * @var string $base_url
     */
    private string $base_url;
    /**
     * Application id acquired after you create an Authorization application on the kopokopo dashboard
     * @var string $client_id
     */
    public string $client_id;
    /**
     * Application client secret
     * @var string $client_secret
     */
    public string $client_secret;
    /**
     * Application API key
     * @var string $api_key
     */
    public string $api_key;
    /**
     * Scope of your applications control on kopokopo transaction (i.e company,till). Using company will control transactions for all till numbers regardless
     * @var string $scope
     */
    public string $scope;
    /**
     * The business till number given to you by Kopokopo
     * @var string $till_number
     */
    public string $till_number;
    /**
     * The business till number given to you by Kopokopo to allow stk push payments
     * @var string $stk_till_number
     */
    public string $stk_till_number;
    /**
     * Preferred transacting currency i.e KES, USD, AUD
     * @var string $currency
     */
    public string $currency;
    /**
     * GuzzleHttp Client to handle all the requests
     * @var Client $client
     */
    protected Client $client;
    /**
     * GuzzleHttp Client to handle token services requests
     * @var Client $client
     */
    protected Client $token_client;

    /**
     * Initializes the class with an array of API values.
     */
    public function __construct()
    {
        if (config('kopokopo.sandbox')) {
            $this->base_url = "https://sandbox.kopokopo.com";
        } else {
            $this->base_url = "https://app.kopokopo.com";
        }

        $this->client_id = config('kopokopo.client_id');
        $this->client_secret = config('kopokopo.client_secret');
        $this->api_key = config('kopokopo.api_key');
        $this->scope = config('kopokopo.scope');
        $this->till_number = config('kopokopo.till_number');
        $this->stk_till_number = config('kopokopo.stk_till_number');
        $this->currency = config('kopokopo.currency');

        $this->client = new Client([
            'base_uri' => $this->base_url . "/api/" . $this->version,
        ]);

        $this->token_client = new Client([
            'base_uri' => $this->base_url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * The client credentials flow is the simplest OAuth 2 grant, with a server-to-server exchange of your application’s client_id, client_secret for an OAuth application access token
     * @return array
     */
    public function getAccessToken(): array
    {
        try {
            $res = $this->token_client->postAsync('oauth/token', ['form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'grant_type' => 'client_credentials',
            ]])->wait();

            $dataHandler = new TokenData();

            return $this->success($dataHandler->setGetTokenData(json_decode($res->getBody()->getContents(), true)));
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * The request is used to revoke a particular token at a time.
     * @param string $token
     * @return array
     */
    public function revokeToken(string $token): array
    {
        try {
            $requestData = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'token' => $token,
            ];

            $response = $this->token_client->postAsync('oauth/revoke', ['form_params' => $requestData])->wait();

            return $this->success($response->getBody()->getContents());
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * It can be used to check the validity of your access tokens, and find out other information such as which user and which scopes are associated with the token.
     * @param string $token
     * @return array
     */
    public function introspectToken(string $token): array
    {
        try {
            $requestData = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'token' => $token,
            ];
            $response = $this->token_client->postAsync('oauth/introspect', ['form_params' => $requestData])->wait();
            $dataHandler = new TokenData();
            return $this->success($dataHandler->setIntrospectTokenData(json_decode($response->getBody()->getContents(), true)));
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Shows details about the token used for authentication.
     * @param string $token
     * @return array
     */
    public function getTokenInfo(string $token): array
    {
        try {
            $response = $this->token_client->getAsync('oauth/token/info', [
                'headers' =>
                    [
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                    ]
            ])->wait();
            $dataHandler = new TokenData();
            return $this->success($dataHandler->setInfoTokenData(json_decode($response->getBody()->getContents(), true)));
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Webhooks are a means of getting notified of events in the Kopo Kopo application. To receive webhooks, you need to create a webhook subscription
     * @param string $event_type
     * @param string $url
     * @param string $scope
     * @param int $till
     * @param string $access_token
     * @return array
     */
    public function subscribeWebhooks(string $event_type, string $url, string $scope, int $till, string $access_token): array
    {
        try {
//            $subscribeRequest = new WebhookSubscribeRequest($options);
            $body = [
                'event_type' => $event_type,//'buygoods_transaction_received'
                'url' => $url,
                'scope' => $scope,
                'scope_reference' => $till
            ];
            $response = $this->client->postAsync('webhook_subscriptions', ['body' => json_encode($body), 'headers' =>
                [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ]]);

            return $this->postSuccess($response);
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (InvalidArgumentException|Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Before processing webhook events, make sure that they originated from Kopo Kopo.Each request is signed with the api_key you got when creating an oauth application on the platform.
     * The signature is contained in the X-KopoKopo-Signature header and is a SHA256 HMAC hash of the request body with the key being your API Key.
     * @param $payload
     * @param $signature
     * @return array
     */
    public function webhookHandler($payload,$signature): array
    {

        if (empty($payload) || empty($signature)) {
            return $this->error('Pass the payload and signature ');
        }

        $statusCode = $this->validatePayload($payload,$signature,$this->api_key);

        if ($statusCode == 200) {
            $dataHandler = new DataHandler(json_decode($payload, true));

            return $this->success($dataHandler->dataHandlerSort($payload));
        } else {
            return $this->error('Unauthorized');
        }
    }

    /**
     * @param $payload
     * @param $signature
     * @param $api_key
     * @return int
     */
    private function validatePayload($payload,$signature,$api_key): int
    {
        $expectedSignature = hash_hmac('sha256', $payload, $api_key);
        if (hash_equals($signature, $expectedSignature)) {
            return 200;
        } else {
            return 401;
        }
    }

    public function stkPush($payment_channel,$till_number,$first_name,$last_name,$phone,$email,$amount,$currency,$callback_url,$metadata,$access_token): array
    {
//        $stkPaymentRequest = new StkIncomingPaymentRequest($options);
        $body=[
            'payment_channel' => $payment_channel,
            'till_number' => $till_number,
            'subscriber' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone_number' => $phone,
                'email' => $email,
            ],
            'amount' => [
                'currency' => $currency,
                'value' => $amount,
            ],
            'metadata' => $metadata,
            '_links' => [
                'callback_url' => $callback_url,
            ],
        ];
        $headers=[
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ];
        try {
            $response = $this->client->postAsync('incoming_payments', ['body' => json_encode($body), 'headers' => $headers])->wait();

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    public function addPaymentRecipient(){}

    public function sendPayment(){}

    public function createMerchantBankAccount(){}

    public function createMerchantWallet(){}

    public function settleFunds(){}

    public function pollTransactions(){}

    public function sendTransactionSmsNotification(){}
}
