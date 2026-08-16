# Feature Plan 054 – Configurable Landing Page

_Linked specification:_ `docs/specs/4-architecture/features/054-configurable-landing-page/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-11

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections have been updated.

## Vision & Success Criteria

An admin can pick a landing-page layout (`classic`/`portfolio`/`minimal`/`studio`), reposition the hero text, pick an animation preset, toggle content blocks (intro splash, about text, featured content — automatic or manually curated), and manage an arbitrary list of extra links — all through the existing flat Settings list and a dedicated admin page with a live preview, with zero code changes required per install. Success signals:
- `classic` with all new configs at their defaults renders **no visual change** from today (NFR-054-01).
- A non-SE install configured with `portfolio`/`minimal`/`studio`/premium animations silently and safely falls back to the free defaults (NFR-054-02).
- Private/unpublished content never appears in automatic-mode featured content; manual-mode's admin-trusted exception is deliberate and tested as such, not accidentally broader (NFR-054-03).
- `php artisan test`, `make phpstan`, `npm run check`, `npm run format` all clean at completion.

## Scope Alignment

- **In scope:** 12 new scalar configs + `ConfigIntegrity` wiring, filed under the existing `Mod Welcome` category; `LandingLink` model/migration/CRUD; `LandingFeaturedItem` model/migration/CRUD; `LandingPageResource` extension (layout/animation SE-fallback, about text, automatic + manual featured content, links, CTA text); v8-only frontend (`Landing.vue` dispatcher + prop-driven `LandingClassic.vue`/`LandingPortfolio.vue`/`LandingMinimal.vue`/`LandingStudio.vue` + shared position/animation composables); `LandingConfig.vue` admin page (Settings tab with live preview, Links tab, Featured tab) coexisting with the flat generic Settings list; translations (English required, full 22-locale sweep before completion).
- **Out of scope:** Any change to `resources/js/v7/**`; background resolution logic (Feature 025, untouched); a reorderable section builder; video backgrounds; new billing/licensing plumbing beyond the existing `request()->verify()` check; per-locale admin-authored text; a landing-specific custom CSS/JS field; an icon field on `LandingLink`; a gallery-stats display.

## Dependencies & Interfaces

- `App\Http\Resources\GalleryConfigs\LandingPageResource` (Feature 025) — extended, not replaced; existing background-resolution methods reused unchanged.
- `App\Policies\PhotoQueryPolicy` / `App\Policies\AlbumQueryPolicy` — reused for automatic-mode featured-content queries (`applySearchabilityFilter`/`applyVisibilityFilter($query, null)`, same as Feature 025). **Not** used for manual-mode resolution (direct lookup, admin-trusted, mirrors Feature 025's `photo_id` background mode).
- `request()->verify()->is_supporter()` / `->validate()` — the SE check already used by `InitConfig::set_supporter_properties()` — reused for all SE-fallback resolutions.
- `App\Http\Middleware\ConfigIntegrity` — whitelist to extend (existing pattern for `album_header_size` etc.).
- `App\Models\Webhook` / `App\Http\Controllers\Admin\WebhookController` — structural template for `LandingLink`'s and `LandingFeaturedItem`'s model/controller shape (ULID PK, CRUD, admin-only).
- `resources/js/v8/components/gallery/albumModule/AlbumHeaderPanel.vue`'s `POSITION_CLASSES` — reference implementation for the 5-position Tailwind class mapping (landing gets its own small composable, not a direct import).
- `resources/js/v8/views/admin/WatermarkPreview.vue` (Watermarker module) — the structural template for `LandingConfig.vue`'s Settings tab: two-column settings-form + live-reactive-preview, local-state-then-explicit-Save flow, settings that stay visible in the flat generic Settings list.
- `resources/js/v8/views/admin/NsfwConfig.vue` (Feature 045) — the structural template for `LandingConfig.vue`'s overall `UTabs` shape and the Links/Featured tabs' CRUD UI.
- `resources/js/router/paths.ts` / `resources/js/v8/router/routes.ts` / `resources/js/v8/composables/useAdminTiles.ts` — where `LandingConfig.vue` gets registered (route name/path, component mapping, admin tile).
- `router/paths.ts`'s existing `login` route / `resources/js/v8/components/forms/auth/LoginForm.vue` — reused as-is for `studio`'s "Client Login" CTA; no new auth code.
- `App\Http\Resources\GalleryConfigs\FooterConfig`'s existing `is_contact_form_enabled` (Feature 022) — reused as-is to gate the Contact link on `portfolio`/`minimal`, navigating to the existing `/contact` route.
- `GET /api/v2/Search` (Feature 027/028) / `resources/js/services/search-service.ts` — reused as-is by the Featured tab's picker; already returns private content to admin sessions via the existing `may_administrate` policy bypass.
- `App\View\Components\Meta` / `SettingsController::setCSS()`/`setJS()` — the existing global custom CSS/JS mechanism, untouched by this feature.

## Assumptions & Risks

- **Assumptions:** `request()->verify()` behaves identically wherever `LandingPageResource` is constructed (public, unauthenticated route) — same assumption Feature 039's `InitConfig` already makes. The shared `BoolField`/`SelectField` form components (used by `AllSettings.vue`/`NsfwConfig.vue`/`WatermarkPreview.vue`) cover all 12 new config shapes without new widget types. Refactoring the 4 layout components to be prop-driven is a clean, behaviour-preserving extraction.
- **Risks / Mitigations:**
  - *Risk:* Extracting today's `Landing.vue` into `LandingClassic.vue` accidentally changes markup/CSS. *Mitigation:* pure move/parameterize step with a manual diff-against-original check before any new layout work starts.
  - *Risk:* `parallax_scroll` interacts badly with `prefers-reduced-motion` if not gated correctly. *Mitigation:* the reduced-motion check lives in the shared `useLandingAnimation` composable itself (single choke point), not per-layout.
  - *Risk:* Automatic-mode featured-content queries add load to the highest-traffic public route. *Mitigation:* opt-in (default `false`), reuses indexed columns (NFR-054-06). Manual mode is N direct PK lookups, not a filtered query.
  - *Risk:* No JS test runner exists in this repo — layout components get manual/browser verification only. *Mitigation:* the Branch & Scenario Matrix (S-054-01..30) is the checklist; each row is assigned to an increment below.
  - *Risk:* Manual-mode featured-item resolution deliberately skips the visibility policy check — if not clearly tested as intentional, a future contributor could "fix" it as a privacy bug. *Mitigation:* NFR-054-03 documents this explicitly; the resolution test asserts the bypass is intentional (e.g. successfully resolves a private photo when manually curated).
  - *Risk:* The prop-driven refactor of the 4 layout components could subtly change public-route behaviour if any component has fetch-timing-dependent logic. *Mitigation:* the refactor's exit criterion re-verifies one scenario per layout (S-054-01/02/04/20), not just a compile check.

## Implementation Drift Gate

Before starting frontend increments, re-read `resources/js/v8/views/Landing.vue`, `AlbumHeaderPanel.vue`, `LandingPageResource.php`, `resources/js/v8/views/admin/NsfwConfig.vue`, and `resources/js/v8/views/admin/WatermarkPreview.vue` fresh (they may have changed since this plan was written) and confirm: (a) `LandingPageResource`'s constructor shape and Feature 025's background-resolution methods are still as described in "Dependencies & Interfaces," (b) `NsfwConfig.vue`'s tabbed structure and `WatermarkPreview.vue`'s local-state/explicit-Save/live-preview pattern are still current, (c) `WatermarkPreview.vue`'s settings category is still visible in the flat generic Settings list (the basis for `LandingConfig.vue` coexisting rather than filtering), (d) `Webhooks.vue`/`WebhookController` are still the best structural template for `LandingLink`'s and `LandingFeaturedItem`'s CRUD, (e) `GET /api/v2/Search`'s response shape is still suitable for the Featured tab's picker. Record findings and any deltas at the top of the Analysis Gate section below before writing code.

## Increment Map

1. **I1 – Backend foundation: enums + scalar configs**
   - _Goal:_ Land the 12 new scalar configs and their supporting enums, with zero frontend/behavioural change yet.
   - _Preconditions:_ None — first increment.
   - _Steps:_ Create `App\Enum\LandingLayoutType` (4 values incl. `studio`), `LandingTextPosition`, `LandingAnimationPreset`, `LandingLinkPlacement`, `LandingFeaturedItemsMode`, `LandingFeaturedItemType`. Write migration MIG-054-01 (12 config rows incl. `landing_hero_text_color` (`type_range: 'color'`, reuses the existing Theme Colors config shape) and `landing_hero_text_opacity` (int, range 0-100), `type`/`type_range` metadata, defaults per FR-054-26, filed under `Mod Welcome`). Add all 12 keys to `ConfigIntegrity`'s whitelist. Add `all_settings.details.*` English translation keys (TRANS-054-01..07, TRANS-054-12) and the standalone `landing.client_login`/`landing.view_public_gallery`/`landing.contact` keys (TRANS-054-11).
   - _Commands:_ `php artisan migrate`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
   - _Exit:_ `php artisan config:show` (or equivalent) lists all 12 keys with correct defaults under `Mod Welcome`; the flat generic Settings UI renders `landing_hero_text_color` via the existing `ColorField.vue` (generic `config.type === 'color'` dispatch, no new component) and `landing_hero_text_opacity` as a bounded number input.

2. **I2 – `LandingLink` model, migration, factory**
   - _Goal:_ Data layer for extra links, no API/UI yet.
   - _Preconditions:_ I1 (enum `LandingLinkPlacement` exists).
   - _Steps:_ Migration MIG-054-02 (`landing_links` table). `App\Models\LandingLink` (ULID PK, fillable/casts mirroring `Webhook.php`, `scopeEnabled()`). `LandingLinkFactory`.
   - _Commands:_ `php artisan migrate`, `make phpstan`.
   - _Exit:_ Model + factory create/read/update/delete correctly in a quick unit test.

3. **I3 – `LandingLink` admin CRUD (REST)**
   - _Goal:_ API-054-02..08 fully working and admin-gated.
   - _Preconditions:_ I2.
   - _Steps:_ `StoreLandingLinkRequest`/`UpdateLandingLinkRequest` (validation per FR-054-10). `LandingLinkResource` (public-safe projection, reused for both admin and public embed). `App\Http\Controllers\Admin\LandingLinkController` — index/store/show/update/patch/destroy mirror `WebhookController`; `reorder()` implements FR-054-11's full-list-resync contract (`{ ids: string[] }`, reject on set mismatch, transactional). Routes in `routes/api_v2.php` under the existing admin group.
   - _Commands:_ `php artisan test --filter=LandingLink`, `make phpstan`.
   - _Exit:_ Feature tests cover S-054-15..18 (CRUD, reorder, admin-only 403, count validation).

4. **I4 – `LandingPageResource` extension: layout, animation, intro, position, about, CTA text**
   - _Goal:_ Public payload carries the new fields with correct SE-fallback resolution.
   - _Preconditions:_ I1.
   - _Steps:_ Extend `LandingPageResource` constructor with `layout` (FR-054-01/02), `intro_screen_enabled` (FR-054-03), `hero_text_position` (FR-054-04), `hero_text_color` (FR-054-28, free tier, plain `getValueAsString()` passthrough — not run through `PaletteGenerator`), `hero_text_opacity` (FR-054-29, free tier, `getValueAsInt()`), `animation_preset` (FR-054-05/06), `about_enabled`/`about_text` (FR-054-08), `cta_text` (FR-054-24, free tier, plain passthrough). Reuse the exact `request()->verify()->validate() && ->is_supporter()` check already used by `InitConfig`. Unit tests for SE-on/SE-off × each SE-gated field.
   - _Commands:_ `php artisan test --filter=LandingPageResource`, `make phpstan`.
   - _Exit:_ S-054-01..09, S-054-11, S-054-20..23 covered by unit/feature tests.

5. **I5 – Featured content: automatic-mode resolution**
   - _Goal:_ `featured_items` array in automatic mode (FR-054-09), reusing Feature 025's `resolveLatestAlbumCover` query shape.
   - _Preconditions:_ I4.
   - _Steps:_ Add `LandingFeaturedItemResource` (unified photo/album projection, DO-054-03). Query public albums ordered `published_at DESC, created_at DESC, id DESC`, `LIMIT landing_featured_items_count`, project `item_type: "album"`, `id`/`title`/cover thumb URL/`num_photos`. Gate behind effective `landing_featured_items_enabled` and `landing_featured_items_mode=automatic`.
   - _Commands:_ `php artisan test --filter=LandingFeaturedItemsAutomatic`.
   - _Exit:_ S-054-12..14 covered.

5a. **I5a – `LandingFeaturedItem` model, migration, factory**
   - _Goal:_ Data layer for manual featured-content curation, no API/UI yet.
   - _Preconditions:_ I1 (enum `LandingFeaturedItemType` exists).
   - _Steps:_ Migration MIG-054-03 (`landing_featured_items` table). `App\Models\LandingFeaturedItem` (ULID PK, mirrors `LandingLink.php`, `scopeEnabled()`). `LandingFeaturedItemFactory`.
   - _Commands:_ `php artisan migrate`, `make phpstan`.
   - _Exit:_ Model + factory create/read/update/delete correctly in a quick unit test.

5b. **I5b – `LandingFeaturedItem` admin CRUD (REST)**
   - _Goal:_ API-054-09..15 fully working and admin-gated.
   - _Preconditions:_ I5a.
   - _Steps:_ `StoreLandingFeaturedItemRequest`/`UpdateLandingFeaturedItemRequest` (validates `item_id` references an existing `Photo`/`Album` matching `item_type`). `App\Http\Controllers\Admin\LandingFeaturedItemController` — index/store/show/update/patch/destroy mirror `LandingLinkController`; `reorder()` reuses the identical full-list-resync contract as I3. Routes in `routes/api_v2.php`.
   - _Commands:_ `php artisan test --filter=LandingFeaturedItem`, `make phpstan`.
   - _Exit:_ Feature tests cover S-054-17 (admin-only 403), S-054-26 (CRUD, mixed-type ordering), item-existence validation.

5c. **I5c – Featured content: manual-mode resolution**
   - _Goal:_ `featured_items` array in manual mode (FR-054-27), admin-trusted, no policy check.
   - _Preconditions:_ I5a, I4 (SE-fallback helper).
   - _Steps:_ When effective `landing_featured_items_mode=manual`, resolve enabled `LandingFeaturedItem` rows ordered by `sort_order`, looking up each `item_id` directly against `Photo`/`Album` — no `PhotoQueryPolicy`/`AlbumQueryPolicy` call. Skip silently if the referenced record no longer exists. Project through the same `LandingFeaturedItemResource` as I5.
   - _Commands:_ `php artisan test --filter=LandingFeaturedItemsManual`.
   - _Exit:_ S-054-26..28 covered; NFR-054-03's manual-mode exception explicitly tested (not just incidentally true).

6. **I6 – Frontend: `Landing.vue` dispatcher + `LandingClassic.vue` extraction**
   - _Goal:_ Zero-regression refactor — today's page keeps working exactly as-is, now behind the dispatcher.
   - _Preconditions:_ I1, I4.
   - _Steps:_ Move current `resources/js/v8/views/Landing.vue` markup verbatim into new `resources/js/v8/views/landing/LandingClassic.vue`; parameterize by `intro_screen_enabled`/`hero_text_position`/`hero_text_color`/`hero_text_opacity`/`animation_preset`/`cta_text` (all defaulting to today's fixed values) and append `links`. New `Landing.vue` becomes the fetch + dispatcher (FR-054-13), routing to `LandingClassic.vue` only for now (other layouts added in I8/I8a/I9).
   - _Commands:_ Manual diff of rendered DOM/CSS against pre-change output; `npm run check`.
   - _Exit:_ S-054-01 verified (pixel-identical default output); S-054-05, S-054-15, S-054-23 (classic) verified manually.

7. **I7 – Shared position/animation composables**
   - _Goal:_ Single choke point for the 5-position mapping and the 5 animation presets, including reduced-motion handling.
   - _Preconditions:_ I6.
   - _Steps:_ `resources/js/v8/composables/useLandingTextPosition.ts` (Tailwind class map, landing-scoped). `resources/js/v8/composables/useLandingAnimation.ts` (returns CSS classes/keyframe names per preset; checks `window.matchMedia('(prefers-reduced-motion: reduce)')` and forces `none` when set). CSS keyframes for `zoom_in`/`slide_reveal`; `parallax_scroll` uses `IntersectionObserver` to toggle in-view classes.
   - _Commands:_ `npm run check`, manual OS-level reduced-motion toggle test.
   - _Exit:_ S-054-08, S-054-10 verified manually.

8. **I8 – Frontend: `LandingPortfolio.vue`**
   - _Goal:_ New scrollable, multi-section layout (FR-054-15).
   - _Preconditions:_ I5, I5c, I7.
   - _Steps:_ Sticky nav (logo + `links` + Gallery + Contact link when `footer.is_contact_form_enabled`, navigating to `/contact`), hero (background + positioned, colored, opacity-styled text + CTA respecting `cta_text`, using I7 composables), optional about section, optional featured-content section (renders `featured_items` regardless of automatic/manual mode), scroll-down indicator between hero and the next section (reduced-motion-aware), footer. Each section conditionally omitted per its enable flag. Wire into `Landing.vue` dispatcher.
   - _Commands:_ `npm run check`, manual browser walk-through.
   - _Exit:_ S-054-02, S-054-07, S-054-11 (portfolio), S-054-12..14, S-054-22, S-054-24..28 (rendering side) verified manually.

8a. **I8a – Frontend: `LandingStudio.vue`**
   - _Goal:_ New client-login-first layout (FR-054-17).
   - _Preconditions:_ I4 (studio SE-fallback resolved server-side), I7.
   - _Steps:_ Primary CTA as a `RouterLink` to the existing `login` route, label from `cta_text` else `landing.client_login` translation. Secondary smaller link to the `home` route (public gallery), fixed label. Hero copy reuses `landing_title`/`landing_subtitle`/`landing_about_text`, styled per `hero_text_color`/`hero_text_opacity`; optional background per existing Feature 025 resolution. Footer `links`/social icons.
   - _Commands:_ `npm run check`, manual browser walk-through.
   - _Exit:_ S-054-20, S-054-23 (studio branch) verified manually.

9. **I9 – Frontend: `LandingMinimal.vue`**
   - _Goal:_ New centered-card layout (FR-054-16).
   - _Preconditions:_ I7.
   - _Steps:_ Centered logo/title/subtitle (styled per `hero_text_color`/`hero_text_opacity`), optional about text, single CTA respecting `cta_text`, footer `links`/social icons + Contact link when `footer.is_contact_form_enabled`. No featured-content section. Wire into dispatcher.
   - _Commands:_ `npm run check`, manual browser walk-through.
   - _Exit:_ S-054-04, S-054-06 (minimal branch), S-054-11 (minimal), S-054-24 (minimal branch) verified manually.

9a. **I9a – Refactor: layout components accept data via prop, not self-fetch**
   - _Goal:_ Prerequisite for the admin preview panel (I10) — the 4 layout components become pure presentational components.
   - _Preconditions:_ I6, I8, I8a, I9 (all 4 layout components exist and currently self-fetch).
   - _Steps:_ Change each component's data source from an internal `InitService.fetchLandingData()` call to a required prop shaped like `LandingPageResource`. Move the fetch up into `Landing.vue`'s dispatcher (already fetches once — now also passes the result down as the prop). No behavioural change for the public route.
   - _Commands:_ `npm run check`, manual regression pass on all 4 layouts (re-verify S-054-01, S-054-02, S-054-04, S-054-20).
   - _Exit:_ All 4 layout components compile and render identically, now driven entirely by props.

10. **I10 – Admin UI: `LandingConfig.vue` (Settings-with-preview + Links + Featured tabs)**
    - _Goal:_ FR-054-18..25.
    - _Preconditions:_ I3 (Links tab), I5a/I5b (Featured tab's manual CRUD), I5c (Featured tab's mode concept), I9a (Settings tab's preview needs prop-driven layout components).
    - _Steps:_
      1. New `resources/js/v8/views/admin/LandingConfig.vue`, `UTabs` with `settings`/`links`/`featured` slots (tab shape mirrors `NsfwConfig.vue`).
      2. **Settings tab, left column (form):** load the 12 keys via `SettingsService.getAll()` into local, non-persisted reactive state — same pattern as `WatermarkPreview.vue`. Lay out in `Fieldset` sections ("Layout & Structure," "Hero" — position, `landing_hero_text_color` (reuses `ColorField.vue` via the existing generic `config.type === 'color'` dispatch, no new component), `landing_hero_text_opacity`, animation preset, CTA text — "Content"). A **Save** button writes via `SettingsService.setConfigs()` — nothing autosaves on field change.
      3. **Settings tab, right column (live preview):** assemble a `LandingPageResource`-shaped object from the current unsaved form state plus the already-persisted `links`/`featured_items` (fetched once — those are edited on their own tabs, not part of the draft). Render the layout component matching the in-progress `landing_layout` (prop-driven, I9a) inside a scaled-down frame; re-render reactively on every field change, no Save required.
      4. Disable and badge "SE" on the `landing_layout` dropdown's `portfolio`/`minimal`/`studio` options and the `landing_animation_preset` dropdown's premium-preset options when the install isn't SE (read from existing `is_se_enabled`/`is_se_preview_enabled` init data). Bespoke to this dropdown's rendering, since whole-field `require_se` doesn't fit a field where only some enum *values* are gated. A previously-stored SE-only value still displays as the current selection.
      5. **Links tab:** the `LandingLink` list/create/edit/delete UI + drag-reorder calling API-054-08 (immediate-save CRUD).
      6. **Featured tab:** mode switcher (`landing_featured_items_enabled`/`landing_featured_items_mode`/`landing_featured_items_count`), and the `LandingFeaturedItem` manual-curation UI: a search box hitting `GET /api/v2/Search`, an "Add" action via API-054-10, an ordered list with drag-reorder (API-054-15) and per-row enable/delete (also immediate-save).
      7. Register `landing-config` in `router/paths.ts` (name/path) and `resources/js/v8/router/routes.ts` (component mapping), plus an admin tile in `useAdminTiles.ts` (`group: "core"`, visible whenever `can_edit`), mirroring `nsfw-config`/`watermark-preview`'s registration.
    - _Commands:_ `npm run check`, manual browser walk-through (incl. verifying the flat Settings list still shows all 12 keys).
    - _Exit:_ S-054-15..18, S-054-26 fully verified end-to-end (UI, not just API); UI-054-01..06 verified manually; preview panel confirmed reactive to every field with zero saves.

11. **I11 – Translation sweep**
    - _Goal:_ All new keys present across all 22 supported locales.
    - _Preconditions:_ I1-I10 (keys stable).
    - _Steps:_ Extend `lang/*/all_settings.php`, `lang/*/landing.php`, new `lang/*/landing_link.php`, new `lang/*/landing_featured_item.php` across all locales (English-authored source, mechanical propagation per existing repo translation workflow).
    - _Commands:_ Existing translation-completeness check/script if one exists (verify during increment).
    - _Exit:_ No missing-translation warnings for any new key in any locale.

12. **I12 – Quality gates & full regression pass**
    - _Goal:_ Feature-complete sign-off.
    - _Preconditions:_ I1-I11.
    - _Steps:_ Full `php artisan test` run; `make phpstan`; `vendor/bin/php-cs-fixer fix`; `npm run check`; `npm run format`; manual walk-through of the full Branch & Scenario Matrix (S-054-01..30), including confirming v7 is untouched (S-054-19) and the flat list still shows all landing settings.
    - _Commands:_ `php artisan test`, `make phpstan`, `npm run check`, `npm run format`.
    - _Exit:_ All quality gates green; roadmap.md moved to reflect completion status.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|----------------------------|-------|
| S-054-01 | I6 | Pixel-identical default output — the core regression guardrail. |
| S-054-02 | I8 | Portfolio layout, SE on. |
| S-054-03 | I4 | SE-off fallback to classic. |
| S-054-04 | I9 | Minimal layout, SE on. |
| S-054-05 | I6 | Intro toggle, classic. |
| S-054-06 | I8, I9 | Intro toggle, portfolio + minimal no-op. |
| S-054-07 | I8 | Hero text position, portfolio. |
| S-054-08 | I7, I8 | Parallax animation, SE on. |
| S-054-09 | I4 | Animation SE-off fallback. |
| S-054-10 | I7 | Reduced-motion override. |
| S-054-11 | I8, I9 | About block, portfolio + minimal; absent on classic. |
| S-054-12 | I5, I8 | Featured content, automatic mode, full count. |
| S-054-13 | I5, I8 | Featured content, automatic mode, partial count. |
| S-054-14 | I5, I8 | Featured content, automatic mode, zero available. |
| S-054-15 | I3, I10 | Links CRUD + placement filtering, API and UI. |
| S-054-16 | I3, I10 | Links reorder, API and UI. |
| S-054-17 | I3, I5b | Admin-only 403 (Links and Featured Items). |
| S-054-18 | I3 | Count range validation. |
| S-054-19 | I12 | v7 untouched confirmation. |
| S-054-20 | I8a | Studio layout, SE on. |
| S-054-21 | I4 | Studio SE-off fallback to classic. |
| S-054-22 | I8 | CTA text override, portfolio. |
| S-054-23 | I4, I8a | CTA text default per layout. |
| S-054-24 | I8, I9 | Contact-form link surfacing, portfolio + minimal. |
| S-054-25 | I8 | Scroll-down indicator + reduced-motion behaviour. |
| S-054-26 | I5b, I5c, I8, I10 | Manual mode: mixed photo/album curation, CRUD, rendering. |
| S-054-27 | I5c | Manual mode: deleted referenced item, graceful skip. |
| S-054-28 | I5c, I8 | Manual mode: zero enabled items, section omitted. |

## Analysis Gate

Not yet run. To be recorded here (date, reviewer, findings) once the Implementation Drift Gate re-read (above) is performed at the start of implementation.

## Exit Criteria

- All 30 Branch & Scenario Matrix rows verified (automated where a test exists, manual/browser otherwise, per Test Strategy).
- `php artisan test`, `make phpstan`, `npm run check`, `npm run format` all clean.
- `resources/js/v7/**` has zero diffs (NFR-054-08 / S-054-19).
- All 12 new scalar configs render correctly both in `LandingConfig.vue`'s Settings tab (with working live preview) and in the flat generic Settings list.
- `LandingClassic.vue`/`LandingPortfolio.vue`/`LandingMinimal.vue`/`LandingStudio.vue` are fully prop-driven with no internal fetching.
- Translation sweep complete across all 22 locales.
- `docs/specs/4-architecture/roadmap.md` and `docs/specs/_current-session.md` updated to reflect completion.

## Follow-ups / Backlog

- Modular/reorderable section builder — revisit only if 4 named layouts prove insufficient in practice.
- Dedup `POSITION_CLASSES`-style mapping between `AlbumHeaderPanel.vue` and the landing composable into one shared utility.
- Mosaic/grid-first layout, "coming soon" layout, split-screen editorial layout, background video support.
- Second image slot for the About section; testimonials/client-logos CRUD block; dedicated hero tagline field.
- A curated icon picker, if an icon field is reintroduced to `LandingLink`.
- Live WYSIWYG preview in the flat generic Settings list itself (the live preview lives only on `LandingConfig.vue`).
