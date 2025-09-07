<?php

namespace App\Filament\Resources\GovResource\Pages;

use App\Filament\Resources\GovResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListGovs extends ListRecords
{
    protected static string $resource = GovResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء محافظة جديدة'),
        ];
    }
}
