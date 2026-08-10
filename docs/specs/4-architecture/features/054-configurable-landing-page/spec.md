# Feature 054 – Configurable Landing Page

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-08-10 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/054-configurable-landing-page/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/054-configurable-landing-page/tasks.md` |
| Roadmap entry | Active Features |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

The landing page (`resources/js/v8/views/Landing.vue`) is currently a single hard-coded layout: fixed full-screen background, a timed intro splash, a centered "access gallery" call-to-action, a fixed header/menu, and a footer with social icons. Every visual choice — whether the intro splash plays, where the hero text sits, which animations run, and what content appears — is baked into the template. Operators who want a different visual identity (e.g. a portfolio-style page with a top nav bar, an "about/philosophy" blurb, and a preview of recent work before the gallery link) currently cannot get one without forking the component.

This feature turns the landing page into a **layout picker** with four selectable, admin-configurable layouts (`classic`, `portfolio`, `minimal`, `studio`), plus a set of cross-cutting knobs — content-block toggles, hero text position, animation preset, configurable CTA copy, curated/automatic featured content, and an admin-manageable list of extra links — that apply wherever a layout supports them. It reuses the existing config system (`Config`/`ConfigManager`, DB-backed key/value settings, read/written through the same `SettingsService` API the generic settings list already uses), the existing dynamic-background resolution from Feature 025, the existing hero text-position/color pattern already proven on the album "extended hero" (`AlbumHeaderPanel.vue`, `AlbumTitlePosition`), and the existing global custom CSS/JS mechanism (Settings → `dist/user.css`/`dist/custom.js`, loaded on every page via `<x-meta>` already — no landing-specific styling mechanism is added). Admin configuration is consolidated into one dedicated, curated page (`LandingConfig.vue`) structurally mirroring Feature 045's `NsfwConfig.vue` — grouped `Fieldset` sections across tabs for settings, extra links, and featured content, replacing the entire `Mod Welcome` settings category's presence in the flat generic list.

Affected modules: **Config** (`App\Models\Config`, `App\Http\Middleware\ConfigIntegrity`), **Landing Page** (`App\Http\Resources\GalleryConfigs\LandingPageResource`), **new Admin CRUD** (`App\Models\LandingLink`, `App\Models\LandingFeaturedItem`, `App\Http\Controllers\Admin\LandingLinkController`, `App\Http\Controllers\Admin\LandingFeaturedItemController`), **new Admin UI** (`resources/js/v8/views/admin/LandingConfig.vue`), **Frontend v8 only** (`resources/js/v8/views/Landing.vue` and new layout components).

## Goals

1. Admins choose one of four named layouts (`classic` — today's page, unchanged by default; `portfolio` — a scrollable, multi-section portfolio-style page; `minimal` — centered single-card page; `studio` — client-login-first page for studios whose visitors are mostly returning clients, not public browsers) via a `landing_layout` config.
2. Admins can enable/disable the intro splash screen independently of the chosen layout.
3. Admins can choose where the hero headline/subtitle sits (5 positions), reusing the UX pattern already shipped for album hero titles.
4. Admins can choose an animation preset for the landing page, including a "no animation" option for reduced-motion/perf-sensitive installs; the browser's `prefers-reduced-motion` setting always wins client-side.
5. Admins can manage an arbitrary, ordered list of extra links (nav and/or footer placement) without touching the fixed social-media fields.
6. Admins can opt in to content blocks — a free-text "about" block, and a featured-content section that defaults to fully automatic (most-recently-published public albums) but can be switched to fully manual curation of specific photos and/or albums.
7. Admins can override the primary call-to-action button's label, and can surface the already-existing Contact Form (Feature 022) as a nav/footer entry point on `portfolio`/`minimal`, without any new backend functionality.
8. `portfolio`, `minimal`, and `studio` layouts, and non-default animation presets, are exclusive to Lychee SE (Supporter Edition); `classic` and the current default animation remain free forever, matching how Feature 039 (white-label) gates premium branding behind SE while Feature 025 (dynamic backgrounds) stayed free.
9. Zero behavioural or visual change for any existing installation that has not touched the new settings (all new flags default to today's exact behaviour).
10. All new frontend work targets `resources/js/v8/**` (Nuxt UI) only; the legacy `resources/js/v7/**` landing page is left untouched, matching the v8-only precedent set by Feature 051.
11. All landing-page settings — old and new — live in exactly one dedicated admin page instead of being split across a flat settings list and one or more standalone CRUD routes.

## Non-Goals

- **v7 (legacy PrimeVue) parity.** `resources/js/v7/views/Landing.vue` keeps rendering today's classic page unconditionally, forever — no new v7 work. Explicit scope decision (see Appendix: Resolved Scope Decisions).
- **A full modular/reorderable section page-builder.** Four named layouts were chosen over an arbitrary drag-and-drop section composer; that shape is a much larger, separate feature and is left as a documented Follow-up.
- **Further named layouts beyond the four in scope** — mosaic/grid-first, split-screen editorial, "coming soon," and cinematic/video-hero were all considered (see Appendix) and deliberately deferred to backlog rather than added here.
- **A new client-specific authentication or credential system.** `studio`'s "Client Login" CTA reuses the existing `login` route/`LoginForm.vue` flow as-is (NFR-054-10) — no per-client accounts, magic links, or new session handling are introduced.
- **A landing-specific custom CSS/JS mechanism.** Lychee already has a global one: `Settings` lets an admin save raw CSS/JS, persisted to `dist/user.css`/`dist/custom.js` (`SettingsController::setCSS()`/`setJS()`) and loaded on **every** page — including every landing layout — via `<x-meta>` in the shared `vueapp.blade.php` shell (`Meta::$user_css_url`/`$user_js_url`). This feature adds no new styling/scripting field; admins wanting bespoke landing styling reuse the existing global one and target `#landing`/the active layout's markup themselves — it is on the admin to figure out selectors that work for their configuration. See Appendix.
- **Surfacing the Contact Form on `classic` or `studio`.** Contact surfacing (FR-054-23) is scoped to `portfolio`/`minimal` only, matching the original proposal; `classic` stays visually frozen (existing Non-Goal) and `studio` visitors are treated as already-known clients who don't need a general inquiry form.
- **Ever hiding the call-to-action into the gallery entirely.** Every layout must always render at least one visible, reachable link into the gallery — no configuration path may produce a dead-end landing page.
- **Per-user or authenticated/personalised landing pages.** The landing page remains public-only, matching Feature 025's Non-Goal.
- **Slideshow or multiple rotating backgrounds**, and **video backgrounds.** Backgrounds remain single static image URLs per orientation, resolved exactly as Feature 025 already does; this feature does not touch background resolution logic.
- **New billing/licensing mechanics.** SE-gating reuses the existing supporter/license verification (`request()->verify()->is_supporter()`) already used by `InitConfig::is_se_enabled` — no new purchase flow.
- **Per-locale authoring of `landing_about_text` or extra-link labels.** Both are single global strings, matching the existing single-language `footer_additional_text` field — translators are not expected to localise admin-authored free text.
- **Reordering/renaming the existing footer social-media fields** (`sm_facebook_url`, etc.) — untouched; extra links are additive.
- **A curated icon picker for `LandingLink.icon`.** The field is a free-text Iconify/Lucide identifier the admin types directly (e.g. `lucide:instagram`), not a visual picker component — matches the "it's on the admin to configure things" direction given for this feature generally.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-054-01 | New enum config `landing_layout` with values `classic`, `portfolio`, `minimal`, `studio` (new `App\Enum\LandingLayoutType`). Default `classic`. | Admin selects a layout in Settings; value persisted like any other enum config. | Config validation restricts to the 4 enum values (same mechanism as `landing_background_landscape_mode`). | Invalid value rejected at config-update time. | — | User request 2026-08-10 |
| FR-054-02 | `LandingPageResource` resolves an **effective** layout: if the stored `landing_layout` is `portfolio`, `minimal`, or `studio` but the requester is not on Lychee SE (`request()->verify()->validate() && request()->verify()->is_supporter()` is false), the effective layout silently falls back to `classic`. `classic` is always available. | SE install sees the configured layout; non-SE install always sees `classic` regardless of stored config. | — | No exception, no error surfaced to the visitor — same fail-safe shape as `InitConfig::is_white_label_enabled` (FR-039-02). | — | SE-gating decision 2026-08-10 |
| FR-054-03 | New bool config `landing_intro_screen_enabled`, default `true`. Controls whether the full-screen animated splash (logo/title pop-in, current `#intro` block) plays before the hero is shown. Applies to `classic` and `portfolio`; `minimal` has no splash by design (flag ignored there). | Splash renders/skips per the flag; when disabled, the hero is visible immediately on load. | — | — | — | User request 2026-08-10 ("enable disable the first screen") |
| FR-054-04 | New enum config `landing_hero_text_position` with 5 values — `top_left`, `top_right`, `bottom_left`, `bottom_right`, `center` (new `App\Enum\LandingTextPosition`, deliberately a landing-scoped enum distinct from `App\Enum\AlbumTitlePosition` — see Appendix). Default `center` (matches current visual center-screen CTA). Controls placement of the hero headline/subtitle/CTA block within the hero viewport for `classic` and `portfolio`. | Hero text block renders at the configured corner/center using the same Tailwind position-class mapping already proven in `AlbumHeaderPanel.vue`'s `POSITION_CLASSES`. | Config validation restricts to the 5 enum values. | Invalid value rejected at config-update time. | — | User request 2026-08-10 ("similar to the pro position of the text on extended hero album") |
| FR-054-05 | New enum config `landing_animation_preset` with values `none`, `classic_fade`, `zoom_in`, `parallax_scroll`, `slide_reveal` (new `App\Enum\LandingAnimationPreset`). Default `classic_fade` (= today's exact keyframes: fade/slide header+menu+footer, pop-in intro, pulsing chevrons). | Selected preset governs which CSS animation classes the frontend applies. | Config validation restricts to the 5 enum values. | Invalid value rejected at config-update time. | — | User request 2026-08-10 ("different kind of animations") |
| FR-054-06 | `LandingPageResource` resolves an **effective** animation preset: `none` and `classic_fade` are available to every install; `zoom_in`, `parallax_scroll`, `slide_reveal` require SE (same check as FR-054-02) and silently fall back to `classic_fade` otherwise. | SE install gets the configured preset; non-SE install configured with a premium preset gets `classic_fade`. | — | No exception, fail-safe like FR-054-02. | — | SE-gating decision 2026-08-10 |
| FR-054-07 | On the frontend, when the browser reports `prefers-reduced-motion: reduce`, the effective animation preset is forced to `none` client-side regardless of the resolved server value. | Reduced-motion users see zero animation on every layout. | — | — | — | Accessibility (WCAG 2.3.3), NFR-054-04 |
| FR-054-08 | New bool config `landing_about_enabled` (default `false`) and new text config `landing_about_text` (default `''`, admin-authored HTML like `footer_additional_text`). When enabled, `portfolio` and `minimal` render an "about" text block; `classic` never renders it (classic's single-screen shape is frozen, see Non-Goals). | Block renders `landing_about_text` verbatim (same trust model as `footer_additional_text` — no sanitizer exists for that field today either) when both layout supports it and the flag is on. | — | Empty `landing_about_text` with the flag on renders nothing (graceful, no placeholder text). | — | User request 2026-08-10 ("what info to display") |
| FR-054-09 | New bool config `landing_show_stats` (default `false`, SE-gated per FR-054-02's check). When effectively enabled, `LandingPageResource` exposes `public_photo_count` and `public_album_count`, computed via `PhotoQueryPolicy::applySearchabilityFilter($query, null, [])` / `AlbumQueryPolicy::applyVisibilityFilter($query, null)` counts. `portfolio` and `minimal` may display these; `classic` never does. | Counts reflect only publicly visible content, matching NFR-025-03's policy usage. | — | If the flag is effectively disabled (config off, or non-SE fallback), the fields are omitted/null and no query runs. | — | User request 2026-08-10 ("what info to display") |
| FR-054-10 | New bool config `landing_featured_items_enabled` (default `false`, SE-gated) and new enum config `landing_featured_items_mode` with values `automatic`, `manual` (new `App\Enum\LandingFeaturedItemsMode`, default `automatic`). In `automatic` mode, `LandingPageResource` exposes an array of up to `landing_featured_items_count` (int, default `6`, range 3–12) featured **albums** — public albums ordered `published_at DESC, created_at DESC, id DESC` via `AlbumQueryPolicy::applyVisibilityFilter($query, null)` (reusing the exact query shape of Feature 025's `resolveLatestAlbumCover`), each with `item_type: "album"`, `id`, `title`, `thumb_url`, `num_photos`. Only `portfolio` renders this section; `classic` and `minimal` never do (keeps `minimal` minimal). `manual` mode's resolution is FR-054-29. | Automatic mode: section shows up to N most-recently-published public albums with cover thumbnails, linking into the gallery. | `landing_featured_items_count` validated to the 3–12 range at config-update time; `landing_featured_items_mode` restricted to the 2 enum values. | Automatic mode with fewer than N public albums → shows however many exist (no padding/placeholders); zero public albums → section renders nothing. | — | User request 2026-08-10; refined 2026-08-10 (automatic default + manual curation) |
| FR-054-11 | New model `App\Models\LandingLink` (ULID PK, mirrors `App\Models\Webhook`'s shape) backed by a new `landing_links` table: `label`, `url`, `icon` (nullable free-text Iconify/Lucide name, e.g. `lucide:instagram` — admin-typed, no picker UI, see Non-Goals), `placement` (enum `nav`\|`footer`\|`both`, new `App\Enum\LandingLinkPlacement`), `open_in_new_tab` (bool, default `true`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), timestamps. When `icon` is null/empty, the frontend renders a generic default icon (`lucide:link`) instead of no icon. | Admin can define any number of extra links beyond the fixed 5 social-media fields. | `url` must be a valid absolute URL; `label` required, ≤255 chars; `placement`/enum fields restricted to allowed values. | 422 with field errors on invalid input. | — | User request 2026-08-10 ("possibility to add extra links") |
| FR-054-12 | New admin-only REST CRUD for `LandingLink`: `GET/POST /api/v2/LandingLink`, `GET/PUT/PATCH/DELETE /api/v2/LandingLink/{landingLink}`, plus `PATCH /api/v2/LandingLink/Reorder` accepting an ordered array of IDs to bulk-update `sort_order` (mirrors the CRUD shape of `App\Http\Controllers\Admin\WebhookController`, FR-031-01..04). | Admin creates/lists/updates/reorders/deletes links via `LandingConfig.vue`'s Links tab. | Only `is_admin = true` may call these routes (mirrors FR-031-12). | Non-admin → 403; missing link → 404. | — | Mirrors Feature 031 pattern |
| FR-054-13 | `LandingPageResource` includes a `links` array: only `enabled = true` `LandingLink` rows, ordered by `sort_order`, each projected to `{id, label, url, icon, placement, open_in_new_tab}` (no admin-only fields to hide — no secrets involved, unlike webhooks). Available on **every** layout including `classic` (not SE-gated — see Appendix: Resolved Scope Decisions), rendered in the nav area (placement `nav`/`both`) and/or footer area (placement `footer`/`both`). | Enabled links appear in the correct area(s) on every layout; disabled links never appear publicly. | — | Zero links configured → sections render nothing extra (today's behaviour, unchanged). | — | User request 2026-08-10 |
| FR-054-14 | `resources/js/v8/views/Landing.vue` becomes a thin dispatcher: it fetches `LandingPageResource` once, then renders `LandingClassic.vue`, `LandingPortfolio.vue`, or `LandingMinimal.vue` based on the resolved `layout` field. Loading/error handling (redirect to gallery when `landing_page_enable` is false, or on fetch error) is unchanged from today. | Correct layout component mounts for the resolved layout. | — | Unknown/unexpected layout value (should be unreachable given backend enum validation) falls back to `LandingClassic.vue`. | — | Architecture decision 2026-08-10 |
| FR-054-15 | `LandingClassic.vue` contains the **exact** current markup, CSS keyframes, and behaviour of today's `Landing.vue`, parameterised only by `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_animation_preset` (all defaulting to today's fixed behaviour), and the new `links` array appended to the existing menu/footer. With every new config left at its default, `classic` is pixel-for-pixel identical to pre-feature output (S-054-01). | Default install renders unchanged. | — | — | — | NFR-054-01 |
| FR-054-16 | New `LandingPortfolio.vue`: sticky top nav bar (logo + `links` with placement `nav`/`both` + existing "Gallery" link), a hero section (background from existing Feature 025 resolution, headline/subtitle positioned per FR-054-04, CTA into the gallery, animated per FR-054-06), an optional about section (FR-054-08), an optional featured-content section (FR-054-10/FR-054-29, automatic or manual), and a footer (existing `FooterConfig` — social icons, copyright, additional text — plus `links` with placement `footer`/`both`). Sections that are disabled/empty are omitted entirely, not shown blank. | A `portfolio`-configured SE install shows a multi-section, scrollable page: nav → hero → about → featured work → footer. | — | Any content block resolving to nothing (e.g. zero public albums, zero enabled manual items) is simply omitted. | — | User request 2026-08-10 |
| FR-054-17 | New `LandingMinimal.vue`: a single centered card (logo or title/subtitle, optional about text, one CTA button into the gallery, footer `links`/social icons below the fold). No full-bleed background is required (existing background config may still be used as a subtle backdrop); no featured-content section (kept out to preserve the "minimal" intent, see FR-054-10). | A `minimal`-configured SE install shows a compact, distraction-free landing page. | — | — | — | User request 2026-08-10 ("multiple layouts") |
| FR-054-18 | The 11 new scalar configs (`landing_layout`, `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_animation_preset`, `landing_about_enabled`, `landing_about_text`, `landing_show_stats`, `landing_featured_items_enabled`, `landing_featured_items_mode`, `landing_featured_items_count`, `landing_cta_text`) are added to `App\Http\Middleware\ConfigIntegrity`'s whitelist, given `type`/`type_range` metadata, filed under the existing `Mod Welcome` settings category (same category the pre-existing landing keys already live in), and remain readable/writable through the existing generic `SettingsService.getAll()`/`setConfigs()` API (same storage, same endpoints — no new config backend). | Keys are readable/writable via `SettingsService`. | — | — | — | Matches existing `SettingsService` data-driven pattern |
| FR-054-19 | New dedicated admin page `resources/js/v8/views/admin/LandingConfig.vue`, structurally mirroring `resources/js/v8/views/admin/NsfwConfig.vue` (Feature 045): a `UTabs` page with a **"Settings"** tab, a **"Links"** tab, and a **"Featured"** tab. The **Settings** tab covers the **entire `Mod Welcome` settings category** — both the pre-existing landing keys (landing enable/title/subtitle/owner, background mode fields, logos, footer/social/contact-form keys — exact list confirmed against `config_categories`/`SettingsController::getAll()` at implementation time) and the 11 new keys from FR-054-18 — loaded via `SettingsService.getAll()`/saved via `setConfigs()`, laid out in curated `Fieldset` sections (e.g. "General," "Background," "Branding," "Layout & Structure," "Hero," "Content," "Footer & Social") using the same shared `BoolField`/`SelectField` components NSFW's page already uses. The **Links** tab is the `LandingLink` CRUD (list/create/edit/delete/reorder against API-054-02..08). The **Featured** tab is the featured-content configuration and, when `landing_featured_items_mode=manual`, the `LandingFeaturedItem` curation UI (FR-054-28). Registered as an admin tile in `useAdminTiles.ts` (`group: "core"`, alongside `settings`/`design-system`) at route `landing-config` / path `/admin/landing-config`, visible whenever `can_edit`. | Admin manages every landing setting, every extra link, and featured-content curation from one purpose-built page. | — | — | — | User decision 2026-08-10 ("configuration page similar to the NSFW classifier"); expanded 2026-08-10 to full-category absorption |
| FR-054-20 | Existing installations upgrading receive: `landing_layout=classic`, `landing_intro_screen_enabled=true`, `landing_hero_text_position=center`, `landing_animation_preset=classic_fade`, `landing_about_enabled=false`, `landing_show_stats=false`, `landing_featured_items_enabled=false`, `landing_featured_items_mode=automatic`, `landing_cta_text=''`, zero `LandingLink`/`LandingFeaturedItem` rows. Net-zero behavioural change until an admin opts in. | Upgrade produces no visible change to the landing page. | — | — | — | NFR-054-01; backward-compatibility precedent (NFR-025-04) |
| FR-054-21 | New `LandingStudio.vue` layout (SE): the primary hero CTA is a "Client Login" button (`RouterLink` to the existing `login` route — see NFR-054-10, no new auth is built) with label sourced from `landing_cta_text` when set, else the `landing.client_login` translation default. A smaller secondary link into the public gallery (existing `home` route, same link classic/portfolio already expose) renders beneath it. Hero copy otherwise reuses `landing_title`/`landing_subtitle`/`landing_about_text` exactly like the other layouts. | Studio-configured SE install shows client-login-first hero with a secondary public-gallery link. | — | — | — | User decision 2026-08-10 (extra-layout brainstorm) |
| FR-054-22 | `Landing.vue`'s dispatcher (FR-054-14) routes the resolved `layout=studio` to `LandingStudio.vue`; non-SE requesters get the FR-054-02 fallback to `classic` exactly like `portfolio`/`minimal`. | Correct component mounts; non-SE always sees classic. | — | — | — | User decision 2026-08-10 |
| FR-054-23 | When the existing `contact_form_enabled` flag (Feature 022, surfaced today via `FooterConfig`) is `true`, `portfolio` renders a "Contact" nav-bar link and `minimal` renders a "Contact" footer link, both navigating to the existing `/contact` route (Feature 022's dedicated contact-form page). No new contact-form backend/frontend is created — this is wiring only. `classic` and `studio` never render this link (see Non-Goals). | Contact entry point appears on `portfolio`/`minimal` exactly when the contact form is already enabled site-wide. | — | Flag off → link absent, identical to today's behaviour elsewhere. | — | User decision 2026-08-10; reuses Feature 022 |
| FR-054-24 | New string config `landing_cta_text` (default `''`, free tier — not SE-gated). When non-empty, overrides the primary hero CTA button label on whichever layout is active. When empty, each layout falls back to its own existing translation default (`classic`/`portfolio`/`minimal` → `landing.access_gallery`; `studio` → `landing.client_login`, new key). | Configured text appears verbatim on the active layout's primary CTA; unset installs see unchanged copy. | ≤255 chars. | — | — | User decision 2026-08-10 |
| FR-054-25 | `LandingPortfolio.vue` renders a scroll-down indicator (animated chevron) between the hero section and the next rendered section (about, featured content, or footer — whichever comes first). Its animation follows the same `useLandingAnimation`/reduced-motion choke point as FR-054-07/NFR-054-04: static (non-bouncing) but still visible and clickable when reduced motion is requested or `animation_preset=none`. | Indicator present and clickable (smooth-scrolls to the next section) on every `portfolio` render. | — | — | — | User decision 2026-08-10 |
| FR-054-27 | Because `LandingConfig.vue`'s Settings tab (FR-054-19) covers the **entire** `Mod Welcome` category rather than a subset, `Mod Welcome` is filtered out of the flat generic Settings list's category listing (patch to `SettingsController::getAll()`'s visibility logic — same mechanism Feature 052's Q-052-07 resolution already established for a different category) so admins have exactly one place to configure landing settings instead of seeing the same fields twice. This is a deliberate deviation from Feature 045's precedent (NSFW's curated keys stay visible in the flat list too) because NSFW only curates a subset of keys, while this page curates a whole category. | Flat Settings list no longer shows a "Landing page" category; `LandingConfig.vue` is the only place these fields appear. | — | — | — | User decision 2026-08-10 (full-category absorption) |
| FR-054-28 | New model `App\Models\LandingFeaturedItem` (ULID PK, mirrors `App\Models\LandingLink`'s shape) backed by a new `landing_featured_items` table: `item_type` (enum `photo`\|`album`, new `App\Enum\LandingFeaturedItemType`), `item_id` (string — a `Photo.id` or `Album.id` depending on `item_type`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), timestamps. New admin-only REST CRUD mirroring FR-054-12 exactly: `GET/POST /api/v2/LandingFeaturedItem`, `GET/PUT/PATCH/DELETE /api/v2/LandingFeaturedItem/{landingFeaturedItem}`, `PATCH /api/v2/LandingFeaturedItem/Reorder`. The picker UI (`LandingConfig.vue`'s Featured tab) searches existing photos/albums via the already-existing `GET /api/v2/Search` endpoint (Feature 027/028) — no new search backend. | Admin searches for and adds specific photos/albums to an ordered, manually-curated list. | `item_id` must reference an existing `Photo` or `Album` matching `item_type` at write time; `url`-style validation not applicable. | 422 if the referenced photo/album doesn't exist at write time; 403 for non-admin (mirrors NFR-054-05). | — | User decision 2026-08-10 (manual curation) |
| FR-054-29 | When effective `landing_featured_items_mode=manual` (SE required per FR-054-02's check, same fallback shape), `LandingPageResource` resolves the `featured_items` array from `enabled = true` `LandingFeaturedItem` rows ordered by `sort_order`: each row is looked up directly by `item_id` against `Photo`/`Album` **without** a public-visibility policy check — the admin who manually selected the item is trusted/responsible for its appropriateness, exactly mirroring Feature 025's `photo_id` background mode (FR-025-04). If a referenced photo/album no longer exists at resolution time, that row is silently skipped (graceful, no error) — matching this spec's existing fallback conventions. Each resolved item is projected to a unified `{item_type, id, title, thumb_url, url, num_photos?}` (`num_photos` present only for `item_type=album`). | Manual mode: section shows exactly the curated, enabled items in the admin's chosen order, mixing photos and albums freely. | — | Deleted/nonexistent referenced item → skipped silently; zero enabled items → section renders nothing (same as automatic mode with zero public albums). | — | User decision 2026-08-10 (manual curation); mirrors FR-025-04's admin-trusted precedent |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-054-01 | The `classic` layout with all new configs at their defaults is behaviourally and visually identical to today's landing page — no regression. | Backward compatibility for the overwhelming majority of installs that will never touch these settings. | Manual/Selenium comparison of DOM structure + existing landing tests continue to pass unmodified. | FR-054-15, FR-054-20 | User expectation |
| NFR-054-02 | SE-gated fields (`layout`, `animation_preset`, `show_stats`, `featured_items_enabled`/`featured_items_mode`) resolve fail-safe: never throw, never leak the SE-only value to a non-SE requester. | Licensing integrity — mirrors Feature 039's `is_white_label_enabled` fail-safe pattern (FR-039-02). | Unit test: non-SE request with all SE-only configs set to non-default values still returns `classic`/`classic_fade`/no stats/no featured content. | `request()->verify()` | White-label precedent |
| NFR-054-03 | Automatic-mode dynamic content resolution (`public_photo_count`, `public_album_count`, automatic featured albums) uses `PhotoQueryPolicy`/`AlbumQueryPolicy` with `user=null`, so private/unpublished content never appears on the public landing page. Manual-mode featured items (FR-054-29) are the one deliberate exception — same admin-trusted precedent as Feature 025's `photo_id` background mode (FR-025-04): the admin explicitly chose the item, so no policy check runs. | Privacy — same requirement as Feature 025's dynamic backgrounds, with the same documented admin-trust carve-out Feature 025 already established. | Test: automatic mode excludes private album/photo counts and never selects a private album; manual mode successfully resolves an admin-selected private photo/album without a policy check (by design, not a bug). | `App\Policies\PhotoQueryPolicy`, `App\Policies\AlbumQueryPolicy` | NFR-025-03 precedent; FR-025-04 precedent |
| NFR-054-04 | Client-side, `prefers-reduced-motion: reduce` always forces the `none` animation behaviour, regardless of the server-resolved preset. | Accessibility (WCAG 2.3.3, "Animation from Interactions"). | Manual check with OS-level reduced-motion enabled: zero CSS animations fire on any layout. | Frontend `useLandingAnimation` composable | Accessibility requirement |
| NFR-054-05 | `LandingLink` and `LandingFeaturedItem` CRUD endpoints are restricted to `is_admin = true`. | Security — same access control as webhook management (FR-031-12). | Feature test: non-admin request to any `/api/v2/LandingLink*` or `/api/v2/LandingFeaturedItem*` route returns 403. | Existing admin auth middleware | FR-031-12 precedent |
| NFR-054-06 | Stats/automatic-featured-albums queries execute in ≤100ms p95, using indexed columns (`published_at`, `created_at`) and a `LIMIT`. Manual-mode resolution is N direct primary-key lookups (N = enabled `LandingFeaturedItem` count, typically small), no policy-filtered query at all. | Performance — landing page is the first thing every visitor loads. | Query plan review; existing `published_at`/`created_at` indexes reused (no new index required, matches Feature 025's NFR-025-01 which already covers these columns). | Existing DB indexes | NFR-025-01 precedent |
| NFR-054-07 | New PHP/TS code follows PSR-4, strict comparisons, no `empty()`, snake_case DB/PHP variables, camelCase TS. | Coding conventions | `vendor/bin/php-cs-fixer fix` + `make phpstan` + `npm run check` all pass. | — | AGENTS.md |
| NFR-054-08 | All new frontend code lives under `resources/js/v8/**`; `resources/js/v7/**` is not modified by this feature. | Scope decision — v7 is being phased out (Feature 049); doubling effort on a UI stack mid-deprecation is not worthwhile. | Code review: no diffs under `resources/js/v7/`. | Frontend scope decision 2026-08-10 | User decision 2026-08-10 |
| NFR-054-09 | No layout/config combination this feature introduces can result in a landing page with zero reachable links into the gallery (or, for `studio`, into the login flow that leads to it). This does not extend to the pre-existing, feature-independent global custom CSS/JS mechanism (Settings → `dist/user.css`/`custom.js`) hiding elements — that risk already exists site-wide today, on every page, independent of this feature; not a new risk introduced here. | UX guardrail — avoid dead-end pages (see Non-Goals). | Manual check across all layout × config-flag combinations, with the global custom CSS/JS fields empty: at least one "enter gallery"/"login" affordance is always present and enabled. | FR-054-14..17, FR-054-21..22 | Non-Goals |
| NFR-054-10 | The `studio` layout's "Client Login" CTA reuses the existing `login` route and `LoginForm.vue` flow exactly as-is. This feature introduces no new authentication mechanism, session handling, per-client credential, or magic-link system. | Scope control — avoid quietly growing an auth feature inside a landing-page feature. | Code review: `LandingStudio.vue`'s CTA is a plain `RouterLink`/navigation to the existing `login` route, nothing else. | `router/paths.ts`'s existing `login` route | User decision 2026-08-10 |

## UI / Interaction Mock-ups

### Layout 1 — `classic` (today's page, unchanged default)

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
│  "A gallery of moments"                                    │  ← hero text, position:
│  Subtitle text                     [ Enter gallery ]        │    top_left/top_right/
│                                                              │    bottom_left/bottom_right/
├──────────────────────────────────────────────────────────┤    center
│  ABOUT                                                       │  ← optional (landing_about_enabled)
│  Free-text philosophy / description block                   │
├──────────────────────────────────────────────────────────┤
│  RECENT WORK                                                 │  ← optional (landing_featured_items_enabled)
│  [cover] [cover] [cover] [cover] [cover] [cover]             │    automatic: N latest public albums
│                                                                │    manual: admin-curated photos/albums
├──────────────────────────────────────────────────────────┤
│  1,204 photos · 38 albums          (landing_show_stats)      │  ← optional
│  [social icons]   © copyright   [extra footer links]         │
└──────────────────────────────────────────────────────────┘
```

### Layout 3 — `minimal` (SE, centered card)

```
┌──────────────────────────────────────────────────────────┐
│                                                              │
│                                                              │
│                        [ logo ]                             │
│                     Site Title                               │
│                   Site subtitle text                         │
│              (optional short about text)                     │
│                                                              │
│                  [  Enter gallery  ]                          │
│                                                              │
│           [social icons]   [extra links]                     │
│                                                              │
└──────────────────────────────────────────────────────────┘
```

### Layout 4 — `studio` (SE, client-login-first)

```
┌──────────────────────────────────────────────────────────┐
│ [logo]                                                      │
│                                                              │
│           ░░░░ hero background (optional) ░░░░              │
│                                                              │
│                    Welcome back                             │
│              [   Client Login   ]        ← primary CTA      │
│                                                              │
│                  View public gallery →   ← secondary, smaller│
│                                                              │
│           [social icons]   [extra links]                     │
└──────────────────────────────────────────────────────────┘
```

### Admin — `LandingConfig.vue` (new dedicated page, mirrors `NsfwConfig.vue`)

```
┌─────────────────────────────────────────────────────────────┐
│  Landing Page Configuration                                   │
├─────────────────────────────────────────────────────────────┤
│  [ Settings ]   Links   Featured           ← UTabs             │
├─────────────────────────────────────────────────────────────┤
│  ┌─ General ────────────────────────────────────────────┐    │
│  │ Enable landing page: [x] Enabled                      │    │
│  │ Title: [_____]  Subtitle: [_____]  Owner: [_____]     │    │
│  └────────────────────────────────────────────────────────┘    │
│  ┌─ Background & Branding ──────────────────────────────┐    │
│  │ (existing Feature 025 background-mode fields, logos)  │    │
│  └────────────────────────────────────────────────────────┘    │
│  ┌─ Layout & Structure ─────────────────────────────────┐    │
│  │ Layout:      [ Classic                          ▼]   │    │
│  │                ↳ Portfolio/Minimal/Studio: SE badge   │    │
│  │ Intro splash screen:  [x] Enabled                     │    │
│  └────────────────────────────────────────────────────────┘    │
│  ┌─ Hero ───────────────────────────────────────────────┐    │
│  │ Text position:   [ Center                        ▼]   │    │
│  │ Animation preset:[ Classic fade                   ▼]   │    │
│  │                    ↳ Zoom in/Parallax/Slide: SE badge  │    │
│  │ CTA button text: [ (blank = layout default)       ]   │    │
│  └────────────────────────────────────────────────────────┘    │
│  ┌─ Content ────────────────────────────────────────────┐    │
│  │ About block:      [ ] Enabled   Text: [_________]     │    │
│  │ Show gallery stats:      [ ] Enabled  (SE badge)       │    │
│  └────────────────────────────────────────────────────────┘    │
│  ┌─ Footer & Social ────────────────────────────────────┐    │
│  │ (existing footer copyright/social/contact-form fields)│    │
│  └────────────────────────────────────────────────────────┘    │
│  This page replaces the "Landing page" entry in the flat       │
│  Settings list entirely (FR-054-27) — nothing is duplicated.   │
└─────────────────────────────────────────────────────────────┘
```

```
┌─────────────────────────────────────────────────────────────┐
│  Landing Page Configuration                                   │
├─────────────────────────────────────────────────────────────┤
│  Settings    [ Links ]   Featured         ← UTabs, 2nd tab     │
├─────────────────────────────────────────────────────────────┤
│                                                    [+ Add link]│
│  ⋮⋮ Instagram   instagram.com/...   nav+footer   ●  [Edit][Del]│
│  ⋮⋮ Blog        example.com/blog    nav          ●  [Edit][Del]│
│  ⋮⋮ Press Kit   example.com/press   footer       ○  [Edit][Del]│
└─────────────────────────────────────────────────────────────┘
  ⋮⋮ = drag handle (reorders via Reorder endpoint), ● enabled / ○ disabled
```

```
┌─────────────────────────────────────────────────────────────┐
│  Landing Page Configuration                                   │
├─────────────────────────────────────────────────────────────┤
│  Settings    Links   [ Featured ]         ← UTabs, 3rd tab     │
├─────────────────────────────────────────────────────────────┤
│  Featured content:  [x] Enabled  (SE)                          │
│  Mode:  ( ) Automatic (latest N public albums)                 │
│         (•) Manual — curate specific photos/albums              │
│  Count (automatic mode only): [6]  (3-12)                      │
│  ─────────────────────────────────────────────────────────     │
│  Manual selection (shown only when Mode = Manual):              │
│  [ Search photos/albums...                              ]      │
│                                                    [+ Add item]│
│  ⋮⋮ 📷 "Sunset over the bay"          ●  [Del]                  │
│  ⋮⋮ 📁 "Iceland 2026"                 ●  [Del]                  │
│  ⋮⋮ 📷 "Portrait study #3"            ○  [Del]                  │
└─────────────────────────────────────────────────────────────┘
  ⋮⋮ = drag handle (reorders via Reorder endpoint), 📷 photo / 📁 album
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-054-01 | Fresh/unmodified install (`landing_layout=classic`, all new flags at default) — landing page is pixel-for-pixel identical to pre-feature output. |
| S-054-02 | SE install sets `landing_layout=portfolio` — visitor sees the portfolio layout with nav, hero, and footer sections. |
| S-054-03 | Non-SE install sets `landing_layout=portfolio` — visitor sees `classic` instead (silent fallback, no error). |
| S-054-04 | SE install sets `landing_layout=minimal` — visitor sees the centered-card layout. |
| S-054-05 | `landing_intro_screen_enabled=false` on `classic` — hero is visible immediately, no splash. |
| S-054-06 | `landing_intro_screen_enabled=false` on `portfolio` — hero visible immediately; behaviour is a no-op on `minimal` (no splash there regardless). |
| S-054-07 | `landing_hero_text_position=bottom_right` on `portfolio` — hero headline/subtitle/CTA render bottom-right of the hero viewport. |
| S-054-08 | SE install sets `landing_animation_preset=parallax_scroll` on `portfolio` — sections fade/slide in on scroll. |
| S-054-09 | Non-SE install sets `landing_animation_preset=zoom_in` — effective preset falls back to `classic_fade` (silent). |
| S-054-10 | Visitor's browser reports `prefers-reduced-motion: reduce` with `landing_animation_preset=parallax_scroll` — zero animations fire regardless of server-resolved preset. |
| S-054-11 | `landing_about_enabled=true` with non-empty `landing_about_text` on `portfolio` — about section renders the HTML; `classic` never renders it even if the flag is on. |
| S-054-12 | `landing_show_stats=true` (SE) with 1,204 public photos / 38 public albums, some private — displayed counts exclude private content. |
| S-054-13 | `landing_featured_items_enabled=true`, `mode=automatic`, `count=6` (SE), 20 public albums exist — the 6 most recently published public albums appear with covers. |
| S-054-14 | Same as S-054-13 but only 2 public albums exist — section shows 2, no placeholders. |
| S-054-15 | Same as S-054-13 but 0 public albums exist — featured-content section is omitted entirely. |
| S-054-16 | Admin creates 3 `LandingLink` rows (placements `nav`, `footer`, `both`), 1 disabled — public landing page shows only the 2 enabled links in their correct area(s); admin CRUD page shows all 3. |
| S-054-17 | Admin reorders links via `PATCH /LandingLink/Reorder` — public link order reflects the new `sort_order` on next load. |
| S-054-18 | Non-admin user calls any `/api/v2/LandingLink*` or `/api/v2/LandingFeaturedItem*` route — 403. |
| S-054-19 | Admin saves `landing_featured_items_count=15` — rejected (max 12); saves `2` — rejected (min 3). |
| S-054-20 | v7 (`resources/js/v7/views/Landing.vue`) is loaded regardless of any new config value — always renders today's static classic page, completely unaffected by this feature. |
| S-054-21 | SE install sets `landing_layout=studio` — primary CTA links to `/login`; secondary smaller link to the public gallery is visible below it. |
| S-054-22 | Non-SE install sets `landing_layout=studio` — falls back to `classic` (same fail-safe shape as S-054-03). |
| S-054-23 | `landing_cta_text="View My Work"` set on a `portfolio`-configured install — primary CTA shows the configured text instead of the "Access Gallery" translation default. |
| S-054-24 | `landing_cta_text` left empty (default) — `classic`/`portfolio`/`minimal` show "Access Gallery"; `studio` shows "Client Login" — each layout's own translation default. |
| S-054-25 | `contact_form_enabled=true` — "Contact" link appears in `portfolio`'s nav and `minimal`'s footer, navigating to `/contact`; absent when the flag is `false`, and never present on `classic`/`studio` regardless of the flag. |
| S-054-26 | `portfolio` layout renders — scroll-down indicator appears between the hero and the next section; under `prefers-reduced-motion` or `animation_preset=none` it is present and clickable but does not bounce/animate. |
| S-054-28 | `landing_featured_items_mode=manual` (SE) with 2 photos + 1 album added, one disabled — public landing page shows only the 2 enabled items, mixed types, in `sort_order`; admin Featured tab shows all 3. |
| S-054-29 | Manual mode with a curated item whose underlying photo/album was deleted after being added — that item is silently skipped on the next landing-page load, no error. |
| S-054-30 | Manual mode with zero enabled `LandingFeaturedItem` rows — featured-content section renders nothing (same graceful-empty behaviour as automatic mode with zero public albums). |
| S-054-31 | Admin opens the flat generic Settings list — no "Landing page" category/section appears there (FR-054-27); all landing configuration is only reachable via `LandingConfig.vue`. |

## Test Strategy

- **Core/Application:** Unit tests for `LandingPageResource`'s new resolution methods (layout/animation SE fallback, stats query construction, automatic-mode featured-albums query, manual-mode featured-item resolution incl. graceful-skip-on-missing), mirroring the existing `resolveBackgroundUrl` tests from Feature 025.
- **REST:** Feature tests for the extended `GET /api/Init::landing` payload (new fields present/absent per config) and the full `LandingLink`/`LandingFeaturedItem` CRUD + Reorder endpoints (admin-only, validation, ordering).
- **UI (JS):** No JS test runner exists in this repo (same gap noted in Q-051-05) — verification is manual/browser-based for `LandingClassic.vue`/`LandingPortfolio.vue`/`LandingMinimal.vue`/`LandingStudio.vue` and `LandingConfig.vue` (Settings, Links, and Featured tabs), per each Branch & Scenario Matrix row.
- **Security:** Tests confirming `LandingLink`/`LandingFeaturedItem` CRUD is admin-only (S-054-18), and that automatic-mode stats/featured content never surface private content while manual mode's admin-trusted exception is deliberate and tested as such (NFR-054-03).
- **Regression:** Existing landing-page tests (if any) re-run unmodified against the `classic` default to prove NFR-054-01.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-054-01 | `App\Models\LandingLink` — ULID PK, `label`, `url`, `icon` (nullable), `placement` (`LandingLinkPlacement`), `open_in_new_tab`, `sort_order`, `enabled`, timestamps. Mirrors `App\Models\Webhook`'s shape. | core, application |
| DO-054-02 | `LandingPageResource` additions: `layout` (effective `LandingLayoutType`), `intro_screen_enabled`, `hero_text_position` (effective `LandingTextPosition`), `animation_preset` (effective `LandingAnimationPreset`), `about_enabled`, `about_text`, `public_photo_count?`, `public_album_count?`, `featured_items_enabled`, `featured_items_mode` (effective `LandingFeaturedItemsMode`), `featured_items: LandingFeaturedItemResource[]`, `links: LandingLinkResource[]`, `cta_text`. | REST, UI |
| DO-054-03 | `LandingFeaturedItemResource` — unified projection for both automatic and manual modes: `item_type` (`"photo"`\|`"album"`), `id`, `title`, `thumb_url`, `url`, `num_photos?` (album only). | REST, UI |
| DO-054-04 | `App\Models\LandingFeaturedItem` — ULID PK, `item_type` (`LandingFeaturedItemType`), `item_id`, `sort_order`, `enabled`, timestamps. Mirrors `App\Models\LandingLink`'s shape. | core, application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-054-01 | GET `/api/Init::landing` | Existing endpoint (Feature 025's API-025-01); response extended with DO-054-02's new fields. | No breaking change — additive fields only. |
| API-054-02 | REST `GET /api/v2/LandingLink` | List all `LandingLink` rows (admin). | Admin-only |
| API-054-03 | REST `POST /api/v2/LandingLink` | Create a `LandingLink` (admin). | Admin-only |
| API-054-04 | REST `GET /api/v2/LandingLink/{landingLink}` | Show one `LandingLink` (admin). | Admin-only |
| API-054-05 | REST `PUT /api/v2/LandingLink/{landingLink}` | Full update (admin). | Admin-only |
| API-054-06 | REST `PATCH /api/v2/LandingLink/{landingLink}` | Partial update, e.g. toggle `enabled` (admin). | Admin-only |
| API-054-07 | REST `DELETE /api/v2/LandingLink/{landingLink}` | Hard delete (admin). | Admin-only |
| API-054-08 | REST `PATCH /api/v2/LandingLink/Reorder` | Bulk `sort_order` update from an ordered ID array (admin). | Admin-only |
| API-054-09 | REST `GET /api/v2/LandingFeaturedItem` | List all `LandingFeaturedItem` rows (admin). | Admin-only |
| API-054-10 | REST `POST /api/v2/LandingFeaturedItem` | Create a `LandingFeaturedItem` (admin). | Admin-only; validates `item_id` exists for `item_type` |
| API-054-11 | REST `GET /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Show one `LandingFeaturedItem` (admin). | Admin-only |
| API-054-12 | REST `PUT /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Full update (admin). | Admin-only |
| API-054-13 | REST `PATCH /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Partial update, e.g. toggle `enabled` (admin). | Admin-only |
| API-054-14 | REST `DELETE /api/v2/LandingFeaturedItem/{landingFeaturedItem}` | Hard delete (admin). | Admin-only |
| API-054-15 | REST `PATCH /api/v2/LandingFeaturedItem/Reorder` | Bulk `sort_order` update from an ordered ID array (admin). | Admin-only |
| API-054-16 | GET `/api/v2/Search` | Existing endpoint (Feature 027/028) reused by the Featured tab's picker to search photos/albums by title. | No change — reused as-is |

### Database Migrations

| ID | Description |
|----|-------------|
| MIG-054-01 | Add 11 new scalar config rows: `landing_layout` (enum, default `classic`, values incl. `studio`), `landing_intro_screen_enabled` (bool, default `true`), `landing_hero_text_position` (enum, default `center`), `landing_animation_preset` (enum, default `classic_fade`), `landing_about_enabled` (bool, default `false`), `landing_about_text` (text, default `''`), `landing_show_stats` (bool, default `false`), `landing_featured_items_enabled` (bool, default `false`), `landing_featured_items_mode` (enum `automatic`\|`manual`, default `automatic`), `landing_featured_items_count` (int, default `6`, range `3-12`), `landing_cta_text` (string, default `''`). All filed under the `Mod Welcome` category, added to `ConfigIntegrity`'s whitelist with `type`/`type_range` metadata. |
| MIG-054-02 | `CREATE TABLE landing_links` — ULID `id` (primary), `label` (string 255), `url` (string 2048), `icon` (string 255, nullable), `placement` (string 20), `open_in_new_tab` (bool, default `true`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), `created_at`/`updated_at`. Indexes on `enabled`, `placement`. Mirrors MIG shape of `webhooks` table. |
| MIG-054-03 | `CREATE TABLE landing_featured_items` — ULID `id` (primary), `item_type` (string 10), `item_id` (string, matches `Photo`/`Album` ID format), `sort_order` (int, default `0`), `enabled` (bool, default `true`), `created_at`/`updated_at`. Indexes on `enabled`, `item_type`. Mirrors MIG-054-02's shape. |

### Translation Keys

| ID | Key | Description |
|----|-----|--------------|
| TRANS-054-01 | `all_settings.details.landing_layout` | Description + per-enum-value labels for the layout dropdown. |
| TRANS-054-02 | `all_settings.details.landing_intro_screen_enabled` | Description for the intro-splash toggle. |
| TRANS-054-03 | `all_settings.details.landing_hero_text_position` | Description + per-value labels for the position dropdown. |
| TRANS-054-04 | `all_settings.details.landing_animation_preset` | Description + per-value labels for the animation dropdown. |
| TRANS-054-05 | `all_settings.details.landing_about_enabled` / `landing_about_text` | Descriptions for the about-block fields. |
| TRANS-054-06 | `all_settings.details.landing_show_stats` | Description for the stats toggle (SE-flagged in UI copy). |
| TRANS-054-07 | `all_settings.details.landing_featured_items_enabled` / `landing_featured_items_mode` / `landing_featured_items_count` | Descriptions for the featured-content fields (SE-flagged in UI copy). |
| TRANS-054-08 | `landing_link.*` (new file, mirrors `webhook.php`) | Admin CRUD labels for the Links tab: field labels, validation messages, empty state, confirm-delete text. |
| TRANS-054-09 | `landing.portfolio.*`, `landing.minimal.*`, `landing.studio.*` (extend `landing.php`) | Frontend copy for the new layouts (about heading, featured-content heading, "Welcome back," etc.) — English required for this feature; full 22-locale set delivered in tasks. |
| TRANS-054-10 | `landing.client_login`, `landing.view_public_gallery`, `landing.contact` | New standalone keys: `studio`'s default primary CTA label, `studio`'s secondary public-gallery link label, and the "Contact" nav/footer link label used by `portfolio`/`minimal`. |
| TRANS-054-11 | `all_settings.details.landing_cta_text` | Description for the CTA-text override field. |
| TRANS-054-13 | `landing_featured_item.*` (new file, mirrors `landing_link.php`) | Admin CRUD labels for the Featured tab's manual curation UI: mode switcher, picker placeholder, empty state, confirm-delete text. |

## Telemetry & Observability

No new telemetry events — matches Feature 025's precedent (landing page requests are not individually tracked). SE-gating fallbacks (FR-054-02, FR-054-06, FR-054-29) are silent by design (NFR-054-02), matching Feature 039's fail-safe behaviour; no log entry is written on fallback, to avoid log noise on every request from a non-SE install with a premium value configured.

## Documentation Deliverables

- Update `docs/specs/4-architecture/roadmap.md` — add Feature 054 to Active Features.
- Update `docs/specs/_current-session.md` with this session's summary.
- Update `docs/specs/4-architecture/open-questions.md` with the architecture decisions resolved via user confirmation on 2026-08-10 (Q-054-01..10).
- Admin-facing docs (if a user-facing settings guide exists) noting the new layout picker, SE-gated fields, and that landing settings moved from the flat Settings list to a dedicated page.

## Fixtures & Sample Data

Existing test helpers (photos/albums with public/private access permissions) are sufficient for stats/automatic-featured-content tests, matching Feature 025. A small `LandingLinkFactory` and `LandingFeaturedItemFactory` (both mirror the existing `WebhookFactory`) are needed for CRUD tests.

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
      - name: icon
        type: string
        constraints: "nullable, free-text (e.g. lucide:instagram); defaults to lucide:link when empty"
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
      - animation_preset: LandingAnimationPreset
      - about_enabled: boolean
      - about_text: string
      - public_photo_count: integer|null
      - public_album_count: integer|null
      - featured_items_enabled: boolean
      - featured_items_mode: LandingFeaturedItemsMode
      - featured_items: LandingFeaturedItemResource[]
      - links: LandingLinkResource[]
      - cta_text: string
      # note: contact-form visibility (FR-054-23) reads the existing
      # footer.is_contact_form_enabled field — no new field needed.
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
    description: "Add 11 new landing-page scalar configs (layout, intro screen, hero text position, animation preset, about block, stats, featured-items enabled/mode/count, CTA text override), filed under the Mod Welcome category."
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
  - id: TRANS-054-13
    key: landing_featured_item.*

ui_states:
  - id: UI-054-01
    description: LandingConfig.vue's Layout dropdown shows "Portfolio"/"Minimal" as disabled/badge "SE" when the install is not on Lychee SE.
  - id: UI-054-02
    description: LandingConfig.vue's Animation dropdown shows "Zoom in"/"Parallax scroll"/"Slide reveal" as disabled/badge "SE" when not on Lychee SE.
  - id: UI-054-03
    description: LandingConfig.vue's Links tab table row drag-reorder updates sort_order via API-054-08.
  - id: UI-054-04
    description: LandingConfig.vue's Layout dropdown Studio option shows an "SE" badge/disabled state when the install is not on Lychee SE, same treatment as Portfolio/Minimal.
  - id: UI-054-05
    description: Portfolio layout's scroll-down indicator smooth-scrolls to the next rendered section on click.
  - id: UI-054-06
    description: LandingConfig.vue is reachable as an admin tile (group "core") from the admin dashboard, at route landing-config / path /admin/landing-config, visible whenever can_edit is true.
  - id: UI-054-07
    description: LandingConfig.vue's Featured tab shows the manual-curation picker/list only meaningfully relevant when Mode = Manual; the mode switcher itself is always visible at the top of the tab.
```

## Appendix

### Related Request

User request, 2026-08-10: rework the landing page to be more configurable — enable/disable the first screen, choose what info to display, position hero text similar to the existing album "extended hero" pro position feature, add extra links, support different animations, and support alternate page shapes (a multi-section portfolio-style page: nav bar, philosophy/about blurb, work preview, contact). Follow-up requests, same day: propose additional layouts and improvements; adopt a dedicated admin configuration page pattern similar to the existing NSFW classifier config page; drop the landing-specific custom CSS idea in favour of the already-existing global custom CSS/JS mechanism; make `LandingConfig.vue` the single home for the entire landing settings category; support manual (as well as automatic) curation of featured content, covering both photos and albums.

### Extra-Layout and Extra-Feature Brainstorm (2026-08-10) — what was proposed and what was chosen

A brainstorming pass proposed 5 additional layout candidates and ~10 non-layout improvements, tiered by cost/value. The user selected a subset (logged as Q-054-04/05 in `open-questions.md`); the rest are recorded here as backlog so the reasoning isn't lost.

**Layout candidates considered:**
- **`studio` (client-login-first) — selected, now FR-054-21/22.** Different information architecture, not a re-skin: primary CTA is entry into the existing login flow (for returning clients with private albums), not the public gallery.
- **Mosaic/grid-first (full-bleed photo grid as the hero, title overlaid)** — deferred. Would reuse the bulk of the featured-content resolution logic at photo-level instead of album-level; good candidate for a fast follow-up feature.
- **"Coming soon"/maintenance (logo + one line, nothing else)** — deferred. Trivial to build; revisit if requested.
- **Split-screen editorial (fixed panel + scrollable content)** — deferred. Mostly a CSS-layout variant of `portfolio`, lower differentiation.
- **Cinematic/video-hero background** — deferred, deliberately not silently folded in. Directly contradicts this feature's inherited Non-Goal from Feature 025 ("no video backgrounds") and raises infra questions (storage, transcoding, mobile data cost) that a background-mode toggle doesn't. Should be its own scope decision if pursued.

**Non-layout improvements considered:**
- **Selected (now FR-054-23/24/25):** Contact-form surfacing on `portfolio`/`minimal` (reuses Feature 022 entirely — wiring only); configurable CTA button text (`landing_cta_text`); `portfolio` scroll-down indicator.
- **Considered and reversed — landing-specific custom CSS override.** Initially proposed as `landing_custom_css`; withdrawn once it was pointed out (and confirmed by code read) that Lychee already has a global custom CSS/JS mechanism covering every page, landing included — a landing-specific field would have been a redundant, inconsistent second mechanism. See Non-Goals and Q-054-07.
- **Deferred — second image slot for the About section** (distinct from the hero background, a two-column image+text block style). Real value, real scope; revisit alongside the mosaic layout if `portfolio` proves popular.
- **Deferred — testimonials/client-logos block.** Would need its own admin-managed CRUD list (mirrors `LandingLink`'s shape) — meaningful scope, not a tweak; good candidate for a dedicated follow-up feature once `LandingLink`'s CRUD pattern is proven in production.
- **Deferred — dedicated hero tagline field separate from `landing_title`/`site_title`.** Minor; current reuse of `landing_title`/`landing_subtitle` as hero headline/subtitle across all four layouts was judged sufficient.
- **Not landing-scoped — site-wide language switcher, cookie-consent banner.** Neither exists anywhere in Lychee v8 today (confirmed by search before proposing); these are cross-cutting, app-wide features, not something this landing-page feature should introduce as a side effect.
- **Not pursued — live WYSIWYG settings preview.** Already recorded as a Follow-up in `plan.md` from the original draft; restated here for completeness.

### Resolved Scope Decisions (2026-08-10, via direct user confirmation)

These decisions shape the entire feature and are recorded here (and in `open-questions.md` as Q-054-01..10) for traceability, per this repo's convention of resolving high-impact questions before/alongside spec authoring (see Q-049-01..04, Q-052-01..05).

- **Q-054-01 (Architecture shape).** Chose **multiple named layout templates** (`classic`/`portfolio`/`minimal`) over (a) one mega-configurable single-shape hero, or (b) a fully modular/reorderable section builder. Reasoning: gives real shape variety without the open-ended scope of a page builder.
- **Q-054-02 (Frontend scope).** Chose **v8 (Nuxt UI) only**. `resources/js/v7/views/Landing.vue` is not touched. Reasoning: v7 is being actively retired by Feature 049; building full parity into a stack mid-deprecation is wasted effort, and Feature 051 already set this precedent for new UI-heavy work.
- **Q-054-03 (SE gating).** Chose **classic layout and the current default animation stay free forever; the two new layouts (`portfolio`, `minimal`) and the three new animation presets are Lychee SE-exclusive**, resolved fail-safe to the free defaults otherwise. Reasoning: mirrors Feature 039's precedent of gating premium visual/branding capability behind SE, while the underlying flexibility mechanism (config-driven, no code fork needed) benefits every install. Extra links (FR-054-11..13) and hero text position (FR-054-04) were deliberately **not** SE-gated — they extend existing free features (footer social links; album hero position) rather than introducing a new page shape, so gating them would be inconsistent with those precedents.
- **Q-054-06 (Admin UI approach).** Chose a **dedicated `LandingConfig.vue` page mirroring Feature 045's `NsfwConfig.vue`** over relying solely on the flat generic settings list. See "Admin UI Architecture Decision" below.
- **Q-054-07 (Landing-specific custom CSS).** **Dropped entirely.** Lychee already has a global custom CSS/JS mechanism (`Settings` → `dist/user.css`/`dist/custom.js`, loaded on every page via `<x-meta>`, confirmed by reading `App\View\Components\Meta` and `SettingsController::setCSS()`/`setJS()`) — a landing-specific field would have duplicated it inconsistently. Admins wanting bespoke landing styling reuse the existing global fields and target the relevant selectors themselves; it is on the admin to figure out what works for their configuration.
- **Q-054-08 (`Mod Welcome` category absorption).** `LandingConfig.vue`'s Settings tab absorbs the **entire** `Mod Welcome` category — every pre-existing landing key, not just the new ones — and that category is filtered out of the flat generic Settings list entirely (FR-054-27), unlike Feature 045's NSFW keys which stay visible in both places. Reasoning: NSFW only curates a subset of keys among many unrelated ones in the flat list, so duplication is harmless there; `Mod Welcome` would become a fully redundant duplicate category if left in both places once the entire category has a dedicated home.
- **Q-054-09 (Featured content curation model).** Chose **automatic by default, with full manual curation supported** — not automatic-only, and not manual-only. `landing_featured_items_mode` (`automatic`/`manual`) governs which resolution path runs; manual mode supports selecting **either photos or albums**, not albums only. Reasoning: automatic-only was the simpler initial proposal, but the user explicitly asked for full manual curation across both content types, while still wanting automatic as the low-effort default.
- **Q-054-10 (`LandingLink.icon` input).** Confirmed **free-text** Iconify/Lucide identifier (e.g. `lucide:instagram`), not a visual icon picker — admin types it directly, consistent with the "it's on the user to figure out how to configure things" direction given for this feature. Falls back to a generic `lucide:link` icon when empty.

### Why a landing-scoped `LandingTextPosition` enum instead of reusing `AlbumTitlePosition`

Both enums have identical values (5 corner/center positions) and it would be technically possible to import `App\Enum\AlbumTitlePosition` directly. This spec chooses a small, deliberately duplicated `LandingTextPosition` enum instead, because:
1. Albums and the landing page are different bounded contexts — coupling them through a shared enum means any future album-hero-specific change (e.g. adding a 6th position for albums only) forces a decision about whether landing inherits it.
2. The duplication cost is five string cases, not meaningful maintenance burden.

This is a documented implementation-detail choice, not a resolved open question — no ADR required (below the "architecturally significant" bar).

### Admin UI Architecture Decision (2026-08-10)

Initially drafted as "no bespoke admin Vue — the flat generic settings list is sufficient." Revised after the user pointed at Feature 045's `NsfwConfig.vue` as the desired pattern: a **dedicated, curated admin page** (`LandingConfig.vue`) rather than leaving settings scattered through the flat list. Confirmed by reading `NsfwConfig.vue` that this is additive, not a replacement of storage — it still reads/writes through the exact same `SettingsService.getAll()`/`setConfigs()` API the generic list uses (same `configs` DB table, no new backend), just organized into `UTabs` + curated `Fieldset` groups instead of one long flat list. The `LandingLink` CRUD (originally planned as a standalone `LandingLinks.vue` page) was folded into this same page as a second tab, and `LandingFeaturedItem` curation as a third, directly mirroring how `NsfwConfig.vue`'s own second tab ("Presets") shows a related-but-distinct richer view alongside "Settings." The scope was then widened once more (Q-054-08) to absorb the *entire* `Mod Welcome` category rather than just the new keys, with that category consequently filtered out of the flat list (FR-054-27) — a deliberate deviation from the NSFW precedent, justified because NSFW only curates a subset of keys while this page curates a whole category.

### Follow-ups Considered Out of Scope

- A true modular/reorderable section builder (drag sections, not just pick a layout) — bigger feature, revisit if 4 named layouts prove insufficient.
- Sharing the `POSITION_CLASSES` Tailwind mapping between `AlbumHeaderPanel.vue` and the new landing components via a common composable — both currently duplicate a small position→class map; worth a follow-up dedup pass once the landing implementation stabilises, not required for this feature's completion.
- Mosaic/grid-first layout, "coming soon" layout, split-screen editorial layout, per-layout background video support (see Extra-Layout brainstorm above).
- Second image slot for the About section; testimonials/client-logos CRUD block; dedicated hero tagline field (see Extra-Feature brainstorm above).
- A live WYSIWYG preview in the admin settings page (today's mock-up pattern is static ASCII/description text like every other Lychee settings section).
- A curated icon picker for `LandingLink.icon` (see Q-054-10) — free-text only for now.

---

*Last updated: 2026-08-10 (rev 4 — dropped landing-specific custom CSS in favour of the existing global mechanism; `LandingConfig.vue` now absorbs the entire `Mod Welcome` category and is filtered from the flat list; featured content redesigned as automatic-default + full manual photo/album curation via new `LandingFeaturedItem` CRUD; confirmed `LandingLink.icon` as free-text; removed all named references to the external site that originally inspired the `portfolio` layout, per explicit instruction — layout is now described structurally only)*
