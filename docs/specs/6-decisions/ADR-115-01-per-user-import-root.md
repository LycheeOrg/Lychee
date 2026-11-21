# ADR-115-01: Import from server confined to a per-user import root

- **Status:** Accepted
- **Date:** 2026-11-15
- **Related features/specs:** Feature 115 (docs/specs/4-architecture/features/115-server-import-improvements/spec.md), Feature 024 (docs/specs/4-architecture/features/024-sync-file-list/spec.md), Feature 033 (docs/specs/4-architecture/features/033-upload-trust-level/spec.md)
- **Related open questions:** Q-115-01, Q-115-02, Q-115-03, Q-115-07, Q-115-09, Q-115-10, Q-115-11
- **Amended:** 2026-11-16 (Q-115-07: only the server owner sets `server_path`), 2026-11-18 (Q-115-09: the owner scope also covers duplicate photos), 2026-11-19 (Q-115-10: restricted users may import via symbolic link), 2026-11-21 (Q-115-11: links are never followed)

## Context

Import from server reads the server's file system with the PHP process's rights. Until Feature 115 only the user named by the `owner_id` config could use it, so the code trusted its input: browse called `is_dir()` on any path sent, `BuildTree` followed every symbolic link with no loop guard, `CreateNonExistingAlbums` reused any album with the same title whoever owned it, and `DeleteMissingAlbums`/`DeleteMissingPhotos` deleted any child not on disk whoever owned it.

Feature 115 opens import to other users, each confined to a folder, often one they fill themselves through a network share. Every assumption above then becomes a way to read or delete what belongs to someone else. Affected modules: user model and management (application, REST, UI), `AlbumPolicy`, import requests/controller, `Actions\Import` pipes, `ImportImageJob`, `lychee:sync`.

## Decision

1. **Access = `users.server_path`.** `null` disables import, `/` gives today's unrestricted access, any other absolute path is the user's **import root**. Only the server owner (the `owner_id` user) sets it: other admins neither grant, widen, narrow nor revoke it (`UserPolicy::CAN_EDIT_SERVER_PATH`, no admin bypass). `DISABLE_IMPORT_FROM_SERVER` still turns the feature off for everyone.
2. **Containment before disk access.** For a restricted user every requested path is checked lexically against the import root, then by `realpath()` against the real import root with a trailing separator, before `is_dir()` or any read. Outside paths get one generic 403 whether they exist or not. One pure helper (`ImportPathGuard`) does this for browse, import, the tree builder, the job and the sync command.
3. **Symbolic links are never followed** (owner directive 2026-11-21, Q-115-11, replacing "followed only inside the import root"). Browse lists no link and `BuildTree` skips every link with a warning, wherever it points and for every user, explicit `lychee:sync` paths included. No loops can occur, so there is no visited set. A directory or file named explicitly that is itself a link is refused too (browse and web import 422, `lychee:sync` exit 1). Links higher up in a named path (server path, install directory) are resolved as before.
4. **The job checks again.** `ImportImageJob` carries the real import root and re-checks the file's real path when it runs, so a file swapped for a link between scan and job is not read.
5. **`import_via_symlink` for every user** (owner directive 2026-11-19, Q-115-10, replacing "restricted users always get copies"). Restricted users get the same option as full-access users, in the dialog and in `lychee:sync`. The containment checks still apply when the link is created.
6. **Imports stay within their owner's content.** With `import_scope_to_owner` (on by default) album reuse and delete-missing only consider albums and photos owned by the intended owner, and a file whose duplicate is another user's photo is skipped (that photo is neither resynced nor added to the importer's album), for every import. Admins can turn it off to get the pre-115 behaviour.
7. **Upgrade and first admin.** The migration grants `/` to the `owner_id` user. Every first-admin creation path (v7 installer, v8 admin setup, `lychee:create_user` on an empty database) grants `/`. Every other user starts at `null`.

## Consequences

### Positive
- A restricted user can neither list, probe, import nor serve files outside their folder, including through links or file swaps.
- Imports by several users no longer merge into, modify or delete each other's albums and photos.
- Skipping links also removes nested-album chains from link loops for today's admin import.
- Installs that link folders into their import directory lose those folders from the import (behaviour change for `/` users and `lychee:sync`).
- Existing installs and new installs keep a working import for the owner with no action.

### Negative
- One `realpath()` per tree entry.
- Accepted risk (Q-115-10): after a symbolic link import, a restricted user who can write to their import root (network share) can replace the source file with a link to any file the PHP process can read. Lychee then serves that file as the photo's original. Copies are not affected.
- With the owner scope on, an admin syncing into a same-title album owned by someone else gets a new album, where pre-115 Lychee merged.
- The `owner_id` user keeps whole-disk browsing until they narrow it themselves.
- Other admins depend on the owner to grant or change import access.

## Alternatives Considered

- **Q-115-01 B, restricted users never follow links:** first rejected because it also ignores harmless links inside the user's own folder. Q-115-11 later went further: no user follows links.
- **Q-115-01 C, follow every link:** no change, but defeats the restriction.
- **Q-115-02 B, everyone starts disabled:** strictest, but the feature disappears after upgrade with no explanation.
- **Q-115-02 C, admins always have full access:** no setting for admins, but an admin can never be confined.
- **Q-115-03 B, scope only for non-admin owners:** no change for admin imports, but an admin sync with delete-missing still deletes other users' albums.
- **Q-115-03 C, no scope:** cross-user merges and deletions.

## Security / Privacy Impact

- Trust boundary moves from "the owner" to "each user with a path", and only the owner draws it: an admin account cannot widen its own or anyone's access to the file system. The import root is the boundary; real paths are compared, never strings sent by the client.
- No existence oracle outside the root (identical refusals).
- Delete operations (`delete_imported`, delete-missing) only reach files inside the root and content owned by the importer (when the scope setting is on).
- Imported photos take the owner's upload trust level, so moderation (Feature 033) cannot be bypassed by importing.

## Operational Impact

- The server owner manages `server_path` per user in user management (other admins do not see the field); the how-to `import-from-server.md` documents folder layout and the `sync_users_server_paths` cron line.
- Refusals and skipped links are logged (`import path denied`, `symlink_skipped`, `import file left the import root`).
- No new dependency; no measurable cost beyond one `realpath()` per entry.

## Links

- Related spec sections: `docs/specs/4-architecture/features/115-server-import-improvements/spec.md#functional-requirements` (FR-115-01 … 06, 11, 12, 14, 18, 19), NFR-115-01/02/06.
- Related ADRs: none.
- Related issues / PRs: —
