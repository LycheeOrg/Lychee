# Feature Plan 047 – Person Smart Album

_Linked specification:_ `docs/specs/4-architecture/features/047-person-smart-album/spec.md`
_Status:_ Draft
_Last updated:_ 2026-06-28

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Enable users to create Person Albums that dynamically aggregate photos by identified persons, mirroring the TagAlbum pattern. Success is measured by:

- Person Albums are created, edited, and deleted through the same UX patterns as Tag Albums.
- AND/OR semantics correctly filter photos by person presence.
- Feature gating prevents any data leakage when v8 or facial recognition is disabled.
- All quality gates pass: PHPStan level 6, php-cs-fixer, phpunit, npm run check, npm run format.

## Scope Alignment

- **In scope:**
  - Database migration (person_albums table, person_albums_persons junction, config keys)
  - PersonAlbum model, PersonAlbumBuilder, HasManyPhotosByPerson relation
  - CreatePersonAlbum action
  - AddPersonAlbumRequest, UpdatePersonAlbumRequest
  - AlbumController endpoints (createPersonAlbum, updatePersonAlbum)
  - API routes (POST/PATCH /PersonAlbum)
  - HeadPersonAlbumResource, HeadAbstractAlbumResource dispatch
  - RootAlbumResource + TopAlbumDTO extension (person_albums section)
  - Top action extension (query person albums with visibility filter)
  - AlbumHeadController dispatch for PersonAlbum
  - InitConfig flag for frontend gating
  - Frontend: AlbumCreatePersonDialog.vue, PersonsMultiSelect.vue, AlbumProperties.vue extension
  - Frontend: album-service.ts extension, ModalsState, AlbumState, lychee.d.ts types
  - Frontend: AlbumsHeader.vue + Albums.vue for person_albums section rendering
  - Translations (22 languages — English first, stubs for others)
  - Feature tests
  - Quality gates

- **Out of scope:**
  - Automatic album creation on person identification
  - Person/Face detection pipeline changes
  - Pagination for Person Album photos (uses existing pagination infrastructure)
  - Person Album in search results (follow-up)

## Dependencies & Interfaces

- **Person model** (`app/Models/Person.php`) — existing, consumed as-is
- **Face model** (`app/Models/Face.php`) — existing, joined for photo resolution
- **BaseAlbum** (`app/Models/Extensions/BaseAlbum.php`) — parent class for PersonAlbum
- **BaseHasManyPhotos** (`app/Relations/BaseHasManyPhotos.php`) — parent class for HasManyPhotosByPerson
- **TagAlbum** — reference implementation for the entire pattern
- **AlbumQueryPolicy** — visibility filtering for root listing
- **PhotoQueryPolicy** — searchability/sensitivity filtering for photo resolution
- **Feature flags:** `config('features.v8')`, config `ai_vision_face_enabled`

## Assumptions & Risks

- **Assumptions:**
  - Person and Face data already exists in the database (from the AI Vision feature).
  - The TagAlbum pattern is stable and will not undergo major refactoring during this work.
  - The `faces` table has indexes on `person_id` and `photo_id` for efficient joins.

- **Risks / Mitigations:**
  - **Performance with large face datasets:** The subquery pattern from TagAlbum avoids duplicate rows; additionally, the `faces` table should already have appropriate indexes. Mitigation: verify index existence in I1.
  - **Dismissed faces:** Faces with `is_dismissed = true` must be excluded from photo resolution. Mitigation: add `AND face.is_dismissed = false` to all queries (specified in FR-047-02).

## Implementation Drift Gate

After all increments pass, verify:
1. Traceability: every FR/NFR/scenario has at least one test or manual verification.
2. PHPStan 0 errors, php-cs-fixer clean, phpunit green, npm run check clean.
3. Knowledge map updated with PersonAlbum model and HasManyPhotosByPerson relation.
4. Roadmap updated with Feature 047 status.

## Increment Map

### I1 – Database Migration + Config Keys (~45 min)

- _Goal:_ Create `person_albums` table, `person_albums_persons` junction table, and two config keys.
- _Preconditions:_ `persons` and `base_albums` tables exist.
- _Steps:_
  1. Create migration file.
  2. `person_albums` table: `id` char(24) PK + FK to `base_albums.id` cascadeOnDelete, `is_and` boolean not null default false.
  3. `person_albums_persons` junction: `id` auto-increment, `person_id` char(24) FK to `persons.id` cascadeOnDelete, `album_id` char(24) FK to `person_albums.id` cascadeOnDelete, unique index on `(person_id, album_id)`.
  4. Insert config rows: `PA_override_visibility` (bool, default 0, cat smart-albums), `hide_nsfw_in_person_albums` (bool, default 1, cat smart-albums).
  5. Reversible: down() drops junction, drops table, removes config rows.
- _Commands:_ `php artisan migrate`, `php artisan test`
- _Exit:_ Migration runs cleanly forward and backward; tables exist with correct schema.

### I2 – PersonAlbum Model + PersonAlbumBuilder + Integration Points (~90 min)

- _Goal:_ Create the Eloquent model, custom builder, and register PersonAlbum in all dispatch/resolution points (Q-047-01).
- _Preconditions:_ I1 migration applied.
- _Steps:_
  1. Create `app/Models/PersonAlbum.php` extending `BaseAlbum` with `is_and` attribute, `persons()` BelongsToMany relation, `photos()` HasManyPhotosByPerson relation, `getThumbAttribute()` via `Thumb::createFromQueryable`.
  2. Create `app/Models/Builders/PersonAlbumBuilder.php` mirroring `TagAlbumBuilder`.
  3. Register `PersonAlbum` in `AlbumFactory::findBaseAlbumOrFail()` — add third query for PersonAlbum after TagAlbum.
  4. Register `PersonAlbum` in `AlbumFactory::findBaseAlbumsOrFail()` — same pattern.
  5. Update `GetAlbumPhotosRequest::processValidatedValues()` — add `PersonAlbum::find()` fallback after `TagAlbum::find()`.
  6. Update `AlbumPhotosController::get()` — add `elseif ($album instanceof PersonAlbum)` branch identical to the TagAlbum branch (calls `$album->photos()->with([...])`, applies `SortingDecorator`).
- _Commands:_ `make phpstan`, `php artisan test`
- _Exit:_ Model instantiates correctly, album resolution and photo pagination work, PHPStan clean.

### I3 – HasManyPhotosByPerson Relation (~60 min)

- _Goal:_ Implement the custom relation that resolves photos via the `faces` table.
- _Preconditions:_ I2 model exists.
- _Steps:_
  1. Create `app/Relations/HasManyPhotosByPerson.php` extending `BaseHasManyPhotos`.
  2. Implement `addEagerConstraints()`: build subquery joining `faces` where `person_id IN (...)` and `is_dismissed = false`, with AND (HAVING COUNT DISTINCT = N) or OR logic.
  3. Apply `PA_override_visibility` config: use `applySensitivityFilter` when true, `applySearchabilityFilter` when false.
  4. Apply `hide_nsfw_in_person_albums` config for NSFW filtering.
  5. Implement `match()` with sorting (same pattern as TagAlbum).
  6. Write unit test for AND/OR logic.
- _Commands:_ `php artisan test --filter=PersonAlbum`, `make phpstan`
- _Exit:_ Relation correctly resolves photos with AND/OR semantics; dismissed faces excluded; access control applied.

### I4 – CreatePersonAlbum Action + Request + Route + Delete + Cleanup Job (~90 min)

- _Goal:_ Wire the creation endpoint, deletion support, and orphan cleanup.
- _Preconditions:_ I2 model, I3 relation.
- _Steps:_
  1. Create `app/Actions/Album/CreatePersonAlbum.php` mirroring `CreateTagAlbum` — creates album, syncs persons, sets statistics.
  2. Create `app/Http/Requests/Album/AddPersonAlbumRequest.php` — validates title, persons (array of IDs, min 1, each must exist in `persons` table via `exists:persons,id`), is_and; authorizes via `AlbumPolicy::CAN_EDIT` on null parent; gates on `features.v8` + `ai_vision_face_enabled`. Note: person visibility is NOT validated at creation time (Q-047-05 resolved as Option B).
  3. Add `createPersonAlbum()` method to `AlbumController`.
  4. Add route: `POST /PersonAlbum` in `routes/api_v2.php`.
  5. Update `Delete::do()` — add `person_albums` table check and `deletePersonAlbums()` method mirroring `deleteTagAlbums()` (Q-047-02).
  6. Create `CleanupOrphanedPersonAlbumsJob` — queries `person_albums` whose ID has zero entries in `person_albums_persons`, deletes them via `Delete` action. Dispatch in `PeopleController::destroy()` and register as event listener (Q-047-06).
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ POST /PersonAlbum creates album. DELETE handles PersonAlbum correctly. Orphan cleanup job works.

### I5 – UpdatePersonAlbumRequest + Controller Method + Route (~45 min)

- _Goal:_ Wire the update endpoint.
- _Preconditions:_ I4 create works.
- _Steps:_
  1. Create `app/Http/Requests/Album/UpdatePersonAlbumRequest.php` mirroring `UpdateTagAlbumRequest` — validates album_id, title, description, persons, is_and, sorting, layout, timeline, copyright, is_pinned, slug; gates on feature flags.
  2. Add `updatePersonAlbum()` method to `AlbumController` — updates fields, syncs persons.
  3. Add route: `PATCH /PersonAlbum` in `routes/api_v2.php`.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ PATCH /PersonAlbum updates album correctly.

### I6 – HeadPersonAlbumResource + Album Head Dispatch + EditableBaseAlbumResource (~60 min)

- _Goal:_ Return proper head data when viewing a Person Album; widen EditableBaseAlbumResource (Q-047-03).
- _Preconditions:_ I2 model.
- _Steps:_
  1. Widen `EditableBaseAlbumResource` constructor and `fromModel()` signature to `Album|TagAlbum|PersonAlbum`. Add `persons` field (array of `{id, name}`, default empty). Add `if ($album instanceof PersonAlbum)` branch that sets `$this->persons` (visibility-filtered via `Person::searchable()` scope per Q-047-05) and `$this->is_and` (Q-047-03).
  2. Create `app/Http/Resources/Models/HeadPersonAlbumResource.php` mirroring `HeadTagAlbumResource` — includes `is_person_album: true`, `show_persons` array of `{id, name}` visibility-filtered via `Person::searchable()` scope (Q-047-05), protection policy, rights, editable resource.
  3. Update `HeadAbstractAlbumResource` union type to include `HeadPersonAlbumResource`.
  4. Update `AlbumHeadController::get()` — add `PersonAlbum` case to `match(true)` (Q-047-01).
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ GET /Album::head for a PersonAlbum returns HeadPersonAlbumResource. EditableBaseAlbumResource handles PersonAlbum.

### I7 – Root Listing Extension (~45 min)

- _Goal:_ Show Person Albums in root listing.
- _Preconditions:_ I2 model, I6 resource.
- _Steps:_
  1. Add `person_albums` field to `TopAlbumDTO`.
  2. Update `Top::get()` to query PersonAlbum with visibility filter, gated by feature flags (return empty collection when disabled).
  3. Add `person_albums` field to `RootAlbumResource`.
  4. Update `RootAlbumResource::fromDTO()`.
- _Commands:_ `php artisan test`, `make phpstan`
- _Exit:_ Root listing includes person_albums when feature is enabled.

### I8 – InitConfig Frontend Flag (~30 min)

- _Goal:_ Expose the feature gate to the frontend.
- _Preconditions:_ Config keys exist.
- _Steps:_
  1. Add `is_person_album_enabled` to `InitConfig.php` — computed as `features.v8 === true && ai_vision_face_enabled config is truthy`.
  2. Update TypeScript types in `lychee.d.ts`.
- _Commands:_ `make phpstan`, `npm run check`
- _Exit:_ Frontend receives the flag.

### I9 – Feature Tests (~90 min)

- _Goal:_ Comprehensive backend test coverage.
- _Preconditions:_ I1–I7 complete.
- _Steps:_
  1. Test create Person Album: valid, missing title, empty persons, feature gated off.
  2. Test update Person Album: change persons, toggle AND/OR, feature gated off.
  3. Test delete Person Album (via `Delete::do()` — verifies `deletePersonAlbums()` path).
  4. Test root listing: with/without feature gates.
  5. Test album head: returns HeadPersonAlbumResource; verify `show_persons` is visibility-filtered (Q-047-05).
  6. Test photo resolution: AND logic, OR logic, no matching photos, dismissed faces excluded.
  7. Test access control: non-owner cannot see private photos via person album.
  8. Test photo pagination via `GET /Album::photos` for PersonAlbum (Q-047-01).
  9. Test `CleanupOrphanedPersonAlbumsJob`: delete a person → orphaned PersonAlbum is cleaned up (Q-047-06).
- _Commands:_ `php artisan test --filter=PersonAlbum`
- _Exit:_ All tests green.

### I10 – Frontend: Create Person Album Dialog (~60 min)

- _Goal:_ UI for creating Person Albums.
- _Preconditions:_ I8 flag available.
- _Steps:_
  1. Create `AlbumCreatePersonDialog.vue` mirroring `AlbumCreateTagDialog.vue` — title input, persons multiselect (fetches from `GET /Person`), AND/OR toggle.
  2. Create `PersonsMultiSelect.vue` component — autocomplete/multiselect that queries persons.
  3. Add `is_create_person_album_visible` to `ModalsState` store.
  4. Add `toggleCreatePersonAlbum` to `galleryModals.ts`.
  5. Add "Create Person Album" menu item in `AlbumsHeader.vue` (gated by `initConfig.is_person_album_enabled`).
  6. Wire dialog in `Albums.vue`.
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Dialog opens, person selection works, album is created.

### I11 – Frontend: Album Service + Types + AlbumState (~45 min)

- _Goal:_ TypeScript types and service methods for Person Albums.
- _Preconditions:_ I10 dialog.
- _Steps:_
  1. Add `CreatePersonAlbumData` and `UpdatePersonAlbumData` types to `album-service.ts`.
  2. Add `createPerson()` and `updatePerson()` methods to `AlbumService`.
  3. Update `lychee.d.ts` with `HeadPersonAlbumResource` type (including `is_person_album`, `show_persons`).
  4. Update `AlbumState` to handle `personAlbum` (same pattern as `tagAlbum`).
  5. Update `RootAlbumResource` TypeScript type to include `person_albums`.
- _Commands:_ `npm run check`
- _Exit:_ TypeScript compiles cleanly with new types.

### I12 – Frontend: Root Listing (merge) + Album Properties (~45 min)

- _Goal:_ Merge Person Albums into root listing and support edit properties.
- _Preconditions:_ I11 types.
- _Steps:_
  1. Update `AlbumsState` — add `personAlbums` state field, populate from `data.data.person_albums`. Extend `smartAlbums` getter to concatenate `baseSmartAlbums`, `tagAlbums`, and `personAlbums` (Q-047-04). No new section in `Albums.vue` needed.
  2. Update `AlbumProperties.vue` to handle Person Album variant — show persons multiselect + AND/OR toggle (similar to tag album variant). Read persons from `EditableBaseAlbumResource.persons` field.
  3. Update context menu to handle Person Album (disable album-specific actions like merge/move, same as TagAlbum).
- _Commands:_ `npm run check`, `npm run format`
- _Exit:_ Person Albums appear merged into Smart Albums section; properties panel works.

### I13 – Translations (~30 min)

- _Goal:_ Add translation keys for English and stubs for other languages.
- _Preconditions:_ I10–I12 UI complete.
- _Steps:_
  1. Add English translations: dialog title/info/create button, properties labels, section header.
  2. Add stub translations for other languages (copy English).
- _Commands:_ `npm run check`
- _Exit:_ No missing translation warnings.

### I14 – Quality Gates + Documentation (~30 min)

- _Goal:_ Final quality pass and documentation updates.
- _Preconditions:_ All previous increments.
- _Steps:_
  1. Run `vendor/bin/php-cs-fixer fix`.
  2. Run `npm run format`.
  3. Run `npm run check`.
  4. Run `php artisan test`.
  5. Run `make phpstan`.
  6. Update roadmap with Feature 047 status.
  7. Update knowledge map with PersonAlbum model and HasManyPhotosByPerson relation.
- _Commands:_ See above.
- _Exit:_ All gates green, docs updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-047-01 | I9 / T-047-18 | One person OR mode test |
| S-047-02 | I9 / T-047-19 | Two persons AND mode test |
| S-047-03 | I9 / T-047-20 | Two persons OR mode test |
| S-047-04 | I9 / T-047-14 | Feature v8 off test |
| S-047-05 | I9 / T-047-15 | ai_vision_face off test |
| S-047-06 | I9 / T-047-17 | Root listing enabled test |
| S-047-07 | I9 / T-047-17 | Root listing disabled test |
| S-047-08 | I9 / T-047-16 | Update test |
| S-047-09 | I9 / T-047-16 | Delete test |
| S-047-10 | I9 / T-047-21 | Access control test |
| S-047-11 | I3 / T-047-06 | Override visibility test |
| S-047-12 | I9 / T-047-20 | Empty album test |
| S-047-13 | I2 / T-047-03 | Thumbnail test |
| S-047-14 | I6 / T-047-11 | Head resource test |
| S-047-15 | I9 / T-047-22 | Protection policy test |
| S-047-16 | I10 / T-047-23 | UI gating test (manual) |
| S-047-17 | I9 / T-047-22b | Person deletion → orphan cleanup test |
| S-047-18 | I9 / T-047-22c | show_persons visibility-filtered test |
| S-047-19 | I9 / T-047-22d | Photo pagination for PersonAlbum test |

## Analysis Gate

_Not yet completed. To be signed off after spec/plan/tasks alignment review._

## Exit Criteria

- [ ] All feature tests passing (`php artisan test --filter=PersonAlbum`)
- [ ] PHPStan 0 errors (`make phpstan`)
- [ ] php-cs-fixer clean
- [ ] npm run check clean
- [ ] npm run format clean
- [ ] Roadmap updated
- [ ] Knowledge map updated
- [ ] All tasks marked `[x]` in tasks.md

## Follow-ups / Backlog

- Person Albums in search results (extend search to include person album matching).
- Pagination performance optimization for Person Albums with very large face datasets.
- Person Album cover photo selection (explicit cover, not just first-by-sort).
- Person Album auto-suggestions (suggest creating an album when a person has many photos).
