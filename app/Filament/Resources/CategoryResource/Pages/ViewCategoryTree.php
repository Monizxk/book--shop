<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use Filament\Resources\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Actions\Action;
use Filament\Actions\EditAction;

class ViewCategoryTree extends Page implements \Filament\Infolists\Contracts\HasInfolists
{
    use InteractsWithInfolists;

    protected static string $resource = CategoryResource::class;
    protected static string $view = 'filament.resources.category-resource.pages.view-category-tree';

    public Category $record;

    public function mount(int|string $record): void
    {
        $this->record = Category::findOrFail($record);
    }

    public function getTitle(): string
    {
        return "Category Tree: {$this->record->name}";
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->record($this->record),

            Action::make('back')
                ->label('Back to Categories')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function categoryInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Category Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('path')
                            ->label('Full Path')
                            ->getStateUsing(fn (Category $record): string => $record->getPathString())
                            ->copyable(),

                        TextEntry::make('level')
                            ->label('Level')
                            ->getStateUsing(fn (Category $record): int => $record->getLevel())
                            ->badge()
                            ->color('success'),

                        TextEntry::make('parent.name')
                            ->label('Parent Category')
                            ->placeholder('Root Level'),

                        TextEntry::make('children_count')
                            ->label('Direct Children')
                            ->getStateUsing(fn (Category $record): int => $record->children()->count()),

                        TextEntry::make('descendants_count')
                            ->label('Total Descendants')
                            ->getStateUsing(fn (Category $record): int => $record->descendants()->count()),

                        TextEntry::make('is_leaf')
                            ->label('Is Leaf Category')
                            ->getStateUsing(fn (Category $record): string => $record->isLeaf() ? 'Yes' : 'No')
                            ->badge()
                            ->color(fn (string $state): string => $state === 'Yes' ? 'success' : 'warning'),
                    ])
                    ->columns(2),

                Section::make('Ancestors')
                    ->schema([
                        RepeatableEntry::make('ancestors')
                            ->getStateUsing(function (Category $record): array {
                                return $record->ancestors()->reverse()->map(function ($ancestor, $index) {
                                    return [
                                        'level' => $index + 1,
                                        'name' => $ancestor->name,
                                        'id' => $ancestor->id,
                                    ];
                                })->values()->toArray();
                            })
                            ->schema([
                                TextEntry::make('level')
                                    ->label('Level')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('name')
                                    ->label('Category Name'),
                            ])
                            ->columns(2)
                            ->placeholder('This is a root category'),
                    ])
                    ->collapsible(),

                Section::make('Direct Children')
                    ->schema([
                        RepeatableEntry::make('children')
                            ->getStateUsing(function (Category $record): array {
                                return $record->children->map(function ($child) {
                                    return [
                                        'name' => $child->name,
                                        'id' => $child->id,
                                        'children_count' => $child->children()->count(),
                                        'descendants_count' => $child->descendants()->count(),
                                        'is_leaf' => $child->isLeaf(),
                                    ];
                                })->toArray();
                            })
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Name')
                                    ->weight('bold'),
                                TextEntry::make('children_count')
                                    ->label('Children')
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('descendants_count')
                                    ->label('Total Descendants')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('is_leaf')
                                    ->label('Leaf')
                                    ->getStateUsing(fn (array $state): string => $state['is_leaf'] ? 'Yes' : 'No')
                                    ->badge()
                                    ->color(fn (string $state): string => $state === 'Yes' ? 'success' : 'warning'),
                            ])
                            ->columns(4)
                            ->placeholder('No direct children'),
                    ])
                    ->collapsible(),
            ]);
    }

    public function getTreeData(): array
    {
        return $this->buildTreeArray($this->record);
    }

    private function buildTreeArray(Category $category): array
    {
        $children = $category->children->map(function ($child) {
            return $this->buildTreeArray($child);
        })->toArray();

        return [
            'id' => $category->id,
            'name' => $category->name,
            'level' => $category->getLevel(),
            'children_count' => $category->children()->count(),
            'descendants_count' => $category->descendants()->count(),
            'is_leaf' => $category->isLeaf(),
            'children' => $children,
        ];
    }
}
