# E-commerce and Webshop

This document explains Lychee's integrated e-commerce system for selling photos and albums.

## Table of Contents

- [What is the Webshop Feature?](#what-is-the-webshop-feature)
- [Purchasable](#purchasable)
- [PurchasablePrice](#purchasableprice)
- [Order](#order)
- [OrderItem](#orderitem)
- [Payment Processing](#payment-processing)

---

## What is the Webshop Feature?

Lychee includes an integrated **e-commerce system** that allows photographers to sell digital downloads and prints of their photos. The webshop supports both registered users and guest purchases.

**Key Features:**
- Sell individual photos or entire albums
- Multiple price points per item (size variants, licenses)
- Guest checkout with email delivery
- Omnipay integration (Stripe, Mollie, PayPal, etc.)
- Manual/offline payment processing
- Order tracking and fulfillment

---

## Purchasable

### What is a Purchasable?

**Purchasable** makes a photo or album available for purchase. It defines what can be sold and links to pricing options.

### Purchasable Attributes

**Core Properties:**
- **id**: Unique identifier
- **photo_id**: Link to Photo being sold (nullable, either photo or album)
- **album_id**: Link to Album being sold (nullable, either photo or album)
- **description**: Customer-facing product description
- **owner_notes**: Internal notes for photographer
- **is_active**: Enable/disable without deleting

**Relationships:**
- `photo()` - BelongsTo Photo (optional)
- `album()` - BelongsTo Album (optional)
- `prices()` - HasMany PurchasablePrice entries

**Business Rules:**
- Either photo_id OR album_id must be set (not both)
- Can be temporarily disabled via `is_active` flag
- Owner notes not visible to customers

---

## PurchasablePrice

### Pricing Model

**PurchasablePrice** defines individual price points for a purchasable item. Multiple prices allow different size variants and license types.

### Price Attributes

**Core Properties:**
- **id**: Unique identifier
- **purchasable_id**: Link to Purchasable
- **size_variant**: Which variant to deliver
- **license_type**: Personal or Commercial use
- **price**: Money object (using moneyphp/money)
- **currency**: ISO currency code (USD, EUR, GBP, etc.)

### Size Variant Types

**SMALL:**
- Lower resolution for web/social media use
- Typical: 1080px longest edge
- Instant digital delivery

**MEDIUM:**
- Mid-resolution for standard prints
- Typical: 1920px or 2048px longest edge
- Instant digital delivery

**ORIGINAL:**
- Full resolution as uploaded
- Preserves all EXIF and quality
- Instant digital delivery

**FULL:**
- Requires manual processing by photographer
- Email-based delivery workflow
- For custom edits, RAW files, or special requests
- Order includes customer email for delivery coordination

### License Types

**Personal Use:**
- Non-commercial purposes only
- Personal prints, wallpapers, gifts
- Lower price point

**Commercial Use:**
- Business and marketing purposes
- Advertising, websites, publications
- Higher price point

### Pricing Strategy Examples

```
Photo "Sunset Over Mountains"
  ├─ SMALL / Personal: $5
  ├─ MEDIUM / Personal: $15
  ├─ ORIGINAL / Personal: $50
  ├─ SMALL / Commercial: $25
  ├─ MEDIUM / Commercial: $75
  └─ ORIGINAL / Commercial: $200
```

---

## Order

### Order Lifecycle

**Order** represents a complete purchase transaction from basket to fulfillment.

### Order States

1. **PENDING** - Initial state when items added to basket
2. **PROCESSING** - Payment being processed by provider
3. **COMPLETED** - Payment successful, order fulfilled
4. **OFFLINE** - Manual/offline payment processing
5. **CLOSED** - Order delivered and finalized
6. **CANCELLED** - Order cancelled by customer or admin
7. **FAILED** - Payment failed or declined

### Order Attributes

**Core Properties:**
- **id**: Unique identifier
- **transaction_id**: Unique identifier for payment provider tracking
- **provider**: Payment provider (Stripe, Mollie, PayPal, etc.)
- **user_id**: Registered user (nullable for guest purchases)
- **email**: Customer email (required for guests and FULL variants)
- **status**: Current payment status
- **total**: Total order amount (Money object)
- **currency**: ISO currency code
- **payment_details**: JSON metadata from payment provider
- **created_at**, **updated_at**: Order timestamps

**Relationships:**
- `user()` - BelongsTo User (optional, nullable for guests)
- `items()` - HasMany OrderItem entries

### Guest Purchases

**Guest Checkout:**
- No user account required
- Email address mandatory for delivery
- Receive order confirmation and download links via email

**FULL Variant Delivery:**
- Requires photographer to process manually
- Customer email captured in order
- Photographer contacts customer for delivery

### Payment Provider Integration

**Supported via Omnipay:**
- Stripe
- Mollie  
- PayPal
- Square
- And 100+ other gateways

**Offline Payments:**
- Bank transfer
- Cash/check
- Manual processing
- Status set to OFFLINE until confirmed

---

## OrderItem

### What is an OrderItem?

**OrderItem** represents individual line items in an order basket. Each item links to a photo and captures pricing at purchase time.

### OrderItem Attributes

**Core Properties:**
- **id**: Unique identifier
- **order_id**: Link to parent Order
- **photo_id**: Link to purchased Photo
- **purchasable_id**: Link to Purchasable configuration
- **size_variant**: Which variant was purchased
- **license_type**: License granted (personal/commercial)
- **price**: Price paid at purchase time (Money object)
- **currency**: Currency of purchase

**Relationships:**
- `order()` - BelongsTo Order
- `photo()` - BelongsTo Photo
- `purchasable()` - BelongsTo Purchasable

### Price History

**Why capture price in OrderItem?**
- Photographer may change prices after purchase
- Order shows what customer actually paid
- Historical record for accounting/taxes
- Prevents disputes over pricing

---

## Payment Processing

### Omnipay Integration

**Payment Gateway Abstraction:**
- Omnipay provides unified API for 100+ payment providers
- Configure provider in system settings
- API keys and secrets stored in Configs
- Webhook handling for async payment confirmation

### Payment Flow

1. **Customer adds items to basket**
   - OrderItems created with PENDING Order
2. **Customer proceeds to checkout**
   - Order total calculated
   - Redirect to payment provider
3. **Customer completes payment**
   - Provider processes payment
   - Webhook notifies Lychee
4. **Order fulfilled**
   - Status updated to COMPLETED
   - Download links sent to customer email
   - FULL variants flagged for manual processing

### Offline Payment Flow

1. **Photographer enables offline payments**
2. **Customer selects offline payment method**
3. **Order created with OFFLINE status**
4. **Customer pays via bank transfer/cash/check**
5. **Photographer manually confirms payment**
6. **Order status updated to COMPLETED**
7. **Download links sent to customer**

---

**Related:** [Photos](photos.md) | [Albums](albums.md) | [System Configuration](system.md)

---

*Last updated: December 22, 2025*
