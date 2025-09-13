<?php

namespace App\Filament\Pages;

use App\Models\ReturnPolicySetting;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ReturnPolicySettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Return Policy Settings';

    protected static ?string $title = 'Return Policy Settings';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.return-policy-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin ?? false;
    }

    public function mount(): void
    {
        $settings = $this->getSettings();
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    Grid::make(2)
                        ->schema([
                            Toggle::make('returns_enabled')
                                ->label('Enable Returns')
                                ->helperText('Allow customers to request returns for their orders')
                                ->default(true),

                            TextInput::make('return_window_days')
                                ->label('Return Window (Days)')
                                ->helperText('Number of days customers can request returns after order completion')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(365)
                                ->default(30)
                                ->required(),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Toggle::make('require_admin_approval')
                                ->label('Require Admin Approval')
                                ->helperText('Returns must be approved by an admin before processing')
                                ->default(true),

                            Toggle::make('auto_approve_unused')
                                ->label('Auto-approve Unused Items')
                                ->helperText('Automatically approve returns for items marked as unused')
                                ->default(false),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Select::make('allowed_return_reasons')
                                ->label('Allowed Return Reasons')
                                ->helperText('Select which return reasons customers can choose from')
                                ->multiple()
                                ->options([
                                    'defective' => 'Defective/Damaged',
                                    'wrong_item' => 'Wrong Item Received',
                                    'not_as_described' => 'Not as Described',
                                    'changed_mind' => 'Changed Mind',
                                    'size_issue' => 'Size/Fit Issue',
                                    'quality_issue' => 'Quality Issue',
                                    'other' => 'Other',
                                ])
                                ->default(['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'size_issue'])
                                ->required(),

                            TextInput::make('max_return_percentage')
                                ->label('Max Return Percentage (%)')
                                ->helperText('Maximum percentage of order value that can be returned')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100)
                                ->default(100)
                                ->suffix('%')
                                ->required(),
                        ]),

                    Grid::make(2)
                        ->schema([
                            Toggle::make('require_return_fee')
                                ->label('Require Return Fee')
                                ->helperText('Charge a fee for processing returns')
                                ->default(false),

                            TextInput::make('return_fee_amount')
                                ->label('Return Fee Amount')
                                ->helperText('Fixed fee amount for return processing')
                                ->numeric()
                                ->minValue(0)
                                ->prefix('$')
                                ->visible(fn ($get) => $get('require_return_fee')),
                        ]),

                    Grid::make(1)
                        ->schema([
                            Toggle::make('email_notifications')
                                ->label('Email Notifications')
                                ->helperText('Send email notifications for return status updates')
                                ->default(true),

                            Toggle::make('sms_notifications')
                                ->label('SMS Notifications')
                                ->helperText('Send SMS notifications for return status updates')
                                ->default(false),
                        ]),
                ])
                    ->heading('Return Policy Configuration')
                    ->description('Configure your store\'s return policy settings and rules.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            ReturnPolicySetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title('Settings saved successfully!')
            ->success()
            ->send();
    }

    protected function getSettings(): array
    {
        $settings = ReturnPolicySetting::pluck('value', 'key')->toArray();

        // Set defaults if settings don't exist
        return array_merge([
            'returns_enabled' => true,
            'return_window_days' => 30,
            'require_admin_approval' => true,
            'auto_approve_unused' => false,
            'allowed_return_reasons' => ['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'size_issue'],
            'max_return_percentage' => 100,
            'require_return_fee' => false,
            'return_fee_amount' => 0,
            'email_notifications' => true,
            'sms_notifications' => false,
        ], $settings);
    }
}
