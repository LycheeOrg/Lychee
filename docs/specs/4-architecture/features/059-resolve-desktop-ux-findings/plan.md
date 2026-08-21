# Feature Plan 059 – Resolve Desktop UX Audit Findings

_Linked specification:_ `docs/specs/4-architecture/features/059-resolve-desktop-ux-findings/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-22

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md`. Any new high- or medium-impact question found during implementation goes into `docs/specs/4-architecture/open-questions.md` and gets resolved there (not asked mid-session) — per this feature's own precedent (Q-059-01..03), decide the most defensible option and log it.

## Vision & Success Criteria
Every genuine bug identified in `STUDY-DESKTOP-v8.md` (D1, D2, D3, D5, D6, D7, D8, D9) is fixed and re-verified with the same Playwright method used to find it. `resources/js/v7/` and `resources/js/composables/photo.ts` remain untouched. No new translation key ships without English-locale coverage propagated to every other locale. Success is measured by re-running the 20 Branch & Scenario Matrix rows (S-059-01..20) against the implemented app and confirming each passes — not by unit-test coverage alone, since 6 of the 8 fixed findings are pure frontend rendering/wiring bugs with no prior test seam.

## Scope Alignment
- **In scope:** FR-059-01 through FR-059-12, exactly as specified. Both a JS/frontend track (Map fork, Flow/Error overlay fix, Admin Dashboard wiring, table responsiveness, icon labels, Statistics grid, Error.vue diagnostics gating + responsiveness) and a small backend track (Purchasables thumbnail fallback).
- **Out of scope:** D4, D10, D11, D12 (Non-Goals in spec.md), Settings.vue's own responsiveness/banner, any v7 change, any new UI component library or bespoke mobile layout beyond the existing `FaceMaintenance.vue` column-hiding convention.

## Dependencies & Interfaces
- `leaflet.markercluster` / `leaflet-rotatedmarker` / `leaflet-gpx` (already in `package.json`, already used correctly by `Map.vue`'s own top-of-file imports — FR-059-01 only needs the marker-cluster plugin import moved into the new forked module, not a new dependency).
- Nuxt UI's `UTable` column `meta.class` mechanism (already used by `FaceMaintenance.vue` — no new library).
- `initData` (`resources/js/stores/LeftMenuState.ts`) and `leftMenu.ts`'s `load()` — read-only dependency for FR-059-04 and FR-059-11 (`can_see_diagnostics`).
- `app/Http/Controllers/Admin/ShopManagementController.php`'s existing `album` eager-load — read-only dependency for FR-059-09.
- `LangTest` (existing) — gate for NFR-059-03.
- `screenshot/mobile/`, `screenshot/desktop/` (this session's audit captures) — baseline "before" images for the Playwright re-verification pass.

## Assumptions & Risks
- **Assumptions:**
  - The `leaflet.markercluster` chunk-evaluation-order hypothesis (FR-059-01's root cause) is confirmed by the console stack trace pointing at `photo-*.js`, but the exact Vite chunk-graph mechanism that causes photo.ts's module body to run before Map.vue's side-effect import resolves was not independently reproduced outside the browser — I1's fix (self-contained import inside the new fork) is robust regardless of the precise mechanism, so this assumption doesn't block the fix even if the exact chunking theory is imprecise.
  - `AdminDashboardController::stats` (FR-059-04's data source) returns data for the audited instance's real data (67046 photos, 882 albums per Statistics) — confirmed implemented, not stubbed, by the D3 investigation; not independently re-verified by calling the endpoint directly in this planning pass.
  - `can_see_diagnostics` (FR-059-11) is already present on `initData.value.settings` for every authenticated session (confirmed used elsewhere, in `leftMenu.ts`'s Diagnostics link) — assumed also present/`false`-defaulted correctly for guest sessions; verified in I8.
- **Risks / Mitigations:**
  - *Risk:* Forking `photo.ts`'s cluster code (I1) could drift from the shared version over time if the shared file is later changed for a v7-only reason and the v8 fork isn't updated. *Mitigation:* the forked module is deliberately small (only the two `L.*.extend()` definitions + the marker-cluster import), documented with a comment pointing back at the shared original, matching the precedent already set by Feature 055's WASM layout-engine fork.
  - *Risk:* FR-059-05's column-hiding choices (which columns to hide, what to fold inline) are a UX judgment call per table, not mechanically derived. *Mitigation:* each table's specific hide/fold plan is written out explicitly in I5 below before implementation, using the same "most identifying info stays, secondary metadata hides" heuristic `FaceMaintenance.vue` already uses.
  - *Risk:* FR-059-03's overlay z-index fix is systemic (NFR-059-05) — a naive fix (just bumping `Error.vue`'s z-index) could have unintended effects on other overlays/modals that currently rely on `LoadingProgress` painting on top of everything. *Mitigation:* I2 audits every other `z-50`-or-higher overlay in `resources/js/v8/components/` before choosing the exact mechanism (z-index bump vs. `LoadingProgress` yielding to an active error) so the fix doesn't just move the bug elsewhere.
  - *Risk:* FR-059-09's backend fix touches a resource consumed by an admin-only page — low blast radius, but `Purchasable` album-level vs. photo-level branching should be re-confirmed against `Purchasable::isAlbumLevel()`'s exact semantics before writing the fallback, in case there's a third state (e.g. neither `photo_id` nor `album_id` set) not covered by the investigation. *Mitigation:* I4 reads `Purchasable.php` in full before writing the fix.

## Implementation Drift Gate
Before closing this feature, re-diff the actual implementation against every FR/NFR above; record any deviation (with rationale) directly in this section, following the precedent set by Feature 055's plan.md. Re-run `git diff --stat -- resources/js/composables/photo.ts resources/js/v7/` and confirm it is empty (NFR-059-01) as the final gate check.

## Increment Map

1. **I1 – Fork the Leaflet marker-cluster code out of the shared `photo.ts` (FR-059-01)**
   - _Goal:_ `/map` renders without a JS crash, on any device, without touching `resources/js/composables/photo.ts` or any `resources/js/v7/` file.
   - _Preconditions:_ None — root cause already grounded (spec.md Overview).
   - _Steps:_
     - Create `resources/js/v8/composables/photo-map.ts`, copying `photo.ts`'s `PhotosLayer`/`photosLayerFunc`/`Cluster`/`clusterFunc` definitions verbatim.
     - Add `import "leaflet.markercluster/dist/leaflet.markercluster.js";` at the top of the new file, ahead of the `L.MarkerClusterGroup.extend(...)` call, so the module is self-sufficient regardless of import/chunk order.
     - Update `resources/js/v8/views/gallery-panels/Map.vue`'s `import { clusterFunc } from "@/composables/photo"` to import from the new `@/v8/composables/photo-map` instead.
     - Leave `resources/js/composables/photo.ts` and `resources/js/v7/views/gallery-panels/Map.vue` untouched.
   - _Commands:_ `npm run check`, manual Playwright re-run of the `/map` repro (mobile + desktop viewport) from `STUDY-DESKTOP-v8.md`/`STUDY-MOBILE-v8.md`.
   - _Exit:_ S-059-01/S-059-02 pass; `git diff --stat -- resources/js/composables/photo.ts resources/js/v7/` empty.

2. **I2 – Flow loading/error handling + overlay stacking (FR-059-02, FR-059-03, NFR-059-05)**
   - _Goal:_ A failed Flow load surfaces a visible error; the fix generalizes to any view with the same overlay-stacking hazard.
   - _Preconditions:_ I1 not required as a dependency (independent area).
   - _Steps:_
     - Add `.catch()`/`.finally()` handling to `Flow.vue`'s `load()` and `onMounted`, mirroring `TimelineState.ts`'s pattern, so `isLoading` always resets to `false`.
     - Audit every `z-50`(+)-using overlay/modal component under `resources/js/v8/components/` to confirm the chosen stacking fix (raise `Error.vue`'s z-index above `LoadingProgress.vue`'s, or make `LoadingProgress` not render while a global error is active) doesn't regress any of them.
     - Implement the chosen fix in `Error.vue`/`LoadingProgress.vue`/`App.vue`.
   - _Commands:_ `npm run check`, manual repro of `/flow` with the Flow module disabled (mobile + desktop), spot-check 2-3 other views that use `LoadingProgress` (e.g. Map, Timeline) to confirm no regression.
   - _Exit:_ S-059-03, S-059-04, S-059-05 pass.

3. **I3 – Admin Dashboard Overview wiring (FR-059-04)**
   - _Goal:_ Overview stat cards populate reliably.
   - _Preconditions:_ None.
   - _Steps:_
     - Replace `AdminDashboard.vue`'s one-shot `onMounted` guard with a `watch(initData, ..., { immediate: true })` (or equivalent) that calls `loadStats()`/`loadUpdateStatus()`/`loadAdvisories()` once `initData?.settings.can_edit` is true, handling the case where it's already true at mount.
     - Fix the inverted `v-if="!stats && !isLoading"` at line 109 to show a loading indicator while `isLoading` is true and an explicit failed-fetch state if `loadStats()` ultimately rejects.
   - _Commands:_ `npm run check`, manual repro of `/admin` (fresh page load, confirm stats populate without a manual refresh) at mobile + desktop.
   - _Exit:_ S-059-06, S-059-07, S-059-08 pass.

4. **I4 – Purchasables album-level thumbnail fallback (FR-059-09, NFR-059-04)**
   - _Goal:_ Album-level purchasables show their album's cover thumbnail.
   - _Preconditions:_ Read `app/Models/Purchasable.php` in full first (risk mitigation above) to confirm there's no third `photo_id`/`album_id` state the fallback needs to also handle.
   - _Steps:_
     - In `EditablePurchasableResource` (wherever `photo_url` is actually built — confirm exact method name, likely `fromModel()`, before editing), add: when `photo_id` is null and `album_id` is set, resolve `photo_url` from `$item->album?->get_thumb()` instead of leaving it null.
     - Add `tests/Feature/Http/Shop/EditablePurchasableResourceTest.php` (FX-059-01) covering: photo-level purchasable (unchanged), album-level purchasable with a coverable album (new fallback), album-level purchasable with no coverable album (placeholder path preserved).
   - _Commands:_ `php artisan test --filter=EditablePurchasableResource`, `make phpstan`.
   - _Exit:_ S-059-15, S-059-16 pass; NFR-059-04 confirmed by code review (no new query).

5. **I5 – Admin table responsiveness (FR-059-05, FR-059-06)**
   - _Goal:_ Users, Tags, Orders, Purchasables tables work at 390px; Orders/Purchasables lose their banner.
   - _Preconditions:_ I1-I4 not required as dependencies.
   - _Steps:_
     - Per table, decide (write down before coding) which columns hide below `sm` and what — if anything — folds inline into the primary column, using `FaceMaintenance.vue`'s pattern as the mechanism:
       - **Users** (`Users.vue`): keep Username + Edit/Delete actions always visible; hide the `may_upload`/`may_edit_own_settings`/`upload_trust_level`/`quota` columns below `sm`, fold the quota value inline under the username (most-relevant single hidden datum, per the audit's own observation that storage quota was the one clipped value users could still want to see).
       - **Tags** (`TagsManagement.vue`): keep Name + Merge/Delete/edit-pencil actions always visible; hide `Photos`/`Albums` counts below `sm`, fold the photo count inline (the more commonly relevant of the two).
       - **Orders** (`OrderList.vue`): keep Client + Status + primary action (View Details/Mark as Paid) always visible; hide Amount/Date below `sm`, fold Amount inline (the more commonly relevant of the two).
       - **Purchasables** (`admin/Purchasables.vue`): keep Title + thumbnail always visible; hide Description/Notes below `sm`, fold the lowest price inline.
     - Change `Users.vue`'s `:ui="{ base: 'table-fixed', ... }"` so hidden columns free up space (drop `table-fixed` or adjust per what testing shows).
     - Remove the `lg:hidden` small-screen banner block from `OrderList.vue:8` and `admin/Purchasables.vue:8`.
   - _Commands:_ `npm run check`, manual Playwright re-run of all four tables at 390px and 1440px.
   - _Exit:_ S-059-09, S-059-10, S-059-11, S-059-12 pass.

6. **I6a – Icon accessible names: header + mobile dropdown + album hero + photo header (FR-059-07, FR-059-08)**
   - _Goal:_ Every enumerated icon-only control has a persistent accessible name; `gallery.menus.add` renders translated text.
   - _Preconditions:_ None.
   - _Steps:_
     - Add `aria-label` to: `OpenLeftMenu.vue`'s hamburger; `AlbumsHeader.vue`'s list/search/bell/help/"+"/mobile-overflow-chevron buttons; `AlbumHero.vue`'s stats icon (new key, e.g. `gallery.album.hero.statistics`); `PhotoHeader.vue`'s play/open-original/download/edit/info buttons (new keys).
     - Add `gallery.menus.add` to `lang/en.json` (English text: "Add").
     - Change `AlbumsHeader.vue`'s `mobileMenuSections` (`:349-354`) to emit real translated labels per source item's `key` instead of `label: ""`.
   - _Commands:_ `npm run check`, manual VoiceOver/NVDA pass over the header + mobile dropdown + album hero + photo header.
   - _Exit:_ S-059-13 (partial — header/dropdown/hero/photo-header portion), S-059-14 pass.

7. **I6b – Icon accessible names: admin table headers (FR-059-07)**
   - _Goal:_ Users/Sharing table header icons carry `aria-label`.
   - _Preconditions:_ None (can run parallel to I6a).
   - _Steps:_ Add `aria-label` to `Users.vue`'s 4 header-icon slots and `Sharing.vue`'s 6 header-icon slots, reusing the existing tooltip translation keys already quoted in FR-059-07.
   - _Commands:_ `npm run check`, manual VoiceOver/NVDA pass over both tables.
   - _Exit:_ S-059-13 (remaining portion) passes.

8. **I7 – Statistics grid dead-space fix (FR-059-10)**
   - _Goal:_ Trailing partial row of storage-size cards sits together.
   - _Preconditions:_ None.
   - _Steps:_ Change `SizeVariantMeter.vue:6`'s `sm:justify-between justify-center` to `sm:justify-center justify-center`.
   - _Commands:_ `npm run check`, manual re-check of `/statistics` at a width producing a trailing partial row.
   - _Exit:_ S-059-17 passes.

9. **I8 – Error.vue diagnostics gating + responsive polish (FR-059-11, FR-059-12)**
   - _Goal:_ Debug detail only shown to permitted users; readable at any width.
   - _Preconditions:_ Confirm `can_see_diagnostics` is present (and correctly `false`-defaulted for guests) on `initData.value.settings` before wiring it into `LycheeState.ts` (risk mitigation above).
   - _Steps:_
     - Change `LycheeState.ts`'s `is_debug_enabled` default (line 14) and its force-reset-on-error (line 304) to read `initData.value?.settings.can_see_diagnostics ?? false` instead of a bare `true`.
     - Add responsive wrapping/`overflow-x-auto` to `Error.vue`'s exception/trace display so no line clips off-screen at 390px.
   - _Commands:_ `npm run check`, manual repro: trigger a backend error as an admin (full detail expected) and as a non-admin/guest (message-only expected), at both viewports.
   - _Exit:_ S-059-18, S-059-19, S-059-20 pass.

10. **I9 – Translation sweep, quality gates, documentation (NFR-059-03, Documentation Deliverables)**
    - _Goal:_ Feature is fully translation-complete and quality-gated; docs reflect reality.
    - _Preconditions:_ I1-I8 complete.
    - _Steps:_
      - Propagate every new key from I4/I6a to all locale files (English-placeholder convention, Feature 054 precedent).
      - Run full quality gates (see Exit Criteria).
      - Update `STUDY-DESKTOP-v8.md`/`STUDY-MOBILE-v8.md` to reflect corrected findings (D4 no longer a bug; D5's Bulk Album Edit claim corrected; D9's framing corrected) and mark fixed findings as resolved.
      - Move this feature's roadmap.md row from Active to Completed.
      - Update `knowledge-map.md`.
    - _Commands:_ `php artisan test --filter=LangTest`, `make phpstan`, `npm run check`, `npm run format`, `vendor/bin/php-cs-fixer fix`.
    - _Exit:_ All quality gates green; documentation updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|----------------------------|-------|
| S-059-01, S-059-02 | I1 | Map fork. |
| S-059-03, S-059-04, S-059-05 | I2 | Flow + overlay stacking. |
| S-059-06, S-059-07, S-059-08 | I3 | Admin Dashboard wiring. |
| S-059-09, S-059-10, S-059-11, S-059-12 | I5 | Table responsiveness + banner removal. |
| S-059-13 (header/dropdown/hero/photo-header portion), S-059-14 | I6a | Icon labels + `gallery.menus.add`. |
| S-059-13 (admin tables portion) | I6b | Icon labels on Users/Sharing headers. |
| S-059-15, S-059-16 | I4 | Purchasables thumbnail. |
| S-059-17 | I7 | Statistics grid. |
| S-059-18, S-059-19, S-059-20 | I8 | Error.vue diagnostics gating + responsiveness. |

## Analysis Gate
Analysis gate passed 2026-08-22: all 12 source-study findings (D1-D12) were re-grounded against actual v8/backend source via 4 parallel codebase investigations before this plan was written (see spec.md Overview "Corrections to the source studies"). Three findings were corrected/dropped during this pass (D4 dropped entirely; D5's reference example corrected from Bulk Album Edit to FaceMaintenance.vue; D9's root-cause framing corrected from "raw Laravel HTML" to "app's own Error.vue rendering a JSON debug payload"). No further analysis gate is expected before I1 begins.

## Exit Criteria
- All 12 tasks in tasks.md marked `[x]`.
- `php artisan test --filter=LangTest` and `--filter=EditablePurchasableResource` green.
- `make phpstan` — 0 errors on touched files (full-repo run preferred, per this repo's usual convention).
- `npm run check` / `npm run format` clean.
- `vendor/bin/php-cs-fixer fix` clean.
- `git diff --stat -- resources/js/composables/photo.ts resources/js/v7/` empty (NFR-059-01).
- All 20 Branch & Scenario Matrix rows (S-059-01..20) manually re-verified via Playwright at both 390px and 1440px where applicable, with fresh screenshots replacing the "broken" ones referenced from `screenshot/mobile/`/`screenshot/desktop/` in the two study docs.
- `STUDY-DESKTOP-v8.md`/`STUDY-MOBILE-v8.md` corrected and findings marked resolved.
- roadmap.md moved to Completed; knowledge-map.md updated.

## Follow-ups / Backlog
- **Settings.vue mobile responsiveness** (and its banner's grammar/copy-paste issues across `PrintPixelSizesAdmin.vue`/`PurchasablesList.vue`, mobile finding #9) — explicitly out of scope here (Non-Goals), needs its own feature given Settings' much denser per-row-control layout.
- **`photoMenu()` "no actions available" fallback** when the access-filtered context menu collapses to very few/zero items — a minor UX polish possibility surfaced by the D4 investigation, not a bug; consider only if it recurs as a real user complaint.
- **Left-menu drawer dead-zone** (mobile finding #13) and **icon-toolbar density** (mobile finding #15) — cosmetic-only findings from the mobile study not addressed by this feature; candidates for a future pass if desired.
