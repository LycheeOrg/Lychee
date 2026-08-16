# Feature 054 – Configurable Landing Page

| Field | Value |
|-------|-------|
| Status | Completed |
| Last updated | 2026-08-11 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/054-configurable-landing-page/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/054-configurable-landing-page/tasks.md` |
| Roadmap entry | Completed Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The landing page (`resources/js/v8/views/Landing.vue`) becomes a **layout picker** with four admin-selectable layouts (`classic`, `portfolio`, `minimal`, `studio`), plus cross-cutting configuration — hero text position, animation preset, CTA copy, an about block, featured content (automatic or manually curated), and an admin-manageable list of extra links.

It reuses the existing config system (`Config`/`ConfigManager`, DB-backed key/value settings read/written through `SettingsService`), the existing dynamic-background resolution from Feature 025, the hero text-position/color pattern already shipped for the album "extended hero" (`AlbumHeaderPanel.vue`), and the existing global custom CSS/JS mechanism (Settings → `dist/user.css`/`dist/custom.js`, loaded on every page via `<x-meta>`) — no new styling mechanism is introduced.

Admins get a dedicated page, `LandingConfig.vue`, structured like the Watermarker module's `WatermarkPreview.vue`: a settings form with a live, instantly-reactive preview, plus tabs for managing extra links and featured content. The 12 new settings also remain fully visible and editable in the flat generic Settings list — the dedicated page is an additional, richer way to configure them, not a replacement.

Affected modules: **Config** (`App\Models\Config`, `App\Http\Middleware\ConfigIntegrity`), **Landing Page** (`App\Http\Resources\GalleryConfigs\LandingPageResource`), **Admin CRUD** (`App\Models\LandingLink`, `App\Models\LandingFeaturedItem`), **Admin UI** (`resources/js/v8/views/admin/LandingConfig.vue`), **Frontend v8 only** (`resources/js/v8/views/Landing.vue` and the four layout components).

## Goals

1. Four named layouts (`classic`, `portfolio`, `minimal`, `studio`) selectable via `landing_layout`.
2. `classic` is today's page, unchanged by default, and remains free forever. `portfolio`, `minimal`, `studio`, and 3 premium animation presets require Lychee SE; the default animation stays free.
3. Hero headline/subtitle position is configurable (5 positions), reusing the pattern already shipped for album hero titles. Hero headline/subtitle text color and opacity are also configurable, reusing the site's existing Color Settings picker (`ColorField.vue`) and a percentage slider respectively.
4. Animation preset is configurable, including a "no animation" option; `prefers-reduced-motion` always wins client-side regardless of the configured preset.
5. An arbitrary, ordered list of extra links (nav and/or footer placement) is admin-manageable, independent of the fixed social-media fields, and available on every layout.
6. An optional "about" text block and an optional featured-content section (automatic — most-recently-published public albums — or fully manual curation of specific photos/albums) are available on `portfolio`; `minimal` gets the about block only, `classic` gets neither.
7. The primary CTA button's label is overridable, and the existing Contact Form (Feature 022) can be surfaced as a nav/footer link on `portfolio`/`minimal`.
8. Admins configure everything through the existing flat generic Settings list, plus a dedicated `LandingConfig.vue` page with a live, instantly-reactive preview and extra-link/featured-content management.
9. Existing installations see no behavioural or visual change until an admin opts in.
10. All new frontend work targets `resources/js/v8/**`; `resources/js/v7/**` is untouched.

## Non-Goals

- **v7 (legacy PrimeVue) parity.** `resources/js/v7/views/Landing.vue` is not modified.
- **A full modular/reorderable section page-builder.** Four named layouts, not an arbitrary section composer.
- **Layouts beyond the four in scope** (mosaic/grid-first, split-screen editorial, "coming soon," cinematic/video-hero) — considered, not built.
- **A new authentication mechanism.** `studio`'s "Client Login" CTA reuses the existing `login` route/`LoginForm.vue` flow as-is.
- **A landing-specific custom CSS/JS field.** The existing global mechanism (Settings → `dist/user.css`/`dist/custom.js`, already loaded on every page via `<x-meta>`) covers this; admins target the relevant selectors themselves.
- **A gallery-stats/counter display.** No photo/album count block on any layout.
- **An icon field on `LandingLink`.** Extra links are label + URL only.
- **A curated icon picker.** N/A — no icon field exists.
- **Contact Form surfacing on `classic` or `studio`.**
- **Ever hiding the primary call-to-action entirely.** Every layout always renders at least one visible, reachable path into the gallery (or, for `studio`, into the login flow).
- **Per-user or authenticated/personalised landing pages.**
- **Slideshow, multiple rotating, or video backgrounds.** Backgrounds remain single static image URLs per orientation, resolved exactly as Feature 025 already does.
- **New billing/licensing mechanics.** SE-gating reuses the existing supporter/license verification (`request()->verify()->is_supporter()`).
- **Per-locale authoring of `landing_about_text` or extra-link labels.** Single global strings, like `footer_additional_text`.
- **A configurable label for `studio`'s secondary "view public gallery" link.** Only the primary CTA is overridable.
- **Text color/opacity on the CTA button or any element other than the hero headline/subtitle.** `landing_hero_text_color`/`landing_hero_text_opacity` style only the headline and subtitle text.
- **Per-word or per-line color/opacity.** A single color and opacity value applies uniformly to the whole hero headline+subtitle.
- **Reordering/renaming the existing footer social-media fields.**
- **A live WYSIWYG preview in the flat generic Settings list itself.** The live preview lives only on `LandingConfig.vue`.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-054-01 | New enum config `landing_layout` with values `classic`, `portfolio`, `minimal`, `studio` (new `App\Enum\LandingLayoutType`). Default `classic`. | Admin selects a layout; value persisted like any other enum config. | Restricted to the 4 enum values. | Invalid value rejected at config-update time. | — | Goal 1 |
| FR-054-02 | `LandingPageResource` resolves an **effective** layout: if the stored value is `portfolio`, `minimal`, or `studio` but the requester is not on Lychee SE (`request()->verify()->validate() && request()->verify()->is_supporter()` is false), the effective layout falls back to `classic`. `classic` is always available. | SE install sees the configured layout; non-SE install always sees `classic`. | — | No exception, no error surfaced — same fail-safe shape as `InitConfig::is_white_label_enabled`. | — | Goal 2 |
| FR-054-03 | New bool config `landing_intro_screen_enabled`, default `true`. Controls whether the full-screen animated splash (logo/title pop-in, current `#intro` block) plays before the hero. Applies to `classic` and `portfolio`; `minimal` has no splash by design; `studio` has no splash. | Splash renders/skips per the flag. | — | — | — | User request ("enable/disable the first screen") |
| FR-054-04 | New enum config `landing_hero_text_position` with values `top_left`, `top_right`, `bottom_left`, `bottom_right`, `center` (new `App\Enum\LandingTextPosition` — a landing-scoped enum, deliberately not a reuse of `App\Enum\AlbumTitlePosition`, since albums and the landing page are different bounded contexts). Default `center`. Controls placement of the hero headline/subtitle/CTA within the hero viewport for `classic` and `portfolio`. | Hero text renders at the configured corner/center using the same Tailwind position-class mapping proven in `AlbumHeaderPanel.vue`. | Restricted to the 5 enum values. | Invalid value rejected. | — | User request ("position hero text like album hero") |
| FR-054-28 | New config `landing_hero_text_color` (`type_range: 'color'`, default `''` = use the built-in fallback of `#ffffff`). Rendered in the admin UI by the existing generic `config.type === 'color'` dispatch (`ConfigGroup.vue`) via `ColorField.vue` — the same free-form picker (`@dayflow/blossom-color-picker-vue`) used by the site's Theme Colors settings. Unlike the 7 Theme Colors keys, this value is **not** fed through `Style.php`'s `PaletteGenerator` — it is a single hex string, read via `getValueAsString()` and applied directly as the CSS `color` of the hero headline and subtitle text on all four layouts. | Configured color renders on the hero headline/subtitle text; empty value renders the current default white. | Value must be a valid hex color string (or empty). | Invalid value rejected at config-update time. | — | User request ("choose the color of the text") |
| FR-054-29 | New int config `landing_hero_text_opacity` (default `100`, range `0-100`, percent). Applied as the CSS `opacity` of the hero headline and subtitle text (converted to a 0–1 float) alongside `landing_hero_text_color`, on all four layouts — not applied to the CTA button or other interactive elements. | Text renders at the configured opacity; default `100` is fully opaque, matching today's behaviour. | Restricted to `0-100`. | Invalid value rejected. | — | User request ("change the opacity of the text") |
| FR-054-05 | New enum config `landing_animation_preset` with values `none`, `classic_fade`, `zoom_in`, `parallax_scroll`, `slide_reveal` (new `App\Enum\LandingAnimationPreset`). Default `classic_fade` (today's exact keyframes). | Selected preset governs the CSS animation classes applied. | Restricted to the 5 enum values. | Invalid value rejected. | — | User request ("different kinds of animations") |
| FR-054-06 | `LandingPageResource` resolves an **effective** animation preset: `none` and `classic_fade` are available to every install; `zoom_in`, `parallax_scroll`, `slide_reveal` require SE (same check as FR-054-02) and fall back to `classic_fade` otherwise. | SE install gets the configured preset; non-SE install gets `classic_fade`. | — | No exception, fail-safe like FR-054-02. | — | Goal 2 |
| FR-054-07 | Client-side, `prefers-reduced-motion: reduce` forces the effective animation to `none` regardless of the resolved server value. | Reduced-motion users see zero animation on every layout. | — | — | — | Accessibility (WCAG 2.3.3) |
| FR-054-08 | New bool config `landing_about_enabled` (default `false`) and text config `landing_about_text` (default `''`, admin-authored HTML, same trust model as `footer_additional_text` — rendered verbatim, no sanitizer). When enabled, `portfolio` and `minimal` render an about block; `classic` never does. | Block renders `landing_about_text` when supported and enabled. | — | Empty text with the flag on renders nothing. | — | Goal 6 |
| FR-054-09 | New bool config `landing_featured_items_enabled` (default `false`, SE-gated) and enum config `landing_featured_items_mode` (`automatic`\|`manual`, new `App\Enum\LandingFeaturedItemsMode`, default `automatic`). In `automatic` mode, `LandingPageResource` exposes up to `landing_featured_items_count` (int, default `24`, range 3–100) public albums, ordered `published_at DESC, created_at DESC, id DESC` via `AlbumQueryPolicy::applyVisibilityFilter($query, null)` (same query shape as Feature 025's `resolveLatestAlbumCover`), each `{item_type: "album", id, title, thumb_url, num_photos}`. Manual mode is FR-054-27. Only `portfolio` renders this section. | Automatic mode shows up to N most-recently-published public albums. | `landing_featured_items_count` restricted to 3–100; `landing_featured_items_mode` restricted to the 2 enum values. | Fewer than N public albums → shows however many exist; zero → section omitted. | — | Goal 6 |
| FR-054-10 | New model `App\Models\LandingLink` (ULID PK, mirrors `App\Models\Webhook`'s shape) backed by table `landing_links`: `label`, `url`, `placement` (enum `nav`\|`footer`\|`both`, new `App\Enum\LandingLinkPlacement`), `open_in_new_tab` (bool, default `true`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), timestamps. | Admin defines any number of extra links beyond the fixed 5 social-media fields. | `url` required, valid absolute URL, ≤2048 chars; `label` required, ≤255 chars; `placement` restricted to allowed values. | 422 with field errors. | — | Goal 5 |
| FR-054-11 | Admin-only REST CRUD for `LandingLink`: `GET/POST /api/v2/LandingLink`, `GET/PUT/PATCH/DELETE /api/v2/LandingLink/{landingLink}` (mirrors `App\Http\Controllers\Admin\WebhookController`'s CRUD shape). `PATCH /api/v2/LandingLink/Reorder`: body `{ ids: string[] }` must contain the complete set of every existing `LandingLink` ID in the desired order — a partial/mismatched set is rejected (422), not partially applied; on success, `sort_order` is set to each ID's array index inside a DB transaction; response is the freshly re-ordered index-shaped list. | Admin creates/lists/updates/reorders/deletes links via `LandingConfig.vue`'s Links tab. | Only `is_admin = true` may call these routes. `ids` must exactly match the existing ID set. | Non-admin → 403; missing link → 404; `ids` mismatch on Reorder → 422. | — | Goal 5 |
| FR-054-12 | `LandingPageResource` includes a `links` array: `enabled = true` `LandingLink` rows ordered by `sort_order`, projected to `{id, label, url, placement, open_in_new_tab}`. Available on every layout including `classic`, rendered in the nav area (placement `nav`/`both`) and/or footer area (placement `footer`/`both`). | Enabled links appear in the correct area(s) on every layout. | — | Zero links configured → no extra rendering. | — | Goal 5 |
| FR-054-13 | `resources/js/v8/views/Landing.vue` is a thin dispatcher: fetches `LandingPageResource` once, then renders `LandingClassic.vue`, `LandingPortfolio.vue`, `LandingMinimal.vue`, or `LandingStudio.vue` based on the resolved `layout`, passing the fetched data down via a required prop shaped like `LandingPageResource`. All 4 layout components accept data via this prop — none self-fetches. Loading/error handling (redirect to gallery when `landing_page_enable` is false, or on fetch error) is unchanged from today. | Correct layout component mounts, fed by the dispatcher's single fetch. | — | Unreachable/unknown layout value falls back to `LandingClassic.vue`. | — | Goal 1 |
| FR-054-14 | `LandingClassic.vue` contains the exact current markup, CSS keyframes, and behaviour of today's `Landing.vue`, parameterised only by `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_hero_text_color`, `landing_hero_text_opacity`, `landing_animation_preset`, `landing_cta_text` (all defaulting to today's fixed behaviour), and the `links` array appended to the existing menu/footer. With every new config at its default, `classic` is pixel-for-pixel identical to pre-feature output. | Default install renders unchanged. | — | — | — | Goal 9 |
| FR-054-15 | `LandingPortfolio.vue`: sticky nav bar (logo + `links` placement `nav`/`both` + "Gallery" link + "Contact" link when `footer.is_contact_form_enabled`, navigating to `/contact`), a hero section (background per Feature 025, headline/subtitle positioned per FR-054-04 and styled per FR-054-28/29, CTA respecting `landing_cta_text`, animated per FR-054-06), an optional about section (FR-054-08), an optional featured-content section (FR-054-09/FR-054-27), a scroll-down indicator between the hero and the next rendered section (reduced-motion-aware, static but still clickable when reduced motion is requested), and a footer (existing `FooterConfig` + `links` placement `footer`/`both`). Sections that are disabled/empty are omitted entirely. | `portfolio`-configured SE install shows a multi-section, scrollable page: nav → hero → about → featured content → footer. | — | Any content block resolving to nothing is simply omitted. | — | Goal 1 |
| FR-054-16 | `LandingMinimal.vue`: a single centered card (logo or title/subtitle styled per FR-054-28/29, optional about text, one CTA button respecting `landing_cta_text`, footer `links`/social icons + "Contact" link when `footer.is_contact_form_enabled`). No full-bleed background required; no featured-content section. | `minimal`-configured SE install shows a compact, distraction-free page. | — | — | — | Goal 1 |
| FR-054-17 | `LandingStudio.vue`: primary hero CTA is a "Client Login" button (`RouterLink` to the existing `login` route — no new auth), label from `landing_cta_text` when set, else the `landing.client_login` translation. A smaller, fixed-label secondary link into the public gallery (`home` route) renders beneath it. Hero copy reuses `landing_title`/`landing_subtitle`/`landing_about_text` like other layouts, styled per FR-054-28/29. | Studio-configured SE install shows a client-login-first hero with a secondary public-gallery link. | — | — | — | Goal 1 |
| FR-054-18 | New admin page `resources/js/v8/views/admin/LandingConfig.vue`: a `UTabs` page with **Settings**, **Links**, **Featured** tabs. Registered as an admin tile in `useAdminTiles.ts` (`group: "core"`), route `landing-config` registered in `resources/js/router/paths.ts` (name/path) and `resources/js/v8/router/routes.ts` (component mapping), visible whenever `can_edit`. | Admin reaches the page from the admin dashboard at `/admin/landing-config`. | — | — | — | Goal 8 |
| FR-054-19 | **Settings tab**, structurally modelled on `resources/js/v8/views/admin/WatermarkPreview.vue`: a two-column layout — left column is the edit form for the 12 keys from FR-054-20, grouped in `Fieldset` sections ("Layout & Structure," "Hero" — text position, color (`ColorField.vue`), opacity, animation preset, CTA text — "Content"); right column is a live, instantly-reactive preview (FR-054-25). Values load once via `SettingsService.getAll()` into local component state (not two-way bound), are edited freely without persisting, and are written back only when the admin clicks **Save** (`SettingsService.setConfigs()`). These 12 keys also remain fully visible and editable in the flat generic Settings list — this page does not remove or filter them from there. | Admin edits settings with live feedback, saves explicitly, and can still use the flat list if preferred. | — | — | — | Goal 8 |
| FR-054-20 | The 12 new scalar configs (`landing_layout`, `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_hero_text_color`, `landing_hero_text_opacity`, `landing_animation_preset`, `landing_about_enabled`, `landing_about_text`, `landing_featured_items_enabled`, `landing_featured_items_mode`, `landing_featured_items_count`, `landing_cta_text`) are given `type_range` metadata and filed under the existing `Mod Welcome` settings category (`level=0`, the migration default). They are deliberately **not** added to `App\Http\Middleware\ConfigIntegrity`'s `SE_FIELDS`/`PRO_FIELDS` lists — that whitelist raises the DB `level` column, which `SettingsController` uses to hide `level>0` configs from non-SE/non-Pro admins in the flat Settings list, contradicting Goal 8's "remain fully visible" requirement and FR-054-21's requirement that a previously-stored SE-only value persist through an SE lapse. SE-gating for these fields is enforced exclusively by `LandingPageResource`'s render-time effective-value fallback (FR-054-02, FR-054-06) and by disabling (not hiding) SE-only dropdown options in `LandingConfig.vue` (FR-054-21) — never by blocking the config write itself. (Resolved via Q-054-01.) | Keys are readable/writable via `SettingsService` and render correctly in the flat generic Settings UI, regardless of SE status. | — | — | — | Goal 8 |
| FR-054-21 | On the Settings tab, the `landing_layout` dropdown's `portfolio`/`minimal`/`studio` options and the `landing_animation_preset` dropdown's premium-preset options are disabled (unselectable) and badged "SE" when the install is not on Lychee SE. If a previously-stored SE-only value exists (e.g. SE lapsed after being configured), it still displays as the current selection — disabled blocks picking a *new* SE-only value, not showing the existing one. | Non-SE admin cannot select SE-only options; a previously-configured one still shows as selected. | — | — | — | Goal 2 |
| FR-054-22 | **Links tab**: `LandingLink` list/create/edit/delete UI and drag-reorder (calling FR-054-11's Reorder endpoint) — immediate-save CRUD, independent of the Settings tab's draft-then-Save flow. | Admin manages extra links with immediate persistence. | — | — | — | Goal 5 |
| FR-054-23 | **Featured tab**: mode switcher (`landing_featured_items_enabled`/`landing_featured_items_mode`/`landing_featured_items_count`), and — always available beneath it — the `LandingFeaturedItem` manual-curation UI: a search box hitting the existing `GET /api/v2/Search` endpoint (Feature 027/028, no new search backend) to find photos/albums by title, an "Add" action, an ordered list with drag-reorder and per-row enable/delete — immediate-save CRUD. | Admin curates manual featured content with immediate persistence. | — | — | — | Goal 6 |
| FR-054-24 | New model `App\Models\LandingFeaturedItem` (ULID PK, mirrors `LandingLink`'s shape) backed by table `landing_featured_items`: `item_type` (enum `photo`\|`album`, new `App\Enum\LandingFeaturedItemType`), `item_id` (a `Photo.id` or `Album.id`), `sort_order`, `enabled`, timestamps. Admin-only REST CRUD identical in shape to FR-054-11: `GET/POST /api/v2/LandingFeaturedItem`, `GET/PUT/PATCH/DELETE /api/v2/LandingFeaturedItem/{landingFeaturedItem}`, `PATCH /api/v2/LandingFeaturedItem/Reorder` (same full-list-resync contract). | Admin searches for and adds specific photos/albums to an ordered, manually-curated list. | `item_id` must reference an existing `Photo`/`Album` matching `item_type` at write time. | 422 if the referenced item doesn't exist; 403 for non-admin. | — | Goal 6 |
| FR-054-25 | The Settings tab's live preview (FR-054-19) renders the actual layout component (`LandingClassic.vue`/`LandingPortfolio.vue`/`LandingMinimal.vue`/`LandingStudio.vue`) matching the **in-progress (unsaved)** `landing_layout` form value, fed via the same prop contract as FR-054-13, assembled client-side from the current form field values plus the already-persisted `links`/`featured_items` (fetched once — those are edited on their own immediate-save tabs, not part of the draft). Renders scaled down (e.g. `transform: scale(0.5)`) and updates on every field change with no Save required. Because SE-gated layout options are disabled (FR-054-21), the preview never needs to render an SE-only layout on a non-SE install. | Every settings change is visible in the preview panel instantly. | — | — | — | Goal 8 |
| FR-054-26 | Existing installations upgrading receive: `landing_layout=classic`, `landing_intro_screen_enabled=true`, `landing_hero_text_position=center`, `landing_hero_text_color=''` (renders as `#ffffff`), `landing_hero_text_opacity=100`, `landing_animation_preset=classic_fade`, `landing_about_enabled=false`, `landing_featured_items_enabled=false`, `landing_featured_items_mode=automatic`, `landing_cta_text=''`, zero `LandingLink`/`LandingFeaturedItem` rows. Net-zero behavioural change until an admin opts in. | Upgrade produces no visible change. | — | — | — | Goal 9 |
| FR-054-27 | When effective `landing_featured_items_mode=manual` (SE required, same fallback as FR-054-02), `LandingPageResource` resolves `featured_items` from enabled `LandingFeaturedItem` rows ordered by `sort_order`: each resolved by direct lookup on `item_id` against `Photo`/`Album` **without** a public-visibility policy check — the admin who selected the item is trusted/responsible, mirroring Feature 025's `photo_id` background mode. A referenced item that no longer exists is skipped silently. Each item projects to `{item_type, id, title, thumb_url, url, num_photos?}` (`num_photos` only for `item_type=album`). | Manual mode shows exactly the curated, enabled items in order, mixing photos and albums freely. | — | Deleted/nonexistent item → skipped; zero enabled items → section omitted. | — | Goal 6 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-054-01 | `classic` with all new configs at their defaults is behaviourally and visually identical to today's landing page. | Backward compatibility. | Manual/Selenium DOM comparison; existing landing tests pass unmodified. | FR-054-14, FR-054-26 | Goal 9 |
| NFR-054-02 | SE-gated fields (`layout`, `animation_preset`, `featured_items_enabled`/`featured_items_mode`) resolve fail-safe: never throw, never leak the SE-only value to a non-SE requester. | Licensing integrity. | Unit test: non-SE request with all SE-only configs set to non-default values still returns `classic`/`classic_fade`/no featured content. | `request()->verify()` | Goal 2 |
| NFR-054-03 | Automatic-mode featured-content resolution uses `PhotoQueryPolicy`/`AlbumQueryPolicy` with `user=null`, so private/unpublished content never appears publicly. Manual-mode featured items are the one deliberate exception — same admin-trusted precedent as Feature 025's `photo_id` background mode. | Privacy. | Test: automatic mode never selects a private album; manual mode successfully resolves an admin-selected private photo/album (by design). | `App\Policies\PhotoQueryPolicy`, `App\Policies\AlbumQueryPolicy` | Goal 6 |
| NFR-054-04 | Client-side, `prefers-reduced-motion: reduce` always forces `none` animation, regardless of the server-resolved preset. | Accessibility (WCAG 2.3.3). | Manual check with OS-level reduced-motion enabled: zero CSS animations on any layout. | `useLandingAnimation` composable | FR-054-07 |
| NFR-054-05 | `LandingLink`/`LandingFeaturedItem` CRUD endpoints are restricted to `is_admin = true`. | Security. | Feature test: non-admin request to any `/api/v2/LandingLink*` or `/api/v2/LandingFeaturedItem*` route returns 403. | Existing admin auth middleware | FR-054-11, FR-054-24 |
| NFR-054-06 | Automatic-mode featured-content queries execute in ≤100ms p95, using indexed columns (`published_at`, `created_at`) and a `LIMIT`. Manual-mode resolution is N direct primary-key lookups, no policy-filtered query. | Performance — landing is the first page every visitor loads. | Query plan review; existing indexes reused, no new index required. | Existing DB indexes | FR-054-09 |
| NFR-054-07 | New PHP/TS code follows PSR-4, strict comparisons, no `empty()`, snake_case DB/PHP variables, camelCase TS. | Coding conventions. | `vendor/bin/php-cs-fixer fix` + `make phpstan` + `npm run check` pass. | — | AGENTS.md |
| NFR-054-08 | All new frontend code lives under `resources/js/v8/**`; `resources/js/v7/**` is not modified. | Scope — v8 is the active frontend target. | Code review: no diffs under `resources/js/v7/`. | — | Goal 10 |
| NFR-054-09 | No layout/config combination can result in a landing page with zero reachable links into the gallery (or, for `studio`, into the login flow). | UX guardrail — avoid dead-end pages. | Manual check across all layout × config-flag combinations: at least one "enter gallery"/"login" affordance is always present and enabled. | FR-054-13..17 | Non-Goals |
| NFR-054-10 | `studio`'s "Client Login" CTA reuses the existing `login` route and `LoginForm.vue` flow exactly as-is — no new authentication mechanism, session handling, or credential system. | Scope control. | Code review: `LandingStudio.vue`'s CTA is a plain `RouterLink` to the existing `login` route. | Existing `login` route | Non-Goals |

## UI / Interaction Mock-ups

### Layout 1 — `classic` (default, unchanged)

```
┌──────────────────────────────────────────────────────────┐
│ [logo]                                     [Gallery →]    │  ← fixed header/menu
│                                                             │
│                 (intro splash, 4s, optional)                │
│                                                             │
│           ░░░░░░░░ full-bleed background ░░░░░░░░           │
│                                                             │
│                    ACCESS GALLERY  ›››                      │  ← position: center (default)
│                                                             │
│  [social icons]        © copyright        [extra links]    │  ← footer
└──────────────────────────────────────────────────────────┘
```

### Layout 2 — `portfolio` (SE, scrollable, multi-section)

```
┌──────────────────────────────────────────────────────────┐
│ [logo]      Portfolio · Contact · [extra nav links] · Gallery│ ← sticky nav
├──────────────────────────────────────────────────────────┤
│           ░░░░ hero background ░░░░                        │
│  "A gallery of moments"                                    │  ← hero text, 5 positions
│  Subtitle text                     [ Enter gallery ]        │
├──────────────────────────────────────────────────────────┤
│  ABOUT                                                       │  ← optional
│  Free-text philosophy / description block                   │
├──────────────────────────────────────────────────────────┤
│  RECENT WORK                                                 │  ← optional
│  [cover] [cover] [cover] [cover] [cover] [cover]             │    automatic: N latest public albums
│                                                                │    manual: admin-curated photos/albums
├──────────────────────────────────────────────────────────┤
│  [social icons]   © copyright   [extra footer links]         │
└──────────────────────────────────────────────────────────┘
```

### Layout 3 — `minimal` (SE, centered card)

```
┌──────────────────────────────────────────────────────────┐
│                        [ logo ]                             │
│                     Site Title                               │
│                   Site subtitle text                         │
│              (optional short about text)                     │
│                  [  Enter gallery  ]                          │
│           [social icons]   [extra links]                     │
└──────────────────────────────────────────────────────────┘
```

### Layout 4 — `studio` (SE, client-login-first)

```
┌──────────────────────────────────────────────────────────┐
│ [logo]                                                      │
│           ░░░░ hero background (optional) ░░░░              │
│                    Welcome back                             │
│              [   Client Login   ]        ← primary CTA      │
│                  View public gallery →   ← secondary, fixed  │
│           [social icons]   [extra links]                     │
└──────────────────────────────────────────────────────────┘
```

### Admin — `LandingConfig.vue`

```
┌───────────────────────────────────────────────────────────────────────┐
│  Landing Page Configuration                                             │
├───────────────────────────────────────────────────────────────────────┤
│  [ Settings ]   Links   Featured                        ← UTabs         │
├───────────────────────────────────┬───────────────────────────────────┤
│  SETTINGS (form)                   │  LIVE PREVIEW                      │
│  ┌─ Layout & Structure ─────────┐  │  ┌───────────────────────────┐    │
│  │ Layout:  [ Classic       ▼]  │  │  │  scaled-down live render  │    │
│  │           ↳ Portfolio/Minimal│  │  │  of the selected layout,  │    │
│  │             /Studio: SE badge│  │  │  updates instantly on any │    │
│  │           (disabled if non-SE)│  │  │  field change, no Save    │    │
│  │ Intro splash: [x] Enabled    │  │  │  needed                   │    │
│  └───────────────────────────────┘  │  │                           │    │
│  ┌─ Hero ────────────────────────┐  │  └───────────────────────────┘    │
│  │ Text position: [ Center   ▼] │  │                                   │
│  │ Text color:  [ ⬤ picker    ]  │  │                                   │
│  │ Text opacity: [====|----] 80%│  │                                   │
│  │ Animation: [ Classic fade ▼] │  │                                   │
│  │             ↳ premium: SE     │  │                                   │
│  │ CTA text: [ blank = default ]│  │                                   │
│  └───────────────────────────────┘  │                                   │
│  ┌─ Content ─────────────────────┐  │                                   │
│  │ About: [ ] Enabled  Text:[__] │  │                                   │
│  └───────────────────────────────┘  │                                   │
│  [ Save ]                            │                                   │
├───────────────────────────────────┴───────────────────────────────────┤
│  These 12 settings are also editable from the flat Settings list.       │
└───────────────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────┐
│  Settings    [ Links ]   Featured                              │
├─────────────────────────────────────────────────────────────┤
│                                                    [+ Add link]│
│  ⋮⋮ Instagram   instagram.com/...   nav+footer   ●  [Edit][Del]│
│  ⋮⋮ Blog        example.com/blog    nav          ●  [Edit][Del]│
│  ⋮⋮ Press Kit   example.com/press   footer       ○  [Edit][Del]│
└─────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────┐
│  Settings    Links   [ Featured ]                              │
├─────────────────────────────────────────────────────────────┤
│  Featured content:  [x] Enabled  (SE)                          │
│  Mode:  ( ) Automatic (latest N public albums)                 │
│         (•) Manual — curate specific photos/albums              │
│  Count (automatic mode only): [24]  (3-100)                    │
│  [ Search photos/albums...                        ] [+ Add]    │
│  ⋮⋮ 📷 "Sunset over the bay"          ●  [Del]                  │
│  ⋮⋮ 📁 "Iceland 2026"                 ●  [Del]                  │
└─────────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-054-01 | Fresh/unmodified install — landing page is pixel-for-pixel identical to pre-feature output. |
| S-054-02 | SE install sets `landing_layout=portfolio` — visitor sees the portfolio layout. |
| S-054-03 | Non-SE install sets `landing_layout=portfolio` — visitor sees `classic` (silent fallback). |
| S-054-04 | SE install sets `landing_layout=minimal` — visitor sees the centered-card layout. |
| S-054-05 | `landing_intro_screen_enabled=false` on `classic` — hero visible immediately, no splash. |
| S-054-06 | `landing_intro_screen_enabled=false` on `portfolio` — hero visible immediately; no-op on `minimal`/`studio`. |
| S-054-07 | `landing_hero_text_position=bottom_right` on `portfolio` — hero text renders bottom-right. |
| S-054-08 | SE install sets `landing_animation_preset=parallax_scroll` — sections fade/slide in on scroll. |
| S-054-09 | Non-SE install sets `landing_animation_preset=zoom_in` — falls back to `classic_fade`. |
| S-054-10 | Browser reports `prefers-reduced-motion: reduce` — zero animations fire regardless of server-resolved preset. |
| S-054-11 | `landing_about_enabled=true` with text — renders on `portfolio`/`minimal`; never on `classic`. |
| S-054-12 | `landing_featured_items_mode=automatic`, `count=6` (SE), 20 public albums exist — the 6 most recent appear. |
| S-054-13 | Same as S-054-12 but only 2 public albums exist — shows 2, no placeholders. |
| S-054-14 | Same as S-054-12 but 0 public albums exist — section omitted. |
| S-054-15 | Admin creates 3 `LandingLink` rows (placements `nav`, `footer`, `both`), 1 disabled — public page shows only the 2 enabled links in the correct area(s). |
| S-054-16 | Admin reorders links via the Reorder endpoint — public order reflects the change. |
| S-054-17 | Non-admin calls any `/api/v2/LandingLink*` or `/api/v2/LandingFeaturedItem*` route — 403. |
| S-054-18 | Admin saves `landing_featured_items_count=150` — rejected (max 100); saves `2` — rejected (min 3). |
| S-054-19 | v7 (`resources/js/v7/views/Landing.vue`) renders today's static classic page regardless of any new config. |
| S-054-20 | SE install sets `landing_layout=studio` — primary CTA links to `/login`; secondary link to the public gallery is visible. |
| S-054-21 | Non-SE install sets `landing_layout=studio` — falls back to `classic`. |
| S-054-22 | `landing_cta_text` set — primary CTA shows the configured text on whichever layout is active. |
| S-054-23 | `landing_cta_text` empty — `classic`/`portfolio`/`minimal` show "Access Gallery"; `studio` shows "Client Login". |
| S-054-24 | `contact_form_enabled=true` — "Contact" link appears in `portfolio`'s nav and `minimal`'s footer, navigating to `/contact`; absent when `false`; never on `classic`/`studio`. |
| S-054-25 | `portfolio` renders — scroll-down indicator appears between hero and the next section; under reduced motion it's present and clickable but static. |
| S-054-26 | `landing_featured_items_mode=manual` (SE) with 2 photos + 1 album added, one disabled — public page shows only the 2 enabled items, mixed types, in order. |
| S-054-27 | Manual mode with a curated item whose underlying photo/album was deleted — silently skipped on next load. |
| S-054-28 | Manual mode with zero enabled items — section renders nothing. |
| S-054-29 | `landing_hero_text_color` set to a custom hex — hero headline/subtitle render in that color on all four layouts; left empty — renders the default `#ffffff`. |
| S-054-30 | `landing_hero_text_opacity=40` — hero headline/subtitle render at 40% opacity; CTA button opacity is unaffected; default `100` — fully opaque, matching pre-feature output. |

## Test Strategy

- **Core/Application:** Unit tests for `LandingPageResource`'s resolution methods (layout/animation SE fallback, automatic-mode featured-content query, manual-mode featured-item resolution incl. graceful-skip-on-missing), mirroring existing `resolveBackgroundUrl` tests from Feature 025.
- **REST:** Feature tests for the extended `GET /api/Init::landing` payload and the full `LandingLink`/`LandingFeaturedItem` CRUD + Reorder endpoints (admin-only, validation, ordering).
- **UI (JS):** No JS test runner exists in this repo — verification is manual/browser-based for the 4 layout components and `LandingConfig.vue` (all 3 tabs), per each Branch & Scenario Matrix row.
- **Security:** Tests confirming CRUD is admin-only, and that automatic-mode featured content never surfaces private content while manual mode's admin-trusted exception is deliberate and tested as such.
- **Regression:** Existing landing-page tests (if any) re-run unmodified against the `classic` default.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-054-01 | `App\Models\LandingLink` — ULID PK, `label`, `url`, `placement` (`LandingLinkPlacement`), `open_in_new_tab`, `sort_order`, `enabled`, timestamps. Mirrors `App\Models\Webhook`'s shape. | core, application |
| DO-054-02 | `LandingPageResource` additions: `layout` (effective `LandingLayoutType`), `intro_screen_enabled`, `hero_text_position` (effective `LandingTextPosition`), `hero_text_color`, `hero_text_opacity`, `animation_preset` (effective `LandingAnimationPreset`), `about_enabled`, `about_text`, `featured_items_enabled`, `featured_items_mode` (effective `LandingFeaturedItemsMode`), `featured_items: LandingFeaturedItemResource[]`, `links: LandingLinkResource[]`, `cta_text`. | REST, UI |
| DO-054-03 | `LandingFeaturedItemResource` — `item_type` (`"photo"`\|`"album"`), `id`, `title`, `thumb_url`, `url`, `num_photos?` (album only). | REST, UI |
| DO-054-04 | `App\Models\LandingFeaturedItem` — ULID PK, `item_type` (`LandingFeaturedItemType`), `item_id`, `sort_order`, `enabled`, timestamps. Mirrors `LandingLink`'s shape. | core, application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-054-01 | GET `/api/Init::landing` | Existing endpoint (Feature 025); response extended with DO-054-02's fields. | Additive fields only. |
| API-054-02 | REST `GET /api/v2/LandingLink` | List all `LandingLink` rows (admin). | Admin-only |
| API-054-03 | REST `POST /api/v2/LandingLink` | Create a `LandingLink` (admin). | Admin-only |
| API-054-04 | REST `GET /api/v2/LandingLink/{landingLink}` | Show one (admin). | Admin-only |
| API-054-05 | REST `PUT /api/v2/LandingLink/{landingLink}` | Full update (admin). | Admin-only |
| API-054-06 | REST `PATCH /api/v2/LandingLink/{landingLink}` | Partial update (admin). | Admin-only |
| API-054-07 | REST `DELETE /api/v2/LandingLink/{landingLink}` | Hard delete (admin). | Admin-only |
| API-054-08 | REST `PATCH /api/v2/LandingLink/Reorder` | Bulk `sort_order` update, full-list-resync contract (FR-054-11). | Admin-only |
| API-054-09 | REST `GET /api/v2/LandingFeaturedItem` | List all rows (admin). | Admin-only |
| API-054-10 | REST `POST /api/v2/LandingFeaturedItem` | Create (admin). | Admin-only; validates `item_id` |
| API-054-11 | REST `GET /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Show one (admin). | Admin-only |
| API-054-12 | REST `PUT /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Full update (admin). | Admin-only |
| API-054-13 | REST `PATCH /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Partial update (admin). | Admin-only |
| API-054-14 | REST `DELETE /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Hard delete (admin). | Admin-only |
| API-054-15 | REST `PATCH /api/v2/LandingFeaturedItem/Reorder` | Bulk `sort_order` update, same contract as API-054-08. | Admin-only |
| API-054-16 | GET `/api/v2/Search` | Existing endpoint (Feature 027/028), reused by the Featured tab's picker. | No change — reused as-is |

### Database Migrations

| ID | Description |
|----|-------------|
| MIG-054-01 | Add 12 new scalar config rows: `landing_layout` (enum, default `classic`), `landing_intro_screen_enabled` (bool, default `true`), `landing_hero_text_position` (enum, default `center`), `landing_hero_text_color` (`type_range: 'color'`, default `''`), `landing_hero_text_opacity` (int, default `100`, range `0-100`), `landing_animation_preset` (enum, default `classic_fade`), `landing_about_enabled` (bool, default `false`), `landing_about_text` (text, default `''`), `landing_featured_items_enabled` (bool, default `false`), `landing_featured_items_mode` (enum `automatic`\|`manual`, default `automatic`), `landing_featured_items_count` (int, default `24`, range `3-100`), `landing_cta_text` (string, default `''`). Filed under the `Mod Welcome` category, added to `ConfigIntegrity`'s whitelist with `type`/`type_range` metadata. |
| MIG-054-02 | `CREATE TABLE landing_links` — ULID `id` (primary), `label` (string 255), `url` (string 2048), `placement` (string 20), `open_in_new_tab` (bool, default `true`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), `created_at`/`updated_at`. Indexes on `enabled`, `placement`. |
| MIG-054-03 | `CREATE TABLE landing_featured_items` — ULID `id` (primary), `item_type` (string 10), `item_id` (string), `sort_order` (int, default `0`), `enabled` (bool, default `true`), `created_at`/`updated_at`. Indexes on `enabled`, `item_type`. |

### Translation Keys

| ID | Key | Description |
|----|-----|--------------|
| TRANS-054-01 | `all_settings.details.landing_layout` | Description + per-value labels for the layout dropdown. |
| TRANS-054-02 | `all_settings.details.landing_intro_screen_enabled` | Description for the intro-splash toggle. |
| TRANS-054-03 | `all_settings.details.landing_hero_text_position` | Description + per-value labels for the position dropdown. |
| TRANS-054-12 | `all_settings.details.landing_hero_text_color` / `landing_hero_text_opacity` | Descriptions for the text color picker and opacity slider. |
| TRANS-054-04 | `all_settings.details.landing_animation_preset` | Description + per-value labels for the animation dropdown. |
| TRANS-054-05 | `all_settings.details.landing_about_enabled` / `landing_about_text` | Descriptions for the about-block fields. |
| TRANS-054-06 | `all_settings.details.landing_featured_items_enabled` / `landing_featured_items_mode` / `landing_featured_items_count` | Descriptions for the featured-content fields (SE-flagged in UI copy). |
| TRANS-054-07 | `all_settings.details.landing_cta_text` | Description for the CTA-text override field. |
| TRANS-054-08 | `landing_link.*` (new file, mirrors `webhook.php`) | Admin CRUD labels for the Links tab. |
| TRANS-054-09 | `landing_featured_item.*` (new file, mirrors `landing_link.php`) | Admin CRUD labels for the Featured tab. |
| TRANS-054-10 | `landing.portfolio.*`, `landing.minimal.*`, `landing.studio.*` (extend `landing.php`) | Frontend copy for the layouts. |
| TRANS-054-11 | `landing.client_login`, `landing.view_public_gallery`, `landing.contact` | `studio`'s CTA labels and the "Contact" link label. |

## Telemetry & Observability

No new telemetry events — matches Feature 025's precedent. SE-gating fallbacks (FR-054-02, FR-054-06, FR-054-27) are silent by design; no log entry on fallback.

## Documentation Deliverables

- Update `docs/specs/4-architecture/roadmap.md` — Feature 054 in Active Features.
- Update `docs/specs/_current-session.md`.
- Admin-facing docs (if a user-facing settings guide exists) noting the new layout picker, SE-gated fields, and `LandingConfig.vue`.

## Fixtures & Sample Data

Existing test helpers (photos/albums with public/private access permissions) are sufficient for automatic-featured-content tests. A `LandingLinkFactory` and `LandingFeaturedItemFactory` (both mirror `WebhookFactory`) are needed for CRUD tests.

## Spec DSL

```yaml
domain_objects:
  - id: DO-054-01
    name: LandingLink
    fields:
      - name: label
        type: string
        constraints: "required, <=255 chars"
      - name: url
        type: string
        constraints: "required, valid absolute URL, <=2048 chars"
      - name: placement
        type: LandingLinkPlacement
        constraints: "nav | footer | both"
      - name: open_in_new_tab
        type: boolean
      - name: sort_order
        type: integer
      - name: enabled
        type: boolean

  - id: DO-054-04
    name: LandingFeaturedItem
    fields:
      - name: item_type
        type: LandingFeaturedItemType
        constraints: "photo | album"
      - name: item_id
        type: string
        constraints: "must reference an existing Photo or Album matching item_type"
      - name: sort_order
        type: integer
      - name: enabled
        type: boolean

routes:
  - id: API-054-01
    method: GET
    path: /api/Init::landing
    response_fields:
      - layout: LandingLayoutType
      - intro_screen_enabled: boolean
      - hero_text_position: LandingTextPosition
      - hero_text_color: string
      - hero_text_opacity: integer
      - animation_preset: LandingAnimationPreset
      - about_enabled: boolean
      - about_text: string
      - featured_items_enabled: boolean
      - featured_items_mode: LandingFeaturedItemsMode
      - featured_items: LandingFeaturedItemResource[]
      - links: LandingLinkResource[]
      - cta_text: string
  - id: API-054-02
    method: GET
    path: /api/v2/LandingLink
  - id: API-054-03
    method: POST
    path: /api/v2/LandingLink
  - id: API-054-04
    method: GET
    path: /api/v2/LandingLink/{landingLink}
  - id: API-054-05
    method: PUT
    path: /api/v2/LandingLink/{landingLink}
  - id: API-054-06
    method: PATCH
    path: /api/v2/LandingLink/{landingLink}
  - id: API-054-07
    method: DELETE
    path: /api/v2/LandingLink/{landingLink}
  - id: API-054-08
    method: PATCH
    path: /api/v2/LandingLink/Reorder
  - id: API-054-09
    method: GET
    path: /api/v2/LandingFeaturedItem
  - id: API-054-10
    method: POST
    path: /api/v2/LandingFeaturedItem
  - id: API-054-11
    method: GET
    path: /api/v2/LandingFeaturedItem/{landingFeaturedItem}
  - id: API-054-12
    method: PUT
    path: /api/v2/LandingFeaturedItem/{landingFeaturedItem}
  - id: API-054-13
    method: PATCH
    path: /api/v2/LandingFeaturedItem/{landingFeaturedItem}
  - id: API-054-14
    method: DELETE
    path: /api/v2/LandingFeaturedItem/{landingFeaturedItem}
  - id: API-054-15
    method: PATCH
    path: /api/v2/LandingFeaturedItem/Reorder

migrations:
  - id: MIG-054-01
    description: "Add 12 new landing-page scalar configs (layout, intro screen, hero text position/color/opacity, animation preset, about block, featured-items enabled/mode/count, CTA text override), filed under the Mod Welcome category."
  - id: MIG-054-02
    description: "Create landing_links table."
  - id: MIG-054-03
    description: "Create landing_featured_items table."

enums:
  - name: LandingLayoutType
    values: [classic, portfolio, minimal, studio]
  - name: LandingTextPosition
    values: [top_left, top_right, bottom_left, bottom_right, center]
  - name: LandingAnimationPreset
    values: [none, classic_fade, zoom_in, parallax_scroll, slide_reveal]
  - name: LandingLinkPlacement
    values: [nav, footer, both]
  - name: LandingFeaturedItemsMode
    values: [automatic, manual]
  - name: LandingFeaturedItemType
    values: [photo, album]

translation_keys:
  - id: TRANS-054-01
    key: all_settings.details.landing_layout
  - id: TRANS-054-02
    key: all_settings.details.landing_intro_screen_enabled
  - id: TRANS-054-03
    key: all_settings.details.landing_hero_text_position
  - id: TRANS-054-04
    key: all_settings.details.landing_animation_preset
  - id: TRANS-054-08
    key: landing_link.*
  - id: TRANS-054-09
    key: landing_featured_item.*

ui_states:
  - id: UI-054-01
    description: LandingConfig.vue's Layout dropdown shows Portfolio/Minimal/Studio as disabled and badged "SE" when the install is not on Lychee SE; a previously-stored SE-only value still displays as the current selection.
  - id: UI-054-02
    description: LandingConfig.vue's Animation dropdown shows the 3 premium presets as disabled and badged "SE" when not on Lychee SE.
  - id: UI-054-03
    description: LandingConfig.vue's Links tab table row drag-reorder updates sort_order via API-054-08.
  - id: UI-054-04
    description: Portfolio layout's scroll-down indicator smooth-scrolls to the next rendered section on click.
  - id: UI-054-05
    description: LandingConfig.vue is reachable as an admin tile (group "core") at /admin/landing-config, visible whenever can_edit is true.
  - id: UI-054-06
    description: LandingConfig.vue's Settings tab live preview updates on every field change with no Save required.
```

## Appendix

### Design Notes

- `LandingTextPosition` deliberately duplicates `AlbumTitlePosition`'s 5 values rather than importing it — albums and the landing page are different bounded contexts, and the duplication cost is five string cases.
- `LandingLink.Reorder`/`LandingFeaturedItem.Reorder` use a full-list-resync contract (`{ ids: string[] }`, complete set required, transactional) because no existing bulk-reorder endpoint exists anywhere in this codebase to follow as precedent.
- `LandingConfig.vue`'s Settings tab is modelled on the Watermarker module's `WatermarkPreview.vue` (local-state-then-explicit-Save, live reactive preview, settings that remain visible in the flat generic Settings list) rather than on `NsfwConfig.vue`'s flat-Fieldset/no-preview shape.
- The Featured tab's picker reuses the existing `GET /api/v2/Search` endpoint unmodified; that endpoint already returns private/unpublished content to an authenticated admin session (`AlbumQueryPolicy`/`PhotoQueryPolicy` short-circuit to an unrestricted query when `$user->may_administrate === true`), so manual-mode curation of private content works with no extra backend work.
- `landing_hero_text_color` reuses the existing Theme Colors settings' picker widget (`ColorField.vue`, `type_range: 'color'`, dispatched generically by `ConfigGroup.vue` wherever `config.type === 'color'`) but is consumed differently on the backend than the 7 Theme Colors keys: those feed `App\View\Components\Style::TOKEN_KEYS`, which runs each color through `PaletteGenerator::generatePalette()` to derive a full OKLCH shade ramp for site-wide UI tokens. `landing_hero_text_color` is a single hex value read via `getValueAsString()` and applied directly as CSS `color` — no palette is generated, since it styles one piece of text, not a UI-wide token.

### Follow-ups / Backlog

- A true modular/reorderable section builder (drag sections, not just pick a layout).
- Mosaic/grid-first layout, "coming soon" layout, split-screen editorial layout, background video support.
- Second image slot for the About section; testimonials/client-logos CRUD block; dedicated hero tagline field separate from `landing_title`.
- A curated icon picker, if an icon field is reintroduced to `LandingLink`.
- Sharing the position-class mapping between `AlbumHeaderPanel.vue` and the landing composable via one shared utility.

---

*Last updated: 2026-08-11*
