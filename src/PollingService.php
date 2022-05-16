<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Requests\PollingRequest;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Exception;

class PollingService extends Service
{
    /**
     * Poll Buygoods Transactions between the specified dates for a particular till or for the whole company
     * @see https://api-docs.kopokopo.com/?php#polling-api-requests
     * @param $options
     * @return array
     */
    public function pollTransactions($options): array
    {
        $pollingRequest = new PollingRequest($options);
        try {
            $response = $this->client->post('polling', ['body' => json_encode($pollingRequest->getPollingRequestBody()), 'headers' => $pollingRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
