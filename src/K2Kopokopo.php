<?php

namespace Michaelgatuma\Kopokopo;

/**
 * @deprecated
 */
class K2Kopokopo extends K2
{
    private string $access_token;
    private int $access_token_ttl;

    public function __construct()
    {
        parent::__construct();
        # AUTHORIZATION
        // Get one of the services
        $tokens = (new Kopokopo)->TokenService();
        // Use the service
        $result = $tokens->getToken();
        if ($result['status'] == 'success') {
            $data = $result['data'];
            $this->access_token = $data['accessToken'];
            $this->access_token_ttl = $data['expiresIn'];
        }
    }

    /**
     * Webhooks are a means of getting notified of events in the Kopo Kopo application. To receive webhooks, you need to create a webhook subscription.
     * @return bool|array
     */
    public function registerWebhooks(): bool|array
    {
        $webhooks = Kopokopo::Webhooks();
        $response = $webhooks->subscribe([
            'eventType' => 'buygoods_transaction_received',
            'url' => 'https://upworkstation.com/api/payments/k2-endpoints/buygoods-received-transaction-bell',
            'scope' => 'till',
            'scopeReference' => '9731173', // Your till number
            'accessToken' => $this->access_token
        ]);
//        # Create a webhook subscription
//        $webhooks = Kopokopo::Webhooks();
//        foreach (config('kopokopo.webhooks') as $key => $value) {
//            $response = $webhooks->subscribe([
//                'eventType' => $key,
//                'url' => $value,
//                'scope' => 'till',
//                'scopeReference' => config('kopokopo.till_number'), // Your till number
//                'accessToken' => $this->access_token
//            ]);
//            if ($response['status'] == 'success') {
//                continue;
//            } else {
//                return $response;
//            }
//        }
        return $response;
    }

    /**
     * Receive payments from M-PESA users via STK Push.
     * @param float $amount
     * @param string $e164phone
     * @param string $fname
     * @param string $lname
     * @param string $email
     * @param string $currency
     * @param array $meta
     * @returns array
     */
    public function initiateMpesaPayment(float $amount, string $e164phone, string $fname, string $lname, string $email, string $currency = 'KES', array $meta = []): array
    {
        # Receive payments from M-PESA users via STK Push.
        $stk = Kopokopo::StkService();
        $response = $stk->initiateIncomingPayment([
            'paymentChannel' => 'M-PESA STK Push',
            'tillNumber' => config('kopokopo.till_number'),
            'firstName' => $fname,
            'lastName' => $lname,
            'phoneNumber' => $e164phone,
            'amount' => $amount,
            'currency' => $currency,
            'email' => $email,
            'callbackUrl' => config('kopokopo.stk_payment_received_webhook'),
            'metadata' => $meta,
            'accessToken' => $this->access_token,
        ]);
//        if($response['status'] == 'success')
//        {
//            return true;
//        }
        return $response;
    }

    /**
     * Add external entities that will be the destination of your payments.
     * @param string $fname
     * @param string $lname
     * @param string $email
     * @param string $phone
     * @param string $network
     * @return array
     */
    public function addPaymentRecipient(string $fname,string $lname,string $email,string $phone,string $network): array
    {
        # Add external entities that will be the destination of your payments.
        $pay = Kopokopo::PayService();
        $response = $pay->addPayRecipient([
            'type' => 'mobile_wallet',
            'firstName' => $fname,
            'lastName' => $lname,
            'email' => $email,
            'phoneNumber' => $phone,
            'network' => $network,
            'accessToken' => $this->access_token
        ]);
//        if($response['status'] == 'success')
//        {
//            dump("The resource location is:" . $response['location']) ;
//        }
        return $response;
    }

    public function sendPayment()
    {
        return $this;
    }

    public function mobileWallet()
    {
    }
}
