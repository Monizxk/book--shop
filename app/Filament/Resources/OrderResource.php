<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
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
    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Customer Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('full_name')
                                    ->label('Full Name')
                                    ->required(),
                                TextInput::make('email')
                                    ->email()
                                    ->required(),
                                TextInput::make('phone')
                                    ->label('Phone')
                                    ->required(),
                                TextInput::make('order_number')
                                    ->label('Order Number')
                                    ->disabled()
                                    ->visibleOn('edit'),
                            ]),
                    ]),

                Section::make('Delivery Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('delivery_method')
                                    ->label('Delivery Method')
                                    ->options([
                                        'novaPoshta' => 'Нова Пошта',
                                        'ukrPoshta' => 'Укрпошта',
                                        'selfPickup' => 'Самовивіз',
                                    ])
                                    ->required(),
                                TextInput::make('city')
                                    ->label('City')
                                    ->nullable(),
                                TextInput::make('post_office')
                                    ->label('Post Office')
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Payment Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('payment_method')
                                    ->label('Payment Method')
                                    ->options([
                                        'cashOnDelivery' => 'Накладений платіж',
                                        'cardOnline' => 'Оплата онлайн',
                                    ])
                                    ->required(),
                                Select::make('status')
                                    ->label('Order Status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'confirmed' => 'Confirmed',
                                        'processing' => 'Processing',
                                        'shipped' => 'Shipped',
                                        'delivered' => 'Delivered',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->default('pending')
                                    ->required(),
                            ]),
                        Textarea::make('comment')
                            ->label('Comment')
                            ->nullable()
                            ->rows(3),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        Repeater::make('items')
                            ->label('Products')
                            ->relationship('items')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Product')
                                            ->options(Product::all()->pluck('title', 'id'))
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('product_name', $product->title);
                                                    $set('price', $product->price);
                                                }
                                            }),
                                        TextInput::make('product_name')
                                            ->label('Product Name')
                                            ->required(),
                                        TextInput::make('quantity')
                                            ->label('Quantity')
                                            ->numeric()
                                            ->default(1)
                                            ->required(),
                                        TextInput::make('price')
                                            ->label('Price')
                                            ->numeric()
                                            ->required(),
                                    ]),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Add Product')
                            ->collapsible(),
                    ]),

                Section::make('Order Totals')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->disabled()
                                    ->suffix('₴'),
                                TextInput::make('delivery_cost')
                                    ->label('Delivery Cost')
                                    ->numeric()
                                    ->disabled()
                                    ->suffix('₴'),
                                TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->disabled()
                                    ->suffix('₴'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Phone'),
                BadgeColumn::make('delivery_method')
                    ->label('Delivery')
                    ->colors([
                        'primary' => 'novaPoshta',
                        'success' => 'ukrPoshta',
                        'warning' => 'selfPickup',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'novaPoshta' => 'Нова Пошта',
                        'ukrPoshta' => 'Укрпошта',
                        'selfPickup' => 'Самовивіз',
                        default => $state,
                    }),
                BadgeColumn::make('payment_method')
                    ->label('Payment')
                    ->colors([
                        'primary' => 'cashOnDelivery',
                        'success' => 'cardOnline',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cashOnDelivery' => 'При отриманні',
                        'cardOnline' => 'Онлайн',
                        default => $state,
                    }),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'confirmed',
                        'info' => 'processing',
                        'success' => ['shipped', 'delivered'],
                        'danger' => 'cancelled',
                    ]),
                TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('UAH')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'processing' => 'Processing',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('delivery_method')
                    ->options([
                        'novaPoshta' => 'Нова Пошта',
                        'ukrPoshta' => 'Укрпошта',
                        'selfPickup' => 'Самовивіз',
                    ]),
                SelectFilter::make('payment_method')
                    ->options([
                        'cashOnDelivery' => 'При отриманні',
                        'cardOnline' => 'Онлайн',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'delete' => Pages\DeleteOrder::route('/{record}/delete'),
        ];
    }
}
