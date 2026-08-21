# Feature 059 Tasks – Resolve Desktop UX Audit Findings

_Status: Draft_
_Last updated: 2026-08-22_

> Keep this checklist aligned with plan.md's increments. Stage tests before implementation where a test seam exists (I4); most other tasks are frontend rendering/wiring fixes verified by the manual Playwright re-run described in each task, per spec.md's Test Strategy.
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions.
> Q-059-01/02/03 are already resolved in spec.md's Appendix — do not re-open them.

## Checklist

- [ ] T-059-01 – Fork Leaflet marker-cluster code into `resources/js/v8/composables/photo-map.ts` (FR-059-01, NFR-059-01, S-059-01, S-059-02).
  _Intent:_ Create the new module (copy `PhotosLayer`/`photosLayerFunc`/`Cluster`/`clusterFunc` from `resources/js/composables/photo.ts:4-84` verbatim), add a self-contained `import "leaflet.markercluster/dist/leaflet.markercluster.js";` at its top, and repoint `Map.vue`'s import. Leave `photo.ts` and `resources/js/v7/` untouched.
  _Verification commands:_
  - `npm run check`
  - Manual: load `/map` on an album with geotagged photos at 390px and 1440px, confirm tiles + markers render with zero console errors.
  - `git diff --stat -- resources/js/composables/photo.ts resources/js/v7/` → empty.
  _Notes:_ See plan.md I1.

- [ ] T-059-02 – Add `.catch()`/`.finally()` to `Flow.vue`'s `load()` and `onMounted` (FR-059-02, S-059-03, S-059-04).
  _Intent:_ Match `TimelineState.ts`'s `.finally() => isLoading = false` pattern so a rejected fetch always closes the loading overlay.
  _Verification commands:_
  - `npm run check`
  - Manual: disable the Flow module (or reproduce the 403), load `/flow`, confirm `isLoading` resolves to `false` (spinner closes) instead of spinning forever.
  _Notes:_ See plan.md I2. Does not by itself guarantee the error becomes *visible* — that's T-059-03.

- [ ] T-059-03 – Audit `z-50`(+) overlays and fix `Error.vue`/`LoadingProgress.vue` stacking order (FR-059-03, NFR-059-05, S-059-05).
  _Intent:_ List every overlay/modal component under `resources/js/v8/components/` using `z-50` or higher; choose and implement a stacking fix (raise `Error.vue`'s z-index above `LoadingProgress.vue`'s, or have `LoadingProgress` not render while a global error is active) that doesn't regress any of the audited overlays.
  _Verification commands:_
  - `npm run check`
  - Manual: reproduce T-059-02's Flow 403 end-to-end — confirm the error is now visibly on top, not hidden.
  - Manual: spot-check 2-3 other `LoadingProgress`-using views (e.g. Map, Timeline) for no visual regression.
  _Notes:_ See plan.md I2. This is the systemic half of the Flow fix — do not scope it to Flow.vue only.

- [ ] T-059-04 – Fix Admin Dashboard Overview stats fetch timing (FR-059-04, S-059-06, S-059-07, S-059-08).
  _Intent:_ Replace `AdminDashboard.vue`'s one-shot `onMounted` `initData` check with a reactive `watch(initData, ..., { immediate: true })`; fix the inverted `v-if="!stats && !isLoading"` at line 109 so loading/failed states render correctly.
  _Verification commands:_
  - `npm run check`
  - Manual: fresh page load of `/admin` as admin (real data present) at 390px and 1440px — stats populate without a manual refresh, every time across 3+ repeated loads.
  - Manual: simulate a failed `GET /Admin/Stats` (e.g. temporary network throttle/block) — confirm an explicit failed state, not a stuck blank area.
  _Notes:_ See plan.md I3.

- [ ] T-059-05 – Read `app/Models/Purchasable.php` in full before implementing T-059-06 (risk mitigation, FR-059-09).
  _Intent:_ Confirm `isAlbumLevel()`'s exact semantics and rule out a third `photo_id`/`album_id` state not covered by the investigation.
  _Verification commands:_ N/A (research task).
  _Notes:_ See plan.md I4 preconditions.

- [ ] T-059-06 – Fix `EditablePurchasableResource`'s album-level thumbnail fallback (FR-059-09, NFR-059-04, S-059-15, S-059-16).
  _Intent:_ When `photo_id` is null and `album_id` is set, resolve `photo_url` from `$item->album?->get_thumb()` instead of leaving it null.
  _Verification commands:_
  - `php artisan test --filter=EditablePurchasableResource`
  - `make phpstan`
  - Manual: `/admin/purchasables` shows the album's real cover for "French Alps 2025" (or equivalent test data), not a black square.
  _Notes:_ See plan.md I4. Depends on T-059-05.

- [ ] T-059-07 – Write `EditablePurchasableResourceTest.php` (FR-059-09, NFR-059-04, FX-059-01).
  _Intent:_ Cover photo-level (unchanged), album-level-with-cover (new fallback), album-level-no-cover (placeholder path preserved) cases.
  _Verification commands:_
  - `php artisan test --filter=EditablePurchasableResource`
  _Notes:_ See plan.md I4. Write alongside or just before T-059-06 (test-first where practical).

- [ ] T-059-08 – Make Users table responsive (FR-059-05, FR-059-06 n/a here, S-059-09, S-059-10).
  _Intent:_ Hide `may_upload`/`may_edit_own_settings`/`upload_trust_level`/`quota` columns below `sm` (column `meta.class`, `FaceMaintenance.vue` pattern); fold quota inline under username; adjust/drop `:ui="{ base: 'table-fixed' }"` so hidden columns free up space; keep Username + Edit/Delete always visible.
  _Verification commands:_
  - `npm run check`
  - Manual: `/admin/users` at 390px and 1440px — confirm no regression at desktop width, no overlap at mobile width, Edit/Delete reachable at both.
  _Notes:_ See plan.md I5.

- [ ] T-059-09 – Make Tags table responsive (FR-059-05, S-059-09, S-059-10).
  _Intent:_ Hide `Photos`/`Albums` counts below `sm`; fold photo count inline; keep Name + action icons always visible.
  _Verification commands:_
  - `npm run check`
  - Manual: `/tags` at 390px and 1440px.
  _Notes:_ See plan.md I5.

- [ ] T-059-10 – Make Orders table responsive and remove its small-screen banner (FR-059-05, FR-059-06, S-059-09, S-059-10, S-059-11).
  _Intent:_ Hide Amount/Date below `sm`; fold Amount inline; keep Client + Status + primary action always visible; delete the `lg:hidden` banner block at `OrderList.vue:8`.
  _Verification commands:_
  - `npm run check`
  - Manual: `/orders` at 390px and 1440px — confirm no banner at any width, table usable at 390px.
  _Notes:_ See plan.md I5.

- [ ] T-059-11 – Make Purchasables table responsive and remove its small-screen banner (FR-059-05, FR-059-06, S-059-09, S-059-10, S-059-11).
  _Intent:_ Hide Description/Notes below `sm`; fold lowest price inline; keep Title + thumbnail always visible; delete the `lg:hidden` banner block at `admin/Purchasables.vue:8`.
  _Verification commands:_
  - `npm run check`
  - Manual: `/admin/purchasables` at 390px and 1440px.
  _Notes:_ See plan.md I5. Verify this doesn't conflict with T-059-06's thumbnail fix (same file).

- [ ] T-059-12 – Confirm Settings' small-screen banner is unaffected (FR-059-06, S-059-12).
  _Intent:_ Regression check only — `Settings.vue`'s banner usage must remain exactly as before this feature.
  _Verification commands:_
  - Manual: `/admin/settings` at 390px — banner still present, unchanged text.
  _Notes:_ See plan.md I5 / spec.md Q-059-02.

- [ ] T-059-13 – Add `aria-label`s to main header controls (FR-059-07, S-059-13 partial).
  _Intent:_ `OpenLeftMenu.vue` hamburger; `AlbumsHeader.vue`'s list/search/bell/help/"+"/mobile-overflow-chevron buttons.
  _Verification commands:_
  - `npm run check`
  - Manual: VoiceOver/NVDA pass confirms each button announces a meaningful name.
  _Notes:_ See plan.md I6a.

- [ ] T-059-14 – Add `gallery.menus.add` key and real labels to the mobile overflow dropdown (FR-059-08, S-059-14).
  _Intent:_ Add the missing key to `lang/en.json` ("Add"); change `AlbumsHeader.vue`'s `mobileMenuSections` (`:349-354`) to emit translated labels per source item's `key` instead of `label: ""`.
  _Verification commands:_
  - `npm run check`
  - Manual: open the mobile overflow dropdown, confirm every item (including Add) shows real text, no raw key strings.
  _Notes:_ See plan.md I6a.

- [ ] T-059-15 – Add `aria-label`/tooltip-independent labels to `AlbumHero.vue`'s stats icon and `PhotoHeader.vue`'s bare buttons (FR-059-07, S-059-13 partial).
  _Intent:_ New i18n key for the stats icon (`openStatistics`); new i18n keys for play/open-original/download/edit/info in `PhotoHeader.vue`.
  _Verification commands:_
  - `npm run check`
  - Manual: VoiceOver/NVDA pass over the album hero and photo header toolbars.
  _Notes:_ See plan.md I6a.

- [ ] T-059-16 – Add `aria-label`s to Users/Sharing table header icons (FR-059-07, S-059-13 remaining).
  _Intent:_ Reuse existing `users.upload_rights`/`users.edit_rights`/`users.upload_trust_level`/`users.quota` and `sharing.grants.*` keys as `aria-label` values on the respective header icon slots.
  _Verification commands:_
  - `npm run check`
  - Manual: VoiceOver/NVDA pass over both tables' headers.
  _Notes:_ See plan.md I6b.

- [ ] T-059-17 – Fix Statistics storage-card grid dead-space (FR-059-10, S-059-17).
  _Intent:_ Change `SizeVariantMeter.vue:6`'s `sm:justify-between justify-center` to `sm:justify-center justify-center`.
  _Verification commands:_
  - `npm run check`
  - Manual: `/statistics` at a viewport width producing a trailing partial row — cards sit together, no gap.
  _Notes:_ See plan.md I7.

- [ ] T-059-18 – Confirm `can_see_diagnostics` availability for guest sessions (risk mitigation, FR-059-11).
  _Intent:_ Verify `initData.value.settings.can_see_diagnostics` is present and `false` for an unauthenticated/guest session before wiring it into `LycheeState.ts`.
  _Verification commands:_ N/A (research task).
  _Notes:_ See plan.md I8 preconditions.

- [ ] T-059-19 – Gate `is_debug_enabled` behind `can_see_diagnostics` (FR-059-11, S-059-18, S-059-19).
  _Intent:_ `LycheeState.ts` line 14's default and line 304's force-reset both read `initData.value?.settings.can_see_diagnostics ?? false` instead of a bare `true`.
  _Verification commands:_
  - `npm run check`
  - Manual: trigger a backend error as an admin (full detail expected) and as a non-privileged/guest user (message-only expected).
  _Notes:_ See plan.md I8. Depends on T-059-18.

- [ ] T-059-20 – Make `Error.vue`'s trace/exception display responsive (FR-059-12, S-059-20).
  _Intent:_ Wrap or horizontally scroll long lines (file paths, trace frames) within their own container at every viewport width.
  _Verification commands:_
  - `npm run check`
  - Manual: trigger a debug-detail-visible error at 390px, confirm every line is fully readable, none clipped.
  _Notes:_ See plan.md I8.

- [ ] T-059-21 – Propagate new translation keys to all locales (NFR-059-03).
  _Intent:_ `gallery.menus.add` plus every new key from T-059-15, added to all `lang/*.json` locale files using the English-placeholder convention.
  _Verification commands:_
  - `php artisan test --filter=LangTest`
  _Notes:_ See plan.md I9.

- [ ] T-059-22 – Run full quality gates (Exit Criteria).
  _Intent:_ Confirm the whole feature is clean end to end.
  _Verification commands:_
  - `make phpstan`
  - `npm run check`
  - `npm run format`
  - `vendor/bin/php-cs-fixer fix`
  - `git diff --stat -- resources/js/composables/photo.ts resources/js/v7/` → empty
  _Notes:_ See plan.md I9.

- [ ] T-059-23 – Re-run all 20 Branch & Scenario Matrix rows via Playwright and replace baseline screenshots (Exit Criteria).
  _Intent:_ Confirm S-059-01..20 all pass at both 390px and 1440px where applicable; capture fresh "after" screenshots.
  _Verification commands:_
  - Manual Playwright walkthrough matching `STUDY-DESKTOP-v8.md`/`STUDY-MOBILE-v8.md`'s original methodology.
  _Notes:_ See plan.md I9 / Exit Criteria.

- [ ] T-059-24 – Correct `STUDY-DESKTOP-v8.md`/`STUDY-MOBILE-v8.md` and update roadmap.md/knowledge-map.md (Documentation Deliverables).
  _Intent:_ Mark D1/D2/D3/D5/D6/D7/D8/D9 (desktop) and their mobile-study counterparts as resolved; correct D4's "not a bug" framing, D5's Bulk Album Edit claim, and D9's "raw Laravel debug page" framing directly in the study text (not just in this spec); move roadmap.md's Feature 059 row to Completed; update knowledge-map.md.
  _Verification commands:_ N/A (documentation task).
  _Notes:_ See plan.md I9.

## Notes / TODOs
- FR-059-01's exact chunking mechanism (why `photo.ts`'s module body ran before `Map.vue`'s side-effect import resolved) is not independently reproduced outside the browser — the fix (self-contained import in the new fork) is robust regardless, per plan.md's Assumptions.
- I6a and I6b (T-059-13..16) can be implemented in parallel — they touch disjoint files.
- I5's four table tasks (T-059-08..11) can be implemented in parallel — they touch disjoint files, though T-059-11 should be sequenced after or rebased against T-059-06 since both touch `admin/Purchasables.vue`.
