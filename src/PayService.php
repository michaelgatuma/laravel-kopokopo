<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Requests\PayRecipientAccountRequest;
use Michaelgatuma\Kopokopo\Requests\PayRecipientMobileRequest;
use Michaelgatuma\Kopokopo\Requests\PayRecipientTillRequest;
use Michaelgatuma\Kopokopo\Requests\PayRecipientPaybillRequest;
use Michaelgatuma\Kopokopo\Requests\PayRequest;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Exception;

use GuzzleHttp\Client;


class PayService extends Service
{
    /**
     * Add external entities that will be the destination of your payments.
     * @see https://api-docs.kopokopo.com/?php#adding-pay-recipients
     * @param $options
     * @return array
     */
    public function addPayRecipient($options): array
    {
        try {
            if (!isset($options['type'])) {
                throw new \InvalidArgumentException('You have to provide the type');
            } elseif ($options['type'] === 'bank_account') {
                $payRecipientrequest = new PayRecipientAccountRequest($options);
            } elseif ($options['type'] === 'till') {
                $payRecipientrequest = new PayRecipientTillRequest($options);
            } elseif ($options['type'] === 'paybill') {
                $payRecipientrequest = new PayRecipientPaybillRequest($options);
            } elseif ($options['type'] === 'mobile_wallet') {
                $payRecipientrequest = new PayRecipientMobileRequest($options);
            } else{
                throw new \InvalidArgumentException('Invalid recipient type');
            }

            $response = $this->client->post('pay_recipients', ['body' => json_encode($payRecipientrequest->getPayRecipientBody()), 'headers' => $payRecipientrequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Create an outgoing payment to a third party.
     * @see https://api-docs.kopokopo.com/?php#create-a-payment
     * @param $options
     * @return array
     */
    public function sendPay($options): array
    {
        $payRequest = new PayRequest($options);
        try {
            $response = $this->client->post('payments', ['body' => json_encode($payRequest->getPayBody()), 'headers' => $payRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
