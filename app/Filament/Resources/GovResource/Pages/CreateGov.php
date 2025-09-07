<?php

namespace App\Filament\Resources\GovResource\Pages;

use App\Filament\Resources\GovResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGov extends CreateRecord
{
    protected static string $resource = GovResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'تم إنشاء المحافظة بنجاح';
    }
}
