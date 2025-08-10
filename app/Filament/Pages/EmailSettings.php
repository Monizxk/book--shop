<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\OrderNotificationService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Actions\Action;

class EmailSettings extends Page
{
    protected static string $view = 'filament.pages.email-settings';
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationLabel = 'Email Settings';
    protected static ?string $title = 'Email Settings';
    protected static ?string $slug = 'email-settings';
    protected static ?int $navigationSort = 2;

    public ?string $seller_email = '';
    public bool $notifications_enabled = true;

    public function mount(): void
    {
        $this->seller_email = OrderNotificationService::getSellerEmail();
        $this->notifications_enabled = OrderNotificationService::areSellerNotificationsEnabled();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Налаштування сповіщень про замовлення')
                    ->description('Налаштуйте надсилання email-сповіщень про нові замовлення')
                    ->schema([
                        TextInput::make('seller_email')
                            ->label('Email продавця')
                            ->email()
                            ->required()
                            ->placeholder('seller@example.com')
                            ->helperText('Email-адреса, на яку будуть надсилатися сповіщення про замовлення')
                            ->live(),

                        Toggle::make('notifications_enabled')
                            ->label('Увімкнути сповіщення про замовлення')
                            ->helperText('Надсилати email-сповіщення продавцю при створенні нових замовлень')
                            ->default(true)
                            ->live(),
                    ])
                    ->columns(1),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Зберегти налаштування')
                ->submit('save')
                ->color('primary'),
        ];
    }

    public function save(): void
    {
        $this->validate([
            'seller_email' => 'required|email',
            'notifications_enabled' => 'boolean',
        ]);

        try {
            OrderNotificationService::setSellerEmail($this->seller_email);

            OrderNotificationService::setSellerNotificationsEnabled($this->notifications_enabled);

            Notification::make()
                ->title('Налаштування успішно збережено')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Помилка збереження налаштувань')
                ->body('Спробуйте ще раз')
                ->danger()
                ->send();
        }
    }

}
