# Feature Plan 112 – Login Throttle Unban

_Linked specification:_ [spec.md](spec.md)  
_Linked tasks:_ [tasks.md](tasks.md)  
_Status:_ Implemented  
_Last updated:_ 2026-11-08

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in the feature's [open-questions.md](open-questions.md), and assume clarifications are resolved only when the spec’s normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/6-decisions/` have been updated.

## Vision & Success Criteria
The operator lists the IPs blocked from logging in and unbans one from the console (FR-112-03, FR-112-04). The login path keeps its contract (FR-112-01) and adds no database work and no cache write on a blocked request (NFR-112-01). All scenarios S-112-01 … 13 covered by green tests.

## Scope Alignment
- **In scope:** `ThrottleLogin` middleware replacing `throttle:10,60,login`, `LoginThrottleService`, `BlockedIp` DTO, `lychee:blocked-ips [--unban=<ip>]`, `login` channel lines TE-112-01 … 03, docs.
- **Out of scope:** N-112-01 … 06 (UI/REST, configurable limit, permanent bans, other routes, unban all, migrating old counters).

## Dependencies & Interfaces
- Laravel 13 `Illuminate\Cache\RateLimiter`, `Illuminate\Contracts\Cache\LockProvider`, `Illuminate\Contracts\Cache\LockTimeoutException`, `Illuminate\Http\Exceptions\ThrottleRequestsException`. No new package.
- `config('cache.limiter')` (absent from `config/cache.php`, so the default store), the `login` log channel (`config/logging.php`).
- Feature 111's v7/v8 login forms read the 429 status only; the header set is kept anyway.
- ADR-112-01.

## Assumptions & Risks
- **Assumptions:** HTTP tests run as `127.0.0.1` with `CACHE_STORE=array`; `ArrayStore` has locks.
- **Risks / Mitigations:**
  - Header contract drift from Laravel's middleware → `ThrottleLoginTest` asserts every header on pass and on 429.
  - Lock wait slowing the 10th login under contention → wait capped at 1 s, failure logged and ignored (S-112-12).
  - A lockless store crashing the login path → the service checks `LockProvider` before locking (S-112-13).
  - Old counters orphaned at upgrade (N-112-06) → release note line.

## Implementation Drift Gate
Run 2026-11-08 after T-112-01 … 07 (agent; owner acknowledgement pending).

| Requirement | Code | Tests (latest run green) |
|-------------|------|--------------------------|
| FR-112-01 | `ThrottleLogin`, `LoginThrottleService::isBlocked/hit/passHeaders/blockedHeaders`, `routes/api_v2.php`, `Kernel` alias | `ThrottleLoginTest` (3), `LoginTest::testLoginRateMiddleware`, `LoginThrottleServiceTest::testIpv6FormsShareOneCounter` |
| FR-112-02 | `LoginThrottleService::index/writeIndex/prune/cap` | `LoginThrottleServiceTest` (index at limit, prune on write, invalid IP, held lock, lockless, log line) |
| FR-112-03 | `BlockedIps::list`, `LoginThrottleService::blocked` | `BlockedIpsTest` (table, rounding, empty, expired) |
| FR-112-04 | `BlockedIps::unban`, `LoginThrottleService::unban/removeFromIndex` | `BlockedIpsTest` (blocked, not blocked, invalid, empty, IPv6), `LoginThrottleServiceTest` (unban ×2), `LoginTest::testUnbanFromConsoleLetsTheIpLogInAgain` |
| FR-112-05 / NFR-112-03 | `BlockedIps::handle`, `LoginThrottleService::hasLocks/storeName` | `BlockedIpsTest::testLocklessStoreIsRefused`, `LoginThrottleServiceTest` (lockless, store name) |
| NFR-112-01 | no DB use in service/middleware; blocked path reads only | `ThrottleLoginTest::testBlockedRequestRunsNoQueryAndWritesNoCache` |
| NFR-112-02 | no route added (only the `/Auth::login` middleware changed) | `git diff routes/` |
| NFR-112-04 | static `normalizeIp`, `prune`, `cap` | `LoginThrottleServiceTest` (3 pure tests) |

- Checks: `vendor/bin/php-cs-fixer fix` (0 files changed), `make phpstan` (no errors, 3,215 files), one class at a time: `LoginThrottleServiceTest` 14, `ThrottleLoginTest` 3, `BlockedIpsTest` 10, `LoginTest` 2, `EmailLoginTest` 8, `AuthTest` 2, `LdapAuthTest` 7, `RememberMeTest` 5, `AuthControllerTest` 15, `WebAuthTest` 43, `CoverageTest` 18, `OpenApiTest` 1. Smoke: `php artisan lychee:blocked-ips` on the dev instance (file store) → `No blocked IP.`, exit 0.
- Drift found and fixed: FX-112-01 moved from an in-test anonymous class to `tests/Support/Cache/LocklessArrayStore.php` (shared by two test classes); spec updated. No high/medium divergence.
- Lessons: `App\Listeners\CacheListener` runs on every cache event and reads `cache_event_logging` through `ConfigManager`; a middleware unit test that counts queries must load the configs first, as `ResolveConfigs` does in a real request. `Cache::extend()` rebinds the closure to the cache manager, so `new self()` inside it builds the manager.
- Not covered by an automated test: a real `file`-store concurrency run (two PHP workers hitting the 10th attempt at once); the lock path is covered with the array store only.

## Increment Map
1. **I1 – Service and DTO** (≈60 min)
   - _Goal:_ `LoginThrottleService` + `BlockedIp` (FR-112-01 key, FR-112-02, FR-112-04 core, NFR-112-04).
   - _Steps:_ T-112-01 tests (red), T-112-02 code (green).
   - _Commands:_ `php artisan test --filter=LoginThrottleServiceTest`, `make phpstan`.
   - _Exit:_ service tests green.
2. **I2 – Middleware and route** (≈45 min)
   - _Goal:_ `ThrottleLogin` on `/Auth::login` (FR-112-01, NFR-112-01).
   - _Steps:_ T-112-03 tests (red), T-112-04 middleware, `Kernel` alias, route swap, regression classes.
   - _Commands:_ `php artisan test --filter=ThrottleLoginTest`, `--filter=LoginTest`, `--filter=EmailLoginTest`, `--filter=AuthTest`, `--filter=LdapAuthTest`, `--filter=RememberMeTest`, `--filter=AuthControllerTest` (one at a time).
   - _Exit:_ all green.
3. **I3 – Command** (≈60 min)
   - _Goal:_ `lychee:blocked-ips [--unban=<ip>]` (FR-112-03 … 05).
   - _Steps:_ T-112-05 tests (red, command + `LoginTest` end-to-end), T-112-06 command (green).
   - _Commands:_ `php artisan test --filter=BlockedIpsTest`, `--filter=LoginTest`.
   - _Exit:_ all green.
4. **I4 – Quality gate and docs** (≈30 min)
   - _Steps:_ T-112-07: `vendor/bin/php-cs-fixer fix`, `make phpstan`, rerun I1–I3 classes, knowledge map, roadmap, drift gate, commit command for the operator.

## Scenario Tracking
| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-112-01 | I2 / T-112-03, T-112-04 | `ThrottleLoginTest`, `LoginTest::testLoginRateMiddleware` |
| S-112-02 | I1 / T-112-01, T-112-02 | `LoginThrottleServiceTest` |
| S-112-03 | I2 / T-112-03, T-112-04 | `ThrottleLoginTest` (query log, `WritingKey`) |
| S-112-04 | I3 / T-112-05, T-112-06 | `BlockedIpsTest` |
| S-112-05 | I3 / T-112-05, T-112-06 | `BlockedIpsTest` |
| S-112-06 | I3 / T-112-05, T-112-06 | `BlockedIpsTest` |
| S-112-07 | I3 / T-112-05, T-112-06 | `BlockedIpsTest`, `LoginTest` end-to-end |
| S-112-08 | I3 / T-112-05, T-112-06 | `BlockedIpsTest` |
| S-112-09 | I3 / T-112-05, T-112-06 | `BlockedIpsTest` |
| S-112-10 | I1 + I3 | `LoginThrottleServiceTest`, `BlockedIpsTest` |
| S-112-11 | I1 / T-112-01, T-112-02 | `LoginThrottleServiceTest` |
| S-112-12 | I1 / T-112-01, T-112-02 | `LoginThrottleServiceTest` (lock held by the test) |
| S-112-13 | I1 + I3 | `LoginThrottleServiceTest`, `BlockedIpsTest` (FX-112-01) |

## Analysis Gate
2026-11-08, agent review (owner decisions in open-questions.md):
1. Specification completeness – PASS: goals, FR-112-01 … 05, NFR-112-01 … 04, console mock-up (no web UI), resolved Q-112-01 … 03 folded into FR/NFR/Non-Goals.
2. Open questions – PASS: none open; ADR-112-01 covers the architecturally significant choice (throttle ownership, cache index, no DB).
3. Plan alignment – PASS: links to spec and tasks; dependencies and success criteria match the spec.
4. Tasks coverage – PASS: every FR maps to T-112-01 … 06; tests before code in each increment; success, validation and failure branches listed per scenario.
5. Working agreements – PASS: no dependency, no migration, no UI; pure helpers `normalizeIp`, `prune`, `cap` (NFR-112-04); no ADR referenced Feature 112 before this one.
6. Tooling – PASS: commands listed per increment.

## Exit Criteria
- T-112-01 … 07 checked, scoped test classes green (run one at a time), `make phpstan` and `php-cs-fixer` clean.
- No route added (NFR-112-02), no file under `resources/js` changed.
- Roadmap and knowledge map updated, drift gate recorded, commit command handed to the operator.

## Follow-ups / Backlog
- Release note: "Login throttle counters are reset on upgrade" (N-112-06) and "`php artisan lychee:blocked-ips` lists and unbans IPs blocked from logging in".

## Intent & Tool Usage
Owner request (2026-11-08): "When doing more than 10 login attempts we are throttled for 1h. we want to be able to see the list of banned ip and unban one of them." Read `routes/api_v2.php`, Laravel `ThrottleRequests`/`RateLimiter`/cache stores, `AuthController`, Feature 111 docs. First round of questions offered DB options and a UI; owner: "Any database option is too heavy under lots of requests we may DOS our DB", "We do not want a UI, we only want a console access", then "A B".
