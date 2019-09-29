<?php

namespace AnthonyLajusticia\AuthTracker\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth-tracker:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Auth Tracker scaffolding';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->choice('For which authentication guard driver do you want to install the scaffolding?',
            ['Session', 'Passport', 'Session & Passport']);

        $this->comment('Publishing Auth Tracker Configuration...');

        $this->callSilent('vendor:publish', ['--tag' => 'config']);

        $this->comment('Publishing Auth Tracker Assets...');

        switch ($type) {
            case 'Session':
                $this->callSilent('vendor:publish', ['--tag' => 'web-controllers']);
                $this->callSilent('vendor:publish', ['--tag' => 'views']);
                break;
            case 'Passport':
                $this->callSilent('vendor:publish', ['--tag' => 'api-controllers']);
                break;
            default:
                $this->callSilent('vendor:publish', ['--tag' => 'web-controllers']);
                $this->callSilent('vendor:publish', ['--tag' => 'views']);
                $this->callSilent('vendor:publish', ['--tag' => 'api-controllers']);
        }

        $this->callSilent('vendor:publish', ['--tag' => 'notifications']);
        $this->callSilent('vendor:publish', ['--tag' => 'translations']);

        $this->comment('Auth Tracker scaffolding installed successfully.');
    }
}
