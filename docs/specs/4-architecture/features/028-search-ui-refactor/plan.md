# Feature Plan 028 – Search UI Refactor

_Linked specification:_ `docs/specs/4-architecture/features/028-search-ui-refactor/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-03-13

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant. Log new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../../open-questions.md).

## Vision & Success Criteria

Users gain full control over when search fires and are no longer required to know the raw token grammar. The simple input retains full power-user access; the advanced panel lowers the barrier for casual queries. Quality bars: frontend linting clean (`npm run check`), token assembler has unit-test coverage for all assembly rules, all existing PHPUnit tests remain green.

## Scope Alignment

**In scope:**
- New `SearchInputBar` component (simple input + Search button + ▼/▲ toggle).
- New `AdvancedSearchPanel` component (labelled form grid including EXIF sub-section and `rating:own:` field).
- New `useSearchTokenAssembler` composable (pure token assembly + reverse parsing, covering all modifiers including EXIF and `rating:own:`).
- Refactored `SearchBox` that composes the two new components.  
- Removal of the 1-second debounce auto-fire from the store.
- Auto-scroll to first result after a successful search.
- TypeScript unit tests for the assembler composable.

**Out of scope:**
- Backend API, routes, controllers, token parser.

- Persistent saved searches.
- Redesigning `ResultPanel`, `SearchHeader`, or navigation flow.

## Dependencies & Interfaces

| Dependency | Notes |
|-----------|-------|
| PrimeVue | `InputText`, `DatePicker` (or `Calendar`), `Select`/`Dropdown`, `InputNumber`, `Button` — all already in the project. |
| `@vueuse/core` | Already used; `useDebounceFn` can be removed from `SearchState.ts`. |
| Existing `Collapse` component | Used in `Search.vue` for the header; reuse for the advanced panel animation. |
| `SearchService` | Unchanged; `init()` + `search()` remain the only calls. |
| `SearchState` store | Lightly modified: remove debounce watcher; add `searchNow()` alias if needed. |
| i18n (`lang/en/gallery.php`) | New translation keys needed for advanced panel labels. |

## Assumptions & Risks

**Assumptions:**
- PrimeVue `DatePicker`/`Calendar` component is available and working in the project.
- Tailwind utility classes already used in the project are sufficient for the advanced panel layout.
- The Collapse animation component used elsewhere can be applied to the advanced panel without modification.

**Risks / Mitigations:**
- _Round-trip sync_ (raw input ↔ advanced fields) is the most complex piece. Risk: edge cases with quoted values or multi-word tokens. Mitigation: define the `useSearchTokenAssembler` contract precisely in the spec and test all combinations before wiring it into the UI.
- _PrimeVue DatePicker_ API may differ slightly between project's PrimeVue version; verify the correct component name and props before implementation.

## Implementation Drift Gate

After each increment: run `npm run check` (TypeScript + ESLint). After I4: run the unit tests for the assembler. After I7 (final): run `npm run format && npm run check && php artisan test`.

## Increment Map

### I1 – Translation Keys

- _Goal:_ Add i18n keys for all advanced panel labels (and Search button) so strings are never hardcoded.
- _Preconditions:_ None.
- _Steps:_
  - Add keys to `lang/en/gallery.php` under a `search.advanced` sub-group:
    `title`, `description`, `location`, `tags`, `date_from`, `date_to`, `type`, `orientation`, `rating_min`, `clear`, `search_button`, `toggle_advanced`, `any` (for empty dropdown option).
  - Verify the TypeScript transformer picks them up.
- _Commands:_ `php artisan typescript:transform` (or equivalent)
- _Exit:_ Keys present in `lang/en/gallery.php`; no TS errors.

### I2 – `useSearchTokenAssembler` Composable

- _Goal:_ Pure TypeScript function: `AdvancedSearchState → string` (assemble) and `string → AdvancedSearchState` (parse back).
- _Preconditions:_ I1 not required.
- _Steps:_
  - Create `resources/js/composables/useSearchTokenAssembler.ts`.
  - Implement `assembleTokens(state: AdvancedSearchState): string` following the assembly rules in `spec.md`.
  - Implement `parseTokens(raw: string): { advanced: AdvancedSearchState; remainder: string }` to reverse-parse known token prefixes.
  - Export both as named exports (not as a Vue composable — keep them pure functions).
- _Commands:_ —
- _Exit:_ File created; no TS errors (`npm run check`).

### I3 – Unit Tests for Token Assembler

- _Goal:_ Green unit tests covering all assembly rules and parse-back round-trips.
- _Preconditions:_ I2.
- _Steps:_
  - Create `resources/js/__tests__/search/tokenAssembler.test.ts` (Vitest).
  - Tests for: title, description, location, tags (single / multi), dateFrom, dateTo, type, orientation, ratingMin — each alone and in combination.
  - Tests for parse-back: known tokens extracted into fields; unknown tokens returned as remainder.
  - Tests for edge cases: empty values omitted, quoted multi-word title.
- _Commands:_ `npm run check`
- _Exit:_ All tests pass.

### I4 – `SearchInputBar` Component

- _Goal:_ Simple input bar with Search button and ▼/▲ toggle, replacing the current `InputText`-only `SearchBox`.
- _Preconditions:_ I1.
- _Steps:_
  - Create `resources/js/components/forms/search/SearchInputBar.vue`.
  - Props: `modelValue: string`, `minLength: number`, `advancedOpen: boolean`.
  - Emits: `update:modelValue`, `search`, `clear`, `update:advancedOpen`.
  - Search button disabled when `modelValue.length < minLength || modelValue === ""`.
  - Enter key in input triggers `search` emit.
  - ▼/▲ icon button toggles `advancedOpen`.
  - Inline minimum-length hint (replaces current behaviour).
- _Commands:_ `npm run check`
- _Exit:_ Component renders; no TS/lint errors.

### I5 – `AdvancedSearchPanel` Component

- _Goal:_ Grid of labelled form fields that emit assembled token fragments.
- _Preconditions:_ I1, I2.
- _Steps:_
  - Create `resources/js/components/forms/search/AdvancedSearchPanel.vue`.
  - Internal state: `AdvancedSearchState` reactive object (all fields including EXIF and `ratingOwn`).
  - Each field change calls `assembleTokens(state, isAuthenticated)` and emits `update:tokens`.
  - Clear button resets all fields and emits `update:tokens` with `""` and also `clear`.
  - Layout: 3-column responsive grid using Tailwind flex/grid; EXIF fields in a clearly labelled sub-section.
  - All inputs use PrimeVue components (`InputText`, `DatePicker`/`Calendar`, `Select`, `InputNumber`).
  - Min. Rating (own) field: rendered only when the user is authenticated (read from the auth store with `v-if`).
  - Expose `parseAndLoad(raw: string)` via `defineExpose` so the parent can push raw input changes back into the fields.
- _Commands:_ `npm run check`
- _Exit:_ Component renders all fields; tokens emitted correctly on change; no TS/lint errors.

### I6 – Refactor `SearchBox` to Compose New Components

- _Goal:_ Wire `SearchInputBar` + `AdvancedSearchPanel` into the existing `SearchBox`, replacing the old single `InputText` + debounce logic.
- _Preconditions:_ I4, I5.
- _Steps:_
  - Rewrite `resources/js/components/forms/search/SearchBox.vue`:
    - Use `SearchInputBar` for simple input.
    - Use `Collapse` (or `v-show`) to show/hide `AdvancedSearchPanel`.
    - On `AdvancedSearchPanel` `update:tokens`: synchronise the advanced token fragment into the combined query string (advanced tokens + any remainder typed manually).
    - On `SearchInputBar` `update:modelValue`: call `parseTokens(raw)` and push `advanced` back into `AdvancedSearchPanel` via `parseAndLoad`.
    - On `search` emit: call `emits('search', combinedQuery)`.
    - On `clear` emit: reset advanced panel and clear combined query.
  - Remove the `useDebounceFn` import and debounce logic.
- _Commands:_ `npm run check`
- _Exit:_ `SearchBox` works end-to-end; no TS/lint errors.

### I7 – Auto-scroll After Search & Store Cleanup

- _Goal:_ Scroll to first result; remove auto-trigger from store.
- _Preconditions:_ I6.
- _Steps:_
  - In `resources/js/stores/SearchState.ts`: remove `useDebounceFn`; verify `search()` is purely called on demand.
  - In `resources/js/views/gallery-panels/Search.vue` (or `ResultPanel.vue`): after `searchStore.search()` resolves (`.then()`), call `nextTick()` then `document.querySelector('[data-search-results]')?.scrollIntoView({ behavior: 'smooth' })` (or assign a template ref).
  - Add `data-search-results` attribute to the outer div of `ResultPanel.vue`.
- _Commands:_ `npm run check`
- _Exit:_ Scroll works in manual test; store no longer has debounce watcher.

### I8 – Quality Gate & Cleanup

- _Goal:_ Full quality gate green before merge.
- _Preconditions:_ I1–I7.
- _Steps:_
  - `npm run format`
  - `npm run check`
  - `php artisan test`
  - `make phpstan`
  - Fix any remaining issues.
  - Update roadmap status to "complete".
- _Exit:_ All checks pass.

## Scenario Tracking

| Scenario ID | Increment(s) | Notes |
|-------------|-------------|-------|
| S-028-01 | I4, I6, I7 | Simple input + Search button flow |
| S-028-02 | I4 | Button disabled below min length |
| S-028-03 | I5, I6 | Advanced-only fields assemble and fire |
| S-028-04 | I5, I6 | Merge of advanced tokens + manual tokens |
| S-028-05 | I2, I6 | Round-trip parse on raw input edit |
| S-028-06 | I5, I6 | Clear resets everything |
| S-028-07 | I4, I6 | Collapsed panel state preserved on search |
| S-028-08 | I7 | No scroll on zero results |
| S-028-09 | I5 | Date validation |
| S-028-10 | I5 | Rating range validation |
| S-028-11 | I4 | Enter key |
| S-028-12 | I2, I5 | Comma-separated tags |
| S-028-13 | I6 | Initial state on navigation |
| S-028-14 | I2, I5 | EXIF sub-section fields (make, iso, etc.) |
| S-028-15 | I2, I5 | rating:own visible only when authenticated |

## Analysis Gate

To be run once increments I1–I7 are drafted:
- [ ] All FRs traceable to at least one increment.
- [ ] All NFRs have a verification command noted.
- [ ] Token assembler round-trip coverage verified in unit tests (I3).
- [ ] No new backend routes introduced.

## Exit Criteria

- [ ] `npm run format` passes.
- [ ] `npm run check` passes (TypeScript + ESLint).
- [ ] Token assembler unit tests all green.
- [ ] `php artisan test` — all tests pass.
- [ ] `make phpstan` — no errors.
- [ ] Manual walkthrough of all 13 scenarios complete.
- [ ] Roadmap entry updated to "Complete".

## Follow-ups / Backlog

- Consider persisting the last search term and advanced panel state in `sessionStorage`.
- Consider adding a colour-picker widget for the `color:` token in advanced mode.

---

*Last updated: 2026-03-13*
