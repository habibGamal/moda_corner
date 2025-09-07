<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestoreDatabaseSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:restore-database-snapshot';

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
        $directoryPath = public_path('sqlfiles');
        if (!file_exists($directoryPath)) {
            $this->error('The sqlfiles directory does not exist.');
            return;
        }

        $files = File::files($directoryPath);
        if (empty($files)) {
            $this->error('No SQL files found in the sqlfiles directory.');
            return;
        }

        usort($files, function ($a, $b) {
            return $b->getMTime() - $a->getMTime();
        });

        $latestFile = $files[0]->getRealPath();

        $command = sprintf(
            'mysql --user=%s --password=%s --host=%s %s < %s',
            env('DB_USERNAME'),
            env('DB_PASSWORD'),
            env('DB_HOST'),
            env('DB_DATABASE'),
            $latestFile
        );

        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Database restored successfully.');
        } else {
            $this->error('Failed to restore the database.');
        }
    }
}
