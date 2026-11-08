# Open Questions – Feature 112

Open questions for [Feature 112](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-112-01~~ | 112 | High | Where the list of blocked IPs comes from, without the database | Resolved (A, owner 2026-11-08, spec FR-112-01/02, NFR-112-01, ADR-112-01) | 2026-11-08 | 2026-11-08 |
| ~~Q-112-02~~ | 112 | Medium | Shape of the artisan command(s) | Resolved (B, owner 2026-11-08, spec FR-112-03/04, CLI-112-01/02) | 2026-11-08 | 2026-11-08 |
| ~~Q-112-03~~ | 112 | Medium | Where the feature lives (UI or console) | Resolved (console only, owner 2026-11-08, spec Non-Goals, NFR-112-02) | 2026-11-08 | 2026-11-08 |

## Question Details

### ~~Q-112-01~~ — Where the list of blocked IPs comes from, without the database

Context: `POST /api/v2/Auth::login` uses `throttle:10,60,login`. Laravel stores the counter in the default cache store under `'login' . sha1('|' . ip)`, so the IP cannot be read back from the cache and the cache cannot be listed portably (file, redis, database, memcached). Listing needs a record of which IPs were seen.

Owner 2026-11-08: "Any database option is too heavy under lots of requests we may DOS our DB." The first round's Option A (DB table owns the throttle) and Option C (DB registry) are rejected; recorded as NFR-112-01. Today's limiter already does 2–3 cache writes per attempt and none for a request that is already blocked; the options below keep that.

Applies to every option: the console must reach the web server's cache store. `apc` (separate CLI memory) and `array` cannot work; `file` needs the command to run as the web server user (for example `www-data`).

- **Option A (recommended): cache index written only when an IP gets blocked.** The login limiter is keyed by IP (same rule: 10 attempts per 60 minutes). When an IP reaches the limit, its IP and block end go into one cache entry (`ip → blocked until`), under a cache lock, with expired entries pruned and a cap (for example the 1,000 newest). The command reads that entry and checks each IP's counter. Unban clears the IP's counter and its index entry; it also works for an IP that is not in the index.
  - Pros: exact list; no database; at most one extra cache write per blocked IP per hour; normal and blocked requests cost the same as today.
  - Cons: small change on the login path (Lychee owns the limiter key); past the cap, the oldest blocked IPs drop off the list (still blocked, unban by IP still works); Maintenance "Flush cache" still unbans everyone, as today.
- **Option B: unban by IP only, no list.** `lychee:unban-ip 1.2.3.4` computes the IP's limiter key and clears it.
  - Pros: no change on the login path; smallest change.
  - Cons: no list, which is half the request; the operator must find the IP elsewhere (for example in `storage/logs/login.log`).
- **Option C: list built from the `login` log.** The command reads the last 60 minutes of `storage/logs/login.log` (every attempt that reaches the controller is logged with its IP), then checks each IP's counter and shows the blocked ones.
  - Pros: no change on the login path; still a list.
  - Cons: depends on the log (single file that is never rotated, so it must be read from the end; path or level changed by the operator breaks it); requests rejected by validation count but are not logged; the username in each line is user input, so parsing must be defensive.

Owner 2026-11-08: "A B" → Option A. Normative outcome: spec FR-112-01, FR-112-02, NFR-112-01, ADR-112-01.

### ~~Q-112-02~~ — Shape of the artisan command(s)

- **Option A (recommended): two commands.** `php artisan lychee:blocked-ips` prints a table (IP, attempts, blocked until, minutes left) or "No blocked IP". `php artisan lychee:unban-ip {ip}` validates the IP, clears the block and says whether the IP was blocked.
  - Pros: one job per command, like `lychee:fix-tree`; easy to script.
  - Cons: two names to remember.
- **Option B: one command with an option.** `lychee:blocked-ips` lists; `lychee:blocked-ips --unban=1.2.3.4` unbans.
  - Pros: one entry point.
  - Cons: a listing command that also writes is less obvious.
- **Option C: A plus `lychee:unban-ip --all`.**
  - Pros: one call after a lockout of many IPs.
  - Cons: overlaps Maintenance "Flush cache" (which also clears everything else); one more branch to test.

Owner 2026-11-08: "A B" → Option B (`lychee:blocked-ips`, `--unban=<ip>`). Normative outcome: spec FR-112-03, FR-112-04, CLI-112-01/02; "unban all" out of scope (N-112-05).

### ~~Q-112-03~~ — Where the feature lives (UI or console)

First round offered A (new admin page in v7 and v8), B (Maintenance section), C (v8 only). Owner 2026-11-08: "We do not want a UI, we only want a console access." Resolved: artisan commands only, no web UI and no REST endpoint (spec Non-Goals, NFR-112-02).
