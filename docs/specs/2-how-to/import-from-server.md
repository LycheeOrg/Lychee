# How to: Import from server

Import from server copies (or links) photos that already sit on the server's disk into Lychee. It is available from the gallery ("Import from server" in the add menu) and from the command line (`php artisan lychee:sync`). See Feature 115 ([spec](../4-architecture/features/115-server-import-improvements/spec.md)) and [ADR-115-01](../6-decisions/ADR-115-01-per-user-import-root.md).

## Who may import

Each user has a **server import path** (`users.server_path`), set by the server owner (the `owner_id` user) in the user management form. Other admins do not see the field and cannot change it:

| Value | Meaning |
|-------|---------|
| empty | Import from server is disabled for this user (default for every new user). |
| `/` | Full access: the dialog opens on the Lychee folder and the user may browse the whole disk. |
| `/srv/import/alice` | Restricted: the user only sees and imports this folder and what is below it. |

The user must also be allowed to upload (or be an admin). `DISABLE_IMPORT_FROM_SERVER=true` in `.env` still turns the feature off for everyone, admins included.

The owner of an upgraded install keeps full access (`/`). The first admin of a new install (installer, v8 admin setup, `lychee:create_user` on an empty database) gets `/` too.

The path must exist and be readable by the PHP process when it is saved.

## What a restricted user can do

- Browse and import only inside their folder. Any other path is refused with the same answer whether it exists or not.
- Symbolic links inside the folder are followed only when they point inside the folder; links leaving it are not listed and are skipped during the import (`symlink_outside` in the import report).
- Files are always copied: "import via symbolic link" is not offered to restricted users.
- Imported albums and photos belong to the user who imports, with the user's upload trust level (photos of a `check` user wait for validation).
- A file replaced by a link to somewhere else between the scan and its processing is not imported (the job fails).

Folders reached twice (link loops, two links to one folder) are imported once, for every user.

## Settings (category "Import")

| Setting | Default | Effect |
|---------|---------|--------|
| `import_root_folder_as_album` | on | Off: the selected folder is not created as an album. Its images go into the target album, or into Unsorted at the gallery root, and its sub-folders become albums directly under the target. Delete-missing then applies to the target album itself (never to Unsorted). |
| `import_scope_to_owner` | on | Imports only reuse, and delete-missing only removes, albums and photos owned by the importing user. Off restores the pre-115 behaviour (any owner). |
| `sync_users_server_paths` | off | Allows `lychee:sync` with no path (see below). |
| `delete_imported`, `import_via_symlink`, `skip_duplicates_early`, `sync_delete_missing_photos`, `sync_delete_missing_albums`, `sync_dry_run` | | Defaults of the import dialog and of `lychee:sync`. |
| `import_via_url_*` | | Safety checks of the import from URL. |

## Syncing every user's folder from cron

1. Give each user a restricted server import path.
2. Enable `sync_users_server_paths`.
3. Add a cron line:

```
*/30 * * * * cd /path/to/Lychee && php artisan lychee:sync --dry_run=0 >> storage/logs/sync.log 2>&1
```

With no path, `lychee:sync` syncs, in user id order, the folder of every user whose path is set (and not `/`) and who may import, as that user, into the gallery root. It prints one line per user (synced job count, or why the user was skipped). Users with `/` are skipped: they have no folder of their own. A missing or empty folder is reported and the next user is synced; the command then exits with code 1.

- `--owner_id=<id>` limits the run to that user (error when that user cannot be synced).
- `--album_id` needs a path: it is refused when no path is given.
- With paths, `lychee:sync` behaves as before; `--owner_id` defaults to the first admin. Explicit paths are not restricted (shell access is already full access) and follow every link.
