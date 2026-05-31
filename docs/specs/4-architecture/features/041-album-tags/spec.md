# Feature 041 – Album Tags

| Field | Value |
|-------|-------|
| Status | Complete |
| Last updated | 2026-05-31 |
| Owners | LycheeOrg |
| Linked plan | [plan.md](plan.md) |
| Linked tasks | [tasks.md](tasks.md) |
| Roadmap entry | #041 |

## Overview
Allow regular albums to have tags associated with them directly, enabling organizational tagging and search by tag. Related issue: [#42](https://github.com/LycheeOrg/Lychee/issues/42).

## Goals
1. Add a pivot table `albums_tags` for album-to-tag many-to-many relationship.
2. Expose `tags` on the `Album` model via `tags()` BelongsToMany.
3. Provide a `PATCH /api/Album::albumTags` endpoint to set tags on a regular album.
4. Include tags in `EditableBaseAlbumResource` for regular albums.
5. Allow users to edit album tags in the `AlbumProperties` panel.

## Non-Goals
- Tag-based album search (search UI remains as-is).
- Inheriting tags from parent to child albums.
