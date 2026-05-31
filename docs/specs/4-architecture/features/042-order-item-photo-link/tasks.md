# Feature 042 Tasks – Order Item Photo Link

_Status: Planning_
_Last updated: 2026-05-31_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-042-`) inside the same parentheses immediately after the task title.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.

## Checklist

### I1 – Backend: Extend `OrderItemResource` with existence flags

- [ ] T-042-01 – Write failing PHP tests for `OrderItemResource` existence flags (FR-042-01, S-042-06).
  _Intent:_ Create `tests/Feature_v2/OrderItemResourceTest.php` (or equivalent test class extending `BaseApiWithDataTest`) that seeds an `OrderItem` linked to an existing album+photo, a deleted album (photo still present), a deleted photo (album still present), and both deleted. Assert that `album_exists` and `photo_exists` are set correctly in each case.
  _Verification commands:_
  - `php artisan test --filter=OrderItemResource`
  _Notes:_ Tests must fail (no `album_exists` field yet). Use `DatabaseTransactions` trait per project convention.

- [ ] T-042-02 – Extend `OrderItemResource` with `album_exists` and `photo_exists` (FR-042-01, FR-042-02, NFR-042-01).
  _Intent:_ Add `public bool $album_exists` and `public bool $photo_exists` to the `OrderItemResource` Spatie Data constructor. Update `fromModel()` to read `$item->album !== null` and `$item->photo !== null`. In `OrderResource::fromModel()`, extend all `load('items…')` calls to include `items.album` and `items.photo` so the flags are populated without N+1 queries.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `php artisan test --filter=OrderItemResource`
  - `make phpstan`
  _Notes:_ Guard with `$item->relationLoaded('album')` before reading the relation if there is any path where the relation is not loaded, to avoid lazy-loading and silent N+1 regression.

- [ ] T-042-03 – Regenerate TypeScript types and verify (NFR-042-02).
  _Intent:_ Run the project's TypeScript-transform artisan command (e.g. `php artisan typescript:transform`) to regenerate `resources/js/lychee.d.ts`. Confirm that `OrderItemResource` in the output includes `album_exists: boolean` and `photo_exists: boolean`.
  _Verification commands:_
  - `php artisan typescript:transform` (or project equivalent)
  - `npm run check`
  _Notes:_ If the transform command differs from the above, document the correct command in this tasks file.

### I2 – Frontend: Three-state title rendering in `OrderDownload.vue`

- [ ] T-042-04 – Write failing Vue component tests for linked state (FR-042-03, S-042-01, S-042-02).
  _Intent:_ Add a Vitest test that renders `OrderDownload.vue` (or an extracted `OrderItemTitle.vue`) with a mock item where `album_exists: true`. Assert that a `RouterLink` (or `<a>`) element is present with the correct `to` params. Tests must fail before the code change.
  _Verification commands:_
  - `npm run check`

- [ ] T-042-05 – Write failing Vue component tests for forbidden state (FR-042-04, S-042-03).
  _Intent:_ Add a test with `album_exists: false, photo_exists: true`. Assert that a `.pi-ban` icon element is present (with a red-colour class) and that the `photo_id` text appears. Assert no `RouterLink` is present.
  _Verification commands:_
  - `npm run check`

- [ ] T-042-06 – Write failing Vue component tests for ghost state (FR-042-05, S-042-04, S-042-05).
  _Intent:_ Add a test with `album_exists: false, photo_exists: false` (and a variant with both IDs null). Assert that the title renders with `.italic` and `.text-muted-color` classes. Assert no `RouterLink` and no `pi-ban` are present.
  _Verification commands:_
  - `npm run check`

- [ ] T-042-07 – Implement three-state conditional in `OrderDownload.vue` (FR-042-03, FR-042-04, FR-042-05, FR-042-06).
  _Intent:_ Replace the unconditional `RouterLink` at the order-item title location in `OrderDownload.vue` with a `v-if / v-else-if / v-else` block:
  - `v-if="item.album_exists"` → `RouterLink` to `{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }`
  - `v-else-if="item.photo_exists"` → `<i class="pi pi-ban text-red-600 …" />` + title text + `photo_id` in small muted span
  - `v-else` → `<span class="italic text-muted-color">{{ item.title }}</span>`
  _Verification commands:_
  - `npm run format`
  - `npm run check`
  _Notes:_ If extracting to `OrderItemTitle.vue` sub-component, ensure it is registered and imported in `OrderDownload.vue`. Follow the existing PrimeVue / Tailwind class conventions already used in the file.

### I3 – Quality Gates & Documentation

- [ ] T-042-08 – Full quality gate pass (NFR-042-01, NFR-042-02, NFR-042-03).
  _Intent:_ Execute the complete quality gate and confirm all checks pass.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`

- [ ] T-042-09 – Update roadmap and knowledge map.
  _Intent:_ In `docs/specs/4-architecture/roadmap.md`, move Feature 042 from Active to Completed (or update status to Complete). In `docs/specs/4-architecture/knowledge-map.md`, update the Shop Implementation entry to note that `OrderItemResource` now exposes `album_exists` and `photo_exists`.
  _Verification commands:_ None (documentation only).

## Notes / TODOs

- T-042-02: If `BasketController` / `CheckoutController` constructs `OrderItemResource::collect()` directly from items loaded without the album/photo relations, add a `$items->loadMissing(['album', 'photo'])` call before the collect. Confirm all code paths in `OrderResource::fromModel()` and `BasketService` that produce `OrderItemResource` collections.
- T-042-07: The `item.photo_id` shown in the forbidden state should be the raw stored string (e.g. a short hash). No UI truncation is required at this stage — add a follow-up if needed.
