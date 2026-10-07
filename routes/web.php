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
    Route::post('/add-combo', [Client\CartController::class, 'addCombo'])->name('add-combo');
    Route::put('/update', [Client\CartController::class, 'update'])->name('update');
    Route::delete('/remove', [Client\CartController::class, 'remove'])->name('remove');
});

// Posts
Route::get('/posts', [\App\Http\Controllers\Client\PostController::class, 'index'])->name('posts.index');
Route::get('/{slug}-p{id}.html', [\App\Http\Controllers\Client\PostController::class, 'show'])
    ->where('slug', '[a-zA-Z0-9\-]+')
    ->name('posts.show');

// Static Pages
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/chinh-sach-van-chuyen', [\App\Http\Controllers\Client\PageController::class, 'shipping'])->name('shipping');
    Route::get('/chinh-sach-doi-tra', [\App\Http\Controllers\Client\PageController::class, 'returnPolicy'])->name('return_policy');
    Route::get('/huong-dan-mua-hang', [\App\Http\Controllers\Client\PageController::class, 'howToBuy'])->name('how_to_buy');
    Route::get('/bao-mat-thong-tin', [\App\Http\Controllers\Client\PageController::class, 'privacy'])->name('privacy');
    Route::get('/lien-he', [\App\Http\Controllers\Client\PageController::class, 'contact'])->name('contact');
    
    Route::get('/cau-chuyen-thuong-hieu', [\App\Http\Controllers\Client\PageController::class, 'brandStory'])->name('brand_story');
    Route::get('/he-thong-cua-hang', [\App\Http\Controllers\Client\PageController::class, 'stores'])->name('stores');
    Route::get('/tuyen-dung', [\App\Http\Controllers\Client\PageController::class, 'careers'])->name('careers');
    Route::get('/goc-bao-chi', [\App\Http\Controllers\Client\PageController::class, 'press'])->name('press');
    Route::get('/khach-hang-than-thiet', [\App\Http\Controllers\Client\PageController::class, 'loyalty'])->name('loyalty');
});

// Checkout Routes
Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::post('/apply-coupon', [Client\CheckoutController::class, 'applyCoupon'])->name('apply_coupon');
    Route::post('/prepare', [Client\CheckoutController::class, 'prepare'])->name('prepare');
    Route::get('/', [Client\CheckoutController::class, 'index'])->name('index');
    Route::post('/', [Client\CheckoutController::class, 'store'])->name('store');
    Route::get('/success/{order}', [Client\CheckoutController::class, 'success'])->name('success');
    
    // AJAX Location Routes for GHN
    Route::get('/get-districts', [Client\CheckoutController::class, 'getDistricts'])->name('get_districts');
    Route::get('/get-wards', [Client\CheckoutController::class, 'getWards'])->name('get_wards');
    Route::post('/calculate-fee', [Client\CheckoutController::class, 'calculateFee'])->name('calculate_fee');
});

// Order Cancel Route (Public / Auth)
Route::post('/orders/{order}/cancel', [Client\ProfileController::class, 'cancelOrder'])->name('orders.cancel');

// PayOS Routes
Route::prefix('payos')->name('payos.')->group(function () {
    Route::get('/create/{order}', [Client\PayOSController::class, 'create'])->name('create');
    Route::get('/return', [Client\PayOSController::class, 'returnPage'])->name('return');
    Route::get('/cancel', [Client\PayOSController::class, 'cancelPage'])->name('cancel');
    Route::match(['get', 'post'], '/webhook', [Client\PayOSController::class, 'webhook'])->name('webhook');
});

// MoMo Routes
Route::prefix('payment/momo')->name('momo.')->group(function () {
    Route::get('/start/{order}', [Client\MomoController::class, 'start'])->name('start');
    Route::get('/pay-again/{order}', [Client\MomoController::class, 'payAgain'])->name('pay_again');
    Route::get('/return', [Client\MomoController::class, 'callback'])->name('return');
    Route::post('/notify', [Client\MomoController::class, 'ipn'])->name('notify');
});



// Product Detail Route
Route::get('/products/{slug}', [Client\ProductController::class, 'show'])->name('products.show');
Route::get('/danh-muc/{slug}', [Client\CategoryController::class, 'show'])->name('categories.show');
Route::get('/tim-kiem', [Client\HomeController::class, 'search'])->name('search');
Route::get('/api/search', [Client\HomeController::class, 'apiSearch'])->name('api.search');

Route::middleware('auth')->group(function () {
    // Chat Routes (phía khách hàng)
    Route::post('/chat/open', [Client\ClientChatController::class, 'openOrCreate'])->name('client.chat.open');
    Route::post('/chat/send', [Client\ClientChatController::class, 'sendMessage'])->name('client.chat.send');

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
    Route::post('/profile/orders/{order}/cancel', [Client\ProfileController::class, 'cancelOrder'])->name('profile.orders.cancel');

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
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('categories', Admin\CategoryController::class);
    Route::get('attributes', [Admin\AttributeController::class, 'index'])->name('attributes.index');
    Route::resource('colors', Admin\ColorController::class)->except(['index']);
    Route::resource('sizes', Admin\SizeController::class)->except(['index']);
    Route::resource('materials', Admin\MaterialController::class)->except(['index', 'create', 'show', 'edit']);
    Route::resource('products', Admin\ProductController::class);
    Route::resource('banners', Admin\BannerController::class);
    
    // Quản lý Đơn hàng (Orders)
    Route::get('orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::post('orders/bulk-action', [Admin\OrderController::class, 'bulkAction'])->name('orders.bulk_action');
    Route::post('orders/bulk-print', [Admin\OrderController::class, 'bulkPrint'])->name('orders.bulk_print');
    Route::get('orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/sync-ghn', [Admin\OrderController::class, 'syncGhnStatus'])->name('orders.sync_ghn');
    Route::post('orders/{order}/confirm-ghn', [Admin\OrderController::class, 'confirmAndCreateGhn'])->name('orders.confirm_ghn');
    Route::post('orders/{order}/status', [Admin\OrderController::class, 'updateStatus'])->name('orders.update_status');

    // Finance & Transactions
    Route::get('finance', [Admin\FinanceController::class, 'index'])->name('finance.index');
    Route::get('transactions', [Admin\TransactionController::class, 'index'])->name('transactions.index');
    Route::patch('transactions/{transaction}', [Admin\TransactionController::class, 'update'])->name('transactions.update');

    // Analytics & Reports
    Route::get('analytics', [Admin\AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/export', [Admin\AnalyticsController::class, 'export'])->name('analytics.export');

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

    // Chat / CSKH (phía Admin)
    Route::get('chat', [Admin\AdminChatController::class, 'index'])->name('chat.index');
    Route::patch('chat/{conversation}/close', [Admin\AdminChatController::class, 'toggleStatus'])->name('chat.close');
    Route::post('chat/send', [Admin\AdminChatController::class, 'sendMessage'])->name('chat.send');
    
    // Notifications
    Route::get('notifications', [Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [Admin\NotificationController::class, 'markAsRead'])->name('notifications.mark_read');
    Route::post('notifications/mark-all-read', [Admin\NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
});

require __DIR__.'/auth.php';
