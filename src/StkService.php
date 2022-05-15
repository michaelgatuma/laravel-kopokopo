<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Michaelgatuma\Kopokopo\Requests\StkIncomingPaymentRequest;

class StkService extends Service
{
    /**
     * @param $options
     * @return array
     * @deprecated This method is deprecated. Instead, use `initiateIncomingMpesaPayment(...)`
     * Send a payment request to an M-Pesa Subscriber phone number.
     * @see https://api-docs.kopokopo.com/?php#receive-payments-from-m-pesa-users-via-stk-push
     */
    public function initiateIncomingPayment($options): array
    {
        $stkPaymentRequest = new StkIncomingPaymentRequest($options);
        try {
            $response = $this->client->post('incoming_payments', ['body' => json_encode($stkPaymentRequest->getPaymentRequestBody()), 'headers' => $stkPaymentRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Send a payment request to an M-Pesa Subscriber phone number.
     * @see https://api-docs.kopokopo.com/?php#receive-payments-from-m-pesa-users-via-stk-push
     * @param string $first_name
     * @param string $last_name
     * @param string $phone
     * @param int $amount
     * @param string $currency
     * @param string $email
     * @param string $callback_url
     * @param array $metadata
     * @return array
     */
    public function initiateIncomingMpesaPayment(string $first_name, string $last_name, string $phone, int $amount, string $currency, string $email, string $callback_url, array $metadata = []): array
    {
        $options = [
            'paymentChannel' => 'M-PESA STK Push',
            'tillNumber' => config('kopokopo.till_number'),
            'firstName' => $first_name,
            'lastName' => $last_name,
            'phoneNumber' => $phone,
            'amount' => $amount,
            'currency' => $currency,
            'email' => $email,
            'callbackUrl' => $callback_url,
            'metadata' => $metadata,
            'accessToken' => 'myRand0mAcc3ssT0k3n',
        ];
        $stkPaymentRequest = new StkIncomingPaymentRequest($options);
        try {
            $response = $this->client->post('incoming_payments', ['body' => json_encode($stkPaymentRequest->getPaymentRequestBody()), 'headers' => $stkPaymentRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
