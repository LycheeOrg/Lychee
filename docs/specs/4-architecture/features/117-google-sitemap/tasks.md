# Feature 117 Tasks – Google Sitemap

_Status: Complete_  
_Last updated: 2026-11-22_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Settings and gating
- [x] T-117-01 – Failing tests for the settings and the lock rule (FR-117-01, FR-117-02, FR-117-09, FR-117-10, S-117-11, S-117-12, S-117-13).  
  _Intent:_ `GalleryLockStateTest`: `isSitemapEnabled()` false when `sitemap_enable` is off, when `login_required` is on (with and without `login_required_root_only`), when a gallery password is set; true otherwise. `SitemapTest::testConfigRows`: the three keys exist with defaults `0`, `0`, `1`, categories `config`, `config`, `Mod NSFW`, and `required_keys = sitemap_enable` on the last two.  
  _Verification commands:_  
  - `php artisan test --filter=GalleryLockStateTest` (red)  
  - `php artisan test --filter=SitemapTest` (red)

- [x] T-117-02 – Migration, lock rule, translations (FR-117-01, FR-117-02, FR-117-09, FR-117-10, N-117-07).  
  _Intent:_ `database/migrations/2026_11_22_000001_add_sitemap_configs.php` on `BaseConfigMigration` (orders 103, 104 in `config`, 32767 in `Mod NSFW`; `sitemap_enable` details give `<APP_URL>/sitemap.xml` and say that `login_required` or a gallery password turns it off). `GalleryLockState::isSitemapEnabled()`. Description and details for the three keys in all 23 `lang/*/all_settings.php`.  
  _Verification commands:_  
  - `php artisan test --filter=GalleryLockStateTest`  
  - `php artisan test --filter=SitemapTest` (config test green; other cases not written yet)  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`

### I2 – Pure helpers
- [x] T-117-03 – Failing tests for `<lastmod>` and rendering (FR-117-06, FR-117-07, FR-117-12, S-117-02, S-117-07, S-117-16, S-117-18).  
  _Intent:_ `tests/Unit/Actions/Sitemap/SitemapLastmodTest.php`: both null, only `updated_at`, only `max_taken_at`, later wins either way, future `max_taken_at` skipped, W3C UTC format. `tests/Unit/Actions/Sitemap/SitemapRendererTest.php`: namespace and declaration, root URL first without `<lastmod>`, slug preferred over ID, `<lastmod>` omitted when null, escaping of `&`, `<`, `"`, `'`.  
  _Verification commands:_  
  - `php artisan test --filter=SitemapLastmodTest` (red)  
  - `php artisan test --filter=SitemapRendererTest` (red)

- [x] T-117-04 – `SitemapLastmod` and `SitemapRenderer` (FR-117-06, FR-117-07, FR-117-12).  
  _Intent:_ `app/Actions/Sitemap/SitemapLastmod.php`, `app/Actions/Sitemap/SitemapRenderer.php` (URL builder injected as a closure).  
  _Verification commands:_  
  - `php artisan test --filter=SitemapLastmodTest`  
  - `php artisan test --filter=SitemapRendererTest`  
  - `make phpstan`

### I3a – Album query
- [x] T-117-05 – Failing action tests (FR-117-02, FR-117-03, FR-117-04, FR-117-08, FR-117-10, FR-117-11, S-117-02, S-117-03, S-117-04, S-117-05, S-117-06, S-117-09, S-117-14, S-117-15, S-117-20).  
  _Intent:_ `tests/Feature_v2/Sitemap/SitemapAlbumsTest.php` on `BaseApiWithDataTest`: `album4` and `subAlbum4` listed, `album1` not; password and link-required public albums and their public children left out; public child of `album1` left out; root-only drops `subAlbum4`; sensitive public album (`is_nsfw` + `SyncRecursiveNsfw`) and its child left out with `hide_nsfw` on, listed with it off; public `tagAlbum1` never listed; cap of 2 stops the list and logs a warning.  
  _Verification commands:_  
  - `php artisan test --filter=SitemapAlbumsTest` (red)

- [x] T-117-06 – `ListSitemapAlbums` (FR-117-02, FR-117-03, FR-117-04, FR-117-08, FR-117-10, FR-117-11, NFR-117-01).  
  _Intent:_ `app/Actions/Sitemap/ListSitemapAlbums.php`: explicit `select()`, guest visibility and browsability filters, `when()` for root-only and `is_recursive_nsfw`, `_lft` order, cap, `toBase()`, plain arrays (DO-117-01).  
  _Verification commands:_  
  - `php artisan test --filter=SitemapAlbumsTest`  
  - `make phpstan`

### I3b – Cache entry and invalidation
- [x] T-117-07 – Failing invalidator expectations (NFR-117-02, S-117-19).  
  _Intent:_ in `tests/Unit/Listeners/ManagedCacheAlbumListingInvalidatorTest.php`, add `sitemapTag()` to the expected evictions of album saved, deleted, children changed, access permission changed (regular, tag and person album), computed data updated, base album removed. Amend the existing "only" cases rather than removing them. `testNonFlushEventsNeverTouchTheGlobalTag` stays as is.  
  _Verification commands:_  
  - `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest` (red)

- [x] T-117-08 – `sitemapTag()`, `sitemapKey()`, invalidator flushes, cached action (NFR-117-02, S-117-19).  
  _Intent:_ `CacheKeyProvider::sitemapTag()` and `sitemapKey(bool $root_only, bool $hide_nsfw)`; six `forgetTag()` calls in `ManagedCacheAlbumListingInvalidator`; `ListSitemapAlbums` wrapped in `ManagedCacheService::remember()` with `[sitemapTag(), albumListingGlobalTag()]`.  
  _Verification commands:_  
  - `php artisan test --filter=ManagedCacheAlbumListingInvalidatorTest`  
  - `php artisan test --filter=SitemapAlbumsTest`  
  - `make phpstan`

### I4 – Route, group, controller
- [x] T-117-09 – Failing HTTP tests (API-117-01, FR-117-01, FR-117-03, FR-117-05, FR-117-07, FR-117-09, NFR-117-04, S-117-01, S-117-07, S-117-08, S-117-10, S-117-11, S-117-12, S-117-13, S-117-17).  
  _Intent:_ `tests/Feature_v2/Sitemap/SitemapTest.php`: 404 when off; 200 with `Content-Type: application/xml; charset=UTF-8`, `Cache-Control` containing `public` and `max-age=3600`, no `Set-Cookie`; parses with `SimpleXMLElement`; first `<loc>` is `route('gallery')`; slug URL; admin body equals guest body; no photo ID in the body; 404 with `login_required`, with `login_required_root_only`, with a gallery password; 200 with `Accept: application/xml`.  
  _Verification commands:_  
  - `php artisan test --filter=SitemapTest` (red)

- [x] T-117-10 – `sitemap` middleware group, `routes/web_sitemap.php`, registration (API-117-01, NFR-117-04).  
  _Intent:_ group in `App\Http\Kernel` (`ResolveConfigs`, `installation:complete`), route file with `migration:complete`, `RouteServiceProvider` registration before `web-install` and `web`.  
  _Verification commands:_  
  - `php artisan route:list --path=sitemap`  
  - `make phpstan`

- [x] T-117-11 – `SitemapController` (API-117-01, FR-117-01, FR-117-05, FR-117-07, FR-117-09).  
  _Intent:_ invokable `app/Http/Controllers/SitemapController.php`: `abort(404)` unless `isSitemapEnabled()`; rows from `ListSitemapAlbums` with the current settings; `SitemapRenderer` with `route('gallery', …)`; response headers.  
  _Verification commands:_  
  - `php artisan test --filter=SitemapTest`  
  - `php artisan test --filter=RssTest`  
  - `php artisan test --filter=GalleryPasswordWebTest`  
  - `make phpstan`

### I5 – Docs and gates
- [x] T-117-12 – Knowledge map entries (sitemap action, controller, middleware group, cache tag).  
  _Verification commands:_ review.

- [x] T-117-13 – Full quality gate.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - `php artisan test --filter=GalleryLockStateTest`, `SitemapLastmodTest`, `SitemapRendererTest`, `SitemapAlbumsTest`, `ManagedCacheAlbumListingInvalidatorTest`, `SitemapTest`, `RssTest`, `GalleryPasswordWebTest` (one at a time)

- [x] T-117-14 – Drift gate report in the plan, roadmap row, `_current-session.md`, commit prepared for the owner.  
  _Verification commands:_ `./scripts/codex-commit-review.sh`

## Notes / TODOs
- Never run two test commands at the same time (shared SQLite test database).
