# Facebook Data Feed Implementation

## Overview
This implementation provides a CSV data feed endpoint that follows Facebook's Product Catalog specifications for Commerce Manager. The feed can be used for:
- Facebook Dynamic Ads
- Instagram Shopping
- Facebook Shops
- Meta Advantage+ Catalog Ads

## Endpoint
```
GET /datafeed.csv
```

**Full URL:** `http://localhost:8000/datafeed.csv`

## Facebook Documentation
This implementation follows the official Facebook specification:
https://www.facebook.com/business/help/120325381656392?id=725943027795860

## Included Fields

### Required Fields (Always Included)
- `id` - Unique SKU from product variant
- `title` - Product name (English, max 200 chars)
- `description` - Product description (English, max 5000 chars, HTML stripped)
- `availability` - "in stock" or "out of stock" based on variant quantity
- `condition` - Always "new"
- `price` - Final price in EGP format (e.g., "80.00 EGP")
- `link` - Full URL to product page
- `image_link` - Full URL to main product image
- `brand` - Brand name (English)

### Additional Required for Shops
- `quantity_to_sell_on_facebook` - Available quantity from variant

### Highly Recommended Optional Fields
- `sale_price` - Sale price if available (in EGP format)
- `sale_price_effective_date` - Sale period (currently empty, can be customized)
- `item_group_id` - Groups variants of same product (format: `product_{id}`)
- `additional_image_link` - Comma-separated URLs of additional images
- `product_type` - Category hierarchy (e.g., "Clothing > Women > Tops")
- `google_product_category` - Google product category
- `fb_product_category` - Facebook product category
- `color` - Variant color
- `size` - Variant size
- `gender` - Auto-detected from category/product name (female/male/unisex)
- `age_group` - Always "adult" (can be customized)
- `material` - From variant's additional_attributes
- `pattern` - From variant's additional_attributes
- `gtin` - Global Trade Item Number (currently empty)
- `mpn` - Manufacturer Part Number (uses SKU)
- `shipping_weight` - Shipping weight (currently empty)

## Data Flow

### 1. Product Selection
- Only **active products** (`is_active = true`) are included
- Only **active variants** (`is_active = true`) are included
- Products must have associated brand and category

### 2. Variant Processing
Each product variant generates a separate row in the CSV with:
- Unique `id` from variant SKU
- Same `item_group_id` linking variants of the same product
- Individual pricing, quantity, color, size from the variant
- Shared product information (title, description, brand, category)

### 3. Image Handling
- First image in variant's `images` array becomes `image_link`
- Remaining images become `additional_image_link` (comma-separated)
- Images are converted to full URLs using `asset('storage/' . $image)`

### 4. Price Calculation
Priority order:
1. Variant's `sale_price` (if available)
2. Product's `sale_price` (if available)
3. Variant's `price`
4. Product's `price`

### 5. Category Mapping
The controller includes helper methods to map your categories to:
- **Google Product Categories** - General e-commerce standard
- **Facebook Product Categories** - Facebook-specific taxonomy

**⚠️ Important:** Customize the category mappings in these methods:
- `getGoogleCategory()` - Line ~215
- `getFacebookCategory()` - Line ~240

## Customization Guide

### Adding Custom Fields
To add more fields to the feed:

1. Add the field name to the `$headers` array in `generateFeed()`:
```php
$headers = [
    // ... existing fields
    'custom_field_name',
];
```

2. Add the field value to the `$row` array:
```php
$row = [
    // ... existing fields
    'custom_field_name' => $yourValue,
];
```

### Category Mappings
Update the category mappings based on your actual product categories:

```php
// In getGoogleCategory()
$categoryMappings = [
    'your-category-slug' => 'Google Category Name',
    'clothing' => 'Apparel & Accessories',
    'electronics' => 'Electronics',
];

// In getFacebookCategory()
$categoryMappings = [
    'your-category-slug' => 'Facebook Category Name',
    'women' => 'Clothing & Accessories > Clothing > Women\'s Clothing',
];
```

### Adding Sale Date Logic
To add sale price effective dates:

```php
$row = [
    // ...
    'sale_price_effective_date' => $this->getSaleDateRange($product),
];

private function getSaleDateRange($product): string
{
    if (!$product->sale_start || !$product->sale_end) {
        return '';
    }
    
    $start = $product->sale_start->format('Y-m-d\TH:i:sP');
    $end = $product->sale_end->format('Y-m-d\TH:i:sP');
    
    return "{$start}/{$end}";
}
```

### Adding GTIN/Barcode Support
If you add a `gtin` or `barcode` field to your variants table:

```php
$row = [
    // ...
    'gtin' => $variant->gtin ?? '',
];
```

## Testing

Run the test suite:
```bash
php artisan test --filter=FacebookDataFeedTest
```

Test coverage includes:
- ✅ CSV generation with correct headers
- ✅ Required field validation
- ✅ In-stock vs out-of-stock handling
- ✅ Item group ID for variants
- ✅ Active/inactive product filtering

## Setting Up in Facebook Commerce Manager

1. **Go to Commerce Manager** (https://business.facebook.com/commerce/)
2. **Create or Select a Catalog**
3. **Add Products via Data Feed:**
   - Choose "Scheduled Feed"
   - Enter your feed URL: `https://yourdomain.com/datafeed.csv`
   - Set update frequency (recommended: Daily)
4. **Test the Feed:**
   - Use "Test Feed" button
   - Review any errors or warnings
   - Fix issues and re-test

### Production Considerations

#### 1. Public Accessibility
The endpoint is **publicly accessible** (no authentication required) so Facebook can fetch it.

#### 2. Performance Optimization
For large catalogs (>10,000 products):

```php
// Add chunking to the query
Product::with(['brand', 'category', 'variants'])
    ->where('is_active', true)
    ->chunk(1000, function ($products) use ($output) {
        foreach ($products as $product) {
            // ... process product
        }
    });
```

#### 3. Caching
Consider caching the feed for better performance:

```php
public function generateFeed()
{
    return Cache::remember('facebook_datafeed', 3600, function () {
        // ... existing feed generation logic
    });
}
```

#### 4. URL Configuration
Update `config/app.php` with your production URL:
```php
'url' => env('APP_URL', 'https://yourdomain.com'),
```

#### 5. Scheduled Updates
Facebook will fetch your feed based on the schedule you set (hourly, daily, etc.)

## Troubleshooting

### Common Issues

1. **Images not showing:**
   - Ensure images are publicly accessible
   - Check that `storage` is linked: `php artisan storage:link`
   - Verify image URLs are complete and valid

2. **Currency Issues:**
   - Price format must be: `{amount} {currency_code}`
   - Example: "99.99 EGP" ✅
   - Wrong: "EGP 99.99" ❌ or "$99.99" ❌

3. **Category Not Recognized:**
   - Use exact Facebook/Google category names
   - Download category lists from Facebook documentation
   - Update the mapping methods in the controller

4. **Products Not Appearing:**
   - Check `is_active` status on products and variants
   - Verify quantity > 0 for in-stock items
   - Ensure required fields are not empty

## Additional Resources

- [Facebook Product Catalog Guide](https://www.facebook.com/business/help/890714097648074)
- [Google Product Categories](https://www.google.com/basepages/producttype/taxonomy-with-ids.en-US.txt)
- [Facebook Product Categories](https://www.facebook.com/products/categories/en_US.txt)
- [Facebook Commerce Manager](https://business.facebook.com/commerce/)

## File Structure

```
app/
  Http/
    Controllers/
      FacebookDataFeedController.php  # Main controller
routes/
  web.php                             # Route definition
tests/
  Feature/
    FacebookDataFeedTest.php          # Test suite
```

## Support

For Facebook-specific feed issues:
- Check Facebook Commerce Manager error logs
- Use Facebook's "Test Feed" feature
- Review Facebook's troubleshooting guides

For implementation issues:
- Check Laravel logs: `storage/logs/laravel.log`
- Run tests: `php artisan test --filter=FacebookDataFeedTest`
- Verify database data integrity
