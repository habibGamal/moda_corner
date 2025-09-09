# Paymob Integration Documentation

## Table of Contents
- [Getting Started - Egypt](#getting-started---egypt)
- [Accept Dashboard](#accept-dashboard)
- [API Checkout Experience](#api-checkout-experience)
- [Basics of API](#basics-of-api)
- [API Setup - (Secret and Public Key)](#api-setup---secret-and-public-key)
- [Unified Intention API Experience](#unified-intention-api-experience)
- [Pixel (Native Payment Experience)](#pixel-native-payment-experience)
- [Split Payment Feature](#split-payment-feature)
- [E-Commerce Plugins](#e-commerce-plugins)
  - [Shopify](#shopify)
  - [WordPress (WooCommerce)](#wordpress-woocommerce)
  - [OpenCart](#opencart)
  - [Magento](#magento)
  - [Odoo](#odoo)
  - [WHMCS](#whmcs)
  - [Cs-Cart](#cs-cart)
  - [PrestaShop](#prestashop)
  - [Joomla](#joomla)
  - [ZenCart](#zencart)
  - [OsCommerce](#oscommerce)
  - [Laravel-Bagisto](#laravel-bagisto)
  - [Drupal](#drupal)
- [Mobile SDKs](#mobile-sdks)
- [Subscriptions](#subscriptions)
- [Payment Types](#payment-types)
- [Payment Links](#payment-links)
- [Payment Actions](#payment-actions)
- [Payment Methods](#payment-methods)
- [Test Credentials](#test-credentials)
- [Transaction Inquiry API](#transaction-inquiry-api)
- [Manage Callback](#manage-callback)
- [Error Codes](#error-codes)
- [Bills Portal API](#bills-portal-api)
- [Payouts](#payouts)

---

## Getting Started - Egypt

### Sign up for registering a new account
A guide to registering a new Accept account.

**Why do I have to?** In order to deal with any of Accept's services, you have to register an account.

**To create your account, follow these steps:**
1. Navigate to Accept's home page
2. Enter your mobile number to receive OTP for first-time verification
3. Enter the OTP received on your mobile number
4. Add your name, email details, and desired password
5. Login with your registered mobile number (without country code) and password
6. Enter your business details
7. Receive "Registration successful" message and be routed to your Paymob Dashboard

**Note:** The status of the created account will be marked as a "Test" account, allowing you to test all of Accept's services. When finished testing, contact your Account Manager or Support team (support@paymob.com) to change status to "Live".

---

## Accept Dashboard

In your Accept portal, you can find:
- Profile
- Payment Integrations
- Orders
- Transactions
- Checkout Customization

---

## API Checkout Experience

Welcome to the API Checkout Experience documentation! This guide covers:
- Integrating and setting up our API
- Generating your keys
- Configuring Unified Intention API Checkout and Pixel Native Payment experiences
- API overview and setup instructions
- Using various endpoints for secure and efficient transactions

---

## Basics of API

### Introduction to RESTful APIs and HTTP Methods in Paymob

**RESTful Functionality:** Exposes resources and allows clients to perform actions on them.

**HTTP Methods:**
- GET: Retrieve a representation of a specific resource
- POST: Submit data to create a new resource or trigger state change
- PUT: Replace all representations of a target resource
- DELETE: Remove a specified resource
- PATCH: Apply partial modifications to a resource

**Parameters:**
- Path Parameters: Integral parts of the endpoint URL
- Query Parameters: Appended to filter or paginate results
- Request Parameters: Included in the request body
- Response Parameters: Data returned by the server

**HTTPS Status Codes:**
- Success (200): Successful response
- Client Error (400): Error on the transactions
- Server Error (500): Internal server error

---

## API Setup - (Secret and Public Key)

### Step 1: Sign Up for Paymob
Visit the registration link to create your account.

### Step 2: Get API Key, Secret key, and Public key

**Get Test Mode Keys:**
1. Log in to your Paymob account dashboard
2. Navigate to Settings → Account Info
3. Click "View API Key, Secretkey, or public key"
4. Set account to 'Test' mode
5. Copy test keys (format: `egy_pk_test_XXXXXXXXXXXXXX`)

**Get Live Mode Keys:**
1. Log in to your Paymob account dashboard
2. Navigate to Settings → Account Info
3. Click "View API Key Secret key or public key"
4. Set account to 'Live' mode
5. Copy live keys (format: `egy_pk_live_XXXXXXXXXXXXXXXXX`)

### Step 3: Testing APIs on Postman
1. Download and install Postman
2. Create a new request
3. Enter API endpoint URL
4. Set appropriate HTTP method
5. Add required headers (including authentication)
6. Input necessary request parameters or payloads
7. Click "Send" to execute request

**Create Intention/Payment API:**
- URL: `https://accept.paymob.com/unifiedcheckout/?publicKey=<your_public_key>&clientSecret=<your_client_secret>`
- Get Public key from Dashboard
- Get client secret key from Intention API response

**Header Parameters:**
- Authorization: string (secret key from dashboard)
- Content-Type: string

**Body Parameters:**
- amount: number
- currency: string
- payment_methods: array
- items: array
- billing_data: object
- customer: object
- extras: object

**Response:**
- payment_keys: array
- id: string
- intention_detail: object
- client_secret: string
- payment_methods: array
- special_reference: string
- extras: object
- confirmed: boolean
- status: string
- created: string
- card_detail: string
- object: string

**Base URL:** `https://accept.paymob.com/`

**Endpoint:** `POST /v1/intention/`

---

## Unified Intention API Experience

The Intention API allows you to create payment requests with all required parameters.

**Steps:**
1. Add "secret key" in authorization header
2. Send POST request to `https://accept.paymob.com/v1/intention/` with request parameters
3. Call URL in browser: `https://accept.paymob.com/unifiedcheckout/?publicKey=<public_key>&clientSecret=<client_secret>`
4. Enter card details (use test credentials)
5. View approval message for successful transactions

**Important Notes:**
- Use configured integration ID while creating intention
- Maintain separate integration IDs for test and live environments
- Customize intention API setup with notification_url and redirection_url parameters
- Item array is not mandatory, but if provided, both items_name and items_amount fields are required
- Redirection_url works only with Card payment method

**Webhook Responses:**
- Callback Response
- Token response (if user selects "Save Card" option)

**Parameters:**
- Total Amount (amount): Required - amount in cents
- Payment Methods (payment_methods): Required - configured Integration ID
- Currency (currency): Required - region-specific currency
- Items Description (description): Optional - max 255 characters
- Items Quantity (quantity): Optional
- Items Amount (amount): Required - amount in cents
- Items Name (name): Required - max 50 characters
- First Name (first_name): Required - max 50 characters
- Last Name (last_name): Required - max 50 characters
- Email (email): Required
- Country (country): Optional
- Phone Number (phone_number): Required
- Extras (extras): Optional - additional parameters
- Expiration (expiration): Optional - intention duration in seconds
- Special Reference (special_reference): Optional - unique identifier
- Notification URL (notification_url): Optional - receives transaction-processed callback
- Redirection URL (redirection_url): Optional - customer redirect URL after transaction

**Response:**
- 201: Payment Intention created successfully
- Attributes: payment_keys, intention_order_id, id, intention_detail, client_secret, payment_methods, special_reference, extras, confirmed, status, created, card_detail, card_tokens, object

---

## Pixel (Native Payment Experience)

### Introduction
Paymob Pixel's JS SDK allows merchants to create and customize checkout experience using pre-built UI components.

**Features:**
- Native Payment Experience for Websites and Stores
- Fully Customizable UI Components
- Enhanced Conversion Rates
- Secure Payment Processing
- Support for Quick Payment Options (Apple Pay)

### Pre-Requisites
**Mandatory:**
- Paymob account
- Integrated Intention API

**Optional:**
- Integrated Update Intention Payment API

**Version:** Get latest Pixel package from provided link

**Supported Payment Methods:** Card Payments and Apple Pay in Egypt

### How does Pixel Work?
1. Use Create Intention Payment API to create payment order
2. Pass client_secret from API response into clientSecret parameter of Pixel JS
3. Call Update Intention Payment API if order details change
4. Trigger updateIntentionData event to reflect updated order details

### Implementation
Include script tag in HTML file:
```html
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/paymob-pixel@latest/styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/paymob-pixel@latest/main.css">
<script src="https://cdn.jsdelivr.net/npm/paymob-pixel@latest/main.js" type="module"></script>
```

### Parameters
1. **publicKey:** Accessed from Merchant's Dashboard → Settings → Account Info
2. **clientSecret:** Received from Intention API response
3. **paymentMethods:** ['card', 'google-pay', 'apple-pay']
4. **elementId:** ID of HTML element where checkout pixel will be embedded
5. **disablePay:** Boolean to disable Paymob's Pay Button for Card Payment
6. **updateIntentionData:** Function to update intention data within SDK
7. **showSaveCard:** Boolean to show option to save card details
8. **forceSaveCard:** Boolean to automatically save card details without consent
9. **beforePaymentComplete:** Custom logic before payment processing
10. **afterPaymentComplete:** Custom logic after payment processing (Apple Pay only)
11. **onPaymentCancel:** Custom logic when user cancels Apple Pay payment
12. **cardValidationChanged:** Function triggered on card validation state change
13. **customStyle:** Object to customize checkout pixel appearance

### Sample Script
```html
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<title>Pixel</title>
<base href="/">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/paymob-pixel@latest/styles.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/paymob-pixel@latest/main.css">
</head>
<style>
    .content {
      display: flex;
      flex-direction: column;
      gap: 1rem;
      justify-content: center;
      align-items: center;
      margin-top: 2rem;
    }
    #paymob-elements {
      width: 30%;
    }
    #payFromOutsideButton {
      padding: 0.5rem;
      background-color: blue;
      color: white;
      border-radius: 0.2rem;
    }
</style>
</head>
<body>
<position>
<div class="header" style="padding: 1rem; background-color: rgb(233, 255, 207);">
      Company Paymob
</div>
<div class="wrapper">
<div class="content">
<div id="paymob-elements"></div>
 <button id="payFromOutsideButton">Pay From Outside Button</button>
</div>
</div>
<div class="footer">
</div>
<script src="https://cdn.jsdelivr.net/npm/paymob-pixel@latest/main.js" type="module"></script>
<script src="https://unpkg.com/paymob-pixel/main.js" type="module"></script>
<script>
    // Merchant Pay button logic
      const button = document.getElementById('payFromOutsideButton');
      button?.addEventListener('click', function () {
        // Calling pay request
        const event = new Event('payFromOutside');
        window.dispatchEvent(event);
      });
        onload = (event) => {
          new Pixel({
            publicKey: 'egy_pk_live_XXXX',
            clientSecret: 'egy_csk_live_XXXX',
            paymentMethods: [ 'card','google-pay','apple-pay'],
            elementId: 'paymob-elements',
            disablePay: false,
            showSaveCard :true,
            forceSaveCard : true,
            beforePaymentComplete: async (paymentMethod) => {
                        console.log(paymentMethod);
                        console.log('Before payment start');
                        console.log('We are waiting for 5 seconds');
                        await new Promise(res => setTimeout(() => res(''),5000))
                        console.log('Before payment end');
                        return true
            },
            afterPaymentComplete: async (response) => {
               console.log('After Bannas payment');
               console.log(response)
              },
             onPaymentCancel: () => {
                console.log('Payment has been canceled');
              },
              cardValidationChanged: (isValid) => {
              console.log("Is valid ? ", isValid)
              },              
               customStyle: {
                 Font_Family: 'Gotham',
                 Font_Size_Label: '16',
                 Font_Size_Input_Fields: '16',
                 Font_Size_Payment_Button: '14',
                 Font_Weight_Label: 400,
                 Font_Weight_Input_Fields: 200,
                 Font_Weight_Payment_Button: 600,
                 Color_Container: '#FFF',
                 Color_Border_Input_Fields: '#D0D5DD',
                 Color_Border_Payment_Button: '#A1B8FF',
                 Radius_Border: '8',
                 Color_Disabled: '#A1B8FF',
                 Color_Error: '#CC1142',
                 Color_Primary: '#144DFF',
                 Color_Input_Fields: '#FFF',
                 Text_Color_For_Label: '#000',
                 Text_Color_For_Payment_Button: '#FFF',
                 Text_Color_For_Input_Fields: '#000',
                 Color_For_Text_Placeholder: '#667085',
                 Width_of_Container: '100%',
                 Vertical_Padding: '40',
                 Vertical_Spacing_between_components: '18',
                 Container_Padding: '0'
            }, 
          });
        };
</script>
<script src="https://pay.google.com/gp/p/js/pay.js"></script>
</body>
</html>
```

---

## Split Payment Feature

Allows merchants to split payment cost between merchant and sub-accounts.

**How it Works:**
1. Customer pays charge
2. Paymob collects fee and adds remainder to platform account's pending balance
3. Gateway transfers amounts to main-connected account and sub-connected account

**How to Enable:**
1. Create Paymob account
2. Set up payment methods with Account Manager
3. Use Intention API with 'Split MID' field: `"split_amounts": [{"mid": "B" (Child MID), "amount_cents": "X"}]`

**Note:** Child split percentage should not exceed 97% to avoid negative balance.

**Checking Transaction Breakdown:**
- Generate AUTH token (valid 60 minutes)
- Use Order ID, Transaction ID, or Merchant Order ID for transaction inquiry

**Refund/Void Transaction:** Refer to provided link

**Dashboard Visibility:** Parent Transaction ID displayed under Other References

---

## E-Commerce Plugins

### Shopify
**Steps:**
1. Log in to Shopify Dashboard
2. Select Settings → Payments
3. Set Payment Capture to Automatic
4. Click "Add Payment Methods"
5. Search for "Paymob" and click icon
6. Click "Connect"
7. Choose store country
8. Install app
9. Log in with Paymob credentials
10. Activate Paymob and enable test mode if needed
11. Select payment methods

**Refunds:** Must be processed through Shopify store dashboard

### WordPress (WooCommerce)
**Features:**
- Unified Checkout
- Pixel Experience
- Saved Cards
- Split Payment Methods
- Support Subscriptions

**Sections:**
1. Prerequisites Before Installation
2. Install from WordPress Marketplace
3. Install from WooCommerce Marketplace
4. Main Configuration
5. Payment Integrations
6. Card Embedded Settings
7. WooCommerce Subscription

**Prerequisites:**
1. Register and upload business documents
2. Document verification (up to 3 days)
3. Email confirmation after verification
4. Notification when live payment integrations are set up

**Installation from WordPress Marketplace:**
1. Navigate to Plugins → Add New Plugin
2. Search for "Paymob for WooCommerce"
3. Click "Install Now"
4. Click "Activate"
5. Access via Plugins → Installed Plugin Section → Paymob Settings

**Installation from WooCommerce Marketplace:**
1. Place order for Paymob Payments
2. Download .zip file from My subscriptions
3. Navigate to Plugins → Add New Plugin
4. Upload Plugin → select downloaded file
5. Install and activate

**Main Configuration:**
- Connect Paymob Account or Manual Setup
- Enter API Key, Public Key, and Secret Key
- Find keys in Paymob Dashboard → Settings → Account Info
- Switch between Live and Test modes

**Payment Integrations:**
- Paymob Pixel (Card Embedded Settings)
- Hosted Checkout (List of Payment Methods)
- Paymob Main App (disabled by default)

**Card Embedded Settings:**
- Enabled by default
- Select Integration ID for card payments
- Contact support for Apple Pay & Google Pay
- Customize title, show/force save card, CSS customization

**WooCommerce Subscription:**
- Supports WooCommerce Subscription Plugin
- Enable in Subscription section
- Select 3DS and MOTO Integration IDs
- Create subscription products with price and frequency
- Payment flow: Credit/Debit Card only → Hosted Checkout

### OpenCart
**Supported Versions:** 4.x.x, 3.x, 2.3

**Installation:**
- Download plugin file
- Log in to admin panel → Extension → Installer
- Upload plugin file
- Install from Extensions → Payments

**Merchant Configuration:**
- Get keys from Paymob Dashboard → Settings
- Paste keys in OpenCart setting page
- Validate API key
- Copy integration callback URL to Paymob account

### Magento
**Marketplace:** Magento Adobe marketplace

**Installation:**
```shell
composer require paymob/magento-payment
php -f bin/magento module:enable --clear-static-content Paymob_Payment
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy -f
php bin/magento cache:clean
php bin/magento cache:flush
```

**Merchant Configuration:**
- Get keys from Paymob Dashboard → Settings
- In Magento Admin: Stores → Configuration → Sales → Payment Methods
- Paste keys, add integration IDs separated by comma
- Copy callback URL to Paymob account

### Odoo
**Supported Versions:** 18, 17, 16, 15

### WHMCS
**Marketplace:** WHMCS marketplace

**Installation:**
1. Download plugin
2. Extract to WHMCS project path
3. Login to admin panel → Setup → Apps & integrations → Browse → Payments
4. Search for Paymob Payment → Manage

**Admin Configuration:**
- Get keys from Paymob Dashboard → Settings
- In WHMCS admin: Addons → Apps & integrations → Payments Apps
- Configure module in Manage Existing Gateways tab
- Copy callback URL to Paymob account

### Cs-Cart
**Marketplace:** Cs-Cart marketplace

**Installation:**
1. Download addon
2. Login to admin panel → Add-ons → Manage add-ons
3. Click tools/setting icon → Manual installation → Local
4. Select .zip file → Upload & Install

**Admin Configuration:**
- Administration → Payment Methods → Add
- Name payment method, choose Paymob in Processor
- Configure with keys from Paymob Dashboard
- Validate API key
- Copy callback URL to Paymob account

### PrestaShop
**Supported Versions:** 1.6, 1.7, 8

**Installation:**
1. Download module
2. Login to admin panel → Modules → Module Manager → Upload a module
3. Select .zip file

**Admin Configuration:**
- Payments → payment methods → Configure
- Name payment method
- Get keys from Paymob Dashboard → Settings
- Paste keys in setting page
- Copy callback URL to Paymob account

### Joomla
**Plugin:** Paymob VirtueMart Plugin

**Installation:**
1. Download module
2. Login to admin panel → Extensions → Manage → Install
3. Browse for file → select plugin ZIP
4. VirtueMart → Payment Methods → New

**Merchant Configuration:**
- Get keys from Paymob Dashboard → Settings
- Paste keys in Virtuemart Configuration page
- Validate API key
- Copy callback URL to Paymob account

### ZenCart
**Installation:**
1. Download module
2. Extract to ZenCart project path
3. Login to admin panel → Modules → Payment
4. Search for Paymob Payment → Install

**Admin Configuration:**
- Get keys from Paymob Dashboard → Settings
- Paste keys in configuration page
- Copy callback URL to Paymob account

### OsCommerce
**Installation:**
1. Download module
2. Extract to Oscommerce project path
3. Login to admin panel → Modules → Payment → Online
4. Show not installed → Search for Paymob Payment → Install

**Admin Configuration:**
- Get keys from Paymob Dashboard → Settings
- Paste keys in Settings page
- Copy callback URL to Paymob account

### Laravel-Bagisto
**Bagisto 1.x:**
```shell
composer require paymob/laravel-bagisto1.x
php artisan migrate
php artisan optimize
```
Add `'paymob/callback'` to `$except` array in `app/Http/Middleware/VerifyCsrfToken.php`
```shell
php artisan config:cache
```

**Bagisto 2.x:**
```shell
composer require paymob/laravel-bagisto2.x
php artisan vendor:publish --force --tag=paymob
php artisan migrate
php artisan optimize
```
Add `'paymob/callback'` to `$except` array in `app/Http/Middleware/VerifyCsrfToken.php`
```shell
php artisan config:cache
```

**Merchant Configuration:**
- Get keys from Paymob Dashboard → Settings
- In Bagisto Admin: Configuration → sales → payment methods
- Paste keys, add integration IDs separated by comma
- Copy callback URL to Paymob account

### Drupal
**Marketplace:** Drupal marketplace

**Installation:**
```shell
composer require paymob_drupal/commerce_paymob
```
In admin panel: Extend → search for Paymob module → Install

**Merchant Configuration:**
- Get keys from Paymob Dashboard → Settings
- In Drupal commerce Admin: Commerce → Configuration → Payments → Payment Gateways
- Paste keys, add integration IDs separated by comma
- Copy callback URL to Paymob account

---

## Mobile SDKs
*Details to be provided*

---

## Subscriptions
**Overview:** Comprehensive subscription module for various billing cycles.

**Integration Requirements:**
- Online 3DS Integration ID: For creating subscriptions
- MOTO Integration ID: For creating subscription plan

**3DS Verification:** Contact support to create 3DS verification integration ID for card user verification without deduction

**API:** Download Subscription Module APIs

---

## Payment Types
1. **3D Secure (3DS):** Security protocol with additional authentication layer
2. **Auth and Capture:** Two-step process - authorize first, capture later (within 14 days)
3. **Card Verification:** Authenticate card transaction without deducting amount
4. **Pay with Saved Card Token:** Store card details securely for future transactions

---

## Payment Links
*Details to be provided*

---

## Payment Actions
- Refund Transaction through Dashboard
- Refund Transaction through API
- Void Transaction through Dashboard
- Void Transaction through API
- Auth/Capture Transaction through Dashboard
- Auth/Capture Transaction through API

---

## Payment Methods
- Card Payments
- Digital Wallets
- Apple Pay
- ValU
- Bank Installments
- Souhoola V3
- Aman V3
- Forsa
- Premium
- Contact
- HALAN
- SYMPL
- Kiosk
- InstaPay (Coming Soon)

---

## Test Credentials
**Mastercard:**
- Card number: 5123456789012346
- Cardholder Name: Test Account
- Expiry Month: 12
- Expiry Year: 25
- CVV: 123

**Wallet:**
- Wallet Number: 01010101010
- MPin Code: 123456
- OTP: 123456

**MasterCard For Simulation:**
- Card number: 5123450000000008
- Cardholder Name: TEST CARD
- Expiry Month: 01
- Expiry Year: 39
- CVV: 123

**VISA Card for Simulation:**
- Card number: 4111111111111111
- Cardholder Name: Test Account
- Expiry Month: 12
- Expiry Year: 25
- CVV: 123

---

## Transaction Inquiry API
Retrieve transaction details using:
- Order ID
- Transaction ID
- Merchant Order ID (Special Reference Number)

---

## Manage Callback
Covers:
- Transaction processed callbacks
- Transaction response callbacks
- Calculating HMAC for processed callbacks, redirection callbacks, and saved card token objects

---

## Error Codes
Risk error codes and Acquirer response code descriptions for decline reasons.

---

## Bills Portal API
*Details to be provided*

---

## Payouts
**Table Of Contents:**
- Definitions and Acronyms
- Generate and Refresh Token API Endpoint
- Instant Cashin API Endpoint
- Cancel Aman Transaction API Endpoint
- Bulk Transaction Inquiry API Endpoint
- User Budget Inquiry API Endpoint
- CallBack Url
- Response Codes
