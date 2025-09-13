# Partial Order Return Implementation Approaches

## Current State Analysis

### Current Return System
The application currently implements a **whole-order return system** with the following characteristics:

1. **Order-Level Returns**: Returns are managed at the order level, not item level
2. **Single Return Status**: One `return_status` field on the `orders` table
3. **Binary State**: An order is either returned or not returned
4. **Refund Logic**: Processes refunds for entire order amounts
5. **Inventory Management**: Returns all order items to stock at once

### Current Models Structure
- **Order Model**: Contains return-related fields (`return_status`, `return_requested_at`, `return_reason`, `refunded_at`)
- **OrderItem Model**: Simple structure with no return-specific fields
- **ReturnStatus Enum**: Handles return states at order level

---

## Approach 1: Item-Level Return with Order Status Tracking

### Overview
Add return tracking at the item level while maintaining order-level status aggregation.

### Database Changes Required
```sql
-- Add return fields to order_items table
ALTER TABLE order_items ADD COLUMN return_status VARCHAR(255) NULL;
ALTER TABLE order_items ADD COLUMN return_requested_at TIMESTAMP NULL;
ALTER TABLE order_items ADD COLUMN return_reason TEXT NULL;
ALTER TABLE order_items ADD COLUMN returned_quantity INT NOT NULL DEFAULT 0;
ALTER TABLE order_items ADD COLUMN return_approved_at TIMESTAMP NULL;
ALTER TABLE order_items ADD COLUMN return_completed_at TIMESTAMP NULL;
```

### Implementation Details

#### Pros
- **Minimal Changes**: Extends current system without breaking existing functionality
- **Granular Control**: Track return status for each item independently
- **Flexible Quantities**: Support partial quantity returns (e.g., return 2 out of 5 items)
- **Maintains Order Status**: Keep existing order-level return status for compatibility
- **Simple UI**: Can display return status per item in order details

#### Cons
- **Data Redundancy**: Some return information duplicated between order and item levels
- **Complex Aggregation**: Need to calculate order-level status from item statuses
- **Migration Complexity**: Need to handle existing orders with returns
- **Refund Complexity**: Calculate partial refunds based on returned items

#### Service Changes
```php
class OrderReturnService
{
    public function requestItemReturn(int $orderId, int $orderItemId, int $quantity, string $reason): OrderItem
    {
        // Validate eligibility
        // Update order_item return fields
        // Update order-level status if needed
        // Send notifications
    }
    
    public function approveItemReturn(int $orderItemId): OrderItem
    {
        // Update item status
        // Check if all requested items are processed
        // Update order status accordingly
    }
    
    public function completeItemReturn(int $orderItemId): OrderItem
    {
        // Return inventory to stock
        // Process partial refund
        // Update statuses
    }
}
```

---

## Approach 2: Separate Return Entity Model

### Overview
Create a dedicated `ReturnOrder` model to handle all return operations independently from orders.

### Database Changes Required
```sql
-- Create return_orders table
CREATE TABLE return_orders (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    return_number VARCHAR(255) UNIQUE NOT NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'requested',
    reason TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    refund_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    requested_at TIMESTAMP NOT NULL,
    approved_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    rejected_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create return_items table
CREATE TABLE return_order_items (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    return_id BIGINT NOT NULL,
    order_item_id BIGINT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE CASCADE
);
```

### Implementation Details

#### Pros
- **Clean Separation**: Returns are completely separate from orders
- **Multiple Returns**: Allow multiple return requests for the same order
- **Return Numbers**: Generate unique return tracking numbers
- **Detailed History**: Complete audit trail for each return
- **Flexible Logic**: Independent return processing workflow
- **Easy Reporting**: Dedicated tables for return analytics

#### Cons
- **More Complex**: Additional models and relationships to manage
- **UI Complexity**: Need separate interfaces for return management
- **Migration Required**: Significant changes to existing return logic
- **Performance**: Additional queries to fetch return information
- **Learning Curve**: Team needs to understand new return workflow

#### New Models
```php
class ReturnOrder extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'return_number', 'status', 'reason',
        'total_amount', 'refund_amount', 'requested_at', 'approved_at',
        'completed_at', 'rejected_at', 'rejection_reason'
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function returnItems(): HasMany
    {
        return $this->hasMany(ReturnOrderItem::class);
    }
}

class ReturnOrderItem extends Model
{
    protected $fillable = [
        'return_id', 'order_item_id', 'quantity', 'unit_price', 'subtotal', 'reason'
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(Return::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
```

---

## Approach 3: Hybrid Approach with Return Tracking

### Overview
Combine order-level return status with detailed item-level return tracking using a separate return entity.

### Database Changes Required
```sql
-- Keep existing order return fields for compatibility
-- Add new returns table (same as Approach 2)
-- Add return_items table (same as Approach 2)
-- Add summary fields to orders table
ALTER TABLE orders ADD COLUMN has_returns BOOLEAN DEFAULT FALSE;
ALTER TABLE orders ADD COLUMN total_return_amount DECIMAL(10,2) DEFAULT 0;
ALTER TABLE orders ADD COLUMN active_returns_count INT DEFAULT 0;
```

### Implementation Details

#### Pros
- **Backward Compatibility**: Existing order return fields preserved
- **Best of Both**: Order-level summary + detailed return tracking
- **Progressive Migration**: Can implement gradually
- **Flexible UI**: Support both simple and detailed return views
- **Analytics Friendly**: Easy to query both order and return metrics

#### Cons
- **Maintenance Overhead**: Need to keep order and return data in sync
- **Data Complexity**: Multiple sources of truth for return information
- **Storage Overhead**: Some redundant information stored
- **Complex Logic**: Synchronization between order and return states

#### Synchronization Strategy
```php
class ReturnSyncService
{
    public function syncOrderReturnStatus(Order $order): void
    {
        $returns = $order->returns;
        $totalReturnAmount = $returns->sum('refund_amount');
        $activeReturnsCount = $returns->whereIn('status', ['requested', 'approved'])->count();
        
        $order->update([
            'has_returns' => $returns->count() > 0,
            'total_return_amount' => $totalReturnAmount,
            'active_returns_count' => $activeReturnsCount,
            'return_status' => $this->calculateAggregateReturnStatus($returns)
        ]);
    }
}
```

---

## Approach 4: Event-Driven Return System

### Overview
Implement a comprehensive event-driven system for handling partial returns with domain events.

### Database Changes Required
```sql
-- Returns table (same as Approach 2)
-- Return items table (same as Approach 2)
-- Add events table for audit trail
CREATE TABLE return_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    return_id BIGINT NOT NULL,
    event_type VARCHAR(255) NOT NULL,
    event_data JSON NOT NULL,
    triggered_by BIGINT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (return_id) REFERENCES returns(id) ON DELETE CASCADE,
    FOREIGN KEY (triggered_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### Implementation Details

#### Pros
- **Event Sourcing**: Complete audit trail of all return operations
- **Decoupled Logic**: Return processing, inventory, and refunds handled separately
- **Extensible**: Easy to add new return-related features
- **Reliable**: Event-driven approach ensures consistency
- **Analytics**: Rich data for return pattern analysis

#### Cons
- **Complexity**: Requires understanding of event-driven architecture
- **Development Time**: More complex to implement initially
- **Debugging**: Event-driven systems can be harder to debug
- **Team Training**: Requires team to understand event patterns

#### Event Examples
```php
// Domain Events
class ReturnRequestedEvent
{
    public function __construct(
        public Return $return,
        public array $returnItems,
        public User $user
    ) {}
}

class ReturnItemApprovedEvent
{
    public function __construct(
        public ReturnItem $returnItem,
        public User $approvedBy
    ) {}
}

// Event Listeners
class ProcessInventoryReturnListener
{
    public function handle(ReturnItemApprovedEvent $event): void
    {
        $this->inventoryService->returnInventory(
            $event->returnItem->orderItem->variant,
            $event->returnItem->quantity
        );
    }
}
```

---

## Comparison Matrix

| Criteria | Approach 1: Item-Level | Approach 2: Separate Entity | Approach 3: Hybrid | Approach 4: Event-Driven |
|----------|----------------------|---------------------------|-------------------|-------------------------|
| **Implementation Complexity** | Medium | High | High | Very High |
| **Development Time** | 2-3 weeks | 4-5 weeks | 5-6 weeks | 6-8 weeks |
| **Data Consistency** | Medium | High | Medium | Very High |
| **Backward Compatibility** | High | Low | High | Medium |
| **Scalability** | Medium | High | High | Very High |
| **Maintainability** | Medium | High | Medium | High |
| **Return Flexibility** | High | Very High | Very High | Very High |
| **Audit Trail** | Medium | High | High | Very High |
| **Performance Impact** | Low | Medium | Medium | Medium |
| **Team Learning Curve** | Low | Medium | Medium | High |

---

## Recommendation

### For Quick Implementation: **Approach 1 (Item-Level Return)**
- Fastest to implement
- Minimal disruption to existing code
- Good balance of features vs complexity
- Can be enhanced later

### For Long-term Scalability: **Approach 2 (Separate Entity)**
- Clean architecture
- Best for future enhancements
- Clear separation of concerns
- Industry standard approach

### For Gradual Migration: **Approach 3 (Hybrid)**
- Allows gradual transition
- Maintains existing functionality
- Good for large existing orders database

### For Enterprise-grade: **Approach 4 (Event-Driven)**
- Most robust and scalable
- Best audit trail and consistency
- Future-proof architecture
- Requires advanced development expertise

---

## Implementation Considerations

### 1. Migration Strategy
- **Data Migration**: Handle existing orders with returns
- **API Compatibility**: Ensure existing APIs continue to work
- **UI Updates**: Update admin and customer interfaces
- **Testing**: Comprehensive testing for all return scenarios

### 2. Business Rules
- **Return Window**: Define return eligibility periods
- **Quantity Limits**: Handle partial quantity returns
- **Refund Calculations**: Proportional refunds for partial returns
- **Shipping Costs**: How to handle shipping cost refunds
- **Promotions**: Handle returns on discounted items

### 3. User Experience
- **Customer Interface**: Easy item selection for returns
- **Admin Interface**: Efficient processing workflows
- **Notifications**: Keep users informed of return status
- **Documentation**: Clear return policies and procedures

### 4. Integration Points
- **Inventory System**: Real-time stock updates
- **Payment Gateway**: Partial refund processing
- **Notification System**: Multi-channel return updates
- **Analytics**: Return metrics and reporting

---

## Next Steps

1. **Choose Approach**: Select the most suitable approach based on requirements
2. **Create Detailed Specifications**: Define exact business rules and workflows
3. **Design Database Schema**: Create migration files and model relationships
4. **Plan Implementation Phases**: Break down development into manageable phases
5. **Prepare Test Cases**: Define comprehensive testing scenarios
6. **Update Documentation**: Update API documentation and user guides
