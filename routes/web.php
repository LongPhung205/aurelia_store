<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Client;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Public / Client Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [Client\HomeController::class, 'index'])->name('home');

    // Cart Routes (Public/Session based initially)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [Client\CartController::class, 'index'])->name('index');
    Route::post('/add', [Client\CartController::class, 'add'])->name('add');
    Route::put('/update', [Client\CartController::class, 'update'])->name('update');
    Route::delete('/remove', [Client\CartController::class, 'remove'])->name('remove');
});

// Posts
Route::get('/posts', [\App\Http\Controllers\Client\PostController::class, 'index'])->name('posts.index');
Route::get('/{slug}-p{id}.html', [\App\Http\Controllers\Client\PostController::class, 'show'])->name('posts.show');

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::post('/apply-coupon', [Client\CheckoutController::class, 'applyCoupon'])->name('apply_coupon');
    Route::post('/prepare', [Client\CheckoutController::class, 'prepare'])->name('prepare');
    Route::get('/', [Client\CheckoutController::class, 'index'])->name('index');
    Route::post('/', [Client\CheckoutController::class, 'store'])->name('store');
    
    // AJAX Location Routes for GHN
    Route::get('/get-districts', [Client\CheckoutController::class, 'getDistricts'])->name('get_districts');
    Route::get('/get-wards', [Client\CheckoutController::class, 'getWards'])->name('get_wards');
    Route::post('/calculate-fee', [Client\CheckoutController::class, 'calculateFee'])->name('calculate_fee');
});



// Product Detail Route
Route::get('/products/{slug}', [Client\ProductController::class, 'show'])->name('products.show');
Route::get('/collections/{slug}', [Client\CollectionController::class, 'show'])->name('collections.show');

Route::middleware('auth')->group(function () {
    // Client Profile Routes
    Route::get('/profile', [Client\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [Client\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/password', [Client\ProfileController::class, 'password'])->name('profile.password');
    Route::post('/profile/password/update', [Client\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Addresses
    Route::get('/profile/addresses', [Client\ProfileController::class, 'addresses'])->name('profile.addresses');
    Route::post('/profile/addresses', [Client\ProfileController::class, 'storeAddress'])->name('profile.addresses.store');
    Route::put('/profile/addresses/{address}', [Client\ProfileController::class, 'updateAddress'])->name('profile.addresses.update');
    Route::delete('/profile/addresses/{address}', [Client\ProfileController::class, 'destroyAddress'])->name('profile.addresses.destroy');
    Route::post('/profile/addresses/{address}/default', [Client\ProfileController::class, 'setDefaultAddress'])->name('profile.addresses.default');

    // Orders
    Route::get('/profile/orders', [Client\ProfileController::class, 'orders'])->name('profile.orders');
    Route::get('/profile/orders/{order}', [Client\ProfileController::class, 'showOrder'])->name('profile.orders.show');

    // Wishlist Routes
    Route::get('/wishlist', [Client\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle', [Client\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    // Reviews Route
    Route::post('/products/{product}/reviews', [Client\ReviewController::class, 'store'])->name('reviews.store');
});

/*
|--------------------------------------------------------------------------
| 2. Admin Routes (Bảo vệ bởi Auth và Role Middleware)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', function () {
        return redirect()->route('admin.products.index');
    })->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class);
    Route::get('attributes', [Admin\AttributeController::class, 'index'])->name('attributes.index');
    Route::resource('colors', Admin\ColorController::class)->except(['index']);
    Route::resource('sizes', Admin\SizeController::class)->except(['index']);
    Route::resource('materials', Admin\MaterialController::class)->except(['index', 'create', 'show', 'edit']);
    Route::resource('products', Admin\ProductController::class);
    Route::resource('banners', Admin\BannerController::class);
    
    // Quản lý Đơn hàng (Orders)
    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/sync-ghn', [Admin\OrderController::class, 'syncGhnStatus'])->name('orders.sync_ghn');
    Route::post('orders/{order}/confirm-ghn', [Admin\OrderController::class, 'confirmAndCreateGhn'])->name('orders.confirm_ghn');
    Route::post('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update_status');

    // Warehouse & Inventory Routes
    Route::resource('imports', Admin\ImportController::class);
    Route::get('inventory', [Admin\InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/{product}/details', [Admin\InventoryController::class, 'details'])->name('inventory.details');
    Route::get('inventory-history/details', [Admin\InventoryHistoryController::class, 'details'])->name('inventory_history.details');
    Route::get('inventory-history', [Admin\InventoryHistoryController::class, 'index'])->name('inventory_history.index');

    // Routes cho AJAX xử lý Variants & Images
    Route::prefix('products/{product}')->name('products.')->group(function () {
        Route::resource('variants', Admin\ProductVariantController::class)->only(['store', 'update', 'destroy']);
        Route::post('images', [Admin\ProductImageController::class, 'store'])->name('images.store');
    });
    Route::delete('product-images/{productImage}', [Admin\ProductImageController::class, 'destroy'])->name('product_images.destroy');

    // Marketing Routes
    Route::resource('coupons', Admin\CouponController::class);
    Route::resource('flash_sales', Admin\FlashSaleController::class);
    Route::resource('collections', Admin\CollectionController::class);
    Route::get('flash_sales/{flash_sale}/items', [Admin\FlashSaleController::class, 'manageItems'])->name('flash_sales.items');
    Route::post('flash_sales/{flash_sale}/items', [Admin\FlashSaleController::class, 'addItem'])->name('flash_sales.items.store');
    Route::delete('flash_sales/{flash_sale}/items/{item}', [Admin\FlashSaleController::class, 'removeItem'])->name('flash_sales.items.destroy');

    // Settings Route
    Route::get('settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    
    // User & Role Routes
    Route::resource('users', Admin\UserController::class);

    // Posts (Lookbook/Tạp chí)
    Route::resource('posts', Admin\PostController::class);
});

require __DIR__.'/auth.php';
