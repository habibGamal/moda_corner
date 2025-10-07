<?php

namespace App\Filament\Resources\GovResource\Pages;

use App\Filament\Resources\GovResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
