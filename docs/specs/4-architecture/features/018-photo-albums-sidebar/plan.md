# Feature Plan 018 – Photo Albums Sidebar

_Linked specification:_ `docs/specs/4-architecture/features/018-photo-albums-sidebar/spec.md`
_Status:_ Draft
_Last updated:_ 2026-02-26

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User value:** Users can see all albums a photo belongs to directly from the photo detail sidebar, giving them spatial orientation within their library and surfacing cross-album membership that was previously hidden.

**Success signals:**
- `GET /api/v2/Photo/{photo_id}/albums` returns the correct, filtered album list within 200ms for typical usage
- Sidebar displays album titles lazily on open, with no duplicate requests
- All tests pass (feature tests for authorization, filtering, edge cases)
- No N+1 queries
- PHPStan level 6, php-cs-fixer, and frontend lint all pass

## Scope Alignment

- **In scope:**
  - Backend: Request class with authorization, controller method, route, Spatie Data response resource
  - Backend: Feature tests covering S-018-01 through S-018-07
  - Frontend: `PhotoService.albums()` method
  - Frontend: Albums section in `PhotoDetails.vue` with loading/empty/error states
  - Translations: English labels, placeholders for other languages

- **Out of scope:**
  - Smart album inclusion
  - Album add/remove from sidebar
  - Photo edit drawer changes

## Dependencies & Interfaces

| Dependency | Description |
|------------|-------------|
| `Photo::albums()` | Existing `BelongsToMany` relationship via `photo_album` pivot |
| `PhotoPolicy::CAN_SEE` | Existing policy gate for photo visibility |
| `AlbumPolicy::canAccess()` | Existing policy method for album access check |
| `PhotoDetails.vue` | Existing sidebar component to be extended |
| `PhotoService` | Existing frontend service to receive new method |

## Assumptions & Risks

- **Assumptions:**
  - The `photo_album` pivot table has proper indexes on `photo_id` and `album_id` (confirmed by existing usage patterns).
  - Most photos belong to 1–5 albums; extreme cases (50+ albums) are rare.
  - The `AlbumPolicy::canAccess()` check per album is acceptable at small scale; no batch optimization needed.

- **Risks / Mitigations:**
  - _Risk:_ N+1 queries when checking album access for many albums. _Mitigation:_ Eager-load album data in single query, then filter in PHP with policy checks.
  - _Risk:_ Sidebar flicker on slow networks. _Mitigation:_ Show loading indicator; cache response per photo ID.

## Implementation Drift Gate

After each increment, verify:
1. Feature tests pass: `php artisan test --filter=GetPhotoAlbumsTest`
2. Static analysis: `make phpstan`
3. Formatting: `vendor/bin/php-cs-fixer fix --dry-run`
4. Frontend lint: `npm run check` (when frontend changes are made)

Record any drift in this section.

## Increment Map

### I1 – Backend: Request, Controller, Route, Resource (~60 min)

- _Goal:_ Create the API endpoint `GET /api/v2/Photo/{photo_id}/albums` with full authorization and album access filtering.
- _Preconditions:_ Feature spec finalized.
- _Steps:_
  1. Create `PhotoAlbumResource` (Spatie Data) with `id` and `title` fields
  2. Create `GetPhotoAlbumsRequest` extending `BaseApiRequest` with route parameter `photo_id` and `PhotoPolicy::CAN_SEE` authorization
  3. Add `albums()` method to `PhotoController` accepting `GetPhotoAlbumsRequest`, returning `Collection<PhotoAlbumResource>`
  4. Register `GET /api/v2/Photo/{photo_id}/albums` route in `routes/api_v2.php`
  5. Implement album access filtering using `AlbumPolicy::canAccess()` per album
- _Commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
- _Exit:_ Endpoint returns correct filtered album list; request rejects unauthorized access.

### I2 – Backend: Feature Tests (~45 min)

- _Goal:_ Comprehensive test coverage for all scenarios S-018-01 through S-018-07.
- _Preconditions:_ I1 complete.
- _Steps:_
  1. Create `GetPhotoAlbumsTest` extending `BaseApiWithDataTest`
  2. Write test for owner seeing all albums (S-018-01)
  3. Write test for shared user seeing accessible albums only (S-018-02, S-018-03)
  4. Write test for guest seeing public albums (S-018-04)
  5. Write test for guest denied on private photo (S-018-05)
  6. Write test for non-existent photo returning 404 (S-018-06)
  7. Write test for photo with no albums (S-018-07)
- _Commands:_
  - `php artisan test --filter=GetPhotoAlbumsTest`
  - `make phpstan`
- _Exit:_ All 7 scenario tests green.

### I3 – Frontend: Service Method & Sidebar Section (~60 min)

- _Goal:_ Add `albums()` method to `PhotoService` and display album list in `PhotoDetails.vue`.
- _Preconditions:_ I1 complete (endpoint available).
- _Steps:_
  1. Add `albums(photo_id): Promise<AxiosResponse<PhotoAlbumEntry[]>>` to `PhotoService`
  2. Add `PhotoAlbumEntry` type (if not auto-generated by TypeScript transformer)
  3. Add Albums section to `PhotoDetails.vue` between Tags and EXIF sections
  4. Implement lazy-loading logic: watch `areDetailsOpen` + `photoStore.photo.id`, fetch on change
  5. Handle loading, empty, and error states
  6. Cache album list per photo ID to prevent duplicate requests
  7. Render album titles as clickable links using `router.push({ name: "album", params: { albumId } })`
  8. On click: close sidebar (`are_details_open = false`), reset photo store, then navigate
- _Commands:_
  - `npm run check`
  - `npm run format`
- _Exit:_ Sidebar shows album list; loading/empty/error states work correctly.

### I4 – Translations & Quality Gate (~30 min)

- _Goal:_ Add translation keys and run full quality gate.
- _Preconditions:_ I1–I3 complete.
- _Steps:_
  1. Add English translations for album section labels (`ALBUMS`, `NO_ALBUMS`, `ALBUMS_LOADING_ERROR`)
  2. Add placeholder entries to all other language files (22 languages)
  3. Run full quality gate: php-cs-fixer, npm format, npm check, php artisan test, make phpstan
- _Commands:_
  - `vendor/bin/php-cs-fixer fix`
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`
- _Exit:_ All checks green; feature ready for manual testing.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-018-01 | I2 / T-018-05 | Owner sees all albums |
| S-018-02 | I2 / T-018-06 | Shared user sees accessible albums |
| S-018-03 | I2 / T-018-06 | Inaccessible albums filtered |
| S-018-04 | I2 / T-018-07 | Guest sees public albums |
| S-018-05 | I2 / T-018-08 | Guest denied on private photo |
| S-018-06 | I2 / T-018-09 | Non-existent photo → 404 |
| S-018-07 | I2 / T-018-10 | Photo with no albums → empty |
| S-018-08 | I3 / T-018-12 | Sidebar shows albums |
| S-018-09 | I3 / T-018-13 | No duplicate requests |
| S-018-10 | I3 / T-018-13 | Different photo triggers new fetch |
| S-018-11 | I3 / T-018-13 | Album click navigates to album |

## Analysis Gate

_Not yet completed. Run after I1–I2 implementation begins._

## Exit Criteria

- [ ] All feature tests pass (`GetPhotoAlbumsTest`)
- [ ] PHPStan level 6 passes
- [ ] php-cs-fixer clean
- [ ] npm run check / npm run format clean
- [ ] Sidebar displays albums section correctly (manual verification)
- [ ] Clicking album title navigates to album view (manual verification)
- [ ] No N+1 queries on album fetch
- [ ] Translations added for all 22 languages
- [ ] Knowledge map updated
- [ ] Roadmap status updated to Complete

## Follow-ups / Backlog

- **Smart album display:** Consider showing smart albums (Highlighted, Recent) that include the photo.
- **Album thumbnails:** Show small album cover thumbnails alongside titles.
- **Batch pre-fetch:** Optionally prefetch album lists for visible photos in gallery view for perceived faster UX.
