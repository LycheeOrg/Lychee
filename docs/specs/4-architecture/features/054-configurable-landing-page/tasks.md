# Feature 054 Tasks – Configurable Landing Page

_Status: Draft_
_Last updated: 2026-08-10 (rev 4 — removed T-054-18b/T-054-23a's custom-CSS half (feature dropped); I6 split into I6/I6a/I6b/I6c for automatic + manual (LandingFeaturedItem) featured-content resolution; I11 rebuilt for LandingConfig.vue's 3 tabs (Settings absorbs the entire Mod Welcome category + flat-list filtering, Links, Featured); renamed featured-albums-* tasks to featured-items-*; rev 3 — I11 rebuilt around a dedicated `LandingConfig.vue` page mirroring `NsfwConfig.vue`; rev 2 — added T-054-33a..c for the `studio` layout)_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

### I1 – Backend foundation: enums + scalar configs

- [ ] T-054-01 – Create `App\Enum\LandingLayoutType` (4 values incl. `studio`), `LandingTextPosition`, `LandingAnimationPreset`, `LandingLinkPlacement`, `LandingFeaturedItemsMode`, `LandingFeaturedItemType` (F-054-01, F-054-04, F-054-05, F-054-10, F-054-11, F-054-28).
  _Intent:_ Six small backed enums mirroring `App\Enum\AlbumTitlePosition`'s file shape.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ See spec Appendix for why `LandingTextPosition` is not a reuse of `AlbumTitlePosition`.

- [ ] T-054-02 – Migration: add 11 new scalar landing configs with `type`/`type_range` metadata, filed under the existing `Mod Welcome` category (F-054-01, F-054-03, F-054-04, F-054-05, F-054-08, F-054-09, F-054-10, F-054-18, F-054-20, F-054-24).
  _Intent:_ `landing_layout`, `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_animation_preset`, `landing_about_enabled`, `landing_about_text`, `landing_show_stats`, `landing_featured_items_enabled`, `landing_featured_items_mode`, `landing_featured_items_count`, `landing_cta_text` — defaults matching FR-054-20 exactly.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback --step=1` (verify `down()` is clean), then re-migrate
  _Notes:_ `landing_featured_items_count` needs min/max `type_range` (3-12) for S-054-19. No `landing_custom_css` key — that feature was dropped in favour of the existing global custom CSS/JS mechanism (see spec Non-Goals).

- [ ] T-054-03 – Add all 11 new keys to `App\Http\Middleware\ConfigIntegrity`'s whitelist (F-054-18).
  _Intent:_ Same list `album_header_size`/`album_header_landing_title_enabled` already live in.
  _Verification commands:_
  - `make phpstan`
  - Manual: load Settings page, confirm no "unknown config" warning for the 11 keys.

- [ ] T-054-04 – English translation keys: `all_settings.details.*` for all 11 new configs (F-054-18, TRANS-054-01..07, TRANS-054-11) plus standalone `landing.client_login`/`landing.view_public_gallery`/`landing.contact` keys (TRANS-054-10).
  _Intent:_ Descriptions + enum-value labels so both the flat generic Settings UI (until I11 filters it out) and the later `LandingConfig.vue` page render correct copy with no extra translation work.
  _Verification commands:_
  - Manual: Settings > Landing Page shows all 11 fields with correct widgets and copy.

### I2 – `LandingLink` model, migration, factory

- [ ] T-054-05 – Migration: create `landing_links` table (F-054-11).
  _Intent:_ ULID PK, `label`, `url`, `icon`, `placement`, `open_in_new_tab`, `sort_order`, `enabled`, timestamps; indexes on `enabled`, `placement`.
  _Verification commands:_
  - `php artisan migrate`

- [ ] T-054-06 – `App\Models\LandingLink` + `scopeEnabled()` (F-054-11).
  _Intent:_ Mirror `App\Models\Webhook`'s shape (ULID boot hook, fillable, casts).
  _Verification commands:_
  - `make phpstan`

- [ ] T-054-07 – `LandingLinkFactory` (F-054-11).
  _Intent:_ Test fixture support, mirrors `WebhookFactory`.
  _Verification commands:_
  - `php artisan tinker` sanity check (or a throwaway unit test) creating one factory instance.

### I3 – `LandingLink` admin CRUD (REST)

- [ ] T-054-08 – `StoreLandingLinkRequest` / `UpdateLandingLinkRequest` validation (F-054-11).
  _Intent:_ `label` required ≤255, `url` required valid absolute URL ≤2048, `placement`/`icon`/`open_in_new_tab`/`sort_order`/`enabled` validated per spec.
  _Verification commands:_
  - `php artisan test --filter=LandingLinkRequest`

- [ ] T-054-09 – `LandingLinkResource` (public-safe projection) (F-054-13, DO-054-01).
  _Intent:_ `{id, label, url, icon, placement, open_in_new_tab}` for public embed (empty/null `icon` renders as `lucide:link` on the frontend, not here); admin list adds `enabled`/`sort_order`.
  _Verification commands:_
  - `npm run check` (TypeScript type generated correctly)

- [ ] T-054-10 – `App\Http\Controllers\Admin\LandingLinkController` — index/store/show/update/patch/destroy/reorder (F-054-12, API-054-02..08).
  _Intent:_ Mirror `WebhookController`'s structure; `reorder()` accepts an ordered ID array and bulk-updates `sort_order`.
  _Verification commands:_
  - `php artisan test --filter=LandingLinkController`
  - `make phpstan`

- [ ] T-054-11 – Routes in `routes/api_v2.php` under the admin group (API-054-02..08).
  _Intent:_ `GET/POST /LandingLink`, `GET/PUT/PATCH/DELETE /LandingLink/{landingLink}`, `PATCH /LandingLink/Reorder`.
  _Verification commands:_
  - `php artisan route:list | grep LandingLink`

- [ ] T-054-12 – Feature tests: CRUD, reorder, admin-only 403, count validation (S-054-16, S-054-17, S-054-18, S-054-19).
  _Intent:_ Cover the full CRUD lifecycle plus negative cases.
  _Verification commands:_
  - `php artisan test --filter=LandingLink`

### I4 – `LandingPageResource` extension: layout, animation, intro, position, about, CTA text

- [ ] T-054-13 – Extend `LandingPageResource`: `layout` with SE-fallback resolution, now covering all 3 premium values `portfolio`/`minimal`/`studio` (F-054-01, F-054-02, S-054-02, S-054-03, S-054-21, S-054-22).
  _Intent:_ Reuse `request()->verify()->validate() && ->is_supporter()` exactly as `InitConfig::set_supporter_properties()` does.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [ ] T-054-14 – Extend `LandingPageResource`: `animation_preset` with SE-fallback resolution (F-054-05, F-054-06, S-054-08, S-054-09).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [ ] T-054-15 – Extend `LandingPageResource`: `intro_screen_enabled`, `hero_text_position` passthrough (F-054-03, F-054-04, S-054-05, S-054-07).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [ ] T-054-16 – Extend `LandingPageResource`: `about_enabled`/`about_text` (F-054-08, S-054-11).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [ ] T-054-17 – Extend `LandingPageResource`: `links` array from enabled `LandingLink` rows ordered by `sort_order` (F-054-13, S-054-16).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [ ] T-054-18 – Unit tests: SE-on/SE-off matrix for every SE-gated field (NFR-054-02).
  _Intent:_ Prove fail-safe fallback never throws and never leaks a premium value to a non-SE requester.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [ ] T-054-18a – Extend `LandingPageResource`: `cta_text` passthrough, free tier (F-054-24, S-054-23, S-054-24).
  _Intent:_ Plain string passthrough, no SE gating, ≤255 chars.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

### I5 – Stats resolution

- [ ] T-054-19 – Add `public_photo_count`/`public_album_count` resolution to `LandingPageResource`, gated by effective `landing_show_stats` (F-054-09, S-054-12, NFR-054-03).
  _Intent:_ Counts via `PhotoQueryPolicy::applySearchabilityFilter($query, null, [])` / `AlbumQueryPolicy::applyVisibilityFilter($query, null)`.
  _Verification commands:_
  - `php artisan test --filter=LandingStats`
  _Notes:_ Test fixture must include both public and private photos/albums to prove exclusion.

### I6 – Featured content: automatic-mode resolution

- [ ] T-054-20 – `LandingFeaturedItemResource` — unified photo/album projection (DO-054-03).
  _Intent:_ `{item_type, id, title, thumb_url, url, num_photos?}`; used by both automatic mode (this increment) and manual mode (I6c).
  _Verification commands:_
  - `make phpstan`

- [ ] T-054-21 – Add automatic-mode `featured_items` resolution to `LandingPageResource`, gated by effective `landing_featured_items_enabled` **and** `landing_featured_items_mode=automatic` (F-054-10, S-054-13, S-054-14, S-054-15, NFR-054-03, NFR-054-06).
  _Intent:_ Reuse Feature 025's `resolveLatestAlbumCover` query shape; `LIMIT landing_featured_items_count`; every item projected with `item_type: "album"`.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemsAutomatic`

### I6a – `LandingFeaturedItem` model, migration, factory

- [ ] T-054-21a – Migration: create `landing_featured_items` table (F-054-28).
  _Intent:_ ULID PK, `item_type`, `item_id`, `sort_order`, `enabled`, timestamps; indexes on `enabled`, `item_type`. Mirrors `landing_links`' shape (T-054-05).
  _Verification commands:_
  - `php artisan migrate`

- [ ] T-054-21b – `App\Models\LandingFeaturedItem` + `scopeEnabled()` (F-054-28).
  _Intent:_ Mirror `App\Models\LandingLink`'s shape.
  _Verification commands:_
  - `make phpstan`

- [ ] T-054-21c – `LandingFeaturedItemFactory` (F-054-28).
  _Intent:_ Test fixture support, mirrors `LandingLinkFactory` (T-054-07).
  _Verification commands:_
  - `php artisan tinker` sanity check (or a throwaway unit test) creating one factory instance.

### I6b – `LandingFeaturedItem` admin CRUD (REST)

- [ ] T-054-21d – `StoreLandingFeaturedItemRequest` / `UpdateLandingFeaturedItemRequest` validation (F-054-28).
  _Intent:_ `item_type` restricted to `photo`/`album`; `item_id` must reference an existing `Photo`/`Album` matching `item_type` at write time.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemRequest`

- [ ] T-054-21e – `App\Http\Controllers\Admin\LandingFeaturedItemController` — index/store/show/update/patch/destroy/reorder (F-054-28, API-054-09..15).
  _Intent:_ Mirror `LandingLinkController`'s structure (T-054-10) exactly.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemController`
  - `make phpstan`

- [ ] T-054-21f – Routes in `routes/api_v2.php` under the admin group (API-054-09..15).
  _Intent:_ `GET/POST /LandingFeaturedItem`, `GET/PUT/PATCH/DELETE /LandingFeaturedItem/{landingFeaturedItem}`, `PATCH /LandingFeaturedItem/Reorder`.
  _Verification commands:_
  - `php artisan route:list | grep LandingFeaturedItem`

- [ ] T-054-21g – Feature tests: CRUD, reorder, admin-only 403, item-existence validation (S-054-18, S-054-28).
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItem`

### I6c – Featured content: manual-mode resolution

- [ ] T-054-21h – Add manual-mode `featured_items` resolution to `LandingPageResource`, gated by effective `landing_featured_items_enabled` **and** `landing_featured_items_mode=manual` (F-054-29, S-054-28, S-054-29, S-054-30, NFR-054-03).
  _Intent:_ Enabled `LandingFeaturedItem` rows ordered by `sort_order`, each resolved by direct `Photo`/`Album` lookup on `item_id` — **no** `PhotoQueryPolicy`/`AlbumQueryPolicy` call (mirrors FR-025-04's `photo_id` mode precedent). Missing/deleted referenced records are skipped silently.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemsManual`
  _Notes:_ Test must explicitly assert the policy bypass is intentional (e.g. resolve a private photo successfully when manually curated) so it isn't later "fixed" as a privacy bug — see plan.md's Risks.

### I7 – Frontend: `Landing.vue` dispatcher + `LandingClassic.vue` extraction

- [ ] T-054-22 – Move current `resources/js/v8/views/Landing.vue` markup verbatim into `resources/js/v8/views/landing/LandingClassic.vue` (F-054-15, S-054-01).
  _Intent:_ Pure extraction, no behavioural change yet.
  _Verification commands:_
  - Manual: diff rendered DOM against pre-change snapshot.

- [ ] T-054-23 – Parameterize `LandingClassic.vue` by `intro_screen_enabled`/`hero_text_position`/`animation_preset` (all defaulting to today's fixed behaviour) and render `links` in header/footer (F-054-15, F-054-13, S-054-05, S-054-16).
  _Verification commands:_
  - `npm run check`
  - Manual: default config renders identically to pre-change (S-054-01); toggling `intro_screen_enabled=false` skips the splash (S-054-05).

- [ ] T-054-23a – Wire `cta_text` override into `LandingClassic.vue` (F-054-24, S-054-24).
  _Intent:_ Default-empty so classic stays a no-op by default (NFR-054-01).
  _Verification commands:_
  - `npm run check`
  - Manual: empty value → no visible change; non-empty value overrides the CTA label.

- [ ] T-054-24 – New `Landing.vue` dispatcher: fetch once, route to `LandingClassic.vue` (F-054-14).
  _Intent:_ Preserve existing `landing_page_enable=false` redirect and fetch-error handling.
  _Verification commands:_
  - `npm run check`
  - Manual: `landing_page_enable=false` still redirects to gallery.

### I8 – Shared position/animation composables

- [ ] T-054-25 – `useLandingTextPosition.ts` composable (F-054-04).
  _Intent:_ 5-value Tailwind class map, landing-scoped (not imported from `AlbumHeaderPanel.vue`).
  _Verification commands:_
  - `npm run check`

- [ ] T-054-26 – `useLandingAnimation.ts` composable, incl. `prefers-reduced-motion` override (F-054-05, F-054-07, NFR-054-04, S-054-10).
  _Intent:_ Single choke point — `window.matchMedia('(prefers-reduced-motion: reduce)')` forces `none` regardless of resolved preset.
  _Verification commands:_
  - `npm run check`
  - Manual: OS-level reduced-motion enabled → zero animations on any layout.

- [ ] T-054-27 – CSS keyframes for `zoom_in`/`slide_reveal`; `IntersectionObserver`-driven section reveal for `parallax_scroll` (F-054-05, S-054-08).
  _Verification commands:_
  - Manual: each preset visually verified on `portfolio`.

### I9 – Frontend: `LandingPortfolio.vue`

- [ ] T-054-28 – Sticky nav bar: logo + `links` (nav/both) + Gallery link + Contact link when `footer.is_contact_form_enabled` (F-054-16, F-054-23, S-054-16, S-054-25).
  _Verification commands:_
  - `npm run check`
  - Manual: Contact link present/absent matching `contact_form_enabled`; navigates to the existing `/contact` route.

- [ ] T-054-29 – Hero section: background (existing Feature 025 resolution) + positioned headline/subtitle/CTA (respecting `cta_text` override) using I8 composables (F-054-16, F-054-04, F-054-24, S-054-07, S-054-23).
  _Verification commands:_
  - Manual: all 5 positions verified; `cta_text` override reflected on the button.

- [ ] T-054-30 – Optional about section (F-054-08, F-054-16, S-054-11).
  _Verification commands:_
  - Manual: omitted when `landing_about_enabled=false` or text empty.

- [ ] T-054-31 – Optional featured-content section, rendering `featured_items` regardless of which mode (automatic or manual) produced the array (F-054-10, F-054-16, F-054-29, S-054-13, S-054-14, S-054-15, S-054-28, S-054-29, S-054-30).
  _Verification commands:_
  - Manual: automatic mode full/partial/zero counts verified; manual mode mixed photo+album rendering verified; section omitted when the resolved array is empty either way.

- [ ] T-054-31a – Scroll-down indicator between hero and the next rendered section, reduced-motion-aware (F-054-25, S-054-26, UI-054-05).
  _Intent:_ Uses I8's `useLandingAnimation` choke point — present and clickable (smooth-scroll) under reduced motion, just non-bouncing.
  _Verification commands:_
  - Manual: indicator scrolls correctly; static (no bounce) with OS-level reduced-motion enabled or `animation_preset=none`.

- [ ] T-054-32 – Footer: existing `FooterConfig` + `links` (footer/both) (F-054-16, S-054-16).
  _Verification commands:_
  - `npm run check`

- [ ] T-054-33 – Wire `LandingPortfolio.vue` into `Landing.vue` dispatcher (F-054-14, S-054-02, S-054-03).
  _Verification commands:_
  - Manual: SE-on shows portfolio; SE-off falls back to classic.

### I9a – Frontend: `LandingStudio.vue`

- [ ] T-054-33a – Primary CTA: `RouterLink` to the existing `login` route, label from `cta_text` else `landing.client_login` (F-054-21, NFR-054-10, S-054-21, S-054-24).
  _Intent:_ No new auth code — pure navigation to the existing login flow.
  _Verification commands:_
  - `npm run check`
  - Manual: click navigates to `/login`; label reflects override/default correctly.

- [ ] T-054-33b – Secondary smaller link to the `home` route (public gallery), hero copy (`landing_title`/`landing_subtitle`/`landing_about_text`), optional background, footer `links`/social icons (F-054-21, S-054-21).
  _Verification commands:_
  - `npm run check`

- [ ] T-054-33c – Wire `LandingStudio.vue` into `Landing.vue` dispatcher (F-054-22, S-054-21, S-054-22).
  _Verification commands:_
  - Manual: SE-on + `landing_layout=studio` shows the studio layout; SE-off falls back to classic.

### I10 – Frontend: `LandingMinimal.vue`

- [ ] T-054-34 – Centered card: logo/title/subtitle, optional about text, single CTA (respecting `cta_text` override), footer `links`/social icons + Contact link when `footer.is_contact_form_enabled` (F-054-17, F-054-23, F-054-24, S-054-04, S-054-06, S-054-11, S-054-25).
  _Verification commands:_
  - `npm run check`
  - Manual: no featured-content section present (by design); Contact link present/absent matching the flag, navigates to `/contact`.

- [ ] T-054-35 – Wire `LandingMinimal.vue` into `Landing.vue` dispatcher (F-054-14, S-054-04).
  _Verification commands:_
  - Manual: SE-on + `landing_layout=minimal` shows the minimal layout.

### I11 – Admin UI: `LandingConfig.vue` dedicated page (Settings + Links + Featured tabs)

- [ ] T-054-36 – `resources/js/v8/views/admin/LandingConfig.vue` scaffold: `UTabs` with `settings`/`links`/`featured` slots, structural copy of `NsfwConfig.vue` (F-054-19).
  _Intent:_ Same page shell pattern as `NsfwConfig.vue` — loading state, tab items, `OpenLeftMenu` header.
  _Verification commands:_
  - `npm run check`

- [ ] T-054-36a – Confirm the current full key membership of the `Mod Welcome` config category against `config_categories`/`SettingsController::getAll()` (F-054-19).
  _Intent:_ Implementation Drift Gate item — the exact pre-existing key list wasn't fully enumerated at spec time; needed before building the Settings tab's field groups.
  _Verification commands:_
  - Manual: `php artisan tinker` or DB query listing `configs` rows where category = `Mod Welcome`.

- [ ] T-054-36b – Settings tab: load the **entire** `Mod Welcome` category (pre-existing keys from T-054-36a plus the 11 new keys) via `SettingsService.getAll()`, lay out in curated `Fieldset` sections ("General," "Background," "Branding," "Layout & Structure," "Hero," "Content," "Footer & Social"), save via `SettingsService.setConfigs()` (F-054-19, F-054-18).
  _Verification commands:_
  - `npm run check`
  - Manual: every field in the category loads current value and saves correctly.

- [ ] T-054-36c – SE badge/disabled-state on the `landing_layout` dropdown's `portfolio`/`minimal`/`studio` options and the `landing_animation_preset` dropdown's premium-preset options (UI-054-01, UI-054-02, UI-054-04).
  _Intent:_ Bespoke to this dropdown's rendering — read existing `is_se_enabled`/`is_se_preview_enabled` init data; whole-field `require_se` (as NSFW sets client-side) doesn't fit since only some enum *values* are SE-gated.
  _Verification commands:_
  - `npm run check`
  - Manual: non-SE install sees badges/disabled state on `portfolio`/`minimal`/`studio`/premium animation options.

- [ ] T-054-37 – Links tab: `LandingLink` list/create/edit/delete UI, reusing API-054-02..07 (F-054-19, S-054-16).
  _Verification commands:_
  - `npm run check`
  - Manual: full CRUD via UI.

- [ ] T-054-37a – Links tab drag-reorder calling `PATCH /LandingLink/Reorder` (F-054-19, API-054-08, S-054-17).
  _Verification commands:_
  - Manual: reordered links persist and reflect on the public landing page.

- [ ] T-054-37b – Featured tab: mode switcher (`landing_featured_items_enabled`/`_mode`/`_count`, reusing Settings tab's field components) (F-054-19, F-054-10).
  _Verification commands:_
  - `npm run check`
  - Manual: toggling mode enables/disables the count field and the manual picker below appropriately.

- [ ] T-054-37c – Featured tab: manual-curation picker — search box hitting `GET /api/v2/Search` (Feature 027/028), "Add" action creating a `LandingFeaturedItem` via API-054-10, ordered list with drag-reorder (API-054-15) and per-row enable/delete (F-054-19, F-054-28, S-054-28).
  _Intent:_ No new search backend — reuses the existing Search endpoint as-is.
  _Verification commands:_
  - `npm run check`
  - Manual: search finds both photos and albums by title; add/reorder/delete all work end-to-end.

- [ ] T-054-37d – Patch `SettingsController::getAll()`'s category-visibility filter to exclude `Mod Welcome` from the flat generic list's response (F-054-27, S-054-31).
  _Intent:_ Same mechanism Feature 052's Q-052-07 resolution already established for a different category.
  _Verification commands:_
  - `php artisan test --filter=GetAllSettings`
  - Manual: flat Settings list no longer shows a "Landing page" category/section.

- [ ] T-054-38 – Register `landing-config` route (`router/paths.ts`) and admin tile (`useAdminTiles.ts`, `group: "core"`, visible whenever `can_edit`) (F-054-19, UI-054-06).
  _Intent:_ Mirrors `nsfw-config`'s registration.
  _Verification commands:_
  - Manual: page reachable from the admin dashboard at `/admin/landing-config`.

### I12 – Translation sweep

- [ ] T-054-40 – Propagate `all_settings.php`, `landing.php`, `landing_link.php`, new `landing_featured_item.php` keys across all 22 locales (F-054-18, F-054-19, F-054-28, TRANS-054-01..13).
  _Verification commands:_
  - Repo's existing translation-completeness check (confirm exact command during this task; verify against current tooling).

### I13 – Quality gates & full regression pass

- [ ] T-054-41 – Full backend test suite (all F-054/S-054 IDs).
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`

- [ ] T-054-42 – Full frontend checks (all F-054/S-054 IDs).
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [ ] T-054-43 – Manual walk-through of full Branch & Scenario Matrix S-054-01..31, including confirming zero diff under `resources/js/v7/` (NFR-054-08, S-054-20) and that the flat list no longer shows landing settings (S-054-31).
  _Verification commands:_
  - `git diff --stat -- resources/js/v7/` (expect empty)
  - Manual browser pass per scenario row.

- [ ] T-054-44 – Update `docs/specs/4-architecture/roadmap.md` and `docs/specs/_current-session.md` to reflect completion.
  _Verification commands:_
  - N/A (documentation).

## Notes / TODOs

- The exact translation-completeness command for T-054-40 should be confirmed against current repo tooling at execution time (not verified while drafting this spec/plan).
- If p95 latency on the public landing route looks elevated once I5/I6 land (NFR-054-06), profile before adding any new index — stats and automatic-mode featured content are both opt-in and expected to be rare in practice; manual mode is direct PK lookups, not a filtered query, so it carries no equivalent risk.
- T-054-36a's `Mod Welcome` key inventory should happen early in I11 — the Settings tab's Fieldset grouping (T-054-36b) depends on knowing the real list, not the illustrative one in spec.md's mockup.
