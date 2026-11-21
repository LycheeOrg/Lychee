# Open Questions – Feature 115

Open questions for [Feature 115](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-115-01~~ | 115 | High | Symbolic links in browse and the import tree, and `import_via_symlink` for restricted users: (A) follow a link only if its real target is inside the import root, loop guard, re-check in the job, no `import_via_symlink` when restricted, (B) restricted users never follow links, (C) follow every link as today | Resolved (A, owner, 2026-11-15: FR-115-04/05/06/11/17/18, NFR-115-06, ADR-115-01) | 2026-11-15 | 2026-11-15 |
| ~~Q-115-02~~ | 115 | High | Access of the current `owner_id` user after upgrade: (A) migration sets its `server_path` to `/`, others `null`, (B) everyone `null`, (C) admins always full access, column only for non-admins | Resolved (A + first admin gets `/` on every creation path, owner, 2026-11-15: FR-115-14, ADR-115-01) | 2026-11-15 | 2026-11-15 |
| ~~Q-115-03~~ | 115 | High | Album reuse by title and delete-missing touch other users' albums and photos: (A) scope both to the intended owner for every import, (B) scope only for non-admin owners, (C) keep today's behaviour | Resolved (A as config `import_scope_to_owner`, on by default, + new "Import" settings category (server and URL settings), owner, 2026-11-15: FR-115-12, FR-115-15, ADR-115-01) | 2026-11-15 | 2026-11-15 |
| ~~Q-115-04~~ | 115 | Medium | Trust level on web import: (A) derived from the importer like uploads and the CLI, (B) A plus a picker for admins and `--trust_level`, (C) picker for everyone capped at their own level | Resolved (A, owner, 2026-11-15: FR-115-08) | 2026-11-15 | 2026-11-15 |
| ~~Q-115-05~~ | 115 | Medium | `import_root_folder_as_album = 0`: (A) root images at gallery root go to Unsorted, target album stands for the folder for delete-missing (never at gallery root), (B) Unsorted, delete-missing never at target level, (C) root images skipped, delete-missing as A | Resolved (A + delete-missing never deletes Unsorted photos, owner, 2026-11-15: FR-115-09, FR-115-10, FR-115-17) | 2026-11-15 | 2026-11-15 |
| ~~Q-115-06~~ | 115 | Medium | `lychee:sync` with no path: (A) users with a restricted path and upload rights, explicit `--owner_id` narrows to one user, `--album_id` refused, (B) same users, always all, both options refused | Resolved (A, owner, 2026-11-15: FR-115-13) | 2026-11-15 | 2026-11-15 |
| ~~Q-115-07~~ | 115 | High | Who may set a user's `server_path`: (A) server owner only, 403 for any other admin sending the field, field hidden from them, (B) owner only, the field silently ignored for other admins, (C) every admin (as first specified) | Resolved (B, owner directive 2026-11-17, replacing A of 2026-11-16: FR-115-02/16/19, S-115-42, ADR-115-01) | 2026-11-16 | 2026-11-17 |
| Q-115-08 | 115 | High | File swapped between the job's root check and the read (PR #4989 review), reworked 2026-11-22 after Q-115-10/11: (A) accept the read race as Q-115-10 does, refuse `delete_imported` for restricted users, (B) accept the read race, re-check before deleting, (C) private copy for restricted copy imports, (D) accept everything | Open | 2026-11-18 | 2026-11-22 |
| ~~Q-115-09~~ | 115 | Medium | Duplicate photo owned by another user during an import with `import_scope_to_owner = 1` (PR #4989 review): (A) skip and report it, as `skip_duplicates_not_owned`, (B) link it to the album but never resync its metadata, (C) keep today's behaviour and narrow the setting's help text | Resolved (A, owner, 2026-11-18: FR-115-12, S-115-43, DO-115-05, ADR-115-01) | 2026-11-18 | 2026-11-18 |
| ~~Q-115-10~~ | 115 | High | Symbolic link import for restricted users: (A) allowed as for full-access users, risk accepted, (B) refused (ADR-115-01 point 5 as first decided) | Resolved (A, owner directive 2026-11-19: FR-115-04/06/13, S-115-05/28/41, ADR-115-01) | 2026-11-19 | 2026-11-19 |
| ~~Q-115-11~~ | 115 | High | Symbolic links in browse and import: (A) never followed nor listed, for every user, (B) followed only inside the import root (Q-115-01 A as first decided) | Resolved (A, owner directive 2026-11-21: FR-115-05/11, NFR-115-05/07, S-115-21 … 26, TE-115-03/04, ADR-115-01) | 2026-11-21 | 2026-11-21 |
| ~~Q-115-12~~ | 115 | Medium | Delete right for delete-missing (PR #4989 review asked to require `CAN_DELETE` on the target when `delete_missing_photos`/`delete_missing_albums` is set): (A) no delete right, the import-from-server right covers the import's own deletions, (B) require the delete right | Resolved (A, owner directive 2026-11-21: FR-115-07, ADR-115-01) | 2026-11-21 | 2026-11-21 |
| ~~Q-115-13~~ | 115 | Medium | A rejected duplicate (`PhotoRejectedException`: `skip_duplicates_not_owned`, and now scoped server imports) ends the import job as `FAILURE` with an error log and, on an async queue, a `failed_jobs` row: (A) handle it like a skip in import and upload jobs (`SKIPPED`, info log, no failed job), (B) only in `ImportImageJob`, (C) keep it a failure | Resolved (A, owner, 2026-11-22: FR-115-12, S-115-43, FR-114-01/03, S-114-09, ADR-114-01) | 2026-11-21 | 2026-11-22 |

## Question Details

### ~~Q-115-01~~ · Symbolic links

**Status:** Resolved (A, owner, 2026-11-15: FR-115-04/05/06/11/17/18, NFR-115-06, ADR-115-01)
**Superseded:** link following by Q-115-11 (2026-11-21, links never followed), `import_via_symlink` by Q-115-10 (2026-11-19)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Follow links that stay inside the import root

**Question**
`BuildTree` uses `glob()`, `is_dir()` and `is_file()`, which all follow symbolic links, and nothing checks where a link points. Three risks once import is restricted per user:
1. Escape: `alice/x → /srv/import/bob` or `alice/x → /var/www/lychee/storage` lets Alice import (then view and download) images outside her folder.
2. Loops: `alice/loop → alice` builds nested albums `loop/loop/loop/…` until the kernel's ELOOP limit (about 40 levels). This already affects today's admin import.
3. `import_via_symlink`: Lychee stores a link to the source file instead of a copy. Alice controls her folder (for example through a network share), so she can later replace that source file with a link to any file PHP can read, and Lychee serves it as the photo's original.

How should links be handled?

---

#### 🅰️ (**recommended**) Option A – Follow links that stay inside the import root
- **Idea:** Resolve each entry with `realpath()`. Follow a linked directory or file only when its real path is inside the import root (`/` users and explicit `lychee:sync` paths: always inside). Skip the others with a warning in the import report; browse does not list them. Keep the set of real directories visited during one run and skip a directory seen before (loops, two links to one folder). `ImportImageJob` carries the import root and checks the file's real path again when it runs. Restricted users cannot use `import_via_symlink`: the option is hidden and the request is refused if it is set.
- **Spec impact:** FR-115-10 written as above; new scenarios (link inside, link outside, loop, link swapped between scan and job, `import_via_symlink` refused); `ImportImageJob` gains a nullable `import_root`.
- **Pros:**
  - ✅ Links inside one's own folder keep working (common way to organise photos).
  - ✅ No escape, including a file swapped after the scan.
  - ✅ The loop guard also fixes today's admin import.
- **Cons:**
  - ❌ One `realpath()` per entry.
  - ❌ Restricted users lose `import_via_symlink` (they get copies, which use disk space).

---

#### 🅱️ Option B – Restricted users never follow links
- **Idea:** When the import root is not `/`, every symbolic link (directory or file) is skipped with a warning and not listed in browse. Loop guard for the others. Same job re-check and same `import_via_symlink` refusal as A.
- **Spec impact:** FR-115-10 written as above; simpler tree builder (`is_link()` test).
- **Pros:**
  - ✅ Simplest rule, easy to explain.
  - ✅ No path resolution per entry for restricted users.
- **Cons:**
  - ❌ Links that stay inside the user's own folder are ignored too.
  - ❌ Two behaviours to document (`/` users follow links, others do not).

---

#### 🅲 Option C – Follow every link as today
- **Idea:** Only the selected directory is checked against the import root; anything below is followed.
- **Spec impact:** FR-115-10 states the current behaviour.
- **Pros:**
  - ✅ No change in the tree builder.
- **Cons:**
  - ❌ Defeats the restriction: any image PHP can read becomes importable by a restricted user.
  - ❌ Loops still produce nested albums.

**Next action**
Owner picks A, B or C; the answer goes into FR-115-10, the scenario matrix and an ADR on the import access model.

### ~~Q-115-02~~ · Access of the current owner after upgrade

**Status:** Resolved (A + first admin gets `/` on every creation path, owner, 2026-11-15: FR-115-14, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Migration grants `/` to the `owner_id` user

**Question**
Today the user named by the `owner_id` config can import with no further setting. After this feature, access needs `server_path`, which is `null` by default. What do existing installs get?

---

#### 🅰️ (**recommended**) Option A – Migration grants `/` to the `owner_id` user
- **Idea:** The migration adding the column sets `server_path = '/'` on the `owner_id` user. Every other user, admins included, starts at `null`.
- **Spec impact:** FR-115-13: migration rule; scenario "after upgrade, owner keeps full access, other admin has none".
- **Pros:**
  - ✅ Nothing changes for existing installs.
  - ✅ Other admins are opted in explicitly.
- **Cons:**
  - ❌ The owner keeps whole-disk browsing until someone restricts it.

---

#### 🅱️ Option B – Everyone starts disabled
- **Idea:** All users get `null`; admins enable import for themselves in user management.
- **Spec impact:** FR-115-13: no data migration; upgrade note in the release notes.
- **Pros:**
  - ✅ Most restrictive default.
- **Cons:**
  - ❌ The menu entry disappears after upgrade with no explanation; support questions.

---

#### 🅲 Option C – Admins always have full access
- **Idea:** `may_administrate` users can always import with full access; `server_path` only applies to non-admins.
- **Spec impact:** FR-115-03 gains an admin branch; the field is hidden for admins in the form.
- **Pros:**
  - ✅ No setting to manage for admins.
- **Cons:**
  - ❌ An admin can never be restricted.
  - ❌ Contradicts "disabled by default" for admins.

**Next action**
Owner picks A, B or C; the answer goes into FR-115-13 and the migration task.

### ~~Q-115-03~~ · Other users' albums and photos during import

**Status:** Resolved (A as config `import_scope_to_owner`, on by default, + new "Import" settings category (server and URL settings), owner, 2026-11-15: FR-115-12, FR-115-15, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Scope to the intended owner for every import

**Question**
`CreateNonExistingAlbums` reuses any album with the same title under the same parent, whoever owns it. `DeleteMissingAlbums` and `DeleteMissingPhotos` delete every child album and photo of the matched album that is not on disk, whoever owns it. With several importers, Alice importing `Holidays` at the gallery root lands inside Bob's `Holidays`, and Alice's sync with delete-missing can delete Bob's albums and photos (also inside a shared album where she has upload rights). How should imports treat content owned by other users?

---

#### 🅰️ (**recommended**) Option A – Scope to the intended owner for every import
- **Idea:** Album reuse only matches albums owned by the intended owner. Delete-missing only deletes albums and photos owned by the intended owner. Applies to web and CLI, admins included.
- **Spec impact:** FR-115-11 written as above; scenarios "same title, other owner → new album" and "delete-missing leaves other owner's content".
- **Pros:**
  - ✅ An import never merges into or deletes another user's content.
  - ✅ One rule for everybody.
- **Cons:**
  - ❌ Behaviour change for an admin syncing into a same-title album owned by someone else: a new album is created instead.

---

#### 🅱️ Option B – Scope only for non-admin owners
- **Idea:** As A when the intended owner is not an admin; admin-owned imports keep today's matching and deletion.
- **Spec impact:** FR-115-11 with an admin branch.
- **Pros:**
  - ✅ No change at all for admin imports.
- **Cons:**
  - ❌ Two code paths.
  - ❌ An admin sync with delete-missing at the gallery root still deletes other users' albums.

---

#### 🅲 Option C – Keep today's behaviour
- **Idea:** No owner condition.
- **Spec impact:** FR-115-11 documents the merge and deletion risk.
- **Pros:**
  - ✅ No change.
- **Cons:**
  - ❌ Cross-user merges and deletions once several users import.

**Next action**
Owner picks A, B or C; the answer goes into FR-115-11 and the pipe tests.

### ~~Q-115-04~~ · Trust level on web import

**Status:** Resolved (A, owner, 2026-11-15: FR-115-08)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Derived from the importer

**Question**
For the CLI the rule is settled: the `--owner_id` user's trust level applies (FR-115-08). For the web dialog, "set the trust level for the uploaded photos" can mean the same derivation, or a choice in the dialog. Which one?

---

#### 🅰️ (**recommended**) Option A – Derived from the importer
- **Idea:** The importer owns the photos (FR-115-07), so the upload rule applies: admin → `trusted`, else the user's `upload_trust_level`. No new UI.
- **Spec impact:** FR-115-08 covers web and CLI with one rule.
- **Pros:**
  - ✅ Same as uploads; a `check` user cannot bypass moderation by importing.
  - ✅ No new UI or API field.
- **Cons:**
  - ❌ An admin cannot send an imported batch to moderation.

---

#### 🅱️ Option B – A, plus a picker for admins
- **Idea:** Admins get a trust level select in the dialog (default from A) and `lychee:sync` gains `--trust_level`; non-admins have no choice.
- **Spec impact:** new request field and CLI option; exception to FR-033-03 (admin uploads always validated).
- **Pros:**
  - ✅ Admin can import third-party material for review.
- **Cons:**
  - ❌ More UI and API surface; breaks the "admin is always trusted" rule.

---

#### 🅲 Option C – Picker for everyone, capped at their own level
- **Idea:** Any importer can choose a stricter level than their own.
- **Spec impact:** request field, ordering of levels, validation.
- **Pros:**
  - ✅ Users can self-moderate.
- **Cons:**
  - ❌ Most complex; little demand.

**Next action**
Owner picks A, B or C; the answer goes into FR-115-08.

### ~~Q-115-05~~ · Importing a folder's content without its album

**Status:** Resolved (A + delete-missing never deletes Unsorted photos, owner, 2026-11-15: FR-115-09, FR-115-10, FR-115-17)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Unsorted at gallery root, target album stands for the folder

**Question**
With `import_root_folder_as_album = 0`, two cases are open: (1) images directly inside the selected folder when the target is the gallery root, which is not an album; (2) delete-missing, now that the target album (or the gallery root) takes the folder's place.

---

#### 🅰️ (**recommended**) Option A – Unsorted at gallery root, target album stands for the folder
- **Idea:** (1) Those images are imported with no album (Unsorted), as `lychee:sync` file paths without `--album_id` are today. (2) A target album is treated as the folder: delete-missing removes its photos and sub-albums that are not on disk (within Q-115-03's scope). At the gallery root, delete-missing never applies to the root level, only below it.
- **Spec impact:** FR-115-09 completed; scenarios for both cases.
- **Pros:**
  - ✅ Same sync meaning whatever the setting: "the target mirrors the folder".
  - ✅ The gallery root is never cleaned.
- **Cons:**
  - ❌ Syncing with delete-missing into an album that also holds other photos removes them (hint in the dialog).

---

#### 🅱️ Option B – Unsorted, delete-missing never at the target level
- **Idea:** (1) as A. (2) Delete-missing only applies to albums created from sub-folders, never to the target's own photos and sub-albums.
- **Spec impact:** FR-115-09 completed; delete-missing pipes skip the root node.
- **Pros:**
  - ✅ The target album's existing content is never deleted.
- **Cons:**
  - ❌ A file or top-level folder removed from disk is never cleaned up.

---

#### 🅲 Option C – Skip root images
- **Idea:** (1) Images directly in the selected folder are skipped with a warning when the target is the gallery root. (2) as A.
- **Spec impact:** FR-115-09 completed.
- **Pros:**
  - ✅ Nothing lands in Unsorted.
- **Cons:**
  - ❌ Photos are left out of the import.

**Next action**
Owner picks A, B or C; the answer goes into FR-115-09.

### ~~Q-115-06~~ · `lychee:sync` with no path

**Status:** Resolved (A, owner, 2026-11-15: FR-115-13)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Restricted users, explicit `--owner_id` narrows

**Question**
With `sync_users_server_paths = 1` and no path, which users are synced, and what do `--owner_id` and `--album_id` mean? A user with `/` has no folder of their own (syncing `/` would import the whole disk).

---

#### 🅰️ (**recommended**) Option A – Restricted users, explicit `--owner_id` narrows
- **Idea:** Sync every user whose `server_path` is set and not `/` and who may import (FR-115-03), in id order. `/` users are listed as skipped. `--owner_id` given explicitly limits the run to that user (its default moves from the signature to runtime so "given" can be told apart; with paths, the default stays the first admin). `--album_id` with no path → error, exit 1.
- **Spec impact:** FR-115-12 completed; `SyncTest` scenarios for narrowing and the refused album.
- **Pros:**
  - ✅ One cron line syncs everybody; one user can be synced for testing.
  - ✅ The whole disk is never imported by accident.
- **Cons:**
  - ❌ `--help` no longer prints the default owner id.

---

#### 🅱️ Option B – Restricted users, always all
- **Idea:** Same users as A; `--owner_id` and `--album_id` are refused when no path is given.
- **Spec impact:** FR-115-12 completed.
- **Pros:**
  - ✅ Simplest; signature unchanged.
- **Cons:**
  - ❌ Syncing one user means passing their path and `--owner_id` by hand.

**Next action**
Owner picks A or B; the answer goes into FR-115-12.

### ~~Q-115-07~~ · Who may set `server_path`

**Status:** Resolved (B, owner directive 2026-11-17, replacing A of 2026-11-16: FR-115-02/16/19, S-115-42, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Server owner only, refused for other admins

**Question**
The spec let every admin set `server_path`. The owner stated on 2026-11-16: "Only the owner of the server is allowed to modify the path limitations of users. Admins are not allowed to modify this." What does an admin who is not the owner get when the request carries `server_path`, and what does the form show them?

---

#### 🅰️ (**recommended**) Option A – Server owner only, refused for other admins
- **Idea:** New ability `UserPolicy::CAN_EDIT_SERVER_PATH` (owner only, no admin bypass). `AddUserRequest` and `SetUserSettingsRequest` check it whenever the `server_path` key is present, whatever its value, so a `null` cannot revoke access either. 403 otherwise. `UserManagementRightsResource::$can_edit_server_path` drives the v7/v8 forms, which hide the field and omit the key for other admins.
- **Spec impact:** FR-115-02, FR-115-16, new FR-115-19, S-115-42, UI-115-04, ADR-115-01 amended.
- **Pros:**
  - ✅ Explicit refusal, no silent fallback (AGENTS.md greenfield stance).
  - ✅ Same pattern as `OwnerIdRule` (owner-only edits answer 403).
- **Cons:**
  - ❌ A client built for the first version of the form gets 403 when used by a non-owner admin (none released).

---

#### 🅱️ Option B – Owner only, field ignored for other admins
- **Idea:** Same ability, but the requests drop `server_path` for non-owners instead of refusing.
- **Pros:**
  - ✅ No 403 for older clients.
- **Cons:**
  - ❌ The admin believes the path was saved.

---

#### 🅲 Option C – Every admin (as first specified)
- **Cons:**
  - ❌ Contradicts the owner directive.

**Next action**
Resolved: Option A implemented in increment I17, then replaced by Option B on 2026-11-17 (owner: validate the value but ignore it for non-owners, T-115-36).

### Q-115-08 · File swapped between the job's root check and the read

**Status:** Open (options reworked 2026-11-22)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Accept the read race as Q-115-10 does, refuse `delete_imported` for restricted users

**Question**
Raised in the PR #4989 review (thread on `app/Jobs/ImportImageJob.php:96`). `ImportImageJob::assertStillInsideImportRoot()` checks the file's real path (FR-115-18), then `Photo\Create` reads the file again by its path, several times (checksum, metadata, `PlacePhoto` copy). A restricted user who controls their folder (network share) can swap `a.jpg`, or one of its parent folders, for a link to a file outside the import root in that window, and retry until the timing works. The import then reads (and serves as the photo's original) a file outside the root, which NFR-115-06 rules out ("even when the file system changes between the scan and the job"). With `delete_imported`, `PlacePhoto` also deletes the source by its path after the copy, so a swapped parent folder makes Lychee delete a same-named file outside the root. Today S-115-27 only covers a swap before the check. Full-access users (`/`) and explicit `lychee:sync` paths are not affected (no root to escape).

**Re-assessment 2026-11-22.** The options below replace those of 2026-11-18 (in git history), written before Q-115-10 and Q-115-11.
- **Reading:** since Q-115-10, a restricted user can already make Lychee serve any file PHP can read. They import via symbolic link, then replace their source with a link to that file (accepted risk, ADR-115-01). Winning this race gives them nothing more. Closing it for copy imports only (the former option A, private copy) leaves the link-import path open.
- **Deleting:** this is the part nothing accepts yet. With `delete_imported`, a parent folder swapped between the check and `PlacePhoto`'s delete makes Lychee delete a file outside the root. Only files with an image or video extension are reached (`BuildTree` picks nothing else), in folders PHP can write to. That includes other users' import folders and stored originals whose path is known. A path check right before the delete narrows the window but cannot close it: PHP has no delete relative to an open folder (`unlinkat`).
- **Former option B (re-check after the import, roll back):** dropped. It reads the outside file anyway, which link imports already allow.

---

#### 🅰️ (**recommended**) Option A – Accept the read race, refuse `delete_imported` for restricted users
- **Idea:** NFR-115-06 is narrowed to "checked at scan time and right before the read". The remaining read window is the same risk class as Q-115-10 and is recorded in ADR-115-01. A user with an import root never gets Lychee to delete their sources. `ImportFromServerRequest` refuses `delete_imported = true` (422, as the conflicting-options check does), the dialog hides the toggle, and per-user `lychee:sync` runs force it off. Full-access users (`/`) and explicit `lychee:sync` paths keep it.
- **Spec impact:** NFR-115-06, FR-115-04/06/13/17, new scenarios (restricted `delete_imported` → 422, per-user sync never deletes sources), ADR-115-01 points 4 and 5, how-to.
- **Pros:**
  - ✅ Closes the only harm the race adds beyond Q-115-10: no delete can reach outside the root.
  - ✅ Small, deterministic change. No extra copy, no inode bookkeeping.
- **Cons:**
  - ❌ Restricted users lose `delete_imported` and clean their folder themselves.
  - ❌ Undoes part of the "same options for every user" stance of Q-115-10.

---

#### 🅱️ Option B – Accept the read race, re-check before deleting
- **Idea:** As A for reading. `delete_imported` stays available. Right before the delete, `PlacePhoto` re-resolves the source and deletes it only if its real path is still inside the root and has the device and inode of the file it read. On a mismatch, keep the source and log TE-115-05.
- **Spec impact:** NFR-115-06, FR-115-18, a scenario (source swapped before the delete → kept), ADR-115-01.
- **Pros:**
  - ✅ Same options for every user.
  - ✅ Stops every swap made before the re-check.
- **Cons:**
  - ❌ A parent folder swapped between the re-check and the delete still escapes. The window is small, but the user can retry.

---

#### 🅲 Option C – Close the read race for copies: open once, private copy (former option A)
- **Idea:** Restricted copy jobs open the file once, check that the open handle is the checked real path (device and inode), stream it into a private temporary file and import that. Deletion as in B.
- **Spec impact:** FR-115-18, NFR-115-06, new scenarios, ADR-115-01.
- **Pros:**
  - ✅ Copy imports never read a swapped file.
- **Cons:**
  - ❌ One extra copy per file for restricted users. The largest change of the three.
  - ❌ Buys little while link imports allow the same read (Q-115-10). The deletion window of B remains.

---

#### 🅳 Option D – Accept everything
- **Idea:** No code change. NFR-115-06 is narrowed, and both the read and the delete race go into ADR-115-01 and the how-to.
- **Pros:**
  - ✅ No code change.
- **Cons:**
  - ❌ A user who controls their folder can make Lychee delete image files outside it.

**Next action**
Owner picks A, B, C or D. The answer goes into NFR-115-06, FR-115-18, the scenario matrix and ADR-115-01, and the PR #4989 thread is answered.

### ~~Q-115-09~~ · Another user's duplicate photo during a scoped import

**Status:** Resolved (A, owner, 2026-11-18: FR-115-12, S-115-43, DO-115-05, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Skip and report it, as `skip_duplicates_not_owned`

**Question**
Raised in the PR #4989 review (thread on `lang/en/all_settings.php:1077`). FR-115-12 scopes album reuse and delete-missing to the intended owner, and the help text of `import_scope_to_owner` reads "an import never merges into, nor deletes, albums and photos owned by another user". Photo duplicates are not covered: `Photo\Create::handleDuplicate()` runs `ThrowUnownedDuplicate`, which only rejects another user's duplicate when `skip_duplicates_not_owned = 1`. When it is `0`, Alice's import adds Bob's photo to Alice's album (`SetParent`), and with `resync_metadata` it first rewrites Bob's photo from the file (`HydrateMetadata`, `SaveIfDirty`), which can overwrite Bob's own edits. Bob stays the owner, so Alice's later delete-missing (scoped) never removes it from her album. `ImportImageJob` does not carry the scope flag today. What should a scoped import do with another user's duplicate?

---

#### 🅰️ (**recommended**) Option A – Skip and report it, as `skip_duplicates_not_owned`
- **Idea:** `ImportMode` carries `scope_to_owner`. With it on, `ThrowUnownedDuplicate` rejects another user's duplicate whatever `skip_duplicates_not_owned` says, before any resync or `SetParent`; the job records it as skipped, as for the existing setting. With it off, today's behaviour.
- **Spec impact:** FR-115-12 adds photo duplicates to the scope; new scenario "scoped import, duplicate owned by another user → skipped, other user's photo unchanged"; help text unchanged (it then holds).
- **Pros:**
  - ✅ The help text becomes true: no merge into, and no write to, another user's photo.
  - ✅ Reuses an existing rejection path; small change.
- **Cons:**
  - ❌ The importer does not get that photo in their album at all.

---

#### 🅱️ Option B – Link it to the album, never resync it
- **Idea:** With the scope on, another user's duplicate skips `HydrateMetadata` and `SaveIfDirty` but is still added to the importer's album.
- **Spec impact:** FR-115-12 adds the resync exception; scenario for it; help text reworded ("never deletes or modifies").
- **Pros:**
  - ✅ The importer's album mirrors the folder.
- **Cons:**
  - ❌ Another user's photo still appears in the importer's album and is never cleaned by the scoped delete-missing.
  - ❌ Two duplicate rules to explain.

---

#### 🅲 Option C – Keep today's behaviour, narrow the help text
- **Idea:** Photo duplicates follow `skip_duplicates_not_owned` only; the help text says the scope covers album reuse and deletion.
- **Spec impact:** help text of `import_scope_to_owner` in all languages; FR-115-12 states the limit.
- **Pros:**
  - ✅ No code change.
- **Cons:**
  - ❌ A scoped import can still rewrite another user's photo metadata.

**Next action**
Resolved: Option A (owner, 2026-11-18). Precision found while folding it into the spec: `ImportMode` only resyncs metadata when `skip_duplicates` is on, so the two effects above happen in different modes (resync then skip with `skip_duplicates` on; link into the album with it off). Option A covers both because the rejection runs first. Implementation planned as increment I18 (T-115-38, T-115-39), then the PR #4989 thread is answered.

### ~~Q-115-10~~ · Symbolic link import for restricted users

**Status:** Resolved (A, owner directive 2026-11-19: FR-115-04/06/13, S-115-05/28/41, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ Option A – Allowed as for full-access users (owner directive)

**Question**
ADR-115-01 point 5 refused `import_via_symlink` to restricted users (copies only). On 2026-11-19 the owner stated: "we want restricted users to also have import via symlink."

---

#### 🅰️ Option A – Allowed as for full-access users, risk accepted
- **Idea:** Remove the `declined` rule, send the config value in the dialog options, show the toggle, keep the command's option in per-user `lychee:sync`.
- **Spec impact:** FR-115-04, FR-115-06, FR-115-13, S-115-05, S-115-28, S-115-41, API-115-03, UI-115-01, ADR-115-01 point 5 and consequences, how-to.
- **Pros:**
  - ✅ Same options for every user, no disk space spent on copies.
- **Cons:**
  - ❌ A user who can write to their import root can, after the import, replace the source with a link to any readable file and have Lychee serve it as the original.
  - ❌ Q-115-08 option A (private copy) no longer covers every restricted import.

---

#### 🅱️ Option B – Refused (as first decided)
- **Cons:**
  - ❌ Contradicts the owner directive.

**Next action**
Resolved: Option A implemented in increment I19 (T-115-40). Q-115-08 annotated.

### ~~Q-115-11~~ · Symbolic links never followed

**Status:** Resolved (A, owner directive 2026-11-21: FR-115-05/11, NFR-115-05/07, S-115-21 … 26, TE-115-03/04, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ Option A – Never followed nor listed (owner directive)

**Question**
Q-115-01 A followed links whose real path stays inside the import root, with a visited set against loops. On 2026-11-21 the owner stated: "import from server should NOT follow links even if within the path. We just skip all links. (that should simplify things)".

---

#### 🅰️ Option A – Never followed nor listed, for every user
- **Idea:** `BuildTree` skips every `is_link()` entry with warning `symlink_skipped`. Browse lists no link. Same for `/` users and every `lychee:sync` mode. A directory or file named explicitly that is itself a link is refused (owner follow-up 2026-11-21: "Refuse.").
- **Spec impact:** FR-115-05, FR-115-11, NFR-115-05, NFR-115-07, S-115-21 … 26, API-115-02, TE-115-03/04, ADR-115-01 point 3, how-to.
- **Pros:**
  - ✅ No per-entry `realpath()`, no import root in `BuildTree`, no visited set, no loops.
  - ✅ One rule for every user.
- **Cons:**
  - ❌ Behaviour change for installs that link folders into their import directory (`/` users, `lychee:sync`).

---

#### 🅱️ Option B – Followed only inside the import root (as first decided)
- **Cons:**
  - ❌ Contradicts the owner directive.

**Next action**
Resolved: Option A implemented in increment I20 (T-115-41).

### ~~Q-115-12~~ · Delete right for delete-missing

**Status:** Resolved (A, owner directive 2026-11-21: FR-115-07, ADR-115-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ Option A – No delete right (owner directive)

**Question**
Raised in the PR #4989 review (thread on `app/Http/Requests/Admin/ImportFromServerRequest.php:47`, resolved on GitHub without a reply). `ImportFromServerRequest::authorize()` checks the import-from-server right and the upload right on the target album, not the delete right, although `delete_missing_photos` and `delete_missing_albums` delete content of the target. On 2026-11-21 the owner stated: "from the moment you allow import from server all permissions such as delete are ignored for the server actions point of view."

---

#### 🅰️ Option A – No delete right, the import-from-server right covers the import's deletions
- **Idea:** Keep `authorize()` as it is. FR-115-07 states that delete-missing needs no delete right. FR-115-12 (owner scope) still limits what is deleted.
- **Spec impact:** FR-115-07, ADR-115-01 point 6.
- **Pros:**
  - ✅ One right decides what an import may do. No code change.
- **Cons:**
  - ❌ A user with upload rights on a shared album can delete its missing photos and sub-albums through a server import. With the scope on, only the ones they own.

---

#### 🅱️ Option B – Require the delete right
- **Cons:**
  - ❌ Contradicts the owner directive.

**Next action**
Resolved: Option A, no code change. Recorded in FR-115-07 and ADR-115-01. The PR #4989 thread can stay resolved.

### ~~Q-115-13~~ · Rejected duplicates end the job as a failure

**Status:** Resolved (A, owner, 2026-11-22: FR-115-12, S-115-43, FR-114-01/03, S-114-09, ADR-114-01)
**Feature:** F-115 – Server Import Improvements
**Preferred option:** 🅰️ (**recommended**) Option A – Handle a rejection like a skip, in import and upload jobs

**Question**
Found while implementing I18 (Q-115-09). `ThrowUnownedDuplicate` throws `PhotoRejectedException`, which is not a `PhotoSkippedException`. `ImportImageJob::handle()` and `HasFailedTrait::failed()` only treat `PhotoSkippedException` as a handled outcome (ADR-114-01). A rejected duplicate therefore marks the job history `FAILURE`, writes an error log line and, on an asynchronous queue, leaves a `failed_jobs` row. This is already the case for `skip_duplicates_not_owned` (uploads and imports). With `import_scope_to_owner` on by default, a server import over a folder that holds many photos other users already uploaded produces one failed job per file. The upload status (Feature 116, `UploadStatusEntry`) already reports a rejection as `SKIPPED`. How should a job end when its duplicate is rejected?

---

#### 🅰️ (**recommended**) Option A – Handle a rejection like a skip, in import and upload jobs
- **Idea:** `ImportImageJob`, `ProcessImageJob` and `HasFailedTrait` treat `PhotoRejectedException` like `PhotoSkippedException`: history `SKIPPED`, one info log line, no failed job on an asynchronous queue, rethrown on the sync queue (upload 409 unchanged).
- **Spec impact:** FR-115-12 telemetry, S-115-43 (history `SKIPPED`); note in ADR-114-01's related features.
- **Pros:**
  - ✅ Job history matches the upload status (Feature 116).
  - ✅ No failed jobs or error logs for an expected outcome.
- **Cons:**
  - ❌ Changes `skip_duplicates_not_owned` job outcomes too (from failure to skip), beyond Feature 115.

---

#### 🅱️ Option B – Only in `ImportImageJob`
- **Idea:** Same as A for server imports only. Uploads keep failing jobs.
- **Pros:**
  - ✅ Stays inside Feature 115.
- **Cons:**
  - ❌ The same rejection ends differently for an upload and an import.

---

#### 🅲 Option C – Keep it a failure
- **Idea:** No change. The spec describes the failure.
- **Pros:**
  - ✅ No code change.
- **Cons:**
  - ❌ Noisy failed jobs and error logs for scoped imports, on by default.

**Next action**
Resolved: Option A (owner, 2026-11-22), implemented in increment I22 (T-115-48, T-115-49). Feature 114's spec (FR-114-01/03, S-114-09) and ADR-114-01 amended.
