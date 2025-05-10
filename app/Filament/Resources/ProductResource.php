<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use App\Models\Product;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('images')
                    ->label('Зображення')
                    ->multiple()
                    ->disk('s3')
                    ->directory('products')
                    ->image()
                    ->reorderable()
                    ->preserveFilenames()
                    ->visibility('private'),


                TextInput::make('price')
                    ->numeric()
                    ->label('Ціна')
                    ->suffix('₴'),

                Toggle::make('in_stock')
                    ->label('В наявності')
                    ->default(true),

                Select::make('category_id')
                    ->label('Категорія')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_id')->label('id')->searchable(),
                TextColumn::make('title')->label('Назва')->searchable(),
                TextColumn::make('price')->label('Ціна')->sortable(),
                ToggleColumn::make('in_stock')->label('В наявності'),
                TextColumn::make('category.name')->label('Категорія')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
                'index' => \App\Filament\Resources\ProductResource\Pages\ListProducts::route('/'),
                'create' => \App\Filament\Resources\ProductResource\Pages\CreateProduct::route('/create'),
                'edit' => \App\Filament\Resources\ProductResource\Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}


