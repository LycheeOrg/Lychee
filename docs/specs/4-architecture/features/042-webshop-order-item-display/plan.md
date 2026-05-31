# Feature Plan 042 – Photo Display Enrichment

_Linked specification:_ `docs/specs/4-architecture/features/042-webshop-order-item-display/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-05-31

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

An administrator or customer opening an order detail page can immediately identify each purchased item by its album context and a thumbnail. Admin users navigating the duplicate-finder or moderation queue can click a photo's title to jump directly to the album page. Success is measured by:

- Every order item row in `OrderDownload.vue` shows a 48 × 48 thumbnail (or placeholder) and an album title (or "Unknown album").
- `OrderItemResource` exposes `album_title` and `thumb_url` with no N+1 queries.
- Photo title cells in `DuplicateLine.vue` and `Moderation.vue` render as a clickable `RouterLink` (or appropriate fallback) via a shared `PhotoTitleLink.vue` component.
- `Duplicate.album_id` is nullable in the PHP resource.
- PHPStan 0 errors, php-cs-fixer clean, all tests pass, `npm run check` exits 0, `npm run lint` exits 0.

## Scope Alignment

- **In scope (Part A – Webshop Order Item Display):**
  - Extend `OrderItemResource` with `album_title` and `thumb_url` fields (FR-042-01, FR-042-02).
  - Update `OrderResource::fromModel()` to eager-load `items.photo.size_variants` and `items.album` (FR-042-03, NFR-042-01, NFR-042-02).
  - Update `OrderDownload.vue` to render the thumbnail and album title (FR-042-04, FR-042-05).
  - Add `webshop.orderDownload.unknownAlbum` i18n key (FR-042-06).
  - Backend unit and feature tests (all scenarios S-042-01 through S-042-07).

- **In scope (Part B – Admin Maintenance Photo Title Links):**
  - Make `Duplicate.album_id` and `Duplicate.album_title` nullable in the PHP resource (FR-042-12).
  - Create `PhotoTitleLink.vue` reusable component with three render states (FR-042-07, FR-042-08, FR-042-09, NFR-042-09).
  - Update `DuplicateLine.vue` to use `PhotoTitleLink` for the photo-title cell (FR-042-10).
  - Update `Moderation.vue` to use `PhotoTitleLink` for the title column (FR-042-11).

- **Full quality gate (NFR-042-03 through NFR-042-09).**

- **Out of scope:**
  - Modifying `OrderList.vue`.
  - Adding new database columns or migrations.
  - Showing additional photo metadata (EXIF, description, tags).
  - Caching or pre-computing thumbnail URLs.
  - Any other admin or public-facing views beyond `DuplicateLine.vue` and `Moderation.vue`.

## Dependencies & Interfaces

- `app/Http/Resources/Shop/OrderItemResource.php` — DTO to extend (Part A).
- `app/Http/Resources/Shop/OrderResource.php` — eager-load strategy to update (Part A).
- `app/Models/OrderItem.php` — existing `photo()` and `album()` relations (Part A).
- `app/Models/Extensions/Thumb.php` — `sizeVariantsFilter()` for constraining variant loads (Part A).
- `app/Enum/SizeVariantType.php` — `THUMB` constant for size variant filtering (Part A).
- `resources/js/views/webshop/OrderDownload.vue` — frontend view (Part A).
- `app/Http/Resources/Models/Duplicates/Duplicate.php` — `album_id` / `album_title` to be made nullable (Part B).
- `resources/js/components/maintenance/DuplicateLine.vue` — photo-title cell to update (Part B).
- `resources/js/views/admin/Moderation.vue` — title column to update (Part B).
- TypeScript type generation pipeline (`php artisan typescript:transform`) — run after PHP resource changes.
- `lang/` i18n files.
- `tests/Feature_v2/` — existing PHPUnit test suite using `BaseApiWithDataTest`.

## Assumptions & Risks

- **Assumptions:**
  - `OrderItem` records always carry a valid `album_id` at creation time (see `OrderService::addPhotoToOrder`).
  - The `SizeVariant` model exposes a `url` attribute suitable for use as `<img src>`.
  - The TypeScript transformer regenerates types as part of the normal frontend build cycle.
  - Existing `OrderItemFactory` and photo factories create sufficient fixture data for the new tests.
  - The `album` named route with `albumId` and `photoId` params exists in Vue Router (verified in `DuplicateLine.vue`).
  - For `Duplicate` entries produced by the current query, `album_id` is always non-null in practice; the nullability change is defensive.

- **Risks / Mitigations:**
  - **TypeScript transformer command:** The exact artisan command for type generation may differ. Verify with `php artisan list | grep typescript` before I3. If unavailable, the TypeScript interface must be updated manually.
  - **Eager-load depth:** Loading `items.photo.size_variants` inside `OrderResource::fromModel()` adds SQL joins. Mitigated by constraining size variants to THUMB-category types only (NFR-042-02) and confirming with query-count assertion (S-042-07).
  - **Deleted photo / album:** Relations return `null` after deletion; both fields default to `null` in `fromModel()`. Covered by S-042-02, S-042-04.
  - **TypeScript drift (Part B):** If the TypeScript transformer is not run after the `Duplicate.php` change, `npm run check` will fail. Mitigation: run transformer in the same increment (I7).

## Implementation Drift Gate

After each increment, run:

```bash
vendor/bin/php-cs-fixer fix
php artisan test
make phpstan
```

After frontend changes, additionally run:

```bash
npm run format
npm run check
npm run lint
```

Record results in the Scenario Tracking table below.

## Increment Map

### Part A – Webshop Order Item Display

### I1 – Extend `OrderItemResource` (≤30 min)

- _Goal:_ Add `album_title` and `thumb_url` to the resource DTO (FR-042-01, FR-042-02, DO-042-01).
- _Preconditions:_ None.
- _Steps:_
  1. Open `app/Http/Resources/Shop/OrderItemResource.php`.
  2. Add `public ?string $album_title` and `public ?string $thumb_url` to the constructor parameter list and the `fromModel()` factory method.
  3. In `fromModel()`, resolve `album_title` from `$item->album?->title`.
  4. In `fromModel()`, resolve `thumb_url` from `$item->photo?->size_variants->getSizeVariant(SizeVariantType::THUMB)?->url` — returning `null` if any link in the chain is absent.
  5. Add the required `use` statements (`SizeVariantType`).
  6. Run `vendor/bin/php-cs-fixer fix` and `make phpstan`.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`
- _Exit:_ `OrderItemResource` has both new fields; PHPStan 0 errors; php-cs-fixer clean.

### I2 – Update eager-loading in `OrderResource` (≤30 min)

- _Goal:_ Load album and photo (thumb-only size variants) without N+1 queries (FR-042-03, NFR-042-01, NFR-042-02).
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Open `app/Http/Resources/Shop/OrderResource.php`.
  2. In `fromModel()`, locate the block that conditionally calls `$order->load('items.size_variant')` for `CLOSED` orders.
  3. Extend the load to always include `items.album` and `items.photo.size_variants` (filtered to THUMB-category variants via an inline `whereIn` using `Thumb::sizeVariantsFilter()` or the equivalent array `[SizeVariantType::SMALL, SizeVariantType::SMALL2X, SizeVariantType::THUMB, SizeVariantType::THUMB2X, SizeVariantType::PLACEHOLDER]`).
  4. Ensure the load is unconditional — not gated on `CLOSED` status — since album and thumbnail display is needed on the order detail page for all statuses.
  5. Run `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`
- _Exit:_ Relations are eager-loaded; test suite passes; PHPStan 0 errors.

### I3 – Backend tests (≤40 min)

- _Goal:_ Cover all backend scenarios with failing tests first, then confirm they pass (S-042-01 through S-042-07).
- _Preconditions:_ I2 complete.
- _Steps:_
  1. **Write tests first (failing).** In `tests/` (unit or `Feature_v2`), add test methods for:
     - S-042-01: `album_title` populated from existing album.
     - S-042-02: `album_title` is `null` when album deleted.
     - S-042-03: `thumb_url` populated when THUMB variant exists.
     - S-042-04: `thumb_url` is `null` when photo deleted.
     - S-042-05: `thumb_url` is `null` when photo has no THUMB variant.
     - S-042-06: Both fields non-null in the full happy path.
     - S-042-07: `GET /api/v2/Order/{id}` response includes `album_title` and `thumb_url` keys in each item; query count does not grow with item count.
  2. Confirm new tests fail before implementation changes.
  3. Run `php artisan test --filter=<TestClassName>` to confirm tests are green after I1+I2.
  4. Run full suite: `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
- _Commands:_ `php artisan test --filter=<TestClassName>`, `php artisan test`, `make phpstan`, `vendor/bin/php-cs-fixer fix`
- _Exit:_ All new tests green; no regressions; PHPStan 0 errors.

### I4 – Frontend: i18n + TypeScript types (≤20 min)

- _Goal:_ Add the `unknownAlbum` translation key and refresh TypeScript types (FR-042-06, NFR-042-06).
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Add `"unknownAlbum": "Unknown album"` to `lang/en/` (or equivalent JSON file) under the `webshop.orderDownload` namespace, following the existing key pattern.
  2. Run `php artisan typescript:transform` (or equivalent) to regenerate the TypeScript interface for `OrderItemResource`. Confirm `album_title: string | null` and `thumb_url: string | null` appear in the generated file.
  3. If the TypeScript transformer is unavailable, manually add the two fields to the interface definition and note this in the tasks.
  4. Run `npm run format`, `npm run check`.
- _Commands:_ `php artisan typescript:transform` (if available), `npm run format`, `npm run check`
- _Exit:_ i18n key added; TypeScript types include new fields; `npm run check` exits 0.

### I5 – Frontend: update `OrderDownload.vue` (≤40 min)

- _Goal:_ Render thumbnail and album title in the order item row (FR-042-04, FR-042-05, UI-042-01 through UI-042-04).
- _Preconditions:_ I4 complete.
- _Steps:_
  1. Open `resources/js/views/webshop/OrderDownload.vue`.
  2. In the `v-for="item in order.items"` loop, add a thumbnail `<img>` element:
     - `v-if="item.thumb_url"` branch: `<img :src="item.thumb_url" loading="lazy" class="w-12 h-12 object-cover rounded" />`
     - `v-else` branch: `<i class="pi pi-image text-muted-color text-2xl w-12 h-12 flex items-center justify-center" />`
  3. Below the photo title `RouterLink`, add a line for album title:
     - `<div class="text-sm text-muted-color">{{ item.album_title ?? $t('webshop.orderDownload.unknownAlbum') }}</div>`
  4. Adjust the flex layout of the item row to accommodate the thumbnail column (align thumbnail with the text block to its right).
  5. Run `npm run format`, `npm run check`.
- _Commands:_ `npm run format`, `npm run check`
- _Exit:_ Thumbnail and album title visible in order detail; `npm run check` exits 0; layout consistent with existing PrimeVue style.

### I6 – Quality gates & documentation (≤20 min)

- _Goal:_ Full pipeline green; docs updated.
- _Preconditions:_ I5 complete.
- _Steps:_
  1. Run complete quality gate: `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`.
  2. Update `docs/specs/4-architecture/shop-architecture.md` to note that `OrderItemResource` now includes `album_title` and `thumb_url`.
  3. Update `docs/specs/4-architecture/roadmap.md`: move Feature 042 from Active to Completed.
  4. Update `docs/specs/_current-session.md`.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `npm run format`, `npm run check`, `php artisan test`, `make phpstan`
- _Exit:_ All gates green; docs updated.

### Part B – Admin Maintenance Photo Title Links

### I7 – Make `Duplicate` album fields nullable (≤20 min)

- _Goal:_ Update `Duplicate.php` so `album_id` and `album_title` are `?string`; regenerate TypeScript types (FR-042-12, S-042-14).
- _Preconditions:_ None (can run in parallel with Part A or after I6).
- _Steps:_
  1. Open `app/Http/Resources/Models/Duplicates/Duplicate.php`.
  2. Change `public string $album_id` → `public ?string $album_id`.
  3. Change `public string $album_title` → `public ?string $album_title`.
  4. Update `fromModel()` to propagate null values with null-safe operators.
  5. Run `php artisan typescript:transform` to regenerate `App.Http.Resources.Models.Duplicates.Duplicate`. Confirm `album_id: string | null` in the generated interface.
  6. Run `vendor/bin/php-cs-fixer fix`, `make phpstan`, `php artisan test`.
- _Commands:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`, `php artisan test`, `npm run check`
- _Exit:_ PHPStan exits 0, php-cs-fixer exits 0, all PHP tests pass, `npm run check` exits 0.

### I8 – Create `PhotoTitleLink.vue` component (≤30 min)

- _Goal:_ Implement the reusable three-state component (FR-042-07, FR-042-08, FR-042-09, NFR-042-09, S-042-08, S-042-09, S-042-10).
- _Preconditions:_ I7 complete (TypeScript types updated).
- _Steps:_
  1. Create `resources/js/components/maintenance/PhotoTitleLink.vue`.
  2. Props: `title: string`, `album_id: string | null`, `photo_id: string | null`.
  3. Template branch 1 (`v-if="album_id && photo_id"`): `<RouterLink :to="{ name: 'album', params: { albumId: album_id, photoId: photo_id } }" target="_blank">{{ title }}</RouterLink>`.
  4. Template branch 2 (`v-else-if="photo_id"`): `<span class="flex items-center gap-1"><i class="pi pi-ban text-red-600 text-xs"></i><code class="font-mono text-xs">{{ photo_id }}</code></span>`.
  5. Template branch 3 (`v-else`): `<span class="italic text-muted-color">{{ title }}</span>`.
  6. Run `npm run check`, `npm run lint`.
- _Commands:_ `npm run check`, `npm run lint`
- _Exit:_ TypeScript and ESLint both exit 0.

### I9 – Integrate `PhotoTitleLink` in `DuplicateLine.vue` (≤20 min)

- _Goal:_ Replace the photo-title cell's plain `<span>` + separate icon with `PhotoTitleLink` (FR-042-10, S-042-11).
- _Preconditions:_ I8 complete.
- _Steps:_
  1. Import `PhotoTitleLink` in `DuplicateLine.vue`.
  2. In the photo-title `<div>`, replace the `<router-link>` icon + `<span>` pattern with `<PhotoTitleLink :title="duplicate.photo_title" :album_id="duplicate.album_id ?? null" :photo_id="duplicate.photo_id" />`.
  3. Run `npm run check`, `npm run lint`.
- _Commands:_ `npm run check`, `npm run lint`
- _Exit:_ TypeScript and ESLint both exit 0.

### I10 – Integrate `PhotoTitleLink` in `Moderation.vue` (≤20 min)

- _Goal:_ Replace the title column plain text with `PhotoTitleLink` (FR-042-11, S-042-12, S-042-13).
- _Preconditions:_ I8 complete.
- _Steps:_
  1. Import `PhotoTitleLink` in `Moderation.vue`.
  2. In the title `<td>`, replace `{{ photo.title }}` with `<PhotoTitleLink :title="photo.title" :album_id="photo.album_id ?? null" :photo_id="photo.photo_id" />`.
  3. Run `npm run check`, `npm run lint`.
- _Commands:_ `npm run check`, `npm run lint`
- _Exit:_ TypeScript and ESLint both exit 0.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-042-01 | I1, I3 / T-042-01, T-042-04 | `album_title` from existing album. |
| S-042-02 | I1, I3 / T-042-01, T-042-05 | Null `album_title` when album deleted. |
| S-042-03 | I1, I3 / T-042-02, T-042-06 | `thumb_url` from THUMB variant. |
| S-042-04 | I1, I3 / T-042-02, T-042-07 | Null `thumb_url` when photo deleted. |
| S-042-05 | I1, I3 / T-042-02, T-042-08 | Null `thumb_url` when no THUMB variant. |
| S-042-06 | I1, I2, I3 / T-042-03, T-042-09 | Full happy path: both fields non-null. |
| S-042-07 | I2, I3 / T-042-03, T-042-10 | N+1 prevention; query-count assertion. |
| S-042-08 | I8 / T-042-17 | `PhotoTitleLink` state 1: RouterLink. |
| S-042-09 | I8 / T-042-17 | `PhotoTitleLink` state 2: forbidden icon + photo_id. |
| S-042-10 | I8 / T-042-17 | `PhotoTitleLink` state 3: italic muted title. |
| S-042-11 | I9 / T-042-18 | `DuplicateLine` integration. |
| S-042-12 | I10 / T-042-19 | `Moderation.vue` unsorted photo (null album_id). |
| S-042-13 | I10 / T-042-19 | `Moderation.vue` album present (state 1). |
| S-042-14 | I7 / T-042-16 | `Duplicate` resource nullable album_id. |

## Analysis Gate

_To be completed before coding begins._

- [ ] All twelve FRs are unambiguous and traceable to tasks.
- [ ] All fourteen scenarios map to at least one increment/task.
- [ ] No open questions logged in `open-questions.md` for Feature 042.
- [ ] Estimated total effort ≤ 280 min (fits within session).
- [ ] TypeScript transformer command verified (`php artisan typescript:transform` or manual fallback noted in I4 and I7).

## Exit Criteria

- [ ] `OrderItemResource` constructor and `fromModel()` include `album_title` and `thumb_url`.
- [ ] `OrderResource::fromModel()` eager-loads `items.album` and `items.photo.size_variants` (THUMB-only) unconditionally.
- [ ] All seven backend scenarios (S-042-01 through S-042-07) covered by passing tests.
- [ ] `webshop.orderDownload.unknownAlbum` i18n key added.
- [ ] TypeScript interface for `OrderItemResource` includes `album_title: string | null` and `thumb_url: string | null`.
- [ ] `OrderDownload.vue` renders thumbnail and album title per item row.
- [ ] `Duplicate.album_id` and `Duplicate.album_title` are `?string`; TypeScript interface updated.
- [ ] `PhotoTitleLink.vue` component created with three render states.
- [ ] `DuplicateLine.vue` and `Moderation.vue` use `PhotoTitleLink` for the photo-title cell.
- [ ] `vendor/bin/php-cs-fixer fix` exits 0.
- [ ] `php artisan test` exits 0.
- [ ] `make phpstan` exits 0.
- [ ] `npm run format` exits 0.
- [ ] `npm run check` exits 0.
- [ ] `npm run lint` exits 0.
- [ ] `roadmap.md` and `shop-architecture.md` updated.

## Follow-ups / Backlog

- Consider adding album title and thumbnail display to the `OrderList.vue` items-preview if a future iteration warrants it.
- If the TypeScript transformer is removed from the toolchain in future, update `resources/js/types/` manually and document the process in `docs/specs/3-reference/coding-conventions.md`.
- Investigate whether `OrderItemResource` should also store `album_title` as a persisted column on `order_items` (for true historical accuracy if the album is renamed after purchase). Deferred — current spec uses display-time lookup.
- Consider adding Vitest/Vue Test Utils tests for each `PhotoTitleLink` render state once a frontend unit test infrastructure is established.
- Evaluate whether the album-column link in `DuplicateLine.vue` (separate from the photo-title link) should also be migrated to a similar component for consistency.

---

*Last updated: 2026-05-31*
