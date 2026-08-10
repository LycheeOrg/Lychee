# Current Session

_Last updated: 2026-08-10_

## Active Features

- Feature 054 – Configurable Landing Page: spec, plan, and tasks drafted (Planning status) on branch `new-landing`. 3 high-impact architecture questions resolved via direct user confirmation same-day. Not yet implemented.
- Feature 052 – Managed Cache Service: **Completed** (T-052-01..22 all `[x]`). Q-052-01..07 all resolved. Full quality gate green; moved to roadmap.md Completed Features.
- Feature 049 – Migration to Nuxt UI: spec, plan, and tasks drafted (Draft status), analysis gate passed. Not yet implemented.
- Feature 048 – Fix Multi-Group Permissions: spec, plan, and tasks drafted (Draft status). Not yet implemented.

Note: Feature 053 (Album Listing Caching) exists on branch `caching-enablement` (commit `fab22c04`), not on this branch — intentionally skipped per user instruction; not tracked here.

## Session Summary

### Feature 054 – Configurable Landing Page — Spec/Plan/Tasks Drafted (this session, 2026-08-10)

**Request:** Rework the landing page (`resources/js/v8/views/Landing.vue`) to be far more configurable: enable/disable the intro splash screen, choose what info displays, reposition hero text (like the existing album "extended hero" title-position feature), add extra links, support multiple animation presets, and support alternate page shapes such as [eikonas.at](https://eikonas.at/) (multi-section portfolio-style page). Explicitly asked to explore and propose flexibility options, including possibly multiple layouts.

**Codebase inventory before drafting:** Read the existing landing page (`v7`/`v8` twins, currently identical single fullscreen-hero design with a timed intro splash, centered CTA, fixed header/menu, `LandingFooter.vue` social icons), Feature 025 (dynamic landing backgrounds — already resolves landscape/portrait backgrounds via 5 modes using `PhotoQueryPolicy`/`AlbumQueryPolicy`, left untouched by this feature), and the album "extended hero" pattern (`AlbumHeaderPanel.vue` — 5-position `AlbumTitlePosition`, 7-color `AlbumTitleColor`, focus-point picker, inline edit mode) which directly matches the user's "pro position of text" reference. Also reviewed Feature 039 (white-label, SE-gating precedent) and Feature 031 (configurable webhooks — CRUD-list pattern used as the template for the new extra-links feature) and confirmed `AllSettings.vue` renders scalar configs generically from DB metadata (no bespoke Vue needed for simple new config keys).

**Three high-impact architecture questions resolved same-day via direct user confirmation** (all logged as Q-054-01..03, resolved option marked in each case):
- Q-054-01 (shape): **multiple named layout templates** — `classic` (today's page, unchanged default, free), `portfolio` (new, eikonas.at-inspired scrollable multi-section page, SE), `minimal` (new, centered single-card page, SE) — chosen over a single mega-configurable hero or a full modular/reorderable section builder (deferred to Follow-ups as a much larger, separate feature).
- Q-054-02 (frontend scope): **v8 (Nuxt UI) only** — `resources/js/v7/views/Landing.vue` is explicitly untouched forever by this feature, matching the precedent set by Feature 051 (Admin Setup Page, v8-only) given v7 is being actively retired by Feature 049.
- Q-054-03 (SE gating): **classic layout + extra links stay free forever; `portfolio`/`minimal` layouts and 3 new animation presets (`zoom_in`/`parallax_scroll`/`slide_reveal`) are Lychee SE-exclusive**, resolving fail-safe to the free defaults (`classic`/`classic_fade`) when SE is inactive — mirrors Feature 039's white-label SE-gating pattern. Hero text position and extra links were deliberately kept free since they extend already-free precedents (album hero position; footer social links) rather than introducing a new page shape.

**Spec shape (spec.md):** 20 Functional Requirements, 9 Non-Functional Requirements, 20 Branch & Scenario Matrix rows (S-054-01..20), full Interface & Contract Catalogue (3 domain objects, 8 API routes including a new admin CRUD for extra links, 2 migrations, 9 translation-key groups, 4 new enums). Key design points: `LandingPageResource` (Feature 025) is extended, not replaced, and reuses its exact query-policy usage pattern for two new opt-in content blocks (public gallery stats, featured-albums preview grid). A new `App\Models\LandingLink` (admin-manageable, ordered, nav/footer/both placement) mirrors `App\Models\Webhook`'s CRUD shape exactly. A deliberate small design choice is documented in the spec Appendix: a new landing-scoped `LandingTextPosition` enum duplicates `AlbumTitlePosition`'s 5 values rather than importing it, to avoid coupling the Landing and Album bounded contexts for a 5-string-case saving.

**Plan shape (plan.md):** 13 increments (I1 backend config foundation → I2/I3 extra-links data+CRUD → I4/I5/I6 `LandingPageResource` extension incl. SE-fallback resolution, stats, featured albums → I7 zero-regression `LandingClassic.vue` extraction (the core risk-mitigation step, isolated before any new layout work starts) → I8 shared position/animation composables (single choke point for `prefers-reduced-motion` handling) → I9/I10 new `LandingPortfolio.vue`/`LandingMinimal.vue` → I11 admin `LandingLinks.vue` UI + SE badges → I12 translation sweep → I13 quality gates). 44 tasks in tasks.md, each tagged with FR/NFR/Scenario IDs.

**Not yet done:** Implementation (I1/T-054-01 onward). Analysis Gate not yet run — plan.md flags it must happen at the start of implementation with a fresh re-read of `LandingPageResource.php`/`AlbumHeaderPanel.vue`/`AllSettings.vue` since they may have changed since drafting.

### Feature 054 — Extra-Layout/Extra-Feature Brainstorm & Spec Expansion (same session, 2026-08-10)

**Request:** After the initial draft, asked to think about extra things to add/improve and propose extra new layouts beyond classic/portfolio/minimal.

**Brainstormed 5 additional layout candidates and ~10 non-layout improvements**, tiered by cost/value (full list preserved in spec.md's Appendix, "Extra-Layout and Extra-Feature Brainstorm," so the reasoning behind deferred items isn't lost). Confirmed via targeted greps before proposing that neither a site-wide language switcher nor a cookie-consent mechanism exists anywhere in `resources/js/v8` today, so both were framed as bigger cross-cutting asks rather than landing-page scope.

**Resolved via direct user confirmation** (logged as Q-054-04/05):
- **Q-054-04 (layouts):** add a 4th layout, **`studio`** — client-login-first hero (primary CTA is the existing `login` route, secondary smaller link to the public gallery), aimed at studios whose visitors are mostly returning clients rather than public browsers. This is a different information architecture, not a re-skin. Mosaic/grid-first, "coming soon," split-screen editorial, and cinematic/video-hero were all deferred to backlog (video explicitly flagged as contradicting the feature's inherited Non-Goal from Feature 025 rather than silently reversed).
- **Q-054-05 (features):** fold in the 3 "cheap, high-value" items plus a raw CSS override: (1) Contact-form surfacing (wires the already-built Feature 022 onto `portfolio`/`minimal` nav/footer — no new backend), (2) `landing_cta_text` override for the primary CTA label, (3) a reduced-motion-aware scroll-down indicator on `portfolio`, (4) `landing_custom_css` — an admin-authored raw-CSS escape hatch, explicitly given the same no-sanitization trust model as the existing `footer_additional_text` field (confirmed via code read that no sanitizer/purifier is applied to that field today, so this isn't inventing an inconsistent trust boundary). A second About-section image slot and a testimonials/client-logos CRUD block were priced out as real scope and deferred.

**Spec/plan/tasks all updated in place** (not appended as addenda) per the repo's "no per-feature Clarifications section, encode resolved answers directly in the normative sections" guardrail: FR-054-01/02 amended to include `studio`; 6 new FRs (FR-054-21..26) and 2 new NFRs (NFR-054-10/11) added; Branch & Scenario Matrix extended to S-054-27; a new plan increment I9a inserted for `LandingStudio.vue`; ~7 tasks added/extended in tasks.md (now ~48 tasks / 14 increments). Scalar-config count is now 11 (was 9): added `landing_cta_text`, `landing_custom_css`.

**Not yet done:** Same as above — implementation not started.

### Feature 054 — Fourth Revision: Featured-Content Redesign, Full Mod Welcome Absorption, Custom CSS Dropped, eikonas.at References Removed (same session, 2026-08-10)

**Requests, in order, across several rapid mid-turn corrections:**
1. "We don't want custom css/js for the landing, we can just reuse the normal custom. It is on the user to figure out how to configure things for them to work."
2. "Yes `LandingConfig.vue` should absorb the entire `Mod Welcome` category."
3. Two clarifying questions answered ("What is the LandingLink.icon thing?" / "What is the featured albums thing?"), followed by: icon → free-text confirmed; featured content → "Fully manual curation, it should be possible to select either photos or albums," then refined to "Actually automatic by default, but support fully manual curation."
4. Separately, via IDE file-open context: "Do not mention eikonas.at in the specification. We do not want to advertise this. Explain the layout etc but do not link directly."

**Investigation before editing:** grepped the codebase and confirmed Lychee already has a global custom CSS/JS mechanism — `SettingsController::setCSS()`/`setJS()` write to `dist/user.css`/`dist/custom.js`, exposed via `App\View\Components\Meta`'s `user_css_url`/`user_js_url`, and loaded unconditionally in the shared `vueapp.blade.php` shell's `<x-meta />` (used by both v7 and v8, confirmed by reading the blade file) — so it already applies to every landing layout today with zero new work. Also confirmed `Mod Welcome` is a real, pre-existing settings category (`config_categories` table) holding the current landing keys, and that `GET /api/v2/Search` (Feature 027/028) already exists and can be reused for a photo/album picker.

**Resolved (Q-054-07..10, logged in `open-questions.md`):**
- Q-054-07: **`landing_custom_css` dropped entirely** — reuse the existing global mechanism; added as an explicit new Non-Goal with the `Meta.php`/`SettingsController` citations, and NFR-054-09's dead-end-guardrail clause rewritten to reference the pre-existing (not new) risk.
- Q-054-08: **`LandingConfig.vue`'s Settings tab now absorbs the *entire* `Mod Welcome` category**, not just the 11 new keys — and, unlike Feature 045's `NsfwConfig.vue` precedent (whose curated keys stay visible in the flat list too), `Mod Welcome` is now filtered out of the flat list entirely (new FR-054-27, reusing the category-visibility-filter mechanism Feature 052's Q-052-07 already established) since full-category duplication would be pure redundancy, unlike NSFW's partial-key case.
- Q-054-09: **Featured content redesigned** from "automatic-only, albums-only" to "automatic by default, with full manual curation of photos *and* albums also supported." New `landing_featured_items_mode` enum (`automatic`/`manual`), new `App\Models\LandingFeaturedItem` CRUD (mirrors `LandingLink`'s ULID/CRUD/reorder shape exactly, FR-054-28), new manual-resolution logic that deliberately bypasses the public-visibility policy check the same way Feature 025's `photo_id` background mode already does (FR-054-29, admin-trusted precedent) — with an explicit test-intent note so this isn't later "fixed" as a privacy bug. `LandingConfig.vue` gained a third tab ("Featured") housing the mode switcher plus a manual picker built on the existing Search endpoint.
- Q-054-10: **`LandingLink.icon` confirmed free-text** (not a visual picker), defaulting to `lucide:link` when empty.

**Also completed:** every named/linked reference to the external portfolio-site example that originally inspired the `portfolio` layout was removed from `spec.md` (Overview, Goals, FR-054-16, both mockup headers, Appendix's Related Request/brainstorm/Q-054-01 sections) — the layout is now described purely structurally ("scrollable, multi-section portfolio-style page: nav → hero → about → featured work → footer"), per explicit instruction not to advertise the source.

**Spec/plan/tasks all rewritten (rev 4)** — given the scale of interlocking changes (new model, new migration, renumbered scenarios S-054-01..31, new tabs, filtered category), all three files were fully rewritten rather than patched, after first re-reading each current file in full to avoid losing earlier content. Increment count grew from 13 to 16 (new I6a/I6b/I6c for `LandingFeaturedItem`'s model/CRUD/manual-resolution, split out the same way `LandingLink`'s I2/I3 already were); task count grew to ~55.

**Not yet done:** Same as above — implementation not started.

### Feature 054 — Admin UI Architecture Revision (same session, 2026-08-10)

**Request:** "I think we may want to have a configuration page similar to the NSFW classifier."

**Read `resources/js/v8/views/admin/NsfwConfig.vue` (Feature 045)** to confirm the exact pattern being referenced: a dedicated admin page (`UTabs`, curated `Fieldset` sections per tab) that still reads/writes through the same generic `SettingsService.getAll()`/`setConfigs()` API the flat settings list uses — no new config backend, just a better-organized UI on top. Confirmed (no `SettingsController` filtering found for NSFW's keys) that curated-page keys are *not* hidden from the flat generic list — both views coexist.

**Resolved as Q-054-06:** replaced FR-054-18/19's original "no bespoke Vue needed, flat generic settings list is sufficient" with a dedicated `resources/js/v8/views/admin/LandingConfig.vue` page — a Settings tab (11 keys in 4 curated `Fieldset` groups: Layout & Structure, Hero, Content, Advanced) plus a Links tab (the `LandingLink` CRUD, folded in here instead of the previously-planned standalone `LandingLinks.vue` route — mirrors how `NsfwConfig.vue`'s own second tab, "Presets," shows a related-but-distinct view alongside "Settings"). Registered as an admin tile (`group: "core"`, alongside `settings`/`design-system`) at `/admin/landing-config`. The flat generic list keeps working unfiltered in parallel, matching NSFW's precedent.

**Spec/plan/tasks updated in place** (rev 3): FR-054-18/19 rewritten; new UI-054-06; plan's I11 rebuilt in full (was "generic UI + separate LandingLinks.vue page," now "LandingConfig.vue scaffold → Settings tab → SE badges → Links tab → reorder → route/tile registration"); tasks.md's I11 block rewritten (T-054-36/36a/36b/37/37a/38). Task/increment counts unchanged in shape, only I11's content changed.

**Not yet done:** Same as above — implementation not started.



### Feature 052 – Managed Cache Service — Implemented (this session, 2026-07-28)

**Request:** Write plan.md/tasks.md for the already-spec-complete Feature 052, do a clarification pass, then implement.

**Two new open questions found while grounding the plan in the current codebase** (logged with full Decision Cards, both resolved same-day):
- **Q-052-06** — `App\Events\AlbumDeleted` carries only `parent_id`, not the deleted album's own id, so FR-052-06's listener can't literally evict "the album's own tag" on delete. **Resolved Option A** (recommended): evict only the parent's tag; no event-payload change. `ManagedCacheAlbumInvalidator::handleAlbumDeleted()` implements this.
- **Q-052-07** — Reusing the existing `'Mod Cache'` Settings category for `managed_cache_enabled`/`managed_cache_ttl` would hide both by default (that category is gated on `features.enable-request-caching`, which defaults `false`), contradicting the required independence from Feature 040. **Resolved Option B** (user overrode the recommended new-category option): share `'Mod Cache'`, but patch `SettingsController::getAll()`'s visibility filter to exempt those two keys specifically.

**Implementation (all 22 tasks, T-052-01..22, ~30 new/changed files):**
- `App\Services\Cache\ManagedCacheService` (`app/Services/Cache/ManagedCacheService.php`) — `remember()`/`forgetTag()`/`addTags()`, hand-rolled key-list tag bookkeeping (mirrors `RouteCacher`), gated on DB-backed `managed_cache_enabled` via constructor-injected `ConfigManager` (not the `config()` helper — configs live in the `configs` table).
- New events `AccessPermissionChanged`, `UserGroupMembershipChanged`; three previously-silent mutation points now dispatch: `Actions\Album\Move::do()` (also dispatches for the album's *previous* parent when it changed — needed for S-052-06 "both parents invalidated," not just the new one), `SharingController` (create/edit/delete/propagate), `UserGroupsManagementController` (addUser/removeUser/updateUserRole).
- `ManagedCacheAlbumInvalidator` (7 events → album+parent tag eviction, photo events resolved via `photo_album` pivot) and `ManagedCacheUserInvalidator` (1 event → user tag), registered in `EventServiceProvider`.
- `AlbumRepository::getChildrenPaginated()` and `PhotoRepository::getPhotosForAlbumPaginated()` both adopt `remember()`; cache key/tag templates match spec FR-052-09/10 exactly, using `Illuminate\Pagination\Paginator::resolveCurrentPage()` (not `request()->query('page')`) so the cache key stays in lock-step with whatever page `paginate()` itself resolves.
- Migration `2026_07_28_000001_managed_cache_config.php` (config rows) + `SettingsController` filter patch (Q-052-07).

**Two real correctness gaps found and fixed beyond the original spec text (not scope creep — both close testable Branch & Scenario Matrix rows already in spec.md):**
1. `Move::do()` originally only dispatched `AlbumSaved` for the moved album itself, which only carries its *post-move* (new) parent — the *old* parent's cached children-list would never be invalidated. Fixed by also dispatching `AlbumSaved` for the previous parent when it changed, mirroring the existing `Photo\MoveOrDuplicate` from/to dispatch pattern.
2. `ManagedCacheService::remember()`'s tags-up-front signature can't express "tag with the id of every item in the computed result" (needed for FR-052-09's per-child tagging). Added a small `addTags(key, tags)` method (no spec/contract change to `remember()` itself) to associate extra tags with an already-cached key after the callback has run.

**One spec self-consistency finding, no fix needed:** S-052-07 (ancestor-chain cascade, FR-052-08) is not actually exercised by FR-052-09/10's *normative* tag lists (parent + per-item tags only, no ancestor walk) — confirmed N/A for the two pilot consumers as specified, documented in plan.md's Scenario Tracking table rather than silently dropped.

**Testing:** ~35 new tests across `tests/Unit/Services/Cache/`, `tests/Unit/Listeners/`, `tests/Unit/Repositories/`, `tests/Feature_v2/Caching/` (new directory — real end-to-end wiring proofs with no faking), plus extensions to `AlbumMoveTest`, `SharingTest`, `UserGroupMembershipTest`, `GetAllSettingsTest`. Two pre-existing-infrastructure pitfalls hit and worked around (documented in tasks.md Notes): `Illuminate\Cache\Events\*` firing on every `Cache::get()`/`put()` call means NFR-052-03 query-count tests must filter to the `albums`/`photos` table specifically, not assert literal zero; and `actingAs()` leaves the auth guard authenticated across calls within a test method, so simulating "guest after an authenticated call" needs an explicit `forgetGuards()`.

**Closed out:** Full `php artisan test` suite run to completion once (2896 passed / 3 failed — 2 were this feature's own test bug since fixed and re-verified, 1 pre-existing/unrelated confirmed via `git stash`); Implementation Drift Gate recorded in plan.md (Pass); `docs/specs/4-architecture/roadmap.md` moved from Active to Completed. A pre-existing, unrelated full-suite infrastructure issue was also found and documented (not fixed, out of scope): several Artisan commands call `set_time_limit(600)`, which resets the execution-timer budget for the entire `php artisan test` process (one continuous PHP process for the whole suite), so a slow-enough run can fatal near the end regardless of test content.

### Feature 052 (prior session, 2026-07-21) — Spec drafted and complete, all open questions resolved

**Request:** New feature to cache values whose result depends on the requesting user's access rights to one or more albums, with a dependency-mapping mechanism (album id + user id) so cached entries can be invalidated when access rights change, a photo is uploaded, an album is moved, etc.

**Existing infrastructure found (adjacent, not reused as-is):** `App\Metadata\Cache\RouteCacher`/`RouteCacheManager`/`App\Enum\CacheTag` already caches whole HTTP responses keyed by route+user, tagged by album id, with `AlbumCacheCleaner`/`TaggedRouteCacheCleaner` listeners reacting to `AlbumRouteCacheUpdated`/`TaggedRouteCacheUpdated`. Governed by `cache_enabled`, forced off by default since Feature 040 (`disable-request-caching`). Left untouched — this feature builds a new, independent, general-purpose service instead.

**Three real invalidation gaps confirmed during investigation (all fixed by this feature, FR-052-03/04/05):**
- `App\Actions\Album\Move::do()` (`app/Actions/Album/Move.php`) dispatches no event on album move/re-parent.
- `App\Http\Controllers\Gallery\SharingController` (`create`/`edit`/`delete`/`propagate`) dispatches no event when `AccessPermission` rows change.
- `App\Http\Controllers\Admin\UserGroupsManagementController` (`addUser`/`removeUser`/`updateUserRole`) dispatches no event on group-membership change.

**5 open questions logged and resolved same-day** (Q-052-01..05, all Option A):
- Q-052-01: generic service (not query-specific). **Pilot consumer changed 2026-07-21 (user instruction, post-resolution):** instead of `BaseAlbumImpl::current_user_permissions()`, the two pilots are `AlbumRepository::getChildrenPaginated()` (sub-albums) and `PhotoRepository::getPhotosForAlbumPaginated()` (photos) — both permission-filtered, hit on every album view, and the exact routes (`Album::albums`/`Album::photos`) the existing HTTP response cache already lists but runs uncached by default. No other adoption in this feature.
- Q-052-02: new independent `App\Services\Cache\ManagedCacheService` — user clarified it "does not necessarily have to be related to Query," driving the generic (not "query cache") naming/shape.
- Q-052-03: new independent config flag `managed_cache_enabled` (default `true`), decoupled from Feature 040's `cache_enabled`. New `managed_cache_ttl` config too.
- Q-052-04: ancestor-path tagging (tag a cached entry with its own album id + every ancestor id via `Album::ancestorsOf()`) so an ancestor's tag eviction reaches descendants without a runtime tree walk. **User correction:** there is no native cache-tagging primitive available (default `file` driver) — "tags" are hand-rolled key-list bookkeeping (a tag is a cache key whose value is a set of member keys), mirroring `RouteCacher::rememberTags()`/`forgetTag()`'s existing pattern, reimplemented independently.
- Q-052-05: user-group membership change is in scope as an invalidation trigger (new `UserGroupMembershipChanged` event, third gap found to match).

**Spec is now feature-complete** (FR-052-01..11, NFR-052-01..05, S-052-01..12, full Interface & Contract Catalogue including two new events `AccessPermissionChanged`/`UserGroupMembershipChanged` and two new listeners `ManagedCacheAlbumInvalidator`/`ManagedCacheUserInvalidator`). Directory renamed `052-query-cache-service` → `052-managed-cache-service` to match the generic naming resolution. The album-invalidation listener also evicts a mutated album's immediate-parent tag, closing a "negative cache" gap (a child becoming newly visible/hidden must still invalidate the parent's cached children list even though that list never referenced the child).

**Not yet done:** plan.md/tasks.md (spec is ready for planning — Analysis Gate not yet run). Implementation not started.

### Feature 049 – Migration to Nuxt UI — Spec/Plan/Tasks Drafted (this session)

**Request:** Replace PrimeVue (`primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons`) with Nuxt UI (`@nuxt/ui`, standalone Vue mode — no full Nuxt framework) across the frontend.

**Codebase inventory (2026-07-02):** PrimeVue imported in 235 of 286 `.vue`/`.ts` files (~82%). Largest costs: `tailwindcss-primeui` utility classes in 197 files, a ~500-line custom theme preset (`resources/js/style/preset.ts`) with no Nuxt UI equivalent, and PrimeVue's `pt`/`dt` pass-through styling APIs in 36-42 files. High-frequency components: Button (154 files), `useToast` (119 call sites, no wrapper composable existed), Dialog (55), Toolbar (42, no direct Nuxt UI equivalent), ProgressSpinner (41). Icons: `primeicons` (562 occurrences/139 files) — an Iconify collection matching PrimeIcons 1:1 (`@iconify-json/prime`) exists on npm. DataTable (10 admin/statistics/webshop files) requires structural rewrite (TanStack-Table-based `UTable` vs. PrimeVue's slot-based API). Embed bundle (`resources/js/embed/`) has zero PrimeVue coupling — explicitly out of scope. No frontend automated test suite exists (0 `.test.ts` files) — verification is manual/browser-based.

**Three high-impact open questions resolved same-day (recommended option chosen in all three):**
- Q-049-01 (sizing): one feature, full scope, tracked to completion (not split across features, not foundation-only).
- Q-049-02 (icons): parity via `@iconify-json/prime`, no visual redesign bundled into this migration.
- Q-049-03 (ripple): dropped entirely, no replacement; Reka UI's built-in focus-trap replaces `v-focustrap`.

**ADR-0005** recorded (`docs/specs/6-decisions/ADR-0005-nuxt-ui-migration.md`) capturing the overall decision and all three sub-decisions.

**Plan shape:** 15 phases / 45 increments (I1-I43 plus I7a/I7b, I26a/I26b sub-increments) / 43 tasks in tasks.md. Ordered: foundation (install, theme, icons) → new composables (`useAppToast()`, `useConfirmDialog()` — fill gaps Nuxt UI has no built-in for) → app shell (`App.vue`, `LeftMenu.vue`) → toast/confirm call-site sweep → Button (154 files, 6 increments) → Dialog (55 files, 4 increments) → Toolbar (42 files, 2 increments, composed-flex-header pattern since no direct equivalent) → loading/layout primitives → form primitives (8 existing wrapper components are the migration seam) → navigation/context-menu → DataTable (10 files, structural rewrite, 3 increments) → misc components → pass-through/directive cleanup → dependency removal (hard completion gate, FR-049-18) → documentation sync.

**Analysis gate:** run and passed 2026-07-02 (see plan.md's Analysis Gate section) — one intentional, documented deviation (branch-coverage matrix replaced by per-directory grep-verified completion sweeps, appropriate for a mechanical library migration rather than new business logic).

**Not yet done:** Implementation (I1 onward / T-049-01 onward). Dependency approvals still needed before `npm install` for `@nuxt/ui` (T-049-01), `@iconify-json/prime` (T-049-03), and optionally `@tanstack/vue-virtual` (T-049-37).

### Feature 048 – Fix Multi-Group Permissions — Spec/Plan/Tasks Drafted (prior session, 2026-07-01)

**Bug report:** A user who belongs to two groups on the same album (e.g. "All" = View only, "Support_VIP" = View+Access+Download) only received the grants of whichever group's `AccessPermission` row was created first. Reordering the shares flipped the outcome.

**Root cause:** `BaseAlbumImpl::current_user_permissions()` (`app/Models/BaseAlbumImpl.php:261-271`) uses `Collection::first()` to pick a single matching `AccessPermission` row instead of merging every matching row.

**Resolution direction (Q-048-01, resolved — Option A):** Collect every matching row (direct-user row + every row for a group the user belongs to) and OR each of the 5 boolean grant flags. No precedence between direct-user and group rows — most permissive always wins, matching the existing pattern already used elsewhere (`AlbumPolicy` ORs `current_user_permissions()` with `public_permissions()`; `canDeleteById`/`canEditById` already OR across groups in SQL).

**Follow-up design decision (user-requested):** instead of returning a synthetic `App\Models\AccessPermission` Eloquent instance (which is inherently persistable — mass-assignable, `save()`-able), the merged result is a new `App\DTO\EffectiveAccessPermission` (`final readonly class`, plain DTO, matches the existing `CheckoutDTO`/`PixelSizeAssignment` style). This makes "cannot be persisted by accident" a type-level guarantee (NFR-048-03). `current_user_permissions()`'s return type changes accordingly across `BaseAlbumImpl`, the `BaseAlbum` trait, and `@property` docblocks on `Album`/`TagAlbum`/`PersonAlbum` (FR-048-04).

**Key constraint:** Zero new DB queries (NFR-048-01) — `access_permissions` is already eager-loaded via `BaseAlbumImpl::$with`, and `$user->user_groups` is already read by the current (buggy) code, so the fix is a pure in-memory `Collection` merge.

**Not yet done:** Implementation (I1–I7 in plan.md / T-048-01–11 in tasks.md), ADR-0004, quality gates.

## Next Steps

1. Feature 054 spec/plan/tasks are drafted but implementation has not started — begin at T-054-01 (enums + scalar config migration) on branch `new-landing` — see [tasks.md](4-architecture/features/054-configurable-landing-page/tasks.md). Run the plan's Implementation Drift Gate re-read first (`LandingPageResource.php`, `AlbumHeaderPanel.vue`, `AllSettings.vue`) since it was not run this session.
2. Feature 052 is done — no follow-up required unless broader `ManagedCacheService` adoption (deferred per spec Non-Goals) is picked up as a future feature.
2. Confirm dependency approvals (`@nuxt/ui`, `@iconify-json/prime`) with the user, then start Feature 049 implementation at T-049-01 (install Nuxt UI in standalone Vue mode) — see [tasks.md](4-architecture/features/049-nuxt-ui-migration/tasks.md).
3. Alternatively/in parallel across sessions: start Feature 048 implementation at T-048-01 (repo-wide caller sweep) then T-048-02/03 (unit tests reproducing the bug) — see [tasks.md](4-architecture/features/048-fix-multi-group-permissions/tasks.md).
4. Feature 047 (Person Smart Album) remains drafted but not implemented — no active work this session.
5. Feature 042 Part B (I7–I10, admin maintenance photo title links) remains outstanding from a prior session — see [tasks.md](4-architecture/features/042-webshop-order-item-display/tasks.md) T-042-16 to T-042-20.

## Open Questions

None blocking. Q-054-01..10 resolved 2026-08-10 (see spec.md Appendix for full rationale). Q-052-01..07 all resolved (01-05 on 2026-07-21, 06-07 on 2026-07-28 — see spec.md and open-questions.md for full rationale, including Q-052-07's non-default Option B resolution). Q-049-01, Q-049-02, Q-049-03 resolved 2026-07-02 (ADR-0005). Q-048-01 resolved 2026-07-01.

## Key Artefacts

- Feature 054: [spec.md](4-architecture/features/054-configurable-landing-page/spec.md) · [plan.md](4-architecture/features/054-configurable-landing-page/plan.md) · [tasks.md](4-architecture/features/054-configurable-landing-page/tasks.md) (drafted, not yet implemented)
- Feature 052: [spec.md](4-architecture/features/052-managed-cache-service/spec.md) · [plan.md](4-architecture/features/052-managed-cache-service/plan.md) · [tasks.md](4-architecture/features/052-managed-cache-service/tasks.md) (implemented, T-052-01..22 all `[x]`)
- Feature 049: [spec.md](4-architecture/features/049-nuxt-ui-migration/spec.md) · [plan.md](4-architecture/features/049-nuxt-ui-migration/plan.md) · [tasks.md](4-architecture/features/049-nuxt-ui-migration/tasks.md) · [ADR-0005](6-decisions/ADR-0005-nuxt-ui-migration.md)
- Feature 048: [spec.md](4-architecture/features/048-fix-multi-group-permissions/spec.md) · [plan.md](4-architecture/features/048-fix-multi-group-permissions/plan.md) · [tasks.md](4-architecture/features/048-fix-multi-group-permissions/tasks.md)
- Open questions: [open-questions.md](4-architecture/open-questions.md) (Q-052-01..07, Q-049-01..03, Q-048-01 — all resolved)
- Roadmap: [roadmap.md](4-architecture/roadmap.md)
- Knowledge map: [knowledge-map.md](4-architecture/knowledge-map.md) (Frontend Dependencies section annotated with the pending PrimeVue→Nuxt UI swap; Feature 052's `ManagedCacheService`/events/listeners documented under Infrastructure Layer)
