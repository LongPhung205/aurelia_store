<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'is_active', 'image'];

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get all descendants (children, grandchildren, etc.) IDs and self ID.
     *
     * @return array
     */
    public function getAllDescendantIdsAndSelf(): array
    {
        $ids = [(int)$this->id];
        $children = $this->relationLoaded('children') ? $this->children : $this->children()->get();
        foreach ($children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIdsAndSelf());
        }
        return array_values(array_unique($ids));
    }

    /**
     * Build a flat hierarchical tree list with indentation prefixes and level.
     * Includes all levels (level 1, 2, 3, etc.)
     *
     * @param array $excludeIds Categories (and their descendants) to exclude
     * @return \Illuminate\Support\Collection
     */
    public static function getTreeList(array $excludeIds = [])
    {
        $all = static::orderBy('name')->get();
        $tree = collect();

        $traverse = function ($parentId = null, $level = 0) use (&$traverse, $all, &$tree, $excludeIds) {
            $children = $all->where('parent_id', $parentId);
            foreach ($children as $item) {
                if (in_array($item->id, $excludeIds)) {
                    continue;
                }
                $prefix = $level > 0 ? str_repeat('— ', $level) : '';
                $tree->push((object)[
                    'id' => $item->id,
                    'name' => $prefix . $item->name,
                    'raw_name' => $item->name,
                    'level' => $level,
                    'parent_id' => $item->parent_id,
                ]);
                $traverse($item->id, $level + 1);
            }
        };

        $traverse(null, 0);

        // Include any orphaned categories whose parent_id points to a non-existent parent
        $visitedIds = $tree->pluck('id')->toArray();
        $orphans = $all->whereNotIn('id', array_merge($visitedIds, $excludeIds));
        foreach ($orphans as $orphan) {
            $tree->push((object)[
                'id' => $orphan->id,
                'name' => $orphan->name,
                'raw_name' => $orphan->name,
                'level' => 0,
                'parent_id' => null,
            ]);
        }

        return $tree;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function banner()
    {
        return $this->hasOne(Banner::class)->where('is_active', true)->where('type', 'category_header')->latest();
    }

    public function banners()
    {
        return $this->hasMany(Banner::class);
    }

    /**
     * Get the active shared category banner across all categories.
     */
    public static function getSharedBanner()
    {
        return Banner::where('is_active', true)
            ->where('type', 'category_header')
            ->latest()
            ->first();
    }

    /**
     * Get the display hero banner URL for this category.
     * Prioritizes:
     * 1. Category-specific banner if explicitly assigned
     * 2. Shared active category header banner
     * 3. Category image field
     * 4. Parent category hero banner
     */
    public function getHeroBannerUrlAttribute()
    {
        // 1. Specific banner assigned to this category
        if ($this->banner && $this->banner->display_image_url) {
            return $this->banner->display_image_url;
        }

        // 2. Shared banner for all categories
        $sharedBanner = static::getSharedBanner();
        if ($sharedBanner && $sharedBanner->display_image_url) {
            return $sharedBanner->display_image_url;
        }

        // 3. Category own image
        if ($this->image) {
            if (\Illuminate\Support\Str::startsWith($this->image, ['http://', 'https://'])) {
                return $this->image;
            }
            return \Illuminate\Support\Facades\Storage::url($this->image);
        }

        // 4. Parent category fallback
        if ($this->parent) {
            return $this->parent->hero_banner_url;
        }

        return null;
    }
}
