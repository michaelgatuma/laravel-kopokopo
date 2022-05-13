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

        $this->info('✔ Kopokopo Installed.');
    }
}
