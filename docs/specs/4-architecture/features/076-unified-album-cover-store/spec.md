# Feature 076 – Unified Album Cover Store

| Field | Value |
|-------|-------|
| Status | Implemented 2026-09-30 |
| Last updated | 2026-09-30 |
| Owners | ildyria |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #076 |
| Decisions | [ADR-076-01](../../../6-decisions/ADR-076-01-unified-album-cover-store.md) (Q-076-01, Q-076-02) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in the feature's [open-questions.md](open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview
Lychee keeps automatically selected album covers in two stores. Regular albums hold six precomputed columns on `albums`: `auto_cover_id_{max,least}_privilege` and their rank-2/3 siblings (ADR-0003, ADR-075-01). Tag, person and smart albums hold one row per `(album_id, viewer)` in `album_user_thumbs`, with ranks 1–3 (ADR-0010, ADR-075-01). This feature moves the regular-album covers into `album_user_thumbs` as rows keyed by viewer class:

- the max-privilege row is keyed on the album's `owner_id`;
- the least-privilege row is keyed on `NULL`, or on the single user the album is shared with.

Every listing then reads a regular album's automatic cover the same way it reads a tag album's: "the viewer's row, else the `NULL` row", plus an admin shortcut to the owner's row. `albums.cover_id`, the explicit cover chosen by a user, stays on `albums`.

Affected layers: `albums` and `album_user_thumbs` schema and data, `RecomputeAlbumStatsJob`, `FulfillPreCompute`, the v3 struct-of-arrays listing queries (`BuildAlbumDataResource`, `AlbumRootController`, `AlbumPinnedController`) and `AlbumListController`, `SideCoverIds`, the v2 thumb relation `HasAlbumThumb` and `Album::$with`, `GetPhotoAssetRequest`, `PurgeAlbumUserThumbs`, `Transfer`, `User::delete()`, `Actions\Album\Delete`, the landing page and `Meta` component.

API responses are unchanged: this is a storage refactor, and v7/v2 consumers keep working because the same cover ids come out of the new store.

## Goals
- G1: `albums` loses the six `auto_cover_id_*` columns and their six foreign keys. `cover_id` stays.
- G2: One cover store with one read rule for regular, tag, person and smart albums.
- G3: The v3 listings stay one query per listing. The cover row is reached by a join whose shape PHP chooses per viewer, with no per-row `CASE` on viewer identity.
- G4: The dual-privilege guarantee of ADR-0003 is unchanged: a viewer who is neither admin nor owner never receives a max-privilege cover.
- G5: Every cover id that resolves today resolves to the same id after the data migration.

## Non-Goals
- NG1: Any change to API resources, generated TypeScript types or frontend code.
- NG2: Any change to how tag, person and smart album cache rows are seeded, refreshed or read (`CachesAlbumUserThumb`, `RecomputeAlbumUserThumbsJob`, `AlbumUserThumb::rowsForViewer()`).
- NG3: Any change to the cover *selection* query (ordering, NSFW context, `limit(3)`).
- NG4: Moving `cover_id` or `header_id` off `albums`.
- NG5: Renaming `album_user_thumbs`.
- NG6: Purging precomputed rows on revocation. They are never deleted by `PurgeAlbumUserThumbs` (Q-076-01 → A); they are rewritten by `RecomputeAlbumStatsJob`, which FR-076-13 runs on every permission change.
- NG7: Eloquent model events, observers or `booted()` hooks. Every write to precomputed rows is an explicit call, and the permission-change trigger is a listener on the explicitly dispatched `AccessPermissionChanged` domain event (`[[feedback_no_hooks_explicit_writes]]`).

## Row Model

A regular album has at most two precomputed rows in `album_user_thumbs`, each carrying ranks 1–3 (`photo_id`, `photo_id_2`, `photo_id_3`):

| Row | `user_id` | Computed as | Read by |
|-----|-----------|-------------|---------|
| Max-privilege | `albums.owner_id` | first admin (unchanged from `computeMaxPrivilegeCovers()`) | the owner, and every admin |
| Least-privilege, single share | the one user in the album's only `access_permissions` row | that user | that user |
| Least-privilege, otherwise | `NULL` | the public view (`user: null`) | every other logged-in user and guests |

Invariants:

- **I1:** At most one least-privilege row exists per album, keyed either on the single shared user or on `NULL`.
- **I2:** No least-privilege row exists when the album has no `access_permissions` row, or when its single permission's user is the owner (the owner row already serves them).
- **I3:** For a regular album, the only non-`NULL` `user_id`s are the owner and the single shared user. That is why "the viewer's own row first" is correct for every logged-in non-admin viewer: a row keyed on them is either their owner row or their single-share row.
- **I4:** Precomputed rows carry `is_precomputed = true`; tag/person/smart cache rows carry `false` (Q-076-01 → A, ADR-076-01). Every purge path for cache rows filters on `is_precomputed = false` and leaves precomputed rows untouched.
- **I5:** The least-privilege row's key follows the album's current permission set: every change to that set recomputes the album (FR-076-13, Q-076-02 → A).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-076-01 | `album_user_thumbs` gains `is_precomputed` (`boolean`, NOT NULL, default `false`). Only `RecomputeAlbumStatsJob` and the data migration write `true`; `CachesAlbumUserThumb` and `RecomputeAlbumUserThumbsJob` keep writing the default. `AlbumUserThumb` declares it in `$fillable`, casts it to `bool` and documents it. The unique key `(album_id, user_id_unique_key)`, the `user_id` FK (RESTRICT), the `photo_id` FK (CASCADE) and the `photo_id_2`/`photo_id_3` FKs (SET NULL) are unchanged. | Schema holds cache and precomputed rows side by side. | None. | None. | None. | Q-076-01 → A, ADR-076-01 |
| FR-076-02 | `RecomputeAlbumStatsJob` writes the album's precomputed rows per the Row Model instead of the six columns. It resolves the least-privilege key (single shared user or `NULL`, per the existing `computeLeastPrivilegeCovers()` branches), then, in one transaction with the `albums` save, deletes all precomputed rows of the album and inserts at most two: the owner row when the max triple has a rank 1, and the least row when the least triple has a rank 1 and its key is not the owner (I1, I2). Delete-then-insert avoids writing to the generated `user_id_unique_key` of an upsert, which MySQL/MariaDB refuse. | S-076-01, S-076-02, S-076-03. | None. | Empty album or no permission: no least row; no owner row when the max triple is empty (S-076-04). | Existing debug line prints both triples and the least-row key. | ADR-0003 |
| FR-076-03 | A single migration: (1) adds `is_precomputed` (FR-076-01); (2) copies every album's non-`NULL` max triple into an owner row and its non-`NULL` least triple into a least row keyed per the Row Model (the single shared user derived from `access_permissions`, skipped when that user is the owner); (3) drops the six FKs and the six `auto_cover_id_*` columns. `down()` restores the columns, copies the rows back (the least row, whatever its key, into the least columns), deletes the precomputed rows and drops `is_precomputed`. Chunked, driver-agnostic PHP. | Every cover id resolvable before is resolvable after, for the same viewer (G5, S-076-05). | None. | None. | None. | G5 |
| FR-076-04 | New query helper `App\Actions\Album\StructOfArrays\JoinAutoCover::apply(Builder $query, ?User $user): Builder` joins the precomputed row(s) onto a query already joined with `base_albums`, and selects three aliases `auto_cover_id`, `auto_cover_id_2`, `auto_cover_id_3`. PHP picks one of three join shapes, with no `CASE` on viewer identity: **admin** → one `LEFT JOIN` on `t.album_id = albums.id AND t.user_id = base_albums.owner_id`; **guest** → one `LEFT JOIN` on `t.album_id = albums.id AND t.user_id_unique_key = 0`; **logged-in user** → two `LEFT JOIN`s, `t_me` on `user_id_unique_key = :user_id` and `t_pub` on `user_id_unique_key = 0 AND t_me.id IS NULL`, with each alias `COALESCE(t_me.…, t_pub.…)`. `t_pub` only matches where `t_me` did not, so the ranks are never mixed across the two rows. Every join is also constrained to `is_precomputed = true`. | Owner, admin, single-share user, other user and guest each get their row (S-076-06). | None. | No row: aliases `NULL`, the tile falls back as today. | None. | G3, I3 |
| FR-076-05 | `BuildAlbumDataResource`, `AlbumRootController`, `AlbumPinnedController` and `AlbumListController` replace their six (or two) `albums.auto_cover_id_*` selects with `JoinAutoCover::apply()`. `AlbumListController::rawCoverId()` becomes `cover_id ?? auto_cover_id`. The admin/owner test moves out of it, since `JoinAutoCover` already made that choice. | Same `cover_ids` as before for every viewer (S-076-06). | None. | None. | None. | ADR-0009 |
| FR-076-06 | `SideCoverIds::forAlbumRow(row, primary, unlocked_album_ids, enabled)` reads the triple `[auto_cover_id, auto_cover_id_2, auto_cover_id_3]`, with the same pick and lock rules as FR-075-05. The `$user` parameter and the privilege branch are removed. `fromCacheRow()` is unchanged. | Same `cover_ids_2` / `cover_ids_3` as before (S-076-07). | None. | None. | None. | FR-075-05 |
| FR-076-07 | `Album` drops the `min_privilege_cover` and `max_privilege_cover` relations and the six attributes/casts. It gains `autoCoverRows(): HasMany<AlbumUserThumb>` (rows of the album with `is_precomputed = true`), eager-constrained by viewer: admin → all of the album's precomputed rows; logged-in user → `user_id IS NULL OR user_id = Auth::id()`; guest → `user_id IS NULL`. A pure picker `AutoCoverRows::forViewer(rows, owner_id, ?User): ?AlbumUserThumb` applies the same rule as FR-076-04, and `AutoCoverRows::publicRow(rows)` returns the `NULL` row. `Album::$with` replaces the four `*_privilege_cover*` entries with `autoCoverRows`, `autoCoverRows.photo.size_variants`. `Flow`, `Notify`, `BulkAlbumController`, `GetAlbumChildrenRequest`, `GetAlbumPhotosRequest` and `GetAlbumPersonsRequest` update their `with`/`without` lists accordingly. | v2 `ThumbAlbumResource` returns the same thumb as before (S-076-08). | None. | None. | None. | NG1 |
| FR-076-08 | `HasAlbumThumb` resolves the automatic cover through `AutoCoverRows::forViewer()` on the loaded `autoCoverRows`, keeping priority `cover_id` → viewer row → live searchability fallback. `LandingPageResource`, `LandingFeaturedContentResource` and `View\Components\Meta` use `cover_id ?? AutoCoverRows::publicRow(...)?->photo_id`. | S-076-08, S-076-09. | None. | Album shared with a single user: landing/meta get no automatic cover (the album is not public). | None. | ADR-0003 |
| FR-076-09 | Ownership changes re-key the owner row. `AlbumUserThumb::rekeyOwnerRows(album_ids, new_owner_id)` runs before `owner_id` changes. For each album it deletes the precomputed row keyed on the new owner when they do not own that album yet (a former single-share row, see I2), then moves the precomputed row keyed on the current owner to the new owner. `Transfer::do()` calls it for the album and every descendant (`_lft` between the album's bounds); `User::delete()` for the albums it moves to the acting user, before it deletes the user's remaining rows. | Owner row follows the album (S-076-10). | None. | None. | None. | I3 |
| FR-076-10 | `PurgeAlbumUserThumbs::forBaseAlbums()` and `forUsers()` add `where('is_precomputed', false)` (I4). `User::delete()` deletes the user's remaining rows (cache rows and single-share rows, whose permission it deletes too) after FR-076-09. `AlbumsToBeDeletedDTO::executeDelete()` (the regular-album half of `Actions\Album\Delete`) also deletes the precomputed rows of deleted regular albums: `album_id` has no FK, and the `photo_id` cascade does not fire when the cover photo also lives in another album. | Group-membership change keeps the owner's regular covers (S-076-11). Revocation keeps regular-album rows (S-076-12). | None. | None. | None. | ADR-0010, Q-076-01 → A |
| FR-076-11 | `GetPhotoAssetRequest::isPhotoOfAlbum()` for a regular `Album`: `cover_id` is checked in memory first, then **one** query tests "member of the album via `photo_album`, or equal to `photo_id`/`photo_id_2`/`photo_id_3` of any precomputed row of the album". The exception still accepts any precomputed row of the album for any viewer who can access it, as today. `isComputedAlbumThumb()` is unchanged. | Cover/side photo in a descendant served (S-076-13). | None. | Non-member, non-cover photo stays 403 (S-076-14). | None. | FR-075-10 |
| FR-076-12 | `FulfillPreCompute` selects an album as needing computation when its counters are all at default, or when it has no precomputed row at all (replaces the two `whereNull` cover checks). | Maintenance count and dispatch unchanged in spirit (S-076-15). | None. | None. | None. | ADR-0003 |
| FR-076-13 | A change to an album's access permissions recomputes it. New listener `App\Listeners\RecomputeAlbumStatsOnAccessPermissionChange::handle(AccessPermissionChanged)` dispatches `RecomputeAlbumStatsJob($event->base_album_id)` with its default parent propagation, registered with `Event::listen` in `EventServiceProvider` next to the two cache invalidators. `SharingController::create()`, `edit()` and `delete()` already dispatch the event. `SharingController::propagate()` additionally dispatches `AccessPermissionChanged` once per descendant id that `Propagate::update()` / `overwrite()` touched; both methods return those ids. No Eloquent model event is involved (NG7). | Sharing a single-share album with a second user re-keys the least row to `NULL` (S-076-18). Propagating recomputes every descendant (S-076-19). | None. | Job failure: existing `failed()` logging, row stays as before until the next event. | Existing job logs. | Q-076-02 → A, ADR-076-01 |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-076-01 | v3 listing query counts are unchanged; the cover row is joined, never fetched per album. Every join uses the `(album_id, user_id_unique_key)` unique index. | Owner's performance north star (700k photos, 7k albums). | Existing query-count assertions in the listing tests stay green with their current numbers. | FR-076-04. | ADR-0009 |
| NFR-076-02 | Loading an `Album` through `Album::$with` runs no more queries than today (three for `autoCoverRows` + photo + size variants, against four today). | v2 path cost. | Query-count assertion on a v2 album fetch. | FR-076-07. | ADR-0003 |
| NFR-076-03 | `RecomputeAlbumStatsJob` adds exactly two write statements per album (one delete, one multi-row insert) and no read. | Write-path cost. | Query-count assertion in `RecomputeAlbumStatsJobTest`, updated to the new number. | FR-076-02. | ADR-0003 |
| NFR-076-04 | `GetPhotoAssetRequest` runs at most one query for a regular album when the photo is not the manual cover. | Hottest endpoint. | Query-count assertion in `PhotoAssetV3Test`. | FR-076-11. | ADR-0010 |
| NFR-076-05 | A viewer who is neither admin nor owner never receives a photo id from the owner row. | No private photo leak (G4). | `AlbumCoverSecurityTest` and `CoverDisplayPermissionTest` against the new store. | FR-076-04, FR-076-07. | ADR-0003 §5 |
| NFR-076-06 | `make phpstan`, `vendor/bin/php-cs-fixer fix` and every touched test class green. | Quality gate. | Commands in tasks.md. | None. | AGENTS.md |
| NFR-076-07 | Repeated recomputes caused by one propagate call collapse through `RecomputeAlbumStatsJob`'s existing `DebouncesLatestJobTrait`; no new debounce mechanism. | Queue load on large subtrees. | Code review; `SharingPropagateRecomputeV3Test` asserts one dispatch per descendant, not per ancestor chain. | FR-076-13. | Q-076-02 → A |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-076-01 | Album with public and private photos, shared with two users: job writes an owner row (max triple) and a `NULL` row (public triple). No `auto_cover_id_*` columns exist. |
| S-076-02 | Album whose only permission is for user X: job writes an owner row and an X row computed as X. No `NULL` row. |
| S-076-03 | Album goes from single share (X) to two permissions and the job reruns: the X row is deleted and a `NULL` row written (I1). |
| S-076-04 | Album with no permission: owner row only. Empty album: no rows. Album whose single permission is for its owner: owner row only (I2). |
| S-076-05 | Migration on a seeded database: each album's former `max` triple is its owner row, its former `least` triple its least row under the Row Model key. `down()` restores the columns with the same ids. |
| S-076-06 | Same album listed through `/Albums/{parent}`, `/Albums/root`, `/Albums/pinned` and `/Search/albums` for owner, admin, single-share user, another shared user and guest: each gets the cover id of the row the Row Model assigns them. |
| S-076-07 | Side covers (`cover_ids_2`/`_3`) unchanged for the viewers of S-076-06, including manual-cover and locked-album cases of S-075-06/07. |
| S-076-08 | v2 album fetch (`ThumbAlbumResource`) returns the same thumb for owner, admin and guest as before. |
| S-076-09 | Landing page and `Meta` show `cover_id` or the `NULL` row's cover for a public album. |
| S-076-10 | Transfer of an album with a sub-album to user Y, where Y held the single-share row: Y's single-share rows are deleted, the owner rows of the album and the sub-album are re-keyed to Y, Y's listing shows the max-privilege cover. |
| S-076-11 | Owner changes group membership: `forUsers()` runs, the owner's regular-album rows survive, their tag/person/smart cache rows are deleted. |
| S-076-12 | Revoking a permission on a sub-album: `forBaseAlbums()` deletes cache rows pointing at its photos, keeps the parent's precomputed rows. |
| S-076-13 | Asset request for the parent album with a photo from its owner row or `NULL` row (ranks 1–3) returns 200 for any viewer who can access the parent. |
| S-076-14 | Asset request for a photo that is neither member nor in any precomputed row of the album returns 403. |
| S-076-15 | `FulfillPreCompute::check()` counts an album with photos but no precomputed row. |
| S-076-16 | Deleting a regular album deletes its precomputed rows. |
| S-076-17 | Deleting the rank-1 photo of an owner row cascades the row away and the photo-change listener recomputes it. |
| S-076-18 | Album shared with user X only, then with user Y: `AccessPermissionChanged` dispatches `RecomputeAlbumStatsJob`; the X row is replaced by a `NULL` row and Y's listing shows it. Removing Y again re-keys the row to X. |
| S-076-19 | `SharingController::propagate()` (update and overwrite) dispatches `AccessPermissionChanged` once per touched descendant, and each dispatch queues `RecomputeAlbumStatsJob` for that descendant. |

## Test Strategy
- **Models / Jobs:** `tests/Precomputing/CoverSelection/*` (`RecomputeAlbumStatsJobTest`, `DeepNestingPropagationTest`, `CoverSelectionNsfwTest`, `FulfillPreComputeTest`, `Console/AlbumCoverSecurityTest`, `Console/CoverDisplayPermissionTest`, `Console/ExplicitCoverTest`, `Console/AlbumMutationScenariosTest`) assert on precomputed rows instead of columns (S-076-01..04, S-076-15, S-076-17). New `tests/Unit/Actions/AutoCoverRowsTest` for the pure picker. `tests/Unit/Actions/SideCoverIdsTest` drops the privilege cases.
- **Migration:** covered through its consumers per `[[feedback_no_migration_tests]]`; S-076-05 is verified by the job and listing tests seeding through the new store.
- **REST API v3:** `AlbumListV3Test`, `AlbumSideCoversV3Test`, `PhotoAssetV3Test` (S-076-06, 07, 13, 14, NFR-076-01, NFR-076-04), `PurgeSideCoversV3Test` (S-076-11, 12). New `tests/Feature_v3/Album/AutoCoverRowsV3Test` for transfer, user deletion and album deletion (S-076-10, S-076-16), new `tests/Feature_v3/Sharing/SharingPropagateRecomputeV3Test` (S-076-18, S-076-19, NFR-076-07, `Queue::fake()` for the dispatch assertions); no new Feature_v2 tests.
- **Listener:** new `tests/Unit/Listeners/RecomputeAlbumStatsOnAccessPermissionChangeTest` (`Queue::fake()`, one job per event).
- **Unit:** `tests/Unit/View/Components/MetaTest` (S-076-09), `tests/Unit/CoverageTest`.
- **Frontend:** none (NG1).

## Interface & Contract Catalogue

### Domain Objects
| ID | Description | Modules |
|----|-------------|---------|
| DO-076-01 | `album_user_thumbs` precomputed rows per the Row Model, `is_precomputed` boolean. | persistence, `AlbumUserThumb` |
| DO-076-05 | `RecomputeAlbumStatsOnAccessPermissionChange` listener on `AccessPermissionChanged`. | listeners, `EventServiceProvider` |
| DO-076-02 | `albums` without `auto_cover_id_{max,least}_privilege[_2,_3]`. | persistence, `Album` |
| DO-076-03 | `JoinAutoCover::apply(Builder, ?User): Builder` (aliases `auto_cover_id`, `auto_cover_id_2`, `auto_cover_id_3`). | actions |
| DO-076-04 | `Album::autoCoverRows()`, `AutoCoverRows::forViewer()`, `AutoCoverRows::publicRow()`. | models, actions |

### API Routes / Services
| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-076-01 | REST v2/v3 album and listing routes | Unchanged responses. | Storage refactor only. |
| API-076-02 | REST GET `/api/v3/Asset/{album_id}/{photo_id}/{size_variant}` | Cover exception reads precomputed rows. | FR-076-11. |

### CLI Commands / Flags
| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-076-01 | `php artisan lychee:recompute-album-stats` | Unchanged command; writes precomputed rows. |

### Telemetry Events
None.

### Fixtures & Sample Data
| ID | Path | Purpose |
|----|------|---------|
| FX-076-01 | existing `tests/Samples/*.jpg` | Uploaded into nested, shared albums by the tests. |

### UI States
None.

## Telemetry & Observability
None. The job debug line prints the least-row key.

## Documentation Deliverables
- Roadmap row 076.
- Knowledge map: `albums` computed-field list, `AlbumUserThumb` description, `SideCoverIds`, `PurgeAlbumUserThumbs` (precomputed rows are out of its scope), new `JoinAutoCover` and `AutoCoverRows`.
- ADR-076-01 (Row Model, `is_precomputed`, recompute on permission change). ADR-0003, ADR-0010 and ADR-075-01 are rewritten in place to describe the single store.
- Knowledge map: the `AccessPermissionChanged` event line gains the new listener.

## Fixtures & Sample Data
Existing samples only (FX-076-01).

## Spec DSL

```
domain_objects:
  - id: DO-076-01
    name: album_user_thumbs precomputed rows
    keys:
      max_privilege: user_id = albums.owner_id
      least_privilege_single_share: user_id = <only shared user>
      least_privilege: user_id = NULL
    discriminator: is_precomputed (boolean, default false)
  - id: DO-076-02
    name: albums
    dropped_columns:
      - auto_cover_id_max_privilege
      - auto_cover_id_max_privilege_2
      - auto_cover_id_max_privilege_3
      - auto_cover_id_least_privilege
      - auto_cover_id_least_privilege_2
      - auto_cover_id_least_privilege_3
  - id: DO-076-03
    name: JoinAutoCover
    join_shapes:
      admin: "t.user_id = base_albums.owner_id"
      guest: "t.user_id_unique_key = 0"
      user: "t_me.user_id_unique_key = :id, t_pub.user_id_unique_key = 0 AND t_me.id IS NULL, COALESCE per rank"
    aliases: [auto_cover_id, auto_cover_id_2, auto_cover_id_3]
  - id: DO-076-04
    name: AutoCoverRows
    methods: [forViewer, publicRow]
routes:
  - id: API-076-02
    method: GET
    path: /api/v3/Asset/{album_id}/{photo_id}/{size_variant}
cli_commands:
  - id: CLI-076-01
    command: php artisan lychee:recompute-album-stats
listeners:
  - id: DO-076-05
    event: AccessPermissionChanged
    dispatches: RecomputeAlbumStatsJob(base_album_id)
telemetry_events: []
fixtures:
  - id: FX-076-01
    path: tests/Samples/
ui_states: []
```
