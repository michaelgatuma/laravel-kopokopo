<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Requests\StkIncomingPaymentRequest;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Exception;

class StkService extends Service
{
    public function initiateIncomingPayment($options)
    {
        $stkPaymentRequest = new StkIncomingPaymentRequest($options);
        try {
            $response = $this->client->post('incoming_payments', ['body' => json_encode($stkPaymentRequest->getPaymentRequestBody()), 'headers' => $stkPaymentRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
