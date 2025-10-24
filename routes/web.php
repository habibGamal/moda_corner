<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InstaPayController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderReturnController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReturnOrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\WishlistController;
use App\Models\User;
use App\Notifications\Notify;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', App\Http\Controllers\HomeController::class)->name('home');

// Product routes
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Product Review routes (public)
Route::get('/products/{product}/reviews', [ProductReviewController::class, 'index'])->name('products.reviews.index');

// Brand routes
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');

// Category routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

// Section routes
Route::get('/sections/{section}', [SectionController::class, 'show'])->name('sections.show');

// Search routes
Route::get('/search', [SearchController::class, 'results'])->name('search.results');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Pages routes
Route::get('/privacy', [App\Http\Controllers\PagesController::class, 'privacy'])->name('pages.privacy');
Route::get('/returns-policy', [App\Http\Controllers\PagesController::class, 'returns'])->name('pages.returns');
Route::get('/terms', [App\Http\Controllers\PagesController::class, 'terms'])->name('pages.terms');
Route::get('/contact', [App\Http\Controllers\PagesController::class, 'contact'])->name('pages.contact');
Route::get('/exchange-policy', [App\Http\Controllers\PagesController::class, 'exchangePolicy'])->name('pages.exchange-policy');
Route::get('/about-us', [App\Http\Controllers\PagesController::class, 'aboutUs'])->name('pages.about-us');
Route::get('/delivery-policy', [App\Http\Controllers\PagesController::class, 'deliveryPolicy'])->name('pages.delivery-policy');
Route::get('/shipping-policy', [App\Http\Controllers\PagesController::class, 'shippingPolicy'])->name('pages.shipping-policy');
Route::get('/facebook-data-deletion', [App\Http\Controllers\PagesController::class, 'facebookDataDeletion'])->name('pages.facebook-data-deletion');

Route::get('/notify', function () {
    $subscriptions = User::all();
    Notification::send($subscriptions, new Notify);

    return response()->json(['sent' => true]);
});

Route::post('/subscribe', function () {
    $user = auth()->user();

    $user->updatePushSubscription(
        request('endpoint'),
        request('publicKey'),
        request('authToken'),
        'aesgcm'
    );

    return response()->noContent();
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::patch('/cart/{cartItem}', [CartController::class, 'updateItem'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clearCart'])->name('cart.clear');
    Route::get('/cart/summary', [CartController::class, 'getSummary'])->name('cart.summary');

    // Wishlist routes
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add', [WishlistController::class, 'addItem'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{product}', [WishlistController::class, 'removeItem'])->name('wishlist.remove');
    Route::delete('/wishlist', [WishlistController::class, 'clearList'])->name('wishlist.clear');
    Route::get('/wishlist/summary', [WishlistController::class, 'getSummary'])->name('wishlist.summary');

    // Address routes
    Route::post('/addresses', [AddressController::class, 'store'])->name('addresses.store');
    Route::get('/addresses/areas', [AddressController::class, 'getAreas'])->name('addresses.areas');

    // Promotion routes
    Route::post('/promotions/apply', [App\Http\Controllers\PromotionController::class, 'applyPromotion'])->name('promotions.apply');
    Route::delete('/promotions/remove', [App\Http\Controllers\PromotionController::class, 'removePromotion'])->name('promotions.remove');
    Route::get('/promotions/automatic', [App\Http\Controllers\PromotionController::class, 'getAutomaticPromotions'])->name('promotions.automatic');

    // Order routes
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout.index');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show')->where('order', '[0-9]+'); // Ensure order ID is numeric
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->where('order', '[0-9]+'); // Ensure order ID is numeric

    // Order return routes (Legacy - redirects to new system)
    Route::post('/orders/{order}/return', [OrderReturnController::class, 'requestReturn'])->name('orders.return.request')->where('order', '[0-9]+');
    Route::get('/orders/returns/history', [OrderReturnController::class, 'history'])->name('orders.returns.history');

    // New Return Management System
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnOrderController::class, 'index'])->name('index');
        Route::get('/create/{order}', [ReturnOrderController::class, 'create'])->name('create')->where('order', '[0-9]+');
        Route::post('/create/{order}', [ReturnOrderController::class, 'store'])->name('store')->where('order', '[0-9]+');
        Route::get('/{returnNumber}', [ReturnOrderController::class, 'show'])->name('show');
        Route::get('/history', [ReturnOrderController::class, 'history'])->name('history'); // Legacy support
    });

    // Payment routes - updated to use new PaymentController
    Route::get('/payments/initiate', [PaymentController::class, 'initiatePayment'])->name('payment.initiate');
    Route::get('/payments/success', [PaymentController::class, 'handleSuccess'])->name('payment.success');
    Route::get('/payments/failure', [PaymentController::class, 'handleFailure'])->name('payment.failure');
    Route::get('/payments/{order}', [PaymentController::class, 'showPayment'])->name('payment.show');

    // InstaPay routes
    Route::get('/instapay/{order}', [InstaPayController::class, 'show'])->name('instapay.upload')->where('order', '[0-9]+');
    Route::post('/instapay/{order}', [InstaPayController::class, 'store'])->name('instapay.store')->where('order', '[0-9]+');
    Route::post('/instapay/{order}/reupload', [InstaPayController::class, 'reupload'])->name('instapay.reupload')->where('order', '[0-9]+');

    // Product Review routes (authenticated)
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');
    Route::put('/reviews/{review}', [ProductReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ProductReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('/products/{product}/reviews/can-review', [ProductReviewController::class, 'canReview'])->name('products.reviews.can-review');

    // // Legacy Kashier payment routes for backward compatibility
    // Route::get('/payments/kashier/initiate', [PaymentController::class, 'initiatePayment'])->name('kashier.payment.initiate');
    // Route::get('/payments/kashier/success', [PaymentController::class, 'handleSuccess'])->name('kashier.payment.success');
    // Route::get('/payments/kashier/failure', [PaymentController::class, 'handleFailure'])->name('kashier.payment.failure');
    // Route::get('/payments/kashier/{order}', [PaymentController::class, 'showPayment'])->name('kashier.payment.show');
});

// Payment webhook - This route is not protected as it's accessed by payment gateways
Route::post('/webhooks/payment', [PaymentWebhookController::class, 'handle'])->name('payment.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

// Legacy Kashier webhook for backward compatibility
Route::post('/webhooks/kashier', [PaymentWebhookController::class, 'handle'])->name('kashier.payment.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

// Paymob webhook
Route::post('/webhooks/paymob', [App\Http\Controllers\PaymobWebhookController::class, 'handle'])->name('paymob.payment.webhook')
    ->withoutMiddleware([VerifyCsrfToken::class]);

// API Routes for Settings
Route::prefix('api')->group(function () {
    Route::get('/settings', [App\Http\Controllers\Api\SettingsController::class, 'index']);
    Route::get('/settings/group/{group}', [App\Http\Controllers\Api\SettingsController::class, 'byGroup']);
    Route::get('/settings/{key}', [App\Http\Controllers\Api\SettingsController::class, 'show']);
});

// Facebook Data Feed - Public endpoint for Facebook crawlers
Route::get('/datafeed.csv', [App\Http\Controllers\FacebookDataFeedController::class, 'generateFeed'])->name('facebook.datafeed');

require __DIR__.'/auth.php';
