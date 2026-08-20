# Feature Plan 056 – API v3 Asset Retrieval

_Linked specification:_ `docs/specs/4-architecture/features/056-api-v3-asset-retrieval/spec.md`
_Status:_ Draft
_Last updated:_ 2026-08-20

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
Establish API v3 as a real, working, additive REST surface with one correct, fully-tested endpoint: `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}`. Success = every Branch & Scenario Matrix entry (S-056-01..17) has a passing Feature test written before its implementation, `make phpstan` is clean on all new files, v2 is provably untouched (NFR-056-03), and the new v3 routing/versioning convention is documented for the next v3 feature to reuse without re-deriving it.

## Scope Alignment
- **In scope:** `routes/api_v3.php` + `RouteServiceProvider` registration; `PhotoAssetController`; `GetPhotoAssetRequest`; `TemporaryLinkSigner` service; `signatureRequired()` predicate; watermark-aware local streaming; S3 redirect branch; full Feature test suite for S-056-01..17; `api-design.md`/`knowledge-map.md` updates.
- **Out of scope:** Any other v3 endpoint; v2 code changes; a general SoA collection-response convention/serializer (ADR-0009 explicitly defers this); frontend/client consumption of this endpoint (no v8/v7 UI changes in this feature).

## Dependencies & Interfaces
- `App\Models\SizeVariant` / `App\Models\Photo` (existing, unchanged) — `SizeVariant::getFile()`/`FlysystemFile` for local streaming.
- `App\Policies\PhotoPolicy::canSee()`/`canAccessFullPhoto()` (existing, unchanged).
- `App\Image\Watermarker::get_path()` (existing, unchanged).
- `App\Repositories\ConfigManager` (existing, unchanged) — reads `temporary_image_link_enabled`/`temporary_image_link_when_logged_in`/`temporary_image_link_when_admin`/`temporary_image_link_life_in_seconds`.
- `App\Enum\SizeVariantType`, `App\Enum\StorageDiskType` (existing, unchanged).
- `App\Http\Requests\BaseApiRequest` (existing base class for the new `GetPhotoAssetRequest`).
- `Tests\Feature_v2\Base\BaseApiWithDataTest` (existing fixture graph — users/albums/photos/permissions — reused by inheritance from the new v3 test base; see Assumptions).
- `phpunit.xml` — gets a new `Feature_v3` testsuite entry (new, additive; existing `Feature_v2` entry untouched).
- ADR-0008 (signing/authorization model), ADR-0009 (response-shape precedent) — both already Accepted.

## Assumptions & Risks
- **Assumptions:**
  - **Tests live under a new `tests/Feature_v3/` tree, not `Feature_v2`** (owner instruction, 2026-08-20). `phpunit.xml` gets a new `Feature_v3` testsuite entry (`<directory suffix="Test.php">./tests/Feature_v3</directory>`, excluding its own `Base/` classes) mirroring the existing `Feature_v2` block exactly. Since `Tests\Feature_v2\Base\BaseApiTest`'s HTTP-verb helpers (`getJson()`, `postJson()`, etc.) hardcode `self::API_PREFIX = '/api/v2/'` via early-bound `self::` (not `static::`), a v3 subclass cannot simply override the constant — it would be silently ignored by those inherited methods. Rather than editing that v2 file (out of scope, and `self::`→`static::` would be a v2-adjacent change this feature has no reason to make) or duplicating the ~150-line user/album/photo fixture graph in `BaseApiWithDataTest`, this plan takes the lower-footprint path: a new `Tests\Feature_v3\Base\BaseApiWithDataTest extends Tests\Feature_v2\Base\BaseApiWithDataTest` reuses that fixture graph by inheritance (zero duplication, zero v2 edits — `Tests\Feature_v2\Base\BaseApiWithDataTest.php` itself is not modified), and adds its own small v3-specific request helper(s) (e.g. `getV3(string $uri, array $headers = [])` hitting `/api/v3/...` directly via Laravel's native `get()`/`withHeaders()`, since this endpoint's success response is binary, not JSON, so the inherited `getJson()`-style helpers wouldn't fit even if the prefix were fixed). The class carries an explicit doc comment warning not to use the inherited v2-prefixed JSON helpers. This is a deliberate, low-impact convention choice sized for a *single-endpoint* v3 feature; a later v3 feature with many endpoints/fixtures might warrant extracting a version-agnostic fixture trait instead of this inheritance shortcut — noted in Follow-ups.
  - Controller lives at `App\Http\Controllers\Gallery\PhotoAssetController` — follows v2's existing domain-organized (not version-namespaced) controller convention, since no per-version namespace precedent exists to mirror (spec Appendix). Not raised as an open question: this is a low-impact, easily-reversible naming choice, not a behavioral ambiguity.
  - `signatureRequired()`'s exact boolean composition (ADR-0008) is a direct re-purposing of `UrlGenerator::shouldNotUseSignedUrl()`'s existing generation-time logic into a validation-time predicate. This is the plan's best-effort translation of the owner's Q-056-05 answer ("it depends on the temporary link settings... in all cases PhotoPolicy is checked") into precise code; flagged here for review during I2 implementation in case the intended semantics differ subtly from the mirrored predicate.
  - `X-Timestamp`/`X-Mac` header names (not `X-Lychee-Timestamp` etc.) follow the existing single-word `X-<Name>` convention (`X-API-Key`).
- **Risks / Mitigations:**
  - *Risk:* `signatureRequired()` mirrors `shouldNotUseSignedUrl()`'s three-flag composition exactly, but the two predicates serve different purposes (generation vs. validation) — a subtle mismatch could either over- or under-require signatures. *Mitigation:* NFR-056-04's full 2×2×2×3 config/caller-state test matrix (I4) will surface any behavior the owner didn't intend before this ships.
  - *Risk:* No existing precedent for a `size_variant` string being matched against `SizeVariantType` case names in a route/request — case-sensitivity or naming mismatch (e.g. `THUMB2X` vs `thumb2x` vs `thumb_2x`) could confuse API clients. *Mitigation:* I1 picks and documents one exact casing convention (lowercase snake matching the enum's `name()` helper) and tests both a valid and an invalid token (S-056-13).
  - *Risk:* Reusing `config('app.key')` as the HMAC secret means a future `APP_KEY` rotation invalidates all outstanding temporary links instantly (same behavior as Laravel's own signed routes today, so not a new risk class, but worth noting). *Mitigation:* none needed — this matches existing v2 behavior exactly, no regression.

## Implementation Drift Gate
Before I5 (final quality gate), diff `routes/api_v2.php`, `routes/web_v2.php`, `app/Http/Controllers/SecurePathController.php`, and `app/Services/UrlGenerator.php` against `master` — must be empty (NFR-056-03). Record the `git diff --stat` output (or confirmation of zero changes) in this section once run. Any unplanned deviation discovered during implementation (e.g. a needed change to a shared file) must be logged here with rationale before proceeding, per this repo's Implementation Drift Gate convention.

_Not yet run — pending implementation start._

## Increment Map

1. **I1 – Route, request validation, and controller skeleton**
   - _Goal:_ Stand up `GET /api/v3/Photo/{photo_id}/Asset/{size_variant}` end-to-end for the simplest success path (authenticated owner, local disk, no watermark, no temporary link), returning a 200 with correct bytes.
   - _Preconditions:_ Spec FR-056-01/02/03 finalized (done).
   - _Steps:_
     - Scaffold the `Feature_v3` test tree first: add a `Feature_v3` testsuite entry to `phpunit.xml` (mirrors the existing `Feature_v2` block: `<directory suffix="Test.php">./tests/Feature_v3</directory>`, excluding `./tests/Feature_v3/Base/BaseApiWithDataTest.php`); create `tests/Feature_v3/Base/BaseApiWithDataTest.php` (`namespace Tests\Feature_v3\Base`, `extends \Tests\Feature_v2\Base\BaseApiWithDataTest` to reuse the fixture graph, adds a `getV3(string $uri, array $headers = [])` helper hitting `/api/v3/...` via native `get()`/`withHeaders()`, doc comment warning not to use the inherited v2-prefixed `getJson()`/`postJson()`/etc.).
     - Add failing Feature test for S-056-01 (`tests/Feature_v3/Photo/PhotoAssetV3Test.php`, extending the new `Tests\Feature_v3\Base\BaseApiWithDataTest`).
     - Create `routes/api_v3.php`; register in `app/Providers/RouteServiceProvider.php` (`Route::middleware('api')->prefix('api/v3')->group(base_path('routes/api_v3.php'));`).
     - Create `App\Http\Requests\Photo\GetPhotoAssetRequest extends BaseApiRequest`: `rules()` validates `photo_id` (`RandomIDRule`) and `size_variant` (must match a `SizeVariantType` case name, case-insensitive); `authorize()` calls `Gate::check()` with `PhotoPolicy::CAN_SEE` or `CAN_ACCESS_FULL_PHOTO` depending on the resolved `size_variant` (FR-056-03); `processValidatedValues()` resolves the `Photo` and `SizeVariant` models (404 via `findOrFail`-style if either is missing, satisfying FR-056-01/02's validation path).
     - Create `App\Http\Controllers\Gallery\PhotoAssetController` with a single `show(GetPhotoAssetRequest $request)` action; for this increment, local-disk-only, no watermark, no S3 branch, no temporary-link branch — just `response()->file($size_variant->getFile()->getFullPath())` or the `FlysystemFile` equivalent used by `SecurePathController`.
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `make phpstan`
   - _Exit:_ S-056-01 passes; S-056-12/13/14 (404/422 validation paths) also pass since they're implied by this increment's request validation.

2. **I2 – Temporary-link signing (`TemporaryLinkSigner`) and `signatureRequired()`**
   - _Goal:_ Implement FR-056-04/05 — headers-based signature verification and the config-driven requirement predicate.
   - _Preconditions:_ I1 complete.
   - _Steps:_
     - Add failing Feature/Unit tests for S-056-02..11 first.
     - Create `App\Services\TemporaryLinkSigner` (`sign(int $timestamp): string`, `verify(int $timestamp, string $mac): bool`) — unit-tested independent of HTTP (Test Strategy).
     - Extend `GetPhotoAssetRequest::authorize()`: read `X-Timestamp`/`X-Mac` headers (`$this->header('X-Timestamp')`/`$this->header('X-Mac')`, both-or-neither validation → 422 if only one present); compute `signatureRequired(Auth::user(), $config_manager)` (ADR-0008's predicate, implemented as a private method or a small dedicated class — decide during implementation which reads cleaner; either is acceptable, not spec-load-bearing); combine with the temporary-link validity check and the existing `PhotoPolicy` check into the final `authorize()` boolean, returning 401 (not 403) specifically for signature/auth failures vs. 403 for `PhotoPolicy` denial (distinguish via a custom exception or explicit `abort(401, ...)` inside `authorize()` before falling through to the policy check — confirm Laravel's `FormRequest::authorize()` returning `false` always yields 403 by default, so a genuine 401 case needs an explicit `abort(401)`/thrown `AuthenticationException` inside `authorize()` rather than a plain `false` return; verify this during implementation, since it affects whether S-056-04..11's expected 401s require a small deviation from the pure-boolean `authorize()` pattern used elsewhere in this codebase).
   - _Commands:_ `php artisan test --filter=TemporaryLinkSigner`, `php artisan test --filter=PhotoAssetV3Test`, `make phpstan`
   - _Exit:_ S-056-02..11 all pass, including the full config/caller-state matrix (NFR-056-04).

3. **I3 – Watermark and S3-redirect branches**
   - _Goal:_ Implement FR-056-06/07.
   - _Preconditions:_ I1 complete (I2 not required — independent branches).
   - _Steps:_
     - Add failing tests for S-056-15/16 first.
     - In `PhotoAssetController::show()`, call `Watermarker::get_path($size_variant)` instead of the plain `short_path` before resolving the file (FR-056-06).
     - Branch on disk adapter (mirrors `UrlGenerator::pathToUrl()`'s `getAdapter() instanceof AwsS3V3Adapter` check): if S3, `return redirect()->away($disk->temporaryUrl(...))` (302); else stream as in I1.
   - _Commands:_ `php artisan test --filter=PhotoAssetV3Test`, `make phpstan`
   - _Exit:_ S-056-15/16/17 pass.

4. **I4 – Full scenario-matrix sweep and edge-case hardening**
   - _Goal:_ Close any remaining gaps across S-056-01..17; confirm the full `PhotoPolicy` split (`CAN_SEE` vs `CAN_ACCESS_FULL_PHOTO`) per size-variant class (S-056-17).
   - _Preconditions:_ I1-I3 complete.
   - _Steps:_ Run the full `PhotoAssetV3Test` suite; add any missing scenario coverage found; verify NFR-056-04's config-matrix test explicitly enumerates all 2×2×2 boolean combinations crossed with guest/logged-in/admin caller state (8×3 = 24 cases, though several collapse to the same expected outcome — document the collapsed truth table in the test file's comments).
   - _Commands:_ `php artisan test --filter=PhotoAssetV3`, `make phpstan`, `vendor/bin/php-cs-fixer fix`
   - _Exit:_ All S-056-* scenarios green; PHPStan clean; php-cs-fixer clean.

5. **I5 – Documentation and quality gate**
   - _Goal:_ Close out Documentation Deliverables and run the full repo quality gate.
   - _Preconditions:_ I1-I4 complete.
   - _Steps:_
     - Add "API v3" section to `docs/specs/3-reference/api-design.md` (route, headers, response codes, ADR-0009 reference).
     - Add `routes/api_v3.php`/`PhotoAssetController`/`TemporaryLinkSigner` entries to `docs/specs/4-architecture/knowledge-map.md`.
     - Run the Implementation Drift Gate diff check (see above) and record the result.
     - Move Feature 056's roadmap row from Active to Completed once all tasks are `[x]`.
   - _Commands:_ `vendor/bin/php-cs-fixer fix`, `php artisan test`, `make phpstan`
   - _Exit:_ Full PHP quality gate green; roadmap/knowledge-map/api-design.md updated; this plan's Analysis Gate section completed.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-056-01 | I1 / T-056-01a..05 | Baseline authenticated success path |
| S-056-02 | I2 / T-056-06..09 | Guest + valid temporary link |
| S-056-03 | I2 / T-056-06..09 | Valid signature, `PhotoPolicy` still denies |
| S-056-04 | I2 / T-056-06..09 | Guest, no headers at all |
| S-056-05 | I2 / T-056-06..09 | Feature globally disabled |
| S-056-06 | I2 / T-056-06..09 | Tampered MAC |
| S-056-07 | I2 / T-056-06..09 | Expired timestamp |
| S-056-08 | I2 / T-056-06..09 | Future timestamp |
| S-056-09 | I2 / T-056-06..09 | Only one of the two headers present |
| S-056-10 | I2 / T-056-06..09 | Logged-in user still required to sign |
| S-056-11 | I2 / T-056-06..09 | Admin exempted from signing |
| S-056-12 | I1 / T-056-01a..05 | Missing `SizeVariant` row |
| S-056-13 | I1 / T-056-01a..05 | Invalid `size_variant` token |
| S-056-14 | I1 / T-056-01a..05 | Unknown `photo_id` |
| S-056-15 | I3 / T-056-10..12 | S3 redirect |
| S-056-16 | I3 / T-056-10..12 | Watermark applied |
| S-056-17 | I4 / T-056-13..14 | `CAN_SEE` vs `CAN_ACCESS_FULL_PHOTO` split |

## Analysis Gate
Run 2026-08-20, against [docs/specs/5-operations/analysis-gate-checklist.md](../../../5-operations/analysis-gate-checklist.md) (pre-implementation section only — Implementation Drift Gate is deferred to I5/T-056-17).

1. **Specification completeness** — ✅ Pass. FR-056-01..07/NFR-056-01..04 populated; every FR/NFR cites its resolving Q-ID and/or ADR. No UI mock-up section included — correct, this feature has no UI/frontend surface (Non-Goals).
2. **Open questions review** — ✅ Pass. Q-056-01..06 all Resolved (no `Open` rows remain for Feature 056 in open-questions.md). ADR-0008 (signing/authorization) and ADR-0009 (response-shape precedent) both Accepted and linked from spec.md and open-questions.md.
3. **Plan alignment** — ✅ Pass. This plan references `spec.md`/`tasks.md` at the correct paths; Dependencies & Interfaces and Vision & Success Criteria match spec wording (ADR-0008/0009, `PhotoPolicy`, `Watermarker`, `ConfigManager`, existing enums).
4. **Tasks coverage** — ✅ Pass. Every FR-056-* maps to ≥1 task (see Scenario Tracking table above for the S-ID↔task mapping; FR↔task mapping: FR-01→T-01a..05, FR-02→T-01b/04, FR-03→T-03/09/14, FR-04→T-06..09, FR-05→T-09, FR-06→T-10/11, FR-07→T-10/12). Tests precede implementation in every increment (T-056-01a/01b/06/07/10 are test-first tasks). All 17 scenarios (success/validation/failure branches) have queued failing tests before implementation.
5. **Constitution compliance** — ⚠️ Partial/Note. `docs/specs/6-decisions/project-constitution.md` (the checklist's cited input) does not exist in this repo — treating AGENTS.md's guardrails as the de facto constitution reference instead, consistent with how this repo actually operates (pre-existing gap, not introduced by this feature). Against AGENTS.md: spec-first ✅ (spec preceded plan/tasks), clarification gate ✅ (all Q-IDs resolved before plan.md was written), test-first ✅ (Increment Map sequences tests first throughout), documentation sync ✅ (I5 tasks cover api-design.md/knowledge-map.md), dependency control ✅ (no new dependencies). Control-flow minimization: `TemporaryLinkSigner` and the `signatureRequired()` predicate are extracted as small, independently-testable units rather than inline branching in the controller — satisfies the "nearly straight-line" guidance.
6. **Tooling readiness** — ✅ Pass. Commands documented per-increment in the Increment Map (`php artisan test --filter=...`, `make phpstan`, `vendor/bin/php-cs-fixer fix`).

**Outcome:** Gate passed (one documented pre-existing repo gap under #5, not blocking). Implementation (I1) may proceed.

## Exit Criteria
- All tasks in `tasks.md` marked `[x]`.
- `php artisan test` green (new `PhotoAssetV3Test` + `TemporaryLinkSigner` unit tests; no regressions elsewhere).
- `make phpstan` — 0 errors on touched/new files.
- `vendor/bin/php-cs-fixer fix` — clean.
- Implementation Drift Gate confirms zero changes to the four named v2 files (NFR-056-03).
- `docs/specs/3-reference/api-design.md` and `docs/specs/4-architecture/knowledge-map.md` updated.
- Roadmap entry moved to Completed.

## Follow-ups / Backlog
- A future v3 feature will need to define the actual SoA collection-response convention (deferred by ADR-0009/this feature's Non-Goals) — likely the natural second v3 endpoint (e.g. a paginated photo/album listing).
- S3 proxying (rather than redirect) was explicitly rejected for this feature (Q-056-03 Option B chosen) — revisit only if a concrete need for server-side S3 proxying emerges (e.g. hiding bucket URLs from clients).
- Per-resource-scoped signatures (MAC over `photo_id`+`size_variant`+`timestamp`, not just `timestamp`) were explicitly out of scope per the owner's instruction — flagged in ADR-0008 as an accepted trade-off, not a deferred task, but worth revisiting if abuse patterns are observed in production.
