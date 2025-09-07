<?php

namespace App\Filament\Resources\DirectPromotionResource\Pages;

use App\Filament\Resources\DirectPromotionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDirectPromotion extends EditRecord
{
    protected static string $resource = DirectPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
