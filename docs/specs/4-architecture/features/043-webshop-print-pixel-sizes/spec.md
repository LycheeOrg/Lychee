# Feature 043 – Webshop Print & Pixel Sizes

| Field | Value |
|-------|-------|
| Status | Draft – blocked on open questions Q-043-01 … Q-043-05 |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/tasks.md` |
| Roadmap entry | #043 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature extends the Lychee webshop to support **physical print orders** (at admin-configured print sizes in cm or inches) and **custom pixel-size digital exports**. The admin defines a global catalogue of supported print and pixel sizes. When a basket contains at least one print item, the checkout flow collects a shipping address. A new admin configuration page manages the print/pixel catalogue. The existing size-variant flow (MEDIUM, MEDIUM2X, ORIGINAL, FULL) is unchanged.

**Affected modules:** Database (new `print_sizes` / `pixel_sizes` tables, migrations for `orders` and `order_items`), Models (`Order`, `OrderItem`, new `PrintSize` / `PixelSize`), Application services (`BasketService`, `CheckoutService`, `PurchasableService`), REST API (new management routes, extended basket/checkout endpoints), UI (`InfoSection`, new admin page, basket item type selector).

> ⚠️ **Spec incomplete.** Five open questions (Q-043-01 through Q-043-05) must be resolved before requirements and implementation details can be finalised. See [docs/specs/4-architecture/open-questions.md](../../open-questions.md).

## Goals

- Allow customers to purchase photos as physical prints at admin-defined sizes (width × height, unit cm or inch).
- Allow customers to purchase photos at admin-defined pixel sizes (width × height in pixels).
- Retain the existing digital size-variant purchase flow with zero regression.
- Introduce `is_print` boolean on `OrderItem` to distinguish physical print items from digital items.
- Collect a shipping address at checkout when the order contains at least one print item; store it on the `Order`.
- Provide a dedicated admin page to create, edit, enable/disable, and delete print and pixel sizes.

## Non-Goals

- Automatic fulfilment of print orders (manual offline step).
- Integration with third-party print-on-demand services.
- Per-album or per-photo print/pixel size restrictions *(pending Q-043-04)*.
- Bulk discount or coupon codes.
- Currency conversion or multi-currency support.

## Functional Requirements

> ⚠️ Requirements table will be authored once Q-043-01 … Q-043-05 are resolved.

## Non-Functional Requirements

> ⚠️ NFR table will be authored once Q-043-01 … Q-043-05 are resolved.

## UI / Interaction Mock-ups

> ⚠️ ASCII mock-ups will be finalised once Q-043-01 … Q-043-05 are resolved.

### Basket Item Type Selector (customer-facing, draft)

```
┌──────────────────────────────────────────────────────────┐
│  Add to Basket: "Sunset over the Alps"                   │
├──────────────────────────────────────────────────────────┤
│  Type:  ○ Digital file   ○ Print   ○ Pixel size          │
│                                                          │
│  [If "Digital file" selected]                            │
│  Size:  [MEDIUM ▼]   License: [Personal ▼]               │
│                                                          │
│  [If "Print" selected]                                   │
│  Print size:  [20×30 cm – €25.00 ▼]                      │
│  License: [Personal ▼]   ← presence depends on Q-043-02 │
│                                                          │
│  [If "Pixel size" selected]                              │
│  Pixel size:  [3000×2000 px – €12.00 ▼]                  │
│  License: [Personal ▼]   ← presence depends on Q-043-02 │
│                                                          │
│              [ Add to basket ]                           │
└──────────────────────────────────────────────────────────┘
```

### Checkout – Shipping Address Step (draft)

```
┌──────────────────────────────────────────────────────────┐
│  Your info                                               │
├──────────────────────────────────────────────────────────┤
│  Email: _______________________ *                        │
│                                                          │
│  ── Shipping address (required for print orders) ──      │
│  Street name:   _______________________ *                │
│  Street number: ________                                 │
│  Additional:    _______________________                  │
│  City:          _______________________ *                │
│  Post code:     ______________ *                         │
│  Country:       [Select country ▼]        *              │
│                                                          │
│  ☐ I agree to the Terms and Privacy Policy               │
│                              [ Next → ]                  │
└──────────────────────────────────────────────────────────┘
```

### Admin Print/Pixel Sizes Page (`/admin/shop/sizes`, draft)

```
┌──────────────────────────────────────────────────────────┐
│  Shop › Print & Pixel Sizes                              │
├──────────────────────────────────────────────────────────┤
│  [ + Add Print Size ]        [ + Add Pixel Size ]        │
│                                                          │
│  PRINT SIZES                                             │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Label       │ W × H   │ Unit │ Price  │ Active   │   │
│  │ Small       │ 10 × 15 │ cm   │ €5.00  │ ✓ [Edit] │   │
│  │ Standard    │ 20 × 30 │ cm   │ €25.00 │ ✓ [Edit] │   │
│  │ US Letter   │  8 × 10 │ inch │ €20.00 │ ✗ [Edit] │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  PIXEL SIZES                                             │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Label       │ W × H          │ Price   │ Active  │   │
│  │ Web 1080p   │ 1920 × 1080 px │ €8.00   │ ✓ [Edit]│   │
│  │ Print-ready │ 3000 × 2000 px │ €12.00  │ ✓ [Edit]│   │
│  └──────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

> ⚠️ Scenario IDs will be assigned once requirements are finalised.

## Test Strategy

- **Unit:** `Order::canProcessPayment()` with print items and complete/incomplete shipping address.
- **Feature (REST):** Print/pixel size management CRUD; basket add for print/pixel items; checkout shipping address validation.
- **Regression:** Full `tests/Webshop/` suite must pass unchanged.
- **UI:** Admin size catalogue CRUD; basket item type selector; checkout address form visibility toggle.

## Interface & Contract Catalogue

> ⚠️ Will be authored once open questions are resolved. Draft API routes:
> - `GET/POST/PUT/DELETE /api/v2/Shop/Management/PrintSize`
> - `GET/POST/PUT/DELETE /api/v2/Shop/Management/PixelSize`
> - `GET /api/v2/Shop/Catalogue/Sizes` (customer-facing, active sizes only)
> - `POST /api/v2/Shop/Basket/Photo` (extended to accept `print_size_id` / `pixel_size_id`)
> - `POST /api/v2/Shop/Checkout/Create-session` (extended to accept shipping address)

## Spec DSL

```yaml
# Incomplete — pending open-question resolution
domain_objects:
  - id: DO-043-01
    name: PrintSize
    fields: [id, label, width, height, unit (cm|inch), price_cents, is_active]
  - id: DO-043-02
    name: PixelSize
    fields: [id, label, width, height (px), price_cents, is_active]
  - id: DO-043-03
    name: OrderItem (extensions)
    fields: [is_print, print_size_id, pixel_size_id, print_width, print_height, print_unit, pixel_width, pixel_height]
  - id: DO-043-04
    name: Order (extensions)
    fields: [shipping_street_name, shipping_street_number, shipping_additional_info, shipping_city, shipping_post_code, shipping_country]
```


| Field | Value |
|-------|-------|
| Status | Planning |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/tasks.md` |
| Roadmap entry | #043 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature extends the Lychee webshop beyond digital size variants (MEDIUM, MEDIUM2X, ORIGINAL, FULL) to support **physical print orders** (at configurable print sizes in cm or inches) and **custom pixel-size exports**. The admin can define a global catalogue of supported print sizes and pixel sizes; these become purchasable options alongside the existing size-variant flow. When a basket contains at least one print item the checkout step collects a shipping address. A new admin configuration page manages the print/pixel catalogue.

**Affected modules:** Database (new tables, orders migration), Models (`OrderItem`, `Order`, new `PrintSize`/`PixelSize`), Enums (`PurchasableSizeVariantType`), Application services (`BasketService`, `CheckoutService`), REST API (new management routes, extended basket/checkout endpoints), UI (basket item type selector, shipping address form, new admin config page).

## Goals

- Allow customers to purchase photos as physical prints at photographer-defined print sizes (width × height, unit cm or inch).
- Allow customers to purchase photos at photographer-defined pixel sizes (width × height in pixels).
- Retain the existing size-variant flow unchanged so no regression for digital purchases.
- Introduce a boolean `is_print` flag on `OrderItem` that correctly differentiates physical print orders from digital orders.
- Collect a shipping address (street name, street number, additional info, city, post code, country) at checkout whenever the order contains at least one print item.
- Provide a dedicated admin page to manage the global catalogue of supported print sizes and pixel sizes (enable, disable, add, remove, reorder).
- Store the shipping address on the `Order` so it can be viewed in the order management screen and used for dispatch.

## Non-Goals

- Automatic fulfilment of print orders (this remains a manual offline step for the photographer; the system notifies and records the shipping address only).
- Integration with third-party print-on-demand services.
- Per-album or per-photo print size restrictions (all active print/pixel sizes are available on every purchasable item).
- Pricing per-size-variant override for print/pixel sizes at the album or photo level (print/pixel sizes use only the global catalogue price defined by the admin).
- Currency conversion or multi-currency support (existing currency config applies).
- Bulk discount or coupon codes.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-043-01 | Admin can create print sizes in the print/pixel catalogue | Admin POSTs `{ type: "print", width, height, unit: "cm"\|"inch", label, price_cents, is_active }` to `POST /api/v2/Shop/Management/PrintSize`. Record persisted in `print_sizes` table. | Width and height are positive integers; unit is `cm` or `inch`; label is ≤ 100 chars; price_cents ≥ 0. | 422 with field-level errors when validation fails. | No telemetry. | User requirement |
| FR-043-02 | Admin can create pixel sizes in the print/pixel catalogue | Admin POSTs `{ type: "pixel", width, height, label, price_cents, is_active }` to `POST /api/v2/Shop/Management/PixelSize`. Record persisted in `pixel_sizes` table. | Width and height are positive integers; label is ≤ 100 chars; price_cents ≥ 0. | 422 with field-level errors when validation fails. | No telemetry. | User requirement |
| FR-043-03 | Admin can update and delete print/pixel sizes | Admin PUTs to `PUT /api/v2/Shop/Management/PrintSize/{id}` or `PUT /api/v2/Shop/Management/PixelSize/{id}` to update; DELETEs to remove. Soft-delete not required (hard delete acceptable; existing order items snapshot label and dimensions). | Record must exist and belong to a valid admin session. | 404 if not found; 403 if unauthorised. | No telemetry. | User requirement |
| FR-043-04 | Admin can enable or disable individual print/pixel sizes | `is_active` boolean on each record controls whether the size appears in the customer-facing catalogue. Inactive sizes are still stored for historical order record accuracy. | Only active sizes returned from `GET /api/v2/Shop/Catalogue/Sizes`. | 422 if `is_active` missing on update. | No telemetry. | User requirement |
| FR-043-05 | Customer can add a print-size order item to basket | `POST /api/v2/Shop/Basket/Photo` accepts `{ photo_id, print_size_id, license_type }` (alongside existing `size_variant_type`). Basket service creates an `OrderItem` with `is_print = true`, `print_size_id` set, `pixel_size_id = null`, `size_variant_type = null`, `size_variant_id = null`. | `print_size_id` must reference an active print size; `photo_id` must reference a purchasable photo. Price is taken from `print_sizes.price_cents`. | 422 when `print_size_id` is inactive or missing; 404 when photo not found/purchasable. | No telemetry. | User requirement |
| FR-043-06 | Customer can add a pixel-size order item to basket | `POST /api/v2/Shop/Basket/Photo` accepts `{ photo_id, pixel_size_id, license_type }`. Basket service creates an `OrderItem` with `is_print = false`, `pixel_size_id` set, `print_size_id = null`, `size_variant_type = null`. | `pixel_size_id` must reference an active pixel size. | 422 when `pixel_size_id` inactive or missing. | No telemetry. | User requirement |
| FR-043-07 | Existing size-variant basket flow is unchanged | `POST /api/v2/Shop/Basket/Photo` with `size_variant_type` set (no `print_size_id`/`pixel_size_id`) follows the current code path; `is_print = false`, both new FK columns `null`. | Regression tests for existing digital purchases all pass. | No regression on existing API contract. | No telemetry. | Backward compat |
| FR-043-08 | `is_print` boolean on `OrderItem` distinguishes physical from digital | `is_print = true` means the item requires physical fulfilment. Digital size-variant and pixel-size items have `is_print = false`. | Migration adds `is_print BOOLEAN NOT NULL DEFAULT FALSE`. | Any item without explicit print intent defaults to false. | No telemetry. | User requirement |
| FR-043-09 | Order snapshot captures print/pixel size dimensions at purchase time | `OrderItem` stores `print_width`, `print_height`, `print_unit`, `pixel_width`, `pixel_height` at the moment the item is added to the basket. Changes to the catalogue after purchase do not affect historical records. | Values match the catalogue entry at basket-add time. Nullable for non-print/non-pixel items. | No action if item is a standard size variant. | No telemetry. | Data integrity |
| FR-043-10 | Checkout collects shipping address when basket contains prints | If `Order.items` has any item where `is_print = true`, the checkout `InfoSection` step renders shipping address fields (street name, street number, additional info, city, post code, country). | All required fields (street name, city, post code, country) must be non-empty. | 422 from `POST /api/v2/Shop/Checkout/Create-session` when required fields absent and basket has prints. | No telemetry. | User requirement |
| FR-043-11 | Shipping address is stored on the `Order` | `orders` table gains columns: `shipping_street_name`, `shipping_street_number`, `shipping_additional_info`, `shipping_city`, `shipping_post_code`, `shipping_country` (all `string\|null`). Set to non-null values only for orders containing print items. | Columns present in migration; populated by `CheckoutService` before payment initiation. | Not populated for digital-only orders (all null). | No telemetry. | User requirement |
| FR-043-12 | Order management screen shows shipping address for print orders | `GET /api/v2/Shop/Order/{id}` response includes `shipping_address` sub-object when any item `is_print = true`. | Shipping fields returned in `OrderResource` when non-null. | Shipping address block hidden in UI for digital-only orders. | No telemetry. | User requirement |
| FR-043-13 | Customer-facing catalogue exposes active print/pixel sizes | `GET /api/v2/Shop/Catalogue/Sizes` returns `{ print_sizes: [...], pixel_sizes: [...] }` of active entries. Used by frontend to populate size selectors. | Only `is_active = true` records returned. | Empty arrays when no active sizes. | No telemetry. | User requirement |
| FR-043-14 | Admin management page lists, creates, updates, deletes print/pixel sizes | New admin Vue page `PrintPixelSizesAdmin.vue` at route `/admin/shop/sizes`. Supports full CRUD via the management API endpoints. | Admin-only route (redirects non-admins to home). | Error toast on API failure. | No telemetry. | User requirement |
| FR-043-15 | `canProcessPayment()` on Order requires shipping address when prints present | `Order::canProcessPayment()` returns `false` if any item is `is_print = true` and any required shipping field is null/empty. | Unit test covers both paths. | Payment initiation blocked; UI shows validation message. | No telemetry. | Data integrity |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-043-01 | No regression on existing digital purchase flow | Backward compat | All existing Webshop feature tests continue to pass. | Existing Webshop test suite | Quality bar |
| NFR-043-02 | Migrations are reversible | Maintainability | `php artisan migrate:rollback` succeeds without data loss in test environment. | Laravel migration tooling | Coding standard |
| NFR-043-03 | Code follows Lychee PHP conventions | Maintainability | License headers, snake_case, strict comparison, PSR-4, no `empty()`. php-cs-fixer clean. PHPStan level 6 passes. | php-cs-fixer, phpstan | Coding convention |
| NFR-043-04 | API validation returns field-level 422 errors | User experience | Each invalid field name listed in `errors` response body. | FormRequest classes | API standard |
| NFR-043-05 | Shipping address fields are validated server-side | Security & data integrity | Required fields enforced by FormRequest; no raw HTML injection. | FormRequest | Security standard |
| NFR-043-06 | Existing `OrderItem` MoneyCast and relations preserved | Data integrity | No changes to existing `price_cents` cast or size-variant relations. | MoneyCast, BelongsTo | Backward compat |
| NFR-043-07 | Print/pixel catalogue queries are paginated for large catalogues | Performance | `GET /api/v2/Shop/Management/PrintSize` and `PixelSize` support optional `?page=` param. | Laravel pagination | Performance standard |

## UI / Interaction Mock-ups

### 1. Basket Item Type Selector (customer-facing)

When adding a photo to basket, a size type radio group precedes the size selector:

```
┌──────────────────────────────────────────────────────────┐
│  Add to Basket: "Sunset over the Alps"                   │
├──────────────────────────────────────────────────────────┤
│  Type:  ○ Digital file   ○ Print   ○ Pixel size          │
│                                                          │
│  [If "Digital file" selected]                            │
│  Size:  [MEDIUM ▼]   License: [Personal ▼]               │
│                                                          │
│  [If "Print" selected]                                   │
│  Print size:  [20×30 cm – €25.00 ▼]                      │
│  License: [Personal ▼]                                   │
│                                                          │
│  [If "Pixel size" selected]                              │
│  Pixel size:  [3000×2000 px – €12.00 ▼]                  │
│  License: [Personal ▼]                                   │
│                                                          │
│              [ Add to basket ]                           │
└──────────────────────────────────────────────────────────┘
```

### 2. Checkout – Shipping Address Step (shown when basket contains prints)

Shipping address fields are injected below the email/consent block in the `InfoSection` component:

```
┌──────────────────────────────────────────────────────────┐
│  Your info                                               │
├──────────────────────────────────────────────────────────┤
│  Email: _______________________ *                        │
│                                                          │
│  ── Shipping address (required for print orders) ──      │
│  Street name:        _______________________ *           │
│  Street number:      ________                            │
│  Additional info:    _______________________             │
│  City:               _______________________ *           │
│  Post code:          ______________ *                    │
│  Country:            [Select country ▼]        *         │
│                                                          │
│  ☐ I agree to the Terms and Privacy Policy               │
│                              [ Next → ]                  │
└──────────────────────────────────────────────────────────┘
```

### 3. Admin Print/Pixel Sizes Page (`/admin/shop/sizes`)

```
┌──────────────────────────────────────────────────────────┐
│  Shop › Print & Pixel Sizes                              │
├──────────────────────────────────────────────────────────┤
│  [ + Add Print Size ]        [ + Add Pixel Size ]        │
│                                                          │
│  PRINT SIZES                                             │
│  ┌─────────────────────────────────────────────────────┐ │
│  │ Label          │ W × H       │ Unit │ Price  │ Active│ │
│  │ Small print    │ 10 × 15     │ cm   │ €olean5.00│ ✓  │ │
│  │ Standard print │ 20 × 30     │ cm   │ €25.00 │ ✓    │ │
│  │ Large print    │ 40 × 60     │ cm   │ €45.00 │ ✓    │ │
│  │ US Letter      │  8 × 10     │ inch │ €20.00 │ ✗    │ │
│  └─────────────────────────────────────────────────────┘ │
│  [Edit] [Delete] per row                                 │
│                                                          │
│  PIXEL SIZES                                             │
│  ┌─────────────────────────────────────────────────────┐ │
│  │ Label          │ W × H          │ Price   │ Active   │ │
│  │ Web (1080p)    │ 1920 × 1080 px │ €olean8.00 │ ✓    │ │
│  │ Print-ready    │ 3000 × 2000 px │ €12.00  │ ✓        │ │
│  └─────────────────────────────────────────────────────┘ │
└──────────────────────────────────────────────────────────┘
```

### 4. Order Management – Shipping Address Panel

```
┌──────────────────────────────────────────────────────────┐
│  Order #1042  [COMPLETED]  2026-05-20                    │
├──────────────────────────────────────────────────────────┤
│  Items:                                                  │
│  • "Sunset Alps" – 20×30 cm print – Personal – €25.00  [PRINT]│
│  • "City Night" – MEDIUM digital – Personal – €5.00    │
│                                                          │
│  Shipping address:                                       │
│    Jane Doe                                              │
│    Hauptstraße 42                                        │
│    Apt 3B                                                │
│    Berlin, 10115, Germany                                │
└──────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-043-01 | Admin creates a print size with valid cm dimensions → persisted, returned in catalogue |
| S-043-02 | Admin creates a print size with unit "inch" → persisted with correct unit |
| S-043-03 | Admin creates a pixel size with valid px dimensions → persisted |
| S-043-04 | Admin updates a print size label and price → changes reflected in catalogue |
| S-043-05 | Admin deletes a print size → removed from catalogue; existing order items retain snapshotted data |
| S-043-06 | Admin disables a print size → not returned in customer catalogue |
| S-043-07 | Customer adds a print item to basket → `is_print = true`, dimensions snapshotted |
| S-043-08 | Customer adds a pixel-size item to basket → `is_print = false`, pixel dims snapshotted |
| S-043-09 | Customer adds a digital size-variant item → existing flow, `is_print = false`, new columns null |
| S-043-10 | Customer proceeds to checkout with only digital items → shipping address fields not shown |
| S-043-11 | Customer proceeds to checkout with at least one print item → shipping address fields shown and required |
| S-043-12 | Customer submits checkout without required shipping fields when basket has prints → 422 from API |
| S-043-13 | Customer submits checkout with all required shipping fields → order created with shipping address |
| S-043-14 | Admin views order containing print item → shipping address displayed |
| S-043-15 | Admin views order containing only digital items → shipping address block hidden |
| S-043-16 | Customer tries to add an inactive print size → 422 error |
| S-043-17 | Customer tries to add a non-existent pixel size → 404 error |
| S-043-18 | Existing digital-purchase test suite passes without modification |
| S-043-19 | `Order::canProcessPayment()` returns false when prints present and shipping address incomplete |
| S-043-20 | `Order::canProcessPayment()` returns true when prints present and shipping address complete |
| S-043-21 | Admin page loads print and pixel size catalogue |
| S-043-22 | Admin adds a print size via the UI form → appears in list |
| S-043-23 | Admin toggles active/inactive status via the UI → API updated |

## Test Strategy

- **Unit:** `Order::canProcessPayment()` with print items and complete/incomplete shipping address. `OrderItem` snapshot attributes. `PrintSizeService` / `PixelSizeService` create/update/delete.
- **Feature (REST):** `PrintSizeManagementControllerTest`, `PixelSizeManagementControllerTest`, `BasketControllerPrintTest` (S-043-07..09), `CheckoutShippingAddressTest` (S-043-10..13, S-043-19..20), `OrderResourceShippingTest` (S-043-14..15).
- **Regression:** Run existing `tests/Webshop/` suite unchanged (S-043-18).
- **UI (manual/Playwright):** Admin size catalogue CRUD, basket item type selector, checkout address form visibility toggle.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-043-01 | `PrintSize`: id, label, width, height, unit (cm\|inch), price_cents, is_active | DB, Model, API |
| DO-043-02 | `PixelSize`: id, label, width, height (px), price_cents, is_active | DB, Model, API |
| DO-043-03 | `OrderItem` extensions: `is_print`, `print_size_id`, `pixel_size_id`, `print_width`, `print_height`, `print_unit`, `pixel_width`, `pixel_height` | DB, Model |
| DO-043-04 | `Order` shipping address fields: `shipping_street_name`, `shipping_street_number`, `shipping_additional_info`, `shipping_city`, `shipping_post_code`, `shipping_country` | DB, Model |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-043-01 | GET /api/v2/Shop/Catalogue/Sizes | Returns active print and pixel sizes for customer selection | Public (within purchasable album) |
| API-043-02 | GET /api/v2/Shop/Management/PrintSize | Lists all print sizes (admin) | Requires admin auth |
| API-043-03 | POST /api/v2/Shop/Management/PrintSize | Creates a print size | Requires admin auth |
| API-043-04 | PUT /api/v2/Shop/Management/PrintSize/{id} | Updates a print size | Requires admin auth |
| API-043-05 | DELETE /api/v2/Shop/Management/PrintSize/{id} | Deletes a print size | Requires admin auth |
| API-043-06 | GET /api/v2/Shop/Management/PixelSize | Lists all pixel sizes (admin) | Requires admin auth |
| API-043-07 | POST /api/v2/Shop/Management/PixelSize | Creates a pixel size | Requires admin auth |
| API-043-08 | PUT /api/v2/Shop/Management/PixelSize/{id} | Updates a pixel size | Requires admin auth |
| API-043-09 | DELETE /api/v2/Shop/Management/PixelSize/{id} | Deletes a pixel size | Requires admin auth |
| API-043-10 | POST /api/v2/Shop/Basket/Photo (extended) | Accepts `print_size_id` or `pixel_size_id` in addition to existing `size_variant_type` | Mutually exclusive inputs |
| API-043-11 | POST /api/v2/Shop/Checkout/Create-session (extended) | Accepts shipping address fields when basket has print items | New optional body fields |

### Telemetry Events

None. No new telemetry events introduced by this feature.

---

*Last updated: 2026-05-31*
