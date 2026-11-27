# ADR-119-01: Photo deletion through a trash table and a background purge

- **Status:** Accepted
- **Date:** 2026-11-27
- **Related features/specs:** Feature 119 (docs/specs/4-architecture/features/119-background-photo-delete/spec.md), Feature 112 (docs/specs/4-architecture/features/112-fix-album-delete-timeout/spec.md)
- **Related open questions:** Q-112-13, Q-119-01, Q-119-02, Q-119-03, Q-119-04, Q-119-05

## Context

Album and photo deletes remove the photo rows inside the HTTP request. On a production server (MariaDB on an HDD, 128 MB buffer pool, Octane's 30 s wall-clock `LYCHEE_MAX_EXECUTION_TIME`), deleting a 7000-photo album timed out in the DB transaction: every deleted photo is read from the disk (`photos` and `size_variants`, about 394 MB on the dev copy, do not fit in the buffer pool), and each changes about 17 index entries in `photos` and 4 per size variant. Feature 112 reduced the reads (FR-112-14, one read per chunk), but the cost still grows with the number of photos inside the request. Raising the time limit was rejected by the owner.

`photo_album` is many-to-many: a photo is deleted only when its last album link goes (owner rule). Photos without any album link are already invisible to other users (`PhotoQueryPolicy`), but not to their owner or to admins.

Affected modules: delete actions (`Album\Delete`, `Photo\Delete`), `PhotosToBeDeletedDTO`, the `Photo` model, raw `photos` reads, jobs, admin Maintenance (REST and v8), artisan commands, one migration.

## Decision

- A delete removes the album links it targets and inserts the photos left without any link into a new table `trashed_photos` (`photo_id` primary key, foreign key to `photos` with `ON DELETE CASCADE`, `trashed_at`). Album rows are deleted in the same transaction. Every album and photo delete path does this (Q-119-02 A).
- A global Eloquent scope on `Photo` (`NotTrashedScope`) hides trashed photos from every model query, for owners and admins too. Raw `photos` reads that list or serve photos apply the same `NOT EXISTS` condition. The trash action and the purge read without the scope.
- A purge deletes trashed photos in batches of 100, oldest first, each batch in its own transaction, through `PhotosToBeDeletedDTO`. Before deleting, it takes out of the trash any photo that has an album link again. It runs as `PurgeTrashJob` after every delete that trashed photos (worker on async connections, 30 minutes per run before it dispatches itself again; after the response on `sync`, with a 10 s budget in HTTP requests), and can be resumed from an admin Maintenance task (v8) and `php artisan lychee:purge-trash` (Q-119-03 A).
- No retention, no restore (Q-119-04 A). Side effects users see (webhooks, covers, thumbs, caches) run when the photos are trashed. Those that need the rows gone (files, person stats, face embeddings) run in the purge (Q-119-05 A).

## Consequences

### Positive
- The delete request writes only `photo_album`, `trashed_photos` and album rows: small tables, independent of the size of the photo rows.
- Albums and photos disappear for users at once, on every disk.
- The trash is the purge's to-do list: a killed worker or request loses nothing.
- The last-link rule is checked inside the transaction (no link counting) and again by the purge, which closes the move race of Q-112-12.

### Negative
- One more table and a global scope on a central model. Code that must see trashed photos has to remove the scope explicitly, and a raw `photos` read that misses the condition shows a trashed photo until the purge.
- Rows and files of deleted photos live on until the purge: direct file URLs stay reachable, quotas and size-based statistics count them, and a delete webhook arrives before the rows are gone.
- On `sync` installs without a worker, a large purge can need a Maintenance click or the command.

## Alternatives Considered

- System trash album (owner's first proposal): Unsorted unaffected and a browsable trash, but a system album to special-case in every album listing and action, and every link check must skip it.
- Deletion flag on `photos`: one column filter, but setting it updates every photo row, the cost being removed.
- Deletion flag on `base_albums`, purged in the background: no photo filter, but a filter on every album read path, and photo-selection deletes stay heavy.
- Queuing the whole delete as a job (Q-112-01 Option B, Q-112-13 Option D): no change on `sync`, the album stays visible until the worker runs, and a killed worker loses the deletion.
- A bounded, resumable delete across HTTP requests: works on `sync`, but changes the REST contract and every caller.

## Security / Privacy Impact

- Trashed photos are hidden from owners and admins in every model query, and from other users as before (no album link).
- Their files stay reachable by direct URL until the purge, as deleted photos' files are today until the file jobs run.
- The Maintenance routes are admin only.

## Operational Impact

- The `jobs` log gets one line per purge run (`Purged {n} photos from the trash, {r} restored, {m} left.`).
- With a worker the purge runs right after each delete. Without one, Maintenance shows "Pending deletions" until the trash is empty, and `lychee:purge-trash` can run from cron.
- Each photo query gains a `NOT EXISTS` lookup on the primary key of a table that is usually empty.

## Links

- Related spec sections: `docs/specs/4-architecture/features/119-background-photo-delete/spec.md` FR-119-01..08, NFR-119-01..03
- Related ADRs: none
- Related issues / PRs: Feature 112, PR #4997
