# Shop Integration for Lychee

This document describes the architecture and implementation details for the shop integration in Lychee, allowing users to purchase photos.

## Overview

The shop integration provides a complete e-commerce solution for Lychee, enabling photographers to monetize their work by selling photos. The system supports single-photo purchases as well as basket-based shopping for multiple items. 

The basket functionality allows users to add photos from different albums, select different size variants and licenses, and complete the purchase in a single transaction. The system maintains the basket state across sessions, allowing users to continue their shopping experience even after leaving the site.

## Architecture Components

### 1. Data Models

The shop integration introduces the following data models:

- **Purchasable**: A model that defines whether a photo or an album is purchasable, with columns for photo_id, album_id, and pricing for different photo sizes (MEDIUM, FULL, ORIGINAL). 
  - When album_id is set and photo_id is null, it defines an entire album as purchasable with album-level pricing
  - When both album_id and photo_id are set, it defines photo-specific pricing that overrides album settings
- **Order**: Represents a complete purchase transaction, including payment status, email address, and optionally user ID. An order with a PENDING status acts as a basket.
- **OrderItem**: Represents individual photos within an order, with columns for photo_id, price_cents, and size_variant_type to specify which size variant to provide.

### 2. Service Layer

The shop integration uses a service-oriented architecture:

- **BasketService**: Handles basket operations (adding/removing items, creating baskets, etc.)
- **OrderService**: Handles order processing and payment
- **PurchasableService**: Manages purchasable settings and pricing

### 3. Request/Response Pattern

The shop integration follows Laravel's request/response pattern with additional features:

- **Request Classes**: Custom request classes with validation and business logic
- **Resources**: Data transfer objects for API responses using Spatie Data
- **Exception Handling**: Domain-specific exceptions for error cases

## Access Controls

Only the lychee instance owner can set their album and photos as purchasable. Only the photos that are in their owned album can be set as purchasable.

## Payment Processing

The shop integration uses a modular payment processing architecture that:

- Supports multiple payment providers.
- Handles payment verification and confirmation.
- Manages secure download delivery after purchase.
- Maintains purchase records for accounting.

## Size Variant Options

The system supports multiple size variant options for purchase:

- **MEDIUM**: Lower-priced option for digital/web use.
- **FULL**: Medium-priced option providing the largest size available on Lychee.
- **ORIGINAL**: Premium option providing the original file directly from the photographer (optional, requires the photographer to export the photo).

Pricing for each size variant follows a hierarchical determination process:
1. First, check if there's a Purchasable entry for the specific photo (photo_id set)
2. If not found, check if there's a Purchasable entry for the album (album_id set, photo_id null)
3. If neither is found, use the global configuration pricing
4. A photo is only purchasable if it either has a specific Purchasable entry or belongs to an album with a Purchasable entry

## Configuration Options

The system offers several configuration options:

- **Pricing Hierarchy**: 
  - **Global Configuration**: Set default prices for all photo sizes (MEDIUM, FULL, ORIGINAL)
  - **Album-level Pricing**: Override global pricing at the album level via Purchasable entries with album_id set and photo_id null
  - **Photo-specific Pricing**: Override album pricing for specific photos via Purchasable entries with both album_id and photo_id set
- **Currency Settings**: Configure currency display and processing.
- **Tax Handling**: Options for tax calculation and display.
- **Payment Gateways**: Configure available payment methods via environment variables in the .env file.

## Security Considerations

- **Payment Security**: Offloading payment processing to trusted third-party providers (Stripe/PayPal) to handle sensitive payment data.
- **Download Protection**: Prevention of unauthorized access to purchased photos => use secure & temporary link.
- **Receipt Verification**: Secure verification of purchase records.
- **Payment Webhooks**: Secure handling of payment confirmation webhooks from payment processors.

## UI Components

The frontend implementation consists of several key components:

- **Basket**: A global component allowing users to review selected photos before purchase.
- **Photo Purchase Dialog**: Interface for selecting photo resolution and adding to basket.
- **Checkout Flow**: Steps to complete the purchase, including payment method selection.
- **Purchase History**: View for users to access their previously purchased photos.

## State Management

### Frontend State
The shop functionality uses Pinia store to manage:

- Shopping basket contents
- Checkout process state

### Backend Session Management
The basket functionality uses Laravel's session management:

- Basket ID is stored in the session
- When a user logs in, the session basket is associated with their account
- Requests include a trait to automatically fetch the current basket
- Controller methods work with the session to maintain basket state
- Session is cleared when an order is completed or manually deleted

## Integration Points

The shop functionality integrates with Lychee at multiple levels:

- **Photo Context Menu**: Adds "Add to Basket" option for purchasable photos.
- **Photo Detail View**: Displays purchase information and pricing.
- **User Account**: Extends with purchase history and download rights.
- **Admin Panel**: Adds controls to set photos as purchasable and configure pricing.

## User Flows

### Photo Purchase Flow

1. User browses photos within purchasable albums.
2. User adds photo to basket via context menu or detail view or album view.
3. User selects desired size (MEDIUM, FULL, or ORIGINAL if available) and license.
4. User can continue browsing and add more photos to the basket.
5. User can review and modify basket contents (remove items).
6. User proceeds to checkout when ready to complete purchase.
7. User completes payment process.
8. System records purchase and grants download access.

### Basket Management Flow

1. User's basket is created automatically when they first view it or add items
2. User adds photos or entire albums to the basket
3. User can view the basket contents at any time
4. User can remove items from the basket
5. User can delete the entire basket
6. The basket is maintained across sessions via a session cookie
7. If the user logs in, they will see any existing basket associated with their account

### Photographer Flow

1. Photographer uploads photos.
2. Photographer marks specific photos as purchasable.
3. Photographer sets pricing and license options.
4. Photographer receives notifications of sales.
5. Photographer can review sales history.

## License Management

Photos can be sold with different licensing terms:

- **Personal Use**: For non-commercial use by the purchaser.
- **Commercial License**: For business and promotional use.
- **Extended License**: For broader usage rights.

---

*Last updated: September 7, 2025*
