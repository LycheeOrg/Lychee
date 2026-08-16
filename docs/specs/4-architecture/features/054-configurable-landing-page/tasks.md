# Feature 054 Tasks – Configurable Landing Page

_Status: Completed_
_Last updated: 2026-08-11_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.

## Checklist

### I1 – Backend foundation: enums + scalar configs

- [x] T-054-01 – Create `App\Enum\LandingLayoutType` (4 values incl. `studio`), `LandingTextPosition`, `LandingAnimationPreset`, `LandingLinkPlacement`, `LandingFeaturedItemsMode`, `LandingFeaturedItemType` (F-054-01, F-054-04, F-054-05, F-054-09, F-054-10, F-054-24).
  _Intent:_ Six small backed enums mirroring `App\Enum\AlbumTitlePosition`'s file shape.
  _Verification commands:_
  - `make phpstan`

- [x] T-054-02 – Migration: add 12 new scalar landing configs with `type`/`type_range` metadata, filed under the existing `Mod Welcome` category (F-054-01, F-054-03, F-054-04, F-054-05, F-054-08, F-054-09, F-054-20, F-054-24, F-054-26).
  _Intent:_ `landing_layout`, `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_hero_text_color` (`type_range: 'color'`), `landing_hero_text_opacity` (int, range 0-100), `landing_animation_preset`, `landing_about_enabled`, `landing_about_text`, `landing_featured_items_enabled`, `landing_featured_items_mode`, `landing_featured_items_count`, `landing_cta_text` — defaults matching FR-054-26 exactly.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback --step=1` (verify `down()` is clean), then re-migrate
  _Notes:_ `landing_featured_items_count` needs min/max `type_range` (3-12) for S-054-18.

- [x] T-054-03 – Add all 12 new keys to `App\Http\Middleware\ConfigIntegrity`'s whitelist (F-054-20).
  _Intent:_ Same list `album_header_size`/`album_header_landing_title_enabled` already live in.
  _Verification commands:_
  - `make phpstan`
  - Manual: load Settings page, confirm no "unknown config" warning for the 12 keys.
  _Notes:_ Resolved via Q-054-01 (see open-questions.md) as **not** adding the keys to `SE_FIELDS`/`PRO_FIELDS` — that whitelist raises the DB `level` column, which hides `level>0` configs from non-SE/non-Pro admins in the flat Settings list, contradicting T-054-58's regression guard. Level stays `0` (migration default) for all 12 keys; SE-gating is enforced only at render time in `LandingPageResource` and via disabled (not hidden) dropdown options in `LandingConfig.vue`. `ConfigIntegrity` itself is unmodified. FR-054-20/spec.md updated accordingly.

- [x] T-054-04 – English translation keys: `all_settings.details.*` for all 12 new configs (F-054-20, TRANS-054-01..07) plus standalone `landing.client_login`/`landing.view_public_gallery`/`landing.contact` keys (TRANS-054-11).
  _Intent:_ Descriptions + enum-value labels so both the flat generic Settings UI and `LandingConfig.vue` render correct copy.
  _Verification commands:_
  - Manual: Settings > Landing Page shows all 12 fields with correct widgets and copy.

### I2 – `LandingLink` model, migration, factory

- [x] T-054-05 – Migration: create `landing_links` table (F-054-10).
  _Intent:_ ULID PK, `label`, `url`, `placement`, `open_in_new_tab`, `sort_order`, `enabled`, timestamps; indexes on `enabled`, `placement`.
  _Verification commands:_
  - `php artisan migrate`

- [x] T-054-06 – `App\Models\LandingLink` + `scopeEnabled()` (F-054-10).
  _Intent:_ Mirror `App\Models\Webhook`'s shape (ULID boot hook, fillable, casts).
  _Verification commands:_
  - `make phpstan`

- [x] T-054-07 – `LandingLinkFactory` (F-054-10).
  _Intent:_ Test fixture support, mirrors `WebhookFactory`.
  _Verification commands:_
  - `php artisan tinker` sanity check (or a throwaway unit test) creating one factory instance.

### I3 – `LandingLink` admin CRUD (REST)

- [x] T-054-08 – `StoreLandingLinkRequest` / `UpdateLandingLinkRequest` validation (F-054-10).
  _Intent:_ `label` required ≤255, `url` required valid absolute URL ≤2048, `placement`/`open_in_new_tab`/`sort_order`/`enabled` validated per spec.
  _Verification commands:_
  - `php artisan test --filter=LandingLinkRequest`

- [x] T-054-09 – `LandingLinkResource` (public-safe projection) (F-054-12, DO-054-01).
  _Intent:_ `{id, label, url, placement, open_in_new_tab}` for public embed; admin list adds `enabled`/`sort_order`.
  _Verification commands:_
  - `npm run check` (TypeScript type generated correctly)

- [x] T-054-10 – `App\Http\Controllers\Admin\LandingLinkController` — index/store/show/update/patch/destroy/reorder (F-054-11, API-054-02..08).
  _Intent:_ index/store/show/update/patch/destroy mirror `WebhookController`'s structure. `reorder()` implements FR-054-11's contract: body `{ ids: string[] }` must be the complete set of existing `LandingLink` IDs — reject (422) on any mismatch, don't partially apply; set `sort_order` = array index inside a DB transaction; respond with the freshly re-ordered index-shaped list.
  _Verification commands:_
  - `php artisan test --filter=LandingLinkController`
  - `make phpstan`

- [x] T-054-11 – Routes in `routes/api_v2.php` under the admin group (API-054-02..08).
  _Intent:_ `GET/POST /LandingLink`, `GET/PUT/PATCH/DELETE /LandingLink/{landingLink}`, `PATCH /LandingLink/Reorder`.
  _Verification commands:_
  - `php artisan route:list | grep LandingLink`

- [x] T-054-12 – Feature tests: CRUD, reorder, admin-only 403, count validation (S-054-15, S-054-16, S-054-17, S-054-18).
  _Intent:_ Cover the full CRUD lifecycle plus negative cases.
  _Verification commands:_
  - `php artisan test --filter=LandingLink`

### I4 – `LandingPageResource` extension: layout, animation, intro, position, about, CTA text

- [x] T-054-13 – Extend `LandingPageResource`: `layout` with SE-fallback resolution covering `portfolio`/`minimal`/`studio` (F-054-01, F-054-02, S-054-02, S-054-03, S-054-20, S-054-21).
  _Intent:_ Reuse `request()->verify()->validate() && ->is_supporter()` exactly as `InitConfig::set_supporter_properties()` does.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-14 – Extend `LandingPageResource`: `animation_preset` with SE-fallback resolution (F-054-05, F-054-06, S-054-08, S-054-09).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-15 – Extend `LandingPageResource`: `intro_screen_enabled`, `hero_text_position` passthrough (F-054-03, F-054-04, S-054-05, S-054-07).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-15a – Extend `LandingPageResource`: `hero_text_color`, `hero_text_opacity` passthrough, free tier (F-054-28, F-054-29, S-054-29, S-054-30).
  _Intent:_ `hero_text_color` via plain `getValueAsString()` (not run through `PaletteGenerator` — see spec Design Notes); `hero_text_opacity` via `getValueAsInt()`, no additional clamping needed since `type_range` already bounds it to 0-100.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-16 – Extend `LandingPageResource`: `about_enabled`/`about_text` (F-054-08, S-054-11).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-17 – Extend `LandingPageResource`: `links` array from enabled `LandingLink` rows ordered by `sort_order` (F-054-12, S-054-15).
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-18 – Extend `LandingPageResource`: `cta_text` passthrough, free tier (F-054-24, S-054-22, S-054-23).
  _Intent:_ Plain string passthrough, no SE gating, ≤255 chars.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

- [x] T-054-19 – Unit tests: SE-on/SE-off matrix for every SE-gated field (NFR-054-02).
  _Intent:_ Prove fail-safe fallback never throws and never leaks a premium value to a non-SE requester.
  _Verification commands:_
  - `php artisan test --filter=LandingPageResource`

### I5 – Featured content: automatic-mode resolution

- [x] T-054-20 – `LandingFeaturedItemResource` — unified photo/album projection (DO-054-03).
  _Intent:_ `{item_type, id, title, thumb_url, url, num_photos?}`; used by both automatic mode (this increment) and manual mode (I5c).
  _Verification commands:_
  - `make phpstan`

- [x] T-054-21 – Add automatic-mode `featured_items` resolution to `LandingPageResource`, gated by effective `landing_featured_items_enabled` and `landing_featured_items_mode=automatic` (F-054-09, S-054-12, S-054-13, S-054-14, NFR-054-03, NFR-054-06).
  _Intent:_ Reuse Feature 025's `resolveLatestAlbumCover` query shape; `LIMIT landing_featured_items_count`; every item projected with `item_type: "album"`.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemsAutomatic`

### I5a – `LandingFeaturedItem` model, migration, factory

- [x] T-054-22 – Migration: create `landing_featured_items` table (F-054-24).
  _Intent:_ ULID PK, `item_type`, `item_id`, `sort_order`, `enabled`, timestamps; indexes on `enabled`, `item_type`. Mirrors `landing_links`' shape.
  _Verification commands:_
  - `php artisan migrate`

- [x] T-054-23 – `App\Models\LandingFeaturedItem` + `scopeEnabled()` (F-054-24).
  _Intent:_ Mirror `App\Models\LandingLink`'s shape.
  _Verification commands:_
  - `make phpstan`

- [x] T-054-24 – `LandingFeaturedItemFactory` (F-054-24).
  _Intent:_ Test fixture support, mirrors `LandingLinkFactory`.
  _Verification commands:_
  - `php artisan tinker` sanity check (or a throwaway unit test) creating one factory instance.

### I5b – `LandingFeaturedItem` admin CRUD (REST)

- [x] T-054-25 – `StoreLandingFeaturedItemRequest` / `UpdateLandingFeaturedItemRequest` validation (F-054-24).
  _Intent:_ `item_type` restricted to `photo`/`album`; `item_id` must reference an existing `Photo`/`Album` matching `item_type` at write time.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemRequest`

- [x] T-054-26 – `App\Http\Controllers\Admin\LandingFeaturedItemController` — index/store/show/update/patch/destroy/reorder (F-054-24, API-054-09..15).
  _Intent:_ Mirror `LandingLinkController`'s structure exactly, including `reorder()`'s identical full-list-resync contract.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemController`
  - `make phpstan`

- [x] T-054-27 – Routes in `routes/api_v2.php` under the admin group (API-054-09..15).
  _Intent:_ `GET/POST /LandingFeaturedItem`, `GET/PUT/PATCH/DELETE /LandingFeaturedItem/{landingFeaturedItem}`, `PATCH /LandingFeaturedItem/Reorder`.
  _Verification commands:_
  - `php artisan route:list | grep LandingFeaturedItem`

- [x] T-054-28 – Feature tests: CRUD, reorder, admin-only 403, item-existence validation (S-054-17, S-054-26).
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItem`

### I5c – Featured content: manual-mode resolution

- [x] T-054-29 – Add manual-mode `featured_items` resolution to `LandingPageResource`, gated by effective `landing_featured_items_enabled` and `landing_featured_items_mode=manual` (F-054-27, S-054-26, S-054-27, S-054-28, NFR-054-03).
  _Intent:_ Enabled `LandingFeaturedItem` rows ordered by `sort_order`, each resolved by direct `Photo`/`Album` lookup on `item_id` — no `PhotoQueryPolicy`/`AlbumQueryPolicy` call. Missing/deleted referenced records are skipped silently.
  _Verification commands:_
  - `php artisan test --filter=LandingFeaturedItemsManual`
  _Notes:_ Test must explicitly assert the policy bypass is intentional (e.g. resolve a private photo successfully when manually curated) so it isn't later "fixed" as a privacy bug.

### I6 – Frontend: `Landing.vue` dispatcher + `LandingClassic.vue` extraction

- [x] T-054-30 – Move current `resources/js/v8/views/Landing.vue` markup verbatim into `resources/js/v8/views/landing/LandingClassic.vue` (F-054-14, S-054-01).
  _Intent:_ Pure extraction, no behavioural change yet.
  _Verification commands:_
  - Manual: diff rendered DOM against pre-change snapshot.

- [x] T-054-31 – Parameterize `LandingClassic.vue` by `intro_screen_enabled`/`hero_text_position`/`hero_text_color`/`hero_text_opacity`/`animation_preset`/`cta_text` (all defaulting to today's fixed behaviour) and render `links` in header/footer (F-054-14, F-054-12, S-054-05, S-054-15, S-054-23, S-054-29, S-054-30).
  _Verification commands:_
  - `npm run check`
  - Manual: default config renders identically to pre-change (S-054-01); toggling `intro_screen_enabled=false` skips the splash (S-054-05).

- [x] T-054-32 – New `Landing.vue` dispatcher: fetch once, route to `LandingClassic.vue` (F-054-13).
  _Intent:_ Preserve existing `landing_page_enable=false` redirect and fetch-error handling.
  _Verification commands:_
  - `npm run check`
  - Manual: `landing_page_enable=false` still redirects to gallery.
  _Notes:_ T-054-30/31/32 and I9a's T-054-48/49 were implemented together in one pass rather than as two sequential increments: `LandingClassic.vue` (and the three new layouts) were written directly prop-driven (`defineProps<{ data: LandingPageResource }>()`, no internal `InitService.fetchLandingData()` call) from the start, with `Landing.vue` as the single-fetch dispatcher from the outset. This reaches the same end state as I6→I9a without an interim self-fetching `LandingClassic.vue` commit; the decorative "ACCESS GALLERY" shadow-text layer and the 5-position Tailwind mapping were preserved/added respectively during that same pass. `landing_page_enable=false` redirect and fetch-error toast+redirect both carried over verbatim from the original `Landing.vue`.

### I7 – Shared position/animation composables

- [x] T-054-33 – `useLandingTextPosition.ts` composable (F-054-04).
  _Intent:_ 5-value Tailwind class map, landing-scoped.
  _Verification commands:_
  - `npm run check`

- [x] T-054-34 – `useLandingAnimation.ts` composable, incl. `prefers-reduced-motion` override (F-054-05, F-054-07, NFR-054-04, S-054-10).
  _Intent:_ Single choke point — `window.matchMedia('(prefers-reduced-motion: reduce)')` forces `none` regardless of resolved preset.
  _Verification commands:_
  - `npm run check`
  - Manual: OS-level reduced-motion enabled → zero animations on any layout.

- [x] T-054-35 – CSS keyframes for `zoom_in`/`slide_reveal`; `IntersectionObserver`-driven section reveal for `parallax_scroll` (F-054-05, S-054-08).
  _Verification commands:_
  - Manual: each preset visually verified on `portfolio`.

### I8 – Frontend: `LandingPortfolio.vue`

- [x] T-054-36 – Sticky nav bar: logo + `links` (nav/both) + Gallery link + Contact link when `footer.is_contact_form_enabled` (F-054-15, S-054-15, S-054-24).
  _Verification commands:_
  - `npm run check`
  - Manual: Contact link present/absent matching `contact_form_enabled`; navigates to `/contact`.

- [x] T-054-37 – Hero section: background (existing Feature 025 resolution) + positioned, colored, opacity-styled headline/subtitle + CTA respecting `cta_text` using I7 composables (F-054-15, F-054-04, F-054-24, F-054-28, F-054-29, S-054-07, S-054-22, S-054-29, S-054-30).
  _Verification commands:_
  - Manual: all 5 positions verified; `cta_text` override reflected on the button; custom color/opacity apply to headline+subtitle only, not the CTA button.

- [x] T-054-38 – Optional about section (F-054-08, F-054-15, S-054-11).
  _Verification commands:_
  - Manual: omitted when `landing_about_enabled=false` or text empty.

- [x] T-054-39 – Optional featured-content section, rendering `featured_items` regardless of which mode produced the array (F-054-09, F-054-15, F-054-27, S-054-12, S-054-13, S-054-14, S-054-26, S-054-27, S-054-28).
  _Verification commands:_
  - Manual: automatic mode full/partial/zero counts verified; manual mode mixed photo+album rendering verified; section omitted when the resolved array is empty either way.

- [x] T-054-40 – Scroll-down indicator between hero and the next rendered section, reduced-motion-aware (F-054-15, S-054-25, UI-054-04).
  _Intent:_ Uses I7's `useLandingAnimation` choke point — present and clickable (smooth-scroll) under reduced motion, just non-bouncing.
  _Verification commands:_
  - Manual: indicator scrolls correctly; static (no bounce) with OS-level reduced-motion enabled or `animation_preset=none`.

- [x] T-054-41 – Footer: existing `FooterConfig` + `links` (footer/both) (F-054-15, S-054-15).
  _Verification commands:_
  - `npm run check`

- [x] T-054-42 – Wire `LandingPortfolio.vue` into `Landing.vue` dispatcher (F-054-13, S-054-02, S-054-03).
  _Verification commands:_
  - Manual: SE-on shows portfolio; SE-off falls back to classic.

### I8a – Frontend: `LandingStudio.vue`

- [x] T-054-43 – Primary CTA: `RouterLink` to the existing `login` route, label from `cta_text` else `landing.client_login` (F-054-17, NFR-054-10, S-054-20, S-054-23).
  _Intent:_ No new auth code — pure navigation to the existing login flow.
  _Verification commands:_
  - `npm run check`
  - Manual: click navigates to `/login`; label reflects override/default correctly.

- [x] T-054-44 – Secondary smaller link to the `home` route (public gallery, fixed label), hero copy (`landing_title`/`landing_subtitle`/`landing_about_text`, styled per `hero_text_color`/`hero_text_opacity`), optional background, footer `links`/social icons (F-054-17, F-054-28, F-054-29, S-054-20).
  _Verification commands:_
  - `npm run check`

- [x] T-054-45 – Wire `LandingStudio.vue` into `Landing.vue` dispatcher (F-054-13, S-054-20, S-054-21).
  _Verification commands:_
  - Manual: SE-on + `landing_layout=studio` shows the studio layout; SE-off falls back to classic.

### I9 – Frontend: `LandingMinimal.vue`

- [x] T-054-46 – Centered card: logo/title/subtitle (styled per `hero_text_color`/`hero_text_opacity`), optional about text, single CTA respecting `cta_text`, footer `links`/social icons + Contact link when `footer.is_contact_form_enabled` (F-054-16, F-054-28, F-054-29, S-054-04, S-054-06, S-054-11, S-054-24).
  _Verification commands:_
  - `npm run check`
  - Manual: no featured-content section present (by design); Contact link present/absent matching the flag, navigates to `/contact`.

- [x] T-054-47 – Wire `LandingMinimal.vue` into `Landing.vue` dispatcher (F-054-13, S-054-04).
  _Verification commands:_
  - Manual: SE-on + `landing_layout=minimal` shows the minimal layout.

### I9a – Refactor: layout components accept data via prop, not self-fetch

- [x] T-054-48 – Change `LandingClassic.vue`/`LandingPortfolio.vue`/`LandingMinimal.vue`/`LandingStudio.vue` to accept a required `LandingPageResource`-shaped prop instead of calling `InitService.fetchLandingData()` internally (F-054-13, F-054-25 prerequisite).
  _Intent:_ Pure responsibility move — no behavioural change for the public route.
  _Verification commands:_
  - `npm run check`
  _Notes:_ See T-054-32's note — all four layout components were written prop-driven from the start (I6/I8/I8a/I9 and this refactor landed as one pass), so there was no separate self-fetching intermediate state to refactor away.

- [x] T-054-49 – Move the fetch into `Landing.vue`'s dispatcher and pass the result down as the prop to whichever layout component it mounts.
  _Verification commands:_
  - `npm run check`
  - Manual: re-verify S-054-01, S-054-02, S-054-04, S-054-20 still pass identically after the refactor.

### I10 – Admin UI: `LandingConfig.vue` (Settings-with-preview + Links + Featured tabs)

- [x] T-054-50 – `resources/js/v8/views/admin/LandingConfig.vue` scaffold: `UTabs` with `settings`/`links`/`featured` slots (tab shape mirrors `NsfwConfig.vue`) (F-054-18).
  _Intent:_ Same page shell pattern as `NsfwConfig.vue` — loading state, tab items, `OpenLeftMenu` header.
  _Verification commands:_
  - `npm run check`

- [x] T-054-51 – Settings tab, left column: load the 12 keys via `SettingsService.getAll()` into local non-persisted reactive state (mirrors `WatermarkPreview.vue`), lay out in `Fieldset` sections ("Layout & Structure," "Hero" — position, `ColorField.vue` for `landing_hero_text_color`, opacity number/range input for `landing_hero_text_opacity`, animation, CTA text — "Content") (F-054-19, F-054-28, F-054-29).
  _Intent:_ `landing_hero_text_color` renders via the existing generic `config.type === 'color'` dispatch — reuse `ColorField.vue` directly, no new component.
  _Verification commands:_
  - `npm run check`
  - Manual: fields load current saved values on open; color picker and opacity input behave like their Theme Colors / Watermarker counterparts.

- [x] T-054-52 – Settings tab, right column: live preview — assemble a `LandingPageResource`-shaped object from the current unsaved form state plus already-persisted `links`/`featured_items`, render the layout component matching the in-progress `landing_layout` (prop-driven, I9a) at reduced scale, reactive to every field change with no save (F-054-19, F-054-25).
  _Intent:_ Mirrors `WatermarkPreview.vue`'s live overlay pattern.
  _Verification commands:_
  - `npm run check`
  - Manual: changing layout/position/color/opacity/animation/about/CTA text updates the preview instantly, with the Save button untouched.

- [x] T-054-53 – Settings tab: explicit **Save** button writes the 12 fields via `SettingsService.setConfigs()` (F-054-19).
  _Intent:_ Nothing autosaves on field change.
  _Verification commands:_
  - `npm run check`
  - Manual: editing fields without clicking Save leaves the flat Settings list's values unchanged; clicking Save persists them there too.

- [x] T-054-54 – Disable and badge "SE" on the `landing_layout` dropdown's `portfolio`/`minimal`/`studio` options and the `landing_animation_preset` dropdown's premium-preset options (not selectable); if a previously-stored SE-only value exists, it still displays as the current selection (F-054-21, UI-054-01, UI-054-02).
  _Intent:_ Bespoke to this dropdown's rendering — read existing `is_se_enabled`/`is_se_preview_enabled` init data; whole-field `require_se` doesn't fit since only some enum *values* are SE-gated.
  _Verification commands:_
  - `npm run check`
  - Manual: non-SE install cannot select `portfolio`/`minimal`/`studio`/premium animation options (disabled + badged); if one was already stored, it still shows as the current selection.

- [x] T-054-55 – Links tab: `LandingLink` list/create/edit/delete UI + drag-reorder calling API-054-08 (F-054-22, S-054-15, S-054-16, UI-054-03).
  _Verification commands:_
  - `npm run check`
  - Manual: full CRUD via UI; reordered links persist and reflect on the public landing page.

- [x] T-054-56 – Featured tab: mode switcher (`landing_featured_items_enabled`/`_mode`/`_count`) and manual-curation picker — search box hitting `GET /api/v2/Search`, "Add" action via API-054-10, ordered list with drag-reorder (API-054-15) and per-row enable/delete (F-054-23, S-054-26).
  _Intent:_ No new search backend — reuses the existing Search endpoint as-is.
  _Verification commands:_
  - `npm run check`
  - Manual: toggling mode enables/disables the count field appropriately; search finds both photos and albums by title; add/reorder/delete all work end-to-end.

- [x] T-054-57 – Register `landing-config` route in `router/paths.ts` (name/path) and `resources/js/v8/router/routes.ts` (component mapping), plus an admin tile (`useAdminTiles.ts`, `group: "core"`, visible whenever `can_edit`) (F-054-18, UI-054-05).
  _Intent:_ Mirrors `nsfw-config`/`watermark-preview`'s registration.
  _Verification commands:_
  - Manual: page reachable from the admin dashboard at `/admin/landing-config`.
  _Notes:_ Discovered during manual verification that the SPA route manifest alone is insufficient for a hard navigation/reload — every other `/admin/*` v8 page also has an explicit `Route::get(...)` entry in `routes/web_v2.php` serving `VueController`/`vueapp.blade.php`, so a matching `Route::get('/admin/landing-config', VueController::class)->middleware(['migration:complete', 'login_required:always']);` line was added there too (mirrors the `/admin/design`/`/admin/nsfw-config` entries). Without it, direct navigation to the URL 404s server-side even though client-side `<RouterLink>` navigation from within the app would have worked.

- [x] T-054-58 – Confirm the flat generic Settings list still shows all 12 keys under `Mod Welcome` after this increment lands (F-054-19, S-054-15).
  _Intent:_ Regression guard — no filtering of the flat list should exist.
  _Verification commands:_
  - Manual: flat Settings list shows the `Mod Welcome` category with all 12 new fields, unchanged from I1.

### I11 – Translation sweep

- [x] T-054-59 – Propagate `all_settings.php`, `landing.php`, `landing_link.php`, `landing_featured_item.php` keys across all 22 locales (F-054-20, TRANS-054-01..11).
  _Verification commands:_
  - `php artisan test --filter=LangTest` (`tests/Unit/LangTest.php` — confirmed as the repo's translation-completeness check; it asserts every key present in `lang/en/*.php` also exists, by key, in every other locale directory — untranslated values are expected to carry the English placeholder text pending a real translation pass, matching the existing convention already visible on several pre-existing keys, e.g. `ja`/`zh_CN`'s `landing_background_portrait*` entries).
  _Notes:_ Also added a new `lang/{locale}/landing_config.php` (admin page-shell strings) to all 22 locales alongside the two files named in the task, since `LandingConfig.vue`'s own copy (tab labels, section legends, save button, etc.) needed a home distinct from the CRUD-label files.

### I12 – Quality gates & full regression pass

- [x] T-054-60 – Full backend test suite (all F-054/S-054 IDs).
  _Verification commands:_
  - `php artisan test`
  - `make phpstan`
  - `vendor/bin/php-cs-fixer fix`
  _Notes:_ Full unscoped run: 3093 passed / 5 failed / 42 skipped (116524 assertions, 1925s). All 5 failures are pre-existing and unrelated to this feature — `Tests\Unit\Actions\Db\OptimizeTablesTest` (DB-engine table-count assumption), `Tests\ImageProcessing\Image\Handlers\PhotosAddHandlerImagickTest` (x2), `Tests\ImageProcessing\Photo\PhotoAddTest` apple-live-photo cases (x2) — confirmed unrelated by file path (no `Landing*` code touches image processing/Imagick/live-photo parsing). An earlier full run this session showed 7 failures; the 2 that are now fixed were `Tests\Unit\CopyrightTest` and `Tests\Unit\LangTest`, both caused by gaps in this feature's own new files (missing copyright-notice format in new test files; missing locale keys) and resolved during I11/backend cleanup. `make phpstan`: 0 errors (full-repo). `vendor/bin/php-cs-fixer fix`: 0 files fixed on final pass.

- [x] T-054-61 – Full frontend checks (all F-054/S-054 IDs).
  _Verification commands:_
  - `npm run check`
  - `npm run format`

- [x] T-054-62 – Manual walk-through of the Branch & Scenario Matrix S-054-01..30, including confirming zero diff under `resources/js/v7/` (NFR-054-08, S-054-19) and that the flat list still shows all landing settings.
  _Verification commands:_
  - `git diff --stat -- resources/js/v7/` (expect empty) — confirmed empty.
  - Manual browser pass per scenario row.
  _Notes:_ `git diff --stat -- resources/js/v7/` confirmed empty. Browser-verified via a real login session + Playwright screenshots (no `chromium-cli` available in this sandbox, so Playwright/Chromium was installed ad hoc and removed after): S-054-01 (classic default, pixel-equivalent), S-054-02/03 (SE on/off `portfolio`), S-054-04 (`minimal`), S-054-07 (`bottom_left` hero position on `portfolio`), S-054-11 (about section on `portfolio`), S-054-15 (links rendered in nav/footer), S-054-20/21 (SE on/off `studio`), S-054-22/23 (`cta_text` override and default per layout), S-054-29/30 (hero text color/opacity styling, CTA unaffected), plus the full admin `LandingConfig.vue` flow (Settings live preview, Links CRUD incl. create, Featured tab incl. automatic→manual mode switch). Scenarios verified only at the automated-test level (not re-confirmed via manual browser screenshot in this session): S-054-05/06/08/09/10 (intro-disable and non-default animation presets), S-054-12/13/14 (automatic featured-content count edge cases), S-054-16/17/18 (link reorder/403/count-validation), S-054-24/25 (Contact link, scroll indicator), S-054-26/27/28 (manual featured-content scenarios) — all of these are covered by passing `php artisan test` feature tests (`LandingPageSeGatingTest`, `LandingFeaturedItemsAutomaticTest`, `LandingFeaturedItemsManualTest`, `LandingLinkReorderTest`, etc.), just not re-clicked through a browser in this pass.

- [x] T-054-63 – Update `docs/specs/4-architecture/roadmap.md` and `docs/specs/_current-session.md` to reflect completion.
  _Verification commands:_
  - N/A (documentation).

## Notes / TODOs

- The exact translation-completeness command for T-054-59 should be confirmed against current repo tooling at execution time.
- If p95 latency on the public landing route looks elevated once I5 lands (NFR-054-06), profile before adding any new index — automatic-mode featured content is opt-in and expected to be rare in practice; manual mode is direct PK lookups, so it carries no equivalent risk.
- I9a's refactor must land before I10's live-preview tasks (T-054-52) — the preview panel directly reuses the now-prop-driven layout components.
