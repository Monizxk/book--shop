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
     * Get the grandparent (level 1) category
     */
    public function grandparent()
    {
        if ($this->parent && $this->parent->parent) {
            return $this->parent->parent;
        }
        
        return null;
    }

    /**
     * Check if this is a level 1 category (no parent)
     */
    public function isLevel1(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Check if this is a level 2 category (has parent but no grandparent)
     */
    public function isLevel2(): bool
    {
        return $this->parent_id !== null && ($this->parent && $this->parent->parent_id === null);
    }

    /**
     * Check if this is a level 3 category (has both parent and grandparent)
     */
    public function isLevel3(): bool
    {
        return $this->parent_id !== null && ($this->parent && $this->parent->parent_id !== null);
    }
}