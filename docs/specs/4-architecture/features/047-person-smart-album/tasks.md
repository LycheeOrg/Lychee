# Feature 047 Tasks – Person Smart Album

_Status: Draft_
_Last updated: 2026-06-28_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### I1 – Database Migration + Config Keys

- [ ] T-047-01 – Create migration for `person_albums` table and `person_albums_persons` junction table (FR-047-01, DO-047-02, DO-047-03).
  _Intent:_ Create database schema for Person Albums.
  _Details:_
  - `person_albums` table: `id` char(24) PK, FK to `base_albums.id` cascadeOnUpdate cascadeOnDelete; `is_and` boolean not null default false.
  - `person_albums_persons` junction: `id` auto-increment PK; `person_id` char(24) FK to `persons.id` cascadeOnDelete; `album_id` char(24) FK to `person_albums.id` cascadeOnUpdate cascadeOnDelete; unique index on `(person_id, album_id)`.
  - Insert config rows: `PA_override_visibility` (string '0', cat 'smart-albums', type_range 'bool', confidentiality 0, description 'When true, Person Albums bypass album-based access control'), `hide_nsfw_in_person_albums` (string '1', cat 'smart-albums', type_range 'bool', confidentiality 0, description 'When true, NSFW photos are hidden from Person Albums').
  - Reversible: down() drops junction, drops person_albums, deletes config rows.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback --step=1`
  - `php artisan migrate`

### I2 – PersonAlbum Model + PersonAlbumBuilder + Integration Points

- [ ] T-047-02 – Create `app/Models/PersonAlbum.php` model (DO-047-01, FR-047-01, FR-047-02).
  _Intent:_ Eloquent model extending BaseAlbum with persons relationship and photo relation.
  _Details:_
  - Extend `BaseAlbum`. Use traits: `ToArrayThrowsNotImplemented`, `HasFactory`, `HasAbstractAlbumProperties`.
  - Attributes: `id` (null), `is_and` (null). Casts: `is_and` → boolean, `min_taken_at` → datetime, `max_taken_at` → datetime.
  - `persons()`: BelongsToMany to `Person::class` via `person_albums_persons` (album_id, person_id).
  - `photos()`: returns `new HasManyPhotosByPerson($this)`.
  - `getThumbAttribute()`: returns `Thumb::createFromQueryable($this->photos(), $this->getEffectivePhotoSorting())`.
  - `newEloquentBuilder()`: returns `new PersonAlbumBuilder($query)`.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-03 – Create `app/Models/Builders/PersonAlbumBuilder.php` (DO-047-01).
  _Intent:_ Custom Eloquent builder mirroring TagAlbumBuilder.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-04 – Register `PersonAlbum` in `AlbumFactory` for album resolution (FR-047-01, Q-047-01).
  _Intent:_ Ensure `findBaseAlbumOrFail()` and `findBaseAlbumsOrFail()` can resolve PersonAlbum IDs.
  _Details:_ Add PersonAlbum query to `AlbumFactory::findBaseAlbumOrFail()` (after TagAlbum), and to `AlbumFactory::findBaseAlbumsOrFail()` (same pattern).
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-04b – Update `GetAlbumPhotosRequest::processValidatedValues()` to try `PersonAlbum::find()` (Q-047-01, S-047-19).
  _Intent:_ Photo pagination request can resolve PersonAlbum IDs.
  _Details:_ Add `PersonAlbum::find()` fallback after `TagAlbum::find()` (line ~120 in `GetAlbumPhotosRequest.php`).
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-04c – Update `AlbumPhotosController::get()` to handle PersonAlbum (Q-047-01, S-047-19).
  _Intent:_ Photo pagination works for PersonAlbum.
  _Details:_ Add `elseif ($album instanceof PersonAlbum)` branch identical to the TagAlbum branch — calls `$album->photos()->with([...])`, applies `SortingDecorator`, returns `PaginatedPhotosResource`.
  _Verification commands:_
  - `make phpstan`

### I3 – HasManyPhotosByPerson Relation

- [ ] T-047-05 – Create `app/Relations/HasManyPhotosByPerson.php` (DO-047-04, FR-047-02, FR-047-03, NFR-047-01).
  _Intent:_ Custom relation resolving photos via faces table with AND/OR logic.
  _Details:_
  - Extend `BaseHasManyPhotos<PersonAlbum>`.
  - `addEagerConstraints()`: load person IDs from album, build subquery on `photos.id` table.
  - Apply `PA_override_visibility` config: when true → `applySensitivityFilter`, when false → `applySearchabilityFilter`.
  - Apply `hide_nsfw_in_person_albums` config for NSFW include/exclude.
  - AND mode: `WHERE EXISTS (SELECT photo_id FROM faces WHERE person_id IN (...) AND photo_id = photos.id AND is_dismissed = false GROUP BY photo_id HAVING COUNT(DISTINCT person_id) = ?)`.
  - OR mode: `WHERE EXISTS (SELECT photo_id FROM faces WHERE person_id IN (...) AND photo_id = photos.id AND is_dismissed = false)`.
  - `match()`: sort photos by effective sorting, assign to album relation.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-06 – Write unit test for HasManyPhotosByPerson AND/OR logic (S-047-01, S-047-02, S-047-03, S-047-11, S-047-12).
  _Intent:_ Verify photo resolution with different person/face configurations.
  _Details:_ Create persons, faces, photos; test AND returns only photos with ALL persons; test OR returns photos with ANY person; test dismissed faces excluded; test empty results.
  _Verification commands:_
  - `php artisan test --filter=HasManyPhotosByPerson`

### I4 – CreatePersonAlbum Action + Request + Route + Delete + Cleanup Job

- [ ] T-047-07 – Create `app/Actions/Album/CreatePersonAlbum.php` (FR-047-01).
  _Intent:_ Action to create a PersonAlbum and sync persons.
  _Details:_ Create album, set title/owner_id/is_and, save, sync person IDs to junction table, create statistics record.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-08 – Create `app/Http/Requests/Album/AddPersonAlbumRequest.php` (FR-047-01, FR-047-08, NFR-047-03, Q-047-05).
  _Intent:_ Request validation and authorization for creating a Person Album.
  _Details:_
  - Authorize: `Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, null])` AND `config('features.v8') === true` AND `ai_vision_face_enabled` config is truthy.
  - Rules: title required + TitleRule, persons array required min 1, persons.* required string + `exists:persons,id`, is_and required boolean.
  - Person visibility is NOT validated at creation time (Q-047-05 resolved as Option B — filtering happens in resources).
  - Process: extract title, person_ids, is_and.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-09 – Add `createPersonAlbum()` to `AlbumController` and route (API-047-01).
  _Intent:_ Wire the POST /PersonAlbum endpoint.
  _Details:_ Add method to AlbumController calling CreatePersonAlbum action. Add route `Route::post('/PersonAlbum', ...)` in api_v2.php.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-09b – Update `Delete::do()` with `deletePersonAlbums()` method (FR-047-06, Q-047-02).
  _Intent:_ Person Albums are correctly deleted via the existing album delete flow.
  _Details:_ At the start of `Delete::do()`, query `DB::table('person_albums')` to find person album IDs. Call new `deletePersonAlbums()` method that cleans up purchasables, live_metrics, access_permissions, statistics, person_albums, base_albums (same pattern as `deleteTagAlbums()`). Remove those IDs from remaining set before regular album processing.
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

- [ ] T-047-09c – Create `CleanupOrphanedPersonAlbumsJob` (FR-047-13, DO-047-05, Q-047-06).
  _Intent:_ Auto-delete PersonAlbums left with zero persons after a Person deletion.
  _Details:_
  - Job queries `person_albums` whose ID has zero entries in `person_albums_persons` and deletes them via the `Delete` action.
  - Triggered explicitly in `PeopleController::destroy()` code path (dispatched after person deletion, not via observer).
  - Also registered as an event listener so other deletion paths (merge, CLI) trigger cleanup.
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

### I5 – UpdatePersonAlbumRequest + Controller Method + Route

- [ ] T-047-10 – Create `app/Http/Requests/Album/UpdatePersonAlbumRequest.php` and add `updatePersonAlbum()` to controller + route (FR-047-05, API-047-02).
  _Intent:_ PATCH /PersonAlbum endpoint for editing Person Albums.
  _Details:_
  - Request mirrors `UpdateTagAlbumRequest` structure. Validates album_id, title, description, persons, is_and, sorting, layout, timeline, copyright, is_pinned, slug. Feature-gated authorization.
  - Controller method: update album fields, sync persons, save.
  - Route: `Route::patch('/PersonAlbum', ...)`.
  _Verification commands:_
  - `make phpstan`

### I6 – HeadPersonAlbumResource + Album Head Dispatch + EditableBaseAlbumResource

- [ ] T-047-10b – Widen `EditableBaseAlbumResource` for PersonAlbum (Q-047-03, FR-047-05).
  _Intent:_ `EditableBaseAlbumResource` accepts PersonAlbum without type error.
  _Details:_
  - Change constructor and `fromModel()` signature to `Album|TagAlbum|PersonAlbum`.
  - Add `persons` field: `array` of `{id: string, name: string}`, default `[]`.
  - Add `if ($album instanceof PersonAlbum)` branch: set `$this->persons` (visibility-filtered via `Person::searchable($user_id)` scope per Q-047-05) and `$this->is_and`.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-11 – Create `app/Http/Resources/Models/HeadPersonAlbumResource.php` (FR-047-12, API-047-04, Q-047-05).
  _Intent:_ Resource for Person Album head data with visibility-filtered persons.
  _Details:_
  - Spatie Data class mirroring `HeadTagAlbumResource`.
  - Fields: `id`, `title`, `slug`, `owner_name`, `copyright`, `is_person_album` (true), `show_persons` (array of `{id, name}`, visibility-filtered via `Person::searchable()` scope — omit non-searchable persons invisible to current user per Q-047-05), `policy`, `rights`, `preFormattedData`, `editable`, `statistics`.
  - Constructor accepts `PersonAlbum`, loads persons via `$person_album->persons`, applies visibility filter.
  _Verification commands:_
  - `make phpstan`

- [ ] T-047-12 – Update `HeadAbstractAlbumResource` union type and `AlbumHeadController` dispatch (API-047-04, Q-047-01).
  _Intent:_ Album head endpoint returns correct resource for PersonAlbum.
  _Details:_ Add `HeadPersonAlbumResource` to the `HeadAbstractAlbumResource` `$resource` union type. Add `$request->album() instanceof PersonAlbum` case to `AlbumHeadController::get()` `match(true)`.
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

### I7 – Root Listing Extension

- [ ] T-047-13 – Extend `TopAlbumDTO`, `Top::get()`, and `RootAlbumResource` with `person_albums` (FR-047-04, API-047-03).
  _Intent:_ Person Albums appear in root album listing.
  _Details:_
  - Add `person_albums` Collection parameter to `TopAlbumDTO`.
  - In `Top::get()`: query `PersonAlbum` with visibility filter, gated by `config('features.v8')` and `ai_vision_face_enabled` config. Return empty collection when disabled.
  - Add `person_albums` field to `RootAlbumResource` (between tag_albums and pinned_albums).
  - Update `RootAlbumResource::fromDTO()`.
  _Verification commands:_
  - `make phpstan`
  - `php artisan test`

### I8 – InitConfig Frontend Flag

- [ ] T-047-14-be – Add `is_person_album_enabled` flag to `InitConfig.php` (NFR-047-05, FR-047-08).
  _Intent:_ Frontend can check whether Person Album feature is active.
  _Details:_ Computed as `config('features.v8') === true && $config_manager->getValueAsBool('ai_vision_face_enabled')`. Add as public bool property.
  _Verification commands:_
  - `make phpstan`

### I9 – Feature Tests

- [ ] T-047-14 – Test: create Person Album with feature v8 disabled → 403 (S-047-04, FR-047-08).
  _Intent:_ Feature gating works for creation.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-15 – Test: create Person Album with ai_vision_face_enabled disabled → 403 (S-047-05, FR-047-08).
  _Intent:_ Feature gating works for ai_vision config.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-16 – Test: create, update, and delete Person Album (S-047-08, S-047-09, FR-047-01, FR-047-05, FR-047-06).
  _Intent:_ Full CRUD lifecycle.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-17 – Test: root listing includes/excludes person_albums based on feature gates (S-047-06, S-047-07, FR-047-04, NFR-047-04).
  _Intent:_ Root listing respects feature gating.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-18 – Test: Person Album with one person (OR mode) resolves correct photos (S-047-01, FR-047-02).
  _Intent:_ Single-person album shows all photos with that person's face.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-19 – Test: Person Album with two persons (AND mode) resolves correct photos (S-047-02, FR-047-02).
  _Intent:_ AND mode only returns photos containing faces for ALL selected persons.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-20 – Test: Person Album with two persons (OR mode) resolves correct photos (S-047-03, FR-047-02, S-047-12).
  _Intent:_ OR mode returns photos containing faces for ANY selected person; also tests empty result case.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-21 – Test: access control — non-owner cannot see private photos via person album (S-047-10, FR-047-03).
  _Intent:_ Photo access control is enforced through PersonAlbum.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-22 – Test: Person Album supports protection policy (S-047-15, FR-047-07).
  _Intent:_ Password protection works on Person Albums.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-22b – Test: Person deletion → orphaned PersonAlbum cleaned up by `CleanupOrphanedPersonAlbumsJob` (S-047-17, FR-047-13, Q-047-06).
  _Intent:_ Verify orphan cleanup job deletes PersonAlbums with zero persons remaining.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-22c – Test: `HeadPersonAlbumResource.show_persons` omits non-searchable persons invisible to current user (S-047-18, FR-047-12, Q-047-05).
  _Intent:_ Verify visibility filtering in head resource and editable resource.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

- [ ] T-047-22d – Test: Photo pagination via `GET /Album::photos` works for PersonAlbum (S-047-19, Q-047-01).
  _Intent:_ Verify `AlbumPhotosController` PersonAlbum branch returns paginated photos.
  _Verification commands:_
  - `php artisan test --filter=PersonAlbum`

### I10 – Frontend: Create Person Album Dialog

- [ ] T-047-23 – Create `AlbumCreatePersonDialog.vue` component (UI-047-01, UI-047-02, UI-047-05, S-047-16).
  _Intent:_ Dialog for creating Person Albums with title input, persons multiselect, and AND/OR toggle.
  _Details:_
  - Mirror `AlbumCreateTagDialog.vue` structure.
  - Persons multiselect: fetch from `GET /api/v2/Person` (existing endpoint), display as selectable chips.
  - AND/OR toggle with `ToggleSwitch`.
  - Submit calls `AlbumService.createPerson()`.
  - Gated by `initConfig.is_person_album_enabled`.
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [ ] T-047-24 – Add `is_create_person_album_visible` to `ModalsState` and `toggleCreatePersonAlbum` to `galleryModals.ts` (UI-047-01).
  _Intent:_ Modal state management for Person Album creation dialog.
  _Verification commands:_
  - `npm run check`

- [ ] T-047-25 – Add "Create Person Album" menu item to `AlbumsHeader.vue` (UI-047-01, NFR-047-05).
  _Intent:_ Menu entry gated by `initConfig.is_person_album_enabled`.
  _Verification commands:_
  - `npm run check`

- [ ] T-047-26 – Wire `AlbumCreatePersonDialog` in `Albums.vue` (UI-047-01).
  _Intent:_ Dialog mounted and triggered from Albums view.
  _Verification commands:_
  - `npm run check`

### I11 – Frontend: Album Service + Types + AlbumState

- [ ] T-047-27 – Add `CreatePersonAlbumData`, `UpdatePersonAlbumData`, `createPerson()`, `updatePerson()` to `album-service.ts` (API-047-01, API-047-02).
  _Intent:_ TypeScript service layer for Person Album API calls.
  _Verification commands:_
  - `npm run check`

- [ ] T-047-28 – Update `lychee.d.ts` with `HeadPersonAlbumResource` type and `person_albums` in `RootAlbumResource` (FR-047-12, FR-047-04).
  _Intent:_ TypeScript type definitions for Person Album responses.
  _Details:_ Add `HeadPersonAlbumResource` with `is_person_album`, `show_persons: {id: string, name: string}[]`. Add `person_albums` to `RootAlbumResource`. Update `HeadAbstractAlbumResource` union.
  _Verification commands:_
  - `npm run check`

- [ ] T-047-29 – Update `AlbumState` to handle `personAlbum` (FR-047-12).
  _Intent:_ Store correctly identifies and stores PersonAlbum head data.
  _Details:_ Add `personAlbum` property (same pattern as `tagAlbum`). Set in `loadAlbums()` dispatch.
  _Verification commands:_
  - `npm run check`

### I12 – Frontend: Root Listing (merge) + Album Properties

- [ ] T-047-30 – Merge `personAlbums` into `smartAlbums` getter in `AlbumsState` (UI-047-04, FR-047-04, Q-047-04).
  _Intent:_ Person Albums appear in the Smart Albums section alongside smart and tag albums.
  _Details:_ Add `personAlbums` state field to `AlbumsState`, populate from `data.data.person_albums`. Extend `smartAlbums` getter to concatenate `baseSmartAlbums`, `tagAlbums`, and `personAlbums`. No new section or `AlbumThumbPanel` needed in `Albums.vue`.
  _Verification commands:_
  - `npm run check`

- [ ] T-047-31 – Extend `AlbumProperties.vue` for Person Album variant (UI-047-03, FR-047-05).
  _Intent:_ Properties panel shows persons multiselect + AND/OR toggle for Person Albums.
  _Details:_ Add `savePersonAlbum()` method (mirror `saveTagAlbum()`). Conditionally render persons input when `albumStore.personAlbum !== undefined`. Read person data from `EditableBaseAlbumResource.persons` field.
  _Verification commands:_
  - `npm run check`

- [ ] T-047-32 – Update context menu for Person Album (same restrictions as TagAlbum).
  _Intent:_ Disable album-specific actions (merge, move, etc.) for Person Albums.
  _Verification commands:_
  - `npm run check`

### I13 – Translations

- [ ] T-047-33 – Add English translation keys for Person Album UI.
  _Intent:_ Translations for dialog, properties, section header.
  _Details:_ Keys: `dialogs.new_person_album.info`, `dialogs.new_person_album.title`, `dialogs.new_person_album.set_persons`, `dialogs.new_person_album.create`, `gallery.album.properties.all_persons_must_match`, `gallery.smart_albums.person_albums` (section header).
  _Verification commands:_
  - `npm run check`

- [ ] T-047-34 – Add stub translations for other languages (copy English keys).
  _Intent:_ Prevent missing translation warnings.
  _Verification commands:_
  - `npm run check`

### I14 – Quality Gates + Documentation

- [ ] T-047-35 – Run full quality gate: php-cs-fixer, phpunit, phpstan, npm format, npm check.
  _Intent:_ All automated quality checks pass.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`

- [ ] T-047-36 – Update roadmap with Feature 047 entry.
  _Intent:_ Roadmap reflects current feature status.
  _Verification commands:_
  - Manual review of `docs/specs/4-architecture/roadmap.md`.

- [ ] T-047-37 – Update knowledge map with PersonAlbum model and HasManyPhotosByPerson relation.
  _Intent:_ Knowledge map reflects new architectural components.
  _Verification commands:_
  - Manual review of `docs/specs/4-architecture/knowledge-map.md`.

## Notes / TODOs

- The `PersonAlbum` model follows the exact same inheritance chain as `TagAlbum`: extends `BaseAlbum` → `ForwardsToParentImplementation` → `BaseAlbumImpl` for base_albums table delegation.
- The `faces` table already has indexes on `person_id` and `photo_id` (from the 2026_03_21_000002 migration). No additional indexes needed.
- The `PersonAlbum` deletion cascades through `base_albums` FK, same as TagAlbum. No changes to the `Delete` action are needed.
- Persons multiselect in the frontend should use the existing `GET /api/v2/Person` endpoint (PeopleController::index) which already handles visibility/searchability filtering.
