<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListSettings extends ListRecords
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('update_delivery_cost')
                ->label('Update Delivery Cost')
                ->form([
                    TextInput::make('delivery_cost')
                        ->label('Ціна за доставку (UAH)')
                        ->numeric()
                        ->required()
                        ->default(fn () => Setting::where('key', 'delivery_cost')->first()?->value ?? 50)
                        ->minValue(0)
                        ->helperText('Введіть ціну за доставку в гривнях (наприклад, 60).'),
                ])
                ->action(function (array $data) {
                    Setting::updateOrCreate(
                        ['key' => 'delivery_cost'],
                        ['value' => $data['delivery_cost']]
                    );
                    Notification::make()
                        ->title('Delivery cost updated')
                        ->success()
                        ->send();
                    $this->refreshTable();
                })
                ->modalHeading('Налаштування ціни доставки')
                ->modalSubmitActionLabel('Зберегти')
                ->modalCancelActionLabel('Скасувати'),
        ];
    }

    protected function refreshTable()
    {
        $this->resetTable();
    }
}
