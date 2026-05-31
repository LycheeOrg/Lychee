# Current Session

_Last updated: 2026-05-31_

## Active Features

**Feature 040 – Disable Request Caching**
- Status: Planning (spec + plan + tasks complete)
- Priority: P2
- License: Open
- Started: 2026-05-18
- Dependencies: None

## Session Summary

Feature 041 (Upload Photo Metadata) has been fully implemented and all quality gates pass.

### Feature 041: Upload Photo Metadata — Complete

**Status:** Implementation complete. All 9 feature tests pass. PHPStan 0 errors. php-cs-fixer clean. Roadmap updated.

**What was built:**
- New `ApplyUserProvidedMetadata` pipe (`app/Actions/Photo/Pipes/Standalone/`) — sets caller-supplied `title`/`description` on the `Photo` model before `HydrateMetadata` runs (so EXIF doesn't overwrite user input).
- `AutoRenamer` guard: skips renaming when `StandaloneDTO::$title` is non-null.
- DTO chain propagation: `ImportParam → InitDTO → StandaloneDTO` all carry `?string $title`, `?string $description`, `?string $preallocated_id`.
- `Photo::preallocateId(string $id)` + `HasRandomIDAndLegacyTimeBasedID::generateKey()` guard for ID pre-allocation.
- `UploadPhotoRequest` — `TitleRule` (max 100 chars) and `DescriptionRule` (max 1 000 chars) validation.
- `UploadMetaResource` — `?string $expected_id`, `?string $title`, `?string $description` fields added.
- `PhotoController::upload()` — generates 24-char Base64url `expected_id` on final non-zip chunk; forwards `title`/`description` to `process()`.
- `ProcessImageJob` — serialises `expected_id`, `title`, `description`; passes them through `ImportParam` to `Create`.
- Feature test: `tests/Feature_v2/Photo/UploadWithMetadataTest.php` (9 tests, 466 assertions).

**Key implementation note:** `skip_duplicates=true` causes `ThrowSkipDuplicate` to throw `PhotoSkippedException` (HTTP 409). With `skip_duplicates=false` (default), duplicates are re-linked without error (HTTP 201).

### Feature 040: Disable Request Caching

**Status:** spec.md + plan.md + tasks.md complete; ready to begin T-040-01.

**No open questions.**

## Next Steps

1. Begin Feature 040 implementation at **T-040-01** (migration).
2. Follow tests-before-code SDD cadence through I1 → I5.

## Open Questions

None for active features.

## References

**Feature 040:**
- Spec: [040-disable-request-caching/spec.md](docs/specs/4-architecture/features/040-disable-request-caching/spec.md)
- Plan: [040-disable-request-caching/plan.md](docs/specs/4-architecture/features/040-disable-request-caching/plan.md)
- Tasks: [040-disable-request-caching/tasks.md](docs/specs/4-architecture/features/040-disable-request-caching/tasks.md)

**Common:**
- Roadmap: [roadmap.md](docs/specs/4-architecture/roadmap.md)
- Open questions: [open-questions.md](docs/specs/4-architecture/open-questions.md)

