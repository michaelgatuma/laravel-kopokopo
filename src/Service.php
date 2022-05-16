<?php

namespace Michaelgatuma\Kopokopo;

use Michaelgatuma\Kopokopo\Requests\StatusRequest;
use Michaelgatuma\Kopokopo\Data\DataHandler;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use GuzzleHttp\Client;
use Exception;

abstract class Service
{
    protected $client;
    protected $clientId;
    protected $clientSecret;

    public function __construct($client, $options)
    {
        $this->client = $client;
        $this->clientId = $options['clientId'];
        $this->clientSecret = $options['clientSecret'];
        $this->apiKey = $options['apiKey'];
    }

    protected static function error($data)
    {
        return [
            'status' => 'error',
            'data' => $data,
        ];
    }

    protected static function postSuccess($data)
    {
        return [
            'status' => 'success',
            'location' => $data->getHeaders()['location'][0],
        ];
    }

    protected static function success($data)
    {
        return [
            'status' => 'success',
            'data' => $data,
        ];
    }

    /**
     * Query the status of a previously initiated Payment or Transfer request or the status of Polling API or a Transaction Notification
     * @see https://api-docs.kopokopo.com/?php#query-incoming-payment-status
     * @see https://api-docs.kopokopo.com/?php#query-payment-status
     * @see https://api-docs.kopokopo.com/?php#query-transfer-status
     * @see https://api-docs.kopokopo.com/?php#query-polling-api-status
     * @param $options
     * @return array
     */
    public function getStatus($options): array
    {
        try {
            $status = new StatusRequest($options);

            $response = $this->client->get($status->getLocation(), ['headers' => $status->getHeaders()]);

            $dataHandler = new DataHandler(json_decode($response->getBody()->getContents(), true));

            return $this->success($dataHandler->dataHandlerSort());
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
