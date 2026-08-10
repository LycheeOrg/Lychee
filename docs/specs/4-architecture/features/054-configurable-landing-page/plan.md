# Feature Plan 054 – Configurable Landing Page

_Linked specification:_ `docs/specs/4-architecture/features/054-configurable-landing-page/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-10 (rev 4 — dropped `landing_custom_css` (I7's step removed); I11 rebuilt around `LandingConfig.vue` absorbing the *entire* `Mod Welcome` category (3 tabs: Settings/Links/Featured) and filtering that category from the flat list; I6 split into I6/I6a/I6b/I6c for automatic-mode resolution + `LandingFeaturedItem` model/CRUD/manual-mode resolution; rev 3 — I11 rebuilt around a dedicated `LandingConfig.vue` page mirroring Feature 045's `NsfwConfig.vue`; rev 2 — added I9a for the `studio` layout; extended I1/I4/I7/I9/I10/I11 for CTA text, contact-form surfacing, scroll indicator)

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

An admin can pick a landing-page layout (`classic`/`portfolio`/`minimal`/`studio`), reposition the hero text, pick an animation preset, toggle content blocks (intro splash, about text, stats, featured content — automatic or manually curated), and manage an arbitrary list of extra links — all through one dedicated admin page, with zero code changes required per install. Success signals:
- Every existing install sees **no visual change** after upgrading (NFR-054-01) — verified by re-running any existing landing-page test/manual check against the `classic` default.
- A non-SE install that configures `portfolio`/`minimal`/`studio`/premium animations silently and safely falls back to the free defaults (NFR-054-02) — verified by a unit test with SE mocked off.
- Private/unpublished content never appears in stats or automatic-mode featured content (NFR-054-03); manual-mode featured content's admin-trusted exception is deliberate and tested as such, not accidentally broader.
- `php artisan test`, `make phpstan`, `npm run check`, `npm run format` all clean at completion.

## Scope Alignment

- **In scope:** 11 new scalar configs + `ConfigIntegrity` wiring, all filed under the existing `Mod Welcome` category; `LandingLink` model/migration/CRUD; `LandingFeaturedItem` model/migration/CRUD; `LandingPageResource` extension (layout/animation SE-fallback across `portfolio`/`minimal`/`studio`, about text, stats, automatic + manual featured content, links, CTA text); v8-only frontend (`Landing.vue` dispatcher + `LandingClassic.vue`/`LandingPortfolio.vue`/`LandingMinimal.vue`/`LandingStudio.vue` + shared position/animation composables); one dedicated 3-tab admin page (`LandingConfig.vue`) replacing the flat list's `Mod Welcome` category entirely; translations (English required, full 22-locale sweep before completion).
- **Out of scope:** Any change to `resources/js/v7/**`; background resolution logic (Feature 025, untouched); a reorderable section builder; video backgrounds; new billing/licensing plumbing beyond the existing `request()->verify()` check; per-locale admin-authored text; a landing-specific custom CSS/JS field (the existing global mechanism already covers this, untouched by this feature); a curated icon picker for `LandingLink.icon` (free-text only).

## Dependencies & Interfaces

- `App\Http\Resources\GalleryConfigs\LandingPageResource` (Feature 025) — extended, not replaced; its existing background-resolution methods are reused unchanged.
- `App\Policies\PhotoQueryPolicy` / `App\Policies\AlbumQueryPolicy` — reused for stats and automatic-mode featured-content queries (same `applySearchabilityFilter`/`applyVisibilityFilter($query, null)` calls as Feature 025). **Not** used for manual-mode featured-item resolution (FR-054-29 — direct lookup, admin-trusted, mirrors Feature 025's `photo_id` background mode).
- `request()->verify()->is_supporter()` / `->validate()` — the exact SE check already used by `InitConfig::set_supporter_properties()` (line ~391-397) — reused for the SE-fallback resolutions (layout, animation preset, stats, featured-content enablement).
- `App\Http\Middleware\ConfigIntegrity` — whitelist to extend (existing pattern at lines ~141-143 for `album_header_size` etc.).
- `App\Models\Webhook` / `App\Http\Controllers\Admin\WebhookController` / `resources/js/v8/views/admin/Webhooks.vue` — structural template for both `LandingLink`'s and `LandingFeaturedItem`'s model/controller shape (ULID PK, CRUD, admin-only).
- `resources/js/v8/components/gallery/albumModule/AlbumHeaderPanel.vue`'s `POSITION_CLASSES` — reference implementation for the 5-position Tailwind class mapping (not imported directly — landing gets its own small composable, see spec Appendix).
- `resources/js/v8/views/admin/NsfwConfig.vue` (Feature 045) — the structural template for the new `LandingConfig.vue` dedicated admin page: `UTabs`, curated `Fieldset` sections per tab, `SettingsService.getAll()`/`setConfigs()` for read/write, shared `BoolField`/`SelectField` components. Note the one deliberate deviation from this template (FR-054-27): NSFW's curated keys stay visible in the flat generic list too, but `LandingConfig.vue` absorbs the **entire** `Mod Welcome` category, so that category is filtered out of the flat list instead — see Q-054-08 in spec Appendix for the reasoning.
- `resources/js/v8/composables/useAdminTiles.ts` / `resources/js/router/paths.ts` — where `LandingConfig.vue` gets registered as an admin tile (`group: "core"`) and route (`landing-config`, `/admin/landing-config`), mirroring how `nsfw-config` is registered.
- `router/paths.ts`'s existing `login` route / `resources/js/v8/components/forms/auth/LoginForm.vue` — reused as-is for `studio`'s "Client Login" CTA (NFR-054-10); no new auth code.
- `App\Http\Resources\GalleryConfigs\FooterConfig`'s existing `is_contact_form_enabled` (Feature 022) — reused as-is to gate the new Contact link on `portfolio`/`minimal` (FR-054-23), navigating to the existing `/contact` route; no new contact-form code.
- `GET /api/v2/Search` (Feature 027/028) / `resources/js/services/search-service.ts` — reused as-is by `LandingConfig.vue`'s Featured tab picker to search photos/albums by title when adding manual `LandingFeaturedItem` rows; no new search backend.
- `App\View\Components\Meta` / `SettingsController::setCSS()`/`setJS()` — the existing global custom CSS/JS mechanism (`dist/user.css`/`dist/custom.js`, loaded via `<x-meta>` on every page already). This feature does **not** touch this mechanism at all; noted here only because it was considered and explicitly rejected as a place to add landing-specific styling (see spec Non-Goals / Q-054-07).

## Assumptions & Risks

- **Assumptions:** `request()->verify()` is available and behaves identically wherever `LandingPageResource` is constructed (public, unauthenticated route) — same assumption Feature 039's `InitConfig` already makes on every request. The shared `BoolField`/`SelectField` form components (already used by both `AllSettings.vue` and `NsfwConfig.vue`) cover all 11 new config shapes without new widget types — `LandingConfig.vue` just lays them out in curated groups instead of the flat generic list. The exact current membership of the `Mod Welcome` config category (which pre-existing keys it holds today) is not fully enumerated in this plan — confirm via `config_categories`/`SettingsController::getAll()` at the start of I11.
- **Risks / Mitigations:**
  - *Risk:* Extracting today's `Landing.vue` into `LandingClassic.vue` accidentally changes markup/CSS and breaks NFR-054-01. *Mitigation:* I7 is a pure move/parameterize step with a manual diff-against-original check before any new layout work starts.
  - *Risk:* `parallax_scroll` (scroll-triggered `IntersectionObserver` animation) interacts badly with `prefers-reduced-motion` if not gated correctly. *Mitigation:* build the reduced-motion check into the shared `useLandingAnimation` composable itself (single choke point), not per-layout, so it can't be forgotten in `LandingPortfolio.vue`/`LandingMinimal.vue`.
  - *Risk:* Automatic-mode featured-content/stats queries add load to the highest-traffic public route. *Mitigation:* both are opt-in (default `false`) and reuse indexed columns per NFR-054-06; no new index is anticipated but query plans should be checked in I5/I6 if p95 looks off. Manual mode is N direct PK lookups, not a filtered query, so it carries no equivalent risk.
  - *Risk:* No JS test runner exists (repo-wide gap, Q-051-05) — layout components get manual/browser verification only. *Mitigation:* Branch & Scenario Matrix (S-054-01..31) is the checklist for manual verification; each row is assigned to an increment below (Scenario Tracking).
  - *Risk:* Manual-mode featured-item resolution deliberately skips the public-visibility policy check (FR-054-29) — if this isn't clearly tested and documented as intentional, a future contributor could "fix" it as a privacy bug and silently reduce the feature's usefulness (an admin manually featuring a private highlight photo is the whole point of manual mode). *Mitigation:* NFR-054-03 explicitly documents this as a deliberate carve-out mirroring Feature 025's existing `photo_id` precedent; I6c's tests assert the bypass is intentional, not just that it happens to occur.
  - *Risk:* Filtering `Mod Welcome` out of the flat generic Settings list (FR-054-27) could momentarily "hide" landing settings for anyone still looking in the old place. *Mitigation:* `LandingConfig.vue` is registered as a visible admin tile (I11), and this is a one-time, well-documented relocation, not a silent removal.

## Implementation Drift Gate

Before starting frontend increments (I7+), re-read `resources/js/v8/views/Landing.vue`, `AlbumHeaderPanel.vue`, `LandingPageResource.php`, and `resources/js/v8/views/admin/NsfwConfig.vue` fresh (they may have changed since this plan was written) and confirm: (a) `LandingPageResource`'s constructor shape and Feature 025's background-resolution methods are still as described in "Dependencies & Interfaces," (b) `NsfwConfig.vue`'s tabbed/`Fieldset`-grouped structure and its `SettingsService.getAll()`/`setConfigs()` usage are still current — that's the direct template for `LandingConfig.vue`, (c) `Webhooks.vue`/`WebhookController` are still the best structural template for `LandingLink`'s and `LandingFeaturedItem`'s CRUD, (d) the `Mod Welcome` config category's exact current key membership (needed for I11's Settings tab and FR-054-27's filtering), (e) `GET /api/v2/Search`'s response shape is still suitable for the Featured tab's picker (I6b). Record findings and any deltas at the top of the Analysis Gate section below before writing code.

## Increment Map

1. **I1 – Backend foundation: enums + scalar configs**
   - _Goal:_ Land the 11 new scalar configs and their supporting enums, with zero frontend/behavioural change yet.
   - _Preconditions:_ None — first increment.
   - _Steps:_ Create `App\Enum\LandingLayoutType` (4 values incl. `studio`), `LandingTextPosition`, `LandingAnimationPreset`, `LandingLinkPlacement`, `LandingFeaturedItemsMode`, `LandingFeaturedItemType` (mirror `AlbumTitlePosition`'s file shape). Write migration MIG-054-01 (11 config rows incl. `landing_cta_text`, `landing_featured_items_mode`, `type`/`type_range` metadata matching the shared field-widget expectations, defaults per FR-054-20, all filed under the existing `Mod Welcome` category). Add all 11 keys to `ConfigIntegrity`'s whitelist. Add `all_settings.details.*` English translation keys (TRANS-054-01..07, TRANS-054-11) and the new standalone `landing.client_login`/`landing.view_public_gallery`/`landing.contact` keys (TRANS-054-10).
   - _Commands:_ `php artisan migrate`, `make phpstan`, `vendor/bin/php-cs-fixer fix`.
   - _Exit:_ `php artisan config:show` (or equivalent) lists all 11 keys with correct defaults under `Mod Welcome`; the flat generic Settings UI still renders them correctly at this point (FR-054-27's filtering doesn't land until I11).

2. **I2 – `LandingLink` model, migration, factory**
   - _Goal:_ Data layer for extra links, no API/UI yet.
   - _Preconditions:_ I1 (enum `LandingLinkPlacement` exists).
   - _Steps:_ Migration MIG-054-02 (`landing_links` table). `App\Models\LandingLink` (ULID PK, fillable/casts mirroring `Webhook.php`'s shape, `scopeEnabled()`). `LandingLinkFactory`.
   - _Commands:_ `php artisan migrate`, `php artisan tinker` sanity check, `make phpstan`.
   - _Exit:_ Model + factory create/read/update/delete correctly in a quick unit test.

3. **I3 – `LandingLink` admin CRUD (REST)**
   - _Goal:_ API-054-02..08 fully working and admin-gated.
   - _Preconditions:_ I2.
   - _Steps:_ `StoreLandingLinkRequest`/`UpdateLandingLinkRequest` (validation per FR-054-11, incl. default-icon fallback note for the frontend). `LandingLinkResource` (public-safe projection, reused for both admin and public embed). `App\Http\Controllers\Admin\LandingLinkController` (index/store/show/update/patch/destroy/reorder — mirror `WebhookController`). Routes in `routes/api_v2.php` under the existing admin group.
   - _Commands:_ `php artisan test --filter=LandingLink`, `make phpstan`.
   - _Exit:_ Feature tests cover S-054-16..19 (CRUD, reorder, admin-only 403, count validation).

4. **I4 – `LandingPageResource` extension: layout, animation, intro, position, about, CTA text**
   - _Goal:_ Public payload carries the new fields with correct SE-fallback resolution.
   - _Preconditions:_ I1.
   - _Steps:_ Extend `LandingPageResource` constructor with `layout` (FR-054-01/02, 4 values incl. `studio` — SE-fallback check applies identically to all 3 premium values), `intro_screen_enabled` (FR-054-03), `hero_text_position` (FR-054-04), `animation_preset` (FR-054-05/06), `about_enabled`/`about_text` (FR-054-08), `cta_text` (FR-054-24, free tier, plain passthrough). Reuse the exact `request()->verify()->validate() && ->is_supporter()` check already used by `InitConfig`. Unit tests for SE-on/SE-off × each SE-gated field.
   - _Commands:_ `php artisan test --filter=LandingPageResource`, `make phpstan`.
   - _Exit:_ S-054-01..09, S-054-11, S-054-21..24 covered by unit/feature tests.

5. **I5 – Stats resolution**
   - _Goal:_ `public_photo_count`/`public_album_count` (FR-054-09).
   - _Preconditions:_ I4.
   - _Steps:_ Add count queries using `PhotoQueryPolicy::applySearchabilityFilter`/`AlbumQueryPolicy::applyVisibilityFilter` with `user=null`; gate behind effective `landing_show_stats` (SE-fallback reuses I4's helper).
   - _Commands:_ `php artisan test --filter=LandingStats`.
   - _Exit:_ S-054-12 covered; NFR-054-03 verified with mixed public/private fixtures.

6. **I6 – Featured content: automatic-mode resolution**
   - _Goal:_ `featured_items` array in automatic mode (FR-054-10), reusing Feature 025's `resolveLatestAlbumCover` query shape.
   - _Preconditions:_ I4.
   - _Steps:_ Add `LandingFeaturedItemResource` (unified photo/album projection, DO-054-03). Automatic mode: query public albums ordered `published_at DESC, created_at DESC, id DESC`, `LIMIT landing_featured_items_count`, project `item_type: "album"`, `id`/`title`/cover thumb URL/`num_photos`. Gate behind effective `landing_featured_items_enabled` **and** `landing_featured_items_mode=automatic`.
   - _Commands:_ `php artisan test --filter=LandingFeaturedItemsAutomatic`.
   - _Exit:_ S-054-13..15 covered.

6a. **I6a – `LandingFeaturedItem` model, migration, factory**
   - _Goal:_ Data layer for manual featured-content curation, no API/UI yet.
   - _Preconditions:_ I1 (enum `LandingFeaturedItemType` exists).
   - _Steps:_ Migration MIG-054-03 (`landing_featured_items` table). `App\Models\LandingFeaturedItem` (ULID PK, fillable/casts mirroring `LandingLink.php`'s shape, `scopeEnabled()`). `LandingFeaturedItemFactory`.
   - _Commands:_ `php artisan migrate`, `make phpstan`.
   - _Exit:_ Model + factory create/read/update/delete correctly in a quick unit test.

6b. **I6b – `LandingFeaturedItem` admin CRUD (REST)**
   - _Goal:_ API-054-09..15 fully working and admin-gated.
   - _Preconditions:_ I6a.
   - _Steps:_ `StoreLandingFeaturedItemRequest`/`UpdateLandingFeaturedItemRequest` (validates `item_id` references an existing `Photo`/`Album` matching `item_type`). `App\Http\Controllers\Admin\LandingFeaturedItemController` (index/store/show/update/patch/destroy/reorder — mirror `LandingLinkController` from I3). Routes in `routes/api_v2.php` under the existing admin group.
   - _Commands:_ `php artisan test --filter=LandingFeaturedItem`, `make phpstan`.
   - _Exit:_ Feature tests cover S-054-28 (CRUD + placement... i.e. mixed-type ordering), S-054-18 (admin-only 403), item-existence validation.

6c. **I6c – Featured content: manual-mode resolution**
   - _Goal:_ `featured_items` array in manual mode (FR-054-29), admin-trusted, no policy check.
   - _Preconditions:_ I6a, I4 (SE-fallback helper).
   - _Steps:_ When effective `landing_featured_items_mode=manual`, resolve enabled `LandingFeaturedItem` rows ordered by `sort_order`, looking up each `item_id` directly against `Photo`/`Album` (no `PhotoQueryPolicy`/`AlbumQueryPolicy` call — mirrors FR-025-04's `photo_id` mode). Skip silently if the referenced record no longer exists. Project through the same `LandingFeaturedItemResource` as I6's automatic mode.
   - _Commands:_ `php artisan test --filter=LandingFeaturedItemsManual`.
   - _Exit:_ S-054-28, S-054-29, S-054-30 covered; NFR-054-03's manual-mode carve-out explicitly tested (not just incidentally true).

7. **I7 – Frontend: `Landing.vue` dispatcher + `LandingClassic.vue` extraction**
   - _Goal:_ Zero-regression refactor — today's page keeps working exactly as-is, now behind the dispatcher.
   - _Preconditions:_ I1, I4 (fields exist even if I9-I10 haven't built the other layouts yet — `LandingClassic.vue` only needs `intro_screen_enabled`/`hero_text_position`/`animation_preset` defaults and `links`).
   - _Steps:_ Move current `resources/js/v8/views/Landing.vue` markup verbatim into new `resources/js/v8/views/landing/LandingClassic.vue`; parameterize only the 3 flags (all default to today's fixed values) and append `links` (nav placement → header menu area, footer placement → footer). Also wire `cta_text` (overrides the "Access Gallery" label when non-empty, FR-054-24) — default-empty so classic stays a no-op by default. New `Landing.vue` becomes the fetch + dispatcher (FR-054-14), routing to `LandingClassic.vue` only for now (other branches added in I9/I9a/I10).
   - _Commands:_ Manual diff of rendered DOM/CSS against pre-change output; `npm run check`.
   - _Exit:_ S-054-01 verified (pixel-identical default output); S-054-05, S-054-16, S-054-24 (classic) verified manually.

8. **I8 – Shared position/animation composables**
   - _Goal:_ Single choke point for the 5-position mapping and the 5 animation presets, including reduced-motion handling (NFR-054-04).
   - _Preconditions:_ I7.
   - _Steps:_ `resources/js/v8/composables/useLandingTextPosition.ts` (Tailwind class map, same shape as `AlbumHeaderPanel.vue`'s `POSITION_CLASSES` but landing-scoped per spec Appendix). `resources/js/v8/composables/useLandingAnimation.ts` (returns CSS classes/keyframe names per preset; checks `window.matchMedia('(prefers-reduced-motion: reduce)')` and forces `none` when set, per FR-054-07). CSS keyframes for `zoom_in`/`slide_reveal`; `parallax_scroll` uses `IntersectionObserver` to toggle in-view classes per section.
   - _Commands:_ `npm run check`, manual OS-level reduced-motion toggle test.
   - _Exit:_ S-054-08, S-054-10 verified manually.

9. **I9 – Frontend: `LandingPortfolio.vue`**
   - _Goal:_ New scrollable, multi-section layout (FR-054-16).
   - _Preconditions:_ I5, I6, I6c, I8.
   - _Steps:_ Sticky nav (logo + `links` nav/both + Gallery + Contact link when `footer.is_contact_form_enabled`, FR-054-23, navigating to `/contact`), hero (background + positioned text + CTA respecting `cta_text` override, using I8 composables), optional about section, optional featured-content section (renders `featured_items` regardless of automatic/manual — the component doesn't care which mode produced the array), scroll-down indicator between hero and the next section (FR-054-25, reduced-motion-aware per I8), footer (existing `FooterConfig` + `links` footer/both). Each section conditionally omitted per its enable flag. Wire into `Landing.vue` dispatcher.
   - _Commands:_ `npm run check`, manual browser walk-through.
   - _Exit:_ S-054-02, S-054-07, S-054-11 (portfolio), S-054-13..15, S-054-23, S-054-25, S-054-26, S-054-28..30 (rendering side) verified manually.

9a. **I9a – Frontend: `LandingStudio.vue`**
   - _Goal:_ New client-login-first layout (FR-054-21).
   - _Preconditions:_ I4 (studio SE-fallback resolved server-side), I8.
   - _Steps:_ Primary CTA as a `RouterLink` to the existing `login` route, label from `cta_text` else `landing.client_login` translation (NFR-054-10 — no new auth code). Secondary smaller link to the `home` route (public gallery). Hero copy reuses `landing_title`/`landing_subtitle`/`landing_about_text` like other layouts; optional background per existing Feature 025 resolution. Footer `links`/social icons. No Contact link (see spec Non-Goals).
   - _Commands:_ `npm run check`, manual browser walk-through.
   - _Exit:_ S-054-21, S-054-24 (studio branch) verified manually.

10. **I10 – Frontend: `LandingMinimal.vue`**
    - _Goal:_ New centered-card layout (FR-054-17).
    - _Preconditions:_ I8.
    - _Steps:_ Centered logo/title/subtitle, optional about text, single CTA respecting `cta_text` override, footer `links`/social icons + Contact link when `footer.is_contact_form_enabled` (FR-054-23, navigating to `/contact`). No featured-content section (by design). Wire into dispatcher.
    - _Commands:_ `npm run check`, manual browser walk-through.
    - _Exit:_ S-054-04, S-054-06 (minimal branch), S-054-11 (minimal), S-054-25 (minimal branch) verified manually.

11. **I11 – Admin UI: `LandingConfig.vue` dedicated page (Settings + Links + Featured tabs)**
    - _Goal:_ FR-054-19, FR-054-27, UI-054-01/02/03/04/06/07.
    - _Preconditions:_ I3 (Links tab), I6a/I6b (Featured tab's manual CRUD), I6c (Featured tab needs the mode concept to exist server-side).
    - _Steps:_
      1. New `resources/js/v8/views/admin/LandingConfig.vue`, structural copy of `NsfwConfig.vue`'s shape: `UTabs` with `settings`/`links`/`featured` slots.
      2. **Settings tab:** confirm the current full membership of the `Mod Welcome` category (Implementation Drift Gate item (d)), load it via `SettingsService.getAll()`, lay out in curated `Fieldset` sections ("General," "Background," "Branding," "Layout & Structure," "Hero," "Content," "Footer & Social" — grouping both pre-existing and new keys, not just the 11 new ones); save via `SettingsService.setConfigs()` on change, same as NSFW's `save()` helper.
      3. Add an "SE" badge/disabled state on the `landing_layout` dropdown's `portfolio`/`minimal`/`studio` options and the `landing_animation_preset` dropdown's premium-preset options (read from existing `is_se_enabled`/`is_se_preview_enabled` init data) — this is bespoke to `LandingConfig.vue`'s custom dropdown rendering, since whole-field `require_se` (as NSFW sets client-side on its own keys) doesn't fit a field where only some enum *values* are gated.
      4. **Links tab:** the `LandingLink` list/create/edit/delete UI + drag-reorder calling API-054-08.
      5. **Featured tab:** mode switcher (`landing_featured_items_enabled`/`landing_featured_items_mode`/`landing_featured_items_count` — reuses the Settings tab's field components), and, always available beneath it, the `LandingFeaturedItem` manual-curation UI: a search box hitting `GET /api/v2/Search` to find photos/albums by title, an "Add" action creating a `LandingFeaturedItem` via API-054-10, an ordered list with drag-reorder (API-054-15) and per-row enable/delete.
      6. Patch `SettingsController::getAll()`'s category-visibility filter (same mechanism as Feature 052's Q-052-07 resolution) to exclude `Mod Welcome` from the flat generic list's response (FR-054-27).
      7. Register `landing-config` in `router/paths.ts` and as an admin tile in `useAdminTiles.ts` (`group: "core"`, visible whenever `can_edit`), mirroring `nsfw-config`'s registration.
    - _Commands:_ `npm run check`, manual browser walk-through.
    - _Exit:_ S-054-16, S-054-17, S-054-28, S-054-31 fully verified end-to-end (UI, not just API); UI-054-01/02/03/04/06/07 verified manually.

12. **I12 – Translation sweep**
    - _Goal:_ All new keys present across all 22 supported locales (matches repo convention of full-locale coverage at completion, e.g. Feature 050/051).
    - _Preconditions:_ I1-I11 (keys stable).
    - _Steps:_ Extend `lang/*/all_settings.php`, `lang/*/landing.php`, new `lang/*/landing_link.php`, new `lang/*/landing_featured_item.php` across all locales (English-authored source, mechanical propagation per existing repo translation workflow).
    - _Commands:_ Existing translation-completeness check/script if one exists in this repo (verify during increment).
    - _Exit:_ No missing-translation warnings for any new key in any locale.

13. **I13 – Quality gates & full regression pass**
    - _Goal:_ Feature-complete sign-off.
    - _Preconditions:_ I1-I12.
    - _Steps:_ Full `php artisan test` run; `make phpstan`; `vendor/bin/php-cs-fixer fix`; `npm run check`; `npm run format`; manual walk-through of the full Branch & Scenario Matrix (S-054-01..31), including confirming v7 is untouched (S-054-20) and the flat list no longer shows landing settings (S-054-31).
    - _Commands:_ `php artisan test`, `make phpstan`, `npm run check`, `npm run format`.
    - _Exit:_ All quality gates green; roadmap.md moved to reflect completion status.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|----------------------------|-------|
| S-054-01 | I7 | Pixel-identical default output — the core regression guardrail. |
| S-054-02 | I9 | Portfolio layout, SE on. |
| S-054-03 | I4 | SE-off fallback to classic. |
| S-054-04 | I10 | Minimal layout, SE on. |
| S-054-05 | I7 | Intro toggle, classic. |
| S-054-06 | I9, I10 | Intro toggle, portfolio + minimal no-op. |
| S-054-07 | I9 | Hero text position, portfolio. |
| S-054-08 | I8, I9 | Parallax animation, SE on. |
| S-054-09 | I4 | Animation SE-off fallback. |
| S-054-10 | I8 | Reduced-motion override. |
| S-054-11 | I9, I10 | About block, portfolio + minimal; absent on classic. |
| S-054-12 | I5 | Stats, private content excluded. |
| S-054-13 | I6, I9 | Featured content, automatic mode, full count. |
| S-054-14 | I6, I9 | Featured content, automatic mode, partial count. |
| S-054-15 | I6, I9 | Featured content, automatic mode, zero available. |
| S-054-16 | I3, I11 | Links CRUD + placement filtering, API and UI. |
| S-054-17 | I3, I11 | Links reorder, API and UI. |
| S-054-18 | I3, I6b | Admin-only 403 (Links and Featured Items). |
| S-054-19 | I3 | Count range validation. |
| S-054-20 | I13 | v7 untouched confirmation. |
| S-054-21 | I9a | Studio layout, SE on. |
| S-054-22 | I4 | Studio SE-off fallback to classic. |
| S-054-23 | I9 | CTA text override, portfolio. |
| S-054-24 | I4, I9a | CTA text default per layout (classic/portfolio/minimal vs studio). |
| S-054-25 | I9, I10 | Contact-form link surfacing, portfolio + minimal. |
| S-054-26 | I9 | Scroll-down indicator + reduced-motion behaviour. |
| S-054-28 | I6b, I6c, I9, I11 | Manual mode: mixed photo/album curation, CRUD, rendering. |
| S-054-29 | I6c | Manual mode: deleted referenced item, graceful skip. |
| S-054-30 | I6c, I9 | Manual mode: zero enabled items, section omitted. |
| S-054-31 | I11 | Flat generic list no longer shows landing settings. |

## Analysis Gate

Not yet run. To be recorded here (date, reviewer, findings) once the Implementation Drift Gate re-read (above) is performed at the start of implementation — matches the pattern used by Feature 049's Analysis Gate.

## Exit Criteria

- All 30 Branch & Scenario Matrix rows verified (automated where a test exists, manual/browser otherwise, per Test Strategy).
- `php artisan test`, `make phpstan`, `npm run check`, `npm run format` all clean.
- `resources/js/v7/**` has zero diffs (NFR-054-08 / S-054-20).
- All 11 new scalar configs render correctly in the curated `LandingConfig.vue` page's Settings tab (FR-054-19), and the flat generic Settings list no longer shows the `Mod Welcome` category at all (FR-054-27 / S-054-31).
- Translation sweep (I12) complete across all 22 locales.
- `docs/specs/4-architecture/roadmap.md` and `docs/specs/_current-session.md` updated to reflect completion.

## Follow-ups / Backlog

- Modular/reorderable section builder (see spec's Non-Goals) — revisit only if 4 named layouts prove insufficient in practice.
- Dedup `POSITION_CLASSES`-style mapping between `AlbumHeaderPanel.vue` and the new landing composable into one shared utility.
- Mosaic/grid-first layout, "coming soon" layout, split-screen editorial layout, background video support (see spec Appendix's Extra-Layout brainstorm).
- Second image slot for the About section; testimonials/client-logos CRUD block; dedicated hero tagline field (see spec Appendix's Extra-Feature brainstorm).
- Live WYSIWYG preview in the admin settings page.
- A curated icon picker for `LandingLink.icon` (free-text only for this feature).
