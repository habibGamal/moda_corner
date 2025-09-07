<?php

namespace App\Jobs;

use Illuminate\Support\Arr;
use Carbon\CarbonInterface;
use Filament\Actions\Imports\Jobs\ImportCsv as BaseImportCsv;


class ImportCsv extends BaseImportCsv
{
    public $tries = 1;

    public function retryUntil(): ?CarbonInterface
    {
        return null;
    }


    protected function handleExceptions(array $exceptions): void
    {
        if (empty($exceptions)) {
            return;
        }

        if (count($exceptions) > 1) {
            $exceptionDetails = [];
            foreach ($exceptions as $type => $exception) {
                $exceptionDetails[] = $type . ': ' . $exception->getMessage() . ' (Line: ' . $exception->getLine() . ', File: ' . $exception->getFile() . ')';
            }
            throw new \Exception('Multiple types of exceptions occurred: ' . implode(' | ', $exceptionDetails));
        }

        throw Arr::first($exceptions);
    }
}
