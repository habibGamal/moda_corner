# Product Review & Rating System Documentation

## Overview

A comprehensive product review and rating system has been implemented for the Moda Corner e-commerce application. This system allows customers to rate products (1-5 stars) and leave detailed reviews, with support for verified purchases and admin moderation.

## Features

### Frontend Features

1. **Product Reviews Display**
   - Star rating visualization (1-5 stars)
   - Average rating and total review count
   - Rating breakdown by star count
   - Individual review cards with user info
   - Verified purchase badges
   - Pagination support
   - Empty state UI when no reviews exist

2. **Review Submission**
   - Interactive star rating selector
   - Optional comment field (max 1000 characters)
   - Real-time character counter
   - Form validation
   - Success/error feedback

3. **Review Management**
   - Edit own reviews
   - Delete own reviews
   - Confirmation dialogs for destructive actions

4. **Product Card Integration**
   - Average rating display with star visualization
   - Review count display

5. **Multilingual Support**
   - Full Arabic and English translation support
   - RTL-compatible UI components

### Backend Features

1. **Database Schema**
   - Comprehensive product_reviews table
   - Relationships with products, users, and orders
   - Unique constraint to prevent duplicate reviews per user/product
   - Indexed fields for optimized queries

2. **Review Verification**
   - Automatic verified purchase detection
   - Links reviews to orders when applicable
   - Prevents multiple reviews from same user for same product

3. **Moderation System**
   - Review approval workflow
   - Admin can approve/unapprove reviews
   - Only approved reviews shown publicly

4. **API Endpoints**
   - GET /products/{product}/reviews - Fetch paginated reviews
   - POST /products/{product}/reviews - Submit new review
   - PUT /reviews/{review} - Update existing review
   - DELETE /reviews/{review} - Delete review
   - GET /products/{product}/reviews/can-review - Check review eligibility

5. **Authorization**
   - Users can only edit/delete their own reviews
   - Admins can delete any review
   - Guests cannot submit reviews

## File Structure

### Backend Files

```
app/
├── Models/
│   └── ProductReview.php                   # Review model with relationships
├── Http/
│   ├── Controllers/
│   │   └── ProductReviewController.php     # Review API controller
│   └── Requests/
│       └── StoreProductReviewRequest.php   # Form validation rules
└── Filament/
    └── Resources/
        └── ProductReviewResource.php        # Admin panel resource

database/
├── migrations/
│   └── 2025_10_04_173349_create_product_reviews_table.php
└── factories/
    └── ProductReviewFactory.php             # Test data factory
```

### Frontend Files

```
resources/js/
├── Components/
│   ├── Products/
│   │   ├── ProductReviews.tsx              # Main reviews container
│   │   ├── ReviewForm.tsx                  # Review submission form
│   │   ├── ReviewItem.tsx                  # Individual review display
│   │   └── ReviewStats.tsx                 # Rating statistics display
│   ├── EmptyState.tsx                      # Updated with action support
│   └── ui/
│       └── alert-dialog.tsx                # Confirmation dialog component
├── Pages/
│   └── Products/
│       └── Show.tsx                        # Updated to include reviews
└── types/
    └── index.d.ts                          # Updated TypeScript types

resources/js/translations/
├── en.json                                 # English translations
└── ar.json                                 # Arabic translations
```

## Database Schema

```sql
CREATE TABLE product_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NULL,
    rating TINYINT UNSIGNED NOT NULL COMMENT 'Rating from 1 to 5',
    comment TEXT NULL,
    is_verified_purchase BOOLEAN DEFAULT 0,
    is_approved BOOLEAN DEFAULT 1,
    approved_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_user_product (product_id, user_id),
    KEY idx_product_approved (product_id, is_approved),
    KEY idx_user (user_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL
);
```

## API Usage

### Fetch Reviews

```javascript
GET /products/{productId}/reviews?page=1

Response:
{
    "reviews": {
        "data": [...],
        "current_page": 1,
        "last_page": 3,
        "total": 25
    },
    "stats": {
        "average_rating": 4.5,
        "total_reviews": 25,
        "rating_breakdown": {
            "5": 15,
            "4": 7,
            "3": 2,
            "2": 1,
            "1": 0
        }
    }
}
```

### Submit Review

```javascript
POST /products/{productId}/reviews
Content-Type: application/json

{
    "product_id": 1,
    "rating": 5,
    "comment": "Excellent product!"
}
```

### Update Review

```javascript
PUT /reviews/{reviewId}
Content-Type: application/json

{
    "rating": 4,
    "comment": "Updated my review"
}
```

### Delete Review

```javascript
DELETE /reviews/{reviewId}
```

## Admin Panel

Access the admin panel at `/admin/product-reviews` to:

- View all reviews with filtering options
- Approve/unapprove reviews (bulk actions available)
- Edit review content
- Delete inappropriate reviews
- Filter by rating, verification status, or approval status

## Testing

Comprehensive test coverage includes:

- Review submission (authenticated/guest)
- Duplicate review prevention
- Rating validation (1-5 range)
- Review editing/deletion
- Authorization checks
- Verified purchase detection
- Public API filtering (only approved reviews)

Run tests:
```bash
php artisan test --filter=ProductReview
```

## Translation Keys

Key translation strings added:

**English:**
- write_a_review, edit_review, your_review
- rating, select_rating
- review_comment, review_comment_placeholder
- submit_review, update_review, delete_review
- verified_purchase, customer_reviews
- average_rating, total_reviews

**Arabic:**
- Complete Arabic translations for all UI strings
- RTL-compatible layout

## Performance Considerations

1. **Indexes**: Multiple database indexes for fast queries
2. **Pagination**: Reviews are paginated (10 per page)
3. **Eager Loading**: User relationships are eager-loaded
4. **Computed Attributes**: Average rating and review count cached on product model

## Security

1. **Authorization**: Proper ownership checks for edit/delete
2. **Validation**: Strict input validation on all fields
3. **CSRF Protection**: All form submissions protected
4. **SQL Injection**: Prevention via Eloquent ORM
5. **XSS Protection**: Laravel's automatic escaping

## Future Enhancements

Potential improvements:

1. Review helpfulness voting (helpful/not helpful)
2. Review images/photos upload
3. Response to reviews by shop owners
4. Review filtering by rating
5. Sort reviews by most helpful/recent
6. Email notifications for new reviews
7. Review moderation queue
8. Sentiment analysis on comments
9. Review rewards/incentives program
10. Review sharing on social media

## Dependencies

**PHP Packages:**
- Laravel 11
- Filament 3 (admin panel)
- Inertia.js

**NPM Packages:**
- @radix-ui/react-alert-dialog
- React 18
- Lucide React (icons)

## Troubleshooting

### Reviews not appearing
- Check if reviews are approved (is_approved = true)
- Verify database migration ran successfully
- Check browser console for API errors

### Cannot submit review
- Ensure user is authenticated
- Check if user already reviewed the product
- Verify product exists and is active

### Rating stars not showing
- Check if average_rating appended attribute is working
- Verify product includes reviews relationship
- Check for JavaScript console errors

## Support

For questions or issues:
- Check application logs in `storage/logs/laravel.log`
- Review test output for failing tests
- Inspect browser console for frontend errors
- Check Filament admin panel for backend data
