# How to Enable and Use the Memory Profiler

The Memory Profiler (Feature 053) captures a per-request memory-allocation profile using the [`memprof`](https://github.com/arnaud-lb/php-memory-profiler) PHP extension and lets the instance owner browse the resulting traces — including as an SVG call-graph — at `/admin/profiler`. This guide explains how to install, configure, and use it.

---

**This is an optional, opt-in debugging tool, not recommended for continuous production use.** Like [XHProf](enable-hprof.md), the profiling extension itself is not bundled by default and must be installed separately.

**⚠ Unverified under Laravel Octane/FrankenPHP.** Lychee's default production runtime (the official Docker image's `web` mode) runs under Laravel Octane with FrankenPHP, which keeps a single PHP process alive across many requests. `memprof`'s documented behaviour assumes a traditional per-request PHP-FPM/CLI lifecycle; whether its per-request accounting stays correctly scoped inside a persistent Octane worker is currently unverified. See [ADR-0008](../6-decisions/ADR-0008-memory-profiler-octane-risk.md) for the full risk assessment. When enabled, the admin page shows a warning banner while running under Octane. If in doubt, profile using a non-Octane runtime (e.g. `php artisan serve`) for the most trustworthy results.

## What's bundled vs. what you install yourself

| Dependency | Bundled in the official Docker image? | Purpose |
|---|---|---|
| `memprof` PHP extension | **No** — install manually (see below) | Captures the actual memory-allocation profile |
| `pprof`/`google-pprof` CLI (`google-perftools` package) | **Yes** | Renders a `.pprof` dump as an SVG call-graph |
| Graphviz (`dot` binary) | **Yes** | Invoked internally by `pprof --svg` |

If you're running Lychee outside the official Docker image (bare-metal, a custom image, etc.), you'll need to install `google-perftools` and `graphviz` yourself too — see below.

## Installation

### 1. Install the `memprof` extension

**Dependencies:** `memprof` depends on `libjudy` and `sys/queue.h`.

```bash
# Debian or Ubuntu:
apt-get install libjudy-dev
```

**Install with [PIE](https://github.com/php/pie) (recommended) or PECL:**

```bash
pie install arnaud-lb/memprof
# or
pecl install memprof
```

Enable it in `php.ini` (or via `-d`):

```ini
extension=memprof.so
```

Verify:

```bash
php -m | grep memprof
```

If you're extending the official Docker image, add these two lines to your own `Dockerfile` (after the base `install-php-extensions` block):

```dockerfile
RUN apt-get update && apt-get install -y --no-install-recommends libjudy-dev \
    && install-php-extensions memprof
```

### 2. `pprof`/`google-pprof` and Graphviz

Already installed in the official Docker image (`google-perftools` + `graphviz`). For non-Docker installs:

```bash
# Debian or Ubuntu:
apt-get install google-perftools graphviz
```

On Debian/Ubuntu the binary is named `google-pprof` (not `pprof`) — this is Lychee's default (`MEMORY_PROFILER_PPROF_BIN=google-pprof`). If your distribution names it differently, set `MEMORY_PROFILER_PPROF_BIN` accordingly.

## Usage

### 1. Enable the feature

Set the following in your `.env` file:

```env
MEMORY_PROFILER_ENABLED=true
```

Restart the application (or your Octane workers) for the change to take effect.

### 2. Profile requests

Once enabled, every request is profiled automatically (there is no per-request opt-in trigger — see the feature's spec, Q-053-04) and a trace pair (`.pprof` + `.json` metadata) is written to `storage/profiling`.

### 3. Browse traces

Log in as the instance owner (the user whose ID matches `config('owner_id')` — normally the first admin account created) and open:

```
<your-host>/admin/profiler
```

From there, open any trace to render it as an SVG call-graph, or download the raw `.pprof` dump to inspect with your own tooling (e.g. `pprof --web`, `pprof --text`).

### 4. Retention

`storage/profiling` is automatically pruned to the newest `MEMORY_PROFILER_MAX_TRACES` trace pairs (default 200), both on a daily schedule and via the "Prune old traces" button on the admin page. Adjust the cap with:

```env
MEMORY_PROFILER_MAX_TRACES=200
```

Prune manually at any time:

```bash
php artisan lychee:profiler:prune
```

## Troubleshooting

- **Admin page shows "No traces collected yet"** — confirm `MEMORY_PROFILER_ENABLED=true` and `php -m | grep memprof` shows the extension loaded on the process actually serving requests (not just your CLI's `php`).
- **"Could not render this trace" on the SVG view** — `google-pprof` (or your configured `MEMORY_PROFILER_PPROF_BIN`) or Graphviz's `dot` isn't installed/found on `PATH`. Install them per the steps above, or download the raw `.pprof` dump and render it locally with your own `pprof` install.
- **Traces look identical or cumulative across requests** — you may be running under Octane/FrankenPHP; see the warning above and ADR-0008.

---

*Last updated: 2026-07-28*
