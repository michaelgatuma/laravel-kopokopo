<?php

namespace Michaelgatuma\Kopokopo\Console;

use Illuminate\Console\Command;

class InstallKopokopo extends Command
{
    protected $signature = "kopokopo:install";

    protected $description = "Install Kopokopo package";

    public function handle()
    {
        $this->info('Installing Kopokopo...');
        $this->info('Publishing Configuration...');

        $this->call('vendor:publish', [
            '--provider' => "Michaelgatuma\Kopokopo\KopokopoServiceProvider",
            '--tag' => "kopokopo-config"
        ]);

//        $this->info('Adding .env variables');
//        $data=[
//            'KOPOKOPO_CLIENT_ID'=>'',
//            'KOPOKOPO_CLIENT_SECRET'=>'',
//            'KOPOKOPO_API_KEY'=>'',
//        ];
//
//        $path = base_path('.env');
//
//        if (file_exists($path)) {
//            foreach ($data as $key => $value) {
//                file_put_contents($path, str_replace(
//                    $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
//                ));
//            }
//        }


        $this->info('✔ Kopokopo Installed.');
    }
}
