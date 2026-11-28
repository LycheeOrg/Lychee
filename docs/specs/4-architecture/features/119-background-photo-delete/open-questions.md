# Open Questions – Feature 119

Open questions for [Feature 119](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-119-01~~ | 119 | High | Where the photos waiting for deletion are held: a trash table, a system trash album, or a flag on `photos` | Resolved (A, owner 2026-11-27, spec FR-119-01/03, DO-119-01/02, ADR-119-01) | 2026-11-27 | 2026-11-27 |
| ~~Q-119-02~~ | 119 | Medium | Which delete paths go through the trash: album and photo deletes, or album deletes only | Resolved (A, owner 2026-11-27, spec FR-119-02) | 2026-11-27 | 2026-11-27 |
| ~~Q-119-03~~ | 119 | High | How the purge runs and resumes on `sync`, where the container has no worker and no scheduler | Resolved (A, owner 2026-11-27, spec FR-119-05/08, UI mock-up, API-119-01/02, CLI-119-01) | 2026-11-27 | 2026-11-28 |
| ~~Q-119-04~~ | 119 | Medium | When trashed photos are purged: as soon as possible, or after a retention period | Resolved (A, owner 2026-11-27, spec FR-119-07, Non-Goals) | 2026-11-27 | 2026-11-27 |
| ~~Q-119-05~~ | 119 | Medium | When the delete side effects run (webhooks, covers, thumbs, person stats): when the photo is trashed or when it is purged | Resolved (A, owner 2026-11-27, spec FR-119-06) | 2026-11-27 | 2026-11-27 |
| ~~Q-119-06~~ | 119 | Medium | A photo delete from a smart, tag or person album removes no link, so photos that are in a regular album survive it (today's behaviour, inherited by the trash) | Resolved (C, owner 2026-11-28: deleting from Unsorted keeps working; spec FR-119-02, FR-119-09, S-119-17/18) | 2026-11-27 | 2026-11-28 |
| ~~Q-119-07~~ | 119 | Medium | Timeline, Tag page and Person page send `from_id = "unsorted"` (no album in their route) and Search sends the searched album: a delete there silently keeps photos that are in an album, which Q-119-06 C does not catch | Resolved (A, owner 2026-11-28, spec FR-119-02, FR-119-09, S-119-17..19) | 2026-11-28 | 2026-11-28 |

## Question Details

### Q-119-01 – Where the photos waiting for deletion are held

**Context (2026-11-27).** Q-112-13 Option A (owner's proposal): move the photos to delete into a trash, delete the albums at once, purge the trash in the background. Q-112-13 assumed a trash album, because photos without an album link would otherwise lose the access policies' protection. That is wrong: for anyone but an admin, `PhotoQueryPolicy::appendSearchabilityConditions()` only shows a photo without an album link to its owner (`album_id IS NOT NULL OR photos.owner_id = user`), and `applyVisibilityFilter()` likewise requires an accessible album or ownership. So a photo with no link is already hidden from other users. In every design, owners and admins still see their trashed photos until something filters them out.

**Owner constraint (2026-11-27).** `photo_album` is many-to-many: a photo can be in several albums, so it is trashed only once its last link is removed. Today this is computed before the delete: `Album\Delete::findAllPhotosToDelete()` sums each photo's links per album chunk and compares them with its total, and `Photo\Delete::do()` subtracts the photos that are in another album than the one they are deleted from (photos without any album are deleted). `forceDeletePhoto()` (NSFW block action) removes every link on purpose. With a trash the rule can be applied directly, in the delete's transaction: remove the links the delete targets, then trash those of its photos that have no link left (`NOT EXISTS` on `photo_album`). The purge checks it again, so a trashed photo that has a link again leaves the trash instead of being deleted. This replaces the counting and closes the move race of Q-112-12 (a photo moved into a surviving album during the delete survives). Encoded in spec FR-119-01, FR-119-04 and Goals.

**Question.** Where are the photos waiting for deletion held?

**Resolution (2026-11-27).** Option A, owner "1: A". Table `trashed_photos` (DO-119-01), hidden by a global scope on `Photo` and the raw reads listed in FR-119-03 (DO-119-02). Architecturally significant (new table, global scope on a central model, deletes deferred): ADR-119-01.

#### Option A – A trash table, photos detached from every album (recommended)

A new table (for example `photo_trash`: `photo_id` primary key with a foreign key to `photos`, `trashed_at`). The request removes the photos' album links and inserts one row per photo.

Pros: the owner's rule as is: a photo is in the trash exactly when it has no album link. No system album, so nothing to hide in album listings, the tree, album pickers, stats or the sitemap, and nothing to protect against upload, move, share, rename, merge, transfer or delete. Inserts into a small table are cheap. The table is the purge's to-do list.
Cons: trashed photos have no album, so Unsorted (photos without a link, through a `LEFT JOIN photo_album`) must exclude them, next to the owner and admin filter. No browsable trash (a count only, see Q-119-03).

#### Option B – A hidden system trash album

The owner's original proposal. One album row, created by a migration and owned by the admin. The request replaces the photos' links by one link to it.

Pros: Unsorted is unaffected. If admins can see it, they can browse the trash and move photos out with the existing UI (a restore for free, while the purge has not run).
Cons: the owner's rule becomes "no link other than the trash", so every link check (Unsorted, the classification, the purge's re-check) must skip the trash album. A system album to special-case wherever albums are listed or targeted (tree and root listing for admins, pickers, upload, move, share, rename, merge, transfer, delete, stats, recompute jobs). A missed site lets an admin break it, and deleting the trash album itself would take today's slow path.

#### Option C – A deletion flag on `photos`

A `deleted_at` column on `photos`, set by the request.

Pros: the filter is one column on the photo.
Cons: setting it is an `UPDATE` on every photo row, the random-I/O cost measured on the 2026-11-27 server (about 1 s per 500 photos) back in the request. `photos` already has 16 secondary indexes.

### Q-119-02 – Which delete paths go through the trash

**Context (2026-11-27).** Entry points listed in spec 112 (API Routes / Services): `DELETE /api/v2/Album`, `DELETE /api/v2/Photo`, admin `DELETE /api/v2/BulkAlbumEdit`, `Photo::delete()`, the import pipes `DeleteMissingPhotos` / `DeleteMissingAlbums` (`lychee:sync`, server import), the NSFW block action (`forceDeletePhoto()`, one photo), `DELETE /api/v2/NsfwDetection/findings/photos`. Feature 112 measured 4000-photo selections as slow too (fan-out, since fixed), and the read passes of FR-112-14 apply to them the same way.

**Question.** Which delete paths move photos to the trash instead of deleting them inline?

**Resolution (2026-11-27).** Option A, owner "2: A". Every album and photo delete path, `forceDeletePhoto()` included (spec FR-119-02).

#### Option A – Every album and photo delete path (recommended)

Pros: one deletion path. Large photo selections are bounded like album deletes. The purge is the only code that deletes photo rows.
Cons: a photo `DELETE` no longer removes the rows before responding: code reading the DB right after (tests, the import's own follow-up steps) sees the photos still present, hidden, until the purge runs.

#### Option B – Album deletes only

Pros: smaller change. Photo deletes keep today's behaviour.
Cons: a selection of thousands of photos still deletes inline. Two deletion paths to maintain.

#### Option C – Album deletes and HTTP photo deletes, console and single-photo paths inline

Import pipes (console, no request limit) and the NSFW single-photo action stay inline.

Pros: no purge needed where the request limit does not apply.
Cons: three behaviours instead of one. The import's inline delete still pays the read and index cost, only without a time limit.

### Q-119-03 – How the purge runs and resumes on `sync`

**Context (2026-11-27).** On async connections a purge job runs in the worker (`queue:work --timeout=3600`, CLI, no PHP time limit). On `sync` (Lychee's default outside the Docker compose files) there is no worker, and the container runs no scheduler (`entrypoint.sh`: `web` and `worker` modes only, `routes/console.php` schedules nothing). Under Octane, after-response work shares `LYCHEE_MAX_EXECUTION_TIME` (FR-112-04). Admin Maintenance tasks follow one pattern: `check` returns a count, `do` processes at most `maintenance_processing_limit` items per call (`MissingPalettes`, `GenSizeVariants`, …).

**Question.** How does the purge run, and how does it finish on `sync`?

**Resolution (2026-11-27).** Option A, owner 2026-11-27. Purge job after each delete (10 s budget on `sync`), Maintenance task with a card in v8 and v7, `lychee:purge-trash` (spec FR-119-05, FR-119-08, UI mock-up, NFR-119-03). The default `maintenance_processing_limit` (5000) is too large for one request on a slow disk, so the Maintenance task also stops after 10 s.

#### Option A – Purge job after each delete, plus a Maintenance task and an artisan command (recommended)

Every delete dispatches a purge job through `DispatchAfterDelete` (worker on async, after the response on `sync`). The job purges the trash in chunks until it is empty or its time budget is used. Whatever is left is purged by the next delete's job, by a Maintenance task, or by `php artisan lychee:purge-trash`.

```
┌ Maintenance ─────────────────────────────────────────────┐
│ ┌──────────────────────────┐                              │
│ │ Pending deletions        │                              │
│ │ 1 234 photos are hidden  │                              │
│ │ and waiting to be        │                              │
│ │ deleted.                 │                              │
│ │                [ Purge ] │  ← purges up to              │
│ └──────────────────────────┘    maintenance_processing_   │
│                                 limit photos per click    │
└───────────────────────────────────────────────────────────┘
```

Pros: works on every setup. Nothing is lost (the trash is the to-do list). Reuses the Maintenance `check`/`do` pattern and a bounded batch per request. The command serves installs that prefer a cron.
Cons: on `sync`, a large purge can need several clicks or the command, and an admin has to notice the count.

#### Option B – Purge job only

Pros: smallest change.
Cons: on `sync`, leftovers wait for the next delete. Nothing shows that photos are still pending.

#### Option C – Purge slices at the start of later requests

Each request purges one bounded slice while the trash is not empty.

Pros: automatic on `sync`, no admin action.
Cons: random requests get slower, and the cost lands on visitors. Concurrency between requests needs a lock.

### Q-119-04 – When trashed photos are purged

**Context (2026-11-27).** Today a delete frees the space at once (files removed after the response on `sync`, or by the worker). A trash makes a retention period possible.

**Question.** When are trashed photos purged?

**Resolution (2026-11-27).** Option A, owner "4: A". No retention, no restore (spec FR-119-07, Non-Goals).

#### Option A – As soon as possible, no retention (recommended)

Pros: deletion keeps today's meaning (space freed within minutes with a worker). No restore UI to build. Quotas and counts are wrong only for the purge's duration.
Cons: no undo.

#### Option B – After a retention period (setting), restore as its own feature

Pros: an undo window.
Cons: space is not freed for N days. Purging old entries needs a trigger at a time (no scheduler in the container). Quotas and counts include trashed photos for N days. Restore needs the photos' former albums, which Option A of Q-119-01 does not keep.

### Q-119-05 – When the delete side effects run

**Context (2026-11-27).** Today, in `Photo\Delete`: `PhotoWillBeDeleted` (only with an enabled `photo.delete` webhook, payload built from the rows, size-variant URLs included) fires before the DB delete. The thumb targets are resolved before the DB delete (they need `photos_tags`, `faces`, `album_user_thumbs`), and their jobs run after it. In `PhotosToBeDeletedDTO`: covers and headers are reset and `AlbumSaved` / `TagAlbumSaved` dispatched, then person-stats and face-embedding jobs run after the commit.

**Question.** Do the side effects run when the photo is trashed (in the request) or when it is purged (in the job)?

**Resolution (2026-11-27).** Option A, owner "5: A". Purchasables, webhooks, thumb targets, covers and headers, cache flush and album events when trashed; files, person stats and face embeddings when purged (spec FR-119-06).

#### Option A – Visible effects at trash time, row-dependent effects at purge time (recommended)

At trash time: the delete webhooks (users see the photo go then, the rows still exist for the payload), the covers and headers reset (small tables), and the thumb refresh, since the thumb queries must skip trashed photos anyway. At purge time: person stats and face embeddings (they need the face rows gone), and file deletion.

Pros: listeners and caches change when users see the deletion. The payload is built from rows that still exist.
Cons: a webhook says "deleted" while rows and files exist until the purge. The thumb refresh needs the trash filter in its queries.

#### Option B – Everything at purge time

Pros: a webhook means the photo is gone. One place for every side effect.
Cons: covers, thumbs and webhooks lag by the purge's delay (minutes with a worker, until a Maintenance click on `sync`). Until then a cover or thumb can show a photo users no longer see anywhere else.

### Q-119-06 – Deleting photos from a smart, tag or person album

**Context (2026-11-27, spec review).** `Photo\Delete::do($photo_ids, $from_id)` removes the selected photos' links to `$from_id`, then deletes (with this feature: trashes) the photos left without a link. v8 `DeleteDialog.vue` sends `getParentId() ?? "unsorted"`, so a delete from Recent, Highlighted, On This Day, a tag album or a person album sends that album's id, and `DeletePhotosRequest` accepts it (`AlbumIDRule`). These albums have no `photo_album` rows: no link is removed, a photo that is in any regular album survives, and only photos without any album are deleted. This is today's behaviour, and FR-119-02 keeps it so far. Not checked yet: whether the v8 UI offers delete in those views and what it shows afterwards.

**Question.** What does a photo delete from a smart, tag or person album do?

**Resolution (2026-11-28).** Option C, owner "C", then "we should still be able to delete from unsorted". `DeletePhotosRequest` refuses a smart album other than Unsorted, a tag album or a person album as `from_id` (422). The v8 and v7 UIs hide photo delete in those album views. Unsorted keeps working for photos without an album. Encoded in spec FR-119-02, FR-119-09, S-119-17/18, UI mock-up. The views without an album in their route are Q-119-07.

#### Option A – Keep today's behaviour, documented in FR-119-02 (recommended)

Pros: no change to the delete semantics in this feature. The trash makes Option B easy to add later. A bug report can be opened separately if the UI shows a success for photos that stay.
Cons: the possible silent no-op stays.

#### Option B – Trash the selected photos everywhere

When `$from_id` is a smart, tag or person album, every link of the selected photos is removed (as `forceDeletePhoto()` does), so they are trashed.

Pros: "delete" deletes, wherever the user is.
Cons: behaviour change: a photo also disappears from albums the user does not own (the permission check already requires the delete right on the photo). The dialog may need a warning. Not what the owner rule ("trashed once the last link is removed") describes for regular albums.

#### Option C – Refuse such deletes

`DeletePhotosRequest` rejects a smart, tag or person album as `from_id` (422), and the UI hides delete there.

Pros: no silent no-op.
Cons: a frontend change in v8 (and v7). Users lose the shortcut for photos without an album (Unsorted keeps working).

### Q-119-07 – Deletes from views without an album: Timeline, Tag page, Person page, Search

**Context (2026-11-28, while encoding Q-119-06 C).** v8 and v7 `DeleteDialog.vue` send `getParentId() ?? "unsorted"`, and `getParentId()` is the route's `albumId` parameter (`composables/photo/photoRoute.ts`). Routes without that parameter send `"unsorted"`: Timeline (`toggleDelete` in `Timeline.vue`), the Tag page (`Tag.vue`, `delete` shortcut not gated), the Person page. Search sends its `albumId` parameter: the album searched in, or `"all"` (`ALL`). With `from_id = "unsorted"`, a selected photo that is in an album keeps its links and is not trashed, so the delete does nothing for it. In a search inside album X, a photo found in a sub-album of X is not linked to X and survives too. Q-119-06 C refuses smart, tag and person album ids, but `"unsorted"` stays accepted (owner), so these views keep the silent no-op.

**Question.** What happens to a delete from Timeline, the Tag page, the Person page and Search?

**Resolution (2026-11-28).** Option A, owner "A". `DeletePhotosRequest` refuses the request (422, naming the photos) when a selected photo is neither linked to `from_id` nor, for Unsorted, without any album. This also refuses smart albums other than Unsorted, tag and person albums (Q-119-06 C), which have no linked photo. v8 and v7 hide photo delete in Timeline, the Tag page, the Person page, Search and the album views of Q-119-06 C. Both Moderation pages sent every selected photo with the first photo's `album_id`, which the membership rule would refuse. Owner (2026-11-28): "moderation should be delete without needing to give the album id", so Moderation gets its own admin delete, `DELETE /api/v2/Moderation` with `photo_ids` only, which removes every link of each photo and trashes it (FR-119-10). Encoded in spec FR-119-02, FR-119-09, FR-119-10, S-119-17..20 and the UI mock-up.

#### Option A – Refuse by membership, hide delete in those views (recommended)

`DeletePhotosRequest` refuses the request (422, listing the photos) when a selected photo is neither linked to `from_id` nor, for Unsorted, without any album. The v8 and v7 UIs hide photo delete in Timeline, the Tag page, the Person page and Search (and in the album views of Q-119-06 C). Album views and Unsorted keep it.

Pros: no silent no-op for any client, whatever the view or API caller. One rule in the backend covers Q-119-06 C too (a smart, tag or person album has no linked photo). Unsorted works for photos without an album, as the owner asked.
Cons: users can no longer delete from Timeline, Tag, Person and Search (they open the photo's album instead). More UI sites to change.

#### Option B – Q-119-06 C only

Only smart (except Unsorted), tag and person album ids are refused. The four views keep sending `"unsorted"` or the searched album.

Pros: smallest change.
Cons: the silent no-op stays in four views, for the delete action users reach most often outside albums.

#### Option C – Delete everywhere from those views

The four views send no album, and a delete without an album removes every link of the selected photos (as `forceDeletePhoto()`), so they are trashed.

Pros: delete works where users are.
Cons: it is Q-119-06 Option B for these views, which the owner did not pick for album views: a photo also disappears from albums the user does not own. Two delete meanings depending on the view.

