<?php

namespace Michaelgatuma\Kopokopo;

use Michaelgatuma\Kopokopo\Data\TokenData;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Michaelgatuma\Kopokopo\Requests\TokenRequest;

class TokenService extends Service
{
    /**
     * Get a token in order to use the service
     * @see https://api-docs.kopokopo.com/?php#request-application-authorization
     * @return array
     */
    public function getToken(): array
    {
        $grantType = 'client_credentials';

        $requestData = [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => $grantType,
        ];

        try {
            $response = $this->client->post('oauth/token', ['form_params' => $requestData]);

            $dataHandler = new TokenData();

            return $this->success($dataHandler->setGetTokenData(json_decode($response->getBody()->getContents(), true)));
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * The request is used to revoke a particular token at a time.
     * @see https://api-docs.kopokopo.com/?php#revoke-application-39-s-access-token
     * @param $options
     * @return array
     */
    public function revokeToken($options): array
    {
        try {
            $tokenRequest = new TokenRequest($options);

            $requestData = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'token' => $tokenRequest->getAccessToken(),
            ];

            $response = $this->client->post('oauth/revoke', ['form_params' => $requestData]);

            return $this->success($response->getBody()->getContents());
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * It can be used to check the validity of your access tokens, and find out other information such as which user and which scopes are associated with the token.
     * @see https://api-docs.kopokopo.com/?php#request-token-introspection
     * @param $options
     * @return array
     */
    public function introspectToken($options): array
    {
        try {
            $tokenRequest = new TokenRequest($options);

            $requestData = [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'token' => $tokenRequest->getAccessToken(),
            ];

            $response = $this->client->post('oauth/introspect', ['form_params' => $requestData]);

            $dataHandler = new TokenData();

            return $this->success($dataHandler->setIntrospectTokenData(json_decode($response->getBody()->getContents(), true)));
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }

    /**
     * Shows details about the token used for authentication.
     * @see https://api-docs.kopokopo.com/?php#request-token-information
     * @param $options
     * @return array
     */
    public function infoToken($options): array
    {
        try {
            $tokenRequest = new TokenRequest($options);

            $response = $this->client->get('oauth/token/info', ['headers' => $tokenRequest->getHeaders()]);

            $dataHandler = new TokenData();

            return $this->success($dataHandler->setInfoTokenData(json_decode($response->getBody()->getContents(), true)));
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setTokenErrorData($e));
        } catch(\Exception $e){
            return $this->error($e->getMessage());
        }
    }
}
