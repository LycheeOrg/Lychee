# Feature 071 Tasks – Album Date Scrubber

_Status: Implemented — T-071-19 (manual browser check) pending_
_Last updated: 2026-09-24_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
> Test commands are always filtered (`--filter=<Class>`), never the whole suite, and never two test commands at once (shared SQLite).

## Checklist

- [x] T-071-00 – Analysis gate on spec/plan/tasks.
  _Verification:_ checklist in `docs/specs/5-operations/analysis-gate-checklist.md` walked; findings fixed in the docs.

### I1 — Backend eligibility
- [x] T-071-01 – Failing tests: `tests/Feature_v2/Album/AlbumConfigDateScrubberTest.php` (F-071-03/04/05; S-071-01/02/06/07/10).
  _Intent:_ Assert through `GET /api/v2/Album::head` (or whichever endpoint returns `AlbumConfig`): the global setting on/off × override null/true/false → `is_date_scrubber_enabled`; `photo_date_scrubber_field`: sort `created_at`/`taken_at` → that name, `title` + `date_prefix` → `title`, `title` + `alphabetical` / `rating_avg` / `type` / `is_highlighted` → null; `album_date_scrubber_field`: sort `created_at`/`min_taken_at`/`max_taken_at` → that name, `title` + both modes; smart album → global only, album field null; tag album → album field null; `date_scrubber_label_format` equals the config value.
  _Verification:_ `php artisan test --filter=AlbumConfigDateScrubberTest` (red).
- [x] T-071-02 – Migrations (F-071-01/02): `BaseConfigMigration` adding `album_date_scrubber_enabled` (`Gallery`, `0|1`, value `1`, level 0, not expert) and a schema migration adding nullable boolean `base_albums.is_date_scrubber_enabled` (default null).
  _Verification:_ runs as part of the filtered tests.
- [x] T-071-03 – Model: `BaseAlbumImpl` cast `?bool`, default `null`, `@property` on `BaseAlbumImpl` and `BaseAlbum`.
- [x] T-071-04 – `AlbumConfig`: `is_date_scrubber_enabled`, `photo_date_scrubber_field`, `album_date_scrubber_field`, `date_scrubber_label_format`, computed through small private helpers (one decision each, no nested branching). Photo sort comes from `getEffectivePhotoSorting()` on `BaseAlbum`, else `PhotoSortingCriterion::createDefault()`; album sort from `Album::getEffectiveAlbumSorting()`.
  _Verification:_ `php artisan test --filter=AlbumConfigDateScrubberTest` (green); `make phpstan`.

### I2 — Per-album override write path
- [x] T-071-05 – Failing tests: `tests/Feature_v2/Album/UpdateAlbumDateScrubberTest.php` (F-071-02): album, tag album and person album round-trip null/true/false through the update endpoint and the edit resource; `"yes"` → 422; omitted field → override unchanged.
  _Verification:_ `php artisan test --filter=UpdateAlbumDateScrubberTest` (red).
- [x] T-071-06 – `RequestAttribute::ALBUM_DATE_SCRUBBER`, `HasDateScrubber` contract + trait, and rules (`sometimes`, `nullable`, `boolean`, plus a provided-flag like `published_at`) in the three update requests; `AlbumController` sets `$album->is_date_scrubber_enabled` at the three write sites only when provided. _Note:_ first implemented as `present`, which broke v7 (it shares the endpoints and does not send the field); switched to the Feature 068 optional-field pattern. Existing test payloads therefore stay untouched.
- [x] T-071-07 – `EditableBaseAlbumResource` exposes `is_date_scrubber_enabled: ?bool`. Existing album-update tests still pass unchanged.
  _Verification:_ `php artisan test --filter=UpdateAlbumDateScrubberTest`; `php artisan test --filter=AlbumUpdate` (existing, one at a time); `make phpstan`.

### I3 — Lang, types, backend gate
- [x] T-071-08 – Lang keys in `lang/<locale>/*.php` (English text in every locale, following the repo convention) for: the setting's description and details, the properties label and options, the toggle tooltip, and `gallery.album.date_scrubber.albums_count`. Then `php artisan lang:json`.
- [x] T-071-09 – `make gen_typescript_types`; `vendor/bin/php-cs-fixer fix`; `make phpstan`; rerun T-071-01/05 filters.

### I4 — Frontend helpers
- [x] T-071-10 – `resources/js/v8/utils/dateScrubber.ts` (F-071-06/08), all pure and typed:
  - `parseTitleDatePrefix(title): string | null` — mirrors `^(\d{4})(?:-(\d{2}))?(?:-(\d{2}))?`, pads the missing month/day with `01`.
  - `dayKeyOf(raw: string | null): string | null` — `raw.slice(0, 10)` when `raw` matches `^\d{4}-\d{2}-\d{2}`, else null.
  - `deriveDayScrubEntries(items: {day: string | null; top: number}[], totalHeight, formatLabel)` → `{entries, totalHeight}`: consecutive runs; null days skipped; `#n` suffix on a recurring key; monotonic tops (R2).
  - `toRailBuckets(entries)` → `{bucket_ids, labels, counts, bucketable: true}` shaped as `PhotoBucketResource`.
  - `resolveDateScrubberSource({soaActive, enabled, albumCounts, photoCounts, photoField, albumField})` → `"photos" | "albums" | null`.
  _Branch table to verify manually:_ each null-return condition in FR-071-06; an empty title / `"20"` / `"2024 x"` / `"2024-03 x"` / `"2024-03-12 x"`; a null `taken_at`; A,A,B,A runs → ids `A`, `B`, `A#1`.
  _Verification:_ `npm run check`. Branch table also executed: a throwaway script bundled with the already-installed `esbuild` (stubbed `phpDateFormat`) and run under node, kept in the session scratchpad, not the repo — all cases passed 2026-09-24.
- [x] T-071-11 – `TimelineDatesV3.vue`: optional prop `countLabelKey` defaulting to `"gallery.timeline.photos_count"` (F-071-09, N-071-02).
  _Verification:_ `npm run check`; `/timeline` pill unchanged (manual, T-071-19).

### I5 — Grids
- [x] T-071-12 – `PhotoGridVirtual.vue` album mode (F-071-08/10): when `albumStore.config.photo_date_scrubber_field` is non-null and the source is `album` (read from the store like the grid's existing `is_photo_timeline_enabled`, no new prop), emit `scrubberLayoutChanged({entries, totalHeight})` from `layoutResult.positioned` (recomputed only when the layout changes, NFR-071-03) and `scrollOffsetChanged`. `scrollToPixelOffset` is already exposed. Timeline-mode emissions are unchanged. Forward the events and ref through `PhotoThumbPanelVirtual.vue`.
- [x] T-071-13 – `AlbumThumbGridVirtual.vue` **and** `AlbumListViewVirtual.vue` (same `buildVirtualAlbumRows()` model, shared via `composables/album/albumDateScrubber.ts`): the same for sub-album tiles (`created_at` / `min_taken_at` / `max_taken_at` / `title`), with tile tops from `rowsResult` cumulative heights. Expose `scrollToPixelOffset()` and forward through `AlbumThumbPanelVirtual.vue`.
  _Verification:_ `npm run check` — only the two pre-existing `app.ts`/`app-v8.ts` i18n plugin typing errors remain (untouched files). `AdaptedAlbumTile` gained raw `min_taken_at`/`max_taken_at` (constructors `adaptAlbumChildTile.ts`, `adaptCategoryTile.ts`); the `AlbumConfig` fallback literals in v8 and v7 `Search.vue` gained the new fields (a compile-only touch, no v7 behaviour change).

### I6 — Mount + viewer toggle
- [x] T-071-14 – `AlbumPanel.vue` (F-071-06/10/11): compute the source via `resolveDateScrubberSource`; mount `TimelineDatesV3` as a right-hand flex sibling when source ≠ null and not hidden by the viewer and no photo is open. `@load` → `scrollToPixelOffset(entry.top)` (no route push). `@scrub` → `scrollToPixelOffset`. Pass the albums count key when source = `"albums"`. Pass the date field from `AlbumConfig.photo_date_scrubber_field` / `album_date_scrubber_field`.
- [x] T-071-15 – Viewer toggle (F-071-07): a small composable `useDateScrubberVisibility()` (try/catch `localStorage`, key `lychee.album_date_scrubber_hidden`) plus an `AlbumHeader.vue` button (`lucide:calendar-range`), shown only when source ≠ null.
  _Verification:_ `npm run check`.

### I7 — Properties + gate
- [x] T-071-16 – `AlbumProperties.vue`: Default / Enabled / Disabled select, loaded from `editable.is_date_scrubber_enabled` and sent in all three update payloads (L478/L509/L536 blocks); album service types updated. Also surface `album_date_scrubber_enabled` in the curated `settings/General.vue` gallery section (it already appears in the generic config list).
- [x] T-071-17 – `npm run format`; `npm run check`.

### I8 — Docs + verification
- [x] T-071-18 – Knowledge map (`TimelineDatesV3.vue` has two consumers; `dateScrubber.ts`), roadmap status, `_current-session.md`, reflection in plan.
- [ ] T-071-19 – Manual browser verification of S-071-01…12 (LTR/RTL, light/dark, large album). **Pending** — not run in the authoring session.

## Notes / TODOs
- A JS unit-test runner would let T-071-10's branch table be automated, but adding one needs dependency approval (NG8).
