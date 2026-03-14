# Feature 028 Tasks – Search UI Refactor

_Status: Complete_  
_Last updated: 2026-05-30_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification — do not batch completions. Update the roadmap status when all tasks are done.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../../open-questions.md) instead of informal notes.

## Checklist

### I1 · Translation Keys

- [x] T-028-01 – Add advanced-search translation keys to `lang/en/gallery.php` (FR-028-05, S-028-03).  
  _Intent:_ Register all label strings under the `search.advanced` sub-group so no copy is hardcoded in Vue.  
  Keys to add: `search.advanced.title`, `search.advanced.description`, `search.advanced.location`, `search.advanced.tags`, `search.advanced.date_from`, `search.advanced.date_to`, `search.advanced.type`, `search.advanced.orientation`, `search.advanced.rating_min`, `search.advanced.rating_own`, `search.advanced.clear`, `search.advanced.search_button`, `search.advanced.toggle_advanced`, `search.advanced.any`.  
  Also add: `search.advanced.type_image`, `search.advanced.type_video`, `search.advanced.type_raw`, `search.advanced.type_live`, `search.advanced.orientation_landscape`, `search.advanced.orientation_portrait`, `search.advanced.orientation_square`.  
  EXIF sub-section keys: `search.advanced.exif`, `search.advanced.make`, `search.advanced.model`, `search.advanced.lens`, `search.advanced.aperture`, `search.advanced.shutter`, `search.advanced.focal`, `search.advanced.iso`.  
  _Verification commands:_  
  - `php artisan typescript:transform` (or equivalent regeneration command — verify key is available in TS types)  
  - `npm run check`  
  _Notes:_ Only modify `lang/en/gallery.php`. Do **not** touch `lang/php_*.json` files (auto-generated).

---

### I2 · Token Assembler Composable

- [x] T-028-02 – Create `resources/js/composables/useSearchTokenAssembler.ts` with the `AdvancedSearchState` interface and `assembleTokens` function (FR-028-05, FR-028-06, FR-028-10, FR-028-11, FR-028-12, FR-028-13, FR-028-14, FR-028-16, FR-028-17, S-028-03, S-028-12, S-028-14, S-028-15).  
  _Intent:_ Pure function: `assembleTokens(state: AdvancedSearchState, isAuthenticated: boolean) → string`. No Vue reactivity in this file.  
  Assembly rules (per spec.md §Token Assembly Contract):  
  - title → `title:value` (quote if contains space)  
  - description → `description:value`  
  - location → `location:value`  
  - tags (comma-separated) → one `tag:name` per segment  
  - dateFrom → `date:>=YYYY-MM-DD`  
  - dateTo → `date:<=YYYY-MM-DD`  
  - type → `type:value`  
  - orientation → `ratio:value`  
  - ratingMin → `rating:avg:>=:N`  
  - ratingOwn → `rating:own:>=:N` (only when `isAuthenticated === true`)  
  - make → `make:value`  
  - model → `model:value`  
  - lens → `lens:value`  
  - aperture → `aperture:value`  
  - shutter → `shutter:value`  
  - focal → `focal:value`  
  - iso → `iso:value`  
  Empty/blank fields emit no token.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Export as a named function (`export function assembleTokens`), not a composable hook.

- [x] T-028-03 – Add `parseTokens(raw: string): { advanced: AdvancedSearchState; remainder: string }` to the same file (FR-028-07, S-028-05).  
  _Intent:_ Parse known token prefixes out of a raw string, populate `AdvancedSearchState`, return unparsed remainder.  
  Tokens to parse: `title:`, `description:`, `location:`, `tag:` (accumulate into comma list), `date:>=`, `date:<=`, `type:`, `ratio:`, `rating:avg:>=:`, `rating:own:>=:`, `make:`, `model:`, `lens:`, `aperture:`, `shutter:`, `focal:`, `iso:`.  
  Unrecognised tokens go to `remainder`.  
  _Verification commands:_  
  - `npm run check`

---

### I3 · Token Assembler Unit Tests

- [x] T-028-04 – Create `resources/js/__tests__/search/tokenAssembler.test.ts` with tests for `assembleTokens` (NFR-028-04, S-028-03, S-028-04, S-028-12, S-028-14, S-028-15).  
  _Intent:_ Cover every assembly rule with isolated tests; verify empty fields are omitted; verify comma-tag splitting; verify quoted multi-word title; verify EXIF fields; verify `ratingOwn` gating.  
  Test cases (minimum):  
  1. All fields empty → empty string.  
  2. `title: "sunset"` → `title:sunset`.  
  3. `title: "my photo"` (contains space) → `title:"my photo"`.  
  4. `tags: "sunset, beach, "` → `tag:sunset tag:beach` (trailing empty segment ignored).  
  5. `dateFrom: "2024-01-01"` → `date:>=2024-01-01`.  
  6. `dateTo: "2024-12-31"` → `date:<=2024-12-31`.  
  7. `type: "video"` → `type:video`.  
  8. `orientation: "landscape"` → `ratio:landscape`.  
  9. `ratingMin: "3"` → `rating:avg:>=:3`.  
  10. `ratingOwn: "4"`, `isAuthenticated: true` → `rating:own:>=:4`.  
  11. `ratingOwn: "4"`, `isAuthenticated: false` → token NOT emitted.  
  12. `make: "Canon"`, `iso: "400"` → `make:Canon iso:400`.  
  13. All EXIF fields filled → all seven tokens emitted.  
  14. Multiple fields combined → tokens space-concatenated.  
  _Verification commands:_  
  - `npm run check`

- [x] T-028-05 – Add tests for `parseTokens` round-trip (FR-028-07, S-028-05).  
  _Intent:_ Verify that a raw string assembled by `assembleTokens` can be parsed back to the same `AdvancedSearchState` with empty remainder.  
  Test cases (minimum):  
  1. Raw string of a single known token → correct field populated; remainder empty.  
  2. Raw string with `title:sunset date:>=2024-01-01` → both fields populated; remainder empty.  
  3. Raw string with unknown token `foo:bar` → remainder = `"foo:bar"`, advanced fields empty.  
  4. Mixed known + unknown → known fields populated; unknown in remainder.  
  5. `tag:sunset tag:beach` → `tags: "sunset, beach"`, remainder empty.  
  _Verification commands:_  
  - `npm run check`

---

### I4 · `SearchInputBar` Component

- [x] T-028-06 – Create `resources/js/components/forms/search/SearchInputBar.vue` (FR-028-01, FR-028-02, FR-028-03, FR-028-04, FR-028-09, S-028-01, S-028-02, S-028-11).  
  _Intent:_ Single-line input + Search button + ▼/▲ toggle. No debounce. No auto-fire.  
  Props:  
  - `modelValue: string` — current query string  
  - `minLength: number` — from `searchStore.config.search_minimum_length`  
  - `advancedOpen: boolean` — controls chevron icon direction  
  Emits:  
  - `update:modelValue` — on every keypress  
  - `search` — on button click or Enter key press in input  
  - `clear` — when input is cleared  
  - `update:advancedOpen` — on chevron button click  
  Button disabled when `modelValue === "" || modelValue.length < minLength`.  
  Minimum-length hint text shown below input when applicable.  
  Chevron icon: `pi pi-chevron-down` / `pi pi-chevron-up` (PrimeVue icons).  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Does NOT import `useDebounceFn`. Does NOT call `searchStore` directly.

---

### I5 · `AdvancedSearchPanel` Component

- [x] T-028-07 – Create `resources/js/components/forms/search/AdvancedSearchPanel.vue` — structure and layout (FR-028-05, FR-028-16, FR-028-17, S-028-03, S-028-09, S-028-10, S-028-14, S-028-15).  
  _Intent:_ Responsive grid of labelled form fields using PrimeVue components and Tailwind utilities.  
  Fields and PrimeVue components:  
  - Title, Description, Location → `InputText`  
  - Tags → `InputText` (placeholder: "sunset, beach")  
  - Date from, Date to → `DatePicker` / `Calendar` (format `YYYY-MM-DD`); validate correct date  
  - Type → `Select`/`Dropdown` (options: Any / Image / Video / Raw / Live)  
  - Orientation → `Select`/`Dropdown` (options: Any / Landscape / Portrait / Square)  
  - Min. Rating (avg) → `InputNumber` (min 0, max 5, integer only); show error if out of range  
  - Min. Rating (own) → `InputNumber` (min 0, max 5, integer only); **rendered only when user is authenticated** (`v-if="authStore.isAuthenticated"` or equivalent); show error if out of range  
  - EXIF sub-section (separate visual group):  
    - Make, Model, Lens, Aperture, Shutter, Focal length, ISO → `InputText` each  
  - Clear button in panel header.  
  _Verification commands:_  
  - `npm run check`

- [x] T-028-08 – Wire token emission and `parseAndLoad` in `AdvancedSearchPanel.vue` (FR-028-05, FR-028-06, FR-028-07, FR-028-16, FR-028-17, S-028-04, S-028-05, S-028-06, S-028-14, S-028-15).  
  _Intent:_ On any field change, call `assembleTokens(state, isAuthenticated)` and emit `update:tokens`. Expose `parseAndLoad(raw: string)` via `defineExpose` so the parent can sync from raw input. On Clear, reset state and emit `update:tokens` with `""` and emit `clear`.  
  Emits:  
  - `update:tokens: string` — assembled token fragment from advanced fields only  
  - `clear` — when Clear button clicked  
  _Verification commands:_  
  - `npm run check`

---

### I6 · Refactor `SearchBox` to Compose New Components

- [x] T-028-09 – Rewrite `resources/js/components/forms/search/SearchBox.vue` to compose `SearchInputBar` + `AdvancedSearchPanel` (FR-028-01 through FR-028-15, S-028-01 through S-028-07, S-028-13).  
  _Intent:_ Replace the current single-`InputText` + debounce implementation.  
  Logic:  
  - Internal state: `advancedOpen: Ref<boolean>` (starts `false`), `rawInput: Ref<string>`, `advancedTokens: Ref<string>`.  
  - Combined query = merge of `advancedTokens` + `remainder` from `parseTokens(rawInput)`, or just `rawInput` if advanced panel is closed.  
  - On `SearchInputBar` `update:modelValue`: call `parseTokens(value)`; push `advanced` into `advancedPanel.parseAndLoad(value)`; keep `remainder` separately; update `rawInput`.  
  - On `AdvancedSearchPanel` `update:tokens`: update `advancedTokens`; recompute the displayed `rawInput` as `advancedTokens + " " + remainder`.  
  - On `search` emit from `SearchInputBar`: emit `search` with combined query.  
  - On `clear` from either child: reset everything; emit `clear`.  
  - `Collapse` wraps `AdvancedSearchPanel`; bound to `advancedOpen`.  
  Remove all uses of `useDebounceFn`.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ `SearchPanel.vue` and `Search.vue` (parents) are NOT changed in this task — they already receive `search` and `clear` emits from `SearchBox`.

---

### I7 · Auto-scroll After Search & Store Cleanup

- [x] T-028-10 – Remove debounce auto-trigger from `resources/js/stores/SearchState.ts` (FR-028-03, NFR-028-03, S-028-01).  
  _Intent:_ Ensure `search()` is only called on explicit user action. Remove any watcher or debounced timer that auto-fires on `searchTerm` change.  
  _Verification commands:_  
  - `npm run check`  
  _Notes:_ Confirm `searchStore.search()` is only called from `SearchBox` (via `SearchPanel` → `Search.vue` `@search` handler).

- [x] T-028-11 – Add `data-search-results` attribute to `ResultPanel.vue` root element and implement auto-scroll in `Search.vue` (FR-028-08, S-028-01, S-028-08).  
  _Intent:_ After search completes, scroll the result container into view. Zero-results case: no scroll.  
  Implementation in `Search.vue`:  
  ```ts
  function onSearch(terms: string) {
      searchStore.search(terms).then(() => {
          if (searchStore.total > 0) {
              nextTick(() => {
                  document.querySelector('[data-search-results]')
                      ?.scrollIntoView({ behavior: 'smooth' });
              });
          }
      });
  }
  ```  
  Pass `@search="onSearch"` on `<SearchPanel>`.  
  _Verification commands:_  
  - `npm run check`  
  - Manual test: search returns results → page scrolls.  
  - Manual test: search returns zero results → no scroll.

---

### I8 · Quality Gate

- [x] T-028-12 – Run full quality gate and fix any issues (NFR-028-01 through NFR-028-07).  
  _Intent:_ All checks green before feature is declared complete.  
  _Verification commands:_  
  - `npm run format`  
  - `npm run check`  
  - `php artisan test`  
  - `make phpstan`  
  _Notes:_ Fix any TS, lint, PHPStan, or test failures found. Do not suppress warnings with `@ts-ignore` unless there is no alternative — document any suppression.

- [x] T-028-13 – Manual walkthrough of all 15 scenarios from spec §Branch & Scenario Matrix (S-028-01 through S-028-15).  
  _Intent:_ Confirm each scenario behaves as specified in both light and dark mode.  
  _Verification commands:_ Manual browser test.  
  _Notes:_ Record any deviations as follow-up tasks or open questions.

- [x] T-028-14 – Update roadmap status for Feature 028 to "Complete".  
  _Intent:_ Keep `docs/specs/4-architecture/roadmap.md` accurate.  
  _Verification commands:_ — (documentation only)

## Notes / TODOs

- If `rating:own:` field is rendered, confirm which auth store/composable is used in the project to check authentication state before implementing T-028-07.
- If `DatePicker` is not the correct PrimeVue component name in the version used by the project, verify the exact component name before I5 (T-028-07). Check `resources/js/` for existing usages.
- The `Collapse` component used in `Search.vue` should be verified to accept any slot content before I6.
- The `rating:own:` token is intentionally excluded from the advanced panel; if authentication state is readily available it may be added as a post-feature follow-up.
- If `lang/en/gallery.php` already has translation key generation that strips hierarchy, verify the correct nesting depth for the `search.advanced.*` keys.

---

*Last updated: 2026-03-13*
