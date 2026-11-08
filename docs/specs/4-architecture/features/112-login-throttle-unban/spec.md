# Feature 112 – Login Throttle Unban

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-11-08 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #112 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
After 10 login attempts an IP is blocked from `POST /api/v2/Auth::login` for the rest of a 60-minute window (HTTP 429, inline feedback since Feature 111). The operator cannot see which IPs are blocked and cannot lift a block, short of flushing the whole cache. This feature gives the operator one artisan command that lists the blocked IPs and unbans one. Affected layers: login throttling (a Lychee middleware replaces `throttle:10,60,login`) and artisan commands. Constraints: no database on the login path (NFR-112-01), console only (NFR-112-02), cache index written when an IP gets blocked (Q-112-01 A, [ADR-112-01](../../../6-decisions/ADR-112-01-login-throttle-cache-index.md)).

## Goals
- From the console, the operator sees which IPs are currently blocked from logging in, and until when.
- From the console, the operator can unban one IP, which can log in again immediately.
- The login path costs the same as today for normal and blocked requests.

## Non-Goals
- N-112-01: Any web UI (v7 or v8) or REST endpoint (Q-112-03, owner 2026-11-08).
- N-112-02: Changing the limit (10 attempts per 60 minutes per IP) or making it configurable.
- N-112-03: Permanent bans, allow-lists or block-lists.
- N-112-04: Other throttled routes (Gallery/Album unlock, Contact, embed, …).
- N-112-05: An "unban all" option (Maintenance "Flush cache" already clears every counter).
- N-112-06: Keeping counters made by the old `throttle:10,60,login` key: they are orphaned at upgrade and expire within 60 minutes.

## Functional Requirements
| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-112-01 | `POST /api/v2/Auth::login` allows 10 attempts per IP in a 60-minute window that starts at the first attempt. Every attempt counts, successful ones included. The counter key is `login-throttle:<ip>` with the IP in canonical form (`inet_pton`/`inet_ntop`), for guests and signed-in callers alike. | Attempts 1–10 reach the controller; responses carry `X-RateLimit-Limit: 10` and `X-RateLimit-Remaining`. | An IP that is not a valid address (empty in tests) is keyed as given and never indexed. | Attempt 11+ in the window gets `ThrottleRequestsException` (429) with `Retry-After`, `X-RateLimit-Reset`, `X-RateLimit-Limit`, `X-RateLimit-Remaining: 0`, and the controller does not run. | None added on normal attempts. | Today's rule (spec appendix), Q-112-01 A |
| FR-112-02 | The hit that brings an IP to 10 attempts adds `ip → block end (unix time)` to one cache entry `login-throttle:blocked` in the limiter's store. The write runs under the cache lock `login-throttle:blocked:lock` (lock TTL 5 s, wait at most 1 s), drops entries whose block has ended, keeps the 1,000 latest block ends, and stores the entry for 60 minutes. | One index write per IP per window. | Attempts 1–9 write nothing to the index. Invalid IPs are not indexed. | Lock not obtained within 1 s: no index write, the login request carries on unchanged, TE-112-03. Store without locks: no index write. | TE-112-01 on success. | Q-112-01 A, ADR-112-01 |
| FR-112-03 | `php artisan lychee:blocked-ips` prints the indexed IPs that the limiter still blocks, newest block end first, as a table: IP, Attempts, Blocked until (app timezone, `Y-m-d H:i:s T`), Minutes left (rounded up). | Exit 0. | Nobody blocked → `No blocked IP.`, exit 0. Index entries whose counter is gone (expired, unbanned, cache flushed) are not shown. | Store without locks → FR-112-05. | None. | Q-112-02 B |
| FR-112-04 | `php artisan lychee:blocked-ips --unban=<ip>` clears the IP's counter (blocked or not) and removes its index entry, so the IP gets 10 fresh attempts. Works for an IP missing from the index. The IP is matched in canonical form (an expanded IPv6 address unbans the compressed one). | Blocked → `Unbanned <ip>.`, exit 0. Not blocked → `<ip> is not blocked.`, exit 0. | Not a valid IPv4/IPv6 address → `<value> is not a valid IP address.`, exit 2, nothing changed. | Lock not obtained within 5 s: the counter is still cleared (the list skips the stale entry). Store without locks → FR-112-05. | TE-112-02 when an IP was blocked. | Q-112-02 B |
| FR-112-05 | When the limiter's store has no lock support (for example `apc`), both forms of the command print `The login throttle uses the cache store '<name>', which has no locks (for example apc): blocked IPs cannot be listed or unbanned from the console.` and exit 1 without touching the cache. | – | – | Exit 1. | None. | NFR-112-03 |

## Non-Functional Requirements
| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-112-01 | No database read or write is added on the `/Auth::login` request path: throttle state stays in the cache store the limiter uses (`cache.limiter`, else the default store). A request that is already blocked adds no cache write. | A brute-force burst must not load the database. | `ThrottleLoginTest`: no query logged by the middleware; no `WritingKey` event on a blocked request. | Laravel cache `RateLimiter`. | Owner 2026-11-08 ("Any database option is too heavy…", Q-112-01) |
| NFR-112-02 | Access is through the artisan command only. | Owner scope. | No new route in `routes/`. | `app/Console/Commands`. | Q-112-03 (owner 2026-11-08) |
| NFR-112-03 | The command acts on the web server's cache store and says so when it cannot (FR-112-05) instead of reporting an empty list. `array` is not refused: it is the test store (`phpunit.xml`). With the `file` store, the operator runs the command as the web server user. | A wrong "No blocked IP." would mislead the operator. | `BlockedIpsTest` with a lockless test store. | `config/cache.php`. | Lightweight clarification, 2026-11-08 |
| NFR-112-04 | IP normalisation, index pruning and capping are pure static helpers, tested without the cache. | Straight-line increments (AGENTS.md). | `LoginThrottleServiceTest`. | – | AGENTS.md |

## UI / Interaction Mock-ups
No web UI (N-112-01). Console output:

```text
$ php artisan lychee:blocked-ips
+---------------+----------+-------------------------+--------------+
| IP            | Attempts | Blocked until           | Minutes left |
+---------------+----------+-------------------------+--------------+
| 203.0.113.7   | 10       | 2026-11-08 15:42:10 UTC | 37           |
| 2001:db8::1   | 10       | 2026-11-08 15:05:51 UTC | 1            |
+---------------+----------+-------------------------+--------------+

$ php artisan lychee:blocked-ips
No blocked IP.

$ php artisan lychee:blocked-ips --unban=203.0.113.7
Unbanned 203.0.113.7.

$ php artisan lychee:blocked-ips --unban=198.51.100.4
198.51.100.4 is not blocked.

$ php artisan lychee:blocked-ips --unban=nope
nope is not a valid IP address.            (exit 2)
```

## Branch & Scenario Matrix
| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-112-01 | 10 attempts pass, the 11th gets 429 with `Retry-After` (FR-112-01). |
| S-112-02 | The 10th attempt indexes the IP once; attempts 1–9 do not (FR-112-02). |
| S-112-03 | A blocked request runs no query and writes nothing to the cache (NFR-112-01). |
| S-112-04 | The list shows a blocked IP with attempts, block end and minutes left, newest first (FR-112-03). |
| S-112-05 | Nobody blocked → `No blocked IP.`, exit 0 (FR-112-03). |
| S-112-06 | An index entry whose counter is gone is not listed (FR-112-03). |
| S-112-07 | Unban a blocked IP → `Unbanned …`, it leaves the list, its next login reaches the controller (401, not 429) (FR-112-04). |
| S-112-08 | Unban an IP that is not blocked → `… is not blocked.`, exit 0, counter reset (FR-112-04). |
| S-112-09 | Unban an invalid value → error, exit 2 (FR-112-04). |
| S-112-10 | An expanded IPv6 address unbans the compressed one (FR-112-04). |
| S-112-11 | Index writes drop ended blocks and keep the 1,000 latest (FR-112-02). |
| S-112-12 | Lock held elsewhere → the 10th login carries on, no index entry, TE-112-03 (FR-112-02). |
| S-112-13 | Lockless store → the command exits 1 with FR-112-05's message; the middleware still throttles and writes no index (FR-112-02, FR-112-05). |

## Test Strategy
Tests are written first and must fail before the code exists.
- **Service (unit):** `tests/Unit/Services/Auth/LoginThrottleServiceTest` (`AbstractTestCase`): `normalizeIp`, `prune`, `cap` (S-112-10, S-112-11), indexing at the limit (S-112-02), lock held (S-112-12), lockless store (S-112-13), unban result (S-112-07, S-112-08).
- **Middleware (unit):** `tests/Unit/Middleware/ThrottleLoginTest`: headers on pass and on 429, no controller call when blocked (S-112-01), no query and no `WritingKey` on a blocked request (S-112-03).
- **Command:** `tests/Unit/Console/Commands/BlockedIpsTest`: S-112-04 … S-112-09, S-112-13.
- **REST (v2):** `tests/Feature_v2/User/LoginTest` keeps `testLoginRateMiddleware` (S-112-01) and gains an end-to-end unban through the command (S-112-07).
- **Regression:** `EmailLoginTest`, `AuthTest`, `LdapAuthTest`, `RememberMeTest`, `AuthControllerTest` (they flush the cache around login calls).
- **Frontend:** none (no UI; Feature 111's 429 handling relies on the status only).

## Interface & Contract Catalogue
### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-112-01 | `App\DTO\BlockedIp` (readonly): `ip` (canonical string), `attempts` (int), `blocked_until` (unix time, int). | services, console |
| DO-112-02 | Cache entry `login-throttle:blocked`: `array<string ip, int block_end>`, at most 1,000 entries, TTL 3,600 s. Lock `login-throttle:blocked:lock`. Counters `login-throttle:<ip>` and `login-throttle:<ip>:timer` (Laravel `RateLimiter`). | services |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-112-01 | REST POST /api/v2/Auth::login | Middleware `login_throttle` (`App\Http\Middleware\ThrottleLogin`) replaces `throttle:10,60,login`. Response contract unchanged. | FR-112-01 |
| SVC-112-01 | `App\Services\Auth\LoginThrottleService` | `isBlocked`, `hit`, `headers`, `blocked`, `unban`, `hasLocks`, `storeName`; static `normalizeIp`, `prune`, `cap`. Store = `cache.limiter`, else default. | FR-112-01 … 05 |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-112-01 | `php artisan lychee:blocked-ips` | Lists blocked IPs (FR-112-03). |
| CLI-112-02 | `php artisan lychee:blocked-ips --unban=<ip>` | Unbans one IP (FR-112-04). |

### Telemetry Events
| ID | Event name | Fields / Redaction rules |
|----|-----------|---------------------------|
| TE-112-01 | `login` channel notice: `IP <ip> blocked from login until <ISO 8601>` | IP only; no username or password. Once per IP per window. |
| TE-112-02 | `login` channel notice: `IP <ip> unbanned from the console` | IP only. |
| TE-112-03 | `login` channel warning: `Blocked-IP list not updated for <ip>: lock timeout` | IP only. |

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-112-01 | `tests/Support/Cache/LocklessArrayStore.php` (`Store` over `ArrayStore`, no locks), registered with `Cache::extend` as `lockless` | Simulates `apc` for S-112-13. |

### UI States
None (N-112-01).

## Telemetry & Observability
TE-112-01 … 03 go to the existing `login` channel (`storage/logs/login.log`), which already records each attempt with its IP. Nothing else is logged; usernames and passwords never appear in these lines.

## Documentation Deliverables
Roadmap row 112, knowledge-map entry (login throttle), ADR-112-01, this spec, plan, tasks, open questions.

## Fixtures & Sample Data
FX-112-01 only. Test IPs from the documentation ranges (`203.0.113.0/24`, `198.51.100.0/24`, `2001:db8::/32`) and `127.0.0.1` for HTTP tests.

## Spec DSL
```yaml
domain_objects:
  - id: DO-112-01
    name: BlockedIp
    fields:
      - {name: ip, type: string}
      - {name: attempts, type: integer}
      - {name: blocked_until, type: integer, unit: unix_seconds}
  - id: DO-112-02
    name: login-throttle:blocked
    type: cache_entry
    shape: "map<ip, block_end>"
    max_entries: 1000
    ttl_seconds: 3600
routes:
  - id: API-112-01
    method: POST
    path: /api/v2/Auth::login
    middleware: login_throttle
    limit: {attempts: 10, window_seconds: 3600, key: canonical_ip}
cli_commands:
  - id: CLI-112-01
    command: php artisan lychee:blocked-ips
  - id: CLI-112-02
    command: php artisan lychee:blocked-ips --unban=<ip>
    exit_codes: {ok: 0, lockless_store: 1, invalid_ip: 2}
telemetry_events:
  - {id: TE-112-01, channel: login, level: notice}
  - {id: TE-112-02, channel: login, level: notice}
  - {id: TE-112-03, channel: login, level: warning}
fixtures:
  - id: FX-112-01
    path: tests/Support/Cache/LocklessArrayStore.php
ui_states: []
```

## Appendix – Behaviour before this feature (2026-11-08, `master` 9094dacbb)
- Route: `routes/api_v2.php:168`, `Route::post('/Auth::login', …)->middleware('throttle:10,60,login')`.
- `Illuminate\Routing\Middleware\ThrottleRequests::handle` → unnamed limit, key `'login' . sha1(<route domain> . '|' . $request->ip())` (no route domain, so `sha1('|' . ip)`), or the user id when signed in, stored in the limiter's cache store (`file` by default) with a `:timer` entry.
- Every request counts, successful logins included (the hit happens before the controller). The window starts at the first attempt and lasts 60 minutes, so the block ends 60 minutes after the first attempt, not after the 11th.
- The 11th request in the window gets `ThrottleRequestsException` (429, `Retry-After`).
- `$request->ip()` depends on `TrustProxies` (`TRUSTED_PROXIES`); behind an untrusted proxy all clients share the proxy IP.
- Maintenance "Flush cache" (`App\Http\Controllers\Admin\Maintenance\FlushCache`, `Cache::flush()`) clears every counter, which unbans every IP. Unchanged by this feature.
- `AuthController::login` already logs every attempt with the IP on the `login` log channel.
- Only `/Auth::login` uses the `login` prefix. WebAuthn login has no throttle.
- Cache stores without `LockProvider` in Laravel 13: `ApcStore` only (file, array, database, redis, memcached, dynamodb have locks).
