<?php

namespace App\Filament\Resources\GovResource\Pages;

use App\Filament\Resources\GovResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGov extends EditRecord
{
    protected static string $resource = GovResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make()
                ->before(function () {
                    if ($this->record->areas()->count() > 0) {
                        throw new \Exception('لا يمكن حذف المحافظة لأنها تحتوي على مناطق');
                    }
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'تم تحديث المحافظة بنجاح';
    }
}
