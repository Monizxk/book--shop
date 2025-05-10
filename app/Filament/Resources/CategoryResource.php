<?php

namespace App\Filament\Resources;

use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;
    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('parent_id')
                    ->label('Parent Category')
                    ->options(function () {
                        return Category::whereNull('parent_id')
                            ->orWhere(function (Builder $query) {
                                $query->whereNotNull('parent_id')
                                    ->whereHas('parent', function (Builder $query) {
                                        $query->whereNull('parent_id');
                                    });
                            })
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Parent Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Category Level')
                    ->getStateUsing(function (Category $record): string {
                        if ($record->parent_id === null) {
                            return 'Level 1';
                        } elseif ($record->parent && $record->parent->parent_id === null) {
                            return 'Level 2';
                        } else {
                            return 'Level 3';
                        }
                    }),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Subcategories')
                    ->counts('children'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Category Level')
                    ->options([
                        'level1' => 'Level 1',
                        'level2' => 'Level 2',
                        'level3' => 'Level 3',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query->when($data['value'] === 'level1', function (Builder $query) {
                            $query->whereNull('parent_id');
                        })->when($data['value'] === 'level2', function (Builder $query) {
                            $query->whereNotNull('parent_id')
                                  ->whereHas('parent', function (Builder $query) {
                                      $query->whereNull('parent_id');
                                  });
                        })->when($data['value'] === 'level3', function (Builder $query) {
                            $query->whereHas('parent', function (Builder $query) {
                                $query->whereNotNull('parent_id');
                            });
                        });
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(), // Moved CreateAction to headerActions
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CategoryResource\Pages\ListCategories::route('/'),
            'create' => \App\Filament\Resources\CategoryResource\Pages\CreateCategory::route('/create'),
            'edit' => \App\Filament\Resources\CategoryResource\Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}