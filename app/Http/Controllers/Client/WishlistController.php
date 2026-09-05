<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
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

    public function toggle(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = Auth::user();
        $productId = $request->product_id;

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
