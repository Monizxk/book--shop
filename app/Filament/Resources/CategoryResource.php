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
                    ->options(function (Forms\Get $get) {
                        $recordId = $get('../../record.id') ?? request()->route('record');

                        return Category::getPossibleParents($recordId)
                            ->mapWithKeys(function ($category) {
                                return [$category->id => $category->getFullPath()];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->reactive()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if ($state) {
                            $parent = Category::find($state);
                            if ($parent && !$parent->canHaveChildren()) {
                                $set('parent_id', null);
                            }
                        }
                    })
                    ->helperText('Виберіть батьківську категорію (максимум 5 рівнів)'),
                Forms\Components\Toggle::make('hidden')
                    ->label('Приховати категорію')
                    ->helperText('Приховані категорії не відображатимуться на сайті')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_path')
                    ->label('Повний шлях категорії')
                    ->getStateUsing(function (Category $record): string {
                        return $record->getFullPath();
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')
                    ->label('Рівень')
                    ->getStateUsing(function (Category $record): string {
                        return 'Level ' . $record->getLevel();
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Level 1' => 'primary',
                        'Level 2' => 'success',
                        'Level 3' => 'warning',
                        'Level 4' => 'danger',
                        'Level 5' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Головна категорія')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\ToggleColumn::make('hidden')
                    ->label('Схований')
                    ->onIcon('heroicon-o-eye-slash')
                    ->offIcon('heroicon-o-eye')
                    ->onColor('warning')
                    ->offColor('success')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->label('Category Level')
                    ->options([
                        '1' => 'Level 1',
                        '2' => 'Level 2',
                        '3' => 'Level 3',
                        '4' => 'Level 4',
                        '5' => 'Level 5',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['value']) {
                            $query->byLevel((int) $data['value']);
                        }
                    }),
                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Parent Category')
                    ->options(function () {
                        return Category::whereNull('parent_id')
                            ->with('children')
                            ->get()
                            ->mapWithKeys(function ($category) {
                                return [$category->id => $category->name];
                            })
                            ->toArray();
                    })
                    ->searchable(),
                Tables\Filters\Filter::make('can_have_children')
                    ->label('Can Have Children')
                    ->query(function (Builder $query) {

                        $query->where(function ($q) {
                            $q->whereNull('parent_id') // Level 1
                            ->orWhereHas('parent', function ($q) {
                                $q->whereNull('parent_id'); // Level 2
                            })
                                ->orWhereHas('parent.parent', function ($q) {
                                    $q->whereNull('parent_id'); // Level 3
                                })
                                ->orWhereHas('parent.parent.parent', function ($q) {
                                    $q->whereNull('parent_id'); // Level 4
                                });
                        });
                    })
                    ->toggle(),
                Tables\Filters\TernaryFilter::make('hidden')
                    ->label('Visibility')
                    ->placeholder('All categories')
                    ->trueLabel('Hidden only')
                    ->falseLabel('Visible only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggle_visibility')
                    ->label(fn (Category $record): string => $record->hidden ? 'Show' : 'Hide')
                    ->icon(fn (Category $record): string => $record->hidden ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->color(fn (Category $record): string => $record->hidden ? 'success' : 'warning')
                    ->action(function (Category $record) {
                        $record->update(['hidden' => !$record->hidden]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Category $record): string => $record->hidden ? 'Show Category' : 'Hide Category')
                    ->modalDescription(fn (Category $record): string => $record->hidden
                        ? 'Are you sure you want to show this category?'
                        : 'Are you sure you want to hide this category?'),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Category $record) {

                        if ($record->children()->count() > 0) {
                            throw new \Exception('Cannot delete category with subcategories. Please delete subcategories first.');
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('hide')
                        ->label('Сховати вибрані')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['hidden' => true]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Сховати категорії')
                        ->modalDescription('Ви впевнені, що хочете сховати вибрані категорії?'),

                    Tables\Actions\BulkAction::make('show')
                        ->label('Показати вибрані')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update(['hidden' => false]);
                            }
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Показати категорії')
                        ->modalDescription('Ви впевнені, що хочете показати вибрані категорії?'),

                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->children()->count() > 0) {
                                    throw new \Exception("Неможливо видалити категорію '{$record->name}', оскільки вона містить підкатегорії.");
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('name')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CategoryResource\Pages\ListCategories::route('/'),
            'create' => \App\Filament\Resources\CategoryResource\Pages\CreateCategory::route('/create'),
            'view' => \App\Filament\Resources\CategoryResource\Pages\ViewCategory::route('/{record}'),
            'edit' => \App\Filament\Resources\CategoryResource\Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [

        ];
    }
}
