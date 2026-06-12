# Feature Plan 043 – Webshop Print & Pixel Sizes

_Linked specification:_ `docs/specs/4-architecture/features/043-webshop-print-pixel-sizes/spec.md`  
_Status:_ Ready for implementation  
_Last updated:_ 2026-05-31

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Extend the Lychee webshop so that photographers can sell physical prints and custom pixel-size digital exports alongside existing digital size variants. Customers can add print or pixel-size items to the basket, provide a shipping address at checkout (prints only), and complete payment via the existing gateway. Administrators manage the global catalogue of print/pixel sizes (no prices); photographers configure per-purchasable pricing via the existing purchasable management UI.

Success is measured by:
- All 28 scenarios in the Branch & Scenario Matrix pass.
- Full `tests/Webshop/` regression suite passes unchanged.
- `php artisan test`, `make phpstan`, and `npm run check` all pass.
- UI matches mock-ups in spec (basket type selector, checkout shipping form, admin sizes page, purchasable prices form extension).

## Scope Alignment

**In scope:**
- DB migrations: `print_sizes`, `pixel_sizes`, `purchasable_print_sizes`, `purchasable_pixel_sizes` tables; extend `order_items` and `orders`.
- `PurchasableLicenseType::PRINT = 'print'` enum value.
- `PrintSize`, `PixelSize`, `PurchasablePrintSize`, `PurchasablePixelSize` Eloquent models.
- Admin API CRUD for global print/pixel size catalogue (no prices).
- Per-purchasable print/pixel size assignment with prices (extend `PurchasableService`, `ShopManagementController`, `UpdatePurchasablePriceRequest`).
- Basket extension: `POST /api/v2/Shop/Basket/Photo` accepts `print_size_id` / `pixel_size_id`; sets `license_type = print` automatically; snapshots dimensions.
- Customer catalogue endpoint: `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes`.
- Checkout extension: shipping address fields, server-side validation, stored on `Order`.
- `Order::canProcessPayment()` shipping address guard.
- `OrderResource` / `OrderItem` resource shipping address sub-object.
- Admin Vue page `PrintPixelSizesAdmin.vue` at `/admin/shop/sizes`.
- Frontend: basket item type selector (reuse existing modal, add button-group/radio); checkout `InfoSection` shipping address block (visible only when basket has prints); `PricesInput.vue` extension for print/pixel size rows.
- Translation strings for new UI elements (English + placeholder in all other locales).
- Feature tests for all new REST endpoints and scenarios.
- Unit tests for `Order::canProcessPayment()` and enum serialisation.

**Out of scope:**
- Automatic image resizing for pixel-size fulfilment (uses existing `download_link` mechanism).
- Third-party print-on-demand integrations.
- Bulk discount or coupon codes.
- Pricing at the global catalogue level (no `price_cents` on `print_sizes` / `pixel_sizes`).

## Dependencies & Interfaces

**Backend:**
- `App\Enum\PurchasableLicenseType` — add `PRINT` case.
- `App\Models\Purchasable` — add `printSizes()` / `pixelSizes()` relations.
- `App\Models\OrderItem` — add new columns and snapshot logic.
- `App\Models\Order` — add shipping address columns and `canProcessPayment()` guard.
- `App\Actions\Shop\PurchasableService` — extend for print/pixel size assignment.
- `App\Actions\Shop\BasketService` — extend for print/pixel basket items.
- `App\Actions\Shop\CheckoutService` — extend for shipping address.
- `routes/api_v2_shop.php` — new routes.

**Frontend:**
- `resources/js/components/webshop/InfoSection.vue` — add shipping address block.
- `resources/js/components/forms/shop-management/PricesInput.vue` — add print/pixel size sections.
- `resources/js/services/shop-management-service.ts` — extend for print/pixel size catalogue.
- `resources/js/services/webshop-service.ts` — extend basket/catalogue calls.
- New Vue page `resources/js/views/admin/shop/PrintPixelSizesAdmin.vue`.

**Testing:**
- `tests/Webshop/` suite (regression — must remain unchanged).
- New test files per increment (see tasks.md).

## Assumptions & Risks

**Assumptions:**
- `order_items.license_type` is currently NOT NULL; adding `PRINT` to the enum is non-breaking for existing rows.
- The existing `purchasable_prices` table and relations are not modified; new join tables are separate.
- All new API routes follow the existing `support:pro` middleware group in `routes/api_v2_shop.php`.
- Paper type is a free-format string (up to 100 chars) on `print_sizes`; no predefined list.

**Risks & Mitigations:**
- **Risk:** Basket `POST /api/v2/Shop/Basket/Photo` currently expects exactly one of `size_variant_type` / `print_size_id` / `pixel_size_id`; adding mutually-exclusive inputs may complicate validation.  
  **Mitigation:** Use `Rule::requiredIf` and `sometimes` with exactly-one-of validation; add dedicated FormRequest for the extended basket add.
- **Risk:** Adding `is_print` and shipping address columns to existing `order_items` and `orders` tables in production requires a zero-downtime migration.  
  **Mitigation:** All new columns nullable or with a default; migrations tested with rollback.
- **Risk:** `PricesInput.vue` extension adds complexity to an already-complex component.  
  **Mitigation:** Consider extracting print/pixel price rows into a child component `PrintSizePricesInput.vue` / `PixelSizePricesInput.vue`.

## Implementation Drift Gate

Before marking complete:
1. Run `php artisan test` — all existing + new tests pass.
2. Run `make phpstan` — PHPStan level 6, zero errors.
3. Run `npm run check` — TypeScript/ESLint clean.
4. Verify all 28 scenarios from the Branch & Scenario Matrix pass.
5. Manually test: basket type selector, checkout address form toggle, admin sizes page CRUD, purchasable print/pixel price assignment.

Evidence recorded in tasks.md verification notes for each increment.

## Increment Map

### I1 – DB Migrations (FR-043-01, FR-043-02, FR-043-05, FR-043-06, FR-043-11, FR-043-12, FR-043-15)

**Goal:** Create all new tables and extend existing ones.

**Steps:**
1. Migration: `create_print_sizes_table` — `id`, `label`, `width`, `height`, `unit` (enum `cm|inch`), `paper_type` (string nullable), `is_active` (boolean default true).
2. Migration: `create_pixel_sizes_table` — `id`, `label`, `width`, `height`, `is_active` (boolean default true).
3. Migration: `create_purchasable_print_sizes_table` — `id`, `purchasable_id` (FK), `print_size_id` (FK), `price_cents` (integer). Unique on `(purchasable_id, print_size_id)`.
4. Migration: `create_purchasable_pixel_sizes_table` — `id`, `purchasable_id` (FK), `pixel_size_id` (FK), `price_cents` (integer). Unique on `(purchasable_id, pixel_size_id)`.
5. Migration: extend `order_items` — add `is_print` (boolean default false), `print_size_id` (nullable FK), `pixel_size_id` (nullable FK), `print_width` (nullable integer), `print_height` (nullable integer), `print_unit` (nullable string), `print_paper_type` (nullable string), `pixel_width` (nullable integer), `pixel_height` (nullable integer).
6. Migration: extend `orders` — add `shipping_street_name`, `shipping_street_number`, `shipping_additional_info`, `shipping_city`, `shipping_post_code`, `shipping_country` (all nullable string).
7. Verify all migrations are reversible with `php artisan migrate:rollback`.

**Exit:** All migrations run and roll back cleanly in test environment.

---

### I2 – Enum Extension (FR-043-10, DO-043-07)

**Goal:** Add `PRINT = 'print'` to `PurchasableLicenseType`.

**Steps:**
1. Edit `app/Enum/PurchasableLicenseType.php` — add `case PRINT = 'print';`.
2. Run `make phpstan` to verify no cast/type errors in existing code.

**Exit:** Enum serialises `'print'`; PHPStan clean; existing tests pass.

---

### I3 – Models: PrintSize, PixelSize (FR-043-01..04, DO-043-01, DO-043-02)

**Goal:** Eloquent models for the global catalogue.

**Steps:**
1. Create `app/Models/PrintSize.php` — fillable: `label`, `width`, `height`, `unit`, `paper_type`, `is_active`. Scope `active()`.
2. Create `app/Models/PixelSize.php` — fillable: `label`, `width`, `height`, `is_active`. Scope `active()`.
3. Add factories `database/factories/PrintSizeFactory.php` and `PixelSizeFactory.php`.

**Exit:** Models created; PHPStan clean.

---

### I4 – Models: PurchasablePrintSize, PurchasablePixelSize (FR-043-05, FR-043-06, DO-043-03, DO-043-04)

**Goal:** Join table models for per-purchasable pricing.

**Steps:**
1. Create `app/Models/PurchasablePrintSize.php` — `purchasable_id`, `print_size_id`, `price_cents` (MoneyCast). BelongsTo `Purchasable` and `PrintSize`.
2. Create `app/Models/PurchasablePixelSize.php` — `purchasable_id`, `pixel_size_id`, `price_cents` (MoneyCast). BelongsTo `Purchasable` and `PixelSize`.
3. Extend `app/Models/Purchasable.php` — add `printSizes()` (HasMany `PurchasablePrintSize`) and `pixelSizes()` (HasMany `PurchasablePixelSize`) relations; load them in `$with`.
4. Add factories for both models.

**Exit:** Relations resolve; PHPStan clean.

---

### I5 – Model: OrderItem & Order Extensions (DO-043-05, DO-043-06)

**Goal:** Add new columns to `OrderItem` and `Order` models and implement snapshot logic and address guard.

**Steps:**
1. Extend `app/Models/OrderItem.php` — add new fillable columns, casts for `print_size_id`/`pixel_size_id` FKs, BelongsTo relations to `PrintSize`/`PixelSize`.
2. Extend `app/Models/Order.php` — add shipping address fillable columns; update `canProcessPayment()` to return `false` when any item has `is_print = true` and required shipping fields are null/empty.
3. Run `make phpstan`.

**Exit:** PHPStan clean; `canProcessPayment()` logic implemented.

---

### I6 – Admin API: PrintSize & PixelSize CRUD (FR-043-01..04, FR-043-20)

**Goal:** REST endpoints for managing the global print/pixel size catalogue.

**Steps:**
1. Create `app/Http/Requests/ShopManagement/PrintSize/CreatePrintSizeRequest.php` and `UpdatePrintSizeRequest.php`.
2. Create `app/Http/Requests/ShopManagement/PixelSize/CreatePixelSizeRequest.php` and `UpdatePixelSizeRequest.php`.
3. Create `app/Http/Resources/Shop/PrintSizeResource.php` and `PixelSizeResource.php`.
4. Create `app/Http/Controllers/Admin/PrintSizeManagementController.php` — `index`, `store`, `update`, `destroy`.
5. Create `app/Http/Controllers/Admin/PixelSizeManagementController.php` — `index`, `store`, `update`, `destroy`.
6. Register routes in `routes/api_v2_shop.php` inside the `support:pro` group.
7. Write feature tests: `tests/Webshop/PrintSizeManagementControllerTest.php` and `PixelSizeManagementControllerTest.php` covering S-043-01..06, S-043-25..27.

**Exit:** All CRUD tests pass; PHPStan clean.

---

### I7 – Purchasable Service & Controller Extension (FR-043-05, FR-043-06)

**Goal:** Extend purchasable create/update to accept and persist print/pixel size assignments with prices.

**Steps:**
1. Extend `app/Actions/Shop/PurchasableService.php` — `syncPrintSizes(Purchasable, array)` and `syncPixelSizes(Purchasable, array)` methods (upsert / delete orphans).
2. Extend `app/Http/Requests/ShopManagement/PurchasablePhotoRequest.php` and `PurchasableAlbumRequest.php` — optional `print_sizes` and `pixel_sizes` arrays.
3. Extend `app/Http/Requests/ShopManagement/UpdatePurchasablePriceRequest.php` — accept `print_sizes` and `pixel_sizes`.
4. Extend `app/Http/Controllers/Admin/ShopManagementController.php` — call `syncPrintSizes`/`syncPixelSizes` in `setPhotoPurchasable`, `setAlbumPurchasable`, and `updatePurchasablePrices`.
5. Extend `app/Http/Resources/Shop/EditablePurchasableResource.php` — include `print_sizes` and `pixel_sizes` with prices.
6. Write feature tests: `tests/Webshop/Purchasables/ShopManagementPrintPixelPricingTest.php` covering S-043-07, S-043-08.

**Exit:** Tests pass; PHPStan clean.

---

### I8 – Customer Catalogue Endpoint (FR-043-17, API-043-01)

**Goal:** Expose active print/pixel sizes with per-purchasable prices.

**Steps:**
1. Create `app/Http/Controllers/Shop/CatalogueSizesController.php` — `index(purchasable_id)` returns active print/pixel sizes assigned to the purchasable with prices.
2. Create `app/Http/Resources/Shop/CatalogueSizeResource.php` (or reuse `PrintSizeResource`/`PixelSizeResource` extended with `price_cents`).
3. Register route `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes` in `routes/api_v2_shop.php`.
4. Write feature tests covering S-043-09, S-043-19, S-043-20, S-043-21.

**Exit:** Tests pass; PHPStan clean.

---

### I9 – Basket Extension (FR-043-07, FR-043-08, FR-043-09, FR-043-11, FR-043-12)

**Goal:** Allow customers to add print/pixel-size items to the basket.

**Steps:**
1. Create `app/Http/Requests/Shop/AddPrintSizeToBasketRequest.php` and `AddPixelSizeToBasketRequest.php` (or extend existing `AddPhotoToBasketRequest` with mutually-exclusive validation).
2. Extend `app/Actions/Shop/BasketService.php` — `addPrintItem(photo_id, print_size_id)` and `addPixelItem(photo_id, pixel_size_id)` methods; set `is_print`, `license_type = PRINT`, snapshot dimensions.
3. Extend basket controller to handle new inputs.
4. Write feature tests: `tests/Webshop/BasketControllerPrintTest.php` covering S-043-10, S-043-11, S-043-12, S-043-19, S-043-20, S-043-21.

**Exit:** Tests pass; PHPStan clean; existing basket tests unchanged.

---

### I10 – Checkout Extension (FR-043-14, FR-043-15, FR-043-19)

**Goal:** Collect and validate shipping address; store on Order.

**Steps:**
1. Extend `app/Http/Requests/Shop/CreateCheckoutSessionRequest.php` — add optional `shipping_*` fields; require when basket has any print item.
2. Extend `app/Actions/Shop/CheckoutService.php` — persist shipping address on `Order` when provided.
3. Write feature tests: `tests/Webshop/Checkout/CheckoutShippingAddressTest.php` covering S-043-13, S-043-14, S-043-15, S-043-16, S-043-23, S-043-24.

**Exit:** Tests pass; PHPStan clean.

---

### I11 – Order Resource Extension (FR-043-16)

**Goal:** Include shipping address in order API response.

**Steps:**
1. Extend `app/Http/Resources/Shop/OrderResource.php` — add `shipping_address` sub-object when any item `is_print = true`.
2. Extend `app/Http/Resources/Shop/OrderItemResource.php` — include `is_print`, print/pixel dimension snapshot fields.
3. Write feature tests: `tests/Webshop/OrderManagement/OrderResourceShippingTest.php` covering S-043-17, S-043-18.

**Exit:** Tests pass; PHPStan clean.

---

### I12 – Unit Tests (FR-043-10, FR-043-19, S-043-23, S-043-24, S-043-28)

**Goal:** Unit-test `Order::canProcessPayment()` and enum serialisation.

**Steps:**
1. Add test methods in a new `tests/Unit/Order/CanProcessPaymentPrintTest.php` class.
2. Add `tests/Unit/Enum/PurchasableLicenseTypeTest.php` for `PRINT` serialisation.

**Exit:** All unit tests pass.

---

### I13 – Frontend: Admin Print/Pixel Sizes Page (FR-043-18, S-043-25..27)

**Goal:** New Vue admin page for managing the global catalogue.

**Steps:**
1. Create `resources/js/views/admin/shop/PrintPixelSizesAdmin.vue` with separate PRINT SIZES and PIXEL SIZES sections.
2. Add service methods to `resources/js/services/shop-management-service.ts` for CRUD.
3. Register admin route `/admin/shop/sizes` in the Vue router.
4. Add menu entry in admin navigation (alongside existing shop management entries).
5. Run `npm run check`.

**Exit:** Page renders; CRUD actions call correct API endpoints; TypeScript compiles.

---

### I14 – Frontend: Basket Item Type Selector (FR-043-07, FR-043-08, FR-043-09, S-043-10..12)

**Goal:** Extend the existing add-to-basket modal with a type selector.

**Steps:**
1. Extend the existing add-to-basket modal/component — add a button-group/radio selector for Digital / Print / Pixel size above the current size picker.
2. When Print or Pixel size is selected, show the appropriate size dropdown (populated from `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes`); hide the license selector (license_type = print, sent by server).
3. When Digital is selected, retain existing size + license selector.
4. Extend `resources/js/services/webshop-service.ts` — add catalogue sizes fetch and print/pixel basket add calls.
5. Run `npm run check`.

**Exit:** Type selector visible; correct API called per type; TypeScript compiles.

---

### I15 – Frontend: Checkout Shipping Address (FR-043-14, S-043-13, S-043-14)

**Goal:** Show shipping address block in `InfoSection` when basket contains print items.

**Steps:**
1. Extend `resources/js/components/webshop/InfoSection.vue` — add a computed `hasPrints` from basket state; conditionally render shipping address fields.
2. Pass shipping address fields in the checkout form submission.
3. Run `npm run check`.

**Exit:** Shipping block visible/hidden correctly; fields submitted to API; TypeScript compiles.

---

### I16 – Frontend: Purchasable Print/Pixel Price Assignment (FR-043-05, FR-043-06)

**Goal:** Extend `PricesInput.vue` (or add sub-components) for per-purchasable print/pixel size pricing.

**Steps:**
1. Create `resources/js/components/forms/shop-management/PrintSizePricesInput.vue` — dropdown selecting from the global catalogue + price input per row.
2. Create `resources/js/components/forms/shop-management/PixelSizePricesInput.vue` — same pattern.
3. Integrate both into the existing purchasable create/edit form alongside the current `PricesInput`.
4. Run `npm run check`.

**Exit:** Print/pixel price rows can be added, edited, removed; correct payload sent to API.

---

### I17 – Translation Strings

**Goal:** Add new UI labels and messages.

**Steps:**
1. Add print/pixel size keys to `lang/en/webshop.php` and `lang/en/dialogs.php` as appropriate.
2. Copy English strings as placeholders to all other locale files in `lang/*/`.
3. Verify no missing keys with `npm run check`.

**Exit:** No missing translation key errors; all locales have placeholders.

---

### I18 – Final Quality Gate (NFR-043-01..06)

**Goal:** Full quality pass before merge.

**Steps:**
1. `vendor/bin/php-cs-fixer fix` — apply PHP formatting.
2. `php artisan test` — all tests pass including new + existing.
3. `make phpstan` — zero errors at level 6.
4. `npm run format` — apply frontend formatting.
5. `npm run check` — TypeScript/ESLint clean.
6. Verify all 28 scenarios from the scenario matrix pass.

**Exit:** All quality gates green; feature ready for code review.

---

## Scenario Tracking

| Scenario ID | Increment(s) | Notes |
|-------------|-------------|-------|
| S-043-01 | I6 | Admin creates print size with cm + paper type |
| S-043-02 | I6 | Admin creates print size with inch, no paper type |
| S-043-03 | I6 | Admin creates pixel size |
| S-043-04 | I6 | Admin updates print size |
| S-043-05 | I6 | Admin deletes print size |
| S-043-06 | I6 | Admin disables print size |
| S-043-07 | I7 | Photographer assigns print size with price |
| S-043-08 | I7 | Photographer assigns pixel size with price |
| S-043-09 | I8 | Customer sees catalogue with per-purchasable prices |
| S-043-10 | I9, I14 | Customer adds print item |
| S-043-11 | I9, I14 | Customer adds pixel-size item |
| S-043-12 | I9, I14 | Customer adds digital item (regression) |
| S-043-13 | I10, I15 | No shipping form for digital-only basket |
| S-043-14 | I10, I15 | Shipping form shown for basket with prints |
| S-043-15 | I10 | Missing shipping fields → 422 |
| S-043-16 | I10 | Complete shipping → order created |
| S-043-17 | I11 | Order with print → shipping address shown |
| S-043-18 | I11 | Order digital-only → no shipping block |
| S-043-19 | I8, I9 | Print size not assigned to purchasable → 422 |
| S-043-20 | I8, I9 | Inactive print size → 422 |
| S-043-21 | I8, I9 | Non-existent pixel size → 404 |
| S-043-22 | I18 | Full regression suite passes |
| S-043-23 | I5, I12 | canProcessPayment false (prints + incomplete address) |
| S-043-24 | I5, I12 | canProcessPayment true (prints + complete address) |
| S-043-25 | I13 | Admin sizes page loads |
| S-043-26 | I13 | Admin adds print size via UI |
| S-043-27 | I13 | Admin toggles active |
| S-043-28 | I2, I12 | PRINT enum serialises/deserialises |

## Analysis Gate

**Status:** Approved.

**Checklist:**
- [x] All requirements in spec are testable
- [x] UI mock-ups reviewed and approved
- [x] Dependencies verified available
- [x] Risk mitigations documented
- [x] Test strategy covers all scenarios
- [x] Open questions Q-043-01 through Q-043-05 resolved and captured in spec

## Exit Criteria

- [ ] All 18 increments complete
- [ ] All 28 scenarios from the Branch & Scenario Matrix pass
- [ ] Full `tests/Webshop/` regression suite passes unchanged
- [ ] New feature tests all pass (S-043-01..27)
- [ ] Unit tests for `canProcessPayment()` and enum serialisation pass
- [ ] Admin print/pixel sizes page renders and supports CRUD
- [ ] Basket item type selector works for digital / print / pixel items
- [ ] Checkout shipping address form shown/hidden correctly
- [ ] Per-purchasable print/pixel size pricing works in UI
- [ ] PHPStan level 6 passes
- [ ] php-cs-fixer applied
- [ ] npm run check passes

## Follow-ups / Backlog

- Consider adding bulk enable/disable for print sizes.
- Future: automatic pixel-size export via image processing queue (explicitly out of scope for this feature).
- Future: per-album/photo print size restrictions (explicitly out of scope).

---

*Last updated: 2026-05-31*
