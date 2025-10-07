<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use App\Filament\Resources\ProductResource;
use App\Models\ProductVariant;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ExportAction::make()
                ->label('تصدير المنتجات')
                ->exporter(ProductExporter::class)
                ->modifyQueryUsing(
                    fn () => ProductVariant::query()
                        ->with(['product.category', 'product.brand'])
                ),
            Actions\ImportAction::make()
                ->label('استيراد المنتجات')
                ->importer(ProductImporter::class),
            Actions\CreateAction::make(),
        ];
    }
}
