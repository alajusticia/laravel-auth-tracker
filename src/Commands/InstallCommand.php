<?php

namespace ALajusticia\AuthTracker\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracker:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auth Tracker scaffolding';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Publishing configuration...');

        $this->call('vendor:publish', ['--tag' => 'config']);

        $this->line('');
        $this->comment('Publishing controllers...');

        $this->call('vendor:publish', ['--tag' => 'controllers']);

        $this->line('');
        $this->comment('Publishing views...');

        $this->call('vendor:publish', ['--tag' => 'views']);

        $this->line('');
        $this->comment('Adding routes in web.php...');

        file_put_contents(
            base_path('routes/web.php'),
            file_get_contents(__DIR__.'/../routes.stub'),
            FILE_APPEND
        );

        $this->line('');
        $this->info('Auth Tracker installed!');
    }
}
