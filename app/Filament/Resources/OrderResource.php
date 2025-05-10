<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Orders';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Section::make('Інформація про клієнта')->schema([
                TextInput::make('first_name')
                    ->required()
                    ->label('Імʼя'),
                TextInput::make('last_name')
                    ->required()
                    ->label('Прізвище'),
                TextInput::make('middle_name')
                    ->label('По батькові'),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->label('Телефон'),
                TextInput::make('city')
                    ->required()
                    ->label('Місто'),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->label('Електронна пошта'),
                TextInput::make('nova_poshta_office')
                    ->label('Склад нової пошти'),
                TextInput::make('nova_poshta_locker')
                    ->label('Номер поштомату'),
            ])->columns(2),
            Section::make('Order Details')->schema([
                Select::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ])
                    ->required()
                    ->label('Order Status'),
                Select::make('payment_method')
                    ->options([
                        'card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'cash' => 'Cash on Delivery',
                    ])
                    ->required()
                    ->label('Payment Method'),
                Select::make('delivery_method')
                    ->options([
                        'standard' => 'Standard Shipping',
                        'express' => 'Express Shipping',
                        'pickup' => 'Store Pickup',
                    ])
                    ->required()
                    ->label('Delivery Method'),
                TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('$')
                    ->label('Total Amount')
                    ->required(),
                TextInput::make('tracking_number')
                    ->label('Tracking Number')
                    ->nullable(),
                Textarea::make('notes')
                    ->label('Notes')
                    ->columnSpanFull(),
            ])->columns(2),
            Section::make('Order Items')->schema([
                Repeater::make('orderItems')
                    ->relationship()
                    ->schema([
                        Select::make('product_id')
                            ->label('Товар')
                            ->relationship('product', 'title')
                            ->required()
                            ->preload(),
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->label('Quantity'),
                        TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->label('Price'),
                        TextInput::make('discount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->label('Discount'),
                    ])
                    ->columns(4)
                    ->label('Order Items'),
            ]),
            Section::make('Status History')->schema([
                Repeater::make('statusHistory')
                    ->relationship()
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'shipped' => 'Shipped',
                                'delivered' => 'Delivered',
                                'canceled' => 'Canceled',
                            ])
                            ->required()
                            ->label('Status'),
                        Textarea::make('comment')
                            ->label('Comment'),
                        TextInput::make('updated_at')
                            ->disabled()
                            ->default(now())
                            ->label('Updated At'),
                    ])
                    ->columns(3)
                    ->label('Status History'),
            ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('city')
                    ->label('City')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('usd')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->getStateUsing(function ($record) {
                        return ucfirst($record->status);
                    })
                    ->colors([
                        'warning' => 'new',
                        'primary' => 'processing',
                        'success' => 'shipped',
                        'success' => 'delivered',
                        'danger' => 'canceled',
                    ])
                    ->label('Status')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Order Date')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'canceled' => 'Canceled',
                    ])
                    ->label('Status'),
                SelectFilter::make('payment_method')
                    ->options([
                        'card' => 'Credit Card',
                        'paypal' => 'PayPal',
                        'cash' => 'Cash on Delivery',
                    ])
                    ->label('Payment Method'),
                SelectFilter::make('delivery_method')
                    ->options([
                        'standard' => 'Standard Shipping',
                        'express' => 'Express Shipping',
                        'pickup' => 'Store Pickup',
                    ])
                    ->label('Delivery Method'),
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Order Date From'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Order Date Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    }),
                Filter::make('total_amount')
                    ->form([
                        TextInput::make('amount_from')
                            ->numeric()
                            ->label('Amount From'),
                        TextInput::make('amount_until')
                            ->numeric()
                            ->label('Amount Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '>=', $amount)
                            )
                            ->when(
                                $data['amount_until'],
                                fn (Builder $query, $amount): Builder => $query->where('total_amount', '<=', $amount)
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel Order')
                    ->action(function (Order $record) {
                        $record->update([
                            'status' => 'canceled',
                        ]);
                        
                        $record->statusHistory()->create([
                            'status' => 'canceled',
                            'comment' => 'Order canceled by admin',
                        ]);
                    })
                    ->requiresConfirmation()
                    ->visible(fn (Order $record) => $record->status !== 'canceled'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('cancel')
                    ->label('Cancel Selected')
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'status' => 'canceled',
                            ]);
                            
                            $record->statusHistory()->create([
                                'status' => 'canceled',
                                'comment' => 'Order canceled by admin',
                            ]);
                        }
                    })
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
}