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
- `ORIGINAL`: Premium option

## Album Hierarchy Integration

The shop integration works with Lychee's Nested Set model for albums:

- Album-level purchasables can be set to apply to all sub-albums
- The system checks the album hierarchy when determining if a photo is purchasable
- Pricing is resolved based on the closest ancestor in the hierarchy

---

*Last updated: September 7, 2023*

