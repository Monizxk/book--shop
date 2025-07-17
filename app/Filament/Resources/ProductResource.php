<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Components\Checkbox;
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
use Illuminate\Support\Facades\Storage;

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
                    ->disk('public')
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

                Toggle::make('hidden')
                    ->label('Сховати продукт')
                    ->helperText('Сховані продукти не відображаються на сайті')
                    ->default(false),

                Select::make('category_id')
                    ->label('Категорія')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Checkbox::make('is_on_sale')
                    ->label('Розпродаж')
                    ->default(false),

                Checkbox::make('is_on_way')
                    ->label('Готові до відправки')
                    ->default(false),
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
                ToggleColumn::make('hidden')->label('Схований'),
                TextColumn::make('category.name')->label('Категорія')->sortable(),
                ToggleColumn::make('is_on_way')->label('Готові до відправки'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('importProducts')
                    ->label('Імпорт Excel')
                    ->icon('heroicon-o-document-arrow-up')
                    ->form([
                        FileUpload::make('file')
                            ->label('Excel файл')
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                            ->required()
                            ->disk('local')
                            ->directory('imports'),

                        Select::make('default_category_id')
                            ->label('Дефолтна категорія (опціонально)')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Якщо не вказано, категорія буде визначена автоматично'),
                    ])
                    ->action(function (array $data) {
                        try {

                            $fileName = is_array($data['file']) ? $data['file'][0] : $data['file'];

                            if (!Storage::disk('local')->exists($fileName)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Помилка')
                                    ->body('Файл не знайдено в storage: ' . $fileName)
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $filePath = Storage::disk('local')->path($fileName);

                            $import = new \App\Imports\ProductImport($data['default_category_id'] ?? null);
                            $import->import($filePath);

                            Storage::disk('local')->delete($fileName);

                            \Filament\Notifications\Notification::make()
                                ->title('Імпорт завершено успішно!')
                                ->success()
                                ->send();

                        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
                            $failures = $e->failures();
                            $errors = [];

                            foreach ($failures as $failure) {
                                $errors[] = "Рядок {$failure->row()}: " . implode(', ', $failure->errors());
                            }

                            \Filament\Notifications\Notification::make()
                                ->title('Помилка валідації')
                                ->body('Знайдено помилки: ' . implode('; ', array_slice($errors, 0, 3)))
                                ->danger()
                                ->send();

                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Помилка імпорту')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Імпортувати')
                    ->modalCancelActionLabel('Скасувати')
                    ->color('success')
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
