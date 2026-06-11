# Feature Plan 041 – Upload Photo Metadata

_Linked specification:_ `docs/specs/4-architecture/features/041-upload-photo-metadata/spec.md`  
_Status:_ Planning  
_Last updated:_ 2026-05-31

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](docs/specs/4-architecture/open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Uploading a photo to Lychee is a single atomic action from the user's perspective.  Post-upload metadata edits are friction.  This plan eliminates that friction by wiring `title`, `description`, and `expected_id` directly into the upload API and UI in the smallest possible footprint — touching only the DTO chain, one new pipe, the request/resource classes, the job, and the frontend upload component.

**Success signals:**
- `Photo.title` and `Photo.description` reflect user-supplied values without a second API call (S-041-01).
- `UploadMetaResource.expected_id` in the final-chunk response matches the saved `Photo.id` (S-041-05).
- All 11 functional requirements (FR-041-01 through FR-041-11) verified by passing tests.
- Quality gate: PHPStan 0, php-cs-fixer clean, all tests green.

## Scope Alignment

**In scope:**
- `UploadMetaResource` — three new nullable fields.
- `UploadPhotoRequest` — two new optional validation rules + accessor methods.
- `PhotoController::upload()` / `process()` — generate `expected_id`, forward `title`/`description`.
- `ProcessImageJob` — three new serialisable properties.
- `ImportParam` / `InitDTO` / `StandaloneDTO` — three new optional fields propagated through the DTO chain.
- `Photo` model + `HasRandomIDAndLegacyTimeBasedID` trait — `preallocateId()` helper and `generateKey()` guard.
- New pipe `Standalone\ApplyUserProvidedMetadata` (inserted before `HydrateMetadata`).
- `AutoRenamer` pipe guard — skip when user-supplied `title` is non-null.
- Feature test `UploadWithMetadataTest.php` covering S-041-01 through S-041-09, S-041-11.

**Out of scope:**
- Tags, license, rating, or taken-at at upload time.
- `expected_id` for zip or from-URL imports.
- From-URL upload changes.
- OpenAPI automation / snapshot testing.
- Any UI / frontend changes (TypeScript types, Vue components, upload panel).

## Dependencies & Interfaces

| Dependency | Notes |
|-----------|-------|
| `App\Http\Requests\Photo\UploadPhotoRequest` | Entry point for new fields |
| `App\Http\Resources\Editable\UploadMetaResource` | Response DTO — gains 3 fields |
| `App\Jobs\ProcessImageJob` | Must carry new fields through queue serialisation |
| `App\DTO\ImportParam` / `InitDTO` / `StandaloneDTO` | DTO chain propagation |
| `App\Models\Photo` + `HasRandomIDAndLegacyTimeBasedID` | ID pre-allocation |
| `App\Actions\Photo\Pipes\Shared\HydrateMetadata` | Must run *after* `ApplyUserProvidedMetadata` |
| `App\Actions\Photo\Pipes\Standalone\AutoRenamer` | Guard condition added |

## Assumptions & Risks

**Assumptions:**
- `UploadMetaResource` is a Spatie Data object; adding nullable fields with defaults is non-breaking.
- `ProcessImageJob` properties are serialised by Laravel's queue serialiser; plain PHP types (`?string`) serialise correctly without custom casts.
- `HasRandomIDAndLegacyTimeBasedID::generateKey()` is only called from `performInsert`, making the pre-allocation guard safe.

**Risks / Mitigations:**
- *DB collision on pre-allocated ID:* Retry loop in `performInsert` handles this; stored `id` may differ from `expected_id` — acceptable per FR-041-08.
- *DTO chain complexity:* Each DTO gets 2–3 new nullable fields; strictly additive, no breaking changes to existing constructors.
- *Frontend TS type drift:* Manual update of the TS type until the Spatie TypeScript transformer is run — include in quality gate.

## Implementation Drift Gate

After all tasks are complete:
1. Run `php artisan test` — all tests green.
2. Run `make phpstan` — 0 errors.
3. Run `vendor/bin/php-cs-fixer fix --dry-run` — no diff.
4. Cross-check every FR/NFR against a passing test or explicit code path.
5. Record findings in the "Analysis Gate" section below.

## Increment Map

### I1 – DTO Chain & Core Model (≤ 60 min)
- _Goal:_ Wire `title`, `description`, and `preallocated_id` through `ImportParam → InitDTO → StandaloneDTO`; add `preallocateId()` to `Photo` model.
- _Preconditions:_ Spec agreed and no open questions.
- _Steps (tests first):_
  1. Write `UploadWithMetadataTest` test stubs (failing) for S-041-01, S-041-02, S-041-03, S-041-05, S-041-06, S-041-07, S-041-08, S-041-09.
  2. Add nullable `?string $title`, `?string $description`, `?string $preallocated_id` to `ImportParam`.
  3. Propagate to `InitDTO` constructor + field declarations.
  4. Propagate to `StandaloneDTO::ofInit()` + field declarations; call `Photo::preallocateId()` when non-null.
  5. Add `preallocateId(string $id): void` to `Photo` (writes to `$this->attributes[...]` directly); update `generateKey()` to use it when set.
- _Commands:_ `php artisan test --filter=UploadWithMetadataTest`, `make phpstan`
- _Exit:_ DTOs propagate fields; `Photo` model test for pre-allocation passes; PHPStan clean.

### I2 – New Pipe & AutoRenamer Guard (≤ 45 min)
- _Goal:_ Add `ApplyUserProvidedMetadata` pipe; guard `AutoRenamer`.
- _Preconditions:_ I1 complete.
- _Steps (tests first):_
  1. Add unit test for `ApplyUserProvidedMetadata` (assign when non-null; no-op when null).
  2. Create `app/Actions/Photo/Pipes/Standalone/ApplyUserProvidedMetadata.php` implementing `StandalonePipe`.
  3. Insert it before `HydrateMetadata` in `Create::handleStandalone()` and `Create::handlePhotoLivePartner()`.
  4. Add guard to `AutoRenamer::handle()`: return early if `$state->title !== null`.
- _Commands:_ `php artisan test --filter=UploadWithMetadataTest`, `make phpstan`
- _Exit:_ S-041-01 and S-041-11 tests pass; PHPStan clean.

### I3 – Request, Resource & Controller (≤ 45 min)
- _Goal:_ Expose `title`, `description`, `expected_id` at the HTTP layer.
- _Preconditions:_ I1, I2 complete.
- _Steps (tests first):_
  1. Confirm S-041-08, S-041-09 (validation) tests are in place.
  2. Add `?string $expected_id`, `?string $title`, `?string $description` (nullable, default `null`) to `UploadMetaResource`.
  3. Add `title` (sometimes|nullable|TitleRule) and `description` (sometimes|nullable|DescriptionRule) rules to `UploadPhotoRequest`; add accessor methods.
  4. In `PhotoController::upload()`: generate `expected_id` on final chunk; set `meta->expected_id`/`title`/`description`.
  5. Forward `title`, `description`, `expected_id` into `process()` → `ProcessImageJob`.
  6. In `ProcessImageJob::__construct()`: store as properties; in `handle()`: pass through `ImportParam`.
- _Commands:_ `php artisan test --filter=UploadWithMetadataTest`, `make phpstan`
- _Exit:_ S-041-04, S-041-07, S-041-08, S-041-09 pass; PHPStan clean.

### I5 – Quality Gate & Docs (≤ 30 min)
- _Goal:_ Full pipeline green; documentation updated.
- _Preconditions:_ I1–I3 complete.
- _Steps:_
  1. `vendor/bin/php-cs-fixer fix`
  2. `php artisan test` — all green.
  3. `make phpstan` — 0 errors.
  4. Update `docs/specs/4-architecture/knowledge-map.md`.
  5. Update `docs/specs/4-architecture/roadmap.md` — move 041 to Completed (or update progress).
  6. Update `docs/specs/_current-session.md`.
  7. Mark all tasks `[x]` in `tasks.md`.
- _Commands:_ (see quality gate above)
- _Exit:_ All gates pass; docs updated; feature complete.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-041-01 | I1, I2 / T-041-01, T-041-05 | Title + description override EXIF |
| S-041-02 | I1 / T-041-01 | No title → EXIF fallback |
| S-041-03 | I1 / T-041-01 | No description → EXIF fallback |
| S-041-04 | I3 / T-041-09 | `expected_id` in response |
| S-041-05 | I1, I3 / T-041-03, T-041-09 | Stored `id` matches `expected_id` |
| S-041-06 | I3 / T-041-09 | Duplicate case — `expected_id` present but mismatches |
| S-041-07 | I3 / T-041-09 | Zip → `expected_id` null |
| S-041-08 | I3 / T-041-07 | `title` > 100 chars → 422 |
| S-041-09 | I3 / T-041-08 | `description` > 1000 chars → 422 |
| S-041-11 | I2 / T-041-05 | AutoRenamer skipped when title supplied |

## Analysis Gate

_To be completed after spec, plan, and tasks are drafted and before implementation begins._

| Check | Result |
|-------|--------|
| Specification completeness | ✅ All FR/NFR populated; resolved answers in normative sections; UI mock-up removed (API-only feature) |
| Open questions | ✅ No open questions for Feature 041 |
| Plan alignment | ✅ Plan references spec and tasks; dependencies and success criteria match spec wording |
| Tasks coverage | ✅ Every FR maps to ≥ 1 task; tests staged before implementation per SDD cadence |
| Constitution compliance | ✅ No fallbacks or shims; straight-line increments; test-first cadence |
| Tooling readiness | ✅ Commands documented per increment |

_Reviewed: 2026-05-31. No blocking findings. Ready to begin T-041-01._

## Exit Criteria

- [x] All 12 tasks in `tasks.md` marked `[x]`.
- [x] `php artisan test` exits 0 (no regressions).
- [x] `make phpstan` exits 0.
- [x] `vendor/bin/php-cs-fixer fix --dry-run` exits 0.
- [x] `UploadWithMetadataTest` covers S-041-01 through S-041-09, S-041-11.
- [x] Roadmap row updated.
- [x] `knowledge-map.md` updated.
- [x] `_current-session.md` updated.

## Follow-ups / Backlog

- Consider exposing `expected_id` for from-URL imports when that flow is async-job-based (separate feature).
- Evaluate adding tags/license at upload time as a natural extension of this feature (separate feature).
- OpenAPI snapshot testing — once an automated snapshot job exists, add `UploadMetaResource` new fields to the expected schema.
