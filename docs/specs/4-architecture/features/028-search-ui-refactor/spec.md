# Feature 028 – Search UI Refactor

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-03-13 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/028-search-ui-refactor/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/028-search-ui-refactor/tasks.md` |
| Roadmap entry | — |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

The current search UI fires a query automatically after a 1-second debounce as the user types a raw token string. This is opaque for users unfamiliar with the token grammar and gives no explicit control over when the request is sent.

This feature replaces the existing `SearchBox` with a two-mode search form: a **simple mode** (free-text token input + explicit Search button) and an **advanced mode** (a structured form that assembles the token string from individual field controls). The search request is only sent when the user explicitly clicks the Search button. After results arrive the view scrolls to the first result automatically. The backend API (`GET /Search`) is unchanged.

Affected modules: **UI** (`resources/js/`) only.

## Goals

- Replace auto-debounce search with an explicit Search-button-triggered request.
- Provide a simple one-line input that accepts raw token strings (e.g. `tag:sunset date:>=2024-01-01`).
- Provide an advanced panel (toggled by a chevron button) with labelled form fields for the common token modifiers.
- Assemble the token string from advanced fields seamlessly so the simple input always reflects the full query.
- Scroll the viewport to the first result after a successful search.
- No changes to the backend API, routes, or data models.

## Non-Goals

- Modifying the backend search controllers, token parser, or SQL queries.
- Saving/persisting searches.
- Redesigning the result display or pagination (those remain as-is).
- Changing the "go back" / navigation flow.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path |
|----|-------------|--------------|-----------------|--------------|
| FR-028-01 | The search form MUST have a simple-mode text input that accepts the full raw token string. | User types tokens; input value is sent to the search API on button click. | Empty or below-minimum-length input: Search button is disabled; inline hint is shown. | — |
| FR-028-02 | The search form MUST have an explicit **Search** button. | User clicks Search → `searchStore.search(terms)` is called exactly once. | Button is disabled when input is empty or below `search_minimum_length`. | — |
| FR-028-03 | The search request MUST NOT be sent automatically (no debounce auto-fire). | Request is only issued on button click or Enter key press. | Input changes do not trigger network requests. | — |
| FR-028-04 | A **chevron toggle button** (▼/▲) next to the Search button MUST expand/collapse the advanced search panel. | Toggle opens the panel; state remembered for the session. | — | — |
| FR-028-05 | The advanced panel MUST expose individual fields for: **Title**, **Description**, **Location**, **Tags**, **Date from**, **Date to**, **Type**, **Orientation**, **Minimum rating** (avg), and an **EXIF sub-section** with Make, Model, Lens, Aperture, Shutter speed, Focal length, ISO. | Filling any field assembles the corresponding token(s) and appends them to the simple input's value in real-time. | If a field value is invalid (e.g. non-numeric rating) the field shows inline validation; its token is omitted from the query. | — |
| FR-028-06 | Clearing any advanced field MUST remove its corresponding token from the simple input value. | Field cleared → token removed → input value updated. | — | — |
| FR-028-07 | Editing the simple input manually MUST NOT overwrite advanced fields that were filled. Advanced fields are a helper overlay; the simple input is always the authoritative query string. | User edits raw input → advanced fields attempt to parse known tokens and reflect them; unrecognised/compound tokens are left in raw input only. | — | Tokens that cannot be parsed back to a field are kept in the plain text and do not corrupt the advanced panel state. |
| FR-028-08 | After a successful search response the view MUST auto-scroll so the first result (album or photo thumb) is visible. | `element.scrollIntoView({ behavior: 'smooth' })` is called on the first result container after results are populated. | Zero results: no scroll. | — |
| FR-028-09 | The Enter key in the simple input MUST trigger the same action as clicking Search. | Pressing Enter fires search (if valid). | — | — |
| FR-028-10 | The advanced panel field **Tags** MUST support comma-separated entry (each value becomes a separate `tag:value` token). | User types `sunset, beach` → query contains `tag:sunset tag:beach`. | — | — |
| FR-028-11 | The advanced panel field **Date from** / **Date to** MUST assemble to `date:>=YYYY-MM-DD` / `date:<=YYYY-MM-DD` tokens respectively. | DatePicker selection → token appended. | Invalid date: field shows error; token omitted. | — |
| FR-028-12 | The advanced panel field **Type** MUST be a dropdown with options: Image, Video, Raw, Live. Each maps to the corresponding `type:` token value. | Dropdown selection → `type:image` (or similar) token appended. | No selection: token omitted. | — |
| FR-028-13 | The advanced panel field **Orientation** MUST be a dropdown with options: Any, Landscape, Portrait, Square. Maps to `ratio:landscape` / `ratio:portrait` / `ratio:square`. | Selection → token appended. | Any (default): token omitted. | — |
| FR-028-14 | The advanced panel field **Minimum rating (avg)** MUST be a numeric input (0–5) producing a `rating:avg:>=:N` token. | Input 3 → `rating:avg:>=:3` appended. | Non-integer or out-of-range: validation error; token omitted. | — |
| FR-028-15 | The **Clear** / reset action on the advanced panel MUST empty all advanced fields and clear the simple input. | Click Clear → all fields reset, input cleared, previous results removed. | — | — |
| FR-028-16 | The advanced panel MUST expose an **EXIF sub-section** with text inputs for Make, Model, Lens, Aperture, Shutter speed, Focal length, and ISO, each mapping to the corresponding token (`make:`, `model:`, `lens:`, `aperture:`, `shutter:`, `focal:`, `iso:`). | Filling any EXIF field appends `modifier:value` to the query. | — | — |
| FR-028-17 | The advanced panel MUST expose a **Minimum rating (own)** numeric input (0–5) that produces a `rating:own:>=:N` token. This field MUST only be **visible and active when the user is authenticated**; it is hidden (not disabled) for unauthenticated users. | Input 4 → `rating:own:>=:4` appended (authenticated only). | Not authenticated: field not rendered. Non-integer or out-of-range: validation error; token omitted. | — |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies |
|----|-------------|--------|-------------|--------------|
| NFR-028-01 | All new Vue components MUST use Composition API with TypeScript (`<script lang="ts" setup>`). | Coding conventions. | Code review / ESLint. | PrimeVue |
| NFR-028-02 | The advanced panel MUST be accessible: all inputs have visible labels; keyboard navigation works; ARIA attributes match PrimeVue defaults. | Accessibility. | Manual keyboard walkthrough; ARIA audit. | PrimeVue |
| NFR-028-03 | The new components MUST NOT introduce any new API calls beyond the existing `GET /Search` and `GET /Search::init`. | Backend stability. | Network tab inspection. | SearchService |
| NFR-028-04 | Token assembly in the advanced panel MUST be a pure, testable TypeScript function with no side effects. | Testability. | Unit test coverage of the assembler function. | — |
| NFR-028-05 | The advanced panel collapse/expand animation MUST use the existing `Collapse` component already used in the codebase for visual consistency. | UX consistency. | Visual review. | `@vueuse/core` or existing `Collapse` component |
| NFR-028-06 | The feature MUST remain visually consistent with the existing dark/light-mode theming via Tailwind utility classes and PrimeVue tokens. | Design consistency. | Visual review in both modes. | Tailwind CSS, PrimeVue theme |
| NFR-028-07 | The auto-scroll after search MUST be cancelled if the user manually scrolls before the scroll animation completes. | UX. | Manual test. | DOM |

## UI / Interaction Mock-ups

### Simple mode (default)

```
┌────────────────────────────────────────────────────────────────────┐
│  ← [Back]                         Search                           │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  ┌──────────────────────────────────────────┐  [Search]  [▼]      │
│  │  tag:sunset date:>=2024-01-01            │                      │
│  └──────────────────────────────────────────┘                      │
│                                                                    │
│  ┌────────────────────────────────────────────────────────────┐   │
│  │  [album thumb]  [album thumb]  [album thumb]  …            │   │
│  │                                                            │   │
│  │  [photo thumb]  [photo thumb]  [photo thumb]  …            │   │
│  │  [photo thumb]  [photo thumb]  …                           │   │
│  └────────────────────────────────────────────────────────────┘   │
│                                                                    │
└────────────────────────────────────────────────────────────────────┘
```

### Advanced mode (panel expanded)

```
┌────────────────────────────────────────────────────────────────────┐
│  ← [Back]                         Search                           │
├────────────────────────────────────────────────────────────────────┤
│                                                                    │
│  ┌──────────────────────────────────────────┐  [Search]  [▲]      │
│  │  tag:sunset date:>=2024-01-01            │                      │
│  └──────────────────────────────────────────┘                      │
│                                                                    │
│  ╔══════════════════════════════════════════════════════════════╗  │
│  ║  Advanced Search                                 [✕ Clear]  ║  │
│  ║                                                              ║  │
│  ║  Title        Description         Location                  ║  │
│  ║  [__________] [________________] [__________]               ║  │
│  ║                                                              ║  │
│  ║  Tags (comma-separated)   Date from       Date to           ║  │
│  ║  [____________________]   [YYYY-MM-DD]    [YYYY-MM-DD]      ║  │
│  ║                                                              ║  │
│  ║  Type           Orientation     Min. Rating (avg)           ║  │
│  ║  [▾ Image    ]  [▾ Landscape ]  [3__]  (0–5)               ║  │
│  ║                                                              ║  │
│  ║  Min. Rating (own, auth only)                               ║  │
│  ║  [3__]  (0–5)  — hidden when not logged in                  ║  │
│  ║                                                              ║  │
│  ║  ── EXIF ──────────────────────────────────────────────     ║  │
│  ║  Make          Model          Lens                          ║  │
│  ║  [__________] [__________]   [________________]            ║  │
│  ║                                                              ║  │
│  ║  Aperture      Shutter        Focal length    ISO           ║  │
│  ║  [__________] [__________]   [__________]    [_______]     ║  │
│  ║                                                              ║  │
│  ╚══════════════════════════════════════════════════════════════╝  │
│                                                                    │
│  (results appear here after clicking Search)                       │
└────────────────────────────────────────────────────────────────────┘
```

### States

| State | Trigger | Visual |
|-------|---------|--------|
| Search disabled | Input empty or below minimum | Search button disabled + hint text |
| Advanced expanded | User clicks ▼ | Panel slides open; chevron becomes ▲ |
| Advanced collapsed | User clicks ▲ | Panel slides closed; chevron becomes ▼ |
| Loading | Search clicked | Existing `LoadingProgress` spinner shown |
| No results | Response: 0 albums + 0 photos | Existing "no results" message, no scroll |
| Results present | Response with data | View scrolls smoothly to result container |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-028-01 | User types raw token string in simple input and presses Search → results appear and view scrolls to them. |
| S-028-02 | User types in simple input but presses Search while input is below minimum length → Search button is disabled; no request sent. |
| S-028-03 | User fills advanced fields only (no manual token typing) → correct token string assembled, button enabled, search fires. |
| S-028-04 | User fills advanced fields AND types additional tokens manually → both are merged in the query string. |
| S-028-05 | User edits raw input while advanced fields are populated → advanced fields update to reflect parseable tokens; unknown tokens stay in raw input. |
| S-028-06 | User clicks Clear in the advanced panel → all fields reset, input cleared, previous results removed. |
| S-028-07 | User opens advanced panel, fills fields, collapses panel, clicks Search → the assembled token string is still sent. |
| S-028-08 | Search returns zero results → no scroll, no-results message shown. |
| S-028-09 | User enters invalid date in Date from → that field shows validation error, date token omitted from query. |
| S-028-10 | User enters rating 6 → field validation error, token omitted. |
| S-028-11 | User presses Enter in simple input → same as clicking Search button. |
| S-028-12 | Tags field: comma-separated `sunset, beach` → query contains `tag:sunset tag:beach`. |
| S-028-13 | Page reload / navigation to search URL → advanced panel starts collapsed; simple input is empty. |
| S-028-14 | User fills EXIF sub-section fields (e.g. Make = "Canon", ISO = "400") → query contains `make:Canon iso:400`. |
| S-028-15 | Authenticated user sets Min. Rating (own) to 4 → query contains `rating:own:>=:4`. Unauthenticated user: field is not visible. |

## Test Strategy

- **Core / backend:** No changes; existing PHPUnit tests continue to pass.
- **UI (TypeScript unit tests):** The token-assembly pure function must have unit tests covering all FR-028-05/06/07/10/11/12/13/14 combinations. Place in `resources/js/__tests__/search/` using Vitest.
- **UI (component tests):** Optional Vitest/Vue Test Utils smoke tests for `AdvancedSearchPanel.vue` and the refactored `SearchBox.vue` (button enable/disable, Enter key).
- **UI (manual):** Walk through all 15 scenarios in the scenario matrix in both light and dark mode.
- **Accessibility:** Keyboard-only walkthrough for advanced panel fields.

## Interface & Contract Catalogue

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-028-01 | Simple input | Default; shows existing `SearchHeader` + single-line input + Search button + ▼ toggle. |
| UI-028-02 | Advanced panel expanded | ▼ clicked → `AdvancedSearchPanel` slides down; ▲ shown. |
| UI-028-03 | Advanced panel collapsed | ▲ clicked → panel hidden; ▼ shown. |
| UI-028-04 | Results visible | After search response → `ResultPanel` populated and scrolled into view. |

### New Vue Components

| Component | Path | Responsibility |
|-----------|------|----------------|
| `SearchInputBar` | `resources/js/components/forms/search/SearchInputBar.vue` | Renders the simple text input, Search button, and ▼/▲ toggle. Emits `search`, `clear`, `toggle-advanced`. |
| `AdvancedSearchPanel` | `resources/js/components/forms/search/AdvancedSearchPanel.vue` | Renders the grid of labelled form fields. Emits `update:tokens` (assembled token string fragment) and `clear`. |
| `useSearchTokenAssembler` | `resources/js/composables/useSearchTokenAssembler.ts` | Pure composable that converts `AdvancedSearchState` struct → raw token string. Inverse direction: parses raw string back into `AdvancedSearchState` for round-trip sync. |

### Modified Vue Components

| Component | Path | Change |
|-----------|------|--------|
| `SearchBox` | `resources/js/components/forms/search/SearchBox.vue` | Redesigned to use `SearchInputBar` + `AdvancedSearchPanel`; removes debounce; adds explicit button. |
| `SearchState` (store) | `resources/js/stores/SearchState.ts` | Remove `useDebounceFn`; remove auto-trigger from `searchTerm` watcher; add `searchNow()` action; keep `search()` as-is for external callers. |

### Token Assembly Contract

The `useSearchTokenAssembler` composable operates on the following `AdvancedSearchState` interface:

```ts
interface AdvancedSearchState {
  title: string;
  description: string;
  location: string;
  tags: string;           // comma-separated
  dateFrom: string;       // YYYY-MM-DD or ""
  dateTo: string;         // YYYY-MM-DD or ""
  type: string;           // "image" | "video" | "raw" | "live" | ""
  orientation: string;    // "landscape" | "portrait" | "square" | ""
  ratingMin: string;      // "0"–"5" or ""
  ratingOwn: string;      // "0"–"5" or "" — only assembled when user is authenticated
  // EXIF fields
  make: string;
  model: string;
  lens: string;
  aperture: string;
  shutter: string;
  focal: string;
  iso: string;
}
```

Assembly rules (all tokens are AND-ed by appending with a space):

| Field | Token emitted |
|-------|---------------|
| `title` non-empty | `title:value` (multi-word → `title:"value"`) |
| `description` non-empty | `description:value` |
| `location` non-empty | `location:value` |
| `tags` | one `tag:value` per comma-segment (trimmed, non-empty) |
| `dateFrom` | `date:>=YYYY-MM-DD` |
| `dateTo` | `date:<=YYYY-MM-DD` |
| `type` non-empty | `type:value` |
| `orientation` non-empty | `ratio:value` |
| `ratingMin` non-empty | `rating:avg:>=:N` |
| `ratingOwn` non-empty **and user is authenticated** | `rating:own:>=:N` |
| `make` non-empty | `make:value` |
| `model` non-empty | `model:value` |
| `lens` non-empty | `lens:value` |
| `aperture` non-empty | `aperture:value` |
| `shutter` non-empty | `shutter:value` |
| `focal` non-empty | `focal:value` |
| `iso` non-empty | `iso:value` |

---

*Last updated: 2026-03-13*
