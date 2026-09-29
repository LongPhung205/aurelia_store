<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ToggleWishlistRequest;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $wishlists = $user->wishlists()->with(['product.primaryImage', 'product.variants'])->get();
        
        $wishlists->each(function($wishlist) {
            if ($wishlist->product) {
                $wishlist->product->append('primary_image_url');
            }
        });
        
        return view('client.wishlist.index', compact('wishlists'));
    }

    public function toggle(ToggleWishlistRequest $request)
    {
        $user = Auth::user();
        $productId = $request->validated()['product_id'];

        $wishlist = $user->wishlists()->where('product_id', $productId)->first();

        if ($wishlist) {
            $wishlist->delete();
            $isWishlisted = false;
            $message = 'Đã xóa khỏi danh sách yêu thích';
        } else {
            $user->wishlists()->create(['product_id' => $productId]);
            $isWishlisted = true;
            $message = 'Đã thêm vào danh sách yêu thích';
        }

        return response()->json([
            'success' => true,
            'is_wishlisted' => $isWishlisted,
            'message' => $message,
            'wishlist_count' => $user->wishlists()->count()
        ]);
    }
}
