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
- **A configurable label for `studio`'s secondary "view public gallery" link.** Only the primary CTA is overridable (`landing_cta_text`, FR-054-24); the secondary link's copy is fixed to the `landing.view_public_gallery` translation. Deliberate minimalism — see Q-054-19.

## Functional Requirements

> Note: `FR-054-26` is intentionally absent (the table jumps FR-054-25 → FR-054-27). It was the landing-specific custom-CSS requirement, removed by Q-054-07; per this repo's numbering convention (retired numbers are never reused), the gap is permanent rather than renumbering everything after it. See Q-054-14.

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
| FR-054-09 | New bool config `landing_show_stats` (default `false`, SE-gated per FR-054-02's check). When effectively enabled, `LandingPageResource` exposes `public_photo_count` and `public_album_count`, computed via `PhotoQueryPolicy::applySearchabilityFilter($query, null, [])` / `AlbumQueryPolicy::applyVisibilityFilter($query, null)` counts. Only `portfolio` renders these; `classic` and `minimal` never do — consistent with FR-054-10/17's existing rule that `minimal` excludes all supplementary content blocks to preserve its minimal intent (see Q-054-11). | Counts reflect only publicly visible content, matching NFR-025-03's policy usage. | — | If the flag is effectively disabled (config off, or non-SE fallback), the fields are omitted/null and no query runs. | — | User request 2026-08-10 ("what info to display") |
| FR-054-10 | New bool config `landing_featured_items_enabled` (default `false`, SE-gated) and new enum config `landing_featured_items_mode` with values `automatic`, `manual` (new `App\Enum\LandingFeaturedItemsMode`, default `automatic`). In `automatic` mode, `LandingPageResource` exposes an array of up to `landing_featured_items_count` (int, default `6`, range 3–12) featured **albums** — public albums ordered `published_at DESC, created_at DESC, id DESC` via `AlbumQueryPolicy::applyVisibilityFilter($query, null)` (reusing the exact query shape of Feature 025's `resolveLatestAlbumCover`), each with `item_type: "album"`, `id`, `title`, `thumb_url`, `num_photos`. Only `portfolio` renders this section; `classic` and `minimal` never do (keeps `minimal` minimal). `manual` mode's resolution is FR-054-29. | Automatic mode: section shows up to N most-recently-published public albums with cover thumbnails, linking into the gallery. | `landing_featured_items_count` validated to the 3–12 range at config-update time; `landing_featured_items_mode` restricted to the 2 enum values. | Automatic mode with fewer than N public albums → shows however many exist (no padding/placeholders); zero public albums → section renders nothing. | — | User request 2026-08-10; refined 2026-08-10 (automatic default + manual curation) |
| FR-054-11 | New model `App\Models\LandingLink` (ULID PK, mirrors `App\Models\Webhook`'s shape) backed by a new `landing_links` table: `label`, `url`, `icon` (nullable free-text Iconify/Lucide name, e.g. `lucide:instagram` — admin-typed, no picker UI, see Non-Goals), `placement` (enum `nav`\|`footer`\|`both`, new `App\Enum\LandingLinkPlacement`), `open_in_new_tab` (bool, default `true`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), timestamps. When `icon` is null/empty, the frontend renders a generic default icon (`lucide:link`) instead of no icon. | Admin can define any number of extra links beyond the fixed 5 social-media fields. | `url` must be a valid absolute URL, ≤2048 chars; `label` required, ≤255 chars; `icon` nullable, ≤255 chars (matches the `landing_links.icon` column width, MIG-054-02); `placement`/enum fields restricted to allowed values. | 422 with field errors on invalid input. | — | User request 2026-08-10 ("possibility to add extra links") |
| FR-054-12 | New admin-only REST CRUD for `LandingLink`: `GET/POST /api/v2/LandingLink`, `GET/PUT/PATCH/DELETE /api/v2/LandingLink/{landingLink}` (these 6 routes mirror the CRUD shape of `App\Http\Controllers\Admin\WebhookController`, FR-031-01..04). `PATCH /api/v2/LandingLink/Reorder` is a **new contract with no existing precedent in this codebase** (confirmed by repo-wide search — no bulk-reorder endpoint exists anywhere today, see Q-054-17): request body `{ ids: string[] }` must contain the **complete** set of every existing `LandingLink` ID, in the desired order — a partial list (any ID omitted) is rejected, not silently accepted, to avoid ambiguity about what happens to un-listed rows. On success, `sort_order` is set to each ID's array index (0-based) inside a DB transaction; response is the same shape as the index endpoint (full, freshly re-ordered list). | Admin creates/lists/updates/reorders/deletes links via `LandingConfig.vue`'s Links tab. | Only `is_admin = true` may call these routes (mirrors FR-031-12). `ids` must be an array whose members are exactly the set of existing `LandingLink` IDs (no more, no fewer) — otherwise reject. | Non-admin → 403; missing link → 404; `ids` set mismatch on Reorder → 422, no partial update applied. | — | Mirrors Feature 031 pattern for CRUD; Reorder is bespoke (Q-054-17) |
| FR-054-13 | `LandingPageResource` includes a `links` array: only `enabled = true` `LandingLink` rows, ordered by `sort_order`, each projected to `{id, label, url, icon, placement, open_in_new_tab}` (no admin-only fields to hide — no secrets involved, unlike webhooks). Available on **every** layout including `classic` (not SE-gated — see Appendix: Resolved Scope Decisions), rendered in the nav area (placement `nav`/`both`) and/or footer area (placement `footer`/`both`). | Enabled links appear in the correct area(s) on every layout; disabled links never appear publicly. | — | Zero links configured → sections render nothing extra (today's behaviour, unchanged). | — | User request 2026-08-10 |
| FR-054-14 | `resources/js/v8/views/Landing.vue` becomes a thin dispatcher: it fetches `LandingPageResource` once, then renders `LandingClassic.vue`, `LandingPortfolio.vue`, or `LandingMinimal.vue` based on the resolved `layout` field. Loading/error handling (redirect to gallery when `landing_page_enable` is false, or on fetch error) is unchanged from today. | Correct layout component mounts for the resolved layout. | — | Unknown/unexpected layout value (should be unreachable given backend enum validation) falls back to `LandingClassic.vue`. | — | Architecture decision 2026-08-10 |
| FR-054-15 | `LandingClassic.vue` contains the **exact** current markup, CSS keyframes, and behaviour of today's `Landing.vue`, parameterised only by `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_animation_preset` (all defaulting to today's fixed behaviour), and the new `links` array appended to the existing menu/footer. With every new config left at its default, `classic` is pixel-for-pixel identical to pre-feature output (S-054-01). | Default install renders unchanged. | — | — | — | NFR-054-01 |
| FR-054-16 | New `LandingPortfolio.vue`: sticky top nav bar (logo + `links` with placement `nav`/`both` + existing "Gallery" link), a hero section (background from existing Feature 025 resolution, headline/subtitle positioned per FR-054-04, CTA into the gallery, animated per FR-054-06), an optional about section (FR-054-08), an optional featured-content section (FR-054-10/FR-054-29, automatic or manual), and a footer (existing `FooterConfig` — social icons, copyright, additional text — plus `links` with placement `footer`/`both`). Sections that are disabled/empty are omitted entirely, not shown blank. | A `portfolio`-configured SE install shows a multi-section, scrollable page: nav → hero → about → featured work → footer. | — | Any content block resolving to nothing (e.g. zero public albums, zero enabled manual items) is simply omitted. | — | User request 2026-08-10 |
| FR-054-17 | New `LandingMinimal.vue`: a single centered card (logo or title/subtitle, optional about text, one CTA button into the gallery, footer `links`/social icons below the fold). No full-bleed background is required (existing background config may still be used as a subtle backdrop); no featured-content section (kept out to preserve the "minimal" intent, see FR-054-10). | A `minimal`-configured SE install shows a compact, distraction-free landing page. | — | — | — | User request 2026-08-10 ("multiple layouts") |
| FR-054-18 | The 11 new scalar configs (`landing_layout`, `landing_intro_screen_enabled`, `landing_hero_text_position`, `landing_animation_preset`, `landing_about_enabled`, `landing_about_text`, `landing_show_stats`, `landing_featured_items_enabled`, `landing_featured_items_mode`, `landing_featured_items_count`, `landing_cta_text`) are added to `App\Http\Middleware\ConfigIntegrity`'s whitelist, given `type`/`type_range` metadata, filed under the existing `Mod Welcome` settings category (same category the pre-existing landing keys already live in), and remain readable/writable through the existing generic `SettingsService.getAll()`/`setConfigs()` API (same storage, same endpoints — no new config backend). | Keys are readable/writable via `SettingsService`. | — | — | — | Matches existing `SettingsService` data-driven pattern |
| FR-054-19 | New dedicated admin page `resources/js/v8/views/admin/LandingConfig.vue`, structurally mirroring `resources/js/v8/views/admin/NsfwConfig.vue` (Feature 045): a `UTabs` page with a **"Settings"** tab, a **"Links"** tab, and a **"Featured"** tab. The **Settings** tab covers the **entire `Mod Welcome` settings category** — both the pre-existing landing keys (landing enable/title/subtitle/owner, background mode fields, logos, footer/social/contact-form keys — exact list confirmed against `config_categories`/`SettingsController::getAll()` at implementation time) and the 11 new keys from FR-054-18 — loaded via `SettingsService.getAll()`/saved via `setConfigs()`, laid out in curated `Fieldset` sections (e.g. "General," "Background," "Branding," "Layout & Structure," "Hero," "Content," "Footer & Social") using the same shared `BoolField`/`SelectField` components NSFW's page already uses. The **Links** tab is the `LandingLink` CRUD (list/create/edit/delete/reorder against API-054-02..08). The **Featured** tab is the featured-content configuration and, when `landing_featured_items_mode=manual`, the `LandingFeaturedItem` curation UI (FR-054-28). Registration touches **two** files, confirmed by reading how `nsfw-config` is wired: the route name/path entry `{name: "landing-config", path: "/admin/landing-config"}` goes in the shared manifest `resources/js/router/paths.ts` (both v7 and v8 routers attach components to this same list, per its own file header), and the actual component mapping (`const LandingConfig = () => import("@/v8/views/admin/LandingConfig.vue")` plus its route registration) goes in `resources/js/v8/router/routes.ts`, the same place `NsfwConfig`/`Webhooks`/etc. are mapped today. Also registered as an admin tile in `useAdminTiles.ts` (`group: "core"`, alongside `settings`/`design-system`), visible whenever `can_edit`. | Admin manages every landing setting, every extra link, and featured-content curation from one purpose-built page. | — | — | — | User decision 2026-08-10 ("configuration page similar to the NSFW classifier"); expanded 2026-08-10 to full-category absorption |
| FR-054-20 | Existing installations upgrading receive: `landing_layout=classic`, `landing_intro_screen_enabled=true`, `landing_hero_text_position=center`, `landing_animation_preset=classic_fade`, `landing_about_enabled=false`, `landing_show_stats=false`, `landing_featured_items_enabled=false`, `landing_featured_items_mode=automatic`, `landing_cta_text=''`, zero `LandingLink`/`LandingFeaturedItem` rows. Net-zero behavioural change until an admin opts in. | Upgrade produces no visible change to the landing page. | — | — | — | NFR-054-01; backward-compatibility precedent (NFR-025-04) |
| FR-054-21 | New `LandingStudio.vue` layout (SE): the primary hero CTA is a "Client Login" button (`RouterLink` to the existing `login` route — see NFR-054-10, no new auth is built) with label sourced from `landing_cta_text` when set, else the `landing.client_login` translation default. A smaller secondary link into the public gallery (existing `home` route, same link classic/portfolio already expose) renders beneath it, labelled from the fixed `landing.view_public_gallery` translation — **not** independently configurable (only the primary CTA is, via `landing_cta_text`; see Q-054-19 for why a second override field wasn't added). Hero copy otherwise reuses `landing_title`/`landing_subtitle`/`landing_about_text` exactly like the other layouts. | Studio-configured SE install shows client-login-first hero with a secondary public-gallery link. | — | — | — | User decision 2026-08-10 (extra-layout brainstorm) |
| FR-054-22 | `Landing.vue`'s dispatcher (FR-054-14) routes the resolved `layout=studio` to `LandingStudio.vue`; non-SE requesters get the FR-054-02 fallback to `classic` exactly like `portfolio`/`minimal`. | Correct component mounts; non-SE always sees classic. | — | — | — | User decision 2026-08-10 |
| FR-054-23 | When the existing `contact_form_enabled` flag (Feature 022, surfaced today via `FooterConfig`) is `true`, `portfolio` renders a "Contact" nav-bar link and `minimal` renders a "Contact" footer link, both navigating to the existing `/contact` route (Feature 022's dedicated contact-form page). No new contact-form backend/frontend is created — this is wiring only. `classic` and `studio` never render this link (see Non-Goals). | Contact entry point appears on `portfolio`/`minimal` exactly when the contact form is already enabled site-wide. | — | Flag off → link absent, identical to today's behaviour elsewhere. | — | User decision 2026-08-10; reuses Feature 022 |
| FR-054-24 | New string config `landing_cta_text` (default `''`, free tier — not SE-gated). When non-empty, overrides the primary hero CTA button label on whichever layout is active. When empty, each layout falls back to its own existing translation default (`classic`/`portfolio`/`minimal` → `landing.access_gallery`; `studio` → `landing.client_login`, new key). | Configured text appears verbatim on the active layout's primary CTA; unset installs see unchanged copy. | ≤255 chars. | — | — | User decision 2026-08-10 |
| FR-054-25 | `LandingPortfolio.vue` renders a scroll-down indicator (animated chevron) between the hero section and the next rendered section (about, featured content, or footer — whichever comes first). Its animation follows the same `useLandingAnimation`/reduced-motion choke point as FR-054-07/NFR-054-04: static (non-bouncing) but still visible and clickable when reduced motion is requested or `animation_preset=none`. | Indicator present and clickable (smooth-scrolls to the next section) on every `portfolio` render. | — | — | — | User decision 2026-08-10 |
| FR-054-27 | Because `LandingConfig.vue`'s Settings tab (FR-054-19) covers the **entire** `Mod Welcome` category rather than a subset, `Mod Welcome` is filtered out of the flat generic Settings list's category listing (patch to `SettingsController::getAll()`'s existing visibility-filter chain — confirmed live at `app/Http/Controllers/Admin/SettingsController.php:83`, which already does exactly this for a different category: `->when(config('features.enable-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))`; this feature adds an equivalent unconditional `->where('cat', '!=', 'Mod Welcome')` clause) so admins have exactly one place to configure landing settings instead of seeing the same fields twice. This is a deliberate deviation from Feature 045's precedent (NSFW's curated keys stay visible in the flat list too) because NSFW only curates a subset of keys, while this page curates a whole category. | Flat Settings list no longer shows a "Landing page" category; `LandingConfig.vue` is the only place these fields appear. | — | — | — | User decision 2026-08-10 (full-category absorption) |
| FR-054-28 | New model `App\Models\LandingFeaturedItem` (ULID PK, mirrors `App\Models\LandingLink`'s shape) backed by a new `landing_featured_items` table: `item_type` (enum `photo`\|`album`, new `App\Enum\LandingFeaturedItemType`), `item_id` (string — a `Photo.id` or `Album.id` depending on `item_type`), `sort_order` (int, default `0`), `enabled` (bool, default `true`), timestamps. New admin-only REST CRUD mirroring FR-054-12's 6 CRUD routes exactly: `GET/POST /api/v2/LandingFeaturedItem`, `GET/PUT/PATCH/DELETE /api/v2/LandingFeaturedItem/{landingFeaturedItem}`. `PATCH /api/v2/LandingFeaturedItem/Reorder` uses the **identical bespoke contract designed for FR-054-12** (`{ ids: string[] }`, complete set required, transactional, same-shape response) — not a second, independently-invented contract. The picker UI (`LandingConfig.vue`'s Featured tab) searches existing photos/albums via the already-existing `GET /api/v2/Search` endpoint (Feature 027/028) — no new search backend; confirmed by reading `AlbumQueryPolicy`/`PhotoQueryPolicy::applyVisibilityFilter()`/`applySearchabilityFilter()` that both short-circuit to an unrestricted query when `$user->may_administrate === true` (`AlbumQueryPolicy.php:62`), so the admin session driving this picker already sees private/unpublished content through the existing endpoint with no extra work (Q-054-12). | Admin searches for and adds specific photos/albums to an ordered, manually-curated list, including private ones. | `item_id` must reference an existing `Photo` or `Album` matching `item_type` at write time; `url`-style validation not applicable. | 422 if the referenced photo/album doesn't exist at write time; 403 for non-admin (mirrors NFR-054-05); Reorder validation identical to FR-054-12's. | — | User decision 2026-08-10 (manual curation) |
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

> Note: `S-054-27` is intentionally absent (the table jumps S-054-26 → S-054-28) for the same reason as `FR-054-26` above — it was the custom-CSS scenario, removed by Q-054-07. See Q-054-14.

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
    description: LandingConfig.vue's Layout dropdown shows a small "SE" badge next to "Portfolio"/"Minimal" when the install is not on Lychee SE. The options remain selectable (not disabled) — see Q-054-13 for why.
  - id: UI-054-02
    description: LandingConfig.vue's Animation dropdown shows a small "SE" badge next to "Zoom in"/"Parallax scroll"/"Slide reveal" when not on Lychee SE. Options remain selectable, same as UI-054-01.
  - id: UI-054-03
    description: LandingConfig.vue's Links tab table row drag-reorder updates sort_order via API-054-08.
  - id: UI-054-04
    description: LandingConfig.vue's Layout dropdown Studio option shows the same badge-but-selectable "SE" treatment as Portfolio/Minimal (UI-054-01).
  - id: UI-054-05
    description: Portfolio layout's scroll-down indicator smooth-scrolls to the next rendered section on click.
  - id: UI-054-06
    description: LandingConfig.vue is reachable as an admin tile (group "core") from the admin dashboard, at route landing-config / path /admin/landing-config, visible whenever can_edit is true.
  - id: UI-054-07
    description: LandingConfig.vue's Featured tab shows the manual-curation picker/list only meaningfully relevant when Mode = Manual; the mode switcher itself is always visible at the top of the tab.
  - id: UI-054-08
    description: LandingConfig.vue always displays and edits the stored config value (e.g. landing_layout=portfolio even on a non-SE install), never the post-fallback effective value LandingPageResource resolves for public rendering — the admin is editing intent, not previewing current output. See Q-054-13.
```

## Appendix

### Related Request

User request, 2026-08-10: rework the landing page to be more configurable — enable/disable the first screen, choose what info to display, position hero text similar to the existing album "extended hero" pro position feature, add extra links, support different animations, and support alternate page shapes (a multi-section portfolio-style page: nav bar, philosophy/about blurb, work preview, contact). Follow-up requests, same day: propose additional layouts and improvements; adopt a dedicated admin configuration page pattern similar to the existing NSFW classifier config page; drop the landing-specific custom CSS idea in favour of the already-existing global custom CSS/JS mechanism; make `LandingConfig.vue` the single home for the entire landing settings category; support manual (as well as automatic) curation of featured content, covering both photos and albums.

### Extra-Layout and Extra-Feature Brainstorm (2026-08-10) — what was proposed and what was chosen

A brainstorming pass proposed 5 additional layout candidates and ~10 non-layout improvements, tiered by cost/value. The user selected a subset — full Decision Cards for Q-054-04/05 are below; the deferred candidates are recorded here as backlog so the reasoning isn't lost.

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

### Decision Cards (Q-054-01..10)

These decisions shape the entire feature. Formatted per `docs/specs/4-architecture/spec-guidelines/open-questions-format.md`'s Decision Card template; `open-questions.md` carries the compressed index row for each, this is the full detail it points to. All ten were resolved the same day (2026-08-10) via direct user confirmation, most via an `AskUserQuestion` prompt with the options below offered explicitly.

---

#### ❓ Q-054-01 · Landing page architecture shape

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ (**recommended**) Option A – Multiple named layout templates

**Question**
Should the landing page's variety come from several named, admin-selectable layout templates, from one single shape made maximally configurable, or from a fully modular section builder?

---

##### 🅰️ (**recommended**) Option A – Multiple named layout templates
- **Idea:** Admin picks one named layout (`classic`, `portfolio`, `minimal`, later `studio`) from a dropdown; each has its own bounded config sub-schema, with shared knobs (position, animation, links) where they overlap.
- **Spec impact:** Drives the entire `LandingLayoutType` enum, FR-054-01/02/14, and the whole per-layout component split (`LandingClassic.vue`/`LandingPortfolio.vue`/etc.).
- **Pros:**
  - ✅ Real structural variety — a portfolio-style page and a fullscreen hero genuinely look different, which a single-shape design can't achieve.
  - ✅ Bounded scope — each layout is a fixed, plannable increment, unlike an open-ended composer.
  - ✅ Matches existing Lychee precedent (`AlbumHeaderSize`'s `half_screen`/`full_screen`) of enum-driven variety.
- **Cons:**
  - ❌ Adding a 5th layout later means new components, not just new config — less flexible long-term than a true builder.

---

##### 🅱️ Option B – One mega-configurable single-shape hero
- **Idea:** Keep today's single fullscreen-hero structure, but make every piece of it (sections, position, animation, links) configurable via toggles.
- **Spec impact:** No new layout enum; everything stays a variant of the current hero.
- **Pros:**
  - ✅ Smallest possible diff from today's code.
  - ✅ No risk of layout-specific regressions since there's only one shape.
- **Cons:**
  - ❌ Cannot produce a portfolio-style, multi-section page shape at all — fails the core ask.
  - ❌ Toggle count grows unboundedly as more variety is requested, since there's no structural escape valve.

---

##### 🅲 Option C – Fully modular/reorderable section builder
- **Idea:** Admin composes the page from a reorderable list of independent sections (hero, gallery preview, text block, links, footer), each configurable on its own.
- **Spec impact:** Would require a section-ordering data model, a drag-and-drop admin UI, and per-section renderer registration — closer to a page builder than a feature.
- **Pros:**
  - ✅ Maximum long-term flexibility — any future layout is just a new section arrangement.
- **Cons:**
  - ❌ By far the largest scope of the three — open-ended UI/data-model work with no natural stopping point.
  - ❌ Highest regression risk (every section interacts with every other).

**Next action**
Resolved 2026-08-10 — Option A chosen as recommended. Encoded in FR-054-01/02/14 and the Non-Goals entry ruling out Option C (documented as a Follow-up instead).

---

#### ❓ Q-054-02 · Frontend tree scope (v7 vs v8)

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ (**recommended**) Option A – v8 only

**Question**
Lychee is mid-migration from legacy PrimeVue (v7) to Nuxt UI (v8, Feature 049). Should this feature's new landing-page work target v8 only, or ship full parity in both trees?

---

##### 🅰️ (**recommended**) Option A – v8 only
- **Idea:** Build the new configurable landing page only in `resources/js/v8/**`. `resources/js/v7/views/Landing.vue` keeps rendering today's static page unchanged, forever.
- **Spec impact:** NFR-054-08; S-054-20 (v7 untouched); no `LandingClassic.vue`/etc. twins under `v7/`.
- **Pros:**
  - ✅ Matches the precedent already set by Feature 051 (v8-only Admin Setup Page) for new UI-heavy work.
  - ✅ Avoids sinking effort into a UI stack actively being retired by Feature 049.
  - ✅ Halves the frontend implementation and manual-verification surface for this feature.
- **Cons:**
  - ❌ Installs still running v7-only (pre-cutover) never get the new landing capabilities.

---

##### 🅱️ Option B – Both v7 and v8
- **Idea:** Implement full parity in both the legacy PrimeVue tree and the new Nuxt UI tree.
- **Spec impact:** Would double every frontend increment (I7–I11) into v7/v8 pairs.
- **Pros:**
  - ✅ No install is left behind regardless of migration status.
- **Cons:**
  - ❌ Doubles frontend scope for a stack (v7) with a known, planned retirement date.
  - ❌ Every future landing tweak would need to land twice, for the life of the v7→v8 transition window.

**Next action**
Resolved 2026-08-10 — Option A chosen as recommended. Encoded in NFR-054-08 and S-054-20.

---

#### ❓ Q-054-03 · SE (Supporter Edition) gating

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅱️ Option B – Classic free forever, new layouts/animations SE-exclusive *(user-selected — overrides the originally recommended Option A, same pattern as Feature 052's Q-052-07)*

**Question**
Should every part of this feature (all layouts, positions, animations, links) be free on every install, or should the new layouts/animations be reserved for Lychee SE (paid supporter tier), mirroring how white-labelling (Feature 039) is SE-exclusive?

---

##### 🅰️ (originally recommended) Option A – Free for everyone
- **Idea:** All layouts, positioning, animations, and extra links available on every install.
- **Spec impact:** No SE-fallback logic anywhere; `LandingLayoutType`/`LandingAnimationPreset` resolve directly from config with no `request()->verify()` check.
- **Pros:**
  - ✅ Matches the precedent of Feature 025 (dynamic landing backgrounds) and the album-hero position/color feature — neither is SE-gated.
  - ✅ Simpler resolution logic — no fail-safe fallback paths to test.
- **Cons:**
  - ❌ No product incentive for the new, more elaborate layouts to drive SE adoption.

---

##### 🅱️ Option B – Classic free forever; portfolio/minimal/studio + premium animations SE-exclusive *(chosen)*
- **Idea:** `classic` and the current default animation (`classic_fade`) remain free forever. The new layouts and 3 new animation presets require SE, falling back fail-safe to the free defaults otherwise.
- **Spec impact:** FR-054-02/06's SE-fallback resolution (mirrors `InitConfig::is_white_label_enabled`'s fail-safe shape); NFR-054-02.
- **Pros:**
  - ✅ Mirrors Feature 039's precedent of gating premium visual/branding capability behind SE.
  - ✅ The underlying flexibility *mechanism* (config-driven, no fork needed) still benefits every install — only the premium *presentation* is gated.
- **Cons:**
  - ❌ Adds fail-safe fallback logic and its own test matrix (SE-on/SE-off × every gated field).

**Next action**
Resolved 2026-08-10 — user explicitly chose Option B over the recommended Option A. Encoded in FR-054-02/06, NFR-054-02. Extra links (FR-054-11..13) and hero text position (FR-054-04) were deliberately kept in Option A's "always free" category even under this resolution — they extend already-free existing features (footer social links; album hero position) rather than introducing a new page shape, so SE-gating them would have been inconsistent with those precedents.

---

#### ❓ Q-054-04 · Additional layouts beyond classic/portfolio/minimal

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – `studio` (client-login-first) only

**Question**
A brainstorm surfaced 5 additional layout candidates (studio, mosaic/grid-first, "coming soon," split-screen editorial, cinematic/video-hero). How many, if any, should be added to this feature's scope now?

---

##### 🅰️ Option A – `studio` only *(chosen)*
- **Idea:** Add exactly one more layout: client-login-first, for studios whose visitors are mostly returning clients with private albums rather than public browsers.
- **Spec impact:** FR-054-21/22, `LandingLayoutType` gains a 4th value, new `LandingStudio.vue`.
- **Pros:**
  - ✅ Genuinely different information architecture (primary CTA → login, not gallery) — not just a re-skin of an existing layout.
  - ✅ Small, bounded addition — reuses the existing `login` route, no new auth (NFR-054-10).
- **Cons:**
  - ❌ Still leaves 4 considered candidates on the backlog.

---

##### 🅱️ Option B – `studio` + Mosaic + "Coming soon"
- **Idea:** Add three layouts in this feature instead of one.
- **Spec impact:** Would have added a photo-level automatic query (mosaic) and a near-trivial 5th layout in the same increment set.
- **Pros:**
  - ✅ More variety delivered in one pass.
- **Cons:**
  - ❌ Meaningfully larger scope for one feature that's already grown substantially (4 layouts, extra links, featured content, dedicated admin page).

---

##### 🅲 Option C – None for now
- **Idea:** Ship with exactly classic/portfolio/minimal; revisit additional layouts as a later feature.
- **Cons:**
  - ❌ Leaves the "studio/client-focused" use case — a real, distinct need — entirely unaddressed.

**Next action**
Resolved 2026-08-10 — Option A chosen. Mosaic/grid-first, "coming soon," split-screen editorial, and cinematic/video-hero recorded as deferred backlog (see Extra-Layout Brainstorm above) — video-hero specifically flagged as contradicting this feature's inherited Non-Goal from Feature 025, so it should get its own explicit scope decision if ever pursued, not a quiet reversal.

---

#### ❓ Q-054-05 · Non-layout improvements to fold into this feature

**Status:** Resolved (partially superseded by Q-054-07)
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ (**recommended**) Option A – 3 cheap/high-value items only

**Question**
Beyond the layouts, which of ~10 brainstormed non-layout improvements (Contact-form surfacing, configurable CTA text, scroll-down indicator, raw CSS override, second About-section image, testimonials block, etc.) should be folded into this feature now?

---

##### 🅰️ (recommended) Option A – 3 cheap/high-value items only
- **Idea:** Contact-form surfacing on `portfolio`/`minimal` (Feature 022 reuse, wiring only), configurable CTA button text, `portfolio` scroll-down indicator. Nothing else.
- **Pros:**
  - ✅ All three are small, low-risk, and directly serve requirements already in scope.
- **Cons:**
  - ❌ Leaves the "escape hatch for anything not covered" need (raw CSS) unaddressed via a structured field.

---

##### 🅱️ Option B – Option A + raw CSS override *(chosen at the time; later reversed — see Q-054-07)*
- **Idea:** The 3 items above, plus a new `landing_custom_css` admin-authored free-text field, trusted the same way `footer_additional_text` already is.
- **Spec impact (as originally chosen):** New FR-054-26, NFR-054-11.
- **Pros:**
  - ✅ High leverage for Lychee's self-hosted/technical audience — avoids needing a new structured knob for every future request.
- **Cons:**
  - ❌ **Turned out to be redundant** — Lychee already has a global custom CSS/JS mechanism covering every page. This con is *why* Q-054-07 later reversed this part of the decision (see below); it was not apparent until the existing mechanism was pointed out.

---

##### 🅲 Option C – Option B + testimonials/client-logos block
- **Idea:** Everything in Option B, plus a new admin-managed testimonials CRUD list.
- **Cons:**
  - ❌ Most scope of the four options — a whole new CRUD list is a meaningful feature on its own, not a small addition.

---

##### 🅳 Option D – None; treat brainstorm as backlog notes only
- **Cons:**
  - ❌ Leaves clear, low-cost wins (Contact surfacing, CTA text) on the table for no reason.

**Next action**
Resolved 2026-08-10 — Option B chosen initially (FR-054-23/24/25/26). The custom-CSS half of Option B was independently revisited and reversed the same session once the existing global mechanism was identified — see Q-054-07's card, which supersedes that part of this decision. FR-054-23/24/25 (Contact surfacing, CTA text, scroll indicator) stand as originally resolved. Testimonials/client-logos (Option C's addition) and second About-section image remain deferred backlog.

---

#### ❓ Q-054-06 · Admin UI approach

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Dedicated `LandingConfig.vue` page

**Question**
Should the new landing configs be exposed only through the existing flat generic Settings list, or through a dedicated, curated admin page — following the pattern the user pointed at, Feature 045's `NsfwConfig.vue`?

---

##### 🅰️ Option A – Dedicated page mirroring `NsfwConfig.vue` *(chosen)*
- **Idea:** New `LandingConfig.vue`: `UTabs` page with curated `Fieldset`-grouped sections, reading/writing through the exact same `SettingsService.getAll()`/`setConfigs()` API the flat list already uses — no new config backend, just a better-organized UI.
- **Spec impact:** FR-054-19; new admin route/tile (`landing-config`, group `core`).
- **Pros:**
  - ✅ Confirmed (by reading `NsfwConfig.vue`) this is purely additive — same storage, same endpoints.
  - ✅ Groups ~20 interrelated fields logically instead of one long flat list.
  - ✅ Extra-link and (later) featured-item CRUD naturally become additional tabs on the same page, exactly like `NsfwConfig.vue`'s own second "Presets" tab.
- **Cons:**
  - ❌ New Vue component and admin-tile registration work, versus zero new frontend code for the flat-list-only approach.

---

##### 🅱️ Option B – Flat generic settings list only (original draft assumption)
- **Idea:** Rely entirely on `AllSettings.vue`'s existing generic, metadata-driven rendering — no bespoke admin Vue at all.
- **Pros:**
  - ✅ Zero new frontend component work.
- **Cons:**
  - ❌ ~20 interrelated settings (existing + new) scattered through one long list with no logical grouping — poor UX for a feature this configurable.
  - ❌ Doesn't match the pattern the user explicitly asked for.

**Next action**
Resolved 2026-08-10 — Option A chosen, directly prompted by the user citing `NsfwConfig.vue` as the desired pattern. Encoded in FR-054-19. Later widened further by Q-054-08.

---

#### ❓ Q-054-07 · Landing-specific custom CSS/JS

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Drop it; reuse the existing global mechanism

**Question**
Q-054-05 (Option B) had added a landing-specific `landing_custom_css` field. Should this feature keep a landing-specific styling escape hatch, or rely on whatever custom-styling mechanism Lychee already has?

---

##### 🅰️ Option A – Reuse the existing global custom CSS/JS mechanism *(chosen)*
- **Idea:** Confirmed by code read that Lychee already has `Settings` → raw CSS/JS → `dist/user.css`/`dist/custom.js` (`SettingsController::setCSS()`/`setJS()`), loaded unconditionally on **every** page — including every landing layout — via `<x-meta>` in the shared `vueapp.blade.php` shell (`Meta::$user_css_url`/`$user_js_url`). Drop `landing_custom_css` entirely; admins target `#landing`/the active layout's selectors themselves in the existing global field.
- **Spec impact:** FR-054-26/NFR-054-11 removed; new Non-Goal added citing the existing mechanism; NFR-054-09's dead-end-guardrail clause rewritten to reference the *pre-existing*, not new, risk.
- **Pros:**
  - ✅ Zero new code — the capability already exists and already reaches the landing page today.
  - ✅ Avoids a second, redundant, inconsistent styling mechanism living alongside the first.
  - ✅ Directly matches the explicit direction given: "it is on the user to figure out how to configure things for them to work."
- **Cons:**
  - ❌ No landing-specific scoping/guardrails (e.g. no automatic `#landing`-only wrapping) — admin must know to target the right selectors, same as today for every other page.

---

##### 🅱️ Option B – Keep `landing_custom_css` as designed
- **Idea:** Landing-specific field, admin-authored, injected as a scoped `<style>` block after all other landing styles.
- **Pros:**
  - ✅ Slightly safer default scoping (auto-wrapped to the landing root) than the global field.
- **Cons:**
  - ❌ Redundant with a mechanism that already exists and already works site-wide.
  - ❌ Two different "how do I add custom CSS" answers depending on which page you're customizing — inconsistent admin experience.

**Next action**
Resolved 2026-08-10 — Option A chosen, directly instructed by the user ("We don't want custom css/js for the landing, we can just reuse the normal custom"). Supersedes the custom-CSS half of Q-054-05's resolution. FR-054-26 and NFR-054-11 removed from the spec; new Non-Goal added in their place.

---

#### ❓ Q-054-08 · `LandingConfig.vue` scope — partial vs. full category absorption

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Absorb the entire `Mod Welcome` category

**Question**
`NsfwConfig.vue` (Q-054-06's template) curates only a *subset* of settings keys, leaving them also visible in the flat list. Should `LandingConfig.vue` do the same (cover only the 11 new keys), or absorb the entire pre-existing `Mod Welcome` landing-settings category too?

---

##### 🅰️ Option A – Absorb the entire category, filter it from the flat list *(chosen)*
- **Idea:** `LandingConfig.vue`'s Settings tab covers every `Mod Welcome` key — pre-existing (title, subtitle, background modes, logos, footer/social/contact-form fields) and new. Because that makes the flat list's "Landing page" category fully redundant, patch `SettingsController::getAll()` to filter `Mod Welcome` out of the flat list entirely (same mechanism Feature 052's Q-052-07 already established for a different category).
- **Spec impact:** FR-054-19 widened; new FR-054-27 (filtering); S-054-31.
- **Pros:**
  - ✅ Exactly one place to configure any landing setting — no split attention between two UIs.
  - ✅ Matches the explicit instruction ("LandingConfig.vue should absorb the entire Mod Welcome category").
- **Cons:**
  - ❌ Deliberate deviation from the `NsfwConfig.vue` template it's otherwise mirroring (justified below).
  - ❌ Requires enumerating the category's exact current key membership at implementation time (not done at spec time — flagged as a plan.md Assumption/Drift-Gate item).

---

##### 🅱️ Option B – Absorb only the 11 new keys (original design, mirrors NSFW exactly)
- **Idea:** `LandingConfig.vue` covers just the new keys; pre-existing landing keys stay in the flat list, unfiltered — identical shape to how `NsfwConfig.vue` treats its `ai_vision_nsfw_*` keys.
- **Pros:**
  - ✅ Exact precedent match, no deviation to justify.
  - ✅ Smaller diff — no `SettingsController` filter patch needed.
- **Cons:**
  - ❌ Once the entire category has a dedicated home, leaving old keys in the flat list too is pure duplication, not "harmless overlap" — unlike NSFW, where its keys are a small subset among many unrelated ones in the flat list.

**Next action**
Resolved 2026-08-10 — Option A chosen, directly instructed by the user. Encoded in FR-054-19/27, S-054-31. The deviation from Option B (the NSFW precedent) is explicitly justified in FR-054-27's own text: NSFW curates a subset among unrelated keys (duplication harmless), this page curates a whole category (duplication redundant).

---

#### ❓ Q-054-09 · Featured-content curation model

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Automatic by default, with full manual curation supported

**Question**
Should the `portfolio` layout's "recent work" section be fully automatic (most-recently-published public albums), fully manual (admin hand-picks items), or both — and if manual, albums only or photos too? This question went through three stages: the original spec proposed automatic-only; the user first asked for fully manual curation of photos-or-albums; then refined to automatic-by-default with manual also supported.

---

##### 🅰️ Option A – Automatic by default, full manual curation also supported *(chosen, final)*
- **Idea:** New `landing_featured_items_mode` enum (`automatic`/`manual`, default `automatic`). Automatic mode is unchanged from the original design (N latest public albums). Manual mode is a new `LandingFeaturedItem` CRUD (mirrors `LandingLink`'s shape) letting the admin curate an ordered list of specific **photos and/or albums**, resolved by direct lookup that deliberately bypasses the visibility-policy check (mirrors Feature 025's `photo_id` background-mode precedent).
- **Spec impact:** FR-054-10 (rewritten), FR-054-28 (CRUD), FR-054-29 (manual resolution); new `LandingFeaturedItem` model/migration/CRUD; `LandingConfig.vue` gains a 3rd "Featured" tab.
- **Pros:**
  - ✅ Zero-effort default (automatic) for admins who don't want to curate anything.
  - ✅ Full creative control (manual, mixed photo/album) for admins who do.
  - ✅ Manual mode reuses the exact `LandingLink` CRUD shape and the already-existing Search endpoint for its picker — no new architectural pattern, just applying proven ones twice.
- **Cons:**
  - ❌ Largest-scope option of the three — a full new model/CRUD/tab, not a config tweak.

---

##### 🅱️ Option B – Fully manual only (user's first stated preference)
- **Idea:** Drop automatic mode; admin must always explicitly curate photos/albums.
- **Cons:**
  - ❌ No low-effort path — every `portfolio` install wanting this section must curate manually, even if "just show recent work" was all they wanted.
  - ❌ Discarded the working automatic-mode design (and its Feature-025-aligned query reuse) for no benefit once Option A showed both could coexist.

---

##### 🅲 Option C – Automatic only (original spec proposal / my initial recommendation)
- **Idea:** N most-recently-published public albums, no manual override, matching how Feature 025's `latest_album_cover` background mode already works.
- **Pros:**
  - ✅ Simplest possible implementation — no new model, no new CRUD, no new tab.
- **Cons:**
  - ❌ No way for an admin to say "always show *this* photo/album regardless of publish date" — the explicit ask this question was raised to address.

**Next action**
Resolved 2026-08-10 — Option A chosen after two rounds of refinement (C → B → A). Encoded in FR-054-10/28/29 and DO-054-03/04.

---

#### ❓ Q-054-10 · `LandingLink.icon` input method

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ (**recommended**) Option A – Free-text identifier

**Question**
Each admin-added extra link can show a small icon. Should the admin type a raw icon identifier directly, or pick one from a curated visual picker?

---

##### 🅰️ (recommended) Option A – Free-text Iconify/Lucide identifier *(chosen)*
- **Idea:** Admin types e.g. `lucide:instagram` into a text field. Falls back to a generic `lucide:link` icon when left empty.
- **Spec impact:** FR-054-11's `icon` field definition; no new frontend component.
- **Pros:**
  - ✅ Zero new UI component to build/maintain.
  - ✅ Consistent with the general direction given for this feature — configuration is on the admin to figure out.
- **Cons:**
  - ❌ Admin needs to know or look up valid Iconify/Lucide names — no in-app discovery.

---

##### 🅱️ Option B – Visual icon picker
- **Idea:** A curated dropdown/grid of common icons to click instead of typing.
- **Pros:**
  - ✅ No need to know icon-library naming conventions.
- **Cons:**
  - ❌ Real extra frontend work — a picker component plus a curated icon list to build and keep current.

**Next action**
Resolved 2026-08-10 — Option A chosen as recommended. Encoded in FR-054-11. A curated picker remains a documented Follow-up if free-text proves too friction-heavy in practice.

---

#### ❓ Q-054-11 · Does `landing_show_stats` render on `minimal` as well as `portfolio`?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – `portfolio` only

**Question**
FR-054-09 (as originally drafted) said stats "may display on both `portfolio` and `minimal`," but FR-054-17's full description of `LandingMinimal.vue` (and its mockup/task) never included a stats block. This inconsistency was found during review, not raised by the user — which layout(s) is correct?

---

##### 🅰️ Option A – `portfolio` only *(chosen)*
- **Idea:** Fix FR-054-09 to drop "and minimal"; stats are `portfolio`-exclusive, matching the already-established rule that featured content is also excluded from `minimal` "to preserve the minimal intent" (FR-054-10/17).
- **Spec impact:** FR-054-09 reworded.
- **Pros:**
  - ✅ Internally consistent with `minimal`'s one other existing supplementary-content exclusion (featured content) — one rule, not two different ones for two similar content blocks.
  - ✅ No new work — `LandingMinimal.vue`'s design (FR-054-17, mockup, T-054-34) never included a stats element in the first place; this is a spec-text fix, not an implementation change.
- **Cons:**
  - ❌ None identified.

---

##### 🅱️ Option B – Add stats to `minimal` too
- **Idea:** Extend `LandingMinimal.vue`'s design to include a stats line, matching the original (inconsistent) FR-054-09 wording.
- **Cons:**
  - ❌ Contradicts `minimal`'s stated design intent ("compact, distraction-free") for no requested benefit.
  - ❌ Would require reopening FR-054-17/the mockup/T-054-34, none of which anticapated it.

**Next action**
Resolved 2026-08-10 (found and fixed during review, no user input needed) — Option A chosen. FR-054-09 corrected to `portfolio`-only.

---

#### ❓ Q-054-12 · Does the Featured tab's `Search` picker actually surface private content to the admin?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Confirmed as designed — no change needed

**Question**
FR-054-29/NFR-054-03 deliberately let manual-mode featured items include *private* photos/albums (admin-trusted, no policy check at resolution time) — mirroring Feature 025's `photo_id` background mode. But FR-054-28 said the Featured tab's picker reuses `GET /api/v2/Search` "as-is." If `Search` itself filters out private content, an admin could never actually *find* a private photo/album to select it in the first place, making the "manual mode can feature private content" promise unreachable from the UI. Does `Search` actually surface private content to an authenticated admin session?

---

##### 🅰️ Confirmed as designed — no change needed *(the actual finding)*
- **Investigation:** Read `App\Http\Controllers\Gallery\SearchController::search()` → delegates to `App\Actions\Search\AlbumSearch`/`PhotoSearch`, both of which call `AlbumQueryPolicy::applyVisibilityFilter()`/`PhotoQueryPolicy::applySearchabilityFilter()`. Read those methods: `AlbumQueryPolicy::applyVisibilityFilter()` (`app/Policies/AlbumQueryPolicy.php:62`) opens with `if ($user?->may_administrate === true) { return $query; }` — an authenticated admin's query is returned **completely unrestricted**, bypassing the visibility filter entirely. The Featured tab is admin-only (NFR-054-05), so every request to `Search` from that picker carries an admin session.
- **Conclusion:** `GET /api/v2/Search`, reused exactly as specced, already returns private/unpublished photos and albums to the admin driving the Featured tab's picker. FR-054-29's "manual mode can feature private content" is reachable end-to-end with zero extra backend work.
- **Spec impact:** FR-054-28's text updated to cite this confirmation explicitly, so a future reader doesn't have to re-derive it.

**Next action**
Resolved 2026-08-10 (investigated during review) — confirmed correct as designed, no spec change beyond adding the citation.

---

#### ❓ Q-054-13 · SE-gated dropdown options: actually disabled, or just badged? Stored or effective value shown?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Badged but selectable; always shows the stored value

**Question**
UI-054-01/02/04 said non-SE options show as "disabled/badge SE" — a slash implying either could be true, never decided. Separately, undecided: when the stored config differs from what's currently rendering publicly (e.g. `landing_layout=portfolio` stored, but SE is inactive so `classic` actually renders), does `LandingConfig.vue` show the stored value or the live effective value?

---

##### 🅰️ Option A – Badged but selectable; shows the stored value *(chosen)*
- **Idea:** SE-only options get a small "SE" badge but remain clickable/selectable regardless of current SE status. `LandingConfig.vue` always edits and displays the raw stored config value, never the post-fallback effective value `LandingPageResource` computes for public rendering.
- **Spec impact:** UI-054-01/02/04 reworded; new UI-054-08.
- **Pros:**
  - ✅ Consistent with FR-054-02/06's own fail-safe design — the *stored* value is always meaningful and safe to keep, since the effective-resolution layer already handles gating on every read. Nothing bad happens if a non-SE admin "saves" `portfolio`.
  - ✅ Lets a non-SE admin pre-configure their preferred premium layout now, so it activates automatically the moment they upgrade — no need to remember to reconfigure later.
  - ✅ Matches the existing `is_se_preview_enabled` pattern already in this codebase (`InitConfig`) of letting non-SE users explore/preview gated capability rather than hard-blocking the UI.
  - ✅ "Stored, not effective" is simpler to implement and reason about — `LandingConfig.vue` is an editing surface, not a live preview.
- **Cons:**
  - ❌ A non-SE admin could be confused why their saved `portfolio` choice isn't showing on the public page — mitigated by the visible SE badge explaining why.

---

##### 🅱️ Option B – Actually disabled (unselectable) for non-SE
- **Idea:** Non-SE options can't be clicked/selected at all in the dropdown.
- **Cons:**
  - ❌ Blocks the legitimate "pre-configure before upgrading" use case Option A enables.
  - ❌ More UI state to build (disabled-option styling + explaining why) for a worse outcome.

**Next action**
Resolved 2026-08-10 (found and resolved during review) — Option A chosen. Encoded in UI-054-01/02/04/08.

---

#### ❓ Q-054-14 · Orphaned-looking `FR-054-26`/`S-054-27` gaps

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Document the gap, don't renumber

**Question**
The FR table jumps FR-054-25 → FR-054-27, and the Scenario table jumps S-054-26 → S-054-28, with nothing explaining why — looks like an accidental gap left over from a prior revision (it is: both were the dropped `landing_custom_css` requirement/scenario from Q-054-07). Should this be fixed by renumbering everything after the gap, or documented as intentional?

---

##### 🅰️ Option A – Document the gap; never renumber *(chosen)*
- **Idea:** Add a one-line note directly above each table explaining that the missing number was retired along with `landing_custom_css`, per this repo's own numbering convention ("never reuse retired numbers," `feature-numbering-conventions.md`).
- **Pros:**
  - ✅ Zero risk of breaking the many existing cross-references to `FR-054-27..29`/`S-054-28..31` that a renumber would force touching everywhere (plan.md, tasks.md, this file's own Interface Catalogue).
  - ✅ Directly matches this repo's stated convention for feature numbers, applied consistently to sub-IDs too.
- **Cons:**
  - ❌ A reader skimming only the table (not the note) could still wonder — mitigated by making the note impossible to miss (directly above the table header).

---

##### 🅱️ Option B – Renumber to close the gap
- **Idea:** Shift FR-054-27→26, 28→27, 29→28, and all S-054 IDs above 26 down by one.
- **Cons:**
  - ❌ Touches every file in this feature (spec/plan/tasks) and every cross-reference between them, for purely cosmetic benefit.
  - ❌ Contradicts the numbering convention's spirit even if not its literal (feature-level, not sub-ID-level) text.

**Next action**
Resolved 2026-08-10 (found and fixed during review) — Option A chosen. Explanatory notes added directly above both tables.

---

#### ❓ Q-054-15 · Is `router/paths.ts` the correct file citation?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Both files are correct and necessary — citation clarified, not wrong

**Question**
Spec/plan repeatedly cite `router/paths.ts` as *the* route-registration file for the new `landing-config` route. Review flagged that the actual per-tree router file is `resources/js/v8/router/routes.ts` — implying the citation might be wrong.

---

##### 🅰️ Both files are real and both matter *(the actual finding)*
- **Investigation:** Confirmed both files exist: `resources/js/router/paths.ts` (a "shared, component-free route manifest" per its own header comment — "Both the v7 router... and the v8 router... attach components to this same list") and `resources/js/v8/router/routes.ts` (where v8 actually imports view components — `NsfwConfig`, `Webhooks`, etc. — and maps them onto the names/paths already declared in `paths.ts`).
- **Conclusion:** The original citation of `paths.ts` wasn't *wrong*, but it was *incomplete* — registering a new route needs an entry in **both** files: the name/path pair in `paths.ts`, and the component import + mapping in `routes.ts`. This mirrors exactly how `nsfw-config` itself is wired.
- **Spec impact:** FR-054-19 reworded to name both files explicitly with their distinct roles.

**Next action**
Resolved 2026-08-10 (investigated during review) — citation clarified/completed, not corrected-as-wrong. FR-054-19 now names both files.

---

#### ❓ Q-054-16 · Is the Q-052-07 citation for `Mod Welcome` filtering actually verifiable?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Confirmed — exact code line cited

**Question**
FR-054-27 says filtering `Mod Welcome` from the flat list uses "the same mechanism Feature 052's Q-052-07 resolution already established." Review couldn't find a literal `Q-052-07` reference in code/comments to confirm this specific citation was accurate rather than assumed.

---

##### 🅰️ Confirmed with an exact citation *(the actual finding)*
- **Investigation:** Read `app/Http/Controllers/Admin/SettingsController.php`. Line 83, inside `getAll()`: `->when(config('features.enable-caching') === false, fn ($q) => $q->where('cat', '!=', 'Mod Cache'))`. This is precisely the category-exclusion mechanism Feature 052's completed work (Q-052-07, per `roadmap.md`'s Feature 052 entry) added for the `'Mod Cache'` category.
- **Conclusion:** The citation was accurate, just not pinned to a line number. FR-054-27 now cites `SettingsController.php:83` directly and shows the exact existing line being extended with an equivalent unconditional `Mod Welcome` clause.
- **Spec impact:** FR-054-27 reworded with the concrete citation.

**Next action**
Resolved 2026-08-10 (investigated during review) — citation confirmed accurate and made verifiable.

---

#### ❓ Q-054-17 · No existing `Reorder` endpoint precedent — what should the actual contract be?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – Full-list resync, `{ ids: string[] }`, transactional, reject partial sets

**Question**
FR-054-12/28 described `PATCH /LandingLink/Reorder` and `PATCH /LandingFeaturedItem/Reorder` as "mirroring" `WebhookController`'s CRUD shape — but a repo-wide search for any existing bulk-reorder endpoint returned nothing. There is no precedent to mirror for this specific piece. What should the actual contract be?

---

##### 🅰️ Option A – Full-list resync *(chosen)*
- **Idea:** `{ ids: string[] }` must contain **every** existing row's ID, in the desired order — a set mismatch (missing or extra ID) is rejected outright, not partially applied. Sets `sort_order` = array index, wrapped in a DB transaction; response mirrors the index endpoint.
- **Spec impact:** FR-054-12 rewritten with the full contract; FR-054-28 references it instead of re-describing it.
- **Pros:**
  - ✅ Zero ambiguity about "what happens to rows omitted from the payload" — there is no such case, by construction.
  - ✅ Matches what a drag-reorder UI naturally has on hand anyway (it's already rendering the complete list, so sending the complete list back is no extra client-side work).
  - ✅ Transactional — no half-reordered state visible mid-request.
- **Cons:**
  - ❌ A client bug that drops an ID from its local list would 422 instead of silently reordering fewer rows — arguably a pro (fails loud, not silently).

---

##### 🅱️ Option B – Partial reorder (only listed IDs move; omitted rows keep their old `sort_order`)
- **Idea:** Accept any subset of IDs; only touch the ones present.
- **Cons:**
  - ❌ Ambiguous interleaving — if rows 1,3,5 are reordered but 2,4 are untouched, the resulting overall order depends on exactly how old and new `sort_order` values interleave, which is hard to reason about and easy to get wrong.
  - ❌ No client in this feature ever needs partial reorder (`LandingConfig.vue`'s tabs always have the full list loaded already).

**Next action**
Resolved 2026-08-10 (designed during review, since no precedent existed to simply adopt) — Option A chosen for both `LandingLink` and `LandingFeaturedItem` Reorder endpoints (identical contract, not independently designed twice).

---

#### ❓ Q-054-18 · `FR-054-11`'s missing `icon` length validation

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Add the missing constraint

**Question**
FR-054-11's validation column lists constraints for `url`/`label`/enum fields but never mentions `icon`, despite the `landing_links.icon` column being a bounded `string(255)` (MIG-054-02). Simple spec-completeness gap — was this deliberate or an oversight?

---

##### 🅰️ Add the missing constraint *(chosen — it was an oversight)*
- **Idea:** State `icon` nullable, ≤255 chars, matching the DB column width, in FR-054-11's validation path.
- **Pros:**
  - ✅ Prevents a silent DB-level truncation/error path that the spec's own validation table should have caught.
- **Cons:**
  - ❌ None — this was a straightforward gap, not a real design tradeoff.

**Next action**
Resolved 2026-08-10 (found and fixed during review) — FR-054-11 updated with the `icon` length constraint.

---

#### ❓ Q-054-19 · Should `studio`'s secondary link label be configurable too?

**Status:** Resolved
**Feature:** 054 – Configurable Landing Page
**Preferred option:** 🅰️ Option A – No, fixed translation only (deliberate)

**Question**
`landing_cta_text` overrides only the *primary* CTA. `studio`'s secondary "view public gallery" link has no equivalent override. Was this a deliberate scope line, or an unnoticed omission?

---

##### 🅰️ Option A – Deliberate; fixed translation only *(chosen)*
- **Idea:** Confirm this as intentional minimalism and say so explicitly in the spec, rather than leaving it silently unaddressed.
- **Spec impact:** FR-054-21 and a new Non-Goals bullet both now state this explicitly.
- **Pros:**
  - ✅ Keeps `landing_cta_text`'s scope simple and singular ("the primary CTA," not "every CTA-like link on every layout") — avoids a slippery slope toward overriding every piece of copy on the page individually.
  - ✅ The secondary link is deliberately de-emphasized (smaller, secondary) — it isn't the layout's main message the way the primary CTA is, so it doesn't carry the same "admin wants their own words here" weight.
- **Cons:**
  - ❌ An admin who wants to customize *that* specific string can't, without a future follow-up field.

---

##### 🅱️ Option B – Add a second override field
- **Idea:** New `landing_secondary_cta_text` (or similar) config.
- **Cons:**
  - ❌ Opens the door to needing per-element text overrides throughout every layout — real scope creep for a corner case nobody asked to customize.

**Next action**
Resolved 2026-08-10 (found and resolved during review) — Option A chosen; explicitly documented as deliberate rather than left ambiguous.

### Why a landing-scoped `LandingTextPosition` enum instead of reusing `AlbumTitlePosition`

Both enums have identical values (5 corner/center positions) and it would be technically possible to import `App\Enum\AlbumTitlePosition` directly. This spec chooses a small, deliberately duplicated `LandingTextPosition` enum instead, because:
1. Albums and the landing page are different bounded contexts — coupling them through a shared enum means any future album-hero-specific change (e.g. adding a 6th position for albums only) forces a decision about whether landing inherits it.
2. The duplication cost is five string cases, not meaningful maintenance burden.

This is a documented implementation-detail choice, not a resolved open question — no ADR required (below the "architecturally significant" bar).

### Follow-ups Considered Out of Scope

- A true modular/reorderable section builder (drag sections, not just pick a layout) — bigger feature, revisit if 4 named layouts prove insufficient.
- Sharing the `POSITION_CLASSES` Tailwind mapping between `AlbumHeaderPanel.vue` and the new landing components via a common composable — both currently duplicate a small position→class map; worth a follow-up dedup pass once the landing implementation stabilises, not required for this feature's completion.
- Mosaic/grid-first layout, "coming soon" layout, split-screen editorial layout, per-layout background video support (see Extra-Layout brainstorm above).
- Second image slot for the About section; testimonials/client-logos CRUD block; dedicated hero tagline field (see Extra-Feature brainstorm above).
- A live WYSIWYG preview in the admin settings page (today's mock-up pattern is static ASCII/description text like every other Lychee settings section).
- A curated icon picker for `LandingLink.icon` (see Q-054-10) — free-text only for now.

---

*Last updated: 2026-08-10 (rev 6 — investigated and resolved 9 review-flagged questions Q-054-11..19: fixed `landing_show_stats` to be `portfolio`-only (was inconsistently claimed to also render on `minimal`); confirmed the Featured tab's `Search`-based picker already surfaces private content to admin sessions (`may_administrate` bypass); decided SE-gated dropdown options are badged-but-selectable and always show the stored (not effective) value; documented the intentional `FR-054-26`/`S-054-27` numbering gaps; clarified route registration needs both `router/paths.ts` and `resources/js/v8/router/routes.ts`; pinned the `Mod Welcome` filter's citation to `SettingsController.php:83`; designed the previously-unspecified `Reorder` endpoint contract from scratch (no precedent existed in this codebase) as a transactional full-list resync; added the missing `icon` length validation; confirmed `studio`'s secondary link label is deliberately non-configurable — full Decision Cards for all 9 added to spec.md Appendix; rev 5 — expanded all 10 originally-resolved questions into full Decision Cards per `spec-guidelines/open-questions-format.md`, replacing the prior loose prose in Appendix; `open-questions.md`'s index rows updated to cross-reference the card's A/B/C option lettering; rev 4 — dropped landing-specific custom CSS in favour of the existing global mechanism; `LandingConfig.vue` now absorbs the entire `Mod Welcome` category and is filtered from the flat list; featured content redesigned as automatic-default + full manual photo/album curation via new `LandingFeaturedItem` CRUD; confirmed `LandingLink.icon` as free-text; removed all named references to the external site that originally inspired the `portfolio` layout, per explicit instruction — layout is now described structurally only)*
