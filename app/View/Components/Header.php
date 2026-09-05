<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Category;

class Header extends Component
{
    public $categories;
    public $wishlistQty = 0;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $this->categories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => function($query) {
                $query->where('is_active', true)->with(['children' => function($q) {
                    $q->where('is_active', true);
                }]);
            }])
            ->get();
            
        if (auth()->check()) {
            $this->wishlistQty = auth()->user()->wishlists()->count();
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.header');
    }
}
