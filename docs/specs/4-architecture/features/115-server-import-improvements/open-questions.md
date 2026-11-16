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

## Question Details

### ~~Q-115-01~~ · Symbolic links

**Status:** Resolved (A, owner, 2026-11-15: FR-115-04/05/06/11/17/18, NFR-115-06, ADR-115-01)
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
