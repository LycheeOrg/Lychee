# Feature 043 Tasks – Webshop Print & Pixel Sizes

_Status: Implemented_  
_Last updated: 2026-06-04_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – DB Migrations

- [x] T-043-01 – Create `print_sizes` migration (FR-043-01).  
  _Intent:_ Persist global print size catalogue (no price).  
  _Files:_ `database/migrations/YYYY_MM_DD_create_print_sizes_table.php`  
  _Verification commands:_  
  - `php artisan migrate` — runs without error  
  - `php artisan migrate:rollback` — reverts cleanly  
  _Notes:_ Columns: `id`, `label` (string 100), `width` (unsignedInteger), `height` (unsignedInteger), `unit` (enum `cm,inch`), `paper_type` (string 100 nullable), `is_active` (boolean default true).

- [x] T-043-02 – Create `pixel_sizes` migration (FR-043-02).  
  _Intent:_ Persist global pixel size catalogue (no price).  
  _Files:_ `database/migrations/YYYY_MM_DD_create_pixel_sizes_table.php`  
  _Verification commands:_  
  - `php artisan migrate && php artisan migrate:rollback`  
  _Notes:_ Columns: `id`, `label` (string 100), `width` (unsignedInteger), `height` (unsignedInteger), `is_active` (boolean default true).

- [x] T-043-03 – Create `purchasable_print_sizes` migration (FR-043-05).  
  _Intent:_ Per-purchasable print size assignment with price.  
  _Files:_ `database/migrations/YYYY_MM_DD_create_purchasable_print_sizes_table.php`  
  _Verification commands:_  
  - `php artisan migrate && php artisan migrate:rollback`  
  _Notes:_ Columns: `id`, `purchasable_id` (FK → purchasables), `print_size_id` (FK → print_sizes), `price_cents` (integer). Unique constraint on `(purchasable_id, print_size_id)`.

- [x] T-043-04 – Create `purchasable_pixel_sizes` migration (FR-043-06).  
  _Intent:_ Per-purchasable pixel size assignment with price.  
  _Files:_ `database/migrations/YYYY_MM_DD_create_purchasable_pixel_sizes_table.php`  
  _Verification commands:_  
  - `php artisan migrate && php artisan migrate:rollback`  
  _Notes:_ Columns: `id`, `purchasable_id` (FK → purchasables), `pixel_size_id` (FK → pixel_sizes), `price_cents` (integer). Unique constraint on `(purchasable_id, pixel_size_id)`.

- [x] T-043-05 – Extend `order_items` migration (FR-043-11, FR-043-12).  
  _Intent:_ Add print/pixel snapshot columns and `is_print` flag.  
  _Files:_ `database/migrations/YYYY_MM_DD_extend_order_items_for_print.php`  
  _Verification commands:_  
  - `php artisan migrate && php artisan migrate:rollback`  
  _Notes:_ New nullable columns: `is_print` (boolean default false), `print_size_id`, `pixel_size_id` (nullable FKs), `print_width`, `print_height`, `pixel_width`, `pixel_height` (nullable unsignedInteger), `print_unit` (nullable string), `print_paper_type` (nullable string).

- [x] T-043-06 – Extend `orders` migration (FR-043-15).  
  _Intent:_ Add shipping address columns to orders.  
  _Files:_ `database/migrations/YYYY_MM_DD_extend_orders_for_shipping.php`  
  _Verification commands:_  
  - `php artisan migrate && php artisan migrate:rollback`  
  _Notes:_ New nullable columns: `shipping_street_name`, `shipping_street_number`, `shipping_additional_info`, `shipping_city`, `shipping_post_code`, `shipping_country` (all string nullable).

### I2 – Enum Extension

- [x] T-043-07 – Add `PRINT = 'print'` to `PurchasableLicenseType` (FR-043-10, S-043-28).  
  _Intent:_ Introduce dedicated license type for print/pixel-size items.  
  _Files:_ `app/Enum/PurchasableLicenseType.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  - `php artisan test` — existing tests still pass  
  _Notes:_ Add `case PRINT = 'print';` after the existing cases.

### I3 – Models: PrintSize & PixelSize

- [x] T-043-08 – Create `PrintSize` model (FR-043-01, DO-043-01).  
  _Intent:_ Eloquent model for the global print size catalogue.  
  _Files:_ `app/Models/PrintSize.php`, `database/factories/PrintSizeFactory.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  _Notes:_ Fillable: `label`, `width`, `height`, `unit`, `paper_type`, `is_active`. Add `active()` local scope.

- [x] T-043-09 – Create `PixelSize` model (FR-043-02, DO-043-02).  
  _Intent:_ Eloquent model for the global pixel size catalogue.  
  _Files:_ `app/Models/PixelSize.php`, `database/factories/PixelSizeFactory.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  _Notes:_ Fillable: `label`, `width`, `height`, `is_active`. Add `active()` local scope.

### I4 – Models: PurchasablePrintSize & PurchasablePixelSize

- [x] T-043-10 – Create `PurchasablePrintSize` model and extend `Purchasable` (FR-043-05, DO-043-03).  
  _Intent:_ Join table model for per-purchasable print size pricing.  
  _Files:_ `app/Models/PurchasablePrintSize.php`, `database/factories/PurchasablePrintSizeFactory.php`, `app/Models/Purchasable.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  _Notes:_ `price_cents` cast via `MoneyCast`. Add `printSizes()` HasMany on `Purchasable`; load in `$with`.

- [x] T-043-11 – Create `PurchasablePixelSize` model and extend `Purchasable` (FR-043-06, DO-043-04).  
  _Intent:_ Join table model for per-purchasable pixel size pricing.  
  _Files:_ `app/Models/PurchasablePixelSize.php`, `database/factories/PurchasablePixelSizeFactory.php`, `app/Models/Purchasable.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  _Notes:_ `price_cents` cast via `MoneyCast`. Add `pixelSizes()` HasMany on `Purchasable`; load in `$with`.

### I5 – OrderItem & Order Extensions

- [x] T-043-12 – Extend `OrderItem` model (DO-043-05, FR-043-12).  
  _Intent:_ Add new fillable columns and BelongsTo relations for print/pixel items.  
  _Files:_ `app/Models/OrderItem.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  _Notes:_ Add `is_print`, `print_size_id`, `pixel_size_id` (nullable FK BelongsTo), snapshot columns, `print_unit`, `print_paper_type`. Cast `is_print` as boolean.

- [x] T-043-13 – Extend `Order` model + `canProcessPayment()` guard (DO-043-06, FR-043-15, FR-043-19, S-043-23, S-043-24).  
  _Intent:_ Add shipping address fields and enforce them when prints present.  
  _Files:_ `app/Models/Order.php`  
  _Verification commands:_  
  - `make phpstan` — no errors  
  _Notes:_ `canProcessPayment()` returns `false` when any item `is_print = true` AND any of `shipping_street_name`, `shipping_city`, `shipping_post_code`, `shipping_country` is null/empty.

### I6 – Admin API: PrintSize & PixelSize CRUD

- [x] T-043-14 – Create FormRequests for PrintSize CRUD (FR-043-01, FR-043-03, FR-043-04).  
  _Intent:_ Validate admin input for global print size management.  
  _Files:_ `app/Http/Requests/ShopManagement/PrintSize/CreatePrintSizeRequest.php`, `UpdatePrintSizeRequest.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-15 – Create FormRequests for PixelSize CRUD (FR-043-02, FR-043-03, FR-043-04).  
  _Intent:_ Validate admin input for global pixel size management.  
  _Files:_ `app/Http/Requests/ShopManagement/PixelSize/CreatePixelSizeRequest.php`, `UpdatePixelSizeRequest.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-16 – Create `PrintSizeResource` and `PixelSizeResource` (FR-043-02, FR-043-18).  
  _Intent:_ API response format for catalogue entries (no price).  
  _Files:_ `app/Http/Resources/Shop/PrintSizeResource.php`, `PixelSizeResource.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-17 – Create `PrintSizeManagementController` (FR-043-01, FR-043-03, FR-043-04, FR-043-20).  
  _Intent:_ Admin CRUD controller for print sizes.  
  _Files:_ `app/Http/Controllers/Admin/PrintSizeManagementController.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=PrintSizeManagementControllerTest`  

- [x] T-043-18 – Create `PixelSizeManagementController` (FR-043-02, FR-043-03, FR-043-04, FR-043-20).  
  _Intent:_ Admin CRUD controller for pixel sizes.  
  _Files:_ `app/Http/Controllers/Admin/PixelSizeManagementController.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=PixelSizeManagementControllerTest`  

- [x] T-043-19 – Register admin size routes (API-043-02..09, FR-043-20).  
  _Intent:_ Expose print/pixel size CRUD under `support:pro` middleware.  
  _Files:_ `routes/api_v2_shop.php`  
  _Verification commands:_  
  - `php artisan route:list | grep PrintSize`  
  - `php artisan route:list | grep PixelSize`  

- [x] T-043-20 – Write feature tests for PrintSize management (S-043-01, S-043-02, S-043-04, S-043-05, S-043-06, S-043-25, S-043-26, S-043-27).  
  _Intent:_ Cover CRUD scenarios for print sizes.  
  _Files:_ `tests/Webshop/PrintSizeManagementControllerTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=PrintSizeManagementControllerTest`  

- [x] T-043-21 – Write feature tests for PixelSize management (S-043-03, S-043-06).  
  _Intent:_ Cover CRUD scenarios for pixel sizes.  
  _Files:_ `tests/Webshop/PixelSizeManagementControllerTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=PixelSizeManagementControllerTest`  

### I7 – Purchasable Service & Controller Extension

- [x] T-043-22 – Extend `PurchasableService` with `syncPrintSizes` / `syncPixelSizes` (FR-043-05, FR-043-06).  
  _Intent:_ Persist per-purchasable print/pixel size assignments with prices.  
  _Files:_ `app/Actions/Shop/PurchasableService.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-23 – Extend purchasable request classes for print/pixel sizes (FR-043-05, FR-043-06).  
  _Intent:_ Accept `print_sizes` and `pixel_sizes` arrays in purchasable create/update requests.  
  _Files:_ `app/Http/Requests/ShopManagement/PurchasablePhotoRequest.php`, `PurchasableAlbumRequest.php`, `UpdatePurchasablePriceRequest.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-24 – Extend `ShopManagementController` to call sync methods (FR-043-05, FR-043-06).  
  _Intent:_ Wire print/pixel size sync into purchasable create/update flow.  
  _Files:_ `app/Http/Controllers/Admin/ShopManagementController.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=ShopManagementControllerTest`  

- [x] T-043-25 – Extend `EditablePurchasableResource` to include print/pixel sizes.  
  _Intent:_ Return assigned print/pixel sizes with prices in the management API response.  
  _Files:_ `app/Http/Resources/Shop/EditablePurchasableResource.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-26 – Write feature tests for purchasable print/pixel pricing (S-043-07, S-043-08).  
  _Intent:_ Verify per-purchasable assignment is persisted and returned correctly.  
  _Files:_ `tests/Webshop/Purchasables/ShopManagementPrintPixelPricingTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=ShopManagementPrintPixelPricingTest`  

### I8 – Customer Catalogue Endpoint

- [x] T-043-27 – Create `CatalogueSizesController` and route (FR-043-17, API-043-01, FR-043-20).  
  _Intent:_ Return active, per-purchasable print/pixel sizes with prices.  
  _Files:_ `app/Http/Controllers/Shop/CatalogueSizesController.php`, `routes/api_v2_shop.php`  
  _Verification commands:_  
  - `make phpstan`  
  - `php artisan test --filter=CatalogueSizesControllerTest`  

- [x] T-043-28 – Write feature tests for catalogue sizes endpoint (S-043-09, S-043-19, S-043-20, S-043-21).  
  _Intent:_ Verify customer can retrieve sizes and gets errors for invalid inputs.  
  _Files:_ `tests/Webshop/CatalogueSizesControllerTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=CatalogueSizesControllerTest`  

### I9 – Basket Extension

- [x] T-043-29 – Create basket FormRequests for print/pixel items (FR-043-07, FR-043-08).  
  _Intent:_ Validate mutually exclusive basket inputs.  
  _Files:_ `app/Http/Requests/Shop/AddPhotoToBasketRequest.php` (or new split requests)  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-30 – Extend `BasketService` with `addPrintItem` / `addPixelItem` (FR-043-07, FR-043-08, FR-043-11, FR-043-12).  
  _Intent:_ Create OrderItems for print/pixel purchases with snapshots and license_type = print.  
  _Files:_ `app/Actions/Shop/BasketService.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-31 – Write feature tests for basket print/pixel items (S-043-10, S-043-11, S-043-12, S-043-19, S-043-20, S-043-21).  
  _Intent:_ Verify basket add for print/pixel items and rejection paths.  
  _Files:_ `tests/Webshop/BasketControllerPrintTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=BasketControllerPrintTest`  

### I10 – Checkout Extension

- [x] T-043-32 – Extend `CreateCheckoutSessionRequest` with shipping address (FR-043-14, FR-043-15).  
  _Intent:_ Require shipping fields when basket has print items.  
  _Files:_ `app/Http/Requests/Shop/CreateCheckoutSessionRequest.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-33 – Extend `CheckoutService` to store shipping address (FR-043-15).  
  _Intent:_ Persist shipping address on Order before payment initiation.  
  _Files:_ `app/Actions/Shop/CheckoutService.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-34 – Write feature tests for checkout shipping address (S-043-13, S-043-14, S-043-15, S-043-16, S-043-23, S-043-24).  
  _Intent:_ Cover shipping form visibility, validation, and storage.  
  _Files:_ `tests/Webshop/Checkout/CheckoutShippingAddressTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=CheckoutShippingAddressTest`  

### I11 – Order Resource Extension

- [x] T-043-35 – Extend `OrderResource` and `OrderItemResource` (FR-043-16).  
  _Intent:_ Include shipping address and print/pixel item details in order API response.  
  _Files:_ `app/Http/Resources/Shop/OrderResource.php`, `app/Http/Resources/Shop/OrderItemResource.php`  
  _Verification commands:_  
  - `make phpstan`  

- [x] T-043-36 – Write feature tests for order shipping address display (S-043-17, S-043-18).  
  _Intent:_ Verify shipping address included/excluded based on print items.  
  _Files:_ `tests/Webshop/OrderManagement/OrderResourceShippingTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=OrderResourceShippingTest`  

### I12 – Unit Tests

- [x] T-043-37 – Unit test `Order::canProcessPayment()` (S-043-23, S-043-24).  
  _Intent:_ Verify payment guard logic for print + shipping address.  
  _Files:_ `tests/Unit/Order/CanProcessPaymentPrintTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=CanProcessPaymentPrintTest`  

- [x] T-043-38 – Unit test `PurchasableLicenseType::PRINT` enum (S-043-28).  
  _Intent:_ Verify serialisation and deserialisation of new enum case.  
  _Files:_ `tests/Unit/Enum/PurchasableLicenseTypeTest.php`  
  _Verification commands:_  
  - `php artisan test --filter=PurchasableLicenseTypeTest`  

### I13 – Frontend: Admin Print/Pixel Sizes Page

- [x] T-043-39 – Create admin sizes Vue page scaffold (FR-043-18, S-043-25).  
  _Intent:_ New page at `/admin/shop/sizes` for managing global catalogue.  
  _Files:_ `resources/js/views/admin/shop/PrintPixelSizesAdmin.vue`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-043-40 – Add print/pixel size service methods (API-043-02..09).  
  _Intent:_ Frontend service calls for admin CRUD.  
  _Files:_ `resources/js/services/shop-management-service.ts`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-043-41 – Register admin route and nav entry.  
  _Intent:_ Make page reachable from admin navigation.  
  _Files:_ Vue router file, admin navigation component  
  _Verification commands:_  
  - `npm run check`  
  - Manual: navigate to `/admin/shop/sizes`  

### I14 – Frontend: Basket Item Type Selector

- [x] T-043-42 – Extend basket modal with item type selector (FR-043-07, FR-043-08, FR-043-09).  
  _Intent:_ Add Digital / Print / Pixel type toggle to existing add-to-basket modal.  
  _Files:_ Existing basket modal component (identify during implementation)  
  _Verification commands:_  
  - `npm run check`  
  - Manual: verify type selector shows/hides correct fields  

- [x] T-043-43 – Add catalogue sizes fetch and print/pixel basket add service calls.  
  _Intent:_ Fetch per-purchasable sizes from API; send correct basket payload per type.  
  _Files:_ `resources/js/services/webshop-service.ts`  
  _Verification commands:_  
  - `npm run check`  

### I15 – Frontend: Checkout Shipping Address

- [x] T-043-44 – Add shipping address block to `InfoSection.vue` (FR-043-14, S-043-13, S-043-14).  
  _Intent:_ Show/hide shipping fields based on basket print items.  
  _Files:_ `resources/js/components/webshop/InfoSection.vue`  
  _Verification commands:_  
  - `npm run check`  
  - Manual: basket with print → address block visible; digital-only → hidden  

### I16 – Frontend: Purchasable Print/Pixel Price Assignment

- [x] T-043-45 – Create `PrintSizePricesInput.vue` component (FR-043-05).  
  _Intent:_ Select from global catalogue and set price per print size for a purchasable.  
  _Files:_ `resources/js/components/forms/shop-management/PrintSizePricesInput.vue`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-043-46 – Create `PixelSizePricesInput.vue` component (FR-043-06).  
  _Intent:_ Select from global catalogue and set price per pixel size for a purchasable.  
  _Files:_ `resources/js/components/forms/shop-management/PixelSizePricesInput.vue`  
  _Verification commands:_  
  - `npm run check`  

- [x] T-043-47 – Integrate print/pixel price inputs into purchasable create/edit form.  
  _Intent:_ Wire new sub-components into the purchasable management UI.  
  _Files:_ Purchasable create/edit form component (identify during implementation)  
  _Verification commands:_  
  - `npm run check`  
  - Manual: add/remove print/pixel prices in purchasable form  

### I17 – Translation Strings

- [x] T-043-48 – Add English translation strings for print/pixel sizes UI.  
  _Intent:_ Labels, placeholders, and messages for new UI elements.  
  _Files:_ `lang/en/webshop.php`  
  _Verification commands:_  
  - `npm run check`  
  - `grep -r "print_size" lang/en/`  

- [x] T-043-49 – Copy translation string placeholders to all other locales.  
  _Intent:_ Prevent missing-key errors in non-English locales.  
  _Files:_ `lang/*/webshop.php` (all locales)  
  _Verification commands:_  
  - `grep -rl "print_size" lang/ | wc -l` — should equal total number of locales  

### I18 – Final Quality Gate

- [x] T-043-50 – Run full backend quality gate (NFR-043-01..06, S-043-22).  
  _Intent:_ Verify all PHP code meets quality standards and all tests pass.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test` — all tests pass  
  - `make phpstan` — level 6, zero errors  

- [x] T-043-51 – Run full frontend quality gate.  
  _Intent:_ Verify all Vue/TypeScript code meets quality standards.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  

## Notes / TODOs

- None yet. Add notes here as issues arise during implementation.

## Progress Summary

- **Total tasks:** 51
- **Completed:** 0
- **In progress:** 0
- **Blocked:** 0

---

*Last updated: 2026-05-31*
