# Feature 050 Tasks – Album Tags

_Status: Implementation complete (T-050-17 manual browser verification pending — sandbox frontend toolchain unavailable)_
_Last updated: 2026-07-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.

## Checklist

### I1 – Schema & core relations

- [x] T-050-01 – Failing relation test + `albums_tags` migration + `Album::tags()`/`Tag::albums()` relations (F-050-01).
  _Intent:_ Add a failing PHPUnit test asserting `Album::tags()->sync([...])` round-trips and `Tag::albums()` returns the album back, then create migration `xxxx_create_albums_tags_table.php` (mirror `photos_tags` shape: bigIncrements id, `album_id` char(24) FK→`albums.id` cascade update/delete, `tag_id` unsigned bigint FK→`tags.id` cascade update/delete, unique(`album_id`,`tag_id`), individual indexes), then add the two relation methods.
  _Verification commands:_
  - `php artisan test --filter=AlbumTagRelationTest`
  - `make phpstan`
  _Notes:_ DO-050-01, DO-050-02.

### I2 – Cleanup/merge/rename/delete album-awareness

- [x] T-050-02 – Failing test: tag used only by an album survives `cleanupUnusedTags()` (F-050-09, S-050-09).
  _Intent:_ Add failing test in `TagCleanupTrait`'s test suite creating a `Tag` attached only via `albums_tags`, asserting it is NOT deleted after cleanup runs (today it would be, since cleanup only checks `photos_tags`). Then fix `TagCleanupTrait::cleanupUnusedTags()`'s `whereNotExists` to also check `albums_tags`.
  _Verification commands:_
  - `php artisan test --filter=TagCleanupTest`
  - `make phpstan`
  _Notes:_ This must land before T-050-05 (album tag UI) ships, or a tag added to an album with no matching photo tag could be silently deleted by any concurrent photo-tag cleanup.

- [x] T-050-03 – Failing test: non-admin `MergeTag` only transfers own album associations (F-050-09, S-050-08).
  _Intent:_ Add failing test: two users each have an album tagged "car"; user A merges "car" into "vehicle"; assert only user A's album moved, user B's album still tagged "car". Then add `MergeTag::handleAlbums()` mirroring the existing `handleTagAlbums()` method (same ownership-scoping `whereExists`/`base_albums.owner_id` pattern, but against `albums`/`albums_tags`), called from `MergeTag::do()` alongside `handlePhotos()`/`handleTagAlbums()`.
  _Verification commands:_
  - `php artisan test --filter=MergeTagTest`
  _Notes:_ `EditTag` delegates to `MergeTag`, so no separate `EditTag` change is needed once this lands.

- [x] T-050-04 – Failing test: admin rename moves all album associations regardless of owner; `DeleteTag` removes album associations (F-050-09, S-050-07).
  _Intent:_ Extend `DeleteTag` test coverage: deleting a tag removes its `albums_tags` rows (scoped to the acting user unless admin, same pattern as its existing `photos_tags` deletion). Extend `DeleteTag::do()` accordingly. Add an admin-rename test confirming `MergeTag::handleAlbums()` ignores ownership scoping for admins (mirrors existing `handlePhotos()`/`handleTagAlbums()` admin behaviour).
  _Verification commands:_
  - `php artisan test --filter=DeleteTagTest`
  - `php artisan test --filter=MergeTagTest`
  - `make phpstan`

### I3 – `PATCH /Album` accepts `tags`

- [x] T-050-05 – Failing feature test: `PATCH /Album` with `tags` persists new/reused `Tag`s on a regular album (F-050-02, S-050-01).
  _Intent:_ Add feature test submitting `tags: ["vacation","greece"]` for an existing `Album`, asserting both `Tag` rows exist (reusing a pre-existing case-insensitive match) and `albums_tags` links correctly. Then add `HasTags`/`HasTagsTrait` to `UpdateAlbumRequest` (`tags => present|array`, `tags.* => required|string|min:1`), and in `AlbumController::updateAlbum` add `Tag::from($request->tags()); $album->tags()->sync($tag_models->pluck('id')->all());` mirroring `updateTagAlbum()`.
  _Verification commands:_
  - `php artisan test --filter=UpdateAlbumTagsTest`
  - `make phpstan`

- [x] T-050-06 – Failing feature test: submitting `tags: []` clears all album tags (F-050-02, S-050-02).
  _Intent:_ Extend the above test file with an empty-array case; confirm `sync([])` behaviour already covers it (no extra code expected — verification only, but written test-first).
  _Verification commands:_
  - `php artisan test --filter=UpdateAlbumTagsTest`

- [x] T-050-07 – `EditableBaseAlbumResource` Album-branch populates `tags` (F-050-03).
  _Intent:_ Add failing test asserting `EditableBaseAlbumResource::fromModel($album)->tags` reflects `$album->tags` for an `Album` instance (currently only populated for `TagAlbum`). Add the `instanceof Album` branch.
  _Verification commands:_
  - `php artisan test --filter=EditableBaseAlbumResourceTest`
  - `make phpstan`
  _Notes:_ DO-050-05. Add a one-line comment noting the field is intentionally dual-purpose (Album's own tags vs TagAlbum's matching-criteria tags).

### I4 – `/tags` split counts + album-only visibility

- [x] T-050-08 – Failing feature test: `GET /Tags` lists an album-only tag with correct split counts and non-admin scoping (F-050-08, S-050-09, S-050-10).
  _Intent:_ Add feature test: tag attached only to user A's album (0 photos) → appears for user A with `num_photos: 0, num_albums: 1`; invisible to user B (non-admin, no access); visible to admin. Then rewrite `ListTags`'s query to OR-in album usage in the visibility filter (same non-admin ownership scoping extended to albums), and replace `TagResource.num` with `num_photos`/`num_albums`.
  _Verification commands:_
  - `php artisan test --filter=ListTagsTest`
  - `make phpstan`
  _Notes:_ DO-050-03. Use `havingRaw`-style portable SQL (existing code comment warns against `having('num', ...)` on Postgres aliasing — apply the same caution to the new columns).

### I5 – `GET /Tag` returns albums

- [x] T-050-09 – Failing feature test: `GET /Tag` includes accessible albums for the tag (F-050-04, S-050-03, S-050-04).
  _Intent:_ Add feature test: tag used by one album + some photos → response has both non-empty `albums` and `photos`; non-admin sees only their own accessible album(s) (mirrors existing photo-ownership filter in this action). Extend `GetTagWithPhotos::do()` to also query `Album::query()->whereHas('tags', ...)` filtered through existing `AlbumQueryPolicy`/visibility rules, and extend `TagWithPhotosResource` with an `albums` field.
  _Verification commands:_
  - `php artisan test --filter=GetTagWithPhotosTest`
  - `make phpstan`
  _Notes:_ DO-050-04. Confirm the exact album-tile resource shape needed by the v8 grid component before finalising (coordinate with T-050-16).

- [x] T-050-10 – Failing feature test: tag with zero accessible albums returns an empty `albums` array (not an error) (S-050-04).
  _Intent:_ Extend the same test file with a photos-only tag, asserting `albums: []`.
  _Verification commands:_
  - `php artisan test --filter=GetTagWithPhotosTest`

### I6 – `AlbumSearch` tag matching (Album-only, never TagAlbum)

- [x] T-050-11 – Failing test: `AlbumTagStrategy` matches a tagged `Album` via `queryAlbums()` (F-050-06, S-050-05).
  _Intent:_ Add failing test: search token `tag:vacation` returns an `Album` carrying that tag from `AlbumSearch::queryAlbums()`. Create `app/Actions/Search/Strategies/Album/AlbumTagStrategy.php` mirroring `Strategies/TagStrategy` (exact/prefix match against `Album::tags`).
  _Verification commands:_
  - `php artisan test --filter=AlbumSearchTagTest`

- [x] T-050-12 – Failing regression test: `tag:` token has no effect on `queryTagAlbums()` (F-050-06, NFR-050-01).
  _Intent:_ Add failing test asserting a `tag:` token against `AlbumSearch::queryTagAlbums()` does not use `AlbumTagStrategy` (i.e., behaves exactly as before — token skipped, since `TagAlbum` has no album-tags relation and must never be probed for one). Split `AlbumSearch::buildAlbumStrategyRegistry()` into two builders (or add an `include_tags: bool` parameter) so only `queryAlbums()`'s registry includes `'tag' => new AlbumTagStrategy()`.
  _Verification commands:_
  - `php artisan test --filter=AlbumSearchTagTest`
  - `php artisan test --filter=AlbumSearchTest` (existing suite — regression)
  _Notes:_ This is the concrete implementation of NFR-050-01.

- [x] T-050-13 – Wire `tag` modifier into `SearchController`/`AlbumSearch::queryAlbums()` end-to-end (F-050-06, S-050-05).
  _Intent:_ Confirm `SearchController::search()` → `AlbumSearch::queryAlbums()` path returns the tagged album for a `GET /Search?query=tag:vacation` request (feature test at the controller level, not just the `AlbumSearch` unit level).
  _Verification commands:_
  - `php artisan test --filter=SearchControllerTest`

- [x] T-050-14 – Failing test: plain-text search matches album tag names (F-050-07, S-050-06).
  _Intent:_ Add failing test: bare query `vacation` (no modifier) matches an album whose only relevant field is a tag named "vacation" (title/description don't contain the word). Extend `AlbumFieldLikeStrategy`'s `$column === null` branch to OR-in `whereHas('tags', ...)`, guarded so it is never applied against `TagAlbum` queries (reuse the same include/exclude mechanism from T-050-12).
  _Verification commands:_
  - `php artisan test --filter=AlbumSearchTagTest`
  - `make phpstan`

### I7 – v8 UI: Album properties Tags field

- [x] T-050-15 – Add Tags field to `AlbumProperties.vue` (F-050-03, UI-050-01).
  _Intent:_ Bind the existing `TagsInput` v8 component to `editable.tags` in `resources/js/v8/components/forms/album/AlbumProperties.vue`; include `tags` in the save payload sent to `PATCH /Album`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Manual verification via `/verify`: open an album's properties, add tags "vacation"/"greece", save, reopen — tags persist.

### I8 – v8 UI: `/tag/{id}` Albums section

- [x] T-050-16 – Extend `TagState.ts`/`TagsService` + `TagPanel.vue` Albums section (F-050-05, UI-050-02, S-050-03).
  _Intent:_ Extend `TagState.ts` to store `albums` from `TagsService.get()`'s response; add an album-tile grid (reuse the grid component already used in `Albums.vue`/`Search.vue`) to `TagPanel.vue`, rendered above `PhotoThumbPanel`, `v-if` guarded on `albums.length > 0`.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Manual verification: tag with albums+photos, albums-only (S-050-04), photos-only, neither.

- [ ] T-050-17 – Manual verification: tag with only an album shows Albums section + existing empty-state photos message (S-050-04).
  _Intent:_ Exercise via `/verify` (or manual browser check) since this is UI behaviour not covered by `npm run check`.
  _Verification commands:_
  - Manual: navigate to `/tag/{id}` for an album-only tag; confirm Albums section renders and Photos grid shows `gallery.album.no_results`.

### I9 – v8 UI: `/tags` split-count display

- [x] T-050-18 – Update `TagsManagement.vue` to show split `num_photos`/`num_albums` chips (F-050-08, UI-050-03).
  _Intent:_ Replace the single `tag.num > 0` chip with two chips (`num_photos`, `num_albums`), each hidden when zero, mirroring the existing `v-if` pattern.
  _Verification commands:_
  - `npm run check`
  _Notes:_ Manual verification: album-only tag row shows only the album chip.

### I10 – Docs & quality gate

- [x] T-050-19 – Update `docs/specs/4-architecture/tag-system.md`.
  _Intent:_ Document the new `albums_tags` pivot, `Album::tags()`/`Tag::albums()` relations, and the revised `cleanupUnusedTags()` "used by photo OR tag-album OR album" definition.
  _Verification commands:_ N/A (docs only).

- [x] T-050-20 – Full quality gate + Implementation Drift Gate report.
  _Intent:_ Run the full gate, append the Implementation Drift Gate report to `plan.md`, update roadmap status from Active to Completed.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix` — clean (1 file auto-fixed on first pass; `--dry-run` clean thereafter)
  - `npm run format` / `npm run check` — **not run**: this sandbox's frontend toolchain is broken independent of this feature (missing `@iconify-json/lucide` breaks the `precheck` vite build; `vue-tsc` is not installed as a local devDependency binary). Confirmed pre-existing via `git stash` on a clean checkout. v8 Vue changes were reviewed by hand instead.
  - `php artisan test` — 2798 passed, 2 failed (both pre-existing/unrelated `PhotoEditTest` timezone assertions, confirmed via `git stash`)
  - `make phpstan` — 2602 files, 0 errors

## Notes / TODOs

- T-050-09's exact album-tile resource choice is deferred to implementation time (spec/plan flag it as TBD — pick the lightest existing resource the v8 grid already consumes, or a new minimal one if existing ones over-fetch).
- I6's registry-split mechanism (two builder methods vs. an `include_tags` flag) is an implementation-time choice; either satisfies NFR-050-01 as long as `queryTagAlbums()` never gains the `tag` strategy.
