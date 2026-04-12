# Feature Plan 035 – Chunked Archive Download

_Linked specification:_ `docs/specs/4-architecture/features/035-chunked-download/spec.md`
_Status:_ Draft
_Last updated:_ 2026-04-12

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Users with large albums (>300 photos) can download them reliably without hitting server timeout, memory, or browser limits. The experience degrades gracefully: when chunked mode is disabled the behaviour is byte-for-byte identical to the current implementation. When enabled, the frontend downloads N sequentially numbered ZIPs that a standard archive tool can extract individually.

Success signals:
- All existing `GET /Zip` feature tests continue to pass unmodified.
- New feature tests cover all scenarios in S-035-01 through S-035-12.
- `GET /Zip/chunks` responds in < 200 ms on albums with 10 000 photos (COUNT query only).
- Frontend TypeScript compiles without errors after `InitConfig` type update (only `is_download_archive_chunked` added).

## Scope Alignment

**In scope:**
- Two new `configs` entries: `download_archive_chunked` (bool) and `download_archive_chunk_size` (int, default 300).
- New `GET /Zip/chunks` endpoint (API-035-01) returning `{ total_chunks, total_photos }`.
- Extend `GET /Zip` to accept optional `chunk` query param (API-035-02).
- `BaseArchive::do()` refactored to: (1) pre-generate all filenames for the complete photo set, then (2) apply the `ChunkSlice` (offset + limit) to avoid cross-chunk name collisions.
- `InitConfig` extended with `is_download_archive_chunked` **only** — `download_archive_chunk_size` stays server-side.
- `DownloadAlbum.vue` and `DownloadPhoto.vue` updated for sequential chunk downloads.
- Admin settings UI auto-renders both new config keys (no extra frontend wiring needed — I5 is skipped).
- PHPUnit and Vitest tests covering all new scenarios.

**Out of scope:**
- True multi-volume ZIP streams.
- Background/queued chunk generation.
- Resumable downloads.
- Changing photo-level single-file download behaviour.

## Dependencies & Interfaces

| Dependency | Notes |
|------------|-------|
| `maennchen/zipstream-php` (^2.1 or ^3.1) | Already installed; no version change needed. |
| `BaseArchive` (Album + Photo variants) | Refactored to accept `ChunkSlice`; backward-compatible default `null` slice. |
| `ZipRequest` | Extended with optional `chunk` param and validation. |
| `ConfigManager` / `BaseConfigMigration` | New migration for two config keys. |
| `InitConfig` (Spatie Data) | One new public property: `is_download_archive_chunked` only. `download_archive_chunk_size` is server-side. |
| `AlbumController::getArchive` | Passes slice to archive action. |
| Vue 3 + PrimeVue | `DownloadAlbum.vue`, `DownloadPhoto.vue` updates. |
| `album-service.ts` | New `downloadChunk(albumIds, variant, chunk)` method. |

## Assumptions & Risks

**Assumptions:**
- The photo ordering for chunking is stable and deterministic (consistent with the existing order used inside `compressAlbum`).
- The admin UI settings panel already renders bool and int config types; no new UI component is needed (I5 skipped as confirmed by owner).
- Q-035-01 resolved as **Option A** (missing `chunk` ≡ single-archive download, regardless of chunked mode).

**Risks / Mitigations:**
- **Risk:** Sub-album structures complicate flat-photo-count queries. **Mitigation:** Chunk slicing is applied only to the top-level collected photo list (same collection the archive already iterates), not re-queried independently.
- **Risk:** Album photo ordering must match between the `/Zip/chunks` count request and each `/Zip?chunk=n` download; otherwise photos may be duplicated or omitted across chunks. **Mitigation:** Both code paths use the same `album->get_photos()` pipeline; document and test that ordering is stable.
- **Risk:** Frontend sequential download using `<a>` tag tricks may be blocked by browsers. **Mitigation:** Use `fetch()` + `URL.createObjectURL()` + programmatic `<a>.click()` pattern (same as upload-chunk pattern elsewhere in the codebase), with `await` between chunks to ensure sequential completion.

## Implementation Drift Gate

After each increment: run `php artisan test --filter=Zip` and `npm run type-check`. At feature completion: run full `php artisan test` + `npm run build`. Record results in task checklist.

## Increment Map

1. **I1 – DB Configuration (≤ 30 min)**
   - _Goal:_ Introduce the two new `configs` entries and expose `is_download_archive_chunked` in `InitConfig`.
   - _Preconditions:_ None.
   - _Steps:_
     - Create migration `YYYY_MM_DD_000001_add_chunked_download_configs.php` extending `BaseConfigMigration`, adding `download_archive_chunked` (BOOL, Image Processing cat) and `download_archive_chunk_size` (INT, Image Processing cat, default 300).
     - Add `is_download_archive_chunked: bool` to `InitConfig` (only — `download_archive_chunk_size` is server-side, not serialised to the client).
     - Update `lychee.d.ts` TypeScript interface for `InitConfig` with `is_download_archive_chunked` only.
   - _Commands:_ `php artisan migrate`, `php artisan test --filter=InitConfig`.
   - _Exit:_ Migration runs cleanly; `InitConfig` serialises `is_download_archive_chunked`; `download_archive_chunk_size` is absent from JSON.

2. **I2 – Backend: Chunk Metadata Endpoint (≤ 60 min)**
   - _Goal:_ Implement `GET /Zip/chunks` returning `{ total_chunks, total_photos }` (API-035-01).
   - _Preconditions:_ I1 complete.
   - _Steps:_
     - Write `ZipChunksRequest` (extends `BaseApiRequest`): reuses album/photo resolution from `ZipRequest`; validates same params.
     - Write `ZipChunksController::index()` (or add method to `AlbumController`): counts photos from the resolved albums/photos, computes `total_chunks = ceil(total / chunk_size)`, returns `ZipChunksData` resource.
     - Register route `GET /Zip/chunks` in `api_v2.php` (before the existing `/Zip` route).
     - Write PHPUnit feature tests (`ZipChunksTest.php`): S-035-02, S-035-10.
   - _Commands:_ `php artisan test --filter=ZipChunks`.
   - _Exit:_ Tests green; endpoint returns correct counts.

3. **I3 – Backend: Chunked Archive Slicing (≤ 90 min)**
   - _Goal:_ Extend `GET /Zip` to accept `chunk` param and slice the photo set (API-035-02, FR-035-04, FR-035-05).
   - _Preconditions:_ I1 complete.
   - _Steps:_
     - Add optional `chunk: ?int` to `ZipRequest::rules()` and `processValidatedValues()`; validate ≥ 1 when present.
     - Introduce `ChunkSlice` value object (`offset: int`, `limit: int`); add `ZipRequest::chunkSlice(): ?ChunkSlice`.
     - Refactor `BaseArchive::do()` (both Album and Photo variants): first traverse the **full** photo list to pre-assign all unique filenames (de-duplication pass), then apply `ChunkSlice` offset/limit to select which photos to stream into the ZIP. This ensures filenames in chunk N are globally unique relative to all other chunks (NFR-035-04).
     - Adjust `Content-Disposition` filename: when `chunk` is present, produce `<title>.part<n>.zip`.
     - Out-of-range `chunk` (> total_chunks): validate in `ZipRequest` or controller; return 422.
     - Write PHPUnit feature tests: S-035-01, S-035-03, S-035-04, S-035-05, S-035-06, S-035-07, S-035-11, S-035-12.
   - _Commands:_ `php artisan test --filter=ZipChunked`.
   - _Exit:_ All scenario tests green; existing non-chunked tests still pass.

4. **I4 – Frontend: Sequential Chunk Download (≤ 60 min)**
   - _Goal:_ Update `DownloadAlbum.vue` (and `DownloadPhoto.vue`) to download all parts sequentially when chunked mode is on (FR-035-06, UI-035-01 through UI-035-04).
   - _Preconditions:_ I1 complete (for `InitConfig` fields); I2 + I3 complete (for API).
   - _Steps:_
     - Add `downloadChunk(albumIds, variant, chunk): Promise<void>` to `album-service.ts` using `fetch()` + `URL.createObjectURL()` + programmatic link click.
     - Add `getChunkCount(albumIds, variant): Promise<ZipChunksData>` to `album-service.ts`.
     - Refactor `DownloadAlbum.vue`: when `is_download_archive_chunked`, call `getChunkCount()` then loop `downloadChunk()` for each part; show `"Downloading part k / n"` progress label; handle errors with toast.
     - Update `DownloadPhoto.vue` similarly for photo-level chunked downloads.
     - Add `is_download_archive_chunked` ref to `useLycheeStateStore` (no `download_archive_chunk_size` — that's server-side).
     - Write Vitest unit tests for sequential download logic.
   - _Commands:_ `npm run test`, `npm run type-check`.
   - _Exit:_ Vitest tests pass; TypeScript compiles; dialog shows progress label during download.

5. **I5 – Documentation & Knowledge Map (≤ 20 min)**
   - _Goal:_ Keep living docs up to date.
   - _Preconditions:_ I1–I4 complete.
   - _Steps:_
     - Update `docs/specs/4-architecture/knowledge-map.md` to reference feature 035.
     - Mark Q-035-01 as resolved in `open-questions.md`.
   - _Commands:_ None.
   - _Exit:_ Knowledge map updated; open question resolved.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-035-01 | I3 / T-035-06 | Existing behaviour preserved. |
| S-035-02 | I2 / T-035-03 | Chunk count endpoint. |
| S-035-03 | I3 / T-035-07 | Chunk 1 of 2. |
| S-035-04 | I3 / T-035-07 | Chunk 2 of 2 (remainder). |
| S-035-05 | I3 / T-035-08 | chunk=0 → 422. |
| S-035-06 | I3 / T-035-08 | chunk > total → 422. |
| S-035-07 | I3 / T-035-09 | No chunk param + chunked mode ON → single archive (Q-035-01 → Option A). |
| S-035-08 | I4 / T-035-11 | Frontend sequential download. |
| S-035-09 | I4 / T-035-12 | Frontend error handling. |
| S-035-10 | I2 / T-035-04 | Chunk count endpoint auth/validation. |
| S-035-11 | I1 / T-035-02 | chunk_size validation. |
| S-035-12 | I3 / T-035-07 | Filename uniqueness across chunks. |

## Analysis Gate

Q-035-01 resolved as Option A (2026-04-12). I3 implementation may proceed.

## Exit Criteria

- [ ] Migration runs on a clean install; `php artisan migrate:fresh --seed` passes.
- [ ] All existing `GET /Zip` feature tests pass without modification.
- [ ] PHPUnit tests cover S-035-01 through S-035-12.
- [ ] Vitest tests cover UI-035-01 through UI-035-04.
- [ ] `npm run type-check` passes.
- [ ] `npm run build` produces no errors.
- [ ] Knowledge map updated.

## Follow-ups / Backlog

- True multi-volume ZIP stream (deferred — server-side complexity).
- Progress reporting via Server-Sent Events.
- Resumable / ranged chunk downloads.
- Monitoring queue for large-download metrics.
