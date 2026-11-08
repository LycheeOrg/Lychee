# ADR-112-01: Login throttle owned by Lychee, blocked IPs indexed in the cache

- **Status:** Accepted
- **Date:** 2026-11-08
- **Related features/specs:** Feature 112 (docs/specs/4-architecture/features/112-login-throttle-unban/spec.md)
- **Related open questions:** Q-112-01, Q-112-02, Q-112-03

## Context

`POST /api/v2/Auth::login` was throttled by Laravel's `throttle:10,60,login`, which keys the cache on `'login' . sha1('|' . ip)`. The IP cannot be read back from that key, and no cache driver offers a portable way to list keys, so the operator could neither see nor lift a block. The owner rejected any database storage ("Any database option is too heavy under lots of requests we may DOS our DB") and asked for console access only. Today's limiter does 2–3 cache writes per attempt and none for a request that is already blocked.

Affected modules: HTTP middleware (login route), a new auth service, artisan commands.

## Decision

- A Lychee middleware `App\Http\Middleware\ThrottleLogin` (alias `login_throttle`) replaces `throttle:10,60,login`. It keeps the rule (10 attempts per IP in a 60-minute window started by the first attempt, 429 with the same headers) and keys Laravel's `RateLimiter` on `login-throttle:<canonical ip>`, so a given IP's counter can be found and cleared.
- `App\Services\Auth\LoginThrottleService` owns the rule. The hit that brings an IP to 10 adds `ip → block end` to one cache entry `login-throttle:blocked`, under a cache lock (wait ≤1 s), pruned of ended blocks and capped at 1,000 entries. Blocked requests and attempts 1–9 never touch the index.
- `php artisan lychee:blocked-ips` lists the indexed IPs whose counter still blocks them; `--unban=<ip>` clears the counter and the entry. A store without locks (`apc`) is refused by the command and never indexed.
- No database table, no REST endpoint, no UI.

## Consequences

### Positive
- No database load from login attempts, whatever the attack rate.
- Normal and blocked requests cost what they cost before; one extra cache write per blocked IP per hour.
- Counters and index live in the same store and expire on the same clock.
- An IP can be unbanned even when it is missing from the index.

### Negative
- Lychee code replaces Laravel's middleware on this route; the header contract must be kept by hand.
- Past 1,000 blocked IPs in one hour, the oldest drop off the list (still blocked, still unbannable by IP).
- A lock timeout (heavy contention) leaves an IP out of the list; logged on the `login` channel.
- Maintenance "Flush cache" still unbans everyone, as before.
- Counters made by the old key are orphaned at upgrade (expire within 60 minutes).
- `apc` installs cannot use the command (the CLI does not share the web server's APC memory either).

## Alternatives Considered

- First round A – a database table owns the throttle: exact and listable; rejected by the owner (database load under attack).
- First round C – cache limiter plus a database registry: rejected for the same reason, and two stores that drift.
- B – unban by IP only, no list: no change on the login path, but only half of the request.
- C – list built from `storage/logs/login.log`: no change on the login path, but depends on log path, level and rotation; validation failures count but are not logged; usernames in lines are user input.

## Security / Privacy Impact

- IP addresses are kept at most 60 minutes in the cache, the same lifetime as the counters they describe, and are already written to `login.log`.
- No credentials are logged; the new log lines carry the IP only.
- The command is reachable only by someone with shell access to the instance; no new network surface.
- Keying on the IP for signed-in callers too (Laravel used the user id) removes a way to sidestep the IP limit by posting with a session.

## Operational Impact

- Run `php artisan lychee:blocked-ips` as the web server user when `CACHE_STORE=file`, so the command reads and deletes the same files.
- Behind a reverse proxy, `TRUSTED_PROXIES` must be set, otherwise every client shares the proxy IP (unchanged).

## Links

- Related spec sections: `docs/specs/4-architecture/features/112-login-throttle-unban/spec.md#functional-requirements`, `#non-functional-requirements`
- Related ADRs: none
- Related issues / PRs: Feature 111 (#4964, 429 feedback in the login forms)
