# Feature 083 – Fix Search Header Refresh

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-10 |
| Owners | mitpjones |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #083 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

On the **v7** gallery Search view (`resources/js/v7/views/gallery-panels/Search.vue`), the header (back button + "Search" title) disappears on real mobile browsers specifically when a `/search?q=...` results URL is loaded via a hard refresh — not when the user navigates into search from elsewhere in the app. Reported against `akina.oldboys.nz` (Lychee 7.12.0), reproduced and root-caused against the dev fork via real-device remote debugging (`chrome://inspect`). Does not reproduce in desktop Chrome or Chrome DevTools device emulation, since neither simulates the real mobile browser's address-bar expand/collapse behaviour the bug depends on.

Two compounding causes in the same file:
1. `onMounted()` auto-runs the search (and a `resultsMarkerRef.scrollIntoView()` call) whenever `route.query.q` is already present — true only on a hard reload of a results URL.
2. That `scrollIntoView()` call used the default `block: "start"` alignment, which per the CSSOM View spec walks up through every ancestor whose `overflow` isn't `visible` — including the outer `overflow-y-hidden` wrapper around the header (added only to suppress a scrollbar artifact when toggling full-screen, never meant to be scrolled). The inner results container was also sized with plain `100vh` while the outer wrapper used `h-svh`, a large/small-viewport-unit mismatch that overstates available space specifically at cold-load (before the real address bar has settled), making the `scrollIntoView` call more likely to need that outer-ancestor scroll in the first place.

This is a v7-only, frontend-only fix (no REST/DTO/DB changes). No UI layout changes — the fix corrects existing behaviour back to the already-intended design (header stays visible, search results fill the remaining scroll area).

## Goals
- The search header remains visible after a hard refresh of a `/search?q=...` results URL on a real mobile browser.
- No change to desktop or already-working mobile-navigation behaviour.
- No change to the `v8` Nuxt-UI tree (out of scope — gated off by default, not the reported code path).

## Non-Goals
- Porting this fix to `v8`'s equivalent `Search.vue` (different component tree, different header implementation, not reported as affected, not in the NUXT_UI_ENABLED default path). May be worth a follow-up if v8 ships with the same `scrollIntoView` pattern.
- General auditing of every other `scrollIntoView()` call in the codebase for the same ancestor-climbing risk (see Follow-ups in the plan).
- Adding a JS/TS automated test harness for mobile-viewport behaviour — none exists in this repo today (no `npm test` script, Playwright scaffolded but unused — no `e2e/` directory, not wired into CI); out of scope for a bug-fix-sized change.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-083-01 | The inner search-results container's height (non-full-screen case) must be computed with the small-viewport unit (`svh`), matching the outer wrapper's existing `h-svh`, instead of the large-viewport unit (`vh`). **Not `dvh`** (Q-083-02, resolved): a related `v8` mobile-toolbar/`100vh` bug (discussion #4953) was fixed upstream using `dvh` (PR #4956), but that case sizes a single full-screen modal with nothing else measured relative to it, where live-tracking the toolbar is desirable. Here the outer wrapper (pre-existing, untouched `h-svh`) and this inner container together partition one fixed total height, which needs a stable, static value on both sides — `dvh`'s live resizing risks the scrollable results container visibly resizing mid-scroll as the toolbar animates. | Both the outer wrapper and inner results container agree on the same (small/safe, static) viewport-height assumption at any point in the page lifecycle, including cold-load before the address bar has settled. | N/A (CSS-only; no user input). | N/A. | None. | Real-device root-cause investigation, 2026-11-09/10 session; `dvh` alternative considered and rejected per Q-083-02. |
| FR-083-02 | The post-search `resultsMarkerRef.scrollIntoView()` call must use `block: "nearest"` instead of the default `block: "start"`. | Scrolling to reveal results never needs to climb into the outer `overflow-y-hidden` header wrapper — only the inner, intended results container scrolls. | N/A (no user input; internal scroll-alignment behaviour). | N/A. | None. | Real-device root-cause investigation, confirmed via isolated real-device testing. |
| FR-083-03 | Both FR-083-01 and FR-083-02 ship together, even though either alone was independently confirmed sufficient on real-device testing. | FR-083-01 removes the layout condition that forces the climb in the first place; FR-083-02 removes the climb itself regardless of layout state — together they close two independently-reachable instances of the same failure mode. | N/A. | N/A. | None. | Owner decision after isolated A/B/baseline real-device testing (see Appendix). |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-083-01 | No behavioural change to any other gallery view (`Timeline.vue`, `Albums.vue`, `Album.vue`, `Flow.vue`, etc.) or to the `v8` tree. | Minimal, scoped bug fix — this is not a refactor. | Diff touches only `resources/js/v7/views/gallery-panels/Search.vue`, two lines. | None. | AGENTS.md "Backward-compat stance" / straight-line increments. |
| NFR-083-02 | Fix must pass this repo's frontend quality gate (`npm run format`, `npm run check`) with zero new errors/warnings. | AGENTS.md Quality gate for Laravel projects — Frontend changes. | CI-equivalent local run of both commands, zero diff/errors. | Node toolchain already present in the dev container. | AGENTS.md §"After Completing Work". |

## UI / Interaction Mock-ups (required for UI-facing work)

No layout/markup change — this restores already-intended visual behaviour rather than introducing new UI. Before/after is a timing-dependent bug, not a static layout difference:

```
BEFORE (bug) — hard refresh of /search?q=... on real mobile:
┌─────────────────────────┐        ┌─────────────────────────┐
│ ‹      Search            │  hard  │  [G. Warren______][Srch]│  <- header silently
│ [G. Warren______][Srch]  │ reload │  Photos (19)             │     scrolled away
│ Photos (19)              │  ───▶  │  [thumb][thumb][thumb]   │
│ [thumb][thumb][thumb]    │        │  [thumb][thumb][thumb]   │
└─────────────────────────┘        └─────────────────────────┘

AFTER (fixed) — same action:
┌─────────────────────────┐        ┌─────────────────────────┐
│ ‹      Search            │  hard  │ ‹      Search            │  <- header stays
│ [G. Warren______][Srch]  │ reload │ [G. Warren______][Srch]  │
│ Photos (19)               │ ───▶  │ Photos (19)              │
│ [thumb][thumb][thumb]    │        │ [thumb][thumb][thumb]    │
└─────────────────────────┘        └─────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-083-01 | Hard refresh on `/search?q=<term returning results>` on a real mobile browser → header stays visible (reproduces the bug report; must now pass). |
| S-083-02 | Navigate into search from within the app (no pre-existing `q` in the URL), then perform a search → header stays visible (already worked before the fix; must remain unaffected). |
| S-083-03 | Same hard-refresh action on desktop Chrome → unaffected either way (bug never reproduced here; confirms the fix doesn't regress desktop). |
| S-083-04 | Same hard-refresh action under Chrome DevTools device emulation → unaffected either way (bug never reproduced here either; emulation doesn't simulate the real address-bar behaviour this depends on, so this scenario cannot distinguish fixed/unfixed — documented so a future agent doesn't mistake DevTools emulation for valid coverage of this bug class). |

## Test Strategy
- **Models / Actions:** N/A — no backend code touched.
- **REST API (v2/v3):** N/A — no backend code touched.
- **Artisan commands:** N/A.
- **Frontend (v7/v8):** No automated test exists or is added for S-083-01/02 — this repo has no wired-up JS/TS test runner (Playwright is scaffolded in `playwright.config.ts` but has no `e2e/` directory and isn't invoked in CI; see Non-Goals) and the bug is inherently real-mobile-browser-only (doesn't reproduce in Playwright's own desktop/emulated browser projects either, by the same mechanism that makes it invisible to DevTools emulation). Coverage is therefore manual, real-device verification: see Appendix for the full isolation matrix (baseline reproduces the bug; FR-083-01 alone fixes it; FR-083-02 alone fixes it; both together fix it), performed via `chrome://inspect` remote debugging against a real phone over USB, for each of four separately rebuilt/redeployed variants. `npm run check` (`vue-tsc`) and `npm run format` (Prettier) still run as the standard frontend quality gate (NFR-083-02), since they catch unrelated regressions even though they don't exercise this specific runtime behaviour.
- **Docs/Contracts:** No generated-type or API-contract impact (no PHP resource/DTO changes).
- **Desktop no-regression check (S-083-03):** confirmed with exact layout geometry, not just a visual check. Both the dev fork's working tree and its running local container were toggled between the unpatched (`HEAD`) and fixed (staged) versions of `Search.vue`, rebuilt, and hard-refreshed (`Ctrl+Shift+R`, with the served JS bundle hash verified in the live DOM before each capture — a same-URL `navigate()` call was tried first and found to silently keep the old bundle loaded, which would have invalidated the comparison; this is noted here so a future agent doesn't repeat it) at `http://localhost:8000/search?q=10BC`, same viewport (1850×910). `getBoundingClientRect()` on the header toolbar, search input, "Photos (N)" heading, and first result thumbnail — plus `scrollY`/`document.body.scrollHeight` — came out byte-identical between the two builds. Expected: `svh`/`vh` resolve identically with no dynamic toolbar on desktop, and `scrollIntoView`'s `block: "nearest"` vs. the old default only differ when a scroll is actually needed to satisfy alignment, which it never was here.

## Interface & Contract Catalogue

No new domain objects, API routes, CLI commands, telemetry events, or fixtures. UI state table included for completeness:

### UI States
| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-083-01 | Search results view, header visible | Default/fixed state after any navigation path into `/search` — see S-083-01/02. |

## Telemetry & Observability
None — no telemetry change.

## Documentation Deliverables
- This spec/plan/tasks/open-questions set.
- Knowledge-map addendum (see plan Exit Criteria) noting the `scrollIntoView()`-into-`overflow-hidden`-ancestor pitfall, since it's a reusable lesson beyond this one call site (see Non-Goals re: broader audit as a follow-up).
- Upstream PR against `LycheeOrg/Lychee`, referencing the bug discussion the user already posted at `https://github.com/LycheeOrg/Lychee/discussions/categories/bugs`.

## Fixtures & Sample Data
None.

## Spec DSL

```
domain_objects: []
routes: []
cli_commands: []
telemetry_events: []
fixtures: []
ui_states:
  - id: UI-083-01
    description: Search results view, header visible
```

## Appendix

**Real-device isolation testing (2026-11-09, via `chrome://inspect` against a real phone, each variant separately rebuilt and deployed to the dev fork's local instance):**

| Variant | Result |
|---|---|
| Neither fix (baseline) | Bug reproduces |
| FR-083-02 (`scrollIntoView` block:"nearest") only | Fixed |
| FR-083-01 (`svh`) only | Fixed |
| Both together | Fixed |

Both fixes independently break the same causal chain: the `vh`/`svh` mismatch creates a cold-load geometry where the inner results container cannot satisfy "scroll target to top" on its own, forcing the default `scrollIntoView` alignment to climb into the outer `overflow-hidden` ancestor; fixing the `svh` mismatch removes the need for that climb, fixing the alignment mode removes the climb itself. See FR-083-03 for why both ship despite either being independently sufficient.

**Related upstream discussions:**
- Bug reported and this fix offered: [discussion #4968](https://github.com/LycheeOrg/Lychee/discussions/4968). ildyria (maintainer) approved opening a PR and flagged it as similar to #4953.
- Related-but-distinct precedent: [discussion #4953](https://github.com/LycheeOrg/Lychee/discussions/4953) / [PR #4956](https://github.com/LycheeOrg/Lychee/pull/4956) — same `100vh`-on-mobile root cause class, different component (`v8` `PhotoPanel.vue`), fixed with `dvh` rather than `svh`. See FR-083-01 and Q-083-02 for why this fix uses `svh` instead, deliberately.
