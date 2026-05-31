# Feature Plan 043 – Photo Title Link in Admin Maintenance Views

_Linked specification:_ `docs/specs/4-architecture/features/043-photo-title-link/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-05-31

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Admin users navigating the duplicate-finder or moderation queue can click a photo's title to jump directly to the album page in a new tab, eliminating the need for a separate link icon. When the album is gone, a clear forbidden indicator replaces the broken link. When all identifiers are absent, a subtle muted style signals an orphaned entry. Success is measured by: TypeScript (`npm run check`) and ESLint (`npm run lint`) both passing, PHPStan passing, and all existing PHP tests continuing to pass.

## Scope Alignment

- **In scope:**
  - New `PhotoTitleLink.vue` component with three render states (UI-043-01, UI-043-02, UI-043-03).
  - Update `DuplicateLine.vue` photo-title cell to use `PhotoTitleLink` (FR-043-04).
  - Update `Moderation.vue` title column to use `PhotoTitleLink` (FR-043-05).
  - Make `Duplicate.album_id` and `Duplicate.album_title` nullable in the PHP resource (FR-043-06).
  - Regenerate TypeScript types after the PHP resource change.

- **Out of scope:**
  - Any changes to `ModerationResource.php` (already nullable — no action needed).
  - Any changes to the duplicate-finder backend query logic.
  - Any other admin or public-facing views.
  - Backend test additions (the `Duplicate` nullability change is a type-only change; existing tests cover the behaviour).

## Dependencies & Interfaces

- `resources/js/components/maintenance/DuplicateLine.vue` — must be updated to remove the standalone `pi-link` icon pattern in the photo column.
- `resources/js/views/admin/Moderation.vue` — must be updated to replace plain title `<td>` text.
- `app/Http/Resources/Models/Duplicates/Duplicate.php` — `album_id` / `album_title` to be made nullable.
- TypeScript transformer (`php artisan typescript:transform`) — run after the PHP change to update `App.Http.Resources.Models.Duplicates.Duplicate`.
- Vue Router — the `RouterLink` in state 1 references the `album` named route with `albumId` and `photoId` params.

## Assumptions & Risks

- **Assumptions:**
  - The `album` named route with `albumId` and `photoId` params exists in Vue Router (verified in `DuplicateLine.vue` which already uses it).
  - `photo_id` is always non-null in `ModerationResource` (photo is present in the database); only `album_id` can be null.
  - For `Duplicate` entries produced by the current query, `album_id` is always non-null in practice; the nullability change is defensive.

- **Risks / Mitigations:**
  - **TypeScript drift:** If the TypeScript transformer is not run after the PHP change, `npm run check` will fail. Mitigation: run transformer in the same increment.
  - **Visual regression in DuplicateLine:** Removing the separate `pi-link` icon for the photo column changes the visual layout slightly. Mitigation: ensure the `PhotoTitleLink` state-1 output still contains a visible link indicator.

## Implementation Drift Gate

After each increment, run the commands listed under that increment's _Exit_ criterion. Record the exit code and any warnings here before proceeding to the next increment. If any command fails, treat the increment as incomplete.

## Increment Map

1. **I1 – Make `Duplicate` album fields nullable**
   - _Goal:_ Update `Duplicate.php` so `album_id` and `album_title` are `?string`; regenerate TypeScript types.
   - _Preconditions:_ None.
   - _Steps:_
     - Change `public string $album_id` → `public ?string $album_id` in `Duplicate`.
     - Change `public string $album_title` → `public ?string $album_title` in `Duplicate`.
     - Update `fromModel()` to handle null values with null-safe operators.
     - Run `php artisan typescript:transform` to regenerate `App.Http.Resources.Models.Duplicates.Duplicate`.
   - _Commands:_ `make phpstan`, `vendor/bin/php-cs-fixer fix --dry-run`, `php artisan test`
   - _Exit:_ PHPStan exits 0, php-cs-fixer exits 0, all PHP tests pass.

2. **I2 – Create `PhotoTitleLink.vue` component**
   - _Goal:_ Implement the reusable three-state component (S-043-01, S-043-02, S-043-03).
   - _Preconditions:_ I1 complete (TypeScript types updated).
   - _Steps:_
     - Create `resources/js/components/maintenance/PhotoTitleLink.vue`.
     - Props: `title: string`, `album_id: string | null`, `photo_id: string | null`.
     - Template branch 1 (`v-if="album_id && photo_id"`): `<RouterLink :to="{ name: 'album', params: { albumId: album_id, photoId: photo_id } }" target="_blank">{{ title }}</RouterLink>`.
     - Template branch 2 (`v-else-if="photo_id"`): `<span class="flex items-center gap-1"><i class="pi pi-ban text-red-600 text-xs"></i><code class="font-mono text-xs">{{ photo_id }}</code></span>`.
     - Template branch 3 (`v-else`): `<span class="italic text-muted-color">{{ title }}</span>`.
   - _Commands:_ `npm run check`, `npm run lint`
   - _Exit:_ TypeScript and ESLint both exit 0.

3. **I3 – Integrate `PhotoTitleLink` in `DuplicateLine.vue`**
   - _Goal:_ Replace the photo-title cell's plain `<span>` + separate icon with `PhotoTitleLink` (FR-043-04, S-043-04).
   - _Preconditions:_ I2 complete.
   - _Steps:_
     - Import `PhotoTitleLink` in `DuplicateLine.vue`.
     - In the photo-title `<div>`, replace the `<router-link>` icon + `<span>` pattern with `<PhotoTitleLink :title="duplicate.photo_title" :album_id="duplicate.album_id ?? null" :photo_id="duplicate.photo_id" />`.
   - _Commands:_ `npm run check`, `npm run lint`
   - _Exit:_ TypeScript and ESLint both exit 0.

4. **I4 – Integrate `PhotoTitleLink` in `Moderation.vue`**
   - _Goal:_ Replace the title column plain text with `PhotoTitleLink` (FR-043-05, S-043-05, S-043-06).
   - _Preconditions:_ I2 complete.
   - _Steps:_
     - Import `PhotoTitleLink` in `Moderation.vue`.
     - In the title `<td>`, replace `{{ photo.title }}` with `<PhotoTitleLink :title="photo.title" :album_id="photo.album_id ?? null" :photo_id="photo.photo_id" />`.
   - _Commands:_ `npm run check`, `npm run lint`
   - _Exit:_ TypeScript and ESLint both exit 0.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-043-01 | I2 / T-043-02 | Implemented in `PhotoTitleLink` state-1 branch. |
| S-043-02 | I2 / T-043-02 | Implemented in `PhotoTitleLink` state-2 branch. |
| S-043-03 | I2 / T-043-02 | Implemented in `PhotoTitleLink` state-3 branch. |
| S-043-04 | I3 / T-043-03 | `DuplicateLine.vue` integration. |
| S-043-05 | I4 / T-043-04 | `Moderation.vue` unsorted-photo case. |
| S-043-06 | I4 / T-043-04 | `Moderation.vue` album-present case. |
| S-043-07 | I1 / T-043-01 | `Duplicate` PHP resource nullability. |

## Analysis Gate

Not yet started. Record findings here before implementation begins.

## Exit Criteria

- [ ] `make phpstan` exits 0 (after I1).
- [ ] `vendor/bin/php-cs-fixer fix --dry-run` exits 0 (after I1).
- [ ] `php artisan test` exits 0 (after I1).
- [ ] `npm run check` exits 0 (after I2/I3/I4).
- [ ] `npm run lint` exits 0 (after I2/I3/I4).
- [ ] All three `PhotoTitleLink` render states are covered in the component template.
- [ ] Both `DuplicateLine.vue` and `Moderation.vue` use `PhotoTitleLink` for the photo-title cell.

## Follow-ups / Backlog

- Consider adding a Vitest/Vue Test Utils test for each `PhotoTitleLink` render state once a frontend unit test infrastructure is established.
- Evaluate whether the album-column link in `DuplicateLine.vue` (separate from the photo-title link) should also be migrated to a similar component for consistency.
