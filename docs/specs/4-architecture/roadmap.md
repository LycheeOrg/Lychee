# Development Roadmap

High-level planning document for Lychee features and architectural initiatives.

## Active Features

| Feature ID | Name | Status | Priority | Assignee | Started | Updated | Progress |
|------------|------|--------|----------|----------|---------|---------|----------|
| 019 | Friendly URLs (Album Slugs) | Complete | P2 | - | 2026-02-27 | 2026-02-28 | All 24 tasks done. Migration, model, SlugRule, middleware, update requests, feature tests (26 tests/1048 assertions), frontend UI, translations (22 langs). Quality gates: PHPStan 0 errors, php-cs-fixer clean, npm build/check/format clean. |

## Paused Features

| Feature ID | Name | Status | Priority | Reason | Paused Date |
|------------|------|--------|----------|--------|-------------|
| - | - | - | - | - | - |

## Completed Features

| Feature ID | Name | Completed | Notes |
|------------|------|-----------|-------|
| 018 | Photo Albums Sidebar  | 2026-02-26 | Spec, plan, tasks drafted. Pending implementation. |
| 017 | Apply Renamer Rules & Watermark Confirm  | 2026-02-26 | Spec, plan, tasks drafted. Pending implementation. |
| 016 | Bulk License Edit | 2026-02-27 | Backend complete (T-016-01 to T-016-04), next: quality gates & frontend |
| 015 | Upload Watermark Toggle | 2026-02-24 | Per-upload watermark control: UI toggle in upload dialog, backend API parameter (apply_watermark), ApplyWatermark pipe respects flag, admin setting (watermark_optout_disabled) to enforce watermarking, translations in 22 languages, end-to-end flow complete (9 increments: I0-I8b) |
| 013 | Starred to Highlighted Rename | 2026-02-22 | Renamed is_starred → is_highlighted, StarredAlbum → HighlightedAlbum, auto-inserts 5-star rating for highlighted photos, config key rename, translations for 22 languages, 29 tasks complete |
| 012 | Embeddable Photo Album Widget | 2026-02-20 | JavaScript widget for embedding albums/photo streams on external websites, supports all gallery layouts (square/justified/masonry/grid/film), lightbox, CORS API, theme customization, embed code generator UI |
| 011 | My Rated Pictures Smart Albums | 2026-01-28 | Two new smart albums filtering by user ratings: MyRatedPicturesAlbum (all photos rated by current user), MyBestPicturesAlbum (top N rated with tie-inclusive logic), hidden from guests, requires SE license for best pictures, translations in 21 languages |
| 010 | LDAP Authentication Support | 2026-01-26 | Enterprise directory integration with auto-provisioning, role mapping via groups, TLS/SSL support, graceful degradation to local auth, comprehensive logging, full documentation (11 increments: I1-I11 complete) |
| 009 | Rating Ordering and Smart Albums | 2026-01-28 | Photo ordering by rating (average, user-specific), smart albums for rating ranges (Unrated, 1-5 Stars, Best Pictures with configurable count and tie-inclusive logic), translations in 21 languages |
| 008 | Shared Albums Visibility Control | 2026-01-28 | Server-side filtering for shared albums, visibility controls, admin configuration UI for share management |
| 007 | Photos and Albums Pagination | 2026-01-14 | New paginated API endpoints (/Album/{id}/head, /albums, /photos), configurable page sizes, three UI modes (infinite scroll, load more, page navigation), Smart/Tag album support |
| 006 | Photo Star Rating Filter | 2026-01-14 | Frontend filter control (5 clickable stars) for minimum rating threshold, toggle on/off behavior, Pinia state persistence, keyboard accessible, filters photos in album view |
| 005 | Album List View Toggle | 2026-01-04 | Toggle between grid/card and list view for albums, admin-configurable default, session-only user preference, full RTL support, drag-select compatible |
| 004 | Album Size Statistics Pre-computation | 2026-01-02 | Pre-computed album_size_statistics table with 7 size variant columns, RecomputeAlbumSizeJob with deduplication, Spaces.php refactored to use pre-computed values, event-driven updates |
| 003 | Album Computed Fields Pre-computation | 2026-01-02 | Event-driven pre-computation for 6 album fields (num_children, num_photos, min/max_taken_at, dual auto covers), AlbumBuilder virtual column removal, backfill/recovery commands, comprehensive test coverage |
| 002 | Worker Mode Support | 2025-12-28 | Docker worker mode with queue processing, auto-restart, configurable QUEUE_NAMES/WORKER_MAX_TIME, multi-container deployment |
| 001 | Photo Star Rating | 2025-12-27 | User ratings (1-5 stars), statistics aggregation, configurable visibility |

## Backlog

| Feature ID | Name | Priority | Notes |
|------------|------|----------|-------|
| _No backlog items yet_ | | | |

## Feature Directory Structure

Features are organized under `docs/specs/4-architecture/features/<NNN>-<feature-name>/`:

```
features/
├── 001-feature-name/
│   ├── spec.md    # Feature specification
│   ├── plan.md    # Implementation plan
│   └── tasks.md   # Task checklist
└── 002-another-feature/
    ├── spec.md
    ├── plan.md
    └── tasks.md
```

## How to Use This Roadmap

1. **Start a new feature:**
   - Assign next available feature ID (format: `###`)
   - Create feature directory: `features/<NNN>-<feature-name>/`
   - Author `spec.md` using [templates/feature-spec-template.md](../../templates/feature-spec-template.md)
   - Add row to Active Features table

2. **Track progress:**
   - Update Status column (Planning → In Progress → Testing → Complete)
   - Update timestamps regularly
   - Create `plan.md` and `tasks.md` once spec is approved

3. **Complete a feature:**
   - Move row from Active to Completed
   - Archive or remove feature directory
   - Update related knowledge map entries

4. **Add to backlog:**
   - Add row to Backlog table
   - No feature directory needed until promoted to Active

## Status Values

- **Planning** - Specification in progress
- **In Progress** - Active implementation
- **Testing** - Code complete, under verification
- **Blocked** - Waiting on dependencies or clarification
- **Complete** - Delivered and verified

## Priority Levels

- **P0** - Critical (security, data loss, blocking production)
- **P1** - High (major features, significant user impact)
- **P2** - Medium (enhancements, minor features)
- **P3** - Low (nice-to-have, future considerations)

---

*Last updated: 2026-02-26*
