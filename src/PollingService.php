<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Requests\PollingRequest;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Exception;

class PollingService extends Service
{
    public function pollTransactions($options)
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
