# Open Questions – Feature 116

Open questions for [Feature 116](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-116-01~~ | 116 | High | Who may read an upload's status entry | Resolved (A, owner 2026-11-18, spec FR-116-06, FR-116-07, NFR-116-04; FR-116-07 amended the same day: `photo_id` not gated by visibility, owner directive) | 2026-11-18 | 2026-11-18 |
| ~~Q-116-02~~ | 116 | Medium | One provisional ID per request, or a batch | Resolved (A, owner 2026-11-18, spec FR-116-06, API-116-01) | 2026-11-18 | 2026-11-18 |
| ~~Q-116-03~~ | 116 | Medium | What a `failed` entry tells the client about the cause | Resolved (A, owner 2026-11-18, spec FR-116-04, FR-116-05) | 2026-11-18 | 2026-11-18 |
| ~~Q-116-04~~ | 116 | Medium | API only, or also the v8 upload panel | Resolved (A, owner 2026-11-18, spec N6) | 2026-11-18 | 2026-11-18 |
| ~~Q-116-05~~ | 116 | Medium | Which cache store holds the entries on an asynchronous queue, and how expired rows go away | Resolved (C, owner 2026-11-18, spec NFR-116-02, FR-116-10, ADR-116-01) | 2026-11-18 | 2026-11-18 |

## Question Details

### ~~Q-116-01~~ — Who may read an upload's status entry

**Context.** The upload answers with a provisional ID (`IdFactory::createRandomID()`, 24 URL-safe base64 characters from 18 random bytes, 144 bits). The entry is written at upload time, when the uploader is known: `Auth::id()`, or `null` for a guest uploading into a public upload-enabled album (the photo is then owned by the album owner). The status query runs later, possibly from another session. The `photo_id` of a duplicate can point at a photo the caller cannot see (`skip_duplicates_not_owned`, or another user's copy).

**Option A (recommended): bound to the uploader, provisional ID as the token for guests.** The entry stores the uploader's user ID. An authenticated upload answers only to that user. A guest upload answers to anyone presenting its provisional ID. Any other caller gets `unknown`, the same answer as an expired entry, so the endpoint does not reveal whether the ID exists. `photo_id` is returned only when the caller passes `PhotoPolicy::CAN_SEE` on that photo at query time.
- Pros: an authenticated user's upload history is not readable by anyone else, even with a leaked ID. Guests still get an answer. No existence oracle.
- Cons: a client that re-authenticates as another user loses access to its earlier entries.

**Option B: anyone holding the provisional ID.** The ID alone is the capability. `photo_id` stays gated by `CAN_SEE`.
- Pros: simplest rule. Works across sessions and users.
- Cons: a leaked ID (logs, proxies) exposes the upload's outcome and failure message to anyone for a day.

**Option C: authenticated uploaders only.** Same as A, but guest uploads are not tracked.
- Pros: strictest.
- Cons: guest upload clients get no answer.

Owner selected A on 2026-11-18. Normative outcome: spec FR-116-06, FR-116-07, NFR-116-04.

### ~~Q-116-02~~ — One provisional ID per request, or a batch

**Context.** The discussion's client wants to upload a whole set, then learn every outcome. With a single-ID endpoint, polling N uploads costs N requests per round. The cache answers several keys in one call (`Cache::many()`).

**Option A (recommended): batch.** `GET /api/v2/Photo::uploadStatus?photo_ids[]=…` with 1 to 100 provisional IDs. The response is a list in request order, one entry per ID, each with its own `status`.
- Pros: one request per polling round for a whole set. A single ID is just a list of one.
- Cons: slightly larger contract (array validation, the 100 cap).

**Option B: single ID.** `GET /api/v2/Photo::uploadStatus?photo_id=…` returns one entry.
- Pros: smallest contract, matches "query by provisional ID" literally.
- Cons: N requests per polling round for N uploads.

Owner selected A on 2026-11-18. Normative outcome: spec FR-116-06, API-116-01.

### ~~Q-116-03~~ — What a `failed` entry tells the client about the cause

**Context.** Processing can fail for reasons the user can act on (quota exceeded, unsupported media type, file too large for the image handler), all Lychee exceptions extending `BaseLycheeException` with user-facing messages. It can also fail on internal errors whose messages can contain file system paths or SQL.

**Option A (recommended): message only for Lychee's own exceptions.** `failed` carries `message`: the exception message when it is a `BaseLycheeException`, otherwise a fixed `"Processing failed"`. Skip and rejection messages (`PhotoSkippedException`, `PhotoResyncedException`, `PhotoRejectedException`) also fill `message` on `skipped`.
- Pros: the client can tell the user why. Internal details stay in the logs.
- Cons: the client sees a generic message for internal errors.

**Option B: no message.** `failed` is a bare status; details stay in the Jobs page and the logs.
- Pros: nothing to sanitise.
- Cons: the client cannot tell a quota problem from a crash.

**Option C: raw message for every throwable.**
- Pros: most information.
- Cons: can leak paths and SQL to the client.

Owner selected A on 2026-11-18. Normative outcome: spec FR-116-04, FR-116-05.

### ~~Q-116-04~~ — API only, or also the v8 upload panel

**Context.** The request comes from an API client (a Lightroom publishing plugin). The v7/v8 upload panels show `READY` once an async upload is queued and never learn the final outcome; they could poll the new endpoint too.

**Option A (recommended): API only.** Backend, resource, generated TS types and API docs. The upload panels are unchanged; showing final outcomes there becomes a follow-up feature with its own mock-ups.
- Pros: small, matches the request. The contract settles before a UI depends on it.
- Cons: web users still do not see duplicates or failures of async uploads in the panel.

**Option B: API and the v8 upload panel.** The v8 panel polls `Photo::uploadStatus` for its `READY` files and shows the final state (done, duplicate, skipped, failed).
- Pros: web users benefit immediately.
- Cons: larger feature, UI mock-ups, polling lifecycle in the panel, v7 left behind.

Owner selected A on 2026-11-18. Normative outcome: spec N6.

### ~~Q-116-05~~ — Which cache store holds the entries on an asynchronous queue, and how expired rows go away

**Context.** The upload request writes the entry in the web container; the worker writes the outcome in its own container. The LycheeOrg compose file defaults to `CACHE_STORE=file` with a worker container, and `file` caches are not shared between containers (ADR-090-01; `WorkerCacheCheck` warns). With that default the endpoint would answer `processing` until the entry expires: the exact setup the discussion is about. ADR-090-01 already solved this for unique job locks: `HasSharedUniqueLockTrait::uniqueVia()` uses the `database` store whenever `queue.default` is not `sync`, the default store otherwise. Laravel's `database` cache store deletes an expired row only when that key is read again; rows of uploads nobody queries stay in the `cache` table (one small row per upload). Lychee's scheduler runs a single weekly command and cron is not set up in most installs, so a scheduled prune is not reliable. The `cache` table has an index on `expiration`.

**Option A (recommended): same store as the unique locks, pruned on write.** `database` store when `queue.default` is not `sync`, the default store otherwise (one shared helper with `HasSharedUniqueLockTrait`). On the `database` store, each write of a `processing` entry also deletes expired `upload_status:` rows with a 1-in-50 lottery (`DELETE … WHERE key LIKE '<prefix>upload_status:%' AND expiration < now`, on the `expiration` index).
- Pros: works with the default Docker setup, no admin action. Consistent with ADR-090-01. The table stays bounded to about one day of uploads.
- Cons: two or three database writes per upload. A small prune helper tied to the `cache` table layout.

**Option B: default store only.** Entries live in `CACHE_STORE`. A worker on a `file` cache never shares outcomes; the API notes and `WorkerCacheCheck` tell admins to switch to `database` or `redis`.
- Pros: least code. No new database rows when the admin uses `redis`.
- Cons: broken out of the box for the default Docker compose file, the setup the request targets.

**Option C: default store unless it is per-container.** Use `CACHE_STORE` when it is shared (`database`, `redis`, `memcached`…); fall back to the `database` store only when a non-`sync` queue runs on `file`, `array` or `apc`. Same lottery prune when the `database` store is used.
- Pros: Redis users keep entries in Redis; default Docker works.
- Cons: one more branch than A, and a second definition of "per-container driver" next to `WorkerCacheCheck`'s.

Owner selected C on 2026-11-18. The per-container driver list moves to one shared place used by both `WorkerCacheCheck` and the upload status store, which removes C's second con. Normative outcome: spec NFR-116-02, FR-116-10, DO-116-05, ADR-116-01.
