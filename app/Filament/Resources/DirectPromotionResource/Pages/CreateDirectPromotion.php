<?php

namespace App\Filament\Resources\DirectPromotionResource\Pages;

use App\Filament\Resources\DirectPromotionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDirectPromotion extends CreateRecord
{
    protected static string $resource = DirectPromotionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
