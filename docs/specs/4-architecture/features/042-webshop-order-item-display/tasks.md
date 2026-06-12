# Feature 042 Tasks – Photo Display Enrichment

_Status: Draft_  
_Last updated: 2026-05-31_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-042-`) inside the same parentheses immediately after the task title.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections and, when required, ADRs reflect the clarified behaviour.

## Checklist

### Part A – Webshop Order Item Display

### I1 – Extend `OrderItemResource`

- [x] T-042-01 – Add `album_title` and `thumb_url` to `OrderItemResource` (FR-042-01, FR-042-02, DO-042-01).  
  _Intent:_ In `app/Http/Resources/Shop/OrderItemResource.php`, add `public ?string $album_title` and `public ?string $thumb_url` to the constructor. In `fromModel()`, populate `album_title` from `$item->album?->title` and `thumb_url` from `$item->photo?->size_variants->getSizeVariant(SizeVariantType::THUMB)?->url`. Add the `use App\Enum\SizeVariantType;` import. The `#[TypeScript()]` attribute ensures the new fields are picked up by the transformer.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  _Notes:_ License header already present; do not duplicate it. Follow the existing constructor/factory pattern exactly.

### I2 – Update eager-loading in `OrderResource`

- [x] T-042-02 – Eager-load `items.album` and `items.photo.size_variants` (THUMB-only) in `OrderResource::fromModel()` (FR-042-03, NFR-042-01, NFR-042-02, S-042-07).  
  _Intent:_ In `app/Http/Resources/Shop/OrderResource.php`, extend the `$order->load(...)` call so it always loads `items.album` and `items.photo` with a constrained `size_variants` relation (only types: `SMALL`, `SMALL2X`, `THUMB`, `THUMB2X`, `PLACEHOLDER`). The load must be unconditional — not gated on order status — because album and thumbnail display is needed for all statuses on the detail page. Use an inline `whereIn('type', [...])` closure for the size variant constraint, following the pattern in `Thumb::sizeVariantsFilter()`.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Confirm the existing `items.size_variant` load (used for download URLs on CLOSED orders) is retained alongside the new loads.

### I3 – Backend tests

- [x] T-042-03 – Write failing test: happy path — both `album_title` and `thumb_url` non-null (S-042-06, S-042-07).  
  _Intent:_ In `tests/Feature_v2/` (extending `BaseApiWithDataTest`), add a test that creates a complete order with an item linked to a photo and album (both existing), calls `GET /api/v2/Order/{id}`, and asserts:
  - `response.items[0].album_title` equals the album's title.
  - `response.items[0].thumb_url` is a non-null string.
  Write this test before implementing T-042-01 so it fails first.  
  _Verification commands:_  
  - `php artisan test --filter=<TestClassName>`  
  _Notes:_ Ensure the photo has a THUMB size variant in the factory/fixture. Mark `[x]` only after the test is green (post-implementation).

- [x] T-042-04 – Write failing test: `album_title` is `null` when album deleted (S-042-02).  
  _Intent:_ Create an order item with `album_id` pointing to a soft-deleted or hard-deleted album. Call `GET /api/v2/Order/{id}` and assert `items[0].album_title` is `null`.  
  _Verification commands:_  
  - `php artisan test --filter=<TestClassName>`  
  _Notes:_ Use the existing album deletion mechanism in tests to simulate a deleted album.

- [x] T-042-05 – Write failing test: `thumb_url` is `null` when photo deleted (S-042-04).  
  _Intent:_ Create an order item whose `photo_id` references a deleted photo (or set `photo_id` to `null` in the factory). Assert `items[0].thumb_url` is `null`.  
  _Verification commands:_  
  - `php artisan test --filter=<TestClassName>`  
  _Notes:_ `OrderItem.photo` is a `BelongsTo` that returns `null` when the photo is gone.

- [x] T-042-06 – Write failing test: `thumb_url` is `null` when photo has no THUMB variant (S-042-05).  
  _Intent:_ Create an order item with a valid photo that has no THUMB size variant in the database. Assert `items[0].thumb_url` is `null`.  
  _Verification commands:_  
  - `php artisan test --filter=<TestClassName>`  
  _Notes:_ Create the photo without calling the size-variant generation pipeline, or delete the THUMB variant manually in the test.

- [ ] T-042-07 – Run full backend test suite (NFR-042-03, NFR-042-05).  
  _Intent:_ Confirm no regressions after I1 + I2 changes.  
  _Verification commands:_  
  - `php artisan test`  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix`  
  _Notes:_ All three must exit 0.

### I4 – Frontend: i18n + TypeScript types

- [x] T-042-08 – Add `webshop.orderDownload.unknownAlbum` i18n key (FR-042-06).  
  _Intent:_ Locate the English i18n file used for webshop strings (likely `lang/en/` or a JSON file under `resources/js/lang/`). Add `"unknownAlbum": "Unknown album"` under the `webshop.orderDownload` namespace. Check whether other language files need updating and add stub translations if so.  
  _Verification commands:_  
  - Manual review: confirm key resolves via `$t('webshop.orderDownload.unknownAlbum')` in the Vue context.  
  _Notes:_ Follow the exact nesting structure already used by neighbouring keys like `webshop.orderDownload.enterContentUrl`.

- [x] T-042-09 – Refresh TypeScript types for `OrderItemResource` (NFR-042-06).  
  _Intent:_ Run `php artisan typescript:transform` (or equivalent) to regenerate the TypeScript interface. Confirm `App.Http.Resources.Shop.OrderItemResource` now includes `album_title: string | null` and `thumb_url: string | null`. If the transformer command is unavailable, manually add the two fields to the interface definition file and note the manual edit here.  
  _Verification commands:_  
  - `npm run check` — must exit 0.  
  _Notes:_ Do not run the transformer in isolation if it overwrites other manually-maintained types — check the project convention first.

### I5 – Frontend: update `OrderDownload.vue`

- [x] T-042-10 – Render thumbnail image or placeholder in order item row (FR-042-04, UI-042-01, UI-042-02).  
  _Intent:_ In `resources/js/views/webshop/OrderDownload.vue`, inside the `v-for="item in order.items"` loop, add before the title/notes block:
  ```html
  <img v-if="item.thumb_url" :src="item.thumb_url" loading="lazy" class="w-12 h-12 object-cover rounded flex-shrink-0" :alt="item.title" />
  <i v-else class="pi pi-image text-muted-color text-2xl w-12 h-12 flex items-center justify-center flex-shrink-0" />
  ```
  Adjust the outer flex container to include `items-start gap-4` so the thumbnail aligns with the text block.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ Size class `w-12 h-12` = 48 px, consistent with Tailwind/PrimeVue patterns used elsewhere in the project. Use `flex-shrink-0` to prevent thumbnail from collapsing.

- [x] T-042-11 – Render album title or fallback in order item row (FR-042-05, UI-042-03, UI-042-04).  
  _Intent:_ Below the existing `RouterLink` for the photo title, add:
  ```html
  <div class="text-sm text-muted-color">{{ item.album_title ?? $t('webshop.orderDownload.unknownAlbum') }}</div>
  ```
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  _Notes:_ The `??` operator is safe here because `album_title` is typed as `string | null`. Use the same `text-muted-color` class used for `size_variant_type` and `license_type` in the existing row.

### I6 – Quality gates & documentation

- [ ] T-042-12 – Run full quality gate (NFR-042-03 through NFR-042-07).  
  _Intent:_ Execute the complete gate sequence:
  1. `vendor/bin/php-cs-fixer fix`
  2. `npm run format`
  3. `npm run check`
  4. `npm run lint`
  5. `php artisan test`
  6. `make phpstan`
  All six must exit 0.  
  _Verification commands:_ See above.  
  _Notes:_ If any check fails, fix before proceeding to documentation tasks.

- [x] T-042-13 – Update `docs/specs/4-architecture/shop-architecture.md`.  
  _Intent:_ Add a note in the "Request/Response Pattern" or "Data Models" section that `OrderItemResource` now includes `album_title` and `thumb_url` for display purposes, and that `OrderResource::fromModel()` eager-loads photo and album relations when building the item resource.  
  _Verification commands:_ Manual review.  
  _Notes:_ Keep the addition brief (2–3 sentences).

- [x] T-042-14 – Update `roadmap.md`: move Feature 042 from Active to Completed.  
  _Intent:_ Add Feature 042 row to the Completed table with today's date and a one-line summary. Remove from Active Features. Update the "Last updated" footer.  
  _Verification commands:_ Manual review.  
  _Notes:_ Follow the pattern of completed rows (e.g., Feature 037, Feature 028).

- [x] T-042-15 – Update `docs/specs/_current-session.md`.  
  _Intent:_ Replace the current session content with a Feature 042 summary covering what was implemented and confirming all tasks complete.  
  _Verification commands:_ Manual review.  
  _Notes:_ Keep the session doc as the single live snapshot per session conventions.

## Notes / TODOs

- `SizeVariant::getUrlAttribute()` is the accessor providing the URL; if the attribute name differs in the actual model, adjust T-042-01 accordingly.
- The `$item->photo->size_variants->getSizeVariant(SizeVariantType::THUMB)` call relies on `SizeVariants` being the type of `photo->size_variants`. Verify the accessor name on `Photo` before writing T-042-01 implementation.
- If the TypeScript transformer is unavailable, document the manual interface edit in T-042-09 and add a comment to the interface file indicating it is manually maintained.
- Query-count assertion in S-042-07 / T-042-03 can be implemented with `DB::enableQueryLog()` / `DB::getQueryLog()` or Laravel's `assertQueryCount()` helper if available in the test suite.
- Do not add fallback or compatibility behaviour for historic `OrderItem` records that lack an `album_id` — the spec's Non-Goals state no fallback is required unless explicitly requested.
