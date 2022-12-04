<?php

namespace Michaelgatuma\Kopokopo;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
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
    protected Client $auth_client;

    private string $auth_token;

    protected array $headers = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];

    /**
     * Initializes the class with an array of API values.
     */
    public function __construct()
    {
        if (config('kopokopo.sandbox')) {
            $this->base_url = "https://sandbox.kopokopo.com";
        } else {
            $this->base_url = "https://api.kopokopo.com";
        }
        $this->client_id = config('kopokopo.client_id');
        $this->client_secret = config('kopokopo.client_secret');
        $this->api_key = config('kopokopo.api_key');
        $this->scope = config('kopokopo.scope');
        $this->till_number = config('kopokopo.till_number');
        $this->stk_till_number = config('kopokopo.stk_till_number');
        $this->currency = config('kopokopo.currency');

        $this->auth_client = new Client([
            'base_uri' => $this->base_url,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $this->client = new Client([
            'base_uri' => $this->base_url . "/api/" . $this->version . '/',
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Return dynamic headers
     * @return string[]
     */
    private function getHeaders(): array
    {
        return [
            'Authorization' => $this->auth_token
        ];
    }

    /**
     * Authenticate with the api before sending any requests
     * @param $token
     * @return $this
     */
    #[NoReturn] public function authenticate($token): static
    {
        if (is_array($token)) {
            if ($token['status'] == 'success') {
                $this->auth_token = $token['data']['tokenType'] . ' ' . $token['data']['accessToken'];
            }
        } else {
            $this->auth_token = 'Bearer ' . $token;
        }

        return $this;
    }

    /**
     * The client credentials flow is the simplest OAuth 2 grant, with a server-to-server exchange of your application’s client_id, client_secret for an OAuth application access token
     * @return array
     */
    public
    function getAccessToken(): array
    {
        try {
            $res = $this->auth_client->postAsync('oauth/token', ['form_params' => [
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
    public
    function revokeToken(string $token): array
    {
        try {
            $requestData = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'token' => $token,
            ];

            $response = $this->auth_client->postAsync('oauth/revoke', ['form_params' => $requestData])->wait();

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
    public
    function introspectToken(string $token): array
    {
        try {
            $requestData = [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'token' => $token,
            ];
            $response = $this->auth_client->postAsync('oauth/introspect', ['form_params' => $requestData])->wait();
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
    public
    function getTokenInfo(string $token): array
    {
        try {
            $response = $this->auth_client->getAsync('oauth/token/info', [
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

    public
    function subscribeRegisteredWebhooks(): array
    {
        $webhooks = config('kopokopo.webhooks');
        foreach ($webhooks as $event_type => $url) {
            $this->subscribeWebhook($event_type, $url, $this->scope, $this->till_number);
        }
        return self::success("Webhooks Registered");
    }

    /**
     * Webhooks are a means of getting notified of events in the Kopo Kopo application. To receive webhooks, you need to create a webhook subscription
     * @param string $event_type
     * @param string $url
     * @param string $scope
     * @param int $till
     * @return array
     */
    public
    function subscribeWebhook(string $event_type, string $url, string $scope, int $till): array
    {
        try {
//            $subscribeRequest = new WebhookSubscribeRequest($options);
            $body = [
                'event_type' => $event_type,//'buygoods_transaction_received'
                'url' => $url,
                'scope' => $scope,
                'scope_reference' => $till
            ];
            $response = $this->client->postAsync('webhook_subscriptions', ['body' => json_encode($body), 'headers' => $this->headers])->wait();

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
     * @param string $payload
     * @param string $signature
     * @return array
     */
    public
    function webhookHandler(string $payload, string $signature): array
    {

        if (empty($payload) || empty($signature)) {
            return $this->error('Pass the payload and signature ');
        }

        $statusCode = $this->validatePayload($payload, $signature, $this->api_key);

        if ($statusCode == 200) {
            $dataHandler = new DataHandler(json_decode($payload, true));

            return $this->success($dataHandler->dataHandlerSort($payload));
        } else {
            return $this->error('Unauthorized');
        }
    }

    /**
     * @param string $payload
     * @param string $signature
     * @param string $api_key
     * @return int
     */
    private
    function validatePayload(string $payload, string $signature, string $api_key): int
    {
        $expectedSignature = hash_hmac('sha256', $payload, $api_key);
        if (hash_equals($signature, $expectedSignature)) {
            return 200;
        } else {
            return 401;
        }
    }

    /**
     * @param int $amount
     * @param string $phone
     * @param string|null $first_name
     * @param string|null $last_name
     * @param string|null $email
     * @param string $payment_channel
     * @param array|null $metadata
     * @return array
     */
    public
    function stkPush(int $amount, string $phone, string $first_name = null, string $last_name = null, string $email = null, string $payment_channel = 'M-PESA STK Push', array $metadata = null): array
    {

        $body = [
            'payment_channel' => $payment_channel,
            'till_number' => $this->stk_till_number,
            'subscriber' => [
                'first_name' => $first_name,
                'last_name' => $last_name,
                'phone_number' => $phone,
                'email' => $email,
            ],
            'amount' => [
                'currency' => $this->currency,
                'value' => $amount,
            ],
            'metadata' => $metadata,
            '_links' => [
                'callback_url' => config('kopokopo.stk_payment_received_webhook'),
            ],
        ];

        try {
            $response = $this->client->postAsync('incoming_payments', ['body' => json_encode($body), 'headers' => $this->getHeaders()])->wait();
            return $this->postSuccess($response);
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage());
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * @param $location
     * @return array
     */
    public
    function getStatus($location): array
    {
        try {
            $response = $this->client->get($location, ['headers' => $this->headers]);
            $dataHandler = new DataHandler(json_decode($response->getBody()->getContents(), true));
            return $this->success($dataHandler->dataHandlerSort());
        } catch (BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (GuzzleException $e) {
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public
    function addPaymentRecipient(string $access_token, string $first_name, string $last_name, string $email, string $phone, string $type = 'mobile_wallet', string $network = 'Safaricom')
    {
//        try {
//            if (!isset($options['type'])) {
//                throw new \InvalidArgumentException('You have to provide the type');
//            } elseif ($options['type'] === 'bank_account') {
//                $body = [
//                    'type' => $type,
//                    'pay_recipient' => [
//                        'account_name' => $this->getAccountName(),
//                        'bank_branch_ref' => $this->getBankBranchRef(),
//                        'account_number' => $this->getAccountNumber(),
//                        'settlement_method' => $this->getSettlementMethod(),
//                    ],
//                ];
//                $payRecipientrequest = new PayRecipientAccountRequest($options);
//            } elseif ($options['type'] === 'till') {
//                $payRecipientrequest = new PayRecipientTillRequest($options);
//            } elseif ($options['type'] === 'paybill') {
//                $payRecipientrequest = new PayRecipientPaybillRequest($options);
//            } elseif ($options['type'] === 'mobile_wallet') {
//                $payRecipientrequest = new PayRecipientMobileRequest($options);
//            } else {
//                throw new \InvalidArgumentException('Invalid recipient type');
//            }
//
//            $response = $this->client->post('pay_recipients', ['body' => json_encode($payRecipientrequest->getPayRecipientBody()), 'headers' => $payRecipientrequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }

    public
    function sendPayment($destination_type, $destination_reference, $amount, $currency, $description, $category, $tags, $callback_url, $metadata, $access_token)
    {
//        $payRequest = new PayRequest($options);
//        try {
//            $response = $this->client->post('payments', ['body' => json_encode($payRequest->getPayBody()), 'headers' => $payRequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (\Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }

    public
    function createMerchantBankAccount($account_name, $bank_branch_ref, $account_number, $settlement_method, $access_token)
    {
//        $merchantBankAccountRequest = new MerchantBankAccountRequest($options);
//        try {
//            $response = $this->client->post('merchant_bank_accounts', ['body' => json_encode($merchantBankAccountRequest->getSettlementAccountBody()), 'headers' => $merchantBankAccountRequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }

    public
    function createMerchantWallet($network, $phone, $first_name, $last_name, $access_token)
    {
//        $merchantWalletRequest = new MerchantWalletRequest($options);
//        try {
//            $response = $this->client->post('merchant_wallets', ['body' => json_encode($merchantWalletRequest->getSettlementAccountBody()), 'headers' => $merchantWalletRequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }

    public
    function settleFunds($amount, $currency, $callback_url, $destination_type, $destination_reference, $access_token)
    {
//        $settleFundsRequest = new SettleFundsRequest($options);
//        try {
//            $response = $this->client->post('settlement_transfers', ['body' => json_encode($settleFundsRequest->getSettleFundsBody()), 'headers' => $settleFundsRequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (\Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }

    public
    function pollTransactions($scope, $scope_reference, $start_time, $end_time, $callback_url, $access_token)
    {
//        $pollingRequest = new PollingRequest($options);
//        try {
//            $response = $this->client->post('polling', ['body' => json_encode($pollingRequest->getPollingRequestBody()), 'headers' => $pollingRequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (\Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }

    public
    function sendTransactionSmsNotification($location, $access_token)
    {
//        $transactionNotificationRequest = new TransactionSmsNotificationRequest($options);
//        try {
//            $response = $this->client->post('transaction_sms_notifications', ['body' => json_encode($transactionNotificationRequest->getSmsNotificationBody()), 'headers' => $transactionNotificationRequest->getHeaders()]);
//
//            return $this->postSuccess($response);
//        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
//            $dataHandler = new FailedResponseData();
//            return $this->error($dataHandler->setErrorData($e));
//        } catch (\Exception $e) {
//            return $this->error($e->getMessage());
//        }
    }
}
