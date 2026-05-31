# Feature 043 Tasks – Photo Title Link in Admin Maintenance Views

_Status: Draft_  
_Last updated: 2026-05-31_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs, and scenario IDs (`S-043-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/5-decisions/` reflect the clarified behaviour.

## Checklist

- [ ] T-043-01 – Make `Duplicate.album_id` and `Duplicate.album_title` nullable (FR-043-06, S-043-07).  
  _Intent:_ Update `app/Http/Resources/Models/Duplicates/Duplicate.php` to declare `$album_id` and `$album_title` as `?string`; update `fromModel()` to propagate null values; regenerate TypeScript types with `php artisan typescript:transform`.  
  _Verification commands:_  
  - `make phpstan`  
  - `vendor/bin/php-cs-fixer fix --dry-run`  
  - `php artisan test`  
  - `npm run check` (after TypeScript regeneration)  
  _Notes:_ `ModerationResource` already has nullable fields — no change needed there.

- [ ] T-043-02 – Create `resources/js/components/maintenance/PhotoTitleLink.vue` (FR-043-01, FR-043-02, FR-043-03, S-043-01, S-043-02, S-043-03).  
  _Intent:_ Implement the three-state component with props `title: string`, `album_id: string | null`, `photo_id: string | null`. State 1: `RouterLink` with title text. State 2: red `pi-ban` icon + `photo_id` in `<code class="font-mono">`. State 3: `<span class="italic text-muted-color">` with title text.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run lint`  
  _Notes:_ See UI mock-ups in `spec.md` for expected HTML structure per state.

- [ ] T-043-03 – Update `DuplicateLine.vue` to use `PhotoTitleLink` for the photo-title column (FR-043-04, S-043-04).  
  _Intent:_ Remove the `<router-link>` icon + `<span>` pattern in the photo-title `<div>` of `DuplicateLine.vue`; replace with `<PhotoTitleLink :title="duplicate.photo_title" :album_id="duplicate.album_id ?? null" :photo_id="duplicate.photo_id" />`. Add the import for `PhotoTitleLink`.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run lint`  
  _Notes:_ The album-title column (leftmost) retains its own `<router-link>` icon and is not changed by this task.

- [ ] T-043-04 – Update `Moderation.vue` to use `PhotoTitleLink` for the title column (FR-043-05, S-043-05, S-043-06).  
  _Intent:_ In `Moderation.vue`, replace the plain `{{ photo.title }}` in the `col_title` `<td>` with `<PhotoTitleLink :title="photo.title" :album_id="photo.album_id ?? null" :photo_id="photo.photo_id" />`. Add the import for `PhotoTitleLink`.  
  _Verification commands:_  
  - `npm run check`  
  - `npm run lint`  
  _Notes:_ For unsorted photos (`album_id: null`), the component will render state 2 (forbidden icon + photo_id), matching S-043-05.

## Notes / TODOs

- TypeScript regeneration (`php artisan typescript:transform`) must be run as part of T-043-01 to keep `App.Http.Resources.Models.Duplicates.Duplicate` in sync with the PHP resource changes; without it, T-043-02 will fail type-checking.
- If a frontend unit test infrastructure (Vitest + Vue Test Utils) is added in a later feature, add a follow-up task to cover the three `PhotoTitleLink` render states.
