<?php

namespace Michaelgatuma\Kopokopo\Console;

use Illuminate\Console\Command;
use Kopokopo;

class SubscribeWebhook extends Command
{
    protected $signature = "kopokopo:subscribe {--all}";

    protected $description = "Subscribe to a webhook";

    public function handle()
    {
        if ($this->option('all')) {
            if ($this->confirm('Do you wish to subscribe to all webhooks defined in configurations?'))
            {
                $this->info('Creating subscriptions...');
                $token = Kopokopo::getAccessToken();
                $res = Kopokopo::authenticate($token)->subscribeRegisteredWebhooks();
                if ($res['status'] == 'success')
                    $this->info('✔ Subscriptions created');
                else
                    $this->error('Subscription failed: ' . $res['data']['errorCode']);
            }else{
                $this->line('Aborted');
            }
        } else {
            $event_type = $this->askWithCompletion('Enter Event Type', ['buygoods_transaction_received', 'buygoods_transaction_reversed', 'b2b_transaction_received', 'm2m_transaction_received', 'settlement_transfer_completed', 'customer_created']);
            $url = $this->ask('Callback URL i.e https://example.com/stk-callback');
            $scope = $this->askWithCompletion('Scope: i.e till,business', ['till', 'business'], config('kopokopo.scope'));
            $till = $this->anticipate('Till Number', [config('kopokopo.till_number')], config('kopokopo.till_number'));
            $this->info('Creating subscription...');
            $token = Kopokopo::getAccessToken();
//            $this->line(json_encode(Kopokopo::authenticate($token)->introspectToken($token['data']['accessToken'])));
            $res = Kopokopo::authenticate($token)->subscribeWebhook($event_type, $url, $scope, $till);
            if ($res['status'] == 'success')
                $this->info('✔ Subscription created');
            else
                $this->error('Subscription failed: ' . json_encode($res['data']));
        }
    }
}
