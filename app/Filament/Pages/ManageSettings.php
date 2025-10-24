<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 100;

    protected static ?string $navigationLabel = 'الإعدادات';

    protected static ?string $title = 'إدارة الإعدادات';

    protected static string $view = 'filament.pages.manage-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForms();
    }

    protected function fillForms(): void
    {
        $settings = Setting::ordered()->get();
        $this->data = $settings->pluck('value', 'key')->toArray();

        // Initialize all setting keys as properties to avoid Livewire errors
        foreach ($settings as $setting) {
            if (! property_exists($this, $setting->key)) {
                $this->{$setting->key} = $setting->value;
            }
        }

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        $settings = Setting::ordered()->get()->groupBy('group');

        $schema = [];

        foreach ($settings as $group => $groupSettings) {
            $groupLabel = $this->getGroupLabel($group);
            $groupFields = [];

            foreach ($groupSettings as $setting) {
                $field = $this->createFieldForSetting($setting);
                if ($field) {
                    $groupFields[] = $field;
                }
            }

            if (! empty($groupFields)) {
                $schema[] = Forms\Components\Section::make($groupLabel)
                    ->schema($groupFields)
                    ->collapsible()
                    ->collapsed()
                    ->columns(2);
            }
        }

        return $form
            ->schema($schema)
            ->statePath('data');
    }

    protected function createFieldForSetting(Setting $setting): ?Forms\Components\Component
    {
        $baseField = match ($setting->type) {
            'text', 'url' => Forms\Components\TextInput::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar)
                ->required($setting->is_required)
                ->url($setting->type === 'url'),

            'textarea' => Forms\Components\RichEditor::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar)
                ->required($setting->is_required)
                ->columnSpanFull(),

            'integer' => Forms\Components\TextInput::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar)
                ->required($setting->is_required)
                ->numeric()
                ->integer(),

            'float' => Forms\Components\TextInput::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar)
                ->required($setting->is_required)
                ->numeric(),

            'boolean' => Forms\Components\Toggle::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar),

            'image' => Forms\Components\FileUpload::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar)
                ->required($setting->is_required)
                ->image()
                ->directory($setting->group === 'images' ? 'images' : 'settings')
                ->imageEditor(),

            'json' => $setting->key === 'delivery_time_options'
                ? Forms\Components\TagsInput::make($setting->key)
                    ->label($setting->label_ar)
                    ->helperText($setting->description_ar)
                    ->required($setting->is_required)
                    ->placeholder('أضف خيار وقت التوصيل')
                : Forms\Components\KeyValue::make($setting->key)
                    ->label($setting->label_ar)
                    ->helperText($setting->description_ar)
                    ->required($setting->is_required),

            'select' => Forms\Components\Select::make($setting->key)
                ->label($setting->label_ar)
                ->helperText($setting->description_ar)
                ->required($setting->is_required)
                ->options($this->getSelectOptions($setting->key)),

            default => null,
        };

        return $baseField;
    }

    protected function getGroupLabel(string $group): string
    {
        return match ($group) {
            'general' => 'الإعدادات العامة',
            'appearance' => 'إعدادات المظهر',
            'seo' => 'تحسين محركات البحث',
            'social' => 'وسائل التواصل الاجتماعي',
            'analytics' => 'إعدادات التحليلات',
            'contact' => 'معلومات الاتصال',
            'email' => 'إعدادات البريد الإلكتروني',
            'payment' => 'إعدادات الدفع',
            'legal' => 'الإعدادات القانونية',
            'images' => 'الصور الافتراضية',
            'order_management' => 'إدارة الطلبات',
            'stock_management' => 'إدارة المخزون',
            'business_hours' => 'ساعات العمل',
            'notifications' => 'الإشعارات',
            default => ucfirst($group),
        };
    }

    protected function getSelectOptions(string $key): array
    {
        // Add custom select options based on the setting key if needed
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('حفظ الإعدادات')
                ->action('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                $setting = Setting::where('key', $key)->first();
                if ($setting) {
                    // Handle different value types
                    $processedValue = match ($setting->type) {
                        'boolean' => $value ? '1' : '0',
                        'json' => json_encode($value),
                        'integer' => (string) (int) $value,
                        'float' => (string) (float) $value,
                        default => $value,
                    };

                    $setting->update(['value' => $processedValue]);
                }
            }

            Notification::make()
                ->title('تم حفظ الإعدادات بنجاح')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('حدث خطأ أثناء حفظ الإعدادات')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
