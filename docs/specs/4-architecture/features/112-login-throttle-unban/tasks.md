# Feature 112 Tasks – Login Throttle Unban

_Status: Complete_  
_Last updated: 2026-11-08_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`F-`), non-goal IDs (`N-`), and scenario IDs (`S-<NNN>-`) inside the same parentheses immediately after the task title (omit categories that do not apply).
> When new high- or medium-impact questions arise during execution, add them to the feature's [open-questions.md](open-questions.md) instead of informal notes, and treat a task as fully resolved only once the governing spec sections (requirements/NFR/behaviour/telemetry) and, when required, ADRs under `docs/specs/6-decisions/` reflect the clarified behaviour.

## Checklist
- [x] T-112-01 – Service tests, red (FR-112-01, FR-112-02, FR-112-04, NFR-112-04, S-112-02, S-112-10, S-112-11, S-112-12, S-112-13).  
  _Intent:_ `tests/Unit/Services/Auth/LoginThrottleServiceTest.php` (`AbstractTestCase`): `normalizeIp` (IPv4, IPv6 forms, invalid), `prune`, `cap`, index written at attempt 10 only, lock held → no entry + warning, lockless store (FX-112-01) → throttles, no entry, `hasLocks()` false, `unban` returns true/false and resets the counter.  
  _Verification commands:_  
  - `php artisan test --filter=LoginThrottleServiceTest` (fails: class missing)

- [x] T-112-02 – Service and DTO, green (FR-112-01, FR-112-02, FR-112-04).  
  _Intent:_ `App\Services\Auth\LoginThrottleService`, `App\DTO\BlockedIp`.  
  _Verification commands:_  
  - `php artisan test --filter=LoginThrottleServiceTest`  
  - `make phpstan`

- [x] T-112-03 – Middleware tests, red (FR-112-01, NFR-112-01, S-112-01, S-112-03).  
  _Intent:_ `tests/Unit/Middleware/ThrottleLoginTest.php`: headers on attempts 1–10, 429 with `Retry-After`/`X-RateLimit-Reset`/`X-RateLimit-Limit`/`X-RateLimit-Remaining: 0` on 11, `$next` not called when blocked, no query and no `WritingKey` on the blocked request.  
  _Verification commands:_  
  - `php artisan test --filter=ThrottleLoginTest` (fails: class missing)

- [x] T-112-04 – Middleware, alias, route swap, green (FR-112-01, NFR-112-01, NFR-112-02).  
  _Intent:_ `App\Http\Middleware\ThrottleLogin`, alias `login_throttle` in `App\Http\Kernel`, `routes/api_v2.php` `/Auth::login` uses it.  
  _Verification commands (one at a time):_  
  - `php artisan test --filter=ThrottleLoginTest`  
  - `php artisan test --filter=LoginTest`  
  - `php artisan test --filter=EmailLoginTest`  
  - `php artisan test --filter=AuthTest`  
  - `php artisan test --filter=LdapAuthTest`  
  - `php artisan test --filter=RememberMeTest`  
  - `php artisan test --filter=AuthControllerTest`

- [x] T-112-05 – Command tests, red (FR-112-03, FR-112-04, FR-112-05, S-112-04 … S-112-10, S-112-13).  
  _Intent:_ `tests/Unit/Console/Commands/BlockedIpsTest.php` (table, empty, stale entry skipped, unban blocked/not blocked/invalid/IPv6 form, lockless store exit 1); `LoginTest::testUnbanFromConsoleLetsTheIpLogInAgain`.  
  _Verification commands:_  
  - `php artisan test --filter=BlockedIpsTest` (fails: command missing)  
  - `php artisan test --filter=LoginTest` (new test fails)

- [x] T-112-06 – Command, green (FR-112-03, FR-112-04, FR-112-05).  
  _Intent:_ `App\Console\Commands\BlockedIps` (`lychee:blocked-ips {--unban=}`).  
  _Verification commands:_  
  - `php artisan test --filter=BlockedIpsTest`  
  - `php artisan test --filter=LoginTest`

- [x] T-112-07 – Quality gate and docs.  
  _Intent:_ formatting, static analysis, rerun scoped classes, knowledge map, roadmap, drift gate in plan, commit command for the operator.  
  _Verification commands:_  
  - `vendor/bin/php-cs-fixer fix`  
  - `make phpstan`  
  - the classes of T-112-02, T-112-04, T-112-06, one at a time

## Notes / TODOs
- Never run two test commands at once (shared SQLite test database).
