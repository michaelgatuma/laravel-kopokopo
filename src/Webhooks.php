<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Michaelgatuma\Kopokopo\Requests\WebhookSubscribeRequest;
use Michaelgatuma\Kopokopo\Helpers\Auth;
use Michaelgatuma\Kopokopo\Data\DataHandler;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use InvalidArgumentException;

class Webhooks extends Service
{
    /**
     * This will both validate and process the payload for you.
     * The payload is the json value in the body of the POST request i.e. `request()->getContent()`
     * @param $payload
     * @param $signature
     * @return array
     */
    public function webhookHandler($payload, $signature): array
    {
        if (empty($payload) || empty($signature)) {
            return $this->error('Pass the payload and signature ');
        }

        $auth = new Auth();

        $statusCode = $auth->auth($payload, $signature, $this->apiKey);

        if ($statusCode == 200) {
            $dataHandler = new DataHandler(json_decode($payload, true));

            return $this->success($dataHandler->dataHandlerSort($payload));
        } else {
            return $this->error('Unauthorized');
        }
    }

    /**
     * Create a webhook subscription.
     * @see https://api-docs.kopokopo.com/?php#create-a-webhook-subscription
     * @param $options
     * @return array
     */
    public function subscribe($options): array
    {
        try {
            $subscribeRequest = new WebhookSubscribeRequest($options);
            $response = $this->client->post('webhook_subscriptions', ['body' => json_encode($subscribeRequest->getWebhookSubscribeBody()), 'headers' => $subscribeRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
