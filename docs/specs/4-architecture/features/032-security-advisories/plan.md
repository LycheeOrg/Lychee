# Feature Plan 032 – Security Advisories Check

_Linked specification:_ `docs/specs/4-architecture/features/032-security-advisories/spec.md`  
_Status:_ Draft  
_Last updated:_ 2026-04-06

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

Lychee administrators automatically learn about security vulnerabilities in their running version the moment they log in, without any manual action. The check is fully opt-out, cached to avoid API rate limits, and never discloses vulnerability information to non-admin users.

**Success signals:**
- `SecurityAdvisoriesService` fetches, caches, and parses the GitHub API response; unit-tested with fixture data.
- `VersionRangeChecker` correctly evaluates all six semver operators and comma-separated ranges; covered by unit tests for every operator and boundary values.
- `SecurityAdvisoriesCheck` diagnostic pipe adds one error entry per matching advisory, visible only to admins; unit-tested with guard conditions.
- `GET /api/v2/Security/Advisories` endpoint enforces admin-only access and returns the correct structure.
- `SecurityAdvisoriesModal` Vue component renders CVE list, dismisses correctly, and persists dismissal in `sessionStorage`.
- Feature flag (`vulnerability-check = false`) produces no-op across all layers.
- PHPStan 0 errors, `php-cs-fixer` clean, `vue-tsc` clean.

## Scope Alignment

- **In scope:**
  - `config/urls.php` — new `advisories` key (`api_url`, `cache_ttl`).
  - `config/features.php` — new `vulnerability-check` flag (default `true`).
  - `SecurityAdvisoriesService` — fetches, caches, and parses advisory data.
  - `VersionRangeChecker` — pure version/range comparison logic.
  - `SecurityAdvisory` DTO.
  - `AdvisoriesRequest` — extends `JsonRequestFunctions` with custom `Accept` header.
  - `SecurityAdvisoriesCheck` — `DiagnosticPipe` registered in `Errors.php`.
  - `SecurityAdvisoriesController` — REST controller for `GET /api/v2/Security/Advisories`.
  - `SecurityAdvisoryResource` — Spatie Data resource.
  - Route registration in API routes file.
  - `SecurityAdvisoriesModal.vue` — dismissable modal component.
  - Integration in login flow (call advisory endpoint, show modal if results present).
  - Test fixture `tests/Fixtures/github-security-advisories.json`.
  - Unit and feature tests (PHP), Vue component test.
  - OpenAPI schema update.
  - Knowledge-map and roadmap updates.

- **Out of scope:**
  - Automatic patching or version updates.
  - Email/webhook notifications for advisories.
  - Scanning composer or npm dependencies.
  - Custom advisory feeds beyond the configured URL.

## Dependencies & Interfaces

- **`ExternalRequestFunctions`** — base HTTP fetch class; must be extended or modified to support custom HTTP headers (specifically `Accept: application/vnd.github+json`).
- **`JsonRequestFunctions`** — parent of the new `AdvisoriesRequest`; provides JSON decoding.
- **`InstalledVersion::getVersion()`** — returns the running `Version` DTO; used by `VersionRangeChecker`.
- **`Version` DTO** — provides `major`, `minor`, `patch` for semver comparison.
- **`DiagnosticPipe` contract** — implemented by `SecurityAdvisoriesCheck`.
- **`Errors.php`** — registers `SecurityAdvisoriesCheck` in its `$pipes` array.
- **Laravel `Cache` facade** — used by `SecurityAdvisoriesService` for TTL-based caching.
- **Laravel Auth** — guards advisory endpoint and diagnostic pipe.
- **`SettingsPolicy::CAN_SEE_DIAGNOSTICS`** — existing policy for diagnostic access; advisory endpoint uses a tighter `may_administrate` check.
- **Spatie Laravel Data** — `SecurityAdvisoryResource` extends `Data`.
- **PrimeVue / Vue 3** — modal built with `Dialog` component consistent with existing admin modals.
- **`sessionStorage`** — browser-side dismissal persistence (cleared on tab/window close).

## Assumptions & Risks

- **Assumptions:**
  - The GitHub Security Advisories API endpoint is stable and returns the documented JSON structure.
  - `ExternalRequestFunctions` can be extended (via constructor parameter or subclass) to send additional HTTP headers without breaking existing callers.
  - The `InstalledVersion::getVersion()` returns a valid `Version` DTO in all environments (including fresh installs before migration).
  - `sessionStorage` is available in all supported browsers (it is part of the Web Storage API since HTML5).

- **Risks / Mitigations:**
  - *GitHub API rate limits:* Public API limited to 60 req/hour per IP. Mitigation: cache TTL defaults to 1 day; single request per cache expiry.
  - *`ExternalRequestFunctions` header extension breaks existing callers:* Mitigation: use a constructor `array $extra_headers = []` parameter with backward-compatible default `[]`; existing callers unchanged.
  - *Unit tests cannot call live GitHub API:* `ExternalRequestFunctions::fetchFromServer()` already throws in test environment; tests use fixture JSON directly via mock/fake.
  - *`InstalledVersion::getVersion()` throws before migration:* `SecurityAdvisoriesCheck` wraps the call in a try/catch and skips the check if version is unavailable.

## Implementation Drift Gate

After each increment, verify:

1. `php artisan test --filter=SecurityAdvisories` — all advisory tests pass.
2. `make phpstan` — 0 PHPStan errors.
3. `vendor/bin/php-cs-fixer fix --dry-run` — no fixable issues.
4. `npm run type-check` — 0 TypeScript errors.

## Increment Map

1. **I1 – Configuration & DTO**
   - _Goal:_ Add config keys and define the `SecurityAdvisory` DTO and `VersionRangeChecker` service.
   - _Preconditions:_ None.
   - _Steps:_
     - Add `advisories.api_url` and `advisories.cache_ttl` to `config/urls.php`.
     - Add `vulnerability-check` flag to `config/features.php`.
     - Create `app/DTO/SecurityAdvisory.php` with fields `cve_id`, `ghsa_id`, `summary`, `cvss_score`, `cvss_vector`, `affected_version_range`.
     - Create `app/Services/VersionRangeChecker.php` — pure `matches(Version $version, string $range): bool`.
     - Create unit test `tests/Unit/Services/VersionRangeCheckerTest.php` covering all six operators and multi-constraint ranges.
   - _Commands:_ `make phpstan && vendor/bin/php-cs-fixer fix --dry-run && php artisan test --filter=VersionRangeCheckerTest`
   - _Exit:_ Config keys present; DTO and checker pass static analysis and unit tests.

2. **I2 – Advisory Fetch Service**
   - _Goal:_ Implement `AdvisoriesRequest` (HTTP fetch with Accept header) and `SecurityAdvisoriesService` (fetch + cache + parse).
   - _Preconditions:_ I1 complete; `config/urls.php` keys present.
   - _Steps:_
     - Add `array $extra_headers = []` constructor parameter to `ExternalRequestFunctions` and thread it into the stream context `header` array.
     - Create `app/Metadata/Json/AdvisoriesRequest.php` extending `JsonRequestFunctions`; sets `Accept: application/vnd.github+json` header.
     - Create `app/Services/SecurityAdvisoriesService.php` — uses `AdvisoriesRequest` to fetch, caches result, filters advisories with `VersionRangeChecker`, returns `SecurityAdvisory[]`.
     - Create fixture `tests/Fixtures/github-security-advisories.json` with two advisories (one matching, one not).
     - Create `tests/Unit/Services/SecurityAdvisoriesServiceTest.php` using fixture data; verify caching (S-032-06), parse error handling (S-032-09), feature-disabled early-return (S-032-01).
   - _Commands:_ `make phpstan && php artisan test --filter=SecurityAdvisoriesServiceTest`
   - _Exit:_ Service returns correct `SecurityAdvisory[]` for fixture; caching verified; PHPStan clean.

3. **I3 – Diagnostic Pipe**
   - _Goal:_ Implement and register `SecurityAdvisoriesCheck` in the diagnostic pipeline.
   - _Preconditions:_ I2 complete.
   - _Steps:_
     - Create `app/Actions/Diagnostics/Pipes/Checks/SecurityAdvisoriesCheck.php` implementing `DiagnosticPipe`.
     - Guard: return `$next($data)` immediately if feature disabled or user is not admin.
     - For each `SecurityAdvisory` returned by the service: append `DiagnosticData::error("Security vulnerability: {cve_id} (CVSS {score})", self::class, [$advisory->summary])`.
     - Register `SecurityAdvisoriesCheck::class` at the end of `$pipes` in `app/Actions/Diagnostics/Errors.php`.
     - Create `tests/Unit/Actions/Diagnostics/SecurityAdvisoriesCheckTest.php` with mock service; verify admin guard, no-entries when service empty, correct error format.
   - _Commands:_ `make phpstan && php artisan test --filter=SecurityAdvisoriesCheckTest`
   - _Exit:_ Diagnostic pipe registered; unit tests pass; PHPStan clean.

4. **I4 – REST Endpoint**
   - _Goal:_ Expose `GET /api/v2/Security/Advisories` for the frontend to consume.
   - _Preconditions:_ I2 complete.
   - _Steps:_
     - Create `app/Http/Resources/Models/SecurityAdvisoryResource.php` extending Spatie `Data`; fields: `cve_id`, `ghsa_id`, `summary`, `cvss_score`, `cvss_vector`.
     - Create `app/Http/Controllers/Admin/SecurityAdvisoriesController.php`; index method calls service and returns resource collection; admin-only via FormRequest or middleware.
     - Register route `GET /api/v2/Security/Advisories` in the appropriate admin API routes file.
     - Write feature test `tests/Feature_v2/SecurityAdvisories/IndexTest.php` covering: admin receives list (S-032-03), non-admin 403 (S-032-07), unauthenticated 401 (S-032-08), disabled returns `[]` (S-032-01).
   - _Commands:_ `php artisan test --filter=SecurityAdvisories`
   - _Exit:_ Endpoint returns correct JSON; auth enforced; all scenarios covered.

5. **I5 – Frontend Modal**
   - _Goal:_ Show dismissable advisory modal to admin on login when vulnerabilities are present.
   - _Preconditions:_ I4 complete; REST endpoint available.
   - _Steps:_
     - Create `resources/js/services/security-advisories-service.ts` — `getAdvisories(): Promise<AxiosResponse<SecurityAdvisoryResource[]>>`.
     - Create `resources/js/components/modals/SecurityAdvisoriesModal.vue` — PrimeVue `Dialog`; lists CVEs and CVSS scores; "Close" button sets `sessionStorage.advisory_dismissed = '1'`; "Go to Diagnostics" navigates to diagnostics page.
     - Update `lychee.d.ts` to include `SecurityAdvisoryResource` type.
     - In the post-login flow (after successful auth), check `sessionStorage.advisory_dismissed`; if not set, call advisory service; if results present, show modal.
     - Ensure modal is only shown when `is_admin` is true.
     - Write Vitest unit test for `SecurityAdvisoriesModal.vue`: renders CVE list, dismiss sets `sessionStorage`, does not render if flag present.
   - _Commands:_ `npm run type-check && npm run test`
   - _Exit:_ Modal renders correctly in Vitest; dismissal behaviour verified; TypeScript clean.

6. **I6 – Quality Gates & Documentation**
   - _Goal:_ Final quality sweep, OpenAPI update, knowledge-map update, roadmap update.
   - _Preconditions:_ I1–I5 complete.
   - _Steps:_
     - Run full test suite: `php artisan test`.
     - Run `make phpstan`.
     - Run `vendor/bin/php-cs-fixer fix --dry-run`.
     - Run `npm run type-check && npm run lint`.
     - Update OpenAPI schema to include `GET /api/v2/Security/Advisories`.
     - Update `docs/specs/4-architecture/knowledge-map.md`.
     - Move Feature 032 in `roadmap.md` from Active → Completed.
   - _Commands:_ `php artisan test && make phpstan && npm run type-check`
   - _Exit:_ All gates green; documentation updated.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-032-01 | I1 (config), I2 (service guard), I3 (pipe guard), I4 (endpoint guard), I5 (frontend guard) | Feature-disabled path tested in every layer. |
| S-032-02 | I2 (service returns empty), I3 (no entries), I4 (empty list), I5 (no modal) | No-match path. |
| S-032-03 | I2, I3, I4, I5 | Single advisory match — primary happy path. |
| S-032-04 | I2, I3, I4, I5 | Multiple advisory matches. |
| S-032-05 | I2 (fetch failure → empty) | GitHub API error. |
| S-032-06 | I2 (cache hit) | Cache TTL respected. |
| S-032-07 | I4 (403) | Non-admin access to endpoint. |
| S-032-08 | I4 (401) | Unauthenticated access. |
| S-032-09 | I2 (malformed token skip) | Malformed range token logged and skipped. |
| S-032-10 | I5 (dismiss → sessionStorage) | Modal dismissal. |
| S-032-11 | I5 (new session → modal reappears) | New browser session clears sessionStorage. |
| S-032-12 | I5 (non-admin → no call) | Non-admin user; no advisory fetch. |
| S-032-13 | I1 (VersionRangeChecker) | Multi-constraint range evaluation. |

## Analysis Gate

_Not yet completed — pending implementation start._

## Exit Criteria

- All unit and feature tests pass: `php artisan test --filter=SecurityAdvisories`.
- Full test suite passes without regressions: `php artisan test`.
- PHPStan 0 errors: `make phpstan`.
- `php-cs-fixer` clean: `vendor/bin/php-cs-fixer fix --dry-run`.
- TypeScript/Vue: `npm run type-check` reports 0 errors.
- ESLint: `npm run lint` reports 0 errors.
- OpenAPI schema updated.
- Knowledge-map updated.
- Roadmap row moved to Completed.

## Follow-ups / Backlog

- Consider adding a `php artisan lychee:advisories-check` Artisan command for CLI-based advisory checks in CI/CD pipelines.
- Investigate authenticated GitHub API requests (personal access token) to raise the rate limit from 60 to 5000 requests/hour for high-traffic installs.
- Evaluate persisting dismissal in the database (per-user flag) rather than `sessionStorage` if multi-device admins find the per-session approach inconvenient.
