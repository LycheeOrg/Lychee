# Feature 046 Tasks – Tag Album Custom Cover

_Status: Draft_
_Last updated: 2026-06-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.

## Phase 1 – Database Migration (I1)

- [ ] T-046-01 – Create migration to add `cover_id` to `tag_albums` (FR-046-01) (~15 min)
  _Intent:_ Add nullable `cover_id` char(24) column to `tag_albums` with FK to `photos.id` (SET NULL on delete).
  _Sub-steps:_
  - Create migration file
  - `up()`: add nullable char(24) `cover_id`, add FK to `photos.id` with SET NULL on delete
  - `down()`: drop FK constraint, drop column
  _Verification commands:_
  - `php artisan test --filter=AlbumSetCoverTest`
  - `php artisan test`
  _Notes:_ Simple additive migration — no data copy needed.

### Phase 1 verification
- [ ] Migration applies cleanly on test database
- [ ] Migration rollback works
- [ ] Existing `AlbumSetCoverTest` passes

## Phase 2 – Model Layer (I2)

- [ ] T-046-02 – Add `cover_id` attribute and `cover()` relationship to `TagAlbum` (FR-046-02, NFR-046-04) (~15 min)
  _Intent:_ Give `TagAlbum` cover support with eager-loading.
  _Sub-steps:_
  - Add `'cover_id' => null` to `TagAlbum::$attributes` array
  - Add `cover()` HasOne relationship: `$this->hasOne(Photo::class, 'id', 'cover_id')`
  - Add `'cover', 'cover.size_variants'` to eager-loading (define `protected $with` on `TagAlbum`)
  - Update PHPDoc block to document `cover_id` and `cover` properties
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

- [ ] T-046-03 – Update `TagAlbum::getThumbAttribute()` to respect `cover_id` (FR-046-09) (~15 min)
  _Intent:_ When a tag album has an explicit `cover_id`, return that photo's thumb; otherwise fall back to existing dynamic query.
  _Sub-steps:_
  - At the start of `getThumbAttribute()`, check `$this->cover_id !== null`
  - If set, return `Thumb::createFromPhoto($this->cover)`
  - Otherwise, keep existing `Thumb::createFromQueryable()` logic
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

### Phase 2 verification
- [ ] All phase 2 tasks checked off
- [ ] PHPStan 0 errors
- [ ] All existing tests pass

## Phase 3 – Back-end API (I3)

- [ ] T-046-04 – Widen `SetAsCoverRequest` to accept `TagAlbum` (FR-046-03) (~10 min)
  _Intent:_ Remove the `instanceof Album` guard so tag albums can have covers set via the API.
  _Sub-steps:_
  - In `processValidatedValues()`, change `if (!$album instanceof Album)` to accept `BaseAlbum` instances (both `Album` and `TagAlbum`)
  - Smart albums are already excluded by `findBaseAlbumOrFail()` returning only `BaseAlbum` types
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

- [ ] T-046-05 – Update `EditableBaseAlbumResource` to include `cover_id` for tag albums (FR-046-06) (~10 min)
  _Intent:_ Move `$this->cover_id = $album->cover_id` outside the `instanceof Album` block.
  _Sub-steps:_
  - In constructor, set `$this->cover_id = $album->cover_id` unconditionally (both `Album` and `TagAlbum` now have it)
  _Verification commands:_
  - `make phpstan`

- [ ] T-046-06 – Add `cover_id` to `HeadTagAlbumResource` (FR-046-05, S-046-08) (~10 min)
  _Intent:_ Serialise `cover_id` for tag album API responses.
  _Sub-steps:_
  - Add `public ?string $cover_id` property
  - Populate `$this->cover_id = $tag_album->cover_id` in constructor
  _Verification commands:_
  - `make phpstan`

- [ ] T-046-07 – Verify `AlbumController::cover()` works for tag albums (FR-046-04) (~5 min)
  _Intent:_ Confirm the controller sets `cover_id` and calls `save()` — both work for `TagAlbum` since it has `cover_id` in `$attributes`.
  _Verification commands:_
  - `php artisan test`

- [ ] T-046-08 – Nullify `tag_albums.cover_id` on photo deletion in `PhotosToBeDeletedDTO` (FR-046-10, S-046-04) (~10 min)
  _Intent:_ When a photo is force-deleted, clear any `tag_albums.cover_id` referencing it — matching the existing `albums.cover_id` nullification at `PhotosToBeDeletedDTO:103`.
  _Sub-steps:_
  - In `forceDelete()`, inside the existing chunk loop (after line 103), add: `DB::table('tag_albums')->whereIn('cover_id', $chunk->all())->update(['cover_id' => null]);`
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

### Phase 3 verification
- [ ] All phase 3 tasks checked off
- [ ] PHPStan 0 errors
- [ ] API accepts cover-setting for tag albums
- [ ] Photo deletion clears tag album covers

## Phase 4 – Front-end (I4)

- [ ] T-046-09 – Split context menu guard: "Set as cover" for tag albums, "Set as header" album-only (FR-046-07, UI-046-01, UI-046-02, UI-046-03) (~20 min)
  _Intent:_ The block at `contextMenu.ts:126–148` currently guards both "Set as cover" and "Set as header" behind `is_model_album`. Split into two blocks:
  - "Set as cover": show when `is_model_album || albumStore.tagAlbum !== undefined` (and `can_edit`)
  - "Set as header" / "Remove header": keep `is_model_album`-only guard
  _Sub-steps:_
  - Import `useAlbumStore` if not already available in the closure
  - Create separate guard for "Set as cover" that includes tag albums
  - Keep the header guard `is_model_album`-only
  - Ensure `selectors.album.value.id` is used for the API call (works for both resource types)
  _Verification commands:_
  - `npm run format`
  - `npm run check`

- [ ] T-046-10 – Update TypeScript types for `HeadTagAlbumResource.cover_id` (FR-046-05) (~10 min)
  _Intent:_ Regenerate or manually add `cover_id` to the `HeadTagAlbumResource` TypeScript type.
  _Sub-steps:_
  - Run `php artisan typescript:transform` or update `lychee.d.ts` manually
  - Verify `npm run check` passes
  _Verification commands:_
  - `npm run check`

### Phase 4 verification
- [ ] All phase 4 tasks checked off
- [ ] `npm run check` clean
- [ ] Context menu shows "Set as cover" for tag album photos
- [ ] Context menu does NOT show "Set as header" for tag album photos

## Phase 5 – Tests (I5)

- [ ] T-046-11 – Create `TagAlbumSetCoverTest` feature test (S-046-01, S-046-02, S-046-04, S-046-05, S-046-06) (~30 min)
  _Intent:_ Feature tests covering all tag album cover scenarios.
  _Sub-steps:_
  - Create `tests/Feature_v2/TagAlbum/TagAlbumSetCoverTest.php`
  - Test set cover on tag album (authorized user) → verify `cover_id` persisted
  - Test toggle cover off (same photo ID) → verify `cover_id` null
  - Test unauthorized user gets 403
  - Test deleting cover photo sets `cover_id` to null (via `PhotosToBeDeletedDTO::forceDelete()`)
  _Verification commands:_
  - `php artisan test --filter=TagAlbumSetCoverTest`
  - `php artisan test`

- [ ] T-046-12 – Verify existing `AlbumSetCoverTest` still passes (S-046-03, NFR-046-03) (~5 min)
  _Intent:_ Regression check — regular album covers unaffected.
  _Verification commands:_
  - `php artisan test --filter=AlbumSetCoverTest`

### Phase 5 verification
- [ ] All phase 5 tasks checked off
- [ ] All tests green
- [ ] PHPStan 0 errors

## Phase 6 – Quality Gate & Cleanup

- [ ] T-046-13 – Run full quality gate (~10 min)
  _Intent:_ Final check before commit.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`

- [ ] T-046-14 – Update knowledge map (~5 min)
  _Intent:_ Document `TagAlbum.cover_id` and `TagAlbum::cover()` in knowledge map.
  _Verification commands:_ N/A

## Notes / TODOs

- `HasAlbumThumb` is not modified — it remains `Album`-only with its precomputed cover fields.
- `albums.cover_id` is untouched.
- The context menu split is the most delicate front-end change — verify both album types manually.
