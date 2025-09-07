<?php

namespace App\Filament\Resources\DirectPromotionResource\Pages;

use App\Filament\Resources\DirectPromotionResource;
use App\Filament\Widgets\DirectPromotionStatsWidget;
use App\Services\DirectPromotionService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDirectPromotions extends ListRecords
{
    protected static string $resource = DirectPromotionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('revert_all_discounts')
                ->label('إلغاء جميع الخصومات')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('إلغاء جميع خصومات الأسعار')
                ->modalDescription('هذا سيقوم بإلغاء جميع خصومات الأسعار المطبقة حالياً على جميع المنتجات. هل أنت متأكد؟')
                ->action(function () {
                    $service = app(DirectPromotionService::class);
                    $revertedCount = $service->revertPriceDiscounts();

                    $this->redirect(static::getUrl());

                    \Filament\Notifications\Notification::make()
                        ->title('تم إلغاء جميع الخصومات')
                        ->body("تم إلغاء الخصم من {$revertedCount} منتج")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DirectPromotionStatsWidget::class,
        ];
    }
}
