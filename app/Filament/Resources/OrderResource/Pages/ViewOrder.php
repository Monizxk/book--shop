<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('order_number')
                                    ->label('Order Number'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'pending' => 'warning',
                                        'confirmed' => 'primary',
                                        'processing' => 'info',
                                        'shipped', 'delivered' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                                TextEntry::make('created_at')
                                    ->label('Created At')
                                    ->dateTime('d.m.Y H:i'),
                            ]),
                    ]),

                Section::make('Customer Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('full_name')
                                    ->label('Full Name'),
                                TextEntry::make('email')
                                    ->label('Email'),
                                TextEntry::make('phone')
                                    ->label('Phone'),
                            ]),
                    ]),

                Section::make('Delivery Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('delivery_method')
                                    ->label('Delivery Method')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'novaPoshta' => 'Нова Пошта',
                                        'ukrPoshta' => 'Укрпошта',
                                        'selfPickup' => 'Самовивіз',
                                        default => $state,
                                    }),
                                TextEntry::make('city')
                                    ->label('City')
                                    ->placeholder('Not specified'),
                                TextEntry::make('post_office')
                                    ->label('Post Office')
                                    ->placeholder('Not specified'),
                            ]),
                    ]),

                Section::make('Payment Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->label('Payment Method')
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'cashOnDelivery' => 'Накладений платіж',
                                        'cardOnline' => 'Оплата онлайн',
                                        default => $state,
                                    }),
                                TextEntry::make('comment')
                                    ->label('Comment')
                                    ->placeholder('No comment'),
                            ]),
                    ]),

                Section::make('Order Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('product_name')
                                            ->label('Product'),
                                        TextEntry::make('product.title')
                                            ->label('Current Title'),
                                        TextEntry::make('quantity')
                                            ->label('Qty'),
                                        TextEntry::make('price')
                                            ->label('Price')
                                            ->money('UAH'),
                                        TextEntry::make('total')
                                            ->label('Total')
                                            ->state(fn ($record) => $record->quantity * $record->price)
                                            ->money('UAH'),
                                    ]),
                            ]),
                    ]),

                Section::make('Order Totals')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->money('UAH'),
                                TextEntry::make('delivery_cost')
                                    ->label('Delivery Cost')
                                    ->money('UAH'),
                                TextEntry::make('total')
                                    ->label('Total')
                                    ->money('UAH')
                                    ->size(TextEntry\TextEntrySize::Large)
                                    ->weight('bold'),
                            ]),
                    ]),
            ]);
    }
}
