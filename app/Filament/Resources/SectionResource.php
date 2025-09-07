<?php

namespace App\Filament\Resources;

use App\Enums\SectionType;
use App\Filament\Resources\SectionResource\Pages;
use App\Models\Section;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SectionResource extends Resource
{
    protected static ?string $model = Section::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'واجهة المستخدم';

    protected static ?int $navigationSort = 50;

    protected static ?string $label = 'قسم';
    protected static ?string $pluralLabel = 'أقسام';

    protected static ?string $recordTitleAttribute = 'title_ar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات القسم')
                    ->schema([
                        Forms\Components\TextInput::make('title_ar')
                            ->label('العنوان بالعربية')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('title_en')
                            ->label('العنوان بالإنجليزية')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('active')
                            ->label('نشط')
                            ->default(true),
                        Forms\Components\TextInput::make('sort_order')
                            ->label('ترتيب العرض')
                            ->integer()
                            ->default(0),
                    ]),

                Forms\Components\Section::make('منتجات القسم')
                    ->schema([
                        Forms\Components\Select::make('category_filter')
                            ->label('تصفية حسب الفئة')
                            ->options(Category::where('is_active', true)
                                ->pluck('name_' . app()->getLocale(), 'id'))
                            ->searchable()
                            ->dehydrated(false)
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('brand_filter', null);
                            }),

                        Forms\Components\Select::make('brand_filter')
                            ->label('تصفية حسب العلامة التجارية')
                            ->options(function (callable $get) {
                                $categoryId = $get('category_filter');
                                $query = Brand::where('is_active', true);

                                if ($categoryId) {
                                    $productsInCategory = Product::where('category_id', $categoryId)
                                        ->pluck('brand_id');
                                    $query->whereIn('id', $productsInCategory);
                                }

                                return $query->pluck('name_' . app()->getLocale(), 'id');
                            })
                            ->searchable()
                            ->dehydrated(false)
                            ->preload()
                            ->live(),

                        Forms\Components\CheckboxList::make('products')
                            ->label('المنتجات')
                            ->relationship(
                                'products',
                                'name_' . app()->getLocale(),
                                fn(Builder $query, callable $get) => $query
                                    ->where('is_active', true)
                                    ->when($get('category_filter'), fn($q, $category) => $q->where('category_id', $category))
                                    ->when($get('brand_filter'), fn($q, $brand) => $q->where('brand_id', $brand))
                            )
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_ar')
                    ->label('العنوان بالعربية')
                    ->searchable(),
                Tables\Columns\TextColumn::make('title_en')
                    ->label('العنوان بالإنجليزية')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('active')
                    ->label('نشط'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('ترتيب العرض')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('section_type')
                    ->label('نوع القسم')
                    ->badge(),
                Tables\Columns\TextColumn::make('products_count')
                    ->label('عدد المنتجات')
                    ->counts('products')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('تاريخ التحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('active')
                    ->label('نشط')
                    ->placeholder('الكل')
                    ->trueLabel('نشط فقط')
                    ->falseLabel('غير نشط فقط'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hidden(fn(Section $record) => $record->isVirtual),
                Tables\Actions\DeleteAction::make()
                    ->hidden(fn(Section $record) => $record->isVirtual),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                    ,
                    Tables\Actions\BulkAction::make('toggleActive')
                        ->label('تبديل النشاط')
                        ->action(function (Collection $records): void {
                            foreach ($records as $record) {
                                $record->update(['active' => !$record->active]);
                            }
                        })
                        ->icon('heroicon-o-arrow-path'),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn(Section $record) => $record->isReal);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSections::route('/'),
            'create' => Pages\CreateSection::route('/create'),
            'edit' => Pages\EditSection::route('/{record}/edit'),
        ];
    }
}
