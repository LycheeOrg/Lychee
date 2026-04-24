# Feature 035 Tasks – Chunked Archive Download

_Status: Draft_
_Last updated: 2026-04-12_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`NFR-`), and scenario IDs (`S-035-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – DB Configuration

- [ ] T-035-01 – Create config migration for chunked download settings (FR-035-01, FR-035-02).
  _Intent:_ Add `download_archive_chunked` (BOOL, default `0`) and `download_archive_chunk_size` (INT, default `300`) to the `configs` table in the `Image Processing` category, matching the pattern of `2026_02_28_000002_add_raw_download_enabled_config.php`.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback`
  - `php artisan migrate`
  _Notes:_ Use `BaseConfigMigration`; set `is_expert = false`, `is_secret = false`. Order after `raw_download_enabled`.

- [ ] T-035-02 – Extend `InitConfig` with `is_download_archive_chunked` and update TypeScript types (FR-035-07, S-035-11).
  _Intent:_ Add `is_download_archive_chunked: bool` public property to `app/Http/Resources/GalleryConfigs/InitConfig.php`, populated from `ConfigManager`. **Do not** add `download_archive_chunk_size` — that stays server-side. Update `resources/js/lychee.d.ts` `InitConfig` interface with `is_download_archive_chunked` only. Add `is_download_archive_chunked` ref to `useLycheeStateStore` (no `download_archive_chunk_size`).
  _Verification commands:_
  - `npm run type-check`
  - `php artisan test --filter=InitConfig`
  _Notes:_ Follow the same pattern as `is_raw_download_enabled` in `InitConfig`.

### I2 – Backend: Chunk Metadata Endpoint

- [ ] T-035-03 – Write PHPUnit feature test `ZipChunksTest` before implementation (S-035-02, S-035-10).
  _Intent:_ Stage-first tests for `GET /Zip/chunks`. Covers: valid album_ids → correct `total_chunks` + `total_photos`; missing/invalid album_ids → 4xx; unauthenticated guest on non-public album → 403.
  _Verification commands:_
  - `php artisan test --filter=ZipChunksTest` (expected: red until T-035-04 implemented)
  _Notes:_ Place in `tests/Feature_v2/Zip/ZipChunksTest.php`.

- [ ] T-035-04 – Implement `ZipChunksRequest` and `ZipChunksController::index()` + route (FR-035-03, API-035-01, S-035-02, S-035-10).
  _Intent:_ `ZipChunksRequest` reuses album/photo resolution from `ZipRequest`. Controller counts total photos (COUNT query, no file I/O), computes `total_chunks = max(1, ceil(total / chunk_size))`, returns JSON `{ total_chunks, total_photos }`. Register `GET /Zip/chunks` in `routes/api_v2.php` before the existing `/Zip` route.
  _Verification commands:_
  - `php artisan test --filter=ZipChunksTest`
  - `make phpstan` (or `./vendor/bin/phpstan analyse`)
  _Notes:_ When chunked mode is disabled, return `{ total_chunks: 1, total_photos: n }` (FR-035-03).

### I3 – Backend: Chunked Archive Slicing

- [ ] T-035-05 – Write PHPUnit feature tests for chunked `GET /Zip` before implementation (S-035-01, S-035-03 through S-035-07, S-035-11, S-035-12).
  _Intent:_ Stage-first tests covering: no-chunk param → single archive (S-035-01); chunk=1 with 350 photos → 300-photo archive named `.part1.zip` (S-035-03); chunk=2 → 50-photo archive named `.part2.zip` (S-035-04); chunk=0 → 422 (S-035-05); chunk=99 (> total) → 422 (S-035-06); no chunk param + chunked mode ON → single archive, Option A (S-035-07); chunk_size < 1 rejected (S-035-11); filenames disjoint across both chunks (S-035-12).
  _Verification commands:_
  - `php artisan test --filter=ZipChunkedDownloadTest` (expected: red until T-035-06/07/08 implemented)
  _Notes:_ Place in `tests/Feature_v2/Zip/ZipChunkedDownloadTest.php`.

- [ ] T-035-06 – Add `chunk` parameter to `ZipRequest` with validation (FR-035-04, FR-035-05, NFR-035-03, S-035-05, S-035-06, S-035-11).
  _Intent:_ Add `chunk` to `ZipRequest::rules()` as `sometimes|integer|min:1`. Add `chunkSlice(): ?ChunkSlice` accessor that computes offset/limit from `chunk` and `download_archive_chunk_size` (read from `ConfigManager` — not from the request). Validate that `chunk` ≤ `total_chunks` (or defer this to controller). Introduce `ChunkSlice` value object in `app/DTO/ChunkSlice.php`.
  _Verification commands:_
  - `php artisan test --filter=ZipChunkedDownloadTest`
  - `make phpstan`
  _Notes:_ Per Q-035-01 → Option A: absent `chunk` = single-archive download regardless of chunked mode setting.

- [ ] T-035-07 – Refactor `BaseArchive::do()` to pre-generate filenames then apply `?ChunkSlice` (FR-035-04, NFR-035-01, NFR-035-04, S-035-03, S-035-04, S-035-12).
  _Intent:_ Refactor the archive generation pipeline in two passes:
  1. **Pre-generation pass (no streaming):** traverse the full photo list (all albums/pages) and assign a unique filename to every photo using the existing `makeUnique`/`createValidTitle` logic, building a `photo_id → filename` map.
  2. **Streaming pass:** when `ChunkSlice` is non-null, skip `offset` photos and stop after `limit` photos; use the pre-assigned filename from the map when adding each file to the ZIP.
  This guarantees filenames are globally unique across all chunks. Update `Content-Disposition` filename: when `chunk` is present, produce `<title>.part<n>.zip`. Ensure both `Archive32` and `Archive64` subclasses pass the slice through. Update `AlbumController::getArchive()` to pass `$request->chunkSlice()` to archive actions.
  _Verification commands:_
  - `php artisan test --filter=ZipChunkedDownloadTest`
  - `php artisan test --filter=ZipTest` (existing tests must still pass)
  - `make phpstan`
  _Notes:_ The pre-generation pass only builds a lightweight map (`photo_id → string`), not a collection of photo objects — NFR-035-01 (no full load into memory) still holds. Verify that `Album::get_photos()` uses a stable sort; add `->orderBy('id')` if not already present.

- [ ] T-035-08 – Implement out-of-range chunk validation (S-035-05, S-035-06, NFR-035-03).
  _Intent:_ When `chunk` param is present but exceeds `total_chunks`, return 422 before streaming begins. This requires a COUNT query in `ZipRequest::authorize()` or a dedicated validation rule. Ensure chunk=0 is rejected by `min:1` rule (already covered by T-035-06).
  _Verification commands:_
  - `php artisan test --filter=ZipChunkedDownloadTest`
  _Notes:_ Keep the COUNT query lightweight (same query as API-035-01 uses).

- [ ] T-035-09 – Encode Q-035-01 Option A in spec and implement (S-035-07).
  _Intent:_ Q-035-01 is resolved as **Option A** (2026-04-12): absent `chunk` param → single-archive download, regardless of chunked-mode setting. Ensure ZipRequest and BaseArchive implement this branch; add or confirm the corresponding test case for S-035-07.
  _Verification commands:_
  - `php artisan test --filter=ZipChunkedDownloadTest`
  _Notes:_ No further clarification needed — spec and open-questions.md already updated.

### I4 – Frontend: Sequential Chunk Download

- [ ] T-035-10 – Add `getChunkCount()` and `downloadChunk()` to `album-service.ts` (FR-035-06, API-035-01, API-035-02).
  _Intent:_ `getChunkCount(albumIds, variant): Promise<ZipChunksData>` calls `GET /Zip/chunks`. `downloadChunk(albumIds, variant, chunk): Promise<void>` calls `GET /Zip?chunk=n` using `fetch()`, converts the blob to an object URL, programmatically clicks a hidden `<a>` to trigger a save dialog, then revokes the object URL. Update `lychee.d.ts` with `ZipChunksData` type.
  _Verification commands:_
  - `npm run type-check`
  _Notes:_ Use `.then()` chains, not `async/await` (project convention). Sequential ordering is enforced by chaining `.then()` calls. `download_archive_chunk_size` is **not** needed client-side.

- [ ] T-035-11 – Write Vitest unit tests for sequential download logic (S-035-08, UI-035-01, UI-035-02).
  _Intent:_ Mock `AlbumService.getChunkCount()` returning `{ total_chunks: 3, total_photos: 650 }` and `AlbumService.downloadChunk()`. Assert that `DownloadAlbum.vue` calls `downloadChunk` three times in order, and that the progress label transitions `"Downloading part 1 / 3"` → `"Downloading part 2 / 3"` → `"Downloading part 3 / 3"`.
  _Verification commands:_
  - `npm run test`
  _Notes:_ Place in `resources/js/components/modals/__tests__/DownloadAlbum.spec.ts`.

- [ ] T-035-12 – Update `DownloadAlbum.vue` for chunked sequential download with progress and error handling (FR-035-06, S-035-08, S-035-09, UI-035-01 through UI-035-04).
  _Intent:_ When `is_download_archive_chunked` is true: call `getChunkCount()`, then loop `downloadChunk()` for each part sequentially; display `"Downloading part k / n"` label; on error show a toast and abort remaining parts. When `is_download_archive_chunked` is false: preserve current `location.href` approach unchanged. No need to read `download_archive_chunk_size` client-side — chunk count comes from the API.
  _Verification commands:_
  - `npm run test`
  - `npm run type-check`
  _Notes:_ Use PrimeVue `useToast()` for error notification. Do not use `async/await` (project convention).

- [ ] T-035-13 – Update `DownloadPhoto.vue` for chunked sequential download (FR-035-06, S-035-08).
  _Intent:_ Apply the same chunked-download logic as T-035-12 to `DownloadPhoto.vue` for photo-level bulk downloads. Re-use `getChunkCount()` and `downloadChunk()` from `album-service.ts` (or `photo-service.ts` if photos need a different endpoint path).
  _Verification commands:_
  - `npm run test`
  - `npm run type-check`
  _Notes:_ Photo downloads use `photo_ids` query param instead of `album_ids`; verify `GET /Zip/chunks` handles this correctly.

### I5 – Documentation & Knowledge Map

- [ ] T-035-14 – Update knowledge map and confirm Q-035-01 resolved (FR-035-01 through FR-035-07).
  _Intent:_ Add feature 035 entry to `docs/specs/4-architecture/knowledge-map.md`. Confirm Q-035-01 is marked resolved in `open-questions.md` with a link to FR-035-05.
  _Verification commands:_
  - None (documentation only).
  _Notes:_ Also update `docs/specs/architecture-graph.json` if the chunk endpoints add new module dependencies.

## Notes / TODOs

- Q-035-01 resolved as Option A (2026-04-12): absent `chunk` param = single-archive download, regardless of chunked-mode setting.
- T-035-08 (out-of-range chunk validation) may require a lightweight COUNT query in `ZipRequest`; evaluate whether this duplicates the query in `ZipChunksController` and factor accordingly.
- T-035-07: the pre-generation pass traverses the full photo list to assign filenames, then a second pass streams only the slice. The first pass must use the same stable sort order as the second. Verify that `Album::get_photos()` uses a stable sort; add `->orderBy('id')` if not already present.
- `photo-service.ts` may need its own `downloadChunk()` / `getChunkCount()` counterpart if photo-level downloads hit a different URL structure (T-035-13).
- `download_archive_chunk_size` is never sent to the frontend; the client always gets `total_chunks` from the API and iterates from 1 to that value.
