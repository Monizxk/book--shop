<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $slug = 'settings';
    protected static ?string $modelLabel = 'Setting';
    protected static ?string $pluralModelLabel = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('key')
                    ->label('Setting Key')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->options([
                        'delivery_cost' => 'Delivery Cost',
                        'working_hours.weekdays.label' => 'Working Hours - Weekdays Label',
                        'working_hours.weekdays.hours' => 'Working Hours - Weekdays Hours',
                        'working_hours.weekdays.enabled' => 'Working Hours - Weekdays Enabled',
                        'working_hours.saturday.label' => 'Working Hours - Saturday Label',
                        'working_hours.saturday.hours' => 'Working Hours - Saturday Hours',
                        'working_hours.saturday.enabled' => 'Working Hours - Saturday Enabled',
                        'working_hours.sunday.label' => 'Working Hours - Sunday Label',
                        'working_hours.sunday.hours' => 'Working Hours - Sunday Hours',
                        'working_hours.sunday.enabled' => 'Working Hours - Sunday Enabled',
                        'contact_phone' => 'Contact Phone',
                        'contact_viber' => 'Contact Viber',
                        'contact_email' => 'Contact Email',
                        'delivery_title' => 'Delivery Page Title',
                        'delivery_description' => 'Delivery Page Description',
                        'delivery_np_branch_title' => 'Delivery Nova Poshta Branch Title',
                        'delivery_np_branch_desc' => 'Delivery Nova Poshta Branch Description',
                        'delivery_np_branch_price' => 'Delivery Nova Poshta Branch Price',
                        'delivery_np_address_title' => 'Delivery Nova Poshta Address Title',
                        'delivery_np_address_desc' => 'Delivery Nova Poshta Address Description',
                        'delivery_np_address_price' => 'Delivery Nova Poshta Address Price',
                        'delivery_howto_title' => 'Delivery HowTo Title',
                        'delivery_howto_list' => 'Delivery HowTo List (one per line)',
                        'delivery_terms_title' => 'Delivery Terms Title',
                        'delivery_terms_list' => 'Delivery Terms List (one per line)',
                        'payment_title' => 'Payment Page Title',
                        'payment_np_title' => 'Payment Nova Poshta Title',
                        'payment_np_card' => 'Payment Nova Poshta Card',
                        'payment_np_link' => 'Payment Nova Poshta Link',
                        'payment_np_bank' => 'Payment Nova Poshta Bank',
                        'payment_np_bank_note' => 'Payment Nova Poshta Bank Note',
                        'payment_np_cod' => 'Payment Nova Poshta COD',
                        'payment_np_cod_note' => 'Payment Nova Poshta COD Note',
                        'payment_np_cod_important' => 'Payment Nova Poshta COD Important',
                        'payment_np_cod_important2' => 'Payment Nova Poshta COD Important 2',
                    ])
                    ->searchable()
                    ->allowHtml()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('value', ''))
                    ->placeholder('Select a setting key')
                    ->helperText('Choose from predefined settings or type a custom key'),

                Forms\Components\TextInput::make('custom_key')
                    ->label('Custom Key')
                    ->visible(fn (callable $get) => !in_array($get('key'), [
                        'delivery_cost',
                        'working_hours.weekdays.label',
                        'working_hours.weekdays.hours',
                        'working_hours.weekdays.enabled',
                        'working_hours.saturday.label',
                        'working_hours.saturday.hours',
                        'working_hours.saturday.enabled',
                        'working_hours.sunday.label',
                        'working_hours.sunday.hours',
                        'working_hours.sunday.enabled',
                        'contact_phone',
                        'contact_viber',
                        'contact_email',
                        'delivery_title',
                        'delivery_description',
                        'delivery_np_branch_title',
                        'delivery_np_branch_desc',
                        'delivery_np_branch_price',
                        'delivery_np_address_title',
                        'delivery_np_address_desc',
                        'delivery_np_address_price',
                        'delivery_howto_title',
                        'delivery_howto_list',
                        'delivery_terms_title',
                        'delivery_terms_list',
                        'payment_title',
                        'payment_np_title',
                        'payment_np_card',
                        'payment_np_link',
                        'payment_np_bank',
                        'payment_np_bank_note',
                        'payment_np_cod',
                        'payment_np_cod_note',
                        'payment_np_cod_important',
                        'payment_np_cod_important2',
                    ]))
                    ->afterStateUpdated(function (callable $set, $state) {
                        if ($state) {
                            $set('key', $state);
                        }
                    })
                    ->placeholder('Enter custom setting key'),

                Forms\Components\Group::make([

                    Forms\Components\TextInput::make('value')
                        ->label('Delivery Cost (UAH)')
                        ->numeric()
                        ->suffix('UAH')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_cost')
                        ->required()
                        ->placeholder('e.g., 250'),

                    Forms\Components\TextInput::make('value')
                        ->label('Day Label')
                        ->visible(fn (callable $get) => in_array($get('key'), [
                            'working_hours.weekdays.label',
                            'working_hours.saturday.label',
                            'working_hours.sunday.label'
                        ]))
                        ->required()
                        ->placeholder('e.g., ПН, ВТ, СР, ЧТ, ПТ'),

                    Forms\Components\TextInput::make('value')
                        ->label('Working Hours')
                        ->visible(fn (callable $get) => in_array($get('key'), [
                            'working_hours.weekdays.hours',
                            'working_hours.saturday.hours',
                            'working_hours.sunday.hours'
                        ]))
                        ->required()
                        ->placeholder('e.g., з 9:00 до 18:00'),

                    Forms\Components\Toggle::make('value')
                        ->label('Enabled')
                        ->visible(fn (callable $get) => in_array($get('key'), [
                            'working_hours.weekdays.enabled',
                            'working_hours.saturday.enabled',
                            'working_hours.sunday.enabled'
                        ]))
                        ->onColor('success')
                        ->offColor('danger')
                        ->formatStateUsing(fn ($state) => $state === '1' || $state === true)
                        ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),

                    Forms\Components\TextInput::make('value')
                        ->label('Value')
                        ->visible(fn (callable $get) => !in_array($get('key'), [
                            'delivery_cost',
                            'working_hours.weekdays.label',
                            'working_hours.weekdays.hours',
                            'working_hours.weekdays.enabled',
                            'working_hours.saturday.label',
                            'working_hours.saturday.hours',
                            'working_hours.saturday.enabled',
                            'working_hours.sunday.label',
                            'working_hours.sunday.hours',
                            'working_hours.sunday.enabled'
                        ]))
                        ->required()
                        ->placeholder('Enter value'),

                    Forms\Components\TextInput::make('value')
                        ->label('Phone')
                        ->visible(fn (callable $get) => $get('key') === 'contact_phone')
                        ->required()
                        ->placeholder('e.g., +380 (63) 755-42-70'),

                    Forms\Components\TextInput::make('value')
                        ->label('Viber')
                        ->visible(fn (callable $get) => $get('key') === 'contact_viber')
                        ->required()
                        ->placeholder('e.g., +380 (63) 755-42-70'),

                    Forms\Components\TextInput::make('value')
                        ->label('Email')
                        ->visible(fn (callable $get) => $get('key') === 'contact_email')
                        ->required()
                        ->placeholder('e.g., bookseller.in.ua@gmail.com'),

                    Forms\Components\TextInput::make('value')
                        ->label('Delivery Page Title')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_title')
                        ->required()
                        ->placeholder('e.g., Доставка в інтернет-магазині «BookSeller»'),

                    Forms\Components\Textarea::make('value')
                        ->label('Delivery Page Description')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_description')
                        ->required()
                        ->placeholder('e.g., Швидка та надійна доставка книг по всій Україні через службу «Нова Пошта»'),

                    Forms\Components\TextInput::make('value')
                        ->label('Nova Poshta Branch Title')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_np_branch_title')
                        ->required()
                        ->placeholder('e.g., Нова Пошта (відділення або поштомат)'),

                    Forms\Components\Textarea::make('value')
                        ->label('Nova Poshta Branch Description')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_np_branch_desc')
                        ->required()
                        ->placeholder('e.g., Отримайте замовлення у найближчому відділенні або поштоматі Нової Пошти'),

                    Forms\Components\TextInput::make('value')
                        ->label('Nova Poshta Branch Price')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_np_branch_price')
                        ->required()
                        ->placeholder('e.g., згідно з тарифами компанії Нова Пошта'),

                    Forms\Components\TextInput::make('value')
                        ->label('Nova Poshta Address Title')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_np_address_title')
                        ->required()
                        ->placeholder('e.g., Нова Пошта (адресна доставка)'),

                    Forms\Components\Textarea::make('value')
                        ->label('Nova Poshta Address Description')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_np_address_desc')
                        ->required()
                        ->placeholder('e.g., Доставка безпосередньо за вказаною адресою у зручний для вас час'),

                    Forms\Components\TextInput::make('value')
                        ->label('Nova Poshta Address Price')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_np_address_price')
                        ->required()
                        ->placeholder('e.g., згідно з тарифами компанії Нова Пошта'),

                    Forms\Components\TextInput::make('value')
                        ->label('HowTo Title')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_howto_title')
                        ->required()
                        ->placeholder('e.g., Як оформити замовлення:'),

                    Forms\Components\Textarea::make('value')
                        ->label('HowTo List (one per line)')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_howto_list')
                        ->required()
                        ->placeholder('e.g., Крок 1...\nКрок 2...'),

                    Forms\Components\TextInput::make('value')
                        ->label('Terms Title')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_terms_title')
                        ->required()
                        ->placeholder('e.g., Терміни доставки:'),

                    Forms\Components\Textarea::make('value')
                        ->label('Terms List (one per line)')
                        ->visible(fn (callable $get) => $get('key') === 'delivery_terms_list')
                        ->required()
                        ->placeholder('e.g., По Україні: 1-3 робочих дні\nКиїв: 1-2 робочих дні'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Page Title')
                        ->visible(fn (callable $get) => $get('key') === 'payment_title')
                        ->required()
                        ->placeholder('e.g., Оплата в інтернет-магазині «BookSeller»'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta Title')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_title')
                        ->required()
                        ->placeholder('e.g., Нова Пошта (відділення або поштомат)'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta Card')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_card')
                        ->required()
                        ->placeholder('e.g., Оплата платіжною карткою Visa / Mastercard (Без комісії)'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta Link')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_link')
                        ->required()
                        ->placeholder('e.g., Надсилаємо посилання для оплати, після чого Ви отримуєте чек'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta Bank')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_bank')
                        ->required()
                        ->placeholder('e.g., Безготівковий переказ (за IBAN) згідно рахунку.'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta Bank Note')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_bank_note')
                        ->required()
                        ->placeholder('e.g., Зверніть увагу, що банк може стягувати додаткову комісію за здійснення безготівкового переказу.'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta COD')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_cod')
                        ->required()
                        ->placeholder('e.g., Комісія Нової Пошти за накладений платіж сплачується одержувачем 20 грн + 2% від суми'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta COD Note')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_cod_note')
                        ->required()
                        ->placeholder('e.g., Зверніть увагу, що доставка при накладеному платежі є платною!'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta COD Important')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_cod_important')
                        ->required()
                        ->placeholder('e.g., Для оплати при отриманні потрібно вести 20% від суми'),

                    Forms\Components\TextInput::make('value')
                        ->label('Payment Nova Poshta COD Important 2')
                        ->visible(fn (callable $get) => $get('key') === 'payment_np_cod_important2')
                        ->required()
                        ->placeholder('e.g., Для оплати при отриманні потрібно вести 20% від суми'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->groups([
                Tables\Grouping\Group::make('category')
                    ->label('Category')
                    ->getDescriptionFromRecordUsing(function ($record): string {
                        if ($record->key === 'delivery_cost') {
                            return 'Delivery Settings';
                        }
                        if (str_starts_with($record->key, 'working_hours.')) {
                            return 'Working Hours';
                        }
                        return 'Other Settings';
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }

    public static function canDelete($record): bool
    {
        return true;
    }
}
