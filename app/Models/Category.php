<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_id',
    ];

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants recursively
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors recursively
     */
    public function ancestors()
    {
        $ancestors = collect();
        $current = $this->parent;

        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }

        return $ancestors->reverse();
    }

    /**
     * Get the root category (level 1)
     */
    public function root()
    {
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
        }
        return $current;
    }

    /**
     * Get the category level (1-5)
     */
    public function getLevel(): int
    {
        $level = 1;
        $current = $this;

        while ($current->parent) {
            $level++;
            $current = $current->parent;
        }

        return $level;
    }

    /**
     * Check if this category can have children (level < 5)
     */
    public function canHaveChildren(): bool
    {
        return $this->getLevel() < 5;
    }

    /**
     * Get categories that can be parents for this category
     */
    public static function getPossibleParents($excludeId = null)
    {
        return self::where(function ($query) {
            // Получаем категории уровней 1-4
            $query->whereNull('parent_id') // Level 1
            ->orWhereHas('parent', function ($q) {
                $q->whereNull('parent_id'); // Level 2
            })
                ->orWhereHas('parent.parent', function ($q) {
                    $q->whereNull('parent_id'); // Level 3
                })
                ->orWhereHas('parent.parent.parent', function ($q) {
                    $q->whereNull('parent_id'); // Level 4
                });
        })
            ->when($excludeId, function ($query, $excludeId) {
                $query->where('id', '!=', $excludeId);
            })
            ->get();
    }

    /**
     * Check if this is a level 1 category (no parent)
     */
    public function isLevel1(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if this is a level 2 category
     */
    public function isLevel2(): bool
    {
        return $this->parent_id !== null &&
            $this->parent &&
            $this->parent->parent_id === null;
    }

    /**
     * Check if this is a level 3 category
     */
    public function isLevel3(): bool
    {
        return $this->parent_id !== null &&
            $this->parent &&
            $this->parent->parent_id !== null &&
            $this->parent->parent &&
            $this->parent->parent->parent_id === null;
    }

    /**
     * Check if this is a level 4 category
     */
    public function isLevel4(): bool
    {
        return $this->parent_id !== null &&
            $this->parent &&
            $this->parent->parent_id !== null &&
            $this->parent->parent &&
            $this->parent->parent->parent_id !== null &&
            $this->parent->parent->parent &&
            $this->parent->parent->parent->parent_id === null;
    }

    /**
     * Check if this is a level 5 category
     */
    public function isLevel5(): bool
    {
        return $this->parent_id !== null &&
            $this->parent &&
            $this->parent->parent_id !== null &&
            $this->parent->parent &&
            $this->parent->parent->parent_id !== null &&
            $this->parent->parent->parent &&
            $this->parent->parent->parent->parent_id !== null;
    }

    /**
     * Get full category path as string
     */
    public function getFullPath(): string
    {
        $path = collect([$this->name]);
        $ancestors = $this->ancestors();

        foreach ($ancestors as $ancestor) {
            $path->prepend($ancestor->name);
        }

        return $path->implode(' > ');
    }

    /**
     * Scope to get categories by level
     */
    public function scopeByLevel($query, int $level)
    {
        switch ($level) {
            case 1:
                return $query->whereNull('parent_id');
            case 2:
                return $query->whereHas('parent', function ($q) {
                    $q->whereNull('parent_id');
                });
            case 3:
                return $query->whereHas('parent.parent', function ($q) {
                    $q->whereNull('parent_id');
                });
            case 4:
                return $query->whereHas('parent.parent.parent', function ($q) {
                    $q->whereNull('parent_id');
                });
            case 5:
                return $query->whereHas('parent.parent.parent.parent', function ($q) {
                    $q->whereNull('parent_id');
                });
            default:
                return $query;
        }
    }
}
