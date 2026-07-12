# Feature 050 – Album Tags

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-07-12 |
| Owners | LycheeOrg |
| Linked plan | `docs/specs/4-architecture/features/050-album-tags/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/050-album-tags/tasks.md` |
| Roadmap entry | #050 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications.

## Overview

Today, `Tag` is a first-class, shared entity (`tags` table, see [docs/specs/4-architecture/tag-system.md](../../tag-system.md)) attached to `Photo`s (`photos_tags`) and to `TagAlbum` membership criteria (`tag_albums_tags`). Regular `Album`s carry no tags of their own. This feature lets a user attach one or more of these same shared `Tag`s directly to a regular `Album` (`App\Models\Album`), as plain metadata — analogous to title/description — independent of any tags carried by the photos inside it. Tagged albums become visible on the existing `/tag/{id}` detail page and are matched by the existing `Search` feature. This is purely album-level metadata: there is no transitive relationship implied between an album's tags and the tags of the photos it contains (that remains out of scope, reserved for a future feature).

Affected modules: core (new `Album::tags()` relation + pivot), application (`Tag::from`/cleanup/merge/rename actions), REST (`UpdateAlbumRequest`/`AlbumController`, `TagController`/`GetTagWithPhotos`/`ListTags`, `AlbumSearch`), UI (v8 only — `AlbumProperties.vue`, `TagPanel.vue`, `TagsManagement.vue`).

**v8-only.** Per explicit product direction, this feature ships only in the Nuxt UI (v8) tree (`resources/js/v8/**`), consistent with Features 047/049. The legacy v7 tree (`resources/js/v7/**`) is not touched.

## Goals

- Let a user attach/detach any number of existing (or newly-typed) shared `Tag`s to a regular `Album`, reusing the exact same `Tag` vocabulary already used for photos and `TagAlbum` criteria (find-or-create by name, case-insensitive, via `Tag::from()`).
- Surface tagged albums on the existing `/tag/{id}` page, alongside the tagged photos already shown there.
- Make album tags searchable through the existing `Search` feature (`tag:` modifier and, for parity with photo search, the modifier-less plain-text fallback).
- Make album tags visible and manageable from the existing `/tags` global tag-management page (rename/merge/delete already act consistently across whichever entities carry the tag).
- Keep `TagAlbum` (the smart, criteria-based tag album) completely unaffected: its own `tags()` relation (`tag_albums_tags`) continues to mean "photos matching these tags", and album-level descriptive tags introduced by this feature must never be read, written, or displayed for `TagAlbum` instances.

## Non-Goals

- No transitive/derived relationship between an album's own tags and the tags of the photos inside it (no auto-tagging photos from the album's tags or vice versa). Reserved for a future feature.
- No changes to `TagAlbum` (`app/Models/TagAlbum.php`), its `is_and`/OR-AND matching, or its `tag_albums_tags` pivot.
- No changes to `PersonAlbum` or smart albums (Recent, Highlighted, etc.) — tags apply only to `App\Models\Album`.
- No v7 (PrimeVue) frontend work — v8 (Nuxt UI) only.
- No new standalone "tag an album" dialog; editing happens inline in the existing album properties panel (`AlbumProperties.vue` / `UpdateAlbumRequest`), mirroring how `TagAlbum`'s own tags are already edited through `UpdateTagAlbumRequest`.
- No change to the existing `Album::tags` REST endpoint (`AlbumTagsController`, `GET /Album::tags`) — that endpoint returns tags *derived from the photos inside* an album (a distinct, pre-existing concept) and is left as-is.

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-050-01 | A new `albums_tags` pivot table (`album_id` → `albums.id`, `tag_id` → `tags.id`, unique pair, cascade-delete both ways) backs a new `Album::tags(): BelongsToMany` relation and inverse `Tag::albums(): BelongsToMany`. | Migration creates table + indexes mirroring `photos_tags`; relation methods added to `Album` and `Tag` models. | N/A (schema-only). | N/A. | None. | User request; mirrors `tag-system.md` §1. |
| FR-050-02 | `UpdateAlbumRequest` (`PATCH /Album`) accepts an optional `tags: string[]` field (present, array, may be empty). On submit, `AlbumController::updateAlbum` resolves tag names via `Tag::from()` (find-or-create, case-insensitive) and calls `$album->tags()->sync(...)`, mirroring `AlbumController::updateTagAlbum`'s existing `Tag::from($request->tags()); $album->tags()->sync(...)` pattern exactly. | Album's tag set is replaced with the submitted set; unknown names become new `Tag` rows; existing case-insensitive matches are reused. | `tags.*` validated `required|string|min:1` (same rule as `SetPhotosTagsRequest`); authorization unchanged (`AlbumPolicy::CAN_EDIT`, already enforced by `UpdateAlbumRequest::authorize()`). | Validation errors return 422 as with any other `UpdateAlbumRequest` field. | None. | Mirrors FR pattern of `UpdateTagAlbumRequest`/`SetPhotosTagsRequest`. |
| FR-050-03 | `EditableBaseAlbumResource` (already returned to editors via `HeadAlbumResource.editable`) gains population of its existing `tags: string[]` field for `Album` instances (currently only populated for `TagAlbum`), so the album properties panel can display/edit the current tag set. | `AlbumProperties.vue` renders a `TagsInput` bound to `editable.tags`, identical in behaviour to the one already used in `AlbumCreateTagDialog.vue`/`UpdateTagAlbumRequest`. | N/A. | N/A. | None. | Reuses existing `EditableBaseAlbumResource.tags` field/UI component. |
| FR-050-04 | `GetTagWithPhotos` (`GET /Tag`) additionally returns the accessible `Album`s carrying that tag, alongside the existing accessible photos. Non-admins see only albums they own or otherwise have read access to (mirrors the existing non-admin photo-ownership filter already applied to photos in this action). `TagWithPhotosResource` gains an `albums: HeadAlbumResource[]`-shaped field (exact resource TBD in plan — reuse the lightest existing album-list resource, e.g. `ThumbAlbumResource`/`HeadAlbumResource`, to match the album-tile grid component). | Response includes both `photos` and `albums` collections for the requested tag id. | Access-controlled the same way `AlbumQueryPolicy`/`AlbumPolicy` already gate album visibility elsewhere. | Empty `albums` array when none accessible (never an error). | None. | Resolves Q-050-01 (Option A). |
| FR-050-05 | `/tag/{id}` (`Tag.vue` + `TagPanel.vue`) renders a distinct "Albums" grid (reusing the existing album-tile grid component from `Albums.vue`/`Search.vue`) above the existing photos grid, shown only when the tag has ≥1 accessible album. Clicking an album tile navigates to that album. | Albums section visible with correct tiles when present; absent entirely when the tag has zero tagged albums. | N/A (read-only display). | N/A. | None. | Resolves Q-050-01 (Option A). |
| FR-050-06 | `AlbumSearch` gains a `tag` modifier strategy (new `AlbumTagStrategy`, mirroring `Strategies/Album/AlbumFieldLikeStrategy`) registered **only** in `queryAlbums()`'s strategy registry (i.e. only applied to `Album::query()`), never in `queryTagAlbums()`'s registry (`TagAlbum::query()`). A `tag:` token matches an album's own `tags` relation (name exact/prefix match, mirroring photo `TagStrategy`). | `tag:vacation` (or `search "vacation"` if plain-text, see FR-050-07) returns albums carrying a tag named "vacation" alongside any matching photos, without ever matching against `TagAlbum`'s own (unrelated) `tags()` relation. | Reuses existing `SearchTokenParser` grammar (`tag:` is already a recognised modifier at the parser level — currently accepted but skipped by `AlbumSearch::addSearchCondition` as photo-only). | N/A — modifier already validated by the parser. | None. | User requirement: "search for those albums when searching for a string"; explicitly excludes tag albums per user directive. |
| FR-050-07 | The existing modifier-less/plain-text album strategy (`AlbumFieldLikeStrategy` with `$column = null`) additionally OR-matches the album's own tag names, mirroring `PlainTextStrategy`'s existing behaviour for photos (which already ORs `tags.name` into the plain-text match). | A bare search term matches title, description, **or** an album tag name. | Same LIKE-escaping as the existing title/description branches. | N/A. | None. | Decided by precedent (parity with `PlainTextStrategy` for photos) rather than left open — see chat resolution. |
| FR-050-08 | `ListTags` (`GET /Tags`) visibility/`HAVING` clause is extended to also count album usage: a tag is listed if it has ≥1 qualifying photo **or** ≥1 qualifying album (same ownership scoping for non-admins: own photos OR own albums; unchanged for admins — all tags). `TagResource` replaces the single `num` field with `num_photos` and `num_albums`. | `/tags` (`TagsManagement.vue`) lists tags used only on albums (zero photos) and shows both counts (e.g. two `UChip` badges). | N/A (read query). | N/A. | None. | Resolves Q-050-02 (Option A). |
| FR-050-09 | `TagCleanupTrait::cleanupUnusedTags()` is extended so a `Tag` is only deleted when it has **zero** rows in `photos_tags`, `tag_albums_tags`, **and** `albums_tags`. `EditTag`, `MergeTag`, and `DeleteTag` extend their album/photo transfer logic to also migrate/delete `albums_tags` rows (mirroring the existing `handleTagAlbums()` pattern in `MergeTag`, scoped the same way to the current user's own albums unless admin). | Renaming/merging/deleting a tag correctly carries over (or removes) its album associations exactly as it already does for photo and tag-album associations; a tag used only by an album is never silently purged by cleanup. | Same non-admin ownership scoping already used for photos/tag-albums (`base_albums.owner_id = user.id`). | N/A. | None. | Without this fix, existing cleanup would delete/orphan album tags that have no matching photo tag — a correctness requirement, not a preference. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-050-01 | `Album::tags` must never leak into `TagAlbum`/`PersonAlbum` code paths (no shared relation, no shared strategy registry entry). | Explicit user requirement: "should not be used inside tag albums." | Code review: `TagAlbum` has no `album_tags`-style relation; `AlbumSearch::queryTagAlbums()`'s strategy registry has no `tag` key added by this feature. | `App\Models\TagAlbum`, `AlbumSearch`. | User requirement. |
| NFR-050-02 | Feature ships v8-only; no `resources/js/v7/**` files are modified. | Explicit user direction mid-session. | Diff review: no `v7/` paths touched. | v8 dual-tree (Feature 049/ADR-0006). | User requirement (mid-session). |
| NFR-050-03 | Tag rename/merge/delete remain multi-user-safe for album associations exactly as they already are for photo/tag-album associations (no cross-user data leakage or accidental global rename). | Existing invariant documented in `tag-system.md` §3 ("Renaming strategy"). | Feature tests mirroring existing `EditTag`/`MergeTag`/`DeleteTag` test coverage, extended to cover albums. | `TagCleanupTrait`, `MergeTag::handleTagAlbums()` pattern. | `tag-system.md`. |
| NFR-050-04 | No N+1 query regressions on `/tag/{id}` or `/tags` from the added album joins/eager-loads. | Existing performance conventions (see e.g. Feature 003/004 pre-computation precedent). | `phpunit` + manual query-count spot check on `GetTagWithPhotos`/`ListTags`. | `GetTagWithPhotos`, `ListTags`. | Coding conventions. |

## UI / Interaction Mock-ups

### Album properties panel — new "Tags" field (v8, `AlbumProperties.vue`)

```
┌──────────────────────────────────────────────────────────┐
│ Album Properties                                          │
│                                                            │
│ Title        [ Summer in Crete____________________ ]      │
│ Description  [ Two weeks around the island........ ]      │
│              [ ................................... ]      │
│                                                            │
│ Tags         [ vacation ✕ ] [ greece ✕ ] [ + add tag... ] │
│              (same TagsInput used for photos / tag albums) │
│                                                            │
│ Copyright    [ ................................... ]      │
│ ...                                                        │
│                    [ Cancel ]           [ Save ]           │
└──────────────────────────────────────────────────────────┘
```

### `/tag/{id}` page — new Albums section above Photos (v8, `Tag.vue` / `TagPanel.vue`)

```
┌──────────────────────────────────────────────────────────┐
│ ← "vacation"                                               │
│                                                            │
│  Albums                                                    │
│  ┌──────────┐ ┌──────────┐                                │
│  │ [thumb]  │ │ [thumb]  │                                │
│  │ Crete    │ │ Corsica  │                                │
│  └──────────┘ └──────────┘                                │
│                                                            │
│  Photos                                                    │
│  ┌───┐ ┌───┐ ┌───┐ ┌───┐ ┌───┐ ┌───┐                        │
│  │   │ │   │ │   │ │   │ │   │ │   │                        │
│  └───┘ └───┘ └───┘ └───┘ └───┘ └───┘                        │
└──────────────────────────────────────────────────────────┘
```
_The Albums section is omitted entirely when the tag has zero accessible albums (today's behaviour, unchanged)._

### `/tags` management page — split counts (v8, `TagsManagement.vue`)

```
┌──────────────────────────────────────────────────────────┐
│ Tags                                                       │
│                                                            │
│  vacation                              [12 photos] [2 albums] [Rename][Merge][Delete] │
│  greece                                 [3 photos] [1 album]  [Rename][Merge][Delete] │
│  roadtrip                                          [1 album]  [Rename][Merge][Delete] │
│                                    (album-only tag — no photo chip shown)              │
└──────────────────────────────────────────────────────────┘
```

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-050-01 | User adds tags "vacation", "greece" to Album "Crete" via album properties → tags persisted via `Tag::from()`+`sync()`; reused if an identically-named (case-insensitive) `Tag` already exists from photo tagging. |
| S-050-02 | User removes all tags from an album (submits empty `tags: []`) → all `albums_tags` rows for that album removed; orphaned `Tag`s (zero photos/tag-albums/albums) pruned by `cleanupUnusedTags()`. |
| S-050-03 | User opens `/tag/{id}` for a tag used by both photos and an album → both an Albums section and the Photos grid render; non-admin sees only accessible items of each kind. |
| S-050-04 | User opens `/tag/{id}` for a tag used only by an album (zero tagged photos) → Albums section renders, Photos grid shows the existing empty-state message. |
| S-050-05 | User searches `tag:vacation` → matching Albums (via their own tags) and matching Photos (via photo tags) both appear in results; `TagAlbum`s are never matched by this token. |
| S-050-06 | User searches plain text `vacation` (no modifier) → matches album tag names in addition to title/description, mirroring photo plain-text behaviour. |
| S-050-07 | Admin renames tag "greece" → "hellas" → all photo, tag-album, and album associations move to the (found-or-created) "hellas" tag; old tag deleted if now fully unused. |
| S-050-08 | Non-admin renames a tag they use only on their own album (not shared by other users) → only their own album/photo associations move, per existing multi-user-safe rename semantics; other users' unrelated same-named tag usage is untouched. |
| S-050-09 | A tag is attached to an album but zero photos anywhere carry it → `cleanupUnusedTags()` must **not** delete it; it remains listed on `/tags` with `num_albums: 1`, `num_photos: 0`. |
| S-050-10 | `/tags` list for a non-admin → tags scoped to their own accessible photos **or** albums are shown (not other users' private tag usage), consistent with existing photo-only privacy scoping. |

## Test Strategy

- **Core:** Unit tests for `Album::tags()`/`Tag::albums()` relations; `TagCleanupTrait` extended to assert a tag with only an `albums_tags` row survives cleanup (S-050-09).
- **Application:** Feature tests for `EditTag`/`MergeTag`/`DeleteTag` extended to cover album associations (mirroring existing photo/tag-album coverage), including the multi-user-isolation scenario (S-050-08).
- **REST:** Feature tests for `PATCH /Album` with a `tags` payload (add/replace/clear); `GET /Tag` returning `albums`; `GET /Tags` returning split counts and album-only tags; `GET /Search` with `tag:` and plain-text tokens matching albums; confirm `TagAlbum` search/update paths are unaffected (regression coverage for NFR-050-01).
- **UI (Vue/vue-tsc):** `AlbumProperties.vue` tags field wiring (v8 only); `TagPanel.vue` Albums section render/hide logic; `TagsManagement.vue` split-count display. No v7 test changes (NFR-050-02).
- **Docs/Contracts:** Regenerate TypeScript types (`TagResource`, `TagWithPhotosResource`, `EditableBaseAlbumResource` — already TS-transformed via `#[TypeScript()]`); update `docs/specs/4-architecture/tag-system.md` with the new `albums_tags` pivot and cleanup semantics.

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-050-01 | `albums_tags` pivot: `id` (bigIncrements), `album_id` (char(24), FK→`albums.id` cascade), `tag_id` (bigint unsigned, FK→`tags.id` cascade), unique(`album_id`,`tag_id`), indexed both columns individually and combined — mirrors `photos_tags`/`tag_albums_tags` shape. | core, migrations |
| DO-050-02 | `Album::tags(): BelongsToMany<Tag>` via `albums_tags` (`album_id`,`tag_id`); `Tag::albums(): BelongsToMany<Album>` inverse. | core |
| DO-050-03 | `TagResource` fields become `id: int`, `name: string`, `num_photos: int`, `num_albums: int` (replaces single `num`). | REST, UI |
| DO-050-04 | `TagWithPhotosResource` gains `albums: <album-tile resource>[]` (resource type finalised in plan). | REST, UI |
| DO-050-05 | `EditableBaseAlbumResource.tags` (existing field) populated for `Album` instances in addition to the existing `TagAlbum` population. | REST, UI |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|--------------|-------|
| API-050-01 | REST `PATCH /api/v2/Album` | `UpdateAlbumRequest` gains optional `tags: string[]` field; `AlbumController::updateAlbum` resolves via `Tag::from()` + `$album->tags()->sync()`. | Existing endpoint, extended payload; no new route. |
| API-050-02 | REST `GET /api/v2/Tag` | `GetTagWithPhotos`/`TagWithPhotosResource` extended to include accessible albums for the tag. | Existing endpoint, extended response. |
| API-050-03 | REST `GET /api/v2/Tags` | `ListTags`/`TagsResource`/`TagResource` extended for split counts + album-only tags. | Existing endpoint, extended response. |
| API-050-04 | REST `GET /api/v2/Search` | `AlbumSearch` gains a `tag`-modifier strategy for `queryAlbums()` only; plain-text strategy extended to match album tags. | Existing endpoint, extended matching only. |

### CLI Commands / Flags

_None — this feature has no CLI surface._

### Telemetry Events

_None — no new telemetry events; this feature does not introduce webhooks/notifications._

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-050-01 | `database/factories/TagFactory.php` (existing) | Reused for feature-test tag creation; no changes anticipated. |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|------------------------------|
| UI-050-01 | Album properties — Tags field populated | Opening properties for an `Album` with existing tags shows them as chips in `TagsInput`. |
| UI-050-02 | `/tag/{id}` — Albums section hidden | Tag has zero accessible albums → section not rendered (no empty-state box, simply absent). |
| UI-050-03 | `/tags` — album-only row | A tag with `num_photos: 0`, `num_albums: 1` shows only the album-count chip, no photo-count chip (mirrors existing `v-if="tag.num > 0"` chip-hiding pattern). |

## Telemetry & Observability

No new telemetry events. No changes to verbose-trace logging beyond what already exists for `Album` updates and `Tag` mutations.

## Documentation Deliverables

- Update [docs/specs/4-architecture/tag-system.md](../../tag-system.md): document the new `albums_tags` pivot, `Album::tags()`/`Tag::albums()` relations, and the revised `cleanupUnusedTags()` "used by photo OR tag-album OR album" definition.
- Update [docs/specs/4-architecture/knowledge-map.md](../../knowledge-map.md) if a dedicated Tags/Search module entry is introduced during planning.
- Update `docs/specs/4-architecture/roadmap.md` Active Features table.

## Fixtures & Sample Data

None beyond existing `TagFactory`/`AlbumFactory`.

## Spec DSL

```
domain_objects:
  - id: DO-050-01
    name: albums_tags (pivot table)
    fields:
      - name: album_id
        type: char(24)
        constraints: "FK -> albums.id, cascade on update/delete"
      - name: tag_id
        type: unsigned bigint
        constraints: "FK -> tags.id, cascade on update/delete"
    constraints: "unique(album_id, tag_id)"
  - id: DO-050-02
    name: Album::tags / Tag::albums relations
    fields:
      - name: tags
        type: "BelongsToMany<Tag>"
  - id: DO-050-03
    name: TagResource
    fields:
      - name: num_photos
        type: integer
      - name: num_albums
        type: integer
  - id: DO-050-04
    name: TagWithPhotosResource.albums
    fields:
      - name: albums
        type: "array<AlbumTileResource>"
  - id: DO-050-05
    name: EditableBaseAlbumResource.tags (Album population)
    fields:
      - name: tags
        type: "string[]"
routes:
  - id: API-050-01
    method: PATCH
    path: /api/v2/Album
  - id: API-050-02
    method: GET
    path: /api/v2/Tag
  - id: API-050-03
    method: GET
    path: /api/v2/Tags
  - id: API-050-04
    method: GET
    path: /api/v2/Search
cli_commands: []
telemetry_events: []
fixtures:
  - id: FX-050-01
    path: database/factories/TagFactory.php
ui_states:
  - id: UI-050-01
    description: Album properties Tags field populated from existing album tags
  - id: UI-050-02
    description: "/tag/{id} Albums section hidden when zero accessible albums"
  - id: UI-050-03
    description: "/tags row shows only non-zero count chips (photo/album)"
```

## Appendix

### Resolved clarifications

- **Q-050-01** (`/tag/{id}` layout) — Resolved: Option A, separate Albums section above Photos grid. See [open-questions.md](../../open-questions.md#q-050-01--tagid-detail-page--layout-for-showing-tagged-albums-alongside-tagged-photos--resolved).
- **Q-050-02** (`/tags` list & counts) — Resolved: Option A, show album-only tags with split `num_photos`/`num_albums`. See [open-questions.md](../../open-questions.md#q-050-02--tags-global-list--counts--should-album-only-tags-be-listed-and-how-are-counts-split--resolved).
- **Q-050-03** (tag visibility to viewers) — Resolved: Option A, editor-only. `EditableBaseAlbumResource.tags` (FR-050-03) remains the only surface for an album's own tags; `HeadAlbumResource` gains no public `tags` field in this feature. A public read-only chip display is a possible fast-follow, not built here.
- **Plain-text album search matching tags** — Decided by precedent (not logged as a blocking question): `PlainTextStrategy` for photos already ORs `tags.name` into the modifier-less match (`app/Actions/Search/Strategies/PlainTextStrategy.php:35`), so the album equivalent (`AlbumFieldLikeStrategy` with `$column = null`) does the same for consistency (FR-050-07).

### Key existing-code precedents relied upon by this spec

- `AlbumController::updateTagAlbum()` already does `Tag::from($request->tags()); $album->tags()->sync($tag_models->pluck('id')->all());` — the exact pattern FR-050-02 reuses for regular `Album`s.
- `MergeTag::handleTagAlbums()` already implements the "transfer album associations for a tag, scoped to the current user unless admin" pattern that FR-050-09 extends to `albums_tags`.
- `EditableBaseAlbumResource.tags` is an existing field (currently populated only for `TagAlbum`); FR-050-03 adds an `Album` branch rather than introducing a new field.
