# Paymob Payment Gateway API Documentation

## Table of Contents
1. [Overview](#overview)
2. [Authentication & Configuration](#authentication--configuration)
3. [API Endpoints](#api-endpoints)
4. [Payment Integration Flow](#payment-integration-flow)
5. [Unified Intention API](#unified-intention-api)
6. [Webhooks & Callbacks](#webhooks--callbacks)
7. [HMAC Security](#hmac-security)
8. [Refunds](#refunds)
9. [Error Handling](#error-handling)
10. [Testing](#testing)

---

## Overview

**Paymob** (Accept) is an Egyptian payment gateway that provides comprehensive payment processing solutions for merchants in Egypt and the Middle East. It supports multiple payment methods including credit/debit cards, mobile wallets, kiosk payments, and cash collection.

### Key Features
- ✅ Credit/Debit Card Payments
- ✅ Mobile Wallets (Vodafone Cash, Orange Money, etc.)
- ✅ Kiosk Payments
- ✅ Cash Collection
- ✅ 3D Secure Authentication
- ✅ Refunds & Voids
- ✅ Webhooks for transaction notifications
- ✅ Test & Live Modes

### Base URLs
- **Production/Live**: `https://accept.paymob.com`
- **Test/Sandbox**: `https://accept.paymob.com` (Same URL, differentiated by credentials)

---

## Authentication & Configuration

### Required Credentials

Paymob uses multiple keys for authentication and configuration:

| Key | Description | Usage |
|-----|-------------|-------|
| `API_KEY` | Legacy authentication key | Older API versions |
| `SECRET_KEY` | Token-based authentication | Authorization header: `Token <SECRET_KEY>` |
| `PUBLIC_KEY` | Client-side key | Used in iframe/checkout URL |
| `INTEGRATION_ID` | Payment method identifier | Specifies which payment integration to use |
| `IFRAME_ID` | Legacy iframe identifier | Older checkout implementations |
| `HMAC_SECRET` | Webhook validation key | HMAC signature verification |

### Environment Variables Example

```env
PAYMOB_API_KEY=your_api_key
PAYMOB_SECRET_KEY=your_secret_key
PAYMOB_PUBLIC_KEY=your_public_key
PAYMOB_INTEGRATION_ID=your_integration_id
PAYMOB_IFRAME_ID=your_iframe_id
PAYMOB_HMAC_SECRET=your_hmac_secret
PAYMOB_MODE=test  # or 'live'
```

### Authorization Header

All API requests require authentication via the `Authorization` header:

```http
Authorization: Token <YOUR_SECRET_KEY>
Content-Type: application/json
```

---

## API Endpoints

### 1. Create Payment Intention (Unified Intention API)

**Endpoint:** `POST /v1/intention/`

Creates a payment intention and returns a client secret for checkout.

#### Request

```http
POST https://accept.paymob.com/v1/intention/
Authorization: Token <SECRET_KEY>
Content-Type: application/json
```

**Request Body:**

```json
{
  "amount": 100000,
  "currency": "EGP",
  "payment_methods": [123456],
  "items": [
    {
      "name": "Product Name",
      "amount": 50000,
      "description": "Product description",
      "quantity": 2
    }
  ],
  "billing_data": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "customer@example.com",
    "phone_number": "+201000000000",
    "apartment": "12",
    "floor": "5",
    "street": "Main Street",
    "building": "Building A",
    "city": "Cairo",
    "country": "EG",
    "postal_code": "12345"
  },
  "customer": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "customer@example.com",
    "phone_number": "+201000000000"
  },
  "merchant_order_id": "ORDER_12345",
  "special_reference": "REF_12345",
  "extras": {
    "ee": "EXTRA_INFO"
  },
  "redirection_url": "https://yoursite.com/payment/success",
  "notification_url": "https://yoursite.com/webhooks/paymob"
}
```

#### Response

```json
{
  "id": "pi_test_abc123xyz456",
  "client_secret": "sec_abc123xyz456def789",
  "amount": 100000,
  "currency": "EGP",
  "status": "pending",
  "created_at": "2024-01-15T10:30:00.000000+02:00",
  "payment_methods": [123456],
  "merchant_order_id": "ORDER_12345",
  "special_reference": "REF_12345"
}
```

#### Key Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `amount` | integer | Yes | Amount in smallest currency unit (cents/piasters) |
| `currency` | string | Yes | Three-letter currency code (e.g., "EGP") |
| `payment_methods` | array | Yes | Array of integration IDs for payment methods |
| `billing_data` | object | Yes | Customer billing information |
| `merchant_order_id` | string | No | Your internal order reference |
| `redirection_url` | string | Yes | URL to redirect after payment |
| `notification_url` | string | Yes | Webhook endpoint for transaction updates |

---

### 2. Unified Checkout URL

After creating a payment intention, construct the checkout URL:

```
https://accept.paymob.com/unifiedcheckout/?publicKey={PUBLIC_KEY}&clientSecret={CLIENT_SECRET}
```

**Example:**
```
https://accept.paymob.com/unifiedcheckout/?publicKey=pub_abc123&clientSecret=sec_xyz789
```

This URL should be used to redirect customers to complete their payment.

---

### 3. Refund Transaction

**Endpoint:** `POST /api/acceptance/void_refund/refund`

Creates a refund for a completed transaction.

#### Request

```http
POST https://accept.paymob.com/api/acceptance/void_refund/refund
Authorization: Token <SECRET_KEY>
Content-Type: application/json
```

**Request Body:**

```json
{
  "transaction_id": 123456789,
  "amount_cents": 50000
}
```

#### Response

```json
{
  "id": 987654321,
  "transaction_id": 123456789,
  "amount_cents": 50000,
  "status": "success",
  "created_at": "2024-01-15T11:00:00.000000+02:00",
  "refund_type": "refund"
}
```

#### Key Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `transaction_id` | integer | Yes | Original transaction ID to refund |
| `amount_cents` | integer | Yes | Amount to refund in cents |

---

## Payment Integration Flow

### Standard Payment Flow

```
┌─────────────┐
│   Merchant  │
│   Website   │
└──────┬──────┘
       │
       │ 1. Create Order
       ↓
┌──────────────────┐
│ Backend (Server) │
└──────┬───────────┘
       │
       │ 2. POST /v1/intention/
       ↓
┌──────────────────┐
│  Paymob API      │
└──────┬───────────┘
       │
       │ 3. Return client_secret
       ↓
┌──────────────────┐
│ Backend (Server) │
└──────┬───────────┘
       │
       │ 4. Build checkout URL
       │    & redirect customer
       ↓
┌──────────────────────────┐
│ Paymob Unified Checkout  │
│ (Customer enters payment │
│      information)        │
└──────┬───────────────────┘
       │
       │ 5. Complete payment
       ↓
┌──────────────────────────┐
│   Payment Processed      │
└──────┬──────────┬────────┘
       │          │
       │          │ 6. Webhook POST (Server-side)
       │          ↓
       │   ┌──────────────────┐
       │   │ Backend Webhook  │
       │   │    Endpoint      │
       │   └──────────────────┘
       │
       │ 7. Redirect GET (Client-side)
       ↓
┌──────────────────┐
│  Success Page    │
└──────────────────┘
```

### Detailed Steps

1. **Create Order**: Customer initiates checkout on your website
2. **Create Payment Intention**: Your server calls Paymob's `/v1/intention/` endpoint
3. **Receive Client Secret**: Paymob returns a `client_secret` and intention `id`
4. **Build Checkout URL**: Construct unified checkout URL with public key and client secret
5. **Redirect Customer**: Redirect customer to Paymob's hosted checkout page
6. **Customer Pays**: Customer completes payment on Paymob's secure checkout
7. **Webhook Notification**: Paymob sends POST request to your webhook endpoint (server-side)
8. **Redirect Back**: Customer is redirected back to your site with transaction details (client-side)

---

## Unified Intention API

The **Unified Intention API** is Paymob's modern approach to payment processing. It simplifies the integration by creating a payment "intention" that can be completed through various payment methods.

### Advantages

- **Single API call** to create payment
- **Flexible payment methods** - customer chooses at checkout
- **Secure** - client secret ensures payment authenticity
- **Modern architecture** - follows payment industry best practices

### Amount Handling

⚠️ **Important:** Amounts must be in the **smallest currency unit**:

- **EGP (Egyptian Pound)**: 1 EGP = 100 piasters
- Example: 100.00 EGP = 10000 piasters (amount_cents)
- Example: 1,000.00 EGP = 100000 piasters

```javascript
// Converting to Paymob format
const orderTotal = 100.50; // EGP
const amountCents = Math.round(orderTotal * 100); // 10050

// Converting from Paymob format
const amountCents = 10050;
const displayAmount = amountCents / 100; // 100.50 EGP
```

### Billing Data Requirements

Paymob requires comprehensive billing data. Use "NA" for unavailable fields:

```json
{
  "billing_data": {
    "first_name": "John",
    "last_name": "Doe",
    "email": "customer@example.com",
    "phone_number": "+201000000000",
    "apartment": "12",
    "floor": "5",
    "street": "Main Street",
    "building": "Building A",
    "city": "Cairo",
    "country": "EG",
    "postal_code": "NA"
  }
}
```

---

## Webhooks & Callbacks

Paymob sends two types of notifications after payment processing:

### 1. Transaction Processed Callback (Server-Side)

**Type:** POST request with JSON body

**Purpose:** Server-to-server notification for reliable order processing

**URL Configuration:** Set in Paymob merchant dashboard or API request (`notification_url`)

#### Example Webhook Payload

```json
{
  "type": "TRANSACTION",
  "obj": {
    "id": 192036465,
    "pending": false,
    "amount_cents": 100000,
    "success": true,
    "is_auth": false,
    "is_capture": false,
    "is_standalone_payment": true,
    "is_voided": false,
    "is_refunded": false,
    "is_3d_secure": true,
    "integration_id": 4097558,
    "profile_id": 164295,
    "has_parent_transaction": false,
    "order": {
      "id": 217503754,
      "created_at": "2024-06-13T11:32:09.628623",
      "merchant_order_id": "ORDER_12345",
      "amount_cents": 100000,
      "paid_amount_cents": 100000,
      "currency": "EGP"
    },
    "created_at": "2024-06-13T11:33:44.592345",
    "currency": "EGP",
    "source_data": {
      "pan": "2346",
      "type": "card",
      "sub_type": "MasterCard"
    },
    "error_occured": false,
    "owner": 302852
  }
}
```

#### Key Webhook Fields

| Field | Type | Description |
|-------|------|-------------|
| `obj.id` | integer | Transaction ID |
| `obj.success` | boolean | Payment successful (true/false) |
| `obj.pending` | boolean | Payment pending (true/false) |
| `obj.amount_cents` | integer | Transaction amount in cents |
| `obj.order.id` | integer | Paymob order ID |
| `obj.order.merchant_order_id` | string | Your order reference |
| `obj.source_data.type` | string | Payment method type (card, wallet, etc.) |
| `obj.is_refunded` | boolean | Transaction refunded |
| `obj.is_voided` | boolean | Transaction voided |

#### Transaction States

| `success` | `pending` | State | Action |
|-----------|-----------|-------|--------|
| `true` | `false` | ✅ **Successful** | Complete order |
| `false` | `true` | ⏳ **Pending** | Wait for completion |
| `false` | `false` | ❌ **Declined** | Cancel order |

### 2. Transaction Response Callback (Client-Side)

**Type:** GET redirect with query parameters

**Purpose:** Redirect customer back to your site after payment

**URL Configuration:** `redirection_url` in intention API request

#### Example Redirect URL

```
https://yoursite.com/payment/success?id=192036465&success=true&pending=false
&amount_cents=100000&currency=EGP&order=217503754&merchant_order_id=ORDER_12345
&hmac=8aa3e005de7f639dac10952884963d47a65b2b85d3381803b3f22ff2cd372e57...
```

#### Query Parameters

Same fields as webhook payload, but passed as URL query parameters.

### Comparison: Processed vs Response Callbacks

| Feature | Processed Callback | Response Callback |
|---------|-------------------|-------------------|
| **Type** | POST | GET |
| **Format** | JSON body | Query parameters |
| **Direction** | Server-to-Server | Browser redirect |
| **Reliability** | ⭐⭐⭐ High | ⭐⭐ Medium |
| **Use Case** | Order processing | User experience |
| **Required** | ✅ Yes | ✅ Yes |

⚠️ **Best Practice:** Always process orders using the **Transaction Processed Callback** (webhook). The response callback is only for user interface updates.

---

## HMAC Security

**HMAC (Hash-based Message Authentication Code)** ensures that callbacks originate from Paymob and haven't been tampered with.

### Why HMAC?

- ✅ Verify callback authenticity
- ✅ Prevent man-in-the-middle attacks
- ✅ Ensure data integrity
- ✅ Protect against replay attacks

### HMAC Validation Process

#### Step 1: Extract Callback Data

Receive the callback with HMAC parameter.

#### Step 2: Sort Keys Lexicographically

For **Transaction Processed Callback**, use these keys in order:

```
amount_cents
created_at
currency
error_occured
has_parent_transaction
id
integration_id
is_3d_secure
is_auth
is_capture
is_refunded
is_standalone_payment
is_voided
order.id
owner
pending
source_data.pan
source_data.sub_type
source_data.type
success
```

#### Step 3: Concatenate Values

Extract values in the same order and concatenate:

**Example:**
```
1000002024-06-13T11:33:44.592345EGPfalsefalse1920364654097558truefalsefalsefalsetruefalse217503754302852false2346MasterCardcardtrue
```

#### Step 4: Calculate HMAC

Use **HMAC-SHA512** with your HMAC secret key:

```
HMAC-SHA512(concatenated_string, hmac_secret)
```

#### Step 5: Compare

Compare calculated HMAC with received HMAC. If they match, the callback is authentic.

### HMAC Implementation Example (Pseudocode)

```javascript
function validatePaymobWebhook(callbackData, receivedHmac) {
  // Define keys in lexicographical order
  const keys = [
    'amount_cents',
    'created_at',
    'currency',
    'error_occured',
    'has_parent_transaction',
    'id',
    'integration_id',
    'is_3d_secure',
    'is_auth',
    'is_capture',
    'is_refunded',
    'is_standalone_payment',
    'is_voided',
    'order.id',
    'owner',
    'pending',
    'source_data.pan',
    'source_data.sub_type',
    'source_data.type',
    'success'
  ];
  
  // Concatenate values
  let concatenated = '';
  for (const key of keys) {
    const value = getNestedValue(callbackData, key);
    concatenated += String(value);
  }
  
  // Calculate HMAC
  const calculatedHmac = hmacSha512(concatenated, HMAC_SECRET);
  
  // Compare
  return calculatedHmac === receivedHmac;
}
```

### HMAC for Transaction Response Callback

Use the same keys but read values from **query parameters** instead of JSON body.

### Online HMAC Generator

For testing, use: [https://www.freeformatter.com/hmac-generator.html](https://www.freeformatter.com/hmac-generator.html)

- **Algorithm:** SHA-512
- **String:** Concatenated values
- **Secret Key:** Your HMAC secret from Paymob dashboard

---

## Refunds

Paymob supports full and partial refunds for completed transactions.

### Refund Request

```http
POST https://accept.paymob.com/api/acceptance/void_refund/refund
Authorization: Token <SECRET_KEY>
Content-Type: application/json

{
  "transaction_id": 192036465,
  "amount_cents": 50000
}
```

### Refund Response

**Success:**
```json
{
  "id": 987654321,
  "transaction_id": 192036465,
  "amount_cents": 50000,
  "status": "success",
  "created_at": "2024-01-15T11:00:00.000000+02:00"
}
```

**Failure:**
```json
{
  "detail": "Transaction cannot be refunded",
  "status": "error"
}
```

### Refund Rules

- ✅ Full and partial refunds supported
- ✅ Multiple partial refunds allowed (up to original amount)
- ❌ Cannot refund more than original transaction amount
- ❌ Cannot refund pending or failed transactions
- ⏳ Refunds typically process within 3-7 business days

### Checking Refund Status

Refunded transactions will have:
- `is_refunded`: `true`
- `refunded_amount_cents`: Total refunded amount

---

## Error Handling

### Common HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Invalid or missing authentication |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Internal Server Error | Server-side error |

### Error Response Format

```json
{
  "detail": "Error message description",
  "errors": {
    "field_name": ["Specific error for this field"]
  }
}
```

### Common Errors

#### 1. Invalid Authentication

```json
{
  "detail": "Invalid token."
}
```

**Solution:** Verify `SECRET_KEY` is correct and properly formatted in Authorization header.

#### 2. Invalid Integration ID

```json
{
  "detail": "Invalid payment_methods"
}
```

**Solution:** Ensure `INTEGRATION_ID` matches your Paymob dashboard configuration.

#### 3. Missing Required Fields

```json
{
  "errors": {
    "billing_data": ["This field is required"]
  }
}
```

**Solution:** Include all required billing data fields.

#### 4. Amount Validation Error

```json
{
  "detail": "Amount must be greater than 0"
}
```

**Solution:** Ensure amount is positive integer in smallest currency unit.

---

## Testing

### Test Mode

Paymob uses the same base URL for test and live modes. The mode is determined by your credentials.

**Test Credentials:** Obtained from Paymob dashboard in test mode

### Test Card Numbers

| Card Number | Result |
|-------------|--------|
| `5123450000000008` | ✅ Success |
| `4987654321098769` | ❌ Declined |
| `2223000000000007` | ⏳ Pending (timeout) |

### Test Card Details

- **Expiry:** Any future date (e.g., 12/25)
- **CVV:** Any 3 digits (e.g., 123)
- **Cardholder Name:** Any name

### Testing Webhooks Locally

For local development, use tunneling services to expose your webhook endpoint:

- **ngrok**: `ngrok http 8000`
- **localtunnel**: `lt --port 8000`
- **webhook.site**: For inspecting webhook payloads

### Testing Tools

1. **Postman/Insomnia**: Test API endpoints directly
2. **webhook.site**: Inspect webhook payloads
3. **RequestBin**: Debug callback issues
4. **HMAC Generators**: Verify HMAC calculations

---

## Best Practices

### Security

1. ✅ **Always validate HMAC** on webhook callbacks
2. ✅ **Store credentials securely** (environment variables, secrets manager)
3. ✅ **Use HTTPS** for all webhook endpoints
4. ✅ **Validate amount** matches your order
5. ✅ **Check transaction uniqueness** to prevent duplicate processing

### Reliability

1. ✅ **Process orders from webhook**, not redirect callback
2. ✅ **Implement idempotency** - handle duplicate webhooks gracefully
3. ✅ **Return 200 OK** quickly from webhook endpoint
4. ✅ **Process asynchronously** - queue long-running tasks
5. ✅ **Log all interactions** for debugging

### User Experience

1. ✅ **Show loading state** during payment
2. ✅ **Handle all transaction states** (success, pending, declined)
3. ✅ **Clear error messages** for customers
4. ✅ **Email confirmations** for completed orders
5. ✅ **Support order lookup** by merchant_order_id

### Integration

1. ✅ **Store payment details** in your database
2. ✅ **Save transaction_id** for refunds
3. ✅ **Track payment method** used by customer
4. ✅ **Handle currency properly** (always use smallest unit)
5. ✅ **Test thoroughly** before going live

---

## Additional Resources

### Official Documentation

- **Paymob Documentation**: [https://docs.paymob.com/](https://docs.paymob.com/)
- **Merchant Dashboard**: [https://accept.paymob.com/portal](https://accept.paymob.com/portal)
- **API Reference**: Available in merchant dashboard

### Support

- **Email**: support@paymob.com
- **Technical Support**: Available through merchant dashboard
- **Integration Support**: Dedicated integration team for merchants

### API Versioning

Paymob maintains backward compatibility but introduces new API versions:

- **v1** - Current unified intention API (recommended)
- **Legacy APIs** - Older authentication/payment key flow (deprecated)

Always use the latest API version for new integrations.

---

## Quick Reference

### Required Headers

```http
Authorization: Token <SECRET_KEY>
Content-Type: application/json
```

### Key Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/v1/intention/` | POST | Create payment intention |
| `/api/acceptance/void_refund/refund` | POST | Refund transaction |
| `/unifiedcheckout/` | GET | Hosted checkout page |

### Important Fields

| Field | Format | Example |
|-------|--------|---------|
| Amount | Integer (cents) | 100000 (= 1000.00 EGP) |
| Phone | International | +201234567890 |
| Currency | ISO 4217 | EGP, USD, EUR |
| Country | ISO 3166-1 alpha-2 | EG, US, GB |

---

## Changelog

### Version History

- **v1 (Current)**: Unified Intention API with modern checkout experience
- **Legacy**: Authentication token + Payment key flow (deprecated)

---

## License & Legal

This documentation is for educational purposes. Refer to official Paymob documentation for authoritative information. Payment processing is subject to Paymob's terms of service and merchant agreement.

**Paymob** is a registered trademark of Paymob Solutions.

---

**Document Version:** 1.0  
**Last Updated:** October 2025  
**Maintained by:** Development Team

For implementation-specific questions, consult the official Paymob documentation or contact their integration support team.
