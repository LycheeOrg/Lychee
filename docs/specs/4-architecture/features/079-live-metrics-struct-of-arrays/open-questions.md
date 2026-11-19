# Open Questions – Feature 079

Open questions for [Feature 079](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-079-01~~ | 079 – Live metrics SoA | Medium | Scope: endpoint only, or endpoint plus the v8 `LiveMetrics.vue` drawer switching to it? (A: both, B: endpoint only) | Resolved (Option A — endpoint plus v8 drawer adoption; owner, 2026-10-03; spec FR-079-09) | 2026-10-03 | 2026-10-03 |
| ~~Q-079-02~~ | 079 – Live metrics SoA | High | Where is the grouping done: one row per event (client groups as today) or pre-aggregated in SQL? (A: raw rows, B: SQL group by minute, C: full server grouping) | Resolved (Option B — SQL `GROUP BY` per minute with `counts[]`; owner, 2026-10-03; spec FR-079-06) | 2026-10-03 | 2026-10-03 |
| ~~Q-079-03~~ | 079 – Live metrics SoA | High | Bounding strategy (ADR-069-01): capped with truncation, or whole-scope bounded by retention? (A: capped + config, B: whole-scope) | Resolved (Option A — capped with truncation, `live_metrics_result_limit`; owner, 2026-10-03; spec FR-079-07) | 2026-10-03 | 2026-10-03 |
| ~~Q-079-04~~ | 079 – Live metrics SoA | Medium | The v2 GET deletes expired rows before reading. What does v3 do? (A: filter + `defer()` cleanup, B: synchronous delete, C: filter only) | Resolved (owner option — cleanup mode is an admin setting `live_metrics_cleanup`: `deferred`\|`sync`\|`disabled`; owner, 2026-10-03; spec FR-079-08) | 2026-10-03 | 2026-10-03 |
| ~~Q-079-05~~ | 079 – Live metrics SoA | Medium | Add an index on `live_metrics.created_at` (persistence change)? (A: yes, B: no) | Resolved (Option A — index on `created_at`; owner, 2026-10-03; spec NFR-079-05) | 2026-10-03 | 2026-10-03 |

## Question Details

### ~~Q-079-01~~ – Scope of the feature

**Context:** The only consumer of `GET /api/v2/Metrics` is the v8 drawer `resources/js/v8/components/drawers/LiveMetrics.vue` (v7 has none). Earlier SoA features split backend (062, 064) from frontend adoption (063, 065) because each adoption was large. Here the adoption is one service method plus the loader in a single component.

- **Option A (recommended):** endpoint + v8 drawer adoption behind `is_struct_of_array_enabled`, in this feature. Pros: the speed gain reaches users; one test cycle; adoption is about one increment. Cons: slightly larger than the literal request.
- **Option B:** endpoint only; drawer adoption as a follow-up feature. Pros: matches the request literally; smallest change. Cons: the endpoint has no consumer and no browser verification until the follow-up lands.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-079-09.

### ~~Q-079-02~~ – Grouping: raw events or SQL aggregation

**Context:** The drawer groups events by `(action, relative-time label, photo_id ?? album_id)` and shows a count. The labels are relative to *now* and uneven (<1 min, 1–5 min, then each minute from 6 to 15, then 15–30, 30–60, each hour, each day). Most of the v2 cost is not the row count. It comes from Eloquent hydration, five eager-loaded relations (`photo`, `photo.size_variants`, `album`, `album_impl`, `album.thumb`) and URL generation for each row.

- **Option A (recommended):** one row per event. A single `toBase()` query with no hydration, no size variants and no URLs; the client keeps its O(n) grouping unchanged. Pros: simplest SQL, works the same on all three DB drivers, no change to the grouping behaviour; removes the actual bottleneck. Cons: payload grows linearly with event count (bounded by Q-079-03).
- **Option B:** `GROUP BY action, album_id, photo_id, <created_at floored to the minute>` with a `counts[]` column; the client merges minute buckets into its labels. Pros: fewer rows on busy instances. Cons: floor-to-minute is driver-specific SQL (SQLite `strftime`, MySQL `DATE_FORMAT`, PostgreSQL `date_trunc`); the gain is small for older events, which are one row each per minute anyway.
- **Option C:** the server computes the relative labels and returns final groups. Pros: smallest payload. Cons: moves i18n-adjacent presentation logic to PHP; results depend on server "now" versus client clock; the most work.

**Resolution:** Option B, recorded in [spec.md](spec.md) FR-079-06 and DO-079-01.

### ~~Q-079-03~~ – Bounding strategy (ADR-069-01)

**Context:** ADR-069-01 requires every v3 collection endpoint to name its bounding strategy. Rows are kept for `live_metrics_max_time` days, but volume depends on traffic: a busy public gallery can log tens of thousands of events in that window. The drawer is a non-virtualised list, so the client cost grows with the row count too.

- **Option A (recommended):** capped with truncation. Fetch `cap + 1` newest rows, return `cap` with `is_truncated`; a new admin config `live_metrics_result_limit` (default 1000, category `Mod Pro`/metrics) plus its 22 locale entries. Pros: hard upper bound on query, payload and DOM; newest-first means the truncated part is the least interesting. Cons: a new config key with its documentation and locale entries; the drawer must show a truncation hint (only if Q-079-01 = A).
- **Option B:** whole-scope, bounded by `live_metrics_max_time`. Pros: no new config; same behaviour as v2. Cons: no upper bound on busy instances; contradicts the "speed is of essence" goal.

**Resolution:** Option A, recorded in [spec.md](spec.md) FR-079-07 and DO-079-01.

### ~~Q-079-04~~ – Expired-row cleanup on the read path

**Context:** `MetricsController::get()` runs `CleanupMetrics::do()` (`DELETE … WHERE created_at <= now - N days`) before every read. With no index on `created_at`, that is a full table scan plus a write lock on every drawer open. The codebase has no scheduler entries and does not use `defer()` yet (Laravel 13 supports it).

- **Option A (recommended):** the v3 query filters `created_at > now - N days` itself, so expired rows never appear; the delete runs via `defer()` after the response is sent. Pros: the response never waits on the delete; the table still gets pruned. Cons: first use of `defer()` in the codebase; under PHP-FPM the worker stays busy until the delete finishes.
- **Option B:** keep the synchronous delete, as in v2. Pros: identical to v2; no new mechanism. Cons: every request pays for the delete.
- **Option C:** filter only; v3 never deletes, and pruning stays with the v2 route. Pros: v3 GET is strictly read-only. Cons: once the drawer moves to v3, nothing prunes the table, so it grows without bound.
- **Owner option (chosen):** the behaviour is an admin setting, `live_metrics_cleanup` = `deferred` (default) \| `sync` \| `disabled`, applied to both the v2 and the v3 GET. Both reads filter on the retention window, so `deferred` and `disabled` never show expired rows. `deferred` registers the delete with `app()->terminating()`, which runs after the response is sent and needs no global middleware (`defer()` would need `InvokeDeferredCallbacks`, which `app/Http/Kernel.php` does not register).

**Resolution:** owner option, recorded in [spec.md](spec.md) FR-079-08.

### ~~Q-079-05~~ – Index on `live_metrics.created_at`

**Context:** `live_metrics` has indexes on `visitor_id` and `action` only. The v3 query orders by `created_at DESC` (with a `LIMIT` under Q-079-03 A) and filters on it; the cleanup deletes on it. A new index is a persistence change and requires owner approval under AGENTS.md (escalation policy).

- **Option A (recommended):** new migration adding an index on `created_at`. Pros: `ORDER BY … LIMIT` becomes an index scan instead of a full sort; cleanup becomes a range delete. Cons: slightly slower inserts on every metrics event (one extra B-tree write).
- **Option B:** no schema change. Pros: no migration. Cons: every read and cleanup scans and sorts the whole table.

**Resolution:** Option A, recorded in [spec.md](spec.md) NFR-079-05.
