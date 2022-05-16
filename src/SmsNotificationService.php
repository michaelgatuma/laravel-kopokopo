<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Requests\TransactionSmsNotificationRequest;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Exception;

class SmsNotificationService extends Service
{
    /**
     * Send sms notifications to your customer after you have received a payment from them.
     * @see https://api-docs.kopokopo.com/?php#transaction-sms-notifications-api-requests
     * @param $options
     * @return array
     */
    public function sendTransactionSmsNotification($options): array
    {
        $transactionNotificationRequest = new TransactionSmsNotificationRequest($options);
        try {
            $response = $this->client->post('transaction_sms_notifications', ['body' => json_encode($transactionNotificationRequest->getSmsNotificationBody()), 'headers' => $transactionNotificationRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
