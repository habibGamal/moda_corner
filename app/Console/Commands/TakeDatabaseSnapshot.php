<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TakeDatabaseSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:take-database-snapshot';

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

        $databaseName = env('DB_DATABASE');
        $fileName = 'snapshot_'.date('Y_m_d_H_i_s').'.sql';
        $directoryPath = public_path('sqlfiles');
        if (! file_exists($directoryPath)) {
            mkdir($directoryPath, 0755, true);
        }
        $filePath = $directoryPath.'/'.$fileName;

        $command = sprintf(
            'mysqldump --user=%s --password=%s --host=%s %s users sessions categories brands products product_variants > %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            $databaseName,
            $filePath
        );

        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Database snapshot taken successfully.');
        } else {
            $this->error('Failed to take database snapshot.');
        }
    }
}
