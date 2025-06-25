<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Forms;

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

            Action::make('update_working_hours')
                ->label('Редагувати графік роботи')
                ->form([
                    TextInput::make('weekdays_label')
                        ->label('Будні (мітка)')
                        ->default(fn () => Setting::getValue('working_hours.weekdays.label', 'ПН, ВТ, СР, ЧТ, ПТ')),
                    TextInput::make('weekdays_hours')
                        ->label('Будні (час)')
                        ->default(fn () => Setting::getValue('working_hours.weekdays.hours', 'з 9:00 до 18:00')),
                    Toggle::make('weekdays_enabled')
                        ->label('Будні активні')
                        ->default(fn () => Setting::getValue('working_hours.weekdays.enabled', '1') === '1'),

                    TextInput::make('saturday_label')
                        ->label('Субота (мітка)')
                        ->default(fn () => Setting::getValue('working_hours.saturday.label', 'Субота')),
                    TextInput::make('saturday_hours')
                        ->label('Субота (час)')
                        ->default(fn () => Setting::getValue('working_hours.saturday.hours', 'з 10:00 до 15:00')),
                    Toggle::make('saturday_enabled')
                        ->label('Субота активна')
                        ->default(fn () => Setting::getValue('working_hours.saturday.enabled', '1') === '1'),

                    TextInput::make('sunday_label')
                        ->label('Неділя (мітка)')
                        ->default(fn () => Setting::getValue('working_hours.sunday.label', 'Неділя')),
                    TextInput::make('sunday_hours')
                        ->label('Неділя (час)')
                        ->default(fn () => Setting::getValue('working_hours.sunday.hours', 'Вихідний')),
                    Toggle::make('sunday_enabled')
                        ->label('Неділя активна')
                        ->default(fn () => Setting::getValue('working_hours.sunday.enabled', '0') === '1'),
                ])
                ->action(function (array $data) {
                    Setting::updateOrCreate(['key' => 'working_hours.weekdays.label'], ['value' => $data['weekdays_label']]);
                    Setting::updateOrCreate(['key' => 'working_hours.weekdays.hours'], ['value' => $data['weekdays_hours']]);
                    Setting::updateOrCreate(['key' => 'working_hours.weekdays.enabled'], ['value' => $data['weekdays_enabled'] ? '1' : '0']);

                    Setting::updateOrCreate(['key' => 'working_hours.saturday.label'], ['value' => $data['saturday_label']]);
                    Setting::updateOrCreate(['key' => 'working_hours.saturday.hours'], ['value' => $data['saturday_hours']]);
                    Setting::updateOrCreate(['key' => 'working_hours.saturday.enabled'], ['value' => $data['saturday_enabled'] ? '1' : '0']);

                    Setting::updateOrCreate(['key' => 'working_hours.sunday.label'], ['value' => $data['sunday_label']]);
                    Setting::updateOrCreate(['key' => 'working_hours.sunday.hours'], ['value' => $data['sunday_hours']]);
                    Setting::updateOrCreate(['key' => 'working_hours.sunday.enabled'], ['value' => $data['sunday_enabled'] ? '1' : '0']);

                    Notification::make()
                        ->title('Графік роботи оновлено')
                        ->success()
                        ->send();
                    $this->refreshTable();
                })
                ->modalHeading('Редагування графіка роботи')
                ->modalSubmitActionLabel('Зберегти')
                ->modalCancelActionLabel('Скасувати'),

            Action::make('update_contacts')
                ->label('Редагувати контакти')
                ->form([
                    TextInput::make('contact_phone')
                        ->label('Телефон')
                        ->default(fn () => Setting::getValue('contact_phone', '+380 (63) 755-42-70')),
                    TextInput::make('contact_viber')
                        ->label('Viber')
                        ->default(fn () => Setting::getValue('contact_viber', '+380 (63) 755-42-70')),
                    TextInput::make('contact_email')
                        ->label('Email')
                        ->default(fn () => Setting::getValue('contact_email', 'bookseller.in.ua@gmail.com')),
                ])
                ->action(function (array $data) {
                    Setting::updateOrCreate(['key' => 'contact_phone'], ['value' => $data['contact_phone']]);
                    Setting::updateOrCreate(['key' => 'contact_viber'], ['value' => $data['contact_viber']]);
                    Setting::updateOrCreate(['key' => 'contact_email'], ['value' => $data['contact_email']]);
                    Notification::make()
                        ->title('Контакти оновлено')
                        ->success()
                        ->send();
                    $this->refreshTable();
                })
                ->modalHeading('Редагування контактів')
                ->modalSubmitActionLabel('Зберегти')
                ->modalCancelActionLabel('Скасувати'),

            Action::make('update_delivery_texts')
                ->label('Редагувати тексти доставки')
                ->form([
                    TextInput::make('delivery_title')
                        ->label('Заголовок сторінки')
                        ->default(fn () => Setting::getValue('delivery_title', 'Доставка в інтернет-магазині «BookSeller»')),
                    TextInput::make('delivery_description')
                        ->label('Опис сторінки')
                        ->default(fn () => Setting::getValue('delivery_description', 'Швидка та надійна доставка книг по всій Україні через службу «Нова Пошта»')),
                    TextInput::make('delivery_np_branch_title')
                        ->label('Заголовок: Нова Пошта (відділення)')
                        ->default(fn () => Setting::getValue('delivery_np_branch_title', 'Нова Пошта (відділення або поштомат)')),
                    TextInput::make('delivery_np_branch_desc')
                        ->label('Опис: Нова Пошта (відділення)')
                        ->default(fn () => Setting::getValue('delivery_np_branch_desc', 'Отримайте замовлення у найближчому відділенні або поштоматі Нової Пошти')),
                    TextInput::make('delivery_np_branch_price')
                        ->label('Вартість: Нова Пошта (відділення)')
                        ->default(fn () => Setting::getValue('delivery_np_branch_price', 'згідно з тарифами компанії Нова Пошта')),
                    TextInput::make('delivery_np_address_title')
                        ->label('Заголовок: Нова Пошта (адресна доставка)')
                        ->default(fn () => Setting::getValue('delivery_np_address_title', 'Нова Пошта (адресна доставка)')),
                    TextInput::make('delivery_np_address_desc')
                        ->label('Опис: Нова Пошта (адресна доставка)')
                        ->default(fn () => Setting::getValue('delivery_np_address_desc', 'Доставка безпосередньо за вказаною адресою у зручний для вас час')),
                    TextInput::make('delivery_np_address_price')
                        ->label('Вартість: Нова Пошта (адресна доставка)')
                        ->default(fn () => Setting::getValue('delivery_np_address_price', 'згідно з тарифами компанії Нова Пошта')),
                    TextInput::make('delivery_howto_title')
                        ->label('Заголовок: Як оформити замовлення')
                        ->default(fn () => Setting::getValue('delivery_howto_title', 'Як оформити замовлення:')),
                    TextInput::make('delivery_howto_list')
                        ->label('Список: Як оформити замовлення (через \n)')
                        ->default(fn () => Setting::getValue('delivery_howto_list', 'Оберіть потрібні книги та додайте їх до кошика\nПерейдіть до оформлення замовлення\nВкажіть спосіб доставки та адресу\nОберіть зручний спосіб оплати')),
                    TextInput::make('delivery_terms_title')
                        ->label('Заголовок: Терміни доставки')
                        ->default(fn () => Setting::getValue('delivery_terms_title', '⏰ Терміни доставки:')),
                    TextInput::make('delivery_terms_list')
                        ->label('Список: Терміни доставки (через \n)')
                        ->default(fn () => Setting::getValue('delivery_terms_list', 'По Україні: 1-3 робочих дні\nКиїв: 1-2 робочих дні\nВіддалені регіони: 2-4 робочих дні')),
                ])
                ->action(function (array $data) {
                    Setting::updateOrCreate(['key' => 'delivery_title'], ['value' => $data['delivery_title']]);
                    Setting::updateOrCreate(['key' => 'delivery_description'], ['value' => $data['delivery_description']]);
                    Setting::updateOrCreate(['key' => 'delivery_np_branch_title'], ['value' => $data['delivery_np_branch_title']]);
                    Setting::updateOrCreate(['key' => 'delivery_np_branch_desc'], ['value' => $data['delivery_np_branch_desc']]);
                    Setting::updateOrCreate(['key' => 'delivery_np_branch_price'], ['value' => $data['delivery_np_branch_price']]);
                    Setting::updateOrCreate(['key' => 'delivery_np_address_title'], ['value' => $data['delivery_np_address_title']]);
                    Setting::updateOrCreate(['key' => 'delivery_np_address_desc'], ['value' => $data['delivery_np_address_desc']]);
                    Setting::updateOrCreate(['key' => 'delivery_np_address_price'], ['value' => $data['delivery_np_address_price']]);
                    Setting::updateOrCreate(['key' => 'delivery_howto_title'], ['value' => $data['delivery_howto_title']]);
                    Setting::updateOrCreate(['key' => 'delivery_howto_list'], ['value' => $data['delivery_howto_list']]);
                    Setting::updateOrCreate(['key' => 'delivery_terms_title'], ['value' => $data['delivery_terms_title']]);
                    Setting::updateOrCreate(['key' => 'delivery_terms_list'], ['value' => $data['delivery_terms_list']]);
                    Notification::make()
                        ->title('Тексти доставки оновлено')
                        ->success()
                        ->send();
                    $this->refreshTable();
                })
                ->modalHeading('Редагування текстів доставки')
                ->modalSubmitActionLabel('Зберегти')
                ->modalCancelActionLabel('Скасувати'),

            Action::make('update_payment_texts')
                ->label('Редагувати тексти оплати')
                ->form([
                    TextInput::make('payment_title')
                        ->label('Заголовок сторінки')
                        ->default(fn () => Setting::getValue('payment_title', 'Оплата в інтернет-магазині «BookSeller»')),
                    TextInput::make('payment_np_title')
                        ->label('Заголовок: Нова Пошта (відділення)')
                        ->default(fn () => Setting::getValue('payment_np_title', 'Нова Пошта (відділення або поштомат)')),
                    TextInput::make('payment_np_card')
                        ->label('Оплата карткою')
                        ->default(fn () => Setting::getValue('payment_np_card', 'Оплата платіжною карткою Visa / Mastercard (Без комісії)')),
                    TextInput::make('payment_np_link')
                        ->label('Посилання для оплати')
                        ->default(fn () => Setting::getValue('payment_np_link', 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек')),
                    TextInput::make('payment_np_bank')
                        ->label('Банківський переказ')
                        ->default(fn () => Setting::getValue('payment_np_bank', 'Безготівковий переказ (за IBAN) згідно рахунку.')),
                    TextInput::make('payment_np_bank_note')
                        ->label('Примітка до банківського переказу')
                        ->default(fn () => Setting::getValue('payment_np_bank_note', 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.')),
                    TextInput::make('payment_np_cod')
                        ->label('Накладений платіж')
                        ->default(fn () => Setting::getValue('payment_np_cod', 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми')),
                    TextInput::make('payment_np_cod_note')
                        ->label('Примітка до накладеного платежу')
                        ->default(fn () => Setting::getValue('payment_np_cod_note', 'Зверніть увагу, що доставка при накладеному платежі є платною!')),
                    TextInput::make('payment_np_cod_important')
                        ->label('Важливо (1)')
                        ->default(fn () => Setting::getValue('payment_np_cod_important', 'Для оплати при отриманні потрібно вести 20% від суми')),
                    TextInput::make('payment_np_cod_important2')
                        ->label('Важливо (2)')
                        ->default(fn () => Setting::getValue('payment_np_cod_important2', 'Для оплати при отриманні потрібно вести 20% від суми')),
                    TextInput::make('payment_np_address_title')
                        ->label('Заголовок: Нова Пошта (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_title', 'Нова Пошта (адресна доставка)')),
                    TextInput::make('payment_np_address_card')
                        ->label('Оплата карткою (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_card', 'Оплата платіжною карткою Visa / Mastercard (Без комісії)')),
                    TextInput::make('payment_np_address_link')
                        ->label('Посилання для оплати (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_link', 'Надсилаємо посилання для оплати, після чого Ви отримуєте чек')),
                    TextInput::make('payment_np_address_bank')
                        ->label('Банківський переказ (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_bank', 'Безготівковий переказ (за IBAN) згідно рахунку.')),
                    TextInput::make('payment_np_address_bank_note')
                        ->label('Примітка до банківського переказу (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_bank_note', 'Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.')),
                    TextInput::make('payment_np_address_cod')
                        ->label('Накладений платіж (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_cod', 'Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми')),
                    TextInput::make('payment_np_address_cod_note')
                        ->label('Примітка до накладеного платежу (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_cod_note', 'Зверніть увагу, що доставка при накладеному платежі є платною!')),
                    TextInput::make('payment_np_address_cod_important')
                        ->label('Важливо (адресна доставка)')
                        ->default(fn () => Setting::getValue('payment_np_address_cod_important', 'Для оплати при отриманні потрібно вести 20% від суми')),
                ])
                ->action(function (array $data) {
                    Setting::updateOrCreate(['key' => 'payment_title'], ['value' => $data['payment_title']]);
                    Setting::updateOrCreate(['key' => 'payment_np_title'], ['value' => $data['payment_np_title']]);
                    Setting::updateOrCreate(['key' => 'payment_np_card'], ['value' => $data['payment_np_card']]);
                    Setting::updateOrCreate(['key' => 'payment_np_link'], ['value' => $data['payment_np_link']]);
                    Setting::updateOrCreate(['key' => 'payment_np_bank'], ['value' => $data['payment_np_bank']]);
                    Setting::updateOrCreate(['key' => 'payment_np_bank_note'], ['value' => $data['payment_np_bank_note']]);
                    Setting::updateOrCreate(['key' => 'payment_np_cod'], ['value' => $data['payment_np_cod']]);
                    Setting::updateOrCreate(['key' => 'payment_np_cod_note'], ['value' => $data['payment_np_cod_note']]);
                    Setting::updateOrCreate(['key' => 'payment_np_cod_important'], ['value' => $data['payment_np_cod_important']]);
                    Setting::updateOrCreate(['key' => 'payment_np_cod_important2'], ['value' => $data['payment_np_cod_important2']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_title'], ['value' => $data['payment_np_address_title']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_card'], ['value' => $data['payment_np_address_card']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_link'], ['value' => $data['payment_np_address_link']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_bank'], ['value' => $data['payment_np_address_bank']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_bank_note'], ['value' => $data['payment_np_address_bank_note']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_cod'], ['value' => $data['payment_np_address_cod']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_cod_note'], ['value' => $data['payment_np_address_cod_note']]);
                    Setting::updateOrCreate(['key' => 'payment_np_address_cod_important'], ['value' => $data['payment_np_address_cod_important']]);
                    Notification::make()
                        ->title('Тексти оплати оновлено')
                        ->success()
                        ->send();
                    $this->refreshTable();
                })
                ->modalHeading('Редагування текстів оплати')
                ->modalSubmitActionLabel('Зберегти')
                ->modalCancelActionLabel('Скасувати'),
        ];
    }

    protected function refreshTable()
    {
        $this->resetTable();
    }
}
