# Shop Architecture

This document describes the architecture and implementation details for the shop integration in Lychee, allowing users to purchase photos.

---

## Overview

The shop integration provides a complete e-commerce solution for Lychee, enabling photographers to monetize their work by selling photos. The system supports single-photo purchases as well as basket-based shopping for multiple items.

The basket functionality allows users to add photos from different albums, select different size variants and licenses, and complete the purchase in a single transaction. The system maintains the basket state across sessions, allowing users to continue their shopping experience even after leaving the site.

**Basket vs Order Terminology**: In the implementation, a "basket" is simply an order with PENDING status. When payment is initiated, the status changes to PROCESSING, OFFLINE, or COMPLETED depending on the payment method. This means basket operations are really order operations with status validation.

## Architecture Components

### 1. Data Models

The shop integration introduces the following data models:

- **Purchasable**: A model that defines whether a photo or an album is purchasable, with columns for photo_id, album_id (required), and pricing for different photo sizes (MEDIUM, MEDIUM2X, FULL, ORIGINAL). 
  - When `photo_id` is null, it defines an entire album as purchasable with album-level pricing
  - When both `album_id` and `photo_id` are set, it defines photo-specific pricing that overrides album settings
  - A unique constraint on [`album_id`,` photo_id`] prevents duplicate entries
- **Order**: Represents a complete purchase transaction, including payment status, email address, and optionally user ID. An order with a PENDING status acts as a basket.
- **OrderItem**: Represents individual photos within an order, with columns for photo_id, price_cents, size_variant_type, size_variant_id (for fulfillment), and download_link (for custom delivery URLs).

### 2. Service Layer

The shop integration uses a service-oriented architecture:

- **BasketService**: Handles basket operations (adding/removing items, creating baskets, deleting baskets)
- **OrderService**: Handles order processing, payment, fulfillment, and maintenance
- **PurchasableService**: Manages purchasable settings and pricing
- **CheckoutService**: Manages checkout process and payment provider integration

### 3. Request/Response Pattern

The shop integration follows Laravel's request/response pattern with additional features:

- **Request Classes**: Custom request classes with validation and business logic
- **Resources**: Data transfer objects for API responses using Spatie Data
- **Exception Handling**: Domain-specific exceptions for error cases

## Access Controls

Only the Lychee instance owner can set their albums and photos as purchasable. Only the photos that are in their owned albums can be set as purchasable.

## Payment Processing

The shop integration uses a modular payment processing architecture that:

- **Supports multiple payment providers**: Mollie, PayPal (Express, Pro, Rest, ExpressInContext), Stripe, and Dummy (for testing)
- **Handles payment verification and confirmation**: Complete payment flow with return/cancel URLs
- **Manages secure download delivery after purchase**: Temporary signed URLs prevent unauthorized access
- **Maintains purchase records for accounting**: Full order and transaction history
- **Offline mode**: For manual payment processing when `webshop_offline` is enabled
- **Guest checkout support**: Configurable guest checkout with email requirements for FULL size variants

## Order Fulfillment

After payment is confirmed, the system automatically fulfills orders by linking purchased content to order items:

### Automatic Fulfillment

- **Auto-fulfillment**: Controlled by `webshop_auto_fulfill_enabled` configuration
- **Size Variant Linking**: When a customer purchases a photo, the system links the appropriate size variant:
  - MEDIUM, MEDIUM2X, ORIGINAL variants are linked automatically if they exist
  - Order items receive a `size_variant_id` pointing to the downloadable content
- **Download URLs**: Customers can download their purchases via the linked size variants

### Manual Fulfillment

- **FULL Size Variants**: Require photographer to manually export and provide the file
- **Custom Delivery**: Photographers can set a `download_link` on order items for external hosting
- **Manual Completion**: Administrators can manually mark orders as delivered via `markAsDelivered()`

### Order Status Lifecycle

1. **PENDING**: Basket/cart state, items can be added/removed
2. **PROCESSING**: Payment is being processed by payment provider
3. **OFFLINE**: Manual payment method selected, awaiting confirmation
4. **COMPLETED**: Payment received, fulfillment in progress (some items may be unfulfilled)
5. **CLOSED**: Payment received AND all items have been fulfilled/delivered
6. **CANCELLED**: Order cancelled before payment
7. **FAILED**: Payment processing failed
8. **REFUNDED**: Payment was refunded

### Maintenance Tasks

- **FulfillOrders Task**: Periodically processes COMPLETED and CLOSED orders to ensure all items are fulfilled
- **FlushOldOrders Task**: Removes abandoned guest orders (no user_id) older than 2 weeks with PENDING status or no items

## Size Variant Options

The system supports multiple size variant options for purchase:

- **MEDIUM**: Lower-priced option for digital/web use
- **MEDIUM2x**: Higher resolution medium option for better quality
- **FULL**: Medium-priced option providing the largest size available on Lychee
- **ORIGINAL**: Premium option providing the original file directly from the photographer (optional, requires the photographer to export the photo)

### Pricing Hierarchy

Pricing for each size variant follows a hierarchical determination process:

1. First, check if there's a Purchasable entry for the specific photo (photo_id set)
2. If not found, check if there's a Purchasable entry for the album (album_id set, photo_id null)
3. If neither is found, use the global configuration pricing
4. A photo is only purchasable if it either has a specific Purchasable entry or belongs to an album with a Purchasable entry

## Configuration Options

The system offers several configuration options:

### Pricing Hierarchy

- **Global Configuration**: Set default prices for all photo sizes (MEDIUM, FULL, ORIGINAL)
- **Album-level Pricing**: Override global pricing at the album level via Purchasable entries with album_id set and photo_id null
- **Photo-specific Pricing**: Override album pricing for specific photos via Purchasable entries with both album_id and photo_id set

### Additional Settings

- **Currency Settings**: Configure currency display and processing
- **Tax Handling**: Options for tax calculation and display
- **Payment Gateways**: Configure available payment methods via environment variables in the .env file

## Security Considerations

- **Payment Security**: Offloading payment processing to trusted third-party providers (Stripe/PayPal) to handle sensitive payment data
- **Download Protection**: Prevention of unauthorized access to purchased photos via secure & temporary links
- **Receipt Verification**: Secure verification of purchase records
- **Payment Webhooks**: Secure handling of payment confirmation webhooks from payment processors

## UI Components

The frontend implementation consists of several key components:

- **Basket Management** (`BasketList.vue`): Complete basket interface with item management
- **Checkout Flow** (`CheckoutPage.vue`): Multi-step checkout process with payment integration
- **Order Management** (`OrderList.vue`): Admin interface for viewing all orders
- **Purchasables Management** (`PurchasablesList.vue`): Admin interface for managing purchasable items
- **Purchase Actions** (`ThumbBuyMe.vue`): Photo thumbnail overlay for adding to basket
- **Order Components**: Order summary, status indicators, and info sections
- **Integration Components**: Mollie and Stripe payment components with proper theming

## State Management

### Frontend State

The shop functionality uses Pinia stores to manage:

- **OrderManagementStore**: Shopping basket contents, order state, and basket operations
- **CatalogStore**: Purchasable items and catalog data
- **UserStore**: User authentication state for checkout validation

### Backend Session Management

The basket functionality uses Laravel's session management:

- Basket ID is stored in the session
- When a user logs in, the session basket is associated with their account
- Requests include a trait to automatically fetch the current basket
- Controller methods work with the session to maintain basket state
- Session is cleared when an order is completed or manually deleted

## Integration Points

The shop functionality integrates with Lychee at multiple levels:

- **Album/Photo Headers**: Basket icon with item count in headers (`AlbumHeader.vue`, `AlbumsHeader.vue`)
- **Photo Thumbnails**: "Add to Basket" overlay on purchasable photos (`ThumbBuyMe.vue`)
- **Navigation**: Dedicated routes for basket, checkout, orders, and purchasables management
- **Admin Interface**: Complete shop management through admin controllers and views
- **Session Management**: Automatic basket persistence across user sessions
- **Permission System**: Integration with Lychee's permission system for purchasable content

## User Flows

### Photo Purchase Flow

1. User browses photos within purchasable albums
2. User adds photo to basket via context menu, detail view, or album view
3. User selects desired size (MEDIUM, FULL, or ORIGINAL if available) and license
4. User can continue browsing and add more photos to the basket
5. User can review and modify basket contents (remove items)
6. User proceeds to checkout when ready to complete purchase
7. User completes payment process
8. System records purchase and grants download access

### Basket Management Flow

1. User's basket is created automatically when they first view it or add items
2. User adds photos or entire albums to the basket
3. User can view the basket contents at any time
4. User can remove items from the basket
5. User can delete the entire basket
6. The basket is maintained across sessions via a session cookie
7. If the user logs in, they will see any existing basket associated with their account

### Photographer Flow

1. Photographer uploads photos to Lychee
2. Photographer accesses the Purchasables management interface (`/purchasables`)
3. Photographer sets individual photos or entire albums as purchasable
4. Photographer configures pricing for different size variants and license types
5. Photographer can enable/disable purchasable items and add descriptions
6. Photographer views order history and manages completed sales via Orders interface (`/orders`)

## License Management

Photos can be sold with different licensing terms:

- **Personal Use**: For non-commercial use by the purchaser
- **Commercial License**: For business and promotional use
- **Extended License**: For broader usage rights

## Related Documentation

- [Shop Implementation](../3-reference/shop-implementation.md) - Detailed reference for models, services, and API endpoints
- [Backend Architecture](backend-architecture.md) - Overall backend structure
- [Database Schema](../3-reference/database-schema.md) - Data model relationships

---

*Last updated: December 22, 2025*
