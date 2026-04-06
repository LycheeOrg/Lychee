# Feature 032 – Security Advisories Check

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-04-06 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/032-security-advisories/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/032-security-advisories/tasks.md` |
| Roadmap entry | #32 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature introduces an automatic security-advisories check for Lychee administrators. A new `SecurityAdvisoriesService` queries the GitHub Security Advisories API, compares each published vulnerability's `vulnerable_version_range` against the running Lychee version, and surfaces any matches in the existing diagnostic error panel (admin-only) and as a dismissable modal that appears once per login session when the admin first authenticates. The check is gated by a feature flag (`features.vulnerability-check`) and its result set is cached for a configurable TTL (`urls.advisories.cache_ttl` in days).

Affected modules: `application` (service, diagnostic pipe), `REST` (new advisory endpoint), `UI` (login-time dismissable modal, diagnostic page update).

## Goals

1. Administrators are automatically alerted to known CVEs affecting the running Lychee version.
2. The advisory check is enabled by default and can be disabled via an environment variable without code changes.
3. The check result is cached to avoid hitting the GitHub rate-limit on every page load.
4. The diagnostic error panel shows CVE identifiers and CVSS scores for matching vulnerabilities — visible to admins only.
5. A dismissable modal is shown once per admin login session so critical issues are surfaced immediately.
6. Non-admin users and unauthenticated visitors never see advisory data.

## Non-Goals

- Automatically applying patches or updates.
- Sending advisory notifications via email or webhooks.
- Displaying advisories for any version other than the currently running Lychee instance.
- Custom advisory feeds beyond the configured `api_url`.
- Scanning dependencies (composer packages, npm) — only the Lychee application version itself.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-032-01 | When `features.vulnerability-check` is `true`, the `SecurityAdvisoriesService` fetches the JSON array from `urls.advisories.api_url`, sending the `Accept: application/vnd.github+json` header. | Advisories array retrieved and cached for `urls.advisories.cache_ttl` days. | URL must be a non-empty string; TTL must be a positive integer. | Log warning and return empty advisory list; no error surfaced to end user. | Log entry on fetch failure. | Problem statement |
| FR-032-02 | The service caches the raw API response using the API URL as the cache key for `urls.advisories.cache_ttl` days. Subsequent calls within the TTL window return the cached payload without hitting the GitHub API. | Cached response served; GitHub API not contacted during TTL window. | Cache driver configured in Laravel (`CACHE_DRIVER`). | If cache store is unavailable, fetch live on every call. | — | Problem statement |
| FR-032-03 | For each advisory returned by the API, the service iterates `vulnerabilities[*].vulnerable_version_range` (a comma-separated string of semver constraints, e.g. `">= 5.0.0, < 5.1.2"`) and evaluates whether the currently installed Lychee version satisfies the range. **If `vulnerable_version_range` is null or an empty string, the version range check passes (advisory matches all versions).** A single advisory with multiple matching `vulnerabilities[]` entries is included **once** (deduplicated by `ghsa_id`). | Matching advisories collected into a result list and deduplicated. | Each constraint token is trimmed and parsed as an operator (`>=`, `<=`, `>`, `<`, `=`, `!=`) followed by a semver. Malformed tokens are skipped with a log warning. Null/empty ranges treated as match-all. | Parsing errors skip the individual advisory; remaining advisories are still checked. | Log warning per malformed token. | Problem statement, Q-032-03, Q-032-04 |
| FR-032-04 | The `SecurityAdvisoriesCheck` diagnostic pipe (implementing `DiagnosticPipe`) is registered in `Errors.php`. It runs **only when** `features.vulnerability-check` is `true` **and** the currently authenticated user is an admin (`may_administrate = true`). **Matching advisories are sorted by `cvss_score DESC NULLS LAST, cve_id DESC NULLS LAST` before being added to the diagnostic pipeline.** | For each matching advisory, a `DiagnosticData::error()` entry is appended with format: `"Security vulnerability: {cve_id ?? ghsa_id} (CVSS {score})"` where the score is formatted to 1 decimal place (or "(no CVSS score)" when null) and `ghsa_id` is used when `cve_id` is null. Details array contains the advisory summary. | Guard clauses applied at the top of `handle()`. Diagnostic entries are clickable links to `https://github.com/advisories/{ghsa_id}`. | If the service returns an empty list (fetch failure, no matches, or feature disabled), no entries are added. | — | Problem statement, Q-032-05, Q-032-06, Q-032-07 |
| FR-032-05 | A new REST endpoint `GET /api/v2/Security/Advisories` returns the list of matching vulnerabilities (CVE ID, CVSS score, summary) when the authenticated user is an admin. Non-admin requests receive 403. Unauthenticated requests receive 401. | Advisory list returned as JSON. | Auth middleware enforces admin-only access. | Returns empty list `[]` when check is disabled or no vulnerabilities match. | — | Problem statement |
| FR-032-06 | The frontend checks `GET /api/v2/Security/Advisories` **immediately after a successful login response (POST /login) when `is_admin` is true**. The check does not fire on page refresh or navigation for already-authenticated users. **The frontend must verify `is_admin` before calling the endpoint to avoid a 403 response.** If the response contains at least one entry, a dismissable modal is displayed once per browser session (using `sessionStorage` to prevent re-display after dismiss). The modal lists the CVE/GHSA IDs (as clickable links to `https://github.com/advisories/{ghsa_id}`), CVSS scores (formatted to 1 decimal place or "(no CVSS score)" when null), using GHSA ID when `cve_id` is null. | Modal shown on first admin login per session if vulnerabilities are present. | Check performed after successful POST /login response with `is_admin = true`. Advisory list sorted by CVSS score DESC, CVE ID DESC. | If the endpoint returns an error or empty list, the modal is not shown. | — | Problem statement, Q-032-01, Q-032-09 |
| FR-032-07 | The modal can be dismissed via the "Close" button, header "×" button, or "Go to Diagnostics" button (which also navigates to the diagnostics page). **All three dismissal actions set `sessionStorage.advisory_dismissed = '1'`.** After dismissal, the modal does not re-appear within the same browser session. **Dismissal is scoped per browser tab (sessionStorage semantics); opening a new tab triggers the modal again on login in that tab.** The modal does not block navigation — it can be dismissed immediately. | Modal closes and `sessionStorage` flag set. Both "Close" and "Go to Diagnostics" buttons set the dismissal flag. | `sessionStorage.setItem('advisory_dismissed', '1')` after any dismissal action. | — | — | Problem statement, Q-032-02, Q-032-10 |
| FR-032-08 | When `features.vulnerability-check` is `false`, the `SecurityAdvisoriesCheck` pipe is a no-op (returns `$next($data)` immediately), the REST endpoint returns `[]`, and the frontend never shows the modal. | Feature disabled path verified. | Feature flag checked as first guard clause. | — | — | Problem statement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-032-01 | Advisory data must never be disclosed to non-admin users or unauthenticated visitors. | Security — version metadata is sensitive operational information. | REST endpoint enforces admin-only access; diagnostic pipe guards on `may_administrate`. | Laravel Auth, `DiagnosticsRequest` policy. | Problem statement |
| NFR-032-02 | The HTTP fetch to the GitHub API must time out within 3 seconds to avoid slowing diagnostic page loads. | User experience — diagnostics page must remain responsive. | Configurable via `ExternalRequestFunctions` timeout. | `ExternalRequestFunctions`. | Performance |
| NFR-032-03 | The feature flag (`VULNERABILITY_CHECK_ENABLED`) must default to `true` (opt-out model) so new installs are protected by default. | Security-first default. | `config/features.php` sets default. | `config/features.php`. | Problem statement |
| NFR-032-04 | Cache TTL must be configurable in whole days via `ADVISORIES_CACHE_TTL_DAYS` env var, defaulting to 1 day. | Operator flexibility. | `config/urls.php` entry; validated as positive integer. | `config/urls.php`. | Problem statement |
| NFR-032-05 | The advisory fetch must send `Accept: application/vnd.github+json` and a `User-Agent` header. | GitHub API requirement — requests without the Accept header may receive degraded responses or be rejected. | Verified in unit tests using HTTP fakes or stream context inspection. | `ExternalRequestFunctions` (extend to support custom headers). | GitHub API docs |
| NFR-032-06 | Version comparison must use the installed Lychee version from `InstalledVersion::getVersion()` and support all six semver operators: `>=`, `<=`, `>`, `<`, `=`, `!=`. | Correctness — must match GitHub's vulnerability range format exactly. | Unit tests with known version/range pairs covering all six operators and comma-separated ranges. | `App\DTO\Version`, `App\Metadata\Versions\InstalledVersion`. | Problem statement |

## UI / Interaction Mock-ups

### Dismissable Modal (shown once per admin session when vulnerabilities are found)

```
┌─────────────────────────────────────────────────────────────────────┐
│ ⚠ Security Vulnerabilities Detected                          [×]    │
├─────────────────────────────────────────────────────────────────────┤
│ The following vulnerabilities affect your current Lychee version:   │
│                                                                     │
│  • CVE-2024-12345  CVSS 8.5    (clickable → GitHub advisory)        │
│  • GHSA-1234-5678-90ab  CVSS 6.1    (when cve_id is null)           │
│  • CVE-2024-99999  (no CVSS score)  (when cvss_score is null)       │
│                                                                     │
│                                        [Go to Diagnostics] [Close]  │
└─────────────────────────────────────────────────────────────────────┘
```

**Notes:**
- Each CVE/GHSA ID is a clickable link to `https://github.com/advisories/{ghsa_id}`.
- CVSS scores are formatted to exactly 1 decimal place (e.g., `8.0`, `9.5`).
- When `cvss_score` is `null`, display `(no CVSS score)` instead of the score.
- When `cve_id` is `null`, display the GHSA ID in the same format.
- Both "Go to Diagnostics" and "Close" buttons set `sessionStorage.advisory_dismissed = '1'`.
- The header [×] button also sets the dismissal flag and closes the modal.

### Diagnostic Error Panel (existing page, new entries added)

```
Self-diagnosis errors:
  [ERROR]  Security vulnerability: CVE-2024-12345 (CVSS 8.5)  (clickable link)
           Remote code execution in upload handler. Update to v5.1.2 or later.
  [ERROR]  Security vulnerability: GHSA-1234-5678-90ab (CVSS 6.1)  (when cve_id is null)
           Reflected XSS in album title. Update to v5.1.1 or later.
  [ERROR]  Security vulnerability: CVE-2024-99999 (no CVSS score)  (when cvss_score is null)
           Configuration disclosure. Update to v5.1.0 or later.
```

**Notes:**
- Advisories are sorted by CVSS score descending (highest severity first), then by CVE ID descending (higher CVE = more recent).
- Entries with `null` CVSS scores appear last.
- Each entry is a clickable link to `https://github.com/advisories/{ghsa_id}`.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-032-01 | Feature disabled (`vulnerability-check = false`): diagnostic pipe is no-op; REST endpoint returns `[]`; no modal shown. |
| S-032-02 | Feature enabled; no matching vulnerabilities: diagnostic pipe adds no entries; REST endpoint returns `[]`; no modal shown. |
| S-032-03 | Feature enabled; one matching advisory: diagnostic error entry with CVE ID and CVSS score added; REST endpoint returns one item; modal shown on admin login. |
| S-032-04 | Feature enabled; multiple matching advisories: one diagnostic error per advisory; REST endpoint returns all matching items; modal lists all. |
| S-032-05 | GitHub API returns HTTP error (5xx / network failure): service returns empty list; no diagnostic entries; no modal; warning logged. |
| S-032-06 | GitHub API response cached: second call within TTL returns cached data without network request. |
| S-032-07 | Non-admin authenticated user calls advisory endpoint: receives 403. |
| S-032-08 | Unauthenticated request to advisory endpoint: receives 401. |
| S-032-09 | Advisory with malformed `vulnerable_version_range` token: token skipped; remaining tokens evaluated; log warning emitted. |
| S-032-10 | Modal dismissed by admin: `sessionStorage` flag set; modal not re-displayed in same browser session. |
| S-032-11 | Admin reopens browser tab (new session): `sessionStorage` cleared; modal re-appears if vulnerabilities still present. |
| S-032-12 | Non-admin user logs in: advisory endpoint not called; no modal shown. |
| S-032-13 | Comma-separated `vulnerable_version_range` spanning two constraints (`>= 5.0.0, < 5.1.2`): both evaluated; version 5.0.5 matches; version 5.1.2 does not. |

## Test Strategy

- **Core / Unit (PHP):**
  - `VersionRangeCheckerTest` — unit test for the constraint parser, covering all six operators and comma-separated ranges (S-032-13), malformed tokens (S-032-09).
  - `SecurityAdvisoriesServiceTest` — unit test with mock HTTP layer; verifies caching behaviour (S-032-06), empty/error response handling (S-032-05).
  - `SecurityAdvisoriesCheckTest` — unit test of the diagnostic pipe; verifies admin-only guard (S-032-07/12), no-entries on empty service result (S-032-02), correct error format for one/multiple matches (S-032-03/04).

- **Application / Feature (PHP):**
  - `SecurityAdvisoriesApiTest` — feature test covering REST endpoint auth (S-032-07, S-032-08), disabled flag (S-032-01), correct response structure.

- **UI (TypeScript/Vue):**
  - `SecurityAdvisoriesModal` component unit test (Vitest): renders CVE list, dismiss sets `sessionStorage`, does not re-render when flag present.

- **Docs / Contracts:** OpenAPI schema updated to include `GET /api/v2/Security/Advisories`.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-032-01 | `SecurityAdvisory` DTO — fields: `cve_id: ?string`, `ghsa_id: string`, `summary: string`, `cvss_score: float\|null`, `cvss_vector: string\|null`, `affected_version_range: string` | application |
| DO-032-02 | `VersionRangeChecker` service / value object — `matches(Version $version, string $range): bool` evaluates comma-separated semver constraints | application |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-032-01 | REST `GET /api/v2/Security/Advisories` | Returns array of `SecurityAdvisoryResource` items matching the running version. Requires admin auth. Returns `[]` when disabled. | Response: `[{cve_id, ghsa_id, summary, cvss_score, cvss_vector}]` |

### Telemetry Events

| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-032-01 | Log warning `security_advisories.fetch_failed` | `url` (redacted to host only), `error` (exception message). |
| TE-032-02 | Log warning `security_advisories.malformed_range` | `cve_id`, `token` (malformed constraint string). |

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-032-01 | `tests/Fixtures/github-security-advisories.json` | Representative GitHub API response with two advisories (one matching, one not) for unit tests. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-032-01 | Modal hidden | No advisories match running version, or feature disabled, or `sessionStorage.advisory_dismissed` is set. |
| UI-032-02 | Modal visible | Admin completed POST /login with `is_admin = true`, advisory endpoint returns ≥1 entry (sorted by CVSS DESC, CVE ID DESC), `sessionStorage.advisory_dismissed` not set. CVE/GHSA IDs rendered as clickable links to `https://github.com/advisories/{ghsa_id}`. CVSS scores formatted to 1 decimal place. |
| UI-032-03 | Modal dismissed | Admin clicks "Close", "×", or "Go to Diagnostics"; `sessionStorage.advisory_dismissed = '1'` set; modal unmounted. "Go to Diagnostics" also navigates to diagnostics page. |
| UI-032-04 | Diagnostic error entry | Diagnostic page loads for admin; each matching advisory rendered as error line with clickable link to GitHub advisory, sorted by CVSS DESC then CVE ID DESC. |

## Telemetry & Observability

- Failed advisory fetches are logged at `WARNING` level (not `ERROR`) to avoid alerting operators to transient GitHub API failures.
- Malformed version range tokens are logged at `WARNING` level with the raw token value for debugging.
- No sensitive version metadata is logged beyond what is already present in the advisory itself.

## Documentation Deliverables

- `docs/specs/4-architecture/roadmap.md` — add Feature 032 to Active Features table.
- `docs/specs/4-architecture/knowledge-map.md` — add entry for `SecurityAdvisoriesService` and `SecurityAdvisoriesCheck`.
- Update `config/urls.php` with `advisories.api_url` and `advisories.cache_ttl`.
- Update `config/features.php` with `vulnerability-check` flag.

## Fixtures & Sample Data

- `tests/Fixtures/github-security-advisories.json` — two advisories; one with `vulnerable_version_range` matching the test version, one not matching. Includes `cve_id`, `ghsa_id`, `summary`, `cvss.score`, `cvss.vector_string`, and `vulnerabilities` array.

## Spec DSL

```yaml
domain_objects:
  - id: DO-032-01
    name: SecurityAdvisory
    fields:
      - name: cve_id
        type: string
        constraints: "nullable (some advisories have no CVE)"
      - name: ghsa_id
        type: string
        constraints: "required"
      - name: summary
        type: string
        constraints: "required"
      - name: cvss_score
        type: float|null
        constraints: "nullable"
      - name: cvss_vector
        type: string|null
        constraints: "nullable"
      - name: affected_version_range
        type: string
        constraints: "comma-separated semver constraints"
  - id: DO-032-02
    name: VersionRangeChecker
    fields:
      - name: matches
        type: "bool"
        constraints: "pure function; no side effects"

routes:
  - id: API-032-01
    method: GET
    path: /api/v2/Security/Advisories
    auth: admin only

telemetry_events:
  - id: TE-032-01
    event: security_advisories.fetch_failed
  - id: TE-032-02
    event: security_advisories.malformed_range

fixtures:
  - id: FX-032-01
    path: tests/Fixtures/github-security-advisories.json

ui_states:
  - id: UI-032-01
    description: Modal hidden (no advisories / disabled / dismissed)
  - id: UI-032-02
    description: Modal visible (admin login, advisories present)
  - id: UI-032-03
    description: Modal dismissed by admin
  - id: UI-032-04
    description: Diagnostic error entries for each matching advisory
```

## Appendix

### GitHub Security Advisories API

- Endpoint: `https://api.github.com/repos/LycheeOrg/Lychee/security-advisories`
- Required header: `Accept: application/vnd.github+json`
- Response: JSON array. Each item includes:
  ```json
  {
    "ghsa_id": "GHSA-xxxx-xxxx-xxxx",
    "cve_id": "CVE-2024-12345",
    "summary": "Remote code execution in upload handler",
    "cvss": {
      "vector_string": "CVSS:3.1/AV:N/AC:L/PR:N/UI:N/S:U/C:H/I:H/A:H",
      "score": 9.8
    },
    "vulnerabilities": [
      {
        "package": {
          "ecosystem": "composer",
          "name": "lycheeorg/lychee"
        },
        "vulnerable_version_range": ">= 5.0.0, < 5.1.2",
        "patched_versions": "5.1.2"
      }
    ]
  }
  ```
- The `vulnerable_version_range` field is a **comma-separated string** of semver constraints using the operators `>=`, `<=`, `>`, `<`, `=`, `!=`.
- A version is considered vulnerable if it satisfies **all** constraints in the comma-separated list (logical AND).

### Version Comparison Logic

Given `InstalledVersion::getVersion()` returning a `Version` object (major, minor, patch):

1. Split `vulnerable_version_range` by `,` and trim each token.
2. For each token, parse the operator and the version string.
3. Compare the installed version against the parsed version using the operator.
4. If all tokens evaluate to `true`, the running instance is vulnerable.

Example: `">= 5.0.0, < 5.1.2"` → `['>= 5.0.0', '< 5.1.2']` → both must be true for the installed version.

### ExternalRequestFunctions Custom Headers

The existing `ExternalRequestFunctions` uses `file_get_contents` with a stream context. To support the required `Accept: application/vnd.github+json` header, a subclass `AdvisoriesRequest` will override the header array in the stream context, or a new constructor parameter `array $extra_headers = []` will be added to the base class. The chosen approach must be confirmed during implementation.
