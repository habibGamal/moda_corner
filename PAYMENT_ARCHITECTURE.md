# Payment Gateway Architecture

This document describes the new SOLID-compliant payment gateway architecture that supports multiple payment providers with a flexible, extensible design.

## Architecture Overview

The new payment system is built using SOLID principles and design patterns:

- **Single Responsibility Principle (SRP)**: Each class has a single, well-defined responsibility
- **Open/Closed Principle (OCP)**: Open for extension (new gateways) but closed for modification
- **Liskov Substitution Principle (LSP)**: All gateways implement the same interface contract
- **Interface Segregation Principle (ISP)**: Clean, focused interfaces without unnecessary dependencies
- **Dependency Inversion Principle (DIP)**: Controllers depend on abstractions, not concrete implementations

## Key Components

### 1. Core Interfaces

#### `PaymentGatewayInterface`
The main interface that all payment gateways must implement.

```php
interface PaymentGatewayInterface
{
    public function getGatewayName(): string;
    public function initializePayment(Order $order): PaymentRequestData;
    public function validatePaymentResponse(array $params): PaymentValidationData;
    public function processSuccessfulPayment(Order $order, array $paymentData): PaymentResponseData;
    public function getPaymentRedirectData(PaymentRequestData $paymentRequest): PaymentRedirectData;
    // ... other methods
}
```

#### `PaymentGatewayFactoryInterface`
Factory interface for creating payment gateway instances.

```php
interface PaymentGatewayFactoryInterface
{
    public function createGateway(PaymentMethod|string $paymentMethod): PaymentGatewayInterface;
    public function createGatewayForOrder(Order $order): PaymentGatewayInterface;
    public function getAvailableGateways(): array;
    // ... other methods
}
```

### 2. Data Transfer Objects (DTOs)

Located in `app/DTOs/Payment/`, these provide consistent data structures across all gateways:

- **`PaymentRequestData`**: Contains payment initialization data
- **`PaymentValidationData`**: Contains payment validation results
- **`PaymentResponseData`**: Contains payment processing results
- **`PaymentRedirectData`**: Contains UI redirect information

### 3. Gateway Implementations

#### KashierGateway (`app/Services/Payment/Gateways/KashierGateway.php`)
Implements the Kashier payment gateway integration.

#### PaymobGateway (`app/Services/Payment/Gateways/PaymobGateway.php`)
Implements the Paymob payment gateway integration.

### 4. Factory Implementation

#### `PaymentGatewayFactory` (`app/Services/Payment/PaymentGatewayFactory.php`)
Concrete implementation that creates appropriate gateway instances based on payment method.

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Kashier Configuration
KASHIER_MERCHANT_ID=your_merchant_id
KASHIER_API_KEY=your_api_key
KASHIER_SECRET_KEY=your_secret_key
KASHIER_MODE=test

# Paymob Configuration
PAYMOB_API_KEY=your_api_key
PAYMOB_SECRET_KEY=your_secret_key
PAYMOB_PUBLIC_KEY=your_public_key
PAYMOB_INTEGRATION_ID=your_integration_id
PAYMOB_MODE=test
```

### Service Configuration

Configuration is handled in `config/services.php`:

```php
'kashier' => [
    'merchant_id' => env('KASHIER_MERCHANT_ID'),
    'api_key' => env('KASHIER_API_KEY'),
    'secret_key' => env('KASHIER_SECRET_KEY'),
    'mode' => env('KASHIER_MODE', 'test'),
],

'paymob' => [
    'api_key' => env('PAYMOB_API_KEY'),
    'secret_key' => env('PAYMOB_SECRET_KEY'),
    'public_key' => env('PAYMOB_PUBLIC_KEY'),
    'integration_id' => env('PAYMOB_INTEGRATION_ID'),
    'mode' => env('PAYMOB_MODE', 'test'),
],
```

## Usage Examples

### Using the Factory Pattern (Recommended)

```php
use App\Interfaces\PaymentGatewayFactoryInterface;
use App\Enums\PaymentMethod;

class OrderController extends Controller
{
    public function __construct(
        private PaymentGatewayFactoryInterface $gatewayFactory
    ) {}

    public function processPayment(Order $order)
    {
        // Get the appropriate gateway for this order
        $gateway = $this->gatewayFactory->createGatewayForOrder($order);
        
        // Initialize payment
        $paymentRequest = $gateway->initializePayment($order);
        
        // Get redirect data for UI
        $redirectData = $gateway->getPaymentRedirectData($paymentRequest);
        
        // Handle different redirect types
        if ($redirectData->isDirectUrl()) {
            return redirect()->away($redirectData->directUrl);
        } elseif ($redirectData->isComponent()) {
            return Inertia::render($redirectData->componentName, $redirectData->props);
        }
    }
}
```

### Creating a Specific Gateway

```php
// Create a specific gateway
$kashierGateway = $this->gatewayFactory->createGateway(PaymentMethod::KASHIER);
$paymobGateway = $this->gatewayFactory->createGateway(PaymentMethod::PAYMOB);

// Check if a payment method is supported
if ($this->gatewayFactory->isPaymentMethodSupported(PaymentMethod::PAYMOB)) {
    $gateway = $this->gatewayFactory->createGateway(PaymentMethod::PAYMOB);
}
```

### Handling Webhooks

```php
public function handleWebhook(Request $request, string $gateway)
{
    $paymentGateway = $this->gatewayFactory->createGateway($gateway);
    
    // Validate the webhook
    $validationResult = $paymentGateway->validatePaymentResponse($request->all());
    
    if (!$validationResult->isValid) {
        return response()->json(['status' => 'error'], 400);
    }
    
    // Process the payment
    $order = Order::find($validationResult->orderId);
    $paymentResponse = $paymentGateway->processSuccessfulPayment($order, $request->all());
    
    return response()->json(['status' => 'success']);
}
```

## Routes

### New Unified Routes (Recommended)

```php
// Generic payment routes (uses factory pattern)
Route::get('/payments/{order}', [PaymentController::class, 'showPayment'])->name('payment.show');
Route::get('/payments/{gateway}/success', [PaymentController::class, 'handleSuccess'])->name('payment.success');
Route::get('/payments/{gateway}/failure', [PaymentController::class, 'handleFailure'])->name('payment.failure');
Route::post('/webhooks/{gateway}', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');
```

### Gateway-Specific Routes (For specific needs)

```php
// Paymob specific routes
Route::get('/payments/paymob/{order}', [PaymobPaymentController::class, 'showPayment'])->name('paymob.payment.show');
Route::get('/payments/paymob/success', [PaymobPaymentController::class, 'handleSuccess'])->name('paymob.payment.success');
Route::post('/webhooks/paymob', [PaymobPaymentController::class, 'handleWebhook'])->name('paymob.payment.webhook');

// Legacy Kashier routes (maintained for backward compatibility)
Route::get('/payments/kashier/{order}', [KashierPaymentController::class, 'showPayment'])->name('kashier.payment.show');
```

## Adding New Payment Gateways

To add a new payment gateway:

1. **Create Gateway Class**
   ```php
   class NewGateway implements PaymentGatewayInterface
   {
       public function getGatewayName(): string
       {
           return 'new_gateway';
       }
       
       // Implement all interface methods...
   }
   ```

2. **Update Factory**
   ```php
   // In PaymentGatewayFactory::GATEWAY_MAP
   private const GATEWAY_MAP = [
       // ... existing mappings
       'new_gateway' => NewGateway::class,
   ];
   ```

3. **Add Configuration**
   ```php
   // In config/services.php
   'new_gateway' => [
       'api_key' => env('NEW_GATEWAY_API_KEY'),
       'secret_key' => env('NEW_GATEWAY_SECRET_KEY'),
       'mode' => env('NEW_GATEWAY_MODE', 'test'),
   ],
   ```

4. **Update PaymentMethod Enum**
   ```php
   enum PaymentMethod: string
   {
       // ... existing cases
       case NEW_GATEWAY = 'new_gateway';
   }
   ```

5. **Register in Service Provider**
   ```php
   // In AppServiceProvider::register()
   $this->app->bind(NewGateway::class);
   ```

## Testing

Run the payment gateway tests:

```bash
php artisan test tests/Unit/PaymentGatewayFactoryTest.php
```

### Example Test

```php
it('creates new gateway for new payment method', function () {
    $gateway = $this->factory->createGateway('new_gateway');
    
    expect($gateway)->toBeInstanceOf(NewGateway::class);
    expect($gateway->getGatewayName())->toBe('new_gateway');
});
```

## Migration Guide

### Key Benefits of New Architecture

1. **Extensibility**: Easy to add new payment gateways without modifying existing code
2. **Testability**: Each gateway can be tested independently
3. **Maintainability**: Clear separation of concerns and responsibilities
4. **Flexibility**: Support for different payment flows (direct URL, component, form POST)
5. **Type Safety**: Strong typing with DTOs and interfaces

## Payment Methods

The system supports these payment methods:

- **Cash on Delivery** (`cash_on_delivery`) - No gateway required
- **Kashier** (`kashier`) - Uses KashierGateway
- **Credit Card** (`credit_card`) - Uses PaymobGateway
- **Paymob** (`paymob`) - Uses PaymobGateway

## Error Handling

The system provides comprehensive error handling:

- Gateway creation failures are logged and throw meaningful exceptions
- Payment validation failures return detailed error information
- Webhook processing errors are logged with full context

## Security

- All payment validations use timing-safe comparisons
- Webhook signatures are properly verified
- Sensitive data is logged appropriately (excluding secrets)
- CSRF protection is disabled only for webhook endpoints

This architecture provides a solid foundation for handling multiple payment gateways while maintaining clean, testable, and extensible code.
