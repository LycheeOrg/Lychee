# Feature 043 – Webshop Print & Pixel Sizes

| Field | Value |
|-------|-------|
| Status | Ready for implementation |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/tasks.md` |
| Roadmap entry | #043 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature extends the Lychee webshop beyond digital size variants (MEDIUM, MEDIUM2X, ORIGINAL, FULL) to support **physical print orders** (at photographer-configured print sizes in cm or inches, with optional paper-type) and **custom pixel-size digital exports**. The admin defines a global catalogue of available print sizes and pixel sizes (dimensions only, no price). When creating or editing a purchasable, the photographer selects which print/pixel sizes to offer and sets a price for each. When a basket contains at least one print item, the checkout step collects a shipping address. A new admin configuration page manages the global print/pixel size catalogue. A new `PurchasableLicenseType::PRINT` enum value is introduced; print and pixel-size order items carry this license type automatically.

**Affected modules:** Database (new `print_sizes`, `pixel_sizes`, `purchasable_print_sizes`, `purchasable_pixel_sizes` tables; migrations for `orders` and `order_items`), Enum (`PurchasableLicenseType`), Models (`Order`, `OrderItem`, `Purchasable`, new `PrintSize`/`PixelSize`/`PurchasablePrintSize`/`PurchasablePixelSize`), Application services (`BasketService`, `CheckoutService`, `PurchasableService`), REST API (new management routes, extended basket/checkout/purchasable endpoints), UI (basket item type selector, shipping address form, admin print/pixel size catalogue page, extended purchasable prices form).

## Goals

- Allow customers to purchase photos as physical prints at photographer-defined print sizes (width × height, unit cm or inch, optional paper type).
- Allow customers to purchase photos at photographer-defined pixel sizes (width × height in pixels).
- Retain the existing size-variant purchase flow with zero regression.
- Introduce `is_print` boolean on `OrderItem` to distinguish physical from digital items.
- Collect a shipping address at checkout when the order contains at least one print item; store it on the `Order`.
- Provide a dedicated admin page to manage the global catalogue of available print and pixel sizes (no prices — only dimensions/labels).
- Per-purchasable: photographer selects which sizes to offer and sets prices for each in the existing purchasable management UI.
- Introduce `PurchasableLicenseType::PRINT = 'print'` so print and pixel-size order items carry a distinct license type, without exposing the personal/commercial/extended dimension for these item types.

## Non-Goals

- Automatic fulfilment of print orders (manual offline step; system records shipping address only).
- Integration with third-party print-on-demand services.
- Per-print-size price overrides at album or photo level beyond the per-purchasable assignment already described.
- Bulk discount or coupon codes.
- Currency conversion or multi-currency support.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-043-01 | Admin can create print sizes in the global catalogue | Admin POSTs `{ label, width, height, unit: "cm"\|"inch", paper_type: string\|null, is_active }` to `POST /api/v2/Shop/Management/PrintSize`. Record persisted in `print_sizes` table. | Width and height are positive integers; unit is `cm` or `inch`; label ≤ 100 chars; paper_type ≤ 100 chars if provided. No price stored here. | 422 with field-level errors when validation fails. | None. | Q-043-01 B, Q-043-04 B, additional requirement |
| FR-043-02 | Admin can create pixel sizes in the global catalogue | Admin POSTs `{ label, width, height, is_active }` to `POST /api/v2/Shop/Management/PixelSize`. Record persisted in `pixel_sizes` table. | Width and height are positive integers; label ≤ 100 chars. No price stored here. | 422 with field-level errors when validation fails. | None. | Q-043-01 B, Q-043-04 B |
| FR-043-03 | Admin can update and delete print/pixel sizes | Admin PUTs to `PUT /api/v2/Shop/Management/PrintSize/{id}` / `PixelSize/{id}` to update fields; DELETE to remove. Existing `purchasable_print_sizes` / `purchasable_pixel_sizes` rows and order item snapshots are preserved when a catalogue entry is deleted. | Record must exist; admin session required. | 404 if not found; 403 if unauthorised. | None. | User requirement |
| FR-043-04 | Admin can enable or disable individual print/pixel sizes | `is_active` boolean controls customer-facing visibility. Inactive sizes are excluded from `GET /api/v2/Shop/Catalogue/Sizes`. | `is_active` required on update. | 422 if missing. | None. | User requirement |
| FR-043-05 | Photographer assigns print sizes to a purchasable with per-size prices | When creating or updating a purchasable, the photographer includes `{ print_sizes: [{ print_size_id, price_cents }] }` in the request. Each entry is persisted in `purchasable_print_sizes`. | Each `print_size_id` must reference an existing print size; `price_cents` ≥ 0; no duplicate `print_size_id` within one purchasable. | 422 on validation error. | None. | Q-043-01 B, Q-043-04 B |
| FR-043-06 | Photographer assigns pixel sizes to a purchasable with per-size prices | When creating or updating a purchasable, the photographer includes `{ pixel_sizes: [{ pixel_size_id, price_cents }] }` in the request. Each entry is persisted in `purchasable_pixel_sizes`. | Each `pixel_size_id` must reference an existing pixel size; `price_cents` ≥ 0; no duplicates. | 422 on validation error. | None. | Q-043-01 B, Q-043-04 B |
| FR-043-07 | Customer can add a print-size order item to the basket | `POST /api/v2/Shop/Basket/Photo` accepts `{ photo_id, print_size_id }`. Basket service creates an `OrderItem` with `is_print = true`, `print_size_id` set, `pixel_size_id = null`, `size_variant_type = null`, `license_type = print`. Price resolved from `purchasable_print_sizes` for the photo's purchasable. Snapshot columns (`print_width`, `print_height`, `print_unit`, `print_paper_type`) populated from the catalogue entry. | `print_size_id` must reference a `purchasable_print_sizes` row on the photo's purchasable; photo must be purchasable. | 422 when `print_size_id` not assigned to this purchasable or not active; 404 when photo not found or not purchasable. | None. | Q-043-01 B, Q-043-02 |
| FR-043-08 | Customer can add a pixel-size order item to the basket | `POST /api/v2/Shop/Basket/Photo` accepts `{ photo_id, pixel_size_id }`. Basket service creates an `OrderItem` with `is_print = false`, `pixel_size_id` set, `print_size_id = null`, `size_variant_type = null`, `license_type = print`. Price resolved from `purchasable_pixel_sizes` for the photo's purchasable. Snapshot columns (`pixel_width`, `pixel_height`) populated. | `pixel_size_id` must reference a `purchasable_pixel_sizes` row on the photo's purchasable. | 422 when not assigned or inactive; 404 when photo not found. | None. | Q-043-01 B, Q-043-02 |
| FR-043-09 | Existing size-variant basket flow is unchanged | `POST /api/v2/Shop/Basket/Photo` with `size_variant_type` (no `print_size_id`/`pixel_size_id`) follows the current code path. `is_print = false`, new FK columns `null`. | Regression tests for existing digital purchases pass. | No regression on existing API contract. | None. | Backward compat |
| FR-043-10 | `PurchasableLicenseType::PRINT` enum value is introduced | A new case `PRINT = 'print'` is added to `App\Enum\PurchasableLicenseType`. Print and pixel-size order items carry this value on their `license_type` column. The personal/commercial/extended choices are not offered for these item types. | Enum serialises and deserialises `'print'` without error. | Existing digital items unaffected. | None. | Q-043-02 |
| FR-043-11 | `is_print` boolean on `OrderItem` distinguishes physical from digital | `is_print = true` means the item requires physical fulfilment. Digital size-variant and pixel-size items have `is_print = false`. | Migration adds `is_print BOOLEAN NOT NULL DEFAULT FALSE`. | Any item without explicit print intent defaults to `false`. | None. | User requirement |
| FR-043-12 | Order snapshot captures print/pixel size dimensions at basket-add time | `OrderItem` stores `print_width`, `print_height`, `print_unit`, `print_paper_type`, `pixel_width`, `pixel_height` at basket-add time. Changes to the catalogue after purchase do not affect historical records. | Values match catalogue entry at add time. Nullable for non-print/non-pixel items. | No action if item is a standard size variant. | None. | Data integrity |
| FR-043-13 | Pixel-size fulfilment follows the existing `download_link` mechanism | A pixel-size `OrderItem` has `size_variant_type = null`, `is_print = false`. Fulfilment awaits a `download_link` set by the photographer (same admin action as FULL size). `FulfillOrders` task skips these until the link is set. | Existing fulfilment infrastructure applies unchanged. | None. | Q-043-03 A |
| FR-043-14 | Checkout collects shipping address when basket contains print items | If the order has any item where `is_print = true`, the checkout `InfoSection` step renders shipping address fields. | Required fields (street name, city, post code, country) must be non-empty. | 422 from `POST /api/v2/Shop/Checkout/Create-session` when required fields absent and basket has prints. | None. | User requirement |
| FR-043-15 | Shipping address is stored on the `Order` | `orders` table gains columns: `shipping_street_name`, `shipping_street_number`, `shipping_additional_info`, `shipping_city`, `shipping_post_code`, `shipping_country` (all `string\|null`). | Populated by `CheckoutService` before payment initiation. | All null for digital-only orders. | None. | User requirement |
| FR-043-16 | Order management screen shows shipping address for print orders | `GET /api/v2/Shop/Order/{id}` response includes `shipping_address` sub-object when any item is `is_print = true`. | Shipping fields in `OrderResource` when non-null. | Block hidden in UI for digital-only orders. | None. | User requirement |
| FR-043-17 | Customer-facing catalogue returns active print/pixel sizes with per-purchasable prices | `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes` returns `{ print_sizes: [...], pixel_sizes: [...] }` of active entries that are assigned to the given purchasable, including each entry's price for that purchasable. | Only active sizes assigned to the purchasable returned. | Empty arrays when none assigned or active. | None. | Q-043-01 B |
| FR-043-18 | Admin management page lists, creates, updates, deletes print/pixel sizes | New admin Vue page `PrintPixelSizesAdmin.vue` at route `/admin/shop/sizes`. Supports full CRUD for the global catalogue via management endpoints. No prices shown here. | Admin-only route. | Error toast on API failure. | None. | User requirement |
| FR-043-19 | `canProcessPayment()` on Order requires shipping address when prints present | `Order::canProcessPayment()` returns `false` if any item is `is_print = true` and any required shipping field is null/empty. | Unit test covers both paths. | Payment initiation blocked; UI shows validation message. | None. | Data integrity |
| FR-043-20 | New routes are behind `support:pro` middleware | All new API routes (`/api/v2/Shop/Management/PrintSize`, `/PixelSize`, `/Catalogue/Purchasable/{id}/Sizes`) sit inside the existing `support:pro` middleware group. | No new public endpoints. | 403 for non-pro installations. | None. | Q-043-05 A |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-043-01 | No regression on existing digital purchase flow | Backward compat | All existing Webshop feature tests continue to pass. | Existing Webshop test suite | Quality bar |
| NFR-043-02 | Migrations are reversible | Maintainability | `php artisan migrate:rollback` succeeds without data loss in test environment. | Laravel migration tooling | Coding standard |
| NFR-043-03 | Code follows Lychee PHP conventions | Maintainability | License headers, snake_case, strict comparison, PSR-4, no `empty()`. php-cs-fixer clean. PHPStan level 6 passes. | php-cs-fixer, phpstan | Coding convention |
| NFR-043-04 | API validation returns field-level 422 errors | User experience | Each invalid field name listed in `errors` response body. | FormRequest classes | API standard |
| NFR-043-05 | Shipping address fields are validated server-side | Security & data integrity | Required fields enforced by FormRequest; no raw HTML injection. | FormRequest | Security standard |
| NFR-043-06 | Existing `OrderItem` MoneyCast and relations preserved | Data integrity | No changes to existing `price_cents` cast or size-variant relations. | MoneyCast, BelongsTo | Backward compat |

## UI / Interaction Mock-ups

### 1. Basket Item Type Selector (customer-facing)

Reuses the existing add-to-basket modal; a radio/button group selects the item type before revealing the relevant size picker. For print and pixel-size items the license type is fixed to `print` (not displayed to the customer).

```
┌──────────────────────────────────────────────────────────┐
│  Add to Basket: "Sunset over the Alps"                   │
├──────────────────────────────────────────────────────────┤
│                                                          │
│  Type:  [ Digital file ]  [ Print ]  [ Pixel size ]      │
│                 ↑ button group / radio, reuses modal     │
│                                                          │
│  [If "Digital file" selected]                            │
│  Size:    [MEDIUM ▼]                                     │
│  License: [Personal ▼]                                   │
│                                                          │
│  [If "Print" selected]                                   │
│  Print size: [20×30 cm – Glossy – €25.00 ▼]              │
│  (license_type = print, set by server automatically)     │
│                                                          │
│  [If "Pixel size" selected]                              │
│  Pixel size: [3000×2000 px – €12.00 ▼]                   │
│  (license_type = print, set by server automatically)     │
│                                                          │
│              [ Add to basket ]                           │
└──────────────────────────────────────────────────────────┘
```

### 2. Checkout – Shipping Address Step (shown only when basket contains print items)

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

No prices are stored here. Prices are set per-purchasable in the purchasable management UI.

```
┌──────────────────────────────────────────────────────────┐
│  Shop › Print & Pixel Sizes                              │
├──────────────────────────────────────────────────────────┤
│  [ + Add Print Size ]        [ + Add Pixel Size ]        │
│                                                          │
│  PRINT SIZES                                             │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Label          │ W × H   │ Unit │ Paper type │ Act │  │
│  │ Small print    │ 10 × 15 │ cm   │ Glossy     │  ✓  │  │
│  │ Standard print │ 20 × 30 │ cm   │ Matte      │  ✓  │  │
│  │ US Letter      │  8 × 10 │ inch │ (none)     │  ✗  │  │
│  └───────────────────────────────────────────────────┘  │
│  [Edit] [Delete] per row                                 │
│                                                          │
│  PIXEL SIZES                                             │
│  ┌───────────────────────────────────────────────────┐  │
│  │ Label          │ W × H             │ Active        │  │
│  │ Web (1080p)    │ 1920 × 1080 px    │  ✓            │  │
│  │ Print-ready    │ 3000 × 2000 px    │  ✓            │  │
│  └───────────────────────────────────────────────────┘  │
│  [Edit] [Delete] per row                                 │
└──────────────────────────────────────────────────────────┘
```

### 4. Purchasable Management – Print/Pixel Size Pricing

Extends the existing purchasable prices form (`PricesInput.vue`) with a separate section for selecting and pricing the available print and pixel sizes for that purchasable.

```
┌──────────────────────────────────────────────────────────┐
│  Edit Purchasable: "Sunset over the Alps"                │
├──────────────────────────────────────────────────────────┤
│  … existing digital size prices …                        │
│                                                          │
│  PRINT SIZES (select from global catalogue)              │
│  ┌──────────────────────────────────────────────┐       │
│  │ [Small print – 10×15 cm Glossy ▼] Price: €__ │       │
│  │ [Standard print – 20×30 cm Matte ▼] Price: €_│       │
│  │ [ + Add print size ]                         │       │
│  └──────────────────────────────────────────────┘       │
│                                                          │
│  PIXEL SIZES (select from global catalogue)              │
│  ┌──────────────────────────────────────────────┐       │
│  │ [Web 1080p – 1920×1080 px ▼] Price: €___     │       │
│  │ [ + Add pixel size ]                         │       │
│  └──────────────────────────────────────────────┘       │
└──────────────────────────────────────────────────────────┘
```

### 5. Order Management – Shipping Address Panel

```
┌──────────────────────────────────────────────────────────┐
│  Order #1042  [COMPLETED]  2026-05-20                    │
├──────────────────────────────────────────────────────────┤
│  Items:                                                  │
│  • "Sunset Alps" – 20×30 cm Matte print – €25.00 [PRINT]│
│  • "City Night" – MEDIUM digital – Personal – €5.00     │
│                                                          │
│  Shipping address:                                       │
│    Hauptstraße 42, Apt 3B                                │
│    Berlin, 10115, Germany                                │
└──────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-043-01 | Admin creates a print size with valid cm dimensions and paper type → persisted, returned in management list |
| S-043-02 | Admin creates a print size with unit "inch" and no paper type → persisted with `paper_type = null` |
| S-043-03 | Admin creates a pixel size with valid px dimensions → persisted |
| S-043-04 | Admin updates a print size label and paper type → changes reflected in catalogue |
| S-043-05 | Admin deletes a print size → removed; existing order items retain snapshotted data; purchasable assignments cleaned up or orphaned gracefully |
| S-043-06 | Admin disables a print size → not returned in customer catalogue |
| S-043-07 | Photographer assigns a print size to a purchasable with price → `purchasable_print_sizes` row created |
| S-043-08 | Photographer assigns a pixel size to a purchasable with price → `purchasable_pixel_sizes` row created |
| S-043-09 | Customer views catalogue for a purchasable → only active, assigned print/pixel sizes returned with prices |
| S-043-10 | Customer adds a print item to basket → `is_print = true`, `license_type = 'print'`, dimensions snapshotted |
| S-043-11 | Customer adds a pixel-size item to basket → `is_print = false`, `license_type = 'print'`, pixel dims snapshotted |
| S-043-12 | Customer adds a digital size-variant item → existing flow, `is_print = false`, new columns null |
| S-043-13 | Customer proceeds to checkout with only digital items → shipping address fields NOT shown |
| S-043-14 | Customer proceeds to checkout with at least one print item → shipping address fields shown and required |
| S-043-15 | Customer submits checkout without required shipping fields when basket has prints → 422 from API |
| S-043-16 | Customer submits checkout with all required shipping fields → order created with shipping address stored |
| S-043-17 | Admin views order containing a print item → shipping address displayed |
| S-043-18 | Admin views order containing only digital items → shipping address block hidden |
| S-043-19 | Customer tries to add a print size not assigned to that purchasable → 422 error |
| S-043-20 | Customer tries to add an inactive print size → 422 error |
| S-043-21 | Customer tries to add a non-existent pixel size → 404 error |
| S-043-22 | Existing digital-purchase test suite passes without modification |
| S-043-23 | `Order::canProcessPayment()` returns `false` when prints present and shipping address incomplete |
| S-043-24 | `Order::canProcessPayment()` returns `true` when prints present and shipping address complete |
| S-043-25 | Admin page loads print and pixel size catalogue with no prices |
| S-043-26 | Admin adds a print size via the UI form → appears in list |
| S-043-27 | Admin toggles active/inactive status → API updated, customer catalogue reflects change |
| S-043-28 | `PurchasableLicenseType::PRINT` serialises and deserialises correctly |

## Test Strategy

- **Unit:** `Order::canProcessPayment()` with print items and complete/incomplete shipping address (S-043-23, S-043-24). `OrderItem` snapshot attributes. `PrintSizeService` / `PixelSizeService` create/update/delete. `PurchasableLicenseType::PRINT` enum serialisation (S-043-28).
- **Feature (REST):** `PrintSizeManagementControllerTest` (S-043-01..06), `PixelSizeManagementControllerTest` (S-043-03, S-043-06), `PurchasablePrintPixelPricingTest` (S-043-07..09), `BasketControllerPrintTest` (S-043-10..12, S-043-19..21), `CheckoutShippingAddressTest` (S-043-13..16, S-043-23..24), `OrderResourceShippingTest` (S-043-17..18).
- **Regression:** Run existing `tests/Webshop/` suite unchanged (S-043-22).
- **UI (manual/Playwright):** Admin size catalogue CRUD (S-043-25..27); basket item type selector (S-043-10..12); checkout address form visibility toggle (S-043-13..14); purchasable print/pixel price assignment (S-043-07..08).

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-043-01 | `PrintSize`: id, label, width, height, unit (`cm`\|`inch`), paper_type (`string\|null`), is_active | DB, Model, API |
| DO-043-02 | `PixelSize`: id, label, width, height (px), is_active | DB, Model, API |
| DO-043-03 | `PurchasablePrintSize`: id, purchasable_id, print_size_id, price_cents | DB, Model, API |
| DO-043-04 | `PurchasablePixelSize`: id, purchasable_id, pixel_size_id, price_cents | DB, Model, API |
| DO-043-05 | `OrderItem` extensions: `is_print`, `print_size_id`, `pixel_size_id`, `print_width`, `print_height`, `print_unit`, `print_paper_type`, `pixel_width`, `pixel_height` | DB, Model |
| DO-043-06 | `Order` shipping address fields: `shipping_street_name`, `shipping_street_number`, `shipping_additional_info`, `shipping_city`, `shipping_post_code`, `shipping_country` | DB, Model |
| DO-043-07 | `PurchasableLicenseType::PRINT = 'print'` — new enum case | Enum, DB, API |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-043-01 | GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes | Returns active print and pixel sizes assigned to the given purchasable, with per-purchasable prices | Customer-facing, within purchasable album scope |
| API-043-02 | GET /api/v2/Shop/Management/PrintSize | Lists all print sizes (admin, no prices) | Requires admin auth |
| API-043-03 | POST /api/v2/Shop/Management/PrintSize | Creates a print size (no price) | Requires admin auth |
| API-043-04 | PUT /api/v2/Shop/Management/PrintSize/{id} | Updates a print size | Requires admin auth |
| API-043-05 | DELETE /api/v2/Shop/Management/PrintSize/{id} | Deletes a print size | Requires admin auth |
| API-043-06 | GET /api/v2/Shop/Management/PixelSize | Lists all pixel sizes (admin, no prices) | Requires admin auth |
| API-043-07 | POST /api/v2/Shop/Management/PixelSize | Creates a pixel size | Requires admin auth |
| API-043-08 | PUT /api/v2/Shop/Management/PixelSize/{id} | Updates a pixel size | Requires admin auth |
| API-043-09 | DELETE /api/v2/Shop/Management/PixelSize/{id} | Deletes a pixel size | Requires admin auth |
| API-043-10 | POST /api/v2/Shop/Basket/Photo (extended) | Accepts `print_size_id` or `pixel_size_id` alongside existing `size_variant_type`; mutually exclusive | Sets `license_type = print` automatically for print/pixel items |
| API-043-11 | POST /api/v2/Shop/Checkout/Create-session (extended) | Accepts shipping address fields when basket has print items | New optional body fields, required when prints present |
| API-043-12 | POST /api/v2/Shop/Management/Purchasable (extended) | Extended to accept `print_sizes` and `pixel_sizes` arrays with per-size prices | Persists to `purchasable_print_sizes` / `purchasable_pixel_sizes` |
| API-043-13 | PUT /api/v2/Shop/Management/Purchasable/{id}/Prices (extended) | Extended to update print/pixel size prices for a purchasable | Replaces existing per-purchasable print/pixel size entries |

### Telemetry Events

None. No new telemetry events introduced by this feature.

---

*Last updated: 2026-05-31*
