<?php

namespace Michaelgatuma\Kopokopo;

// require 'vendor/autoload.php';

use Exception;
use Michaelgatuma\Kopokopo\Data\FailedResponseData;
use Michaelgatuma\Kopokopo\Requests\MerchantBankAccountRequest;
use Michaelgatuma\Kopokopo\Requests\MerchantWalletRequest;
use Michaelgatuma\Kopokopo\Requests\SettleFundsRequest;

class SettlementTransferService extends Service
{
    /**
     * Create a merchant bank account transfer
     * @see https://api-docs.kopokopo.com/?php#create-a-merchant-bank-account
     * @param $options
     * @return array
     */
    public function createMerchantBankAccount($options): array
    {
        $merchantBankAccountRequest = new MerchantBankAccountRequest($options);
        try {
            $response = $this->client->post('merchant_bank_accounts', ['body' => json_encode($merchantBankAccountRequest->getSettlementAccountBody()), 'headers' => $merchantBankAccountRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Create a merchant mobile wallet transfer
     * @see https://api-docs.kopokopo.com/?php#create-a-merchant-mobile-wallet
     * @param $options
     * @return array
     */
    public function createMerchantWallet($options): array
    {
        $merchantWalletRequest = new MerchantWalletRequest($options);
        try {
            $response = $this->client->post('merchant_wallets', ['body' => json_encode($merchantWalletRequest->getSettlementAccountBody()), 'headers' => $merchantWalletRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * Create a 'blind' or 'targeted' transfer
     * Create a transfer by specifying the amount and optionally the destination. Your preferred settlement location(s) that are linked to your company and tills will be used as the destination. You may also initiate a transfer from your Kopo Kopo account by specifying the destination of the funds.
     * @see https://api-docs.kopokopo.com/?php#create-a-39-blind-39-transfer
     * see https://api-docs.kopokopo.com/?php#create-a-39-targeted-39-transfer
     * @param $options
     * @return array
     */
    public function settleFunds($options): array
    {
        $settleFundsRequest = new SettleFundsRequest($options);
        try {
            $response = $this->client->post('settlement_transfers', ['body' => json_encode($settleFundsRequest->getSettleFundsBody()), 'headers' => $settleFundsRequest->getHeaders()]);

            return $this->postSuccess($response);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            $dataHandler = new FailedResponseData();
            return $this->error($dataHandler->setErrorData($e));
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
