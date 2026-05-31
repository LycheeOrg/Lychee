# Feature 041 Tasks – Upload Photo Metadata

_Status: Complete_  
_Last updated: 2026-05-31_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`N-`), and scenario IDs (`S-041-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md) and treat a task as fully resolved only once the governing spec sections reflect the clarified behaviour.

---

## Increment I1 – DTO Chain & Core Model

- [x] T-041-01 – Write failing feature-test stubs for S-041-01, S-041-02, S-041-03, S-041-05, S-041-06, S-041-07 (FR-041-01 through FR-041-10, S-041-01 to S-041-07).  
  _Intent:_ Create `tests/Feature_v2/Photo/UploadWithMetadataTest.php` extending `BaseApiWithDataTest`. Add test methods for each scenario listed; each assertion should fail until implementation is in place. Scenarios S-041-08 and S-041-09 (validation) are added in T-041-07/T-041-08.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest` (expect failures — confirms stubs run)  
  _Notes:_ Use `DatabaseTransactions` trait. Follow `BaseApiWithDataTest` conventions from `tests/Feature_v2/`.

- [x] T-041-02 – Add `title`, `description`, `preallocated_id` to `ImportParam` (FR-041-01, FR-041-02, FR-041-07).  
  _Intent:_ Add three nullable `?string` properties with default `null` to `App\DTO\ImportParam`. Constructor must remain backward-compatible (named parameters or defaults).  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ No tests needed at this layer alone; covered by T-041-01 integration tests.

- [x] T-041-03 – Propagate new fields through `InitDTO` and `StandaloneDTO::ofInit()` (FR-041-07, S-041-05).  
  _Intent:_ Add `?string $title`, `?string $description`, `?string $preallocated_id` to `InitDTO`. In `StandaloneDTO::ofInit()`, copy the three fields from `InitDTO`; also add them as constructor parameters to `StandaloneDTO`.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ `StandaloneDTO` constructor changes must be backward-compatible (nullable + default null).

- [x] T-041-04 – Add `Photo::preallocateId()` and guard in `generateKey()` (FR-041-07, FR-041-08, S-041-05).  
  _Intent:_ Add `preallocateId(string $id): void` to `App\Models\Photo` that writes directly to `$this->attributes[$this->getKeyName()]`. Modify `generateKey()` in `HasRandomIDAndLegacyTimeBasedID` to use the pre-existing attribute value when set (and clear it after use to prevent accidental reuse). Add a unit test in `tests/Unit/` (or `tests/Feature_v2/`) confirming that after `preallocateId('abc123…')`, the photo is saved with that ID.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest`  
  - `make phpstan`  
  _Notes:_ `preallocateId` bypasses the `setAttribute` guard deliberately; document with an inline comment.

---

## Increment I2 – New Pipe & AutoRenamer Guard

- [x] T-041-05 – Create `ApplyUserProvidedMetadata` pipe with unit test (FR-041-03, FR-041-04, S-041-01, S-041-11).  
  _Intent:_ Write a unit test first: (a) when `$state->title` is non-null it is assigned to `$state->photo->title`; (b) when `$state->title` is null the photo title is unchanged; (c) same for `description`. Then create `app/Actions/Photo/Pipes/Standalone/ApplyUserProvidedMetadata.php` implementing `StandalonePipe`.  
  _Verification commands:_  
  - `php artisan test --filter=ApplyUserProvidedMetadataTest`  
  - `make phpstan`  
  _Notes:_ Implement as `StandalonePipe` only (Q-041-03 resolved). For duplicate uploads the pipe never runs; user-supplied `title`/`description` are discarded (duplicate keeps its existing values).

- [x] T-041-06 – Insert `ApplyUserProvidedMetadata` before `HydrateMetadata`; add `AutoRenamer` guard (FR-041-03, FR-041-04, FR-041-06, S-041-01, S-041-11).  
  _Intent:_ In `App\Actions\Photo\Create::handleStandalone()` and `handlePhotoLivePartner()`, insert `Standalone\ApplyUserProvidedMetadata::class` immediately before `Shared\HydrateMetadata::class`. In `AutoRenamer::handle()`, add `if ($state->title !== null) { return $next($state); }` at the top.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest`  
  - `make phpstan`  
  _Notes:_ Confirm S-041-01 (title override) and S-041-11 (renamer skip) tests now pass.

---

## Increment I3 – Request, Resource & Controller

- [x] T-041-07 – Add `title` validation to `UploadPhotoRequest`; test S-041-08 (FR-041-01, S-041-08).  
  _Intent:_ Add rule `RequestAttribute::TITLE_ATTRIBUTE => ['sometimes', 'nullable', new TitleRule()]` to `UploadPhotoRequest::rules()`. Add `title()` accessor. Confirm S-041-08 test (> 100 chars → 422) passes.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest`  
  - `make phpstan`

- [x] T-041-08 – Add `description` validation to `UploadPhotoRequest`; test S-041-09 (FR-041-02, S-041-09).  
  _Intent:_ Add rule `RequestAttribute::DESCRIPTION_ATTRIBUTE => ['sometimes', 'nullable', new DescriptionRule()]` to `UploadPhotoRequest::rules()`. Add `description()` accessor. Confirm S-041-09 test (> 1 000 chars → 422) passes.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest`  
  - `make phpstan`

- [x] T-041-09 – Add `expected_id`, `title`, `description` to `UploadMetaResource` (FR-041-11, DO-041-01, S-041-04, S-041-07).  
  _Intent:_ Add `public ?string $expected_id = null`, `public ?string $title = null`, `public ?string $description = null` to `App\Http\Resources\Editable\UploadMetaResource`. Spatie Data will expose them in the JSON response automatically.  
  _Verification commands:_  
  - `make phpstan`  
  _Notes:_ Existing constructor parameters remain unchanged; new fields use property declarations with defaults.

- [x] T-041-10 – Update `PhotoController::upload()` and `process()` to generate `expected_id` and forward `title`/`description` (FR-041-07, FR-041-10, S-041-04, S-041-05, S-041-07).  
  _Intent:_ In `PhotoController::upload()`, on the final chunk generate `expected_id` using the same Base64url algorithm as `generateKey()` (24 chars) and assign to `$meta->expected_id`. Set `$meta->title` and `$meta->description` from `$request->title()` / `$request->description()`. Forward all three to `process()`. For zip uploads, leave `expected_id` as `null`. In `process()`, pass `expected_id`, `title`, `description` to `ProcessImageJob::dispatch(...)`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest`  
  - `make phpstan`  
  _Notes:_ Confirm S-041-04 (`expected_id` present), S-041-05 (matches stored `id`), S-041-07 (zip → null) tests pass.

- [x] T-041-11 – Update `ProcessImageJob` to carry and use `expected_id`, `title`, `description` (FR-041-07, DO-041-05).  
  _Intent:_ Add `public ?string $expected_id`, `public ?string $title`, `public ?string $description` to `ProcessImageJob`. Accept them in the constructor; pass through `ImportParam` in `handle()`.  
  _Verification commands:_  
  - `php artisan test --filter=UploadWithMetadataTest`  
  - `make phpstan`  
  _Notes:_ Laravel queue serialises plain PHP scalars; no custom cast needed.

---

## Increment I5 – Quality Gates & Docs

- [x] T-041-14 – Full quality gate and documentation update (NFR-041-01 through NFR-041-04).  
  _Intent:_ Run full pipeline; update knowledge map, roadmap, and session file.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `php artisan test`  
  - `make phpstan`  
  _Steps:_  
  1. Run `vendor/bin/php-cs-fixer fix` — apply any style fixes.  
  2. Run `php artisan test` — all tests green.  
  3. Run `make phpstan` — 0 errors.  
  4. Update `docs/specs/4-architecture/knowledge-map.md` — add entries for `ApplyUserProvidedMetadata` pipe and the three new DTO fields.  
  5. Update `docs/specs/4-architecture/roadmap.md` — move Feature 041 from Active to Completed (or update progress field).  
  6. Update `docs/specs/_current-session.md` — record final state.  
  7. Mark all previous tasks `[x]`.  
  _Notes:_ Do not proceed if any gate step fails — fix the issue in the appropriate earlier task first.

---

## Notes / TODOs

- T-041-04: If `preallocateId()` interacts poorly with Eloquent dirty-tracking, a plain property (`protected ?string $preallocated_id_override`) could be used instead of writing directly to `$this->attributes`; assess during implementation and log a follow-up in the plan if needed.
