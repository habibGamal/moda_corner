<?php

use App\Filament\Pages\Reports\WishlistReport;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Livewire\Livewire;

it('can render wishlist report page', function () {
    $user = User::factory()->create(['is_admin' => true]);

    $this->actingAs($user)
        ->get(WishlistReport::getUrl())
        ->assertSuccessful()
        ->assertSee('تقرير قائمة الأمنيات');
});

it('creates wishlist items correctly', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    // Create some wishlist items
    $wishlistItems = WishlistItem::factory()->count(5)->create([
        'product_id' => $product->id,
        'created_at' => now()->subDays(5),
    ]);

    expect(WishlistItem::count())->toBe(5);
    expect($wishlistItems->first()->product->id)->toBe($product->id);
});

it('filters wishlist data by date range', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    // Create wishlist items with different dates
    $oldItem = WishlistItem::factory()->create([
        'product_id' => $product->id,
        'created_at' => now()->subDays(30),
    ]);

    $newItem = WishlistItem::factory()->create([
        'product_id' => $product->id,
        'created_at' => now()->subDays(5),
    ]);

    // Test filtering by date
    $recentItems = WishlistItem::where('created_at', '>=', now()->subDays(10))->count();
    expect($recentItems)->toBe(1);

    $allItems = WishlistItem::count();
    expect($allItems)->toBe(2);
});

it('displays latest wishlist items correctly', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'category_id' => $category->id,
        'name_ar' => 'منتج تجريبي',
    ]);

    $wishlistItem = WishlistItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Filament\Widgets\LatestWishlistItems::class)
        ->assertCanSeeTableRecords([$wishlistItem])
        ->assertSee('منتج تجريبي')
        ->assertSee($user->name);
});

it('can filter latest wishlist items by purchase status', function () {
    $user = User::factory()->create(['is_admin' => true]);
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->id]);

    $wishlistItem = WishlistItem::factory()->create([
        'user_id' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->actingAs($user);

    Livewire::test(\App\Filament\Widgets\LatestWishlistItems::class)
        ->filterTable('is_purchased', '0') // Filter for not purchased items
        ->assertCanSeeTableRecords([$wishlistItem]);
});

it('can access report page with authentication', function () {
    // Test without authentication should return 403
    $this->get(WishlistReport::getUrl())
        ->assertForbidden();

    // Test with authentication should work
    $user = User::factory()->create(['is_admin' => true]);

    $this->actingAs($user)
        ->get(WishlistReport::getUrl())
        ->assertSuccessful();
});
