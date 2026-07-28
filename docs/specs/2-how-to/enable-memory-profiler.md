# How to Enable and Use the Memory Profiler

The Memory Profiler (Feature 053) captures a per-request memory-allocation profile using the [`spx`](https://github.com/NoiseByNorthwest/php-spx) PHP extension and lets the instance owner browse captured traces at `/admin/profiler`, opening each one in SPX's own bundled analysis screen (flame graph / timeline / call tree). This guide explains how to install, configure, and use it — and, importantly, how access to the analysis screen is protected.

---

**This is an optional, opt-in debugging tool, not recommended for continuous production use.**

## Why `spx`, not `memprof`

The extension originally considered was [`memprof`](https://github.com/arnaud-lb/php-memory-profiler). It cannot be used at all on Lychee's official Docker image: that image's PHP build is **ZTS** (Zend Thread Safety, required by FrankenPHP for its worker model), and `memprof`'s current release refuses to compile against ZTS builds (`#error "ZTS build not supported (yet)"` — a long-standing, unresolved upstream limitation, [tracked here](https://github.com/arnaud-lb/php-memory-profiler/issues/24)). `spx` explicitly supports ZTS and was verified to compile and load on the exact same base image, so it's the engine this feature actually uses. See [ADR-0008](../6-decisions/ADR-0008-memory-profiler-octane-risk.md) for the full history.

`spx` also tracks allocation *and* free counts/bytes per call path (not just usage deltas like XHProf-family tools), which is what makes it useful for hunting leaks rather than just "what's using memory right now."

## What's bundled

`spx` is installed by default in the official Docker image (`install-php-extensions spx`, plus its `zlib1g-dev` build dependency). Non-Docker/bare-metal installs need to install it manually:

```bash
# Debian or Ubuntu:
apt-get install zlib1g-dev
pie install noisebynorthwest/php-spx
# or
pecl install spx
```

Enable it in `php.ini` (or via `-d`):

```ini
extension=spx.so
```

Verify:

```bash
php -m | grep -i spx
```

## Correctness under Octane/FrankenPHP

Lychee's default production runtime (the official Docker image's `web` mode) runs under Laravel Octane with FrankenPHP, which keeps a single PHP worker thread alive across many requests. This was a real concern during implementation — a naive "always profiling" configuration might not isolate memory correctly per request when the same thread serves many of them.

**This was verified empirically, not just assumed.** Two consecutive HTTP requests (allocating different amounts of memory) were sent to the same running worker thread; both were confirmed to be handled by the identical OS thread, yet each produced an independently correct peak-memory reading (not cumulative). This is why the feature uses manual `spx_profiler_start()`/`spx_profiler_stop()` spans (`spx.http_profiling_auto_start=0`) rather than SPX's own ini-only "always profiling" mode — SPX's own documentation recommends exactly this pattern for persistent-worker runtimes. See [ADR-0008](../6-decisions/ADR-0008-memory-profiler-octane-risk.md) for the full test.

## Usage

### 1. Enable the feature

Set the following in your `.env` file:

```env
MEMORY_PROFILER_ENABLED=true
MEMORY_PROFILER_SPX_KEY=<a long random secret — see "Securing the analysis screen" below>
```

Restart the container/application for the change to take effect. Unlike a normal Laravel feature flag, `spx`'s own settings are `PHP_INI_SYSTEM` — they're written by `docker/scripts/06-configure-profiler.sh` at container start (from these same env vars), not read at request time, so a restart is required either way.

### 2. Profile requests

Once enabled, every request is profiled automatically (there is no per-request opt-in trigger — see the feature's spec, Q-053-04). A metadata sidecar (`.json`) is written to `storage/profiling` for each request, alongside SPX's own report files.

### 3. Browse traces

Log in as the instance owner (the user whose ID matches `config('owner_id')` — normally the first admin account created) and open:

```
<your-host>/admin/profiler
```

Each row shows the route, method, status, duration, and peak memory for that request. Rows with a captured SPX report show an "open in SPX" link.

### 4. Securing the analysis screen

**This is the most important part of the setup.** Clicking "open in SPX" takes you to a URL like:

```
<your-host>/?SPX_UI_URI=/report.html&SPX_KEY=<your key>&key=<report key>
```

This request is intercepted by the `spx` extension itself, **before Laravel's router or any middleware runs** — including Lychee's own owner-only gate. In other words, anyone who knows (or guesses) `MEMORY_PROFILER_SPX_KEY` can open the same analysis screen directly, bypassing Lychee's login entirely. This is a deliberate trade-off (not a bug) accepted so the feature can reuse SPX's own, already-built viewer instead of Lychee reimplementing one. To mitigate it:

- **Always set `MEMORY_PROFILER_SPX_KEY` to a long, random, unguessable value.** Generate one with:
  ```bash
  openssl rand -hex 32
  ```
  There is no default — the feature intentionally does not ship a guessable fallback.
- **Set `MEMORY_PROFILER_SPX_IP_WHITELIST`** to a comma-separated list of trusted IPs (e.g. your own office/VPN range) if your deployment allows it. This maps to `spx.http_ip_whitelist`.
- Only enable this feature during active debugging sessions, and disable it (`MEMORY_PROFILER_ENABLED=false`) otherwise.

### 5. Retention

`storage/profiling` is automatically pruned to the newest `MEMORY_PROFILER_MAX_TRACES` traces (default 200), both on a daily schedule and via the "Prune old traces" button on the admin page. Adjust the cap with:

```env
MEMORY_PROFILER_MAX_TRACES=200
```

Prune manually at any time:

```bash
php artisan lychee:profiler:prune
```

## Troubleshooting

- **Admin page shows "No traces collected yet"** — confirm `MEMORY_PROFILER_ENABLED=true` and `php -m | grep spx` shows the extension loaded on the process actually serving requests (not just your CLI's `php`).
- **A row has no "open in SPX" link** — either that request didn't produce an SPX report key, or `MEMORY_PROFILER_SPX_KEY` isn't set.
- **`install-php-extensions spx` / `pecl install spx` fails to compile** — check you've installed `zlib1g-dev` (or your distribution's zlib development package) first.
- **The analysis-screen link 404s or shows the normal Lychee page instead of SPX's viewer** — double-check `MEMORY_PROFILER_SPX_KEY` matches exactly, and that the extension is actually loaded (see above).

---

*Last updated: 2026-07-28*
