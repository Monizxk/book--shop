<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Forms\Form;

class ManageSettings extends ManageRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Сохранить настройки')
                ->action('saveSettings')
                ->color('primary'),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Section::make('Email-уведомления о заказах')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('order_notification_email')
                            ->label('Email продавца для уведомлений')
                            ->email()
                            ->required()
                            ->default(fn() => Setting::getValue('order_notification_email', ''))
                            ->placeholder('seller@yourshop.com'),
                        
                        \Filament\Forms\Components\Toggle::make('order_notification_enabled')
                            ->label('Включить email-уведомления продавцу')
                            ->default(fn() => Setting::getValue('order_notification_enabled', '1') === '1'),
                    ]),
                
                \Filament\Forms\Components\Section::make('Другие настройки')
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('delivery_cost')
                            ->label('Стоимость доставки')
                            ->numeric()
                            ->default(fn() => Setting::getValue('delivery_cost', '50'))
                            ->suffix('грн'),
                    ]),
            ])
            ->statePath('data');
    }

    public function saveSettings(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }
        
        $this->getNotificationManager()->send(
            \Filament\Notifications\Notification::make()
                ->title('Настройки сохранены')
                ->success()
        );
    }

    public function mount(): void
    {
        $this->form->fill([
            'order_notification_email' => Setting::getValue('order_notification_email', ''),
            'order_notification_enabled' => Setting::getValue('order_notification_enabled', '1') === '1',
            'delivery_cost' => Setting::getValue('delivery_cost', '50'),
        ]);
    }
}