<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:db-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('app:take-database-snapshot');
        $this->call('db:wipe');
        $this->call('migrate');
        $this->call('db:seed');
        // $this->call('app:restore-database-snapshot');

    }
}
