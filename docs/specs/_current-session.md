# Current Session

_Last updated: 2026-05-31_

## Active Features

**Feature 041 – Upload Photo Metadata**
- Status: Planning (spec + plan + tasks complete)
- Priority: P2
- License: Open
- Started: 2026-05-31
- Dependencies: None

**Feature 040 – Disable Request Caching**
- Status: Planning (spec + plan + tasks complete)
- Priority: P2
- License: Open
- Started: 2026-05-18
- Dependencies: None

## Session Summary

User requested Feature 041: allow callers to set a photo's `title` and `description` at upload time, and return an `expected_id` in the upload response so clients know the photo's future ID without waiting for full processing.

### Feature 041: Upload Photo Metadata

**Status:** spec.md + plan.md + tasks.md complete; analysis gate passed; ready to begin T-041-01.

**No open questions.** All requirements are clear from the problem statement.

**Plan increments (4 × ≤60 min, 12 tasks total):**
- **I1 – DTO Chain & Core Model** (T-041-01 to T-041-04): failing test stubs, propagate `title`/`description`/`preallocated_id` through `ImportParam → InitDTO → StandaloneDTO`, add `Photo::preallocateId()`.
- **I2 – New Pipe & AutoRenamer Guard** (T-041-05, T-041-06): create `ApplyUserProvidedMetadata` pipe (StandalonePipe only), insert before `HydrateMetadata`, guard `AutoRenamer`.
- **I3 – Request, Resource & Controller** (T-041-07 to T-041-11): add `title`/`description` validation (fires on every chunk), add fields to `UploadMetaResource`, generate `expected_id` in controller, update `ProcessImageJob`.
- **I5 – Quality Gates + Docs** (T-041-14): full pipeline green; roadmap, knowledge map, session docs updated.

**Key artefacts produced:**
- Spec: [docs/specs/4-architecture/features/041-upload-photo-metadata/spec.md](docs/specs/4-architecture/features/041-upload-photo-metadata/spec.md)
- Plan: [docs/specs/4-architecture/features/041-upload-photo-metadata/plan.md](docs/specs/4-architecture/features/041-upload-photo-metadata/plan.md)
- Tasks: [docs/specs/4-architecture/features/041-upload-photo-metadata/tasks.md](docs/specs/4-architecture/features/041-upload-photo-metadata/tasks.md)
- Roadmap row added to Active Features.

### Feature 040: Disable Request Caching

**Status:** spec.md + plan.md + tasks.md complete; ready to begin T-040-01.

**No open questions.**

**Plan increments (5 × ≤40 min, 9 tasks total):**
- **I1 – Migration** (T-040-01): force `cache_enabled = '0'`.
- **I2 – Feature flag** (T-040-02, T-040-03): `enable-request-caching` in `config/features.php`.
- **I3 – Controller filter** (T-040-04): hide `Mod Cache` category when flag off.
- **I4 – Feature tests** (T-040-05, T-040-06).
- **I5 – Quality gates + docs** (T-040-07 to T-040-09).

## Next Steps

1. Begin Feature 041 implementation at **T-041-01** (write failing test stubs for UploadWithMetadataTest).
2. Follow tests-before-code SDD cadence through I1 → I5.
3. After each task passes verification, tick the box in `tasks.md` immediately.
4. On completion of I5, move roadmap row 041 from "Active" to "Completed".
5. Feature 040 is also ready to begin at T-040-01 (migration) — can be picked up in parallel or after 041.

## Open Questions

None for either active feature. Q-041-01, Q-041-02, and Q-041-03 resolved (all Option A) — see `open-questions.md` for details.

## References

**Feature 041:**
- Spec: [041-upload-photo-metadata/spec.md](docs/specs/4-architecture/features/041-upload-photo-metadata/spec.md)
- Plan: [041-upload-photo-metadata/plan.md](docs/specs/4-architecture/features/041-upload-photo-metadata/plan.md)
- Tasks: [041-upload-photo-metadata/tasks.md](docs/specs/4-architecture/features/041-upload-photo-metadata/tasks.md)
- Upload controller: [app/Http/Controllers/Gallery/PhotoController.php](app/Http/Controllers/Gallery/PhotoController.php)
- Upload request: [app/Http/Requests/Photo/UploadPhotoRequest.php](app/Http/Requests/Photo/UploadPhotoRequest.php)
- UploadMetaResource: [app/Http/Resources/Editable/UploadMetaResource.php](app/Http/Resources/Editable/UploadMetaResource.php)
- ProcessImageJob: [app/Jobs/ProcessImageJob.php](app/Jobs/ProcessImageJob.php)
- ImportParam: [app/DTO/ImportParam.php](app/DTO/ImportParam.php)
- InitDTO: [app/DTO/PhotoCreate/InitDTO.php](app/DTO/PhotoCreate/InitDTO.php)
- StandaloneDTO: [app/DTO/PhotoCreate/StandaloneDTO.php](app/DTO/PhotoCreate/StandaloneDTO.php)
- Photo model: [app/Models/Photo.php](app/Models/Photo.php)
- HasRandomIDAndLegacyTimeBasedID: [app/Models/Extensions/HasRandomIDAndLegacyTimeBasedID.php](app/Models/Extensions/HasRandomIDAndLegacyTimeBasedID.php)
- HydrateMetadata pipe: [app/Actions/Photo/Pipes/Shared/HydrateMetadata.php](app/Actions/Photo/Pipes/Shared/HydrateMetadata.php)
- AutoRenamer pipe: [app/Actions/Photo/Pipes/Standalone/AutoRenamer.php](app/Actions/Photo/Pipes/Standalone/AutoRenamer.php)
- Upload service (TS): [resources/js/services/upload-service.ts](resources/js/services/upload-service.ts)
- UploadingLine.vue: [resources/js/components/forms/upload/UploadingLine.vue](resources/js/components/forms/upload/UploadingLine.vue)
- UploadPanel.vue: [resources/js/components/modals/UploadPanel.vue](resources/js/components/modals/UploadPanel.vue)

**Feature 040:**
- Spec: [040-disable-request-caching/spec.md](docs/specs/4-architecture/features/040-disable-request-caching/spec.md)
- Plan: [040-disable-request-caching/plan.md](docs/specs/4-architecture/features/040-disable-request-caching/plan.md)
- Tasks: [040-disable-request-caching/tasks.md](docs/specs/4-architecture/features/040-disable-request-caching/tasks.md)

**Common:**
- Roadmap: [roadmap.md](docs/specs/4-architecture/roadmap.md)
- Open questions: [open-questions.md](docs/specs/4-architecture/open-questions.md)

---

**Session Context for Handoff:**

Feature 041 spec, plan, and tasks are complete (14 tasks across 5 increments, all ≤60 min, tests-before-code). No open questions. Analysis gate passed (2026-05-31). Next author to pick up: begin T-041-01 (write failing `UploadWithMetadataTest` stubs). All increments are sequential (I1 → I5). Feature 040 remains in Planning and can proceed independently.

