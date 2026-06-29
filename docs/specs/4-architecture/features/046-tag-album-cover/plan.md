# Feature Plan 046 – Tag Album Custom Cover

_Linked specification:_ [spec.md](spec.md)
_Status:_ Draft
_Last updated:_ 2026-06-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant.

## Vision & Success Criteria

Tag album owners can set a custom cover photo via the context menu, just like regular album owners. A new `cover_id` column on `tag_albums` stores the selection. The `albums` table and `HasAlbumThumb` relation remain untouched. All existing album cover functionality continues to work. Tests pass, PHPStan clean, front-end type-checks pass.

## Scope Alignment

- **In scope:**
  - Database migration: add `cover_id` to `tag_albums` (FR-046-01)
  - Model changes: `TagAlbum` gains `cover_id` attribute, `cover()` relationship, eager-loading (FR-046-02)
  - Back-end: widen `SetAsCoverRequest` and controller to accept `TagAlbum` (FR-046-03, FR-046-04)
  - API resources: `HeadTagAlbumResource`, `EditableBaseAlbumResource` (FR-046-05, FR-046-06)
  - Front-end: split context menu guard, show "Set as cover" for tag albums (FR-046-07, FR-046-08)
  - Tag album thumb resolution respects `cover_id` (FR-046-09)
  - Tests for tag album cover scenarios

- **Out of scope:**
  - Precomputed covers for tag albums (NG1)
  - Header photo for tag albums (NG2)
  - Smart album covers (NG3)
  - Any changes to `albums.cover_id` or `HasAlbumThumb` (NG4)

## Dependencies & Interfaces

- `TagAlbum` model (`app/Models/TagAlbum.php`) — add `cover_id`, `cover()`, eager-loading
- `SetAsCoverRequest` (`app/Http/Requests/Album/SetAsCoverRequest.php`) — remove `instanceof Album` guard
- `AlbumController::cover()` (`app/Http/Controllers/Gallery/AlbumController.php`) — already generic enough
- `HeadTagAlbumResource` (`app/Http/Resources/Models/HeadTagAlbumResource.php`) — add `cover_id`
- `EditableBaseAlbumResource` (`app/Http/Resources/Editable/EditableBaseAlbumResource.php`) — move `cover_id` assignment outside `instanceof Album` block
- Front-end `contextMenu.ts` (`resources/js/composables/contextMenus/contextMenu.ts`) — split guard at lines 126–148
- Front-end `AlbumState.ts` — `tagAlbum` store state already available
- `photo-service.ts` — `setAsCover()` already generic, no changes needed

## Assumptions & Risks

- **Assumptions:**
  - `TagAlbum` attributes work like `Album` attributes — the `ForwardsToParentImplementation` trait handles `cover_id` on the child table correctly (it should, since `TagAlbum::$attributes` is where child-specific columns are listed).
  - The `photo-service.ts::setAsCover()` call is type-agnostic — it sends `album_id` and `photo_id` without caring about album type.

- **Risks / Mitigations:**
  | Risk | Likelihood | Impact | Mitigation |
  |------|-----------|--------|------------|
  | `ForwardsToParentImplementation` miscategorises `cover_id` (tries to save to `base_albums`) | Low | High | Verify by adding `cover_id` to `TagAlbum::$attributes` array explicitly |
  | Context menu split introduces regression for regular album header menu | Low | Medium | Keep header guard `is_model_album`-only; test both album types manually |

## Increment Map

### I1 – Database Migration (~15 min)

- _Goal:_ Add `cover_id` to `tag_albums`.
- _Preconditions:_ None.
- _Steps:_
  1. Create migration file.
  2. `up()`: Add nullable `cover_id` char(24) to `tag_albums`. Add FK constraint to `photos.id` with SET NULL on delete.
  3. `down()`: Drop FK, drop column.
  4. Run tests (migrations auto-apply to test SQLite DB).
- _Commands:_ `php artisan test`
- _Exit:_ Migration applies and rolls back cleanly; existing tests pass.
- _Covers:_ FR-046-01, NFR-046-01

### I2 – Model Layer (~30 min)

- _Goal:_ `TagAlbum` gains `cover_id`, `cover()` relationship, eager-loading, and updated thumb resolution.
- _Preconditions:_ I1 applied.
- _Steps:_
  1. Add `'cover_id' => null` to `TagAlbum::$attributes`.
  2. Add `cover()` HasOne relationship to `TagAlbum` (same pattern as `Album::cover()`).
  3. Add `'cover', 'cover.size_variants'` to `TagAlbum::$with` (or define a `$with` property if not present) for eager-loading (NFR-046-04).
  4. Update `TagAlbum::getThumbAttribute()`: if `$this->cover_id !== null`, return `Thumb::createFromPhoto($this->cover)` instead of the dynamic query.
  5. Run PHPStan and tests.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ PHPStan clean, all existing tests pass, `TagAlbum::cover()` works.
- _Covers:_ FR-046-02, FR-046-09, NFR-046-02, NFR-046-03, NFR-046-04

### I3 – Back-end API (~20 min)

- _Goal:_ Widen `SetAsCoverRequest`, update resource serialisation.
- _Preconditions:_ I2 complete.
- _Steps:_
  1. In `SetAsCoverRequest::processValidatedValues()`: replace `if (!$album instanceof Album)` with a check that accepts both `Album` and `TagAlbum` (reject only non-BaseAlbum types like smart albums). Update `$this->album` typing accordingly.
  2. Verify `AlbumController::cover()` — it accesses `$album->cover_id` which will work for `TagAlbum` now. No changes expected.
  3. In `EditableBaseAlbumResource`: move `$this->cover_id = $album->cover_id` outside the `instanceof Album` block so tag albums also get their `cover_id` serialised.
  4. In `HeadTagAlbumResource`: add `public ?string $cover_id` property, populate with `$tag_album->cover_id`.
  5. In `PhotosToBeDeletedDTO::forceDelete()`: add `DB::table('tag_albums')->whereIn('cover_id', $chunk->all())->update(['cover_id' => null])` alongside the existing `albums.cover_id` nullification (line 103). This ensures tag album covers are cleared when the referenced photo is deleted via the application delete path.
  6. Run PHPStan.
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ API accepts cover-setting for tag albums; resources serialise `cover_id`; photo deletion clears tag album covers.
- _Covers:_ FR-046-03, FR-046-04, FR-046-05, FR-046-06, FR-046-10, S-046-01, S-046-02, S-046-04, S-046-08

### I4 – Front-end (~30 min)

- _Goal:_ Show "Set as cover" for tag album photos; keep "Set as header" album-only.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. In `contextMenu.ts`, split the guard block at lines 126–148:
     - "Set as cover": show when `is_model_album === true` OR `albumStore.tagAlbum !== undefined` (and user has `can_edit`).
     - "Set as header" / "Remove header": keep existing `is_model_album === true` guard.
  2. For tag albums, `selectors.album.value` is a `HeadTagAlbumResource`. The callback `photoCallbacks.setAsCover` needs to use `selectors.album.value.id` — verify this works for both resource types.
  3. Update TypeScript types if `HeadTagAlbumResource` now includes `cover_id` (regenerate via `php artisan typescript:transform` or update `lychee.d.ts` manually).
  4. Run `npm run check`.
- _Commands:_ `npm run format`, `npm run check`
- _Exit:_ Context menu works correctly for both album types.
- _Covers:_ FR-046-07, FR-046-08, UI-046-01, UI-046-02, UI-046-03, S-046-05, S-046-06, S-046-07

### I5 – Tests (~30 min)

- _Goal:_ Feature tests for tag album cover.
- _Preconditions:_ I3 complete.
- _Steps:_
  1. Create `tests/Feature_v2/TagAlbum/TagAlbumSetCoverTest.php`.
  2. Test S-046-01: Set cover on tag album → verify `cover_id` persisted.
  3. Test S-046-02: Toggle cover off (same photo) → verify `cover_id` null.
  4. Test S-046-04: Delete cover photo → verify `cover_id` null (FK cascade).
  5. Test authorization: user without edit permission gets 403.
  6. Verify existing `AlbumSetCoverTest` still passes (S-046-03).
  7. Full test suite.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ All tests green.
- _Covers:_ S-046-01 through S-046-06, NFR-046-03

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-046-01 | I3 / I5 | Set cover on tag album |
| S-046-02 | I3 / I5 | Toggle cover off |
| S-046-03 | I2 / I5 | Existing album cover unchanged |
| S-046-04 | I5 | FK cascade test |
| S-046-05 | I4 | Context menu visible with edit |
| S-046-06 | I4 | Context menu hidden without edit |
| S-046-07 | I4 | No "Set as header" for tag albums |
| S-046-08 | I3 | API returns cover_id |

## Analysis Gate

All open questions resolved (Q-046-01 B, Q-046-02 B, Q-046-03 N/A). Ready for implementation.

## Exit Criteria

- [ ] All tasks checked off
- [ ] `php artisan test` — all tests green
- [ ] `make phpstan` — 0 errors
- [ ] `vendor/bin/php-cs-fixer fix` — no changes
- [ ] `npm run format` — no changes
- [ ] `npm run check` — clean
- [ ] Existing `AlbumSetCoverTest` passes
- [ ] New `TagAlbumSetCoverTest` passes
- [ ] Manual verification: context menu shows "Set as cover" (not "Set as header") in tag album

## Follow-ups / Backlog

- Precomputed automatic covers for tag albums (deferred — see NG1).
- Header photo support for tag albums (deferred — see NG2).
