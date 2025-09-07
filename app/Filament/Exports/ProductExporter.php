<?php

namespace App\Filament\Exports;

use App\Models\Product;
use App\Models\ProductVariant;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Builder;

class ProductExporter extends Exporter
{
    // Use ProductVariant as base model to export each variant as a row
    protected static ?string $model = ProductVariant::class;
    protected static ?string $label = 'تصدير متغيرات المنتجات';

    public static function getColumns(): array
    {
        return [
            // Product details via relationship
            ExportColumn::make('product.id')->label('معرف المنتج'),
            ExportColumn::make('product.name_en')->label('الاسم (EN)'),
            ExportColumn::make('product.name_ar')->label('الاسم (AR)'),
            ExportColumn::make('product.slug')->label('الرابط المختصر'),
            ExportColumn::make('product.category.name_ar')->label('الفئة'),
            ExportColumn::make('product.brand.name_ar')->label('العلامة التجارية'),

            // Variant columns
            ExportColumn::make('id')->label('معرف المتغير'),
            ExportColumn::make('sku')->label('رمز المتغير'),
            ExportColumn::make('quantity')->label('الكمية'),
            ExportColumn::make('price')->label('سعر المتغير'),
            ExportColumn::make('sale_price')->label('سعر العرض'),
            ExportColumn::make('color')->label('اللون'),
            ExportColumn::make('size')->label('الحجم'),
            ExportColumn::make('capacity')->label('السعة'),
            ExportColumn::make('images')
                ->label('الصور')
                ->formatStateUsing(function ($state): string {
                    if (!$state) return '';
                    return is_array($state) ? implode(',', $state) : (string) $state;
                }),
            ExportColumn::make('is_default')
                ->label('افتراضي')
                ->formatStateUsing(fn(bool $state) => $state ? 'نعم' : 'لا'),
            ExportColumn::make('is_active')
                ->label('نشط')
                ->formatStateUsing(fn(bool $state) => $state ? 'نعم' : 'لا'),
            ExportColumn::make('additional_attributes')
                ->label('خصائص إضافية')
                ->formatStateUsing(fn($state): string => $state ? json_encode($state, JSON_UNESCAPED_UNICODE) : ''),
            ExportColumn::make('created_at')->label('تاريخ الإنشاء'),
            ExportColumn::make('updated_at')->label('تاريخ التحديث'),
        ];
    }

    public static function modifyQuery(Builder $query): Builder
    {
        // Load product with its category and brand for each variant
        return $query->with(['product.category', 'product.brand']);
    }

    // use default getRecords from Exporter: each ProductVariant record will be exported

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . number_format($export->successful_rows) . ' متغير منتج بنجاح.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' عنصر فشل في التصدير.';
        }

        return $body;
    }
}
