<?php

namespace App\Filament\Resources\ReturnOrderResource\Pages;

use App\Filament\Resources\ReturnOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReturnOrder extends ViewRecord
{
    protected static string $resource = ReturnOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
