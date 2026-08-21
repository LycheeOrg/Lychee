# Feature 059 – Resolve Desktop UX Audit Findings

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-08-22 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/059-resolve-desktop-ux-findings/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/059-resolve-desktop-ux-findings/tasks.md` |
| Roadmap entry | #059 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications.

## Overview
A Playwright-driven UX audit of the v8 (Nuxt UI) frontend — `STUDY-MOBILE-v8.md` and `STUDY-DESKTOP-v8.md` at the repo root — surfaced 12 desktop-side findings (D1–D12). Before drafting this spec, four parallel codebase investigations re-grounded every actionable finding against the actual v8/backend source, since several of the study's screenshot-based hypotheses turned out to be imprecise or outright wrong once traced to code (see "Corrections to the source studies" below). This feature fixes the findings that are genuine bugs, all scoped to `resources/js/v8/**` and their backing PHP resources/controllers — modules touched: `resources/js/composables` (shared, forked per NFR-059-01), `resources/js/v8/views/gallery-panels`, `resources/js/v8/views/admin`, `resources/js/v8/views/webshop`, `resources/js/v8/components/headers`, `resources/js/v8/components/gallery/albumModule`, `app/Http/Resources/Shop`, `lang/en.json`.

**Corrections to the source studies** (findings below are written against the *corrected* understanding, not the original study wording):
- **D4 (right-click context menu) is not a bug.** The context-menu system (`resources/js/v8/composables/contextMenus/contextMenu.ts`'s `photoMenu()`, triggered via `UContextMenu` in `AlbumPanel.vue`) is fully implemented and correctly wired to photo thumbnails' native `contextmenu` event. What the audit saw (only a single "Highlight" item) is that same menu correctly rendering after its per-item `access` filter removed every entry gated on `albumStore.rights?.can_edit` — i.e. the tested album/context lacked edit rights. This is dropped from scope entirely (see Non-Goals, Q-059-01).
- **D5's "Bulk Album Edit already has a working mobile layout" is wrong.** `BulkAlbumEdit.vue` has zero responsive-specific markup — it is a plain `UTable`, structurally identical to the "broken" tables. What looked like a deliberate 2-column mobile list was actually its two narrowest leading columns (checkbox, Title, Owner) being the only ones that fit before the table's default `overflow-auto` container required horizontal scrolling. The actual working reference pattern in this codebase is `resources/js/v8/views/face-recog/FaceMaintenance.vue`, which hides non-critical `UTable` columns below the `sm` breakpoint via column `meta` classes and folds their content inline into the always-visible cell.
- **D9's "raw Laravel debug page" is not literal Whoops/Ignition HTML leaking through.** `axios-config.ts` sends every XHR with `X-Requested-With`/`Content-Type: application/json`, so Laravel's `expectsJson()` is true for these calls; `app/Exceptions/Handler.php` returns its JSON debug payload (`message`/`exception`/`file`/`line`/`trace`), which the app's *own* `resources/js/v8/views/Error.vue` renders via a global `window` `"error"` `CustomEvent` — that component (not a raw framework page) is what was seen. The absolute server file paths only appear because this dev environment's `.env` has `APP_DEBUG=true` (`.env.example` correctly defaults to `false`); this is expected dev-mode behavior, not a production information-disclosure bug. What *is* a real, fixable bug: `is_debug_enabled` (`resources/js/stores/LycheeState.ts:14`) defaults to `true` for every visitor and is force-reset to `true` on any load error (`:304`), so any user — not just admins — sees the full debug-style card whenever the backend happens to be in debug mode; and `Error.vue`'s trace display isn't responsive at narrow viewports.

## Goals
- Fix the Leaflet Map view crash so `/map` renders tiles/markers on every device (D1).
- Fix the Flow view so a failed load surfaces a visible error instead of spinning forever, and make the loading/error overlay stacking order robust in general (D2).
- Fix the Admin Dashboard "Overview" stats panel so it actually fetches and displays data (D3).
- Bring the Users, Tags, Orders, and Purchasables admin tables to the same working responsive standard already proven by `FaceMaintenance.vue`, and drop the now-inaccurate "use a larger screen" banner from the two tables (Orders, Purchasables) that gain real mobile support (D5).
- Give every icon-only interactive control a persistent accessible name, and fix the one confirmed missing translation key (`gallery.menus.add`) it depends on (D6).
- Fix the Purchasables list's blank thumbnail for album-level purchasables (D7).
- Fix the Statistics storage-size card grid's dead trailing-row whitespace (D8).
- Gate `Error.vue`'s full debug-detail rendering behind the same diagnostics-visibility permission already used elsewhere in the app, and make its content readable at any viewport width (D9).

## Non-Goals
- **D4 (right-click context menu):** confirmed correctly implemented — no code change (Q-059-01).
- **D10 (single-photo lightbox), D11 (public landing page at ultrawide):** confirmed working as intended — no code change.
- **D12 (login-throttle screenshots):** an artifact of the audit session's own repeated automated logins, not a product bug — no code change.
- **`resources/js/v7/` is never touched** by this feature (see NFR-059-01) — this audit only covered the v8 frontend, and the repo's standing policy is to fork rather than risk changing v7 behavior.
- **`.env.example`'s `APP_DEBUG=false` default, or any CI/production debug-mode configuration** — already correct; not part of this feature.
- **Settings.vue's own mobile responsiveness** and its "use a larger screen" banner text/grammar (`lang/en.json:2310`) — Settings has a materially different, much denser per-row-control layout than a data table and needs its own dedicated redesign; only the Orders/Purchasables banners are addressed here, because those two are the ones this feature makes genuinely responsive. Fixing Settings' banner (and its "For better a experience" grammar error, and its reuse by `PrintPixelSizesAdmin.vue`) is a follow-up (see plan.md Follow-ups).
- **Inventing a new bespoke mobile card-list component.** The fix for D5 reuses the existing `FaceMaintenance.vue` column-hiding convention (Nuxt UI `UTable` column `meta` classes), not a new UI pattern.
- **Any change to the `photoMenu()` context-menu access-filtering rules themselves**, or adding a "no actions available" placeholder when a filtered menu collapses to very few items — out of scope; flagged only as a possible future UX polish item in plan.md Follow-ups.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-059-01 | The v8 Map view (`resources/js/v8/views/gallery-panels/Map.vue`) renders Leaflet tiles and photo markers/clusters without a JS runtime error, on every viewport. Implemented via a new v8-scoped module (e.g. `resources/js/v8/composables/photo-map.ts`) that forks the `PhotosLayer`/`Cluster` `L.FeatureGroup.extend()`/`L.MarkerClusterGroup.extend()` definitions out of the shared `resources/js/composables/photo.ts:4-84`, importing `leaflet.markercluster/dist/leaflet.markercluster.js` directly inside the new module (not relying on the importing view having already loaded the plugin first). `Map.vue`'s `import { clusterFunc } from "@/composables/photo"` is repointed to the new module; the shared `resources/js/composables/photo.ts` and `resources/js/v7/views/gallery-panels/Map.vue` are left byte-for-byte unchanged. | `/map`, at any viewport, shows the base tile layer and at least one marker/cluster for an album containing geotagged photos, with no console error. | N/A (no user input on this view). | If `leaflet.markercluster` itself fails to load (network/build issue upstream of this fix), the map still initializes with ungrouped markers rather than crashing before `L.map(...)` is ever called — i.e. clustering is additive, not a hard dependency of map initialization. | None (no telemetry on this view today; none added). | `STUDY-DESKTOP-v8.md` D1 / `STUDY-MOBILE-v8.md` #1; console `TypeError: Cannot read properties of undefined (reading 'extend')` in the `photo-*.js` chunk, traced to `resources/js/composables/photo.ts:54`. |
| FR-059-02 | `resources/js/v8/views/gallery-panels/Flow.vue`'s `load()` function and its `onMounted` hook both reset `isLoading` to `false` on a rejected fetch, matching the `.finally()` pattern already used by every action in `resources/js/stores/TimelineState.ts` (lines 63–112). | On a successful `FlowService.get()`/`FlowService.init()` call, behavior is unchanged from today. | N/A. | On a rejected call (e.g. 403 when the Flow module is disabled), `isLoading` becomes `false` so `LoadingProgress`'s full-screen overlay (`resources/js/v8/components/loading/LoadingProgress.vue`) closes, revealing whatever error UI FR-059-03 makes visible underneath, instead of spinning forever. | The existing global `window.dispatchEvent(new CustomEvent("error", ...))` in `resources/js/config/axios-config.ts:57-60` already fires on this rejection today — FR-059-02 does not change that, only unblocks the UI from hiding it. | `STUDY-DESKTOP-v8.md` D2; traced to `Flow.vue`'s `load()` (no `.catch()`/`.finally()`) and `onMounted`'s bare `await FlowService.init().then(...)`, contrasted with `TimelineState.ts`'s `.finally()`-wrapped actions. |
| FR-059-03 | `resources/js/v8/views/Error.vue`'s error overlay is never visually occluded by `resources/js/v8/components/loading/LoadingProgress.vue`'s loading overlay when both are simultaneously mounted with `z-50` (as in `App.vue`, where `<Error />` precedes `<router-view>` in DOM order — later siblings paint on top at equal z-index). | When no error is active, `LoadingProgress` behaves exactly as today. | N/A. | When the global `"error"` `CustomEvent` has fired (i.e. `Error.vue`'s `lycheeError` is populated) and a `LoadingProgress`/`isLoading`-driven overlay is also active in the same view, the error overlay is the one visible to the user — e.g. by giving `Error.vue`'s overlay a strictly higher `z-index` than `LoadingProgress`'s, or by having `LoadingProgress` not render while a global error is active. | None. | `STUDY-DESKTOP-v8.md` D2 (root-cause analysis: `App.vue:5-13` DOM order + matching `z-50` on both `Error.vue` and `LoadingProgress.vue`). This is a systemic fix, not Flow-specific — any other view with a loading state that never resolves is protected by the same change. |
| FR-059-04 | `resources/js/v8/views/admin/AdminDashboard.vue`'s Overview stats fetch (`loadStats()`/`loadUpdateStatus()`/`loadAdvisories()`, currently gated by a one-shot `onMounted` check of `initData.value?.settings.can_edit` at lines 274-280) is triggered reactively once `initData` (populated asynchronously by the sibling `<LeftMenu />` component's own `onMounted`, per `resources/js/v8/composables/contextMenus/leftMenu.ts:79-83`) becomes available, e.g. via `watch(initData, ..., { immediate: true })`, instead of only at the exact synchronous tick `AdminDashboard.vue`'s own `onMounted` runs (when `initData` is guaranteed still `undefined`). | Loading the Admin Dashboard as an admin shows the Overview stat cards populated from `GET /api/v2/Admin/Stats` (`AdminDashboardController::stats`, already fully implemented) shortly after page load, every time, without needing a manual refresh. | N/A. | The template's `v-if="!stats && !isLoading"` empty-state condition at `AdminDashboard.vue:109` is corrected (it currently only shows the loading icon when `isLoading` is `false`, i.e. never during an actual fetch) so a loading indicator shows while `isLoading` is `true`, and an explicit "couldn't load" state shows if the fetch ultimately fails. | None new. | `STUDY-DESKTOP-v8.md` D3 (identical on mobile, `STUDY-MOBILE-v8.md` #4); traced to the `initData` population race between `AdminDashboard.vue` and `LeftMenu.vue`, and the inverted `v-if` at `AdminDashboard.vue:109`. |
| FR-059-05 | The Users (`resources/js/v8/views/admin/Users.vue`), Tags (`resources/js/v8/views/TagsManagement.vue`), Orders (`resources/js/v8/views/webshop/OrderList.vue`), and Purchasables (`resources/js/v8/views/admin/Purchasables.vue`) `UTable`s each hide their non-essential columns below the `sm` Tailwind breakpoint via column `meta.class` (`th`/`td`: `"hidden sm:table-cell"`), matching the pattern already proven in `resources/js/v8/views/face-recog/FaceMaintenance.vue:326-338`, and fold the most important piece of hidden-column information inline into their always-visible primary column (e.g. `FaceMaintenance.vue:130-133`'s `sm:hidden` badge pattern) where there is an obvious single most-important hidden value per table. `Users.vue`'s `:ui="{ base: 'table-fixed', ... }"` (line 59) is changed so hidden columns actually free up horizontal space rather than reserving it. | At any viewport ≥ the table's breakpoint, behavior is unchanged from today (all columns visible). | N/A (read-only display logic, no new input). | At a 390px viewport, every one of the four tables shows its primary identifying column(s) plus at least the row-level actions (Edit/Delete on Users; the equivalent primary action on the other three) without any column overlapping another's text, and without needing horizontal scroll to reach a row's primary action. | None. | `STUDY-DESKTOP-v8.md` D5 / `STUDY-MOBILE-v8.md` #3; per-file breakage confirmed at `Users.vue` (columns `:223-238`), `TagsManagement.vue` (`:133-151`), `OrderList.vue` (`:70-77`), `Purchasables.vue` (`:55-61`); working reference pattern confirmed at `FaceMaintenance.vue`. |
| FR-059-06 | The `lg:hidden` "please use a larger screen" banner (`OrderList.vue:8`, `Purchasables.vue:8`, both rendering the shared `settings.small_screen` translation) is removed from `OrderList.vue` and `admin/Purchasables.vue` now that FR-059-05 makes their tables usable at any width. The `Settings.vue` and `PrintPixelSizesAdmin.vue`/`webshop/PurchasablesList.vue` usages of the same banner are left unchanged (Non-Goals). | Orders and Purchasables render their (now-responsive) table directly on mobile, with no gating banner. | N/A. | N/A. | None. | Follows directly from FR-059-05 — leaving a "use a bigger screen" banner in front of a table that has just been made to work at every screen size would be self-contradictory; `Q-059-02` resolves the scope boundary against Settings. |
| FR-059-07 | Every icon-only `UButton`/`UIcon` identified in the D6 investigation gets a persistent accessible name (`aria-label` or equivalent — not solely a `UTooltip`, since `TooltipTrigger`'s `aria-describedby` is only set while the tooltip is actually open, per `node_modules/reka-ui/dist/Tooltip/TooltipTrigger.js`), reusing an existing i18n key wherever one already exists for that exact action, and adding a new key only where none exists. Covers: the hamburger (`resources/js/v8/components/headers/OpenLeftMenu.vue:2`); `AlbumsHeader.vue`'s list/search/bell/help/"+"/mobile-overflow-chevron buttons (`:294-329`, `:77-83`); the album hero's stats icon (`resources/js/v8/components/gallery/albumModule/AlbumHero.vue:70-76`, `openStatistics`, new key needed); `resources/js/v8/components/headers/PhotoHeader.vue`'s play/open-original/download/edit/info buttons (`:32`, `:33-40`, `:41-51`, `:52-62`, `:63-73`, all fully bare today, new keys needed); the Users table's `may_upload`/`may_edit_own_settings`/`upload_trust_level`/`quota` header icons (`Users.vue:71-115`, reusing the existing `users.upload_rights`/`users.edit_rights`/`users.upload_trust_level`/`users.quota` keys); the Sharing table's six grant-column header icons (`resources/js/v8/views/Sharing.vue:27-43`, reusing the existing `sharing.grants.read/original/download/upload/edit/delete` keys). | Every listed control is reachable and identifiable via a screen reader (VoiceOver/NVDA/TalkBack) with the tooltip never having been opened. | N/A. | N/A. | None. | `STUDY-DESKTOP-v8.md` D6 / `STUDY-MOBILE-v8.md` #7; exact locations and existing-key reuse confirmed by codebase investigation. |
| FR-059-08 | `lang/en.json` gains a `gallery.menus.add` key (the one currently missing, causing `trans("gallery.menus.add")` in `AlbumsHeader.vue:358` and `TimelineHeader.vue:271` to echo the raw key string to users), and the mobile "more" dropdown's other mirrored items (`AlbumsHeader.vue`'s `mobileMenuSections`, `:349-354`, currently hardcoding `label: ""` for every item) get real, translated visible labels sourced from each source item's existing `key` (`view_list`, `search`, `metrics`, `help`, `hide_nsfw`, `show_nsfw`) — this dropdown is the one place in the app where these actions are meant to be presented with visible text rather than icon-only (its desktop equivalents stay icon-only per FR-059-07, since desktop has room for a tooltip-adjacent `aria-label` instead). | The mobile header overflow dropdown shows a translated, human-readable label next to every icon, including "Add". | N/A. | N/A. | None. | `STUDY-DESKTOP-v8.md` D6 / `STUDY-MOBILE-v8.md` #6, #7; confirmed the key is genuinely absent from `lang/en.json` (not just hard to find) and that `label: ""` is deliberate/hardcoded, not a bug elsewhere. |
| FR-059-09 | `app/Http/Resources/Shop/EditablePurchasableResource.php`'s thumbnail resolution (currently only reading `$item->photo?->size_variants?->getThumb()?->url` at lines 44-49, leaving `photo_url` `null` whenever the purchasable is album-level rather than photo-level, per `Purchasable::isAlbumLevel()`) falls back to the linked album's own cover thumb (`$item->album?->get_thumb()`, mirroring `ThumbAlbumResource`'s existing pattern) when `photo_id` is `null` and `album_id` is set. `app/Http/Controllers/Admin/ShopManagementController.php:51` already eager-loads the `album` relation this needs. | An album-level purchasable's row in `/admin/purchasables` shows that album's actual cover photo thumbnail. | N/A. | If the linked album itself has no resolvable cover (e.g. an empty album), the existing `/img/placeholder.png` fallback in `resources/js/v8/views/admin/Purchasables.vue:19-21` is unchanged — this FR only extends which cases produce a real thumbnail, it doesn't remove the placeholder fallback. | None. | `STUDY-DESKTOP-v8.md` D7; root cause confirmed at `EditablePurchasableResource.php:44-49` plus the `/img/placeholder.png` asset itself being a ~50%-opaque black 1×1 PNG (explaining the "blank black square" symptom). |
| FR-059-10 | `resources/js/v8/components/statistics/SizeVariantMeter.vue:6`'s storage-card container changes `sm:justify-between justify-center` to `sm:justify-center justify-center` (or equivalently drops the `sm:` override), matching the existing sibling convention already used for near-identical statistic-card rows in `resources/js/v8/components/drawers/AlbumStatistics.vue:3` and `resources/js/v8/components/modals/ShareAlbum.vue:4`. | At any viewport where the storage-size cards wrap onto more than one row, every row's cards — including a trailing partial row — sit together (centered/left-aligned per the existing convention) rather than being spread to opposite container edges. | N/A. | N/A. | None. | `STUDY-DESKTOP-v8.md` D8; confirmed this is a `flex flex-wrap` container (not a CSS grid, correcting the study's original "6-column grid" framing) whose `justify-between` is what spreads the trailing 2-card row apart. |
| FR-059-11 | `resources/js/stores/LycheeState.ts`'s `is_debug_enabled` (currently defaulting to `true` at line 14 and force-reset to `true` on any load error at line 304) instead defaults to, and is reset to, the current user's `initData.value?.settings.can_see_diagnostics` flag — the same permission already gating the "Diagnostics" left-menu link (`resources/js/v8/composables/contextMenus/leftMenu.ts`, `diagnostics.title` entry). Guests/non-privileged users falling back to `false` still see `Error.vue`'s user-facing message; only the raw `exception`/`file`/`line`/`trace` detail block is gated by this flag. | A user with `can_see_diagnostics === true` (typically an admin) sees full debug detail on error, exactly as today. | N/A. | A user without that permission sees `Error.vue`'s message without the raw trace/file-path block, regardless of the backend's `APP_DEBUG` setting. | None. | `STUDY-DESKTOP-v8.md` D9 (corrected framing — see Overview); resolved via `Q-059-03`. |
| FR-059-12 | `resources/js/v8/views/Error.vue`'s exception/trace display wraps or horizontally scrolls within its own container at every viewport width — no line (e.g. an absolute file path or a stack-trace frame) causes the page itself to overflow horizontally or clip text off-screen, matching this repo's general responsive convention (contained `overflow-x-auto` on wide pre/code-style content, as already used elsewhere for long text blocks). | `Error.vue` is fully readable, including every trace line, at a 390px viewport. | N/A. | N/A. | None. | `STUDY-DESKTOP-v8.md` D9 / `STUDY-MOBILE-v8.md` #8 (mobile capture showed every stack-trace line clipped at the viewport edge). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-059-01 | `resources/js/composables/photo.ts` and every file under `resources/js/v7/` remain byte-for-byte unchanged by this feature; FR-059-01's fix lives entirely in a new v8-scoped module. | Standing repo policy: v8 is actively replacing v7, and changes scoped to v8 must not risk altering v7 behavior (see `[[project-v8-migration-scope]]`, and the identical precedent set by Feature 055's WASM layout-engine fork). | `git diff --stat -- resources/js/composables/photo.ts resources/js/v7/` is empty after implementation. | New `resources/js/v8/composables/photo-map.ts` (or equivalent name chosen at implementation time). | Repo convention; user directive recorded in project memory. |
| NFR-059-02 | Every icon-only control's new accessible name (FR-059-07, FR-059-08) reuses an existing `lang/en.json` translation key wherever the exact same action already has one anywhere else in the app, rather than introducing a duplicate string. | Avoid translation-key sprawl and keep terminology consistent (e.g. "Download" must read the same whether it's a tooltip, a button label, or a new `aria-label`). | Code review against the specific existing-key list enumerated in FR-059-07/FR-059-08. | `lang/en.json`. | Investigation findings (D6). |
| NFR-059-03 | Every new translation key this feature introduces (FR-059-07's new keys for the album-hero stats icon and `PhotoHeader.vue`'s play/original/edit/info buttons; FR-059-08's `gallery.menus.add`) is propagated to all locale files under `lang/`, using this repo's existing English-placeholder-for-untranslated-values convention (precedent: Feature 054's translation sweep). | Repo-wide translation-completeness expectation; `LangTest` enforces every locale has every key used in `en.json`. | `php artisan test --filter=LangTest` passes. | `lang/*.json` (all 22+ locales). | Repo convention (Feature 054 precedent), `LangTest`. |
| NFR-059-04 | FR-059-09's backend fix adds no new database query beyond the `album` relation `ShopManagementController` already eager-loads. | Keep the Purchasables admin list's query cost unchanged. | Code review confirms `EditablePurchasableResource` only reads an already-loaded relation (`$item->album`), never calling `::query()`/`with()` itself. | `ShopManagementController`'s existing eager-load. | Investigation findings (D7). |
| NFR-059-05 | FR-059-03's overlay-stacking fix protects every current and future view that combines a `LoadingProgress`-driven loading state with `Error.vue`'s global error overlay, not just Flow — it must not be implemented as a Flow-specific special case. | The same DOM-order/z-index conflict could affect any view with a loading state that fails to resolve; fixing it generally (per FR-059-03) rather than only fixing Flow's specific trigger (FR-059-02) closes the whole bug class. | Code review confirms the fix is made in `Error.vue`/`LoadingProgress.vue`/`App.vue` (shared), not inside `Flow.vue`. | `App.vue`, `Error.vue`, `LoadingProgress.vue`. | Investigation findings (D2), explicitly called out as "systemic, not Flow-specific." |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-059-01 | Navigate to `/map` on an album with geotagged photos, any viewport → tiles and clustered markers render, no console error. |
| S-059-02 | Navigate to `/map` on an album with zero geotagged photos → base map renders (empty of markers), no crash. |
| S-059-03 | Navigate to `/flow` while the Flow module is disabled (403) → loading spinner closes and an error is visible (not a permanent spinner). |
| S-059-04 | Navigate to `/flow` while enabled and reachable → behaves exactly as before this feature. |
| S-059-05 | Any view where a global error fires while a `LoadingProgress` overlay is simultaneously active → the error is the one visibly on top. |
| S-059-06 | Load `/admin` as an admin with existing photos/albums → Overview stat cards populate without a manual refresh, every time. |
| S-059-07 | Load `/admin` as an admin while the stats fetch is in flight → a loading indicator shows (not a blank area). |
| S-059-08 | Load `/admin` as an admin when `GET /Admin/Stats` fails → an explicit "couldn't load" state shows (not a blank area, not a stuck loading icon). |
| S-059-09 | View `/admin/users`, `/tags`, `/orders`, `/admin/purchasables` at 390px → primary column(s) and row actions are reachable without any text/checkbox overlap. |
| S-059-10 | View the same four tables at ≥1440px → unchanged from before this feature. |
| S-059-11 | View `/orders` and `/admin/purchasables` at any width → no "use a larger screen" banner. |
| S-059-12 | View `/admin/settings` at 390px → banner unchanged (Non-Goal). |
| S-059-13 | Screen-reader walkthrough of the main header, mobile overflow dropdown, album hero toolbar, `PhotoHeader.vue` toolbar, Users table headers, Sharing table headers → every icon-only control announces a meaningful name. |
| S-059-14 | Open the mobile header overflow dropdown → every item (including "Add") shows a translated visible label, no raw `gallery.menus.add`-style key text anywhere. |
| S-059-15 | View `/admin/purchasables` for an album-level purchasable whose album has a cover photo → the row shows that cover thumbnail, not a black square. |
| S-059-16 | View `/admin/purchasables` for an album-level purchasable whose album has no resolvable cover → placeholder image shown (unchanged fallback). |
| S-059-17 | View `/statistics` at a width where the storage-size cards wrap to a trailing partial row → that row's cards sit together, no dead-space gap. |
| S-059-18 | Trigger a backend error as a user with `can_see_diagnostics = true` → `Error.vue` shows full debug detail (unchanged from today). |
| S-059-19 | Trigger a backend error as a user without `can_see_diagnostics` → `Error.vue` shows its message without the raw trace/file-path block. |
| S-059-20 | View `Error.vue` with debug detail visible at a 390px viewport → every line is readable, none clipped off-screen. |

## Test Strategy
- **Core:** N/A — no domain-model changes.
- **Application:** New/updated PHPUnit coverage for `EditablePurchasableResource`'s album-level thumbnail fallback (FR-059-09) — direct resource/unit test asserting `photo_url` is populated for an album-level purchasable with a coverable album, and stays `null`/placeholder-driving for one with no coverable album.
- **REST:** No new routes; existing `GET /Admin/Stats` and `Admin/Purchasables` list endpoints are unchanged in shape (only `EditablePurchasableResource`'s field population logic changes).
- **CLI:** N/A.
- **UI (JS/Selenium/Playwright):** A Playwright re-run of the exact repro steps used in `STUDY-DESKTOP-v8.md`/`STUDY-MOBILE-v8.md` for every scenario S-059-01..20, both at 390px and 1440px where applicable, is the primary verification method for this feature (matching how the findings were originally discovered) — screenshot diff against the original "broken" captures in `screenshot/mobile/` and `screenshot/desktop/` confirms each fix. No existing JS unit-test runner exists in this repo for component-level assertions (precedent: Feature 051's Q-051-05); accessible-name checks (S-059-13) are verified via manual VoiceOver/NVDA walkthrough plus a static grep confirming every enumerated button in FR-059-07/FR-059-08 carries an `aria-label`.
- **Docs/Contracts:** `lang/en.json` (and all locale files, NFR-059-03) diff review; no OpenAPI/telemetry contracts affected.

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-059-01 | `EditablePurchasableResource`'s `photo_url` field gains an album-cover fallback branch (no new field, no new type) | `app/Http/Resources/Shop`, v8 |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-059-01 | REST GET `/api/v2/Admin/Stats` (existing, unchanged) | Consumed correctly for the first time by FR-059-04's fix — no route/contract change, only the frontend's call-timing bug is fixed. | `AdminDashboardController::stats`. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-059-01 | `tests/Feature/Http/Shop/EditablePurchasableResourceTest.php` fixtures (album-level purchasable with a coverable album; one with an empty album) | Covers FR-059-09/NFR-059-04. |

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|----------------------------|
| UI-059-01 | Map: base + markers | Album with geotagged photos, any viewport (S-059-01). |
| UI-059-02 | Flow: error visible | Flow module disabled, 403 response (S-059-03). |
| UI-059-03 | Admin Dashboard: loading | Stats fetch in flight (S-059-07). |
| UI-059-04 | Admin Dashboard: populated | Stats fetch succeeded (S-059-06). |
| UI-059-05 | Admin Dashboard: failed | Stats fetch rejected (S-059-08). |
| UI-059-06 | Admin table: mobile-collapsed columns | ≤ `sm` breakpoint, one of the four tables (S-059-09). |
| UI-059-07 | Error.vue: full debug detail | `can_see_diagnostics = true` (S-059-18). |
| UI-059-08 | Error.vue: message only | `can_see_diagnostics = false` (S-059-19). |

## Telemetry & Observability
No new telemetry events. No existing event names, fields, or redaction rules change.

## Documentation Deliverables
- `docs/specs/4-architecture/roadmap.md` — Active Features row added on spec draft; moved to Completed on implementation (per existing convention).
- `docs/specs/4-architecture/open-questions.md` — Q-059-01, Q-059-02, Q-059-03 logged and resolved (see below).
- `STUDY-DESKTOP-v8.md` / `STUDY-MOBILE-v8.md` — corrected at implementation time to reflect the grounded findings above (D4 no longer described as a bug; D5's Bulk Album Edit claim corrected; D9's "raw Laravel debug page" framing corrected) — tracked as a task in tasks.md rather than done as part of this spec, so the correction lands together with the actual fix and can reference the shipped behavior.
- `docs/specs/4-architecture/knowledge-map.md` — updated on completion per standing convention (deferred to implementation, matching every other Draft-status feature in this repo).

## Fixtures & Sample Data
See Interface & Contract Catalogue → Fixtures & Sample Data (FX-059-01).

## Spec DSL
```
domain_objects:
  - id: DO-059-01
    name: EditablePurchasableResource.photo_url
    fields:
      - name: photo_url
        type: "?string"
        constraints: "falls back to album cover thumb when photo_id is null and album_id is set"
routes:
  - id: API-059-01
    method: GET
    path: /api/v2/Admin/Stats
fixtures:
  - id: FX-059-01
    path: tests/Feature/Http/Shop/EditablePurchasableResourceTest.php
ui_states:
  - id: UI-059-01
    description: Map base + markers render
  - id: UI-059-02
    description: Flow error visible instead of infinite spinner
  - id: UI-059-03
    description: Admin Dashboard loading indicator
  - id: UI-059-04
    description: Admin Dashboard populated stats
  - id: UI-059-05
    description: Admin Dashboard failed-fetch state
  - id: UI-059-06
    description: Admin table mobile-collapsed columns
  - id: UI-059-07
    description: Error.vue full debug detail (permitted user)
  - id: UI-059-08
    description: Error.vue message-only (non-permitted user)
```

## Appendix

### Resolved open questions (logged per user instruction not to block on live clarification — see `docs/specs/4-architecture/open-questions.md` for the full entries)

- **Q-059-01 — Drop D4 (right-click context menu) from scope.** Investigation confirmed the context-menu system is correctly implemented and wired; the audit's "only one item" observation was the real, correct behavior for an album/context without edit rights, not a bug. Resolved: no fix, removed from Goals, documented as a correction in Overview.
- **Q-059-02 — Scope boundary for the "use a larger screen" banner removal (FR-059-06).** Only `OrderList.vue`/`admin/Purchasables.vue` lose the banner, since FR-059-05 makes exactly those two tables (plus Users/Tags) responsive. `Settings.vue` keeps its banner — it has a structurally different, much denser per-row-control layout that a column-hiding `UTable` fix doesn't address, and fixing it is a materially larger, separate effort. Resolved: Option A (narrow scope), banner removal tied 1:1 to which tables actually become responsive in this feature.
- **Q-059-03 — `is_debug_enabled` default/reset gating (FR-059-11).** Rather than leaving the flag hardcoded `true` for every visitor (current behavior) or removing the debug-detail feature entirely, it's gated behind the same `can_see_diagnostics` permission already used for the "Diagnostics" left-menu link — reusing an existing, already-understood permission rather than inventing a new one. Resolved: Option A (reuse `can_see_diagnostics`).
