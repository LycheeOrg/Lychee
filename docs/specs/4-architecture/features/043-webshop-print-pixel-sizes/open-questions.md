# Open Questions – Feature 043

Open questions for [Feature 043](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| Q-043-06 | 043 | High | Album-level basket add for print/pixel sizes — scope unspecified | Open | 2026-05-31 | 2026-05-31 |
| Q-043-07 | 043 | High | `{id}` type in `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes` — purchasable PK, photo_id, or album_id? | Open | 2026-05-31 | 2026-05-31 |
| Q-043-08 | 043 | Medium | `canProcessPayment()` interaction — precedence of shipping-address guard vs existing email/FULL-size checks | Open | 2026-05-31 | 2026-05-31 |
| Q-043-09 | 043 | Medium | `license_type` DB column type — native enum or string? Enum extension may require ALTER TABLE migration | Open | 2026-05-31 | 2026-05-31 |
| Q-043-10 | 043 | Medium | FK cascade policy on `purchasable_print_sizes.print_size_id` / `purchasable_pixel_sizes.pixel_size_id` on delete | Open | 2026-05-31 | 2026-05-31 |
| Q-043-11 | 043 | Low | `is_print = false` yet `license_type = 'print'` for pixel items — should be documented in task notes to prevent confusion | Open | 2026-05-31 | 2026-05-31 |
| Q-043-12 | 043 | Medium | Frontend basket/purchasable component names not identified; blocks I14 and I16 | Open | 2026-05-31 | 2026-05-31 |
| Q-043-13 | 043 | Low | Adding `printSizes`/`pixelSizes` to `Purchasable::$with` — always-eager-loaded vs lazy-load for specific resources | Open | 2026-05-31 | 2026-05-31 |
| Q-043-14 | 043 | Medium | `UpdatePurchasablePriceRequest` — how is the purchasable identified when route has no `{id}` segment? | Open | 2026-05-31 | 2026-05-31 |
| Q-043-15 | 043 | Low | Naming inconsistency: existing `CatalogController` vs new `CatalogueSizesController` | Open | 2026-05-31 | 2026-05-31 |
| Q-043-16 | 043 | Low | Translation key placement: which new strings belong in `webshop.php` vs `dialogs.php`? | Open | 2026-05-31 | 2026-05-31 |
| Q-043-17 | 043 | Medium | `is_print` must be exposed in basket GET response (via `OrderItemResource`) before frontend `hasPrints` computed can work — cross-increment dependency not captured in tasks | Open | 2026-05-31 | 2026-05-31 |

## Question Details

### Q-043-06: Album-level basket add for print/pixel sizes

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** High  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** FR-043-07 and FR-043-08 only define `POST /api/v2/Shop/Basket/Photo` for adding print/pixel items to the basket. The existing `POST /api/v2/Shop/Basket/Album` route allows bulk-adding all photos in an album at a chosen size+license. FR-043-05/06 permit photographers to assign print/pixel sizes to **album-level purchasables** as well as photo-level ones.

**Question:** Can customers add album print/pixel items to the basket via the album basket endpoint? If yes, how does the bulk add work (per-photo print size, or a single selection applied to all photos)? If not, should album-level purchasables be prevented from having print/pixel sizes assigned, or silently ignored at basket time?

**Impact:** Determines whether `BasketService::addAlbumToBasket`, the album basket FormRequest, and the album purchasable management UI require any changes. Affects scope of I7 and I9.

**Blocking tasks:** T-043-22 (`syncPrintSizes`/`syncPixelSizes`), T-043-23 (purchasable request extension), T-043-29 (basket FormRequests), T-043-30 (BasketService extension).

---

### Q-043-07: `{id}` type in `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes`

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** High  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** API-043-01 defines `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes` with the note "within purchasable album scope." The route parameter is `{id}`, but the spec does not specify whether this is the `purchasables.id` (integer PK), the `photo_id` (ULID), or the `album_id` (ULID). The existing customer-facing catalog routes (e.g., `GET /Shop`) use album/photo IDs, not the internal `purchasables.id`.

**Question:** What does `{id}` resolve to — `purchasables.id`, `photo_id`, or `album_id`? Is the scope restricted to a specific album context (matching the `AlbumQueryPolicy`)?

**Impact:** Directly affects the controller lookup in T-043-27 (`CatalogueSizesController`) and the URL construction in the frontend service (T-043-43).

**Blocking tasks:** T-043-27, T-043-28, T-043-43.

---

### Q-043-08: `canProcessPayment()` interaction with existing guards

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** The existing `Order::canProcessPayment()` (app/Models/Order.php:289) already returns `false` in several cases: basket not in checkout-ready state, no payment provider, no email when FULL-size items are present. FR-043-19 adds a new guard: return `false` if any item `is_print = true` AND required shipping fields are null/empty.

**Question:** How do the new and existing guards interact? Specifically:
1. A logged-in user with email, FULL-size items, print items, but no shipping address — should payment be blocked (shipping guard fires)?
2. Is the shipping guard evaluated in addition to, or as a replacement for, the email guard for print items?
3. Should print items also require an email (for shipping confirmation), or is the shipping address alone sufficient?

**Impact:** Affects the implementation of `canProcessPayment()` in T-043-13 and the unit tests in T-043-37.

**Blocking tasks:** T-043-13, T-043-37.

---

### Q-043-09: `license_type` DB column type and enum extension migration

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** `order_items.license_type` is cast to `PurchasableLicenseType` in `OrderItem`. If the database column is a MySQL/PostgreSQL native ENUM, adding `PRINT = 'print'` to the PHP enum is insufficient — an `ALTER TABLE order_items MODIFY COLUMN license_type ENUM('personal','commercial','extended','print')` (MySQL) or similar is required. If the column is a plain `VARCHAR`/`string`, no DB migration is needed for this change. The spec and plan mention only a PHP enum case addition (T-043-07) with no corresponding DB migration.

**Question:** Is `order_items.license_type` stored as a DB-native enum or as a plain string? If it is a DB enum, a migration must be added to T-043-07 or as a separate task.

**Impact:** Missing this step would cause runtime DB errors when the new enum value is persisted. Affects T-043-07 scope.

**Blocking tasks:** T-043-07.

---

### Q-043-10: FK cascade policy on `purchasable_print_sizes` and `purchasable_pixel_sizes`

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** S-043-05 states "Admin deletes a print size → removed; existing order items retain snapshotted data; purchasable assignments cleaned up or orphaned gracefully." T-043-03 and T-043-04 specify a FK from `purchasable_print_sizes.print_size_id → print_sizes.id` and similarly for pixel sizes, but do not specify the `onDelete` behaviour.

**Options:**
- **CASCADE:** Deleting a catalogue entry automatically removes all per-purchasable assignments. Purchasable prices are silently lost.
- **RESTRICT / NO ACTION:** Prevent deletion of catalogue entries that are still in use.
- **SET NULL:** Not applicable (column is non-nullable in the join table).

**Question:** What `onDelete` rule should the FK use? Should the admin UI warn about in-use sizes before deletion?

**Blocking tasks:** T-043-03, T-043-04, T-043-20, T-043-21.

---

### Q-043-11: `is_print = false` with `license_type = 'print'` for pixel-size items

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Low  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** FR-043-08 specifies that pixel-size order items have `is_print = false` (no physical fulfilment required) yet `license_type = 'print'`. This means the `is_print` flag and the license type are not in a 1-to-1 relationship: both physical prints and digital pixel exports share the `'print'` license, but only physical prints have `is_print = true`. This is correct per the spec's intent (pixel items do not need shipping) but is semantically surprising and likely to cause confusion for implementers working across T-043-12, T-043-30, and T-043-35.

**Question:** Is there a preferred place (code comment, task note, or inline spec section) to document this intentional asymmetry so that future contributors are not misled by the name `is_print`? No code change is required — this is purely a documentation/clarity question.

**Blocking tasks:** None (low priority). Relevant to T-043-12, T-043-30, T-043-35.

---

### Q-043-12: Frontend basket modal and purchasable form component names not identified

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** T-043-42 says "existing basket modal component (identify during implementation)" and T-043-47 says "purchasable create/edit form component (identify during implementation)." These are deferred unknowns; the frontend increments I14 and I16 cannot begin until the correct component files are located.

**Question:** What are the exact file paths of (a) the customer-facing add-to-basket modal and (b) the admin purchasable create/edit form? Identifying these before implementation begins will prevent a research step mid-increment and allow I14/I16 to be scoped accurately.

**Suggested pre-work:** Run `grep -r "Basket\|basket" resources/js --include="*.vue" -l` and `grep -r "Purchasable\|purchasable" resources/js/views/admin --include="*.vue" -l` to locate candidates.

**Blocking tasks:** T-043-42, T-043-47.

---

### Q-043-13: `printSizes`/`pixelSizes` in `Purchasable::$with` — always eager-loaded?

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Low  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** The plan (I4) says to add `printSizes()` and `pixelSizes()` relations to `Purchasable::$with`, which currently contains only `['prices']`. Making relations eager-loaded globally means every query that fetches a `Purchasable` (management list, catalog, basket get) will also load all print/pixel size assignments with prices, regardless of whether the caller needs them.

**Question:** Should these relations be added to `$with` (always eager-loaded) or loaded selectively in specific resources? If the global management list or catalog already returns many purchasables, this may produce N+1-equivalent overhead even when eager-loaded.

**Impact:** Performance trade-off; affects T-043-10, T-043-11, and T-043-25 implementation choices.

**Blocking tasks:** T-043-10, T-043-11.

---

### Q-043-14: How is the purchasable identified in `UpdatePurchasablePriceRequest`?

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** The existing route is `PUT /api/v2/Shop/Management/Purchasable/Price` (no `{id}` in the URL — see routes/api_v2_shop.php:47). The spec (I7/T-043-23) extends `UpdatePurchasablePriceRequest` to accept `print_sizes` and `pixel_sizes` arrays for syncing per-purchasable print/pixel size prices. But if the route has no `{id}` segment, how does the server know which `Purchasable` to update?

**Question:** Is the `purchasable_id` passed in the request body? Is there a route change planned that adds `{id}` to the URL? Or is this endpoint already implicitly scoped to the authenticated user's single purchasable? Clarification needed before T-043-23 and T-043-24 are implemented.

**Blocking tasks:** T-043-23, T-043-24, T-043-26.

---

### Q-043-15: Naming inconsistency — `CatalogController` vs `CatalogueSizesController`

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Low  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** The existing customer-facing controller is `App\Http\Controllers\Shop\CatalogController` (American spelling, no 'ue'). The plan (I8/T-043-27) introduces `App\Http\Controllers\Shop\CatalogueSizesController` (British spelling, with 'ue'). The same inconsistency appears in the route path: `GET /api/v2/Shop/Catalogue/Purchasable/{id}/Sizes` uses 'Catalogue' while the existing `GET /Shop` corresponds to `CatalogController`.

**Question:** Should the new controller and route path use `Catalog` (matching the existing convention) or `Catalogue` (matching the spec as written)?

**Blocking tasks:** T-043-27, T-043-28, T-043-40, T-043-43.

---

### Q-043-16: Translation key placement — `webshop.php` vs `dialogs.php`

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Low  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** The plan (I17) mentions both `lang/en/webshop.php` and `lang/en/dialogs.php` as candidate files for new translation strings. The task (T-043-48) only mentions `lang/en/webshop.php`. New UI elements span several surfaces: basket modal labels (likely `dialogs.php`), checkout shipping form labels (likely `webshop.php`), admin sizes page labels, and purchasable form section headers.

**Question:** Which translation keys go in `webshop.php` and which in `dialogs.php`? Should a new section be added to an existing file, or should new keys be appended alongside thematically related existing keys?

**Blocking tasks:** T-043-48, T-043-49.

---

### Q-043-17: `is_print` must be in basket GET response before frontend `hasPrints` can work

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Open  
**Opened:** 2026-05-31  
**Updated:** 2026-05-31  

**Context:** I15 (T-043-44) requires `InfoSection.vue` to conditionally render the shipping address block via a computed `hasPrints` derived from the basket state. For `hasPrints` to work, each basket `OrderItem` in the `GET /api/v2/Shop/Basket/` response must expose the `is_print` field. This field is added to `OrderItemResource` in I11 (T-043-35), which is a later increment.

**Question / Gap:** The cross-increment dependency from I15 on I11 is not captured in tasks.md. I15 cannot be fully implemented (or tested for the address-visible/hidden toggle) until I11 is complete. Should T-043-44 list T-043-35 as a prerequisite? Should I15 be reordered after I11 in the increment map, or should `is_print` be added to the basket GET response separately in an earlier increment?

**Blocking tasks:** T-043-44 is blocked on T-043-35. The tasks.md dependency ordering needs to reflect this.

---

### ~~Q-043-01: Pricing model for print/pixel sizes~~ ✅ RESOLVED

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** High  
**Status:** Resolved — **Option B + no global price**  
**Opened:** 2026-05-31  
**Resolved:** 2026-05-31  

**Resolution:** The global `print_sizes` / `pixel_sizes` catalogue stores dimensions and labels only — no `price_cents`. When a photographer creates or edits a purchasable, they select which print/pixel sizes to offer and set a per-size price, stored in new join tables `purchasable_print_sizes` and `purchasable_pixel_sizes`. This mirrors the existing per-purchasable pricing model while keeping the catalogue clean. Captured in FR-043-01, FR-043-02, FR-043-05, FR-043-06, FR-043-17, and DO-043-03, DO-043-04 in the spec.

---

### ~~Q-043-02: License type for print and pixel order items~~ ✅ RESOLVED

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** High  
**Status:** Resolved — **New license type `print`**  
**Opened:** 2026-05-31  
**Resolved:** 2026-05-31  

**Resolution:** A new `PurchasableLicenseType::PRINT = 'print'` enum case is introduced. Print and pixel-size order items carry this license type, set automatically by the server. Customers do not choose between personal/commercial/extended for these item types. Captured in FR-043-10, DO-043-07, and `App\Enum\PurchasableLicenseType` in the spec.

---

### ~~Q-043-03: Fulfillment model for pixel-size items~~ ✅ RESOLVED

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Resolved — **Option A**  
**Opened:** 2026-05-31  
**Resolved:** 2026-05-31  

**Resolution:** Pixel-size items use the same manual `download_link` mechanism as FULL. No new fulfilment code path is needed. Captured in FR-043-13 in the spec.

---

### ~~Q-043-04: Print/pixel catalogue availability scope~~ ✅ RESOLVED

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Medium  
**Status:** Resolved — **Option B (per-purchasable opt-in)**  
**Opened:** 2026-05-31  
**Resolved:** 2026-05-31  

**Resolution:** The admin defines a global catalogue of print/pixel sizes. Each purchasable's owner selects which sizes to offer and sets prices via the purchasable management UI. Only sizes explicitly assigned to a purchasable are available to customers for that item. Captured in FR-043-05, FR-043-06, FR-043-17 in the spec.

---

### ~~Q-043-05: Lychee SE gating for print/pixel features~~ ✅ RESOLVED

**Feature:** 043 – Webshop Print & Pixel Sizes  
**Priority:** Low  
**Status:** Resolved — **Option A**  
**Opened:** 2026-05-31  
**Resolved:** 2026-05-31  

**Resolution:** All new routes use the existing `support:pro` middleware group, consistent with the rest of the webshop. Captured in FR-043-20 in the spec.
