# Open Questions – Feature 103

Open questions for [Feature 103](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-103-01~~ | 103 – Album Load Speedup | High | Which parts of the album-open path does Feature 103 cover beyond the backend per-photo work? | Resolved (Option A — backend + v8 main-thread work; FR-103-03 … 06) | 2026-10-28 | 2026-10-28 |
| ~~Q-103-02~~ | 103 – Album Load Speedup | Medium | How is success measured? | Resolved (Option A — scratch-instance targets; NFR-103-01) | 2026-10-28 | 2026-10-28 |
| ~~Q-103-03~~ | 103 – Album Load Speedup | Medium | How is the random header photo drawn: `count` + `offset` or `ORDER BY RAND()`? | Resolved (Option B — `count` + `offset` over photos; FR-103-02) | 2026-10-28 | 2026-10-28 |
| ~~Q-103-05~~ | 103 – Album Load Speedup | Medium | Do the bulk album edit icon headers and capture-range tooltip apply to the v8 page only, or to the v7 page as well? | Resolved (Option A — v8 only; spec Overview, Non-Goals, FR-103-13) | 2026-10-28 | 2026-10-28 |
| ~~Q-103-04~~ | 103 – Album Load Speedup | Medium | Should the photo NSFW-path condition become an uncorrelated list of hidden albums (Timeline `buckets` 392 → 170 ms at 99,000 photos)? | Resolved (Option C, owner, 2026-10-28; spec FR-103-11/12, ADR-103-01) | 2026-10-28 | 2026-10-28 |

## Question Details

### ~~Q-103-01~~ · Scope beyond the backend per-photo work ✅ RESOLVED

**Status:** Resolved — Option A (owner, 2026-10-28). Encoded in spec Goals, Non-Goals and FR-103-03 … 06.  
**Feature:** F-103 – Album Load Speedup  
**Preferred option:** 🅰️ (**recommended**) Option A – Backend + v8 main-thread work  

**Question**  
FR-103-01/02 (no per-photo work in `Album::head` and the HTML page) are in every option. The two other costs on the critical path are the v8 main-thread work after the photo tiers arrive (≈385 ms on the scratch instance, see the spec's Baseline) and the wait for `Album::head` before the photo tiers are requested. Which of them does this feature take on?

---

#### 🅰️ (**recommended**) Option A – Backend + v8 main-thread work
- **Idea:** Two increments: FR-103-01/02, then cut the work `loadPhotosV3()`, the photo grid and the date scrubber do for all 7,000 tiles before the first one shows (dates formatted for every tile, navigation links, per-bucket layout calls, reactivity on every tile). Request order unchanged.
- **Spec impact:** Adds an FR for the v8 main-thread budget between the photo tiers' responses and the first tile, with the hot spots of the Baseline as its scope.
- **Pros:**  
  - ✅ Covers both large costs measured: ≈340 ms of backend work and ≈385 ms of browser work at 7,000 photos.  
  - ✅ No change to the request flow, the password/401 handling or the deep-link path of `AlbumState.load()`.
- **Cons:**  
  - ❌ The frontend part touches Feature 065's tile adaptation, which relies on in-place mutation of reactive tiles (`mergePhotoDetail()`), so it needs care and browser verification.  
  - ❌ Still waits for `Album::head` (≈35 ms after FR-103-01, plus one round trip) before requesting photos.

---

#### 🅱️ Option B – Backend only
- **Idea:** FR-103-01/02 only; the browser work goes to a later feature.
- **Spec impact:** Spec stays as drafted; frontend hot spots move to a follow-up roadmap entry.
- **Pros:**  
  - ✅ Small, low-risk change, quick to ship; helps v7 and v8 alike.  
  - ✅ Lets the owner measure the server gain alone first.
- **Cons:**  
  - ❌ Leaves ≈385 ms of browser work per album open untouched.

---

#### 🅲 Option C – Option A + request the photo tiers alongside `Album::head`
- **Idea:** Option A, and `AlbumState.load()` fires `Photos/buckets` and `Photos` at navigation time, in parallel with `Album::head`, instead of after it (the struct-of-arrays tiers serve all four album types, so only the flag and the active filters decide whether they apply).
- **Spec impact:** Adds an FR on request order; responses for a password-protected, forbidden or missing album are dropped, and a stale navigation discards them as today.
- **Pros:**  
  - ✅ Removes `Album::head`, its round trip and the ≈60 ms of work after it from the critical path.
- **Cons:**  
  - ❌ Rewrites the load sequence of `AlbumState.load()` (password prompt, 401/403, tag/person filters, deep links).  
  - ❌ A locked album answers the photo tiers with 401/403 on every open.  
  - ❌ On a 4-core server the parallel requests compete with `Album::head` itself, so the gain is mostly the round trip.

---

### ~~Q-103-02~~ · How success is measured ✅ RESOLVED

**Status:** Resolved — Option A (owner, 2026-10-28). Encoded in spec NFR-103-01.  
**Feature:** F-103 – Album Load Speedup  
**Preferred option:** 🅰️ (**recommended**) Option A – Scratch-instance targets, owner checks production after  

**Question**  
The 4 s are seen on the production server (Xeon E3-1270 v6, 4 cores / 8 threads, 32 GB). The scratch instance runs on a faster laptop with the PHP server pinned to 4 cores / 8 threads, where the same album shows its first tile after ≈1.23 s. Which target closes the feature?

---

#### 🅰️ (**recommended**) Option A – Scratch-instance targets, owner checks production after
- **Idea:** On the scratch instance (7,000-photo album, PHP server pinned to CPUs 0–7): `/gallery/{album_id}` and `Album::head` within 15 ms of the same requests on an empty album; first tile in the DOM ≤ 800 ms on a full page load (≈1,230 ms today; ≤ 950 ms if Q-103-01 resolves to Option B). The owner compares on the production server after deployment.
- **Spec impact:** NFR-103-01 gets these numbers; the plan records the harness and the before/after runs.
- **Pros:**  
  - ✅ Repeatable by any agent, no access to the production server needed.  
  - ✅ The "within 15 ms of an empty album" rule tests the cause (per-photo work) directly.
- **Cons:**  
  - ❌ Does not prove the 4 s drop to a given number in production.

---

#### 🅱️ Option B – Absolute target on the production server
- **Idea:** The owner records the browser network panel (or a HAR) for the album on the production server now, and the feature closes when the same recording shows the first tile under an agreed time (for example 1.5 s).
- **Spec impact:** NFR-103-01 holds the production target; the before recording goes into the plan.
- **Pros:**  
  - ✅ Measures what the owner sees.  
  - ✅ Would reveal any production-only cost (database size, network) the scratch instance misses.
- **Cons:**  
  - ❌ Needs a deployment and the owner's measurements before the feature can close.  
  - ❌ Mixes in costs outside this feature (network, other requests at page boot).

---

#### 🅲 Option C – No numeric target
- **Idea:** Implement the agreed scope and report before/after numbers without a pass mark.
- **Spec impact:** NFR-103-01 only requires before/after measurements in the plan.
- **Pros:**  
  - ✅ No risk of a target missed by a few milliseconds blocking the feature.
- **Cons:**  
  - ❌ Nothing tells when the frontend increment has done enough.

---

### ~~Q-103-03~~ · Drawing the random header photo ✅ RESOLVED

**Status:** Resolved — Option B (owner, 2026-10-28, with laravel/framework#14263: `inRandomOrder()` generates and sorts a random value per row on MySQL). Encoded in spec FR-103-02.  
**Feature:** F-103 – Album Load Speedup  
**Preferred option:** 🅰️ (**recommended**) Option A – Measure both on MariaDB  

**Question**  
FR-103-02 draws the header from the album's photos (one `EXISTS` probe per photo) instead of its size-variant rows. On SQLite at 7,000 photos, `ORDER BY RANDOM() LIMIT 1` takes 7.0 ms and `count` + random `offset` 9.1 ms on that set (15.4 and 19.8 ms on variant rows). Neither is measured on MySQL/MariaDB or PostgreSQL. Which method ships?

---

#### 🅰️ (**recommended**) Option A – Measure both on MariaDB
- **Idea:** Run the same 2×2 benchmark in a throwaway MariaDB container (port 53306, tmpfs, removed afterwards) and keep the faster method.
- **Spec impact:** FR-103-02 names the measured method.
- **Pros:**  
  - ✅ Decides on the production engine.
- **Cons:**  
  - ❌ Needs Docker on the owner's machine.

---

#### 🅱️ Option B – `count` + `offset` over photos
- **Idea:** Keep the method the code used before, on the per-photo candidate set.
- **Spec impact:** FR-103-02: `count`, then `offset(rand)` with `limit 1`.
- **Pros:**  
  - ✅ No per-row random value or sort on any engine.  
  - ✅ Most of the gain comes from the candidate set, which it keeps.
- **Cons:**  
  - ❌ ≈2 ms slower than `ORDER BY RANDOM()` on SQLite; time varies with the offset drawn.

---

#### 🅲 Option C – `ORDER BY RANDOM() LIMIT 1` over photos
- **Idea:** Keep `inRandomOrder()` as first implemented.
- **Spec impact:** FR-103-02: `ORDER BY RAND()`/`RANDOM()` with `limit 1`.
- **Pros:**  
  - ✅ Fastest on SQLite; one pass.
- **Cons:**  
  - ❌ Unverified on MySQL, where `ORDER BY RAND()` is known to scale poorly.

---

### ~~Q-103-04~~ · Uncorrelated NSFW-path condition for photo queries ✅ RESOLVED

**Status:** Resolved — Option C (owner, 2026-10-28). Encoded in spec FR-103-11, FR-103-12, NFR-103-03, DB-103-01 and ADR-103-01.  
**Feature:** F-103 – Album Load Speedup  
**Preferred option:** 🅰️ (**recommended**) Option A – Uncorrelated list for `origin = null` photo queries  

**Question**  
After FR-103-09, the Timeline's whole-library `Photos/buckets` count still takes ≈392 ms on the scratch instance (99,000 photos, SQLite), most of it in the sensitivity condition `AlbumQueryPolicy::appendRecursiveSensitiveAlbumsCondition()`: a correlated `NOT EXISTS` over every ancestor album, evaluated once per photo row. Rewritten as one uncorrelated list of albums lying under an NSFW album, the same count takes ≈170 ms with identical results. The condition is shared by album listings, Flow, map, search, tag/person albums, thumbs and stats jobs; some callers pass column references as `origin`, which only the correlated form supports. Should photo queries switch?

---

#### 🅰️ (**recommended**) Option A – Uncorrelated list for `origin = null` photo queries
- **Idea:** `PhotoQueryPolicy::appendSensitivityConditions()` uses `albums.id IS NULL OR albums.id NOT IN (albums under an NSFW album, self included)` when `origin` is null; the correlated form stays for integer/column origins and for album queries. `IS NULL` keeps photos without album passing as they do today (a bare `NOT IN` would drop them).
- **Spec impact:** New FR on the photo sensitivity condition; NFR-103-03 `buckets` target lowered.
- **Pros:**  
  - ✅ ≈220 ms less per whole-library photo query at 99,000 photos (Timeline, search, smart albums, map, tag listings).  
  - ✅ Album queries and origin-bound callers untouched.
- **Cons:**  
  - ❌ Two forms of one policy condition to keep equivalent; needs the NSFW test suites on SQLite, MariaDB and PostgreSQL (CI).

---

#### 🅱️ Option B – Keep the correlated condition
- **Idea:** No policy change; rely on the managed cache for repeated `buckets` requests.
- **Spec impact:** None.
- **Pros:**  
  - ✅ No risk to NSFW visibility anywhere.
- **Cons:**  
  - ❌ First Timeline load after any library change pays ≈392 ms (more on the production server) for `buckets`.

---

#### 🅲 Option C – Precomputed NSFW path on albums
- **Idea:** A stored column on `albums`, the nested-set table (album or an ancestor is NSFW), written on album creation (from the parent), NSFW toggles and moves, each rewriting the album's subtree. `origin = null` callers filter on `albums.<column> = false` on the album row they already join. The column has the meaning of the computed `is_recursive_nsfw` select (`AlbumBuilder::addVirtualIsRecursiveNSFW()`, same condition with `origin = null`, self included) and replaces it. Flow's NSFW hiding, the one caller with an origin, keeps the correlated form because NSFW albums at or above its base do not count there.
- **Why on albums, not photos:** A photo inherits NSFW only through the album it is in, and it can be in several albums. The condition is checked per `photo_album` row together with the searchability condition on the same row. Photo queries join `photo_album` and `albums` in every case (`PhotoQueryPolicy::prepareModelQueryOrFail()`, `BaseSmartAlbum::photos()`), and non-admins need that join for searchability, so a photo column saves no join over an album column: both replace the correlated `NOT EXISTS` with a plain column test. A photo flag also changes the result for photos in several albums. "In any NSFW album" hides a photo that shows today through a non-NSFW album. "Only in NSFW albums" shows a photo that is in a public NSFW album and in a private non-NSFW album, because sensitivity passes on the flag and searchability on the public album. On writes, a toggle or move rewrites every photo of the subtree, and every upload, copy, move, unlink and delete becomes a write site that recomputes the flag from the photo's remaining albums. A write site that is missed shows NSFW photos.
- **Spec impact:** Migration, write-path changes, new FRs.
- **Pros:**  
  - ✅ Cheapest reads for every `origin = null` caller (Timeline, search, smart, tag and person albums, RSS, `is_recursive_nsfw`).  
  - ✅ A toggle or a move rewrites only the albums of the subtree.
- **Cons:**  
  - ❌ Persistence change and write sites to keep in sync (explicit writes only), plus a backfill; largest scope.  
  - ❌ Flow still pays the correlated condition.

---

### ~~Q-103-05~~ · Bulk album edit: v8 only, or v7 and v8 ✅ RESOLVED

**Status:** Resolved — Option A (owner, 2026-10-28). Encoded in spec Overview, Non-Goals and FR-103-13.  
**Feature:** F-103 – Album Load Speedup  
**Preferred option:** 🅰️ (**recommended**) Option A – v8 only  

**Question**  
Both frontends have a bulk album edit page: v8 `resources/js/v8/views/BulkAlbumEdit.vue` (Nuxt UI table, data from `GET /api/v3/Albums?for_bulk_edit=1`) and v7 `resources/js/v7/views/BulkAlbumEdit.vue` (PrimeVue table, data from `GET /api/v2/BulkAlbumEdit`, `BulkAlbumResource`). Which ones get the icon headers and the capture-range tooltip?

---

#### 🅰️ (**recommended**) Option A – v8 only
- **Idea:** Icon headers and the capture-range tooltip on the v8 page; `min_taken_ats`/`max_taken_ats` added to the v3 bulk-edit fields only. The v7 page stays as it is.
- **Spec impact:** FR-103-13 … 15 name the v8 page and the v3 resource.
- **Pros:**  
  - ✅ v8 is replacing v7; no work on a page being retired.  
  - ✅ One payload change, one view.
- **Cons:**  
  - ❌ v7 users keep text headers and no capture range.

---

#### 🅱️ Option B – v7 and v8
- **Idea:** Option A, plus the v7 page: PrimeIcons headers with `v-tooltip`, the capture range in a tooltip on its date cell, and `min_taken_at`/`max_taken_at` added to v2 `BulkAlbumResource` (`GET /api/v2/BulkAlbumEdit`).
- **Spec impact:** FR-103-13/15 cover both views; new FR for the v2 resource; v7 icon mapping in PrimeIcons.
- **Pros:**  
  - ✅ Same page on both frontends.
- **Cons:**  
  - ❌ Two payloads, two views, two icon sets to keep aligned; v2 resource and its tests change too.
