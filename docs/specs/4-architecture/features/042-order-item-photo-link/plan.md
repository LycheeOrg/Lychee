# Feature Plan 042 – Order Item Photo Link

_Linked specification:_ `docs/specs/4-architecture/features/042-order-item-photo-link/spec.md`
_Status:_ Planning
_Last updated:_ 2026-05-31

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Operators and customers looking at an order-download page can immediately navigate to the source gallery of any purchased photo. When a gallery is deleted the UI provides a clear audit trail (photo ID reference) instead of a dead link. When both gallery and photo have been removed the item is visually de-emphasised as a historical record only.

**Success signals:**
- `OrderItemResource` TypeScript type includes `album_exists: boolean` and `photo_exists: boolean` fields.
- All three rendering states (linked / forbidden / ghost) are visible in the browser for an order seeded with each condition.
- No N+1 queries: a 20-item order request hits the DB once for album checks and once for photo checks (eager-load batches).
- All existing PHP and Vue tests remain green; new tests pass for each scenario.

## Scope Alignment

**In scope:**
- `OrderItemResource` — add `album_exists` and `photo_exists` boolean fields (DO-042-01).
- `OrderResource::fromModel()` — eager-load `items.album` and `items.photo` whenever items are loaded.
- `OrderDownload.vue` — replace unconditional `RouterLink` with three-state conditional (UI-042-01 … UI-042-03).
- PHP feature tests for the new resource fields.
- Vue component tests for the three UI states.

**Out of scope:**
- Changes to `OrderList.vue` (order-level table).
- Handling `album_id IS NULL` items beyond the existing ghost/muted fallback.
- Any UI changes on pages other than `OrderDownload.vue`.

## Dependencies & Interfaces

| Dependency | Detail |
|-----------|--------|
| `App\Models\OrderItem` | Has `album()` and `photo()` BelongsTo relations already defined; no model changes needed. |
| `App\Http\Resources\Shop\OrderItemResource` | Spatie Data class — adding fields triggers TypeScript type regeneration. |
| `App\Http\Resources\Shop\OrderResource` | Drives eager-loading strategy; `load('items.size_variant')` pattern to extend. |
| Spatie TypeScript Transformer | `npm run generate-types` (or project equivalent) must be run after PHP resource change. |
| `resources/js/views/webshop/OrderDownload.vue` | UI change; depends on new TypeScript type fields. |

## Assumptions & Risks

**Assumptions:**
- `OrderItem::album()` and `OrderItem::photo()` BelongsTo relations are already defined and return `null` when the referenced entity is deleted (no FK cascade → soft-absent).
- `php artisan test` and `npm run check` are the full test gates; no additional test runners required.
- TypeScript types are regenerated from PHP via a project script (e.g. `php artisan typescript:transform`).

**Risks / Mitigations:**
- _Risk_: `orderResource` is returned from many checkout controller actions; eager-loading `items.album/photo` on all of them could add latency on hot checkout paths.
  _Mitigation_: Load the relations only when `items` are already being loaded (i.e., guard with `$order->relationLoaded('items')`), so non-item-bearing responses are unaffected.
- _Risk_: PHPStan may flag the nullable `album` / `photo` relation access without null guards.
  _Mitigation_: Use `$item->album !== null` and `$item->photo !== null` boolean checks; no direct attribute access on potentially null relations.

## Implementation Drift Gate

After each increment: run `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`, and `npm run check`. Record outcomes in the tasks checklist. If a test turns red and cannot be fixed in the same session, disable it with a `// TODO:` comment and log the follow-up in this plan's Follow-ups section.

## Increment Map

### I1 – Backend: Extend `OrderItemResource` with existence flags

- _Goal:_ Add `album_exists: bool` and `photo_exists: bool` to the Spatie Data resource; update `OrderResource::fromModel()` to eager-load `items.album` and `items.photo` so the flags are set without N+1 queries. Regenerate TypeScript types.
- _Preconditions:_ `OrderItem::album()` and `OrderItem::photo()` BelongsTo relations confirmed present.
- _Steps:_
  1. Write failing unit tests in `OrderItemResourceTest` for all four existence combinations (S-042-01 … S-042-05 backend coverage, NFR-042-01).
  2. Add `public bool $album_exists` and `public bool $photo_exists` constructor params to `OrderItemResource`.
  3. Update `OrderItemResource::fromModel()` to set both flags from the loaded relations: `album_exists: $item->album !== null`, `photo_exists: $item->photo !== null`.
  4. In `OrderResource::fromModel()`, extend the `load('items.size_variant')` call to `load(['items.size_variant', 'items.album', 'items.photo'])`, and add a parallel eager-load when items are already loaded (e.g. for basket responses that call `OrderItemResource::collect()` directly).
  5. Run `php artisan typescript:transform` (or equivalent) to regenerate `lychee.d.ts`.
  6. Run `vendor/bin/php-cs-fixer fix`, `make phpstan`, `php artisan test`.
- _Commands:_ `php artisan test --filter=OrderItemResource`, `make phpstan`, `vendor/bin/php-cs-fixer fix`
- _Exit:_ Tests green; PHPStan 0 errors; `lychee.d.ts` contains `album_exists` and `photo_exists` on `OrderItemResource`.

### I2 – Frontend: Three-state title rendering in `OrderDownload.vue`

- _Goal:_ Replace the unconditional `RouterLink` on the order-item title with a `v-if/v-else-if/v-else` block implementing UI-042-01, UI-042-02, and UI-042-03.
- _Preconditions:_ I1 complete; `OrderItemResource` TypeScript type includes `album_exists` and `photo_exists`.
- _Steps:_
  1. Write Vitest component tests for the three states (linked / forbidden / ghost) in a new test file targeting `OrderDownload.vue` or an extracted `OrderItemTitle.vue` sub-component.
  2. Extract the title cell into a small `OrderItemTitle.vue` component (optional but recommended for testability) that accepts `item: App.Http.Resources.Shop.OrderItemResource` as a prop.
  3. Implement the three conditional blocks:
     - `v-if="item.album_exists"` → `<RouterLink :to="{ name: 'album', params: { albumId: item.album_id, photoId: item.photo_id } }">{{ item.title }}</RouterLink>`
     - `v-else-if="item.photo_exists"` → `<i class="pi pi-ban text-red-600 ltr:mr-1 rtl:ml-1" /><span>{{ item.title }}</span><span class="text-xs text-muted-color ltr:ml-2 rtl:mr-2">{{ item.photo_id }}</span>`
     - `v-else` → `<span class="italic text-muted-color">{{ item.title }}</span>`
  4. Run `npm run format`, `npm run check`.
- _Commands:_ `npm run format`, `npm run check`
- _Exit:_ Vitest tests green; vue-tsc clean; browser smoke-test shows three states for a seeded order.

### I3 – Quality Gates & Roadmap Update

- _Goal:_ Full quality gate pass; update roadmap and knowledge map.
- _Steps:_
  1. `vendor/bin/php-cs-fixer fix`
  2. `npm run format`
  3. `npm run check`
  4. `php artisan test`
  5. `make phpstan`
  6. Update `docs/specs/4-architecture/roadmap.md` — move Feature 042 status to Complete.
  7. Update `docs/specs/4-architecture/knowledge-map.md` — note `OrderItemResource` extension under Shop Implementation.
- _Exit:_ All gates green; roadmap and knowledge map updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-042-01 | I2 / T-042-04 | RouterLink rendered when album exists. |
| S-042-02 | I2 / T-042-04 | RouterLink with null photoId when album exists. |
| S-042-03 | I2 / T-042-05 | Forbidden icon + photo_id when album deleted. |
| S-042-04 | I2 / T-042-06 | Ghost italic when both deleted. |
| S-042-05 | I2 / T-042-06 | Ghost italic when both IDs null. |
| S-042-06 | I1 / T-042-02 | N+1 prevention via eager-load. |

## Analysis Gate

_Not yet run. Gate checklist at [docs/specs/5-operations/analysis-gate-checklist.md](docs/specs/5-operations/analysis-gate-checklist.md) to be executed before I1 implementation begins._

## Exit Criteria

- [ ] `OrderItemResource` TypeScript type includes `album_exists: boolean` and `photo_exists: boolean`.
- [ ] `OrderResource::fromModel()` eager-loads `items.album` and `items.photo` without N+1 queries.
- [ ] `OrderDownload.vue` renders the three states (linked / forbidden / ghost) based on the flags.
- [ ] PHP feature tests cover all four existence combinations.
- [ ] Vue component tests cover all three rendering states.
- [ ] `vendor/bin/php-cs-fixer fix` → no diff.
- [ ] `php artisan test` → all green.
- [ ] `make phpstan` → 0 errors.
- [ ] `npm run check` → passes.
- [ ] Roadmap and knowledge map updated.

## Follow-ups / Backlog

- Consider adding a similar three-state navigation to the `BasketList.vue` / checkout preview for consistency (out of scope for this increment).
- If the `album_id IS NULL` case (album-level purchases without a photo) grows in importance, add a dedicated display state for it in a follow-on feature.
