<?php

namespace App\Filament\Resources\GovResource\Pages;

use App\Filament\Resources\GovResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewGov extends ViewRecord
{
    protected static string $resource = GovResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('معلومات المحافظة')
                    ->schema([
                        Infolists\Components\TextEntry::make('name_ar')
                            ->label('الاسم بالعربية'),
                        Infolists\Components\TextEntry::make('name_en')
                            ->label('الاسم بالإنجليزية'),
                        Infolists\Components\TextEntry::make('areas_count')
                            ->label('عدد المناطق')
                            ->state(fn ($record) => $record->areas()->count())
                            ->badge()
                            ->color('success'),
                    ])
                    ->columns(2),
                Infolists\Components\Section::make('معلومات النظام')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('تاريخ آخر تحديث')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
