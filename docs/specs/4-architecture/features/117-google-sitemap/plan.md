# Feature Plan 117 – Google Sitemap

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Complete  
_Last updated:_ 2026-11-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Owners who want their public albums found by search engines switch on one setting and submit `<APP_URL>/sitemap.xml`. The document lists exactly what a guest can browse among regular albums, nothing more (FR-117-03 … FR-117-05, FR-117-11). Success: every scenario S-117-01 … S-117-20 is covered by a green test, phpstan and php-cs-fixer are clean, and no Eloquent model is hydrated to build the list (NFR-117-01).

## Scope Alignment
- **In scope:** three settings (CFG-117-01 … 03) with translations; `GalleryLockState::isSitemapEnabled()`; the album query; `<lastmod>` and XML rendering helpers; the managed-cache entry and its invalidation; the session-less route, group and controller.
- **Out of scope:** N-117-01 … N-117-07 (photos, other album kinds and pages, sitemap index, `changefreq`/`priority`, `robots.txt`, per-user sitemaps, a new settings category). No frontend code.

## Dependencies & Interfaces
- `AlbumQueryPolicy::applyVisibilityFilter()`, `applyBrowsabilityFilter()` (guest view, origin `null`).
- `albums.is_recursive_nsfw` (ADR-103-01) for the sensitivity filter.
- `ManagedCacheService::remember()`, `CacheKeyProvider`, `ManagedCacheAlbumListingInvalidator` (ADR-117-01).
- `GalleryLockState` (`gallery_password`), `ConfigManager` (`login_required`).
- `BaseConfigMigration` (supports `required_keys`), `lang/*/all_settings.php` (23 languages).
- `RouteServiceProvider`, `App\Http\Kernel` middleware groups (ADR-102-02: `ResolveConfigs` first).

## Assumptions & Risks
- **Assumptions:** album saves (including slug and `is_nsfw` changes) dispatch `AlbumSaved`. Photo additions and removals end in `AlbumComputedDataUpdated` through `RecomputeAlbumStatsJob`. `applyVisibilityFilter()` keeps an explicit `select()` set before it (it only adds `albums.*` when no columns are set) and joins `base_albums` unprefixed.
- **Risk:** existing `ManagedCacheAlbumListingInvalidatorTest` cases assert that a handler evicts *only* certain tags (`testAlbumChildrenChangedEvictsOnlyItsOwnParentTag`, `testAlbumComputedDataUpdatedEvictsOnlyOwnTag`, `testTagAlbumSavedEvictsOwnTagAndTagAlbumsListingOnly`, …). **Mitigation:** amend their expected tag lists to include `sitemapTag()` where the spec adds it (a behaviour change required by NFR-117-02), never delete or skip them. `testNonFlushEventsNeverTouchTheGlobalTag` must stay green: the new flushes target `sitemapTag()`, not the global tag.
- **Risk:** `RouteServiceProvider` order: `web_v2.php` has catch-all routes. **Mitigation:** register `routes/web_sitemap.php` before `web_v2.php` and before `web-install` (I4 test hits the route with a completed install).
- **Risk:** `route('gallery', …)` builds URLs from the request root. **Mitigation:** cache rows, not XML (ADR-117-01 point 3).
- **Risk:** the cap (50,000) cannot be reached in a test. **Mitigation:** the action takes the cap as a constructor parameter with the protocol default, so the test builds the action with a cap of 2.

## Implementation Drift Gate
After T-117-14, compare each FR/NFR/S row of the spec with the tests and classes that implement it, record the matrix and findings below, and rerun the commands listed in Exit Criteria.

Run 2026-11-22 by the agent, after T-117-13.

| Spec row | Implementation | Evidence |
|----------|----------------|----------|
| FR-117-01 | `2026_11_22_000001_add_sitemap_configs.php` (`config`, order 103), `SitemapController` 404 | `SitemapTest::testConfigRows`, `testDisabledAnswers404` |
| FR-117-02 | `sitemap_root_only` (order 104, requires `sitemap_enable`), `whereNull('albums.parent_id')` | `SitemapAlbumsTest::testRootOnly`, `SitemapTest::testRootOnly` |
| FR-117-03 | policy filters with `user = null`, no unlocked IDs | `SitemapTest::testAdminGetsTheGuestDocument` |
| FR-117-04 | `applyVisibilityFilter(…, null)`, `applyBrowsabilityFilter(…, null, [])` | `SitemapAlbumsTest` password, link, inside-private cases |
| FR-117-05 | rows carry album columns only | `SitemapTest::testEnabled` (no photo ID in the body) |
| FR-117-06 | `SitemapRenderer` (slug else ID, root first), `route('gallery', …)` in the controller | `SitemapRendererTest`, `SitemapTest::testSlugUrl` |
| FR-117-07 | `htmlspecialchars(ENT_XML1 \| ENT_QUOTES)`, `Content-Type` header, no `accept_content_type` | `SitemapRendererTest::testLocIsEscaped`, `SitemapTest::testEnabled`, `testAcceptsXmlRequests` |
| FR-117-08 | `ListSitemapAlbums::MAX_URLS`, 49,999 albums plus the root, `_lft` order, `Log::warning` | `SitemapAlbumsTest::testCap`, `testNoWarningBelowTheCap` |
| FR-117-09 | `GalleryLockState::isSitemapEnabled()` / `isSitemapAllowed()` | `GalleryLockStateTest`, `SitemapTest` login and password cases |
| FR-117-10 | `hide_nsfw_in_sitemap` (`Mod NSFW`, default `1`), `albums.is_recursive_nsfw = false` | `SitemapAlbumsTest::testSensitiveAlbums` |
| FR-117-11 | `Album::query()` only | `SitemapAlbumsTest::testPublicTagAlbumIsLeftOut` |
| FR-117-12 | `SitemapLastmod::compute()` | `SitemapLastmodTest` (8 cases) |
| NFR-117-01 | explicit `select()` of four columns, `toBase()`, plain arrays | code review, `SitemapAlbumsTest` |
| NFR-117-02 | `sitemapKey()`, `sitemapTag()`, six `forgetTag()` calls, `Cache-Control: public, max-age=3600` | `ManagedCacheAlbumListingInvalidatorTest` (3 sitemap cases), `SitemapAlbumsTest::testRowsAreCachedUntilAnAlbumChanges`, `SitemapTest::testEnabled` |
| NFR-117-03 | `public/robots.txt` untouched | `git status` |
| NFR-117-04 | `sitemap` group in `App\Http\Kernel`, registered before `web-install` and `web` | `SitemapTest::testEnabled` (no `Set-Cookie`), `testAcceptsXmlRequests` |

S-117-01 … S-117-20 are covered by the tests above (see Scenario Tracking).

Findings (all low impact, tidied locally):
1. FR-117-01 asked for the `details` text to give `<APP_URL>/sitemap.xml`. Settings translations are static, so the text tells the admin to submit "the address of this gallery followed by `/sitemap.xml`". FR-117-01 and the mock-up now say so.
2. T-117-07 planned to amend the existing "only" invalidator cases. The existing cases assert specific tags, not exhaustive lists, so they stayed green unchanged; the sitemap expectations went into three dedicated cases instead. `AccessPermissionChanged` flushes `sitemapTag()` for regular albums only (FR-117-11), not for tag and person albums as the T-117-07 wording listed; `testSitemapEntryIsNotEvictedByUnrelatedEvents` pins that.
3. `ListSitemapAlbums` joins `base_albums` a second time (`sitemap_base`) for `slug` and `updated_at`, because the columns of the policy's own join are fixed by the builder. Still four selected columns and no hydration (NFR-117-01).
4. The controller throws `NotFoundHttpException` instead of calling `abort(404)`: same response.

Commands rerun: `vendor/bin/php-cs-fixer fix` (0 files fixed), `make phpstan` (no errors), `GalleryLockStateTest` 10, `SitemapLastmodTest` 8, `SitemapRendererTest` 5, `SitemapAlbumsTest` 11, `ManagedCacheAlbumListingInvalidatorTest` 27, `SitemapTest` 10, `RssTest` 13, `GalleryPasswordWebTest` 3, all green.

Outcome: pass.

## Increment Map

1. **I1 – Settings and gating** (FR-117-01, FR-117-02, FR-117-09, FR-117-10 setting part)
   - _Steps:_ tests first: config rows, defaults, categories, `required_keys` (in `SitemapTest`, red); `GalleryLockStateTest` cases for `isSitemapEnabled()` (red). Then migration `2026_11_22_000001_add_sitemap_configs.php` (`BaseConfigMigration`): `sitemap_enable` (`config`, order 103), `sitemap_root_only` (`config`, order 104, requires `sitemap_enable`), `hide_nsfw_in_sitemap` (`Mod NSFW`, order 32767, default `1`, requires `sitemap_enable`). `GalleryLockState::isSitemapEnabled()`. Translations in all 23 `lang/*/all_settings.php` (description and details).
   - _Commands:_ `php artisan test --filter=GalleryLockStateTest`, `php artisan test --filter=SitemapTest`, `vendor/bin/php-cs-fixer fix`, `make phpstan`.
   - _Exit:_ settings visible in Basics and Sensitive, lock rule unit-tested.
2. **I2 – Pure helpers** (FR-117-06, FR-117-07, FR-117-12)
   - _Steps:_ `SitemapLastmodTest`, `SitemapRendererTest` (red). Then `App\Actions\Sitemap\SitemapLastmod` (later of two nullable UTC strings, future `max_taken_at` skipped, W3C output) and `App\Actions\Sitemap\SitemapRenderer` (root URL first, `<loc>` escaped with `htmlspecialchars(ENT_XML1 | ENT_QUOTES)`, slug else ID, `<lastmod>` only when set). The renderer takes the URL builder as a closure so the unit test needs no router.
   - _Commands:_ `php artisan test --filter=SitemapLastmodTest`, `php artisan test --filter=SitemapRendererTest`, `make phpstan`.
3. **I3a – Album query** (FR-117-02, FR-117-03, FR-117-04, FR-117-08, FR-117-10, FR-117-11, NFR-117-01)
   - _Steps:_ action-level tests in `SitemapAlbumsTest` (Feature_v2, no HTTP) for S-117-02 … S-117-06, S-117-09, S-117-14, S-117-15, S-117-20 (red). Then `App\Actions\Sitemap\ListSitemapAlbums::do(bool $root_only, bool $hide_nsfw): array`: `Album::query()->select([...])`, `applyVisibilityFilter(…, null)`, `applyBrowsabilityFilter(…, null, [])`, `when($root_only, whereNull('albums.parent_id'))`, `when($hide_nsfw, where('albums.is_recursive_nsfw', false))`, `orderBy('albums._lft')`, `limit(cap)`, `toBase()->get()`, mapped to plain arrays (DO-117-01). Warning when the cap is reached.
   - _Commands:_ `php artisan test --filter=SitemapAlbumsTest`, `make phpstan`.
4. **I3b – Cache entry and invalidation** (NFR-117-02, S-117-19)
   - _Steps:_ extend `ManagedCacheAlbumListingInvalidatorTest` with sitemap-tag expectations and amend the "only" cases (red). Then `CacheKeyProvider::sitemapTag()`, `sitemapKey(bool, bool)`; the six `forgetTag(sitemapTag())` calls in the invalidator; wrap `ListSitemapAlbums` in `ManagedCacheService::remember()` with `[sitemapTag(), albumListingGlobalTag()]`.
   - _Commands:_ `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest`, `php artisan test --filter=SitemapAlbumsTest`, `make phpstan`.
5. **I4 – Route, group, controller** (API-117-01, FR-117-01, FR-117-07, FR-117-09, NFR-117-04)
   - _Steps:_ `SitemapTest` HTTP cases for S-117-01, S-117-07, S-117-08, S-117-10 … S-117-13, S-117-17 (red). Then the `sitemap` middleware group (`ResolveConfigs`, `installation:complete`), `routes/web_sitemap.php` (`GET /sitemap.xml`, `migration:complete`), registration in `RouteServiceProvider` before `web-install` and `web`, invokable `App\Http\Controllers\SitemapController` (404 unless `isSitemapEnabled()`, rows from I3, XML from I2, headers).
   - _Commands:_ `php artisan test --filter=SitemapTest`, `php artisan test --filter=RssTest` (neighbouring web route), `php artisan test --filter=GalleryPasswordWebTest`, `make phpstan`.
6. **I5 – Docs and gates**
   - _Steps:_ knowledge map entries, roadmap status, drift gate report, full quality gate.
   - _Commands:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`, every test class above.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-117-01 | I4 / T-117-11 | `SitemapTest` |
| S-117-02 | I3a / T-117-06 | `SitemapAlbumsTest` (`album4`, `subAlbum4`), root-first order in `SitemapRendererTest` |
| S-117-03 | I3a / T-117-06 | `album1` |
| S-117-04 | I3a / T-117-06 | password permission on a public album and its public child |
| S-117-05 | I3a / T-117-06 | `is_link_required` public album and its public child |
| S-117-06 | I3a / T-117-06 | public child of `album1` |
| S-117-07 | I2 / T-117-04, I4 / T-117-11 | renderer unit test and one HTTP case |
| S-117-08 | I4 / T-117-11 | admin request equals guest request |
| S-117-09 | I3a / T-117-06 | `subAlbum4` left out |
| S-117-10 | I4 / T-117-11 | no `/gallery/{album}/{photo}` URL, no photo ID in the body |
| S-117-11 | I1 / T-117-01, I4 / T-117-11 | unit and HTTP |
| S-117-12 | I1 / T-117-01, I4 / T-117-11 | unit and HTTP |
| S-117-13 | I1 / T-117-01, I4 / T-117-11 | unit and HTTP |
| S-117-14 | I3a / T-117-06 | `is_nsfw` + `SyncRecursiveNsfw` |
| S-117-15 | I3a / T-117-06 | `tagAlbum1` made public |
| S-117-16 | I2 / T-117-04 | `SitemapLastmodTest` |
| S-117-17 | I4 / T-117-11 | headers, `Accept: application/xml` |
| S-117-18 | I2 / T-117-04 | `SitemapRendererTest` |
| S-117-19 | I3b / T-117-08 | `ManagedCacheAlbumListingInvalidatorTest` |
| S-117-20 | I3a / T-117-06 | cap of 2 |

## Analysis Gate

Run 2026-11-22 by the agent, before implementation.

1. **Specification completeness**
   - [x] Objectives, FR and NFR populated.
   - [x] Q-117-01 … 07 folded into FR-117-01, 02, 09, 10, 11, 12 and NFR-117-02, 03 (plus the owner's category directive in FR-117-01, FR-117-02, FR-117-10, N-117-07).
   - [x] ASCII mock-up present (settings only, no new UI).
2. **Open questions review**
   - [x] No open entries in `open-questions.md`.
   - [x] ADR-117-01 covers gating, caching and the session-less route. ADR-103-01 reviewed (stored `is_recursive_nsfw` for origin-less checks, now used by FR-117-10). ADR-102-02 reviewed (`ResolveConfigs` first in the new group).
3. **Plan alignment**
   - [x] Plan links spec and tasks. Dependencies and success criteria match the spec.
4. **Tasks coverage**
   - [x] Every FR maps to a task: FR-117-01 T-117-01/02/11, FR-117-02 T-117-01/06, FR-117-03 T-117-06/11, FR-117-04 T-117-06, FR-117-05 T-117-11, FR-117-06 T-117-04, FR-117-07 T-117-04/11, FR-117-08 T-117-06, FR-117-09 T-117-01/11, FR-117-10 T-117-01/06, FR-117-11 T-117-06, FR-117-12 T-117-04.
   - [x] Tests are queued before code in every increment. Each increment is planned under 90 minutes (I1 is the largest because of 23 translation files).
   - [x] Success (listed albums, 200), validation (settings `0|1`, `required_keys`) and failure (404 off or locked, cap) branches are enumerated.
5. **Working-agreement compliance**
   - [x] Spec-first, test-first, no new dependency.
   - [x] Branching is kept in small helpers: `isSitemapEnabled()` (boolean), `SitemapLastmod` (pure), `SitemapRenderer` (pure), query built with `when()`.
   - [x] Related ADRs reviewed (see 2).
6. **Tooling readiness**
   - [x] Commands listed per increment.
   - [x] Results recorded here.

Outcome: pass. No findings to address.

## Exit Criteria
- All tasks `[x]`, roadmap row updated.
- Green: `GalleryLockStateTest`, `SitemapLastmodTest`, `SitemapRendererTest`, `SitemapAlbumsTest`, `ManagedCacheAlbumListingInvalidatorTest`, `SitemapTest`, `RssTest`, `GalleryPasswordWebTest`.
- `vendor/bin/php-cs-fixer fix` clean, `make phpstan` clean.
- Knowledge map updated. Drift gate report below.

## Follow-ups / Backlog
- A sitemap index for galleries with more than 50,000 browsable albums, if anyone reaches it (N-117-03).
- Optionally add a `<link rel="sitemap">` hint or a dynamic `robots.txt` later if owners ask (N-117-05).

## Intent Log
- 2026-11-22: owner asked for a Google sitemap (off by default), a root-only option (off by default), admin settings, `toBase()` queries, no photos, guest-view browsable albums only. Agent surveyed `AlbumQueryPolicy`, `GalleryLockState`, the RSS action, `ManagedCacheService` and its invalidator, the `web` middleware group and config migrations, then logged Q-117-01 … 07. Owner answered A, A, B, B, B (amended: also `max_taken_at`), A, A, and ruled out a new settings category (`hide_nsfw_in_sitemap` in Sensitive). Planning found that `albumListingGlobalTag()` is not flushed by ordinary album changes (hence `sitemapTag()`) and that ADR-103-01's stored `is_recursive_nsfw` applies (origin-less check).
