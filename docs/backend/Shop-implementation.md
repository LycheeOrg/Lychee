# Shop Integration Implementation Notes

This document provides detailed implementation notes for the Shop Integration in Lychee.

## Database Structure

### Purchasable
The `purchasable` table defines which photos or albums are available for purchase:

- `id`: Primary key
- `album_id`: FK to albums table (nullable if photo-specific)
- `photo_id`: FK to photos table (nullable if album-level)
- `description`: Public description shown to customers
- `owner_notes`: Private notes for the owner
- `is_active`: Whether this item is currently purchasable

### Purchasable Prices
The `purchasable_prices` table handles the combination of size variants and license types:

- `id`: Primary key
- `purchasable_id`: FK to purchasables table
- `size_variant`: Size variant type (MEDIUM, MEDIUM2x, FULL, ORIGINAL)
- `license_type`: License type (PERSONAL, COMMERCIAL, EXTENDED)
- `price_cents`: Integer price in smallest currency unit (cents)

### Order
The `orders` table represents a complete purchase transaction:

- `id`: Primary key
- `transaction_id`: Unique transaction identifier
- `provider`: Payment provider name (OmnipayProviderType enum)
- `user_id`: FK to users table (nullable)
- `email`: Customer email
- `status`: Order status (PaymentStatusType enum: PENDING, COMPLETED, etc.)
- `amount_cents`: Total order amount in smallest currency unit
- `paid_at`: Payment timestamp (nullable)
- `comment`: Order notes (nullable)

### Order Item
The `order_items` table represents individual items within an order:

- `id`: Primary key
- `order_id`: FK to orders table
- `purchasable_id`: FK to purchasables table
- `album_id`: FK to albums table (nullable)
- `photo_id`: FK to photos table (nullable)
- `title`: Item title at time of purchase
- `license_type`: PurchasableLicenseType enum (PERSONAL, COMMERCIAL, EXTENDED)
- `price_cents`: Price in smallest currency unit
- `size_variant_type`: PurchasableSizeVariantType enum (MEDIUM, MEDIUM2x, FULL, ORIGINAL)
- `item_notes`: Item-specific notes (nullable)

## Models

### Purchasable
The `Purchasable` model defines whether a photo or album is available for purchase and its pricing options.

Key methods:
- `getPriceFor(PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type)`: Get price for specific size and license combination as a Money object or null if not available
- `setPriceFor(PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type, Money $money)`: Set price for a specific combination
- `isAlbumLevel()`: Check if this is an album-level purchasable

Relationships:
- `album()`: The album this purchasable item belongs to
- `photo()`: The photo this purchasable item belongs to
- `prices()`: The prices for this purchasable item (HasMany relationship to PurchasablePrice)

### PurchasablePrice
The `PurchasablePrice` model represents a price for a specific size variant and license type combination.

Key attributes:
- `size_variant`: PurchasableSizeVariantType enum
- `license_type`: PurchasableLicenseType enum
- `price_cents`: Money object (using MoneyCast)

Relationships:
- `purchasable()`: The purchasable item this price belongs to

### Order
The `Order` model represents a complete purchase transaction.

Key methods:
- `calculateTotal()`: Calculate the total amount for this order as a Money object
- `updateTotal()`: Update the total amount based on the order items and save
- `markAsPaid(string $transaction_id)`: Mark the order as paid with transaction ID
- `findByTransactionId(string $transaction_id)`: Static method to find an order by transaction ID
- `getOrdersForUser(User $user)`: Static method to get all orders for a user

Relationships:
- `user()`: The user who placed this order (BelongsTo relationship)
- `items()`: The items in this order (HasMany relationship to OrderItem)

Key attributes:
- `status`: PaymentStatusType enum (PENDING, COMPLETED, etc.)
- `provider`: OmnipayProviderType enum
- `amount_cents`: Money object (using MoneyCast)
- `paid_at`: DateTime (nullable)

### OrderItem
The `OrderItem` model represents an individual item within an order.

Key attributes:
- `license_type`: PurchasableLicenseType enum
- `size_variant_type`: PurchasableSizeVariantType enum
- `price_cents`: Money object (using MoneyCast)
- `title`: Title of the photo/album at time of purchase

Relationships:
- `order()`: The order this item belongs to (BelongsTo relationship)
- `purchasable()`: The purchasable definition this item was based on (BelongsTo relationship)
- `photo()`: The photo this item refers to (BelongsTo relationship)
- `album()`: The album this item refers to (BelongsTo relationship)

## Services and Actions

### PurchasableService
Handles the logic for determining which items are purchasable and their pricing.

Key methods:
- `getEffectivePurchasableForPhoto(Photo $photo, string $album_id)`: Find the applicable purchasable for a photo
- `getPhotoOptions(Photo $photo, string $album_id)`: Get all pricing options for a photo as PurchasableOption[] array
- `getPurchasablePhotosInAlbum(Album $album, bool $include_subalbums = false)`: Get all purchasable photos in an album
- `createPurchasableForPhoto(Photo $photo, string $album_id, array $prices, ?string $description, ?string $owner_notes)`: Make a photo purchasable
- `createPurchasableForAlbum(Album $album, array $prices, bool $applies_to_subalbums, ?string $description, ?string $owner_notes)`: Make an album purchasable
- `updatePrices(Purchasable $purchasable, array $prices)`: Update the prices for a purchasable item

### BasketService
Handles the management of baskets (pending orders) that users can add items to.

Key methods:
- `getOrCreateBasket(?Order $basket, ?User $user = null)`: Get an existing basket or create a new one
- `addPhotoToBasket(Order $basket, Photo $photo, string $album_id, PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type, ?string $notes = null)`: Add a photo to a basket
- `addAlbumToBasket(Order $basket, Album $album, PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type, ?string $notes = null, bool $include_subalbums = false)`: Add all purchasable photos in an album to a basket
- `removeItemFromBasket(Order $basket, int $item_id)`: Remove an item from a basket
- `deleteBasket(Order $basket)`: Delete an entire basket
- `ensurePendingStatus(Order $basket)`: Guard method to ensure the basket is in pending state

### OrderService
Handles the logic for creating and processing orders.

Key methods:
- `createOrder(?User $user = null, ?string $comment = null)`: Create a new order
- `addPhotoToOrder(Order $order, Photo $photo, string $album_id, PurchasableSizeVariantType $size_variant, PurchasableLicenseType $license_type, ?string $notes = null)`: Add a photo to an order
- `processPayment(Order $order, string $provider, string $transaction_id)`: Process a payment for an order

Dependencies:
- `MoneyService`: For handling monetary values and currency operations
- `PurchasableService`: For determining purchasable items and pricing

## Pricing Hierarchy

The shop integration uses a hierarchical approach to determine if a photo is purchasable and what pricing should be applied:

1. Check if there's a direct purchasable entry for the specific photo
2. If not found, check the photo's direct parent album
3. If no pricing is found, the photo is not purchasable

## Exception Handling

The shop integration uses a structured approach to exception handling:

1. Domain-specific exceptions in the `App\Exceptions\Shop` namespace:
   - `OrderIsNotPendingException`: Thrown when attempting to modify an order that is not in pending state
   - `BasketDeletionFailedException`: Thrown when a basket deletion operation fails

2. Guard methods in services:
   - `BasketService::ensurePendingStatus()`: Ensures that a basket is in pending state before allowing modifications

3. Exception integration:
   - All exceptions extend `BaseLycheeException` for consistent error handling
   - HTTP status codes are set appropriately (400 for client errors, 500 for server errors)
   - Descriptive messages to help with debugging and user feedback

## Money Handling

The shop integration uses the `moneyphp/money` library to handle monetary values with precision:

1. All monetary values are stored as integers representing the smallest currency unit (cents)
2. The `MoneyCast` class converts between database integer values and Money objects
3. The `MoneyService` class provides helper methods for money operations:
   - `getDefaultCurrencyCode()`: Get the default currency from config
   - `createFromCents(int $cents, ?string $currency_code = null)`: Create Money object from cents
   - `createFromDecimal(float $amount, ?string $currency_code = null)`: Create Money object from decimal amount
   - `format(Money $money)`: Format Money object to human-readable string with currency symbol
   - `toDecimal(Money $money)`: Convert Money object to decimal string

## Data Transfer Objects

The shop implementation uses DTOs to represent data structures:

1. `PurchasableOption`: Read-only DTO for available purchase options
   - `size_variant`: PurchasableSizeVariantType enum
   - `license_type`: PurchasableLicenseType enum
   - `price`: Money object
   - `purchasable_id`: ID of the associated purchasable

2. `PurchasableOptionCreate`: Read-only DTO for creating purchase options
   - `size_variant`: PurchasableSizeVariantType enum
   - `license_type`: PurchasableLicenseType enum
   - `price`: Money object

## Request/Response Pattern

### Request Classes
The shop implementation uses a robust request pattern for handling basket operations:

1. `BasketRequest`: Abstract base class for all basket-related requests
   - Implements `HasBasket` interface
   - Uses `HasBasketTrait` for common functionality
   - Automatically retrieves the current basket from the session (or user id if logged in and session has none)

2. Specialized Request Classes:
   - `AddPhotoToBasketRequest`: For adding photos to a basket
   - `AddAlbumToBasketRequest`: For adding albums to a basket
   - `DeleteItemRequest`: For removing items from a basket
   - `DeleteBasketRequest`: For deleting an entire basket
   - `GetBasketRequest`: For retrieving the current basket

### Resources
The shop implementation uses Spatie Data resources for API responses:

1. `OrderResource`: Resource for Order models with all related items
2. `OrderItemResource`: Resource for individual order items

## License Types

The shop supports three types of licenses (defined in PurchasableLicenseType enum):

- `PERSONAL`: For personal use only
- `COMMERCIAL`: For commercial use
- `EXTENDED`: For extended commercial use with fewer restrictions

## Size Variants

The shop offers multiple size variants:

- `MEDIUM`: Lower-resolution option
- `MEDIUM2x`: Medium resolution option
- `ORIGINAL`: Original photo as uploaded to Lychee
- `FULL`: The largest size that can be exported by the photographer (requires extra export)

## Album Hierarchy Integration

The shop integration works with Lychee's Nested Set model for albums:

- Album-level purchasables can be set to apply to all sub-albums
- The system checks the album hierarchy when determining if a photo is purchasable
- Pricing is resolved based on the closest ancestor in the hierarchy


## Life Cycle of a Shopping Experience

The following outlines the typical flow for a user shopping in the Lychee webshop, including the main API requests and endpoints:

1. **View Shop Items**
   - `GET /Shop` — Get album catalog with purchasable items
   - `GET /Shop/Basket` — Retrieve current basket contents

2. **Add Items to Basket**
   - `POST /Shop/Basket/Photo` — Add a photo to the basket
   - `POST /Shop/Basket/Album` — Add an album to the basket

3. **Remove Items from Basket**
   - `DELETE /Shop/Basket/item` — Remove an item from the basket
   - `DELETE /Shop/Basket` — Delete the entire basket

4. **Checkout**
   - `POST /Shop/Checkout/Create-session` — Create a checkout session (select provider, enter email)
   - `POST /Shop/Checkout/Process` — Process payment (send payment data)
   - `POST /Shop/Checkout/Finalize/{provider}/{transaction_id}` — Finalize payment after provider callback
   - `GET /Shop/Checkout/Cancel/{provider}/{transaction_id}` — Cancel the payment session

5. **Management (Admin only)**
   - `POST /Shop/Management/Purchasable/Photo` — Set photo as purchasable
   - `POST /Shop/Management/Purchasable/Album` — Set album as purchasable
   - `PUT /Shop/Management/Purchasable/Price` — Update purchasable prices
   - `DELETE /Shop/Management/Purchasables` — Delete purchasables

### Diagram: Shopping Experience Flow

```mermaid
graph TD
    A[GET /Shop - View Album Catalog] --> B[GET /Shop/Basket - Check Current Basket]
    B --> C[POST /Shop/Basket/Photo - Add Photo to Basket]
    B --> D[POST /Shop/Basket/Album - Add Album to Basket]
    C --> E[DELETE /Shop/Basket/item - Remove Item]
    D --> E
    E --> F[DELETE /Shop/Basket - Clear Basket]
    C --> G[POST /Shop/Checkout/Create-session]
    D --> G
    G --> H[POST /Shop/Checkout/Process - Process Payment]
    H --> I[POST /Shop/Checkout/Finalize/{provider}/{transaction_id}]
    H --> J[GET /Shop/Checkout/Cancel/{provider}/{transaction_id}]
    
    subgraph "Admin Management"
        K[POST /Shop/Management/Purchasable/Photo]
        L[POST /Shop/Management/Purchasable/Album]
        M[PUT /Shop/Management/Purchasable/Price]
        N[DELETE /Shop/Management/Purchasables]
    end
```

## Checkout Validation and Authorization

The checkout process implements multiple layers of validation to ensure security and data integrity at each critical step:

### 1. Create Session Validation (`CreateSessionRequest`)

**Authorization Requirements:**
- Order must exist (`$this->order !== null`)
- Order must be in a state that allows checkout (`$this->order->canCheckout()`)

**Field Validation:**
- `provider`: Must be a valid `OmnipayProviderType` enum value (DUMMY, STRIPE, PAYPAL_EXPRESS)
- `email`: Must be a valid email format when provided

**Order Checkout Eligibility (`Order::canCheckout()`):**
- Order status must allow checkout (`PaymentStatusType::canCheckout()`)
- Must have either:
  - A valid email address, OR
  - A user_id AND no FULL size variant items (FULL variants require email for delivery)

**Detailed Conditions:**
```php
public function canCheckout(): bool
{
    // 1. Status must allow checkout (typically PENDING)
    if (!$this->status->canCheckout()) {
        return false;
    }

    // 2. If email is provided, checkout is allowed
    if ($this->email !== null) {
        return true;
    }

    // 3. If no email, check for FULL size variants
    // FULL variants require email for delivery, so they block checkout
    if ($this->items()->where('size_variant_type', PurchasableSizeVariantType::FULL)->exists()) {
        return false;
    }

    // 4. Finally, must have a user_id if no email
    return $this->user_id !== null;
}
```

**Summary of canCheckout() conditions:**
- ✅ **Allowed when:** Status allows checkout AND (has email OR (has user_id AND no FULL variants))
- ❌ **Blocked when:** Status doesn't allow checkout OR (no email AND no user_id) OR (no email AND has FULL variants)

### 2. Process Payment Validation (`ProcessRequest`)

**Authorization Requirements:**
- Order must exist and be able to process payment (`$this->order->canProcessPayment()`)

**Field Validation:**
- `additional_data`: Optional array containing payment-specific data (card details, etc.)

**Payment Processing Eligibility (`Order::canProcessPayment()`):**
- Must pass all checkout eligibility checks
- Payment provider must be set (`$this->provider !== null`)

**Detailed Conditions:**
```php
public function canProcessPayment(): bool
{
    return $this->canCheckout() && $this->provider !== null;
}
```

**Summary of canProcessPayment() conditions:**
- ✅ **Allowed when:** All `canCheckout()` conditions are met AND provider is set
- ❌ **Blocked when:** Any `canCheckout()` condition fails OR provider is null

**Why These Conditions Exist:**

1. **Status Check**: Prevents processing completed/cancelled orders
2. **Email Requirement for FULL Variants**: FULL size variants need special export processing and must be delivered via email
3. **User/Email Requirement**: Ensures there's a way to contact the customer for order fulfillment
4. **Provider Requirement**: Cannot process payment without knowing which payment gateway to use

### 3. Finalize Payment Validation (`FinalizeRequest`)

**Authorization Requirements:**
- Order status must be `PROCESSING`
- Order provider must match the URL provider parameter
- Provider type must be valid

**URL Parameter Validation:**
- `transaction_id`: Must correspond to an existing order
- `provider`: Must be a valid `OmnipayProviderType` enum value

**Security Checks:**
- Transaction ID must exist in the database
- Provider in URL must match the order's configured provider
- Order must be in PROCESSING state (prevents replay attacks)

### Validation Flow Summary

```
Create Session → Check canCheckout()
     ↓
Process Payment → Check canProcessPayment() 
     ↓
Finalize → Verify PROCESSING status + provider match
```

**Key Security Features:**
- State-based authorization (orders progress through specific states)
- Provider consistency validation (prevents provider switching attacks)
- Email requirements for FULL size variants (ensures delivery capability)
- Transaction ID validation (prevents unauthorized access to orders)

---

*Last updated: September 13, 2025*

