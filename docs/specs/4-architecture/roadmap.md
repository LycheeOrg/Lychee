# Development Roadmap

High-level planning document for Lychee features and architectural initiatives.

## Active Features

| Feature ID | Name | Status | Priority | Assignee | Started | Updated | Progress |
|------------|------|--------|----------|----------|---------|---------|----------|

## Paused Features

| Feature ID | Name | Status | Priority | Reason | Paused Date |
|------------|------|--------|----------|--------|-------------|
| - | - | - | - | - | - |

## Completed Features

| Feature ID | Name | Completed | Notes |
|------------|------|-----------|-------|
| 037 | Admin Dashboard & `/admin/` URL Reorg | 2026-04-22 | Config migration (`use_admin_dashboard` toggle), `AdminStatsService` with 5-min cache, `GET /api/v2/Admin/Stats` endpoint, 9 admin views relocated to `views/admin/`, `AdminDashboard.vue` tile grid + stats panel + Refresh, left-menu collapse toggle, 22-locale i18n, 13 backend tests passing, TypeScript/PHPStan clean. |
| 034 | Bulk Album Edit | 2026-04-12 | Spec, plan, tasks drafted. 25 tasks across 11 increments (I1 backend scaffold, I2-I6 REST endpoints, I7-I10 frontend, I11 quality gates). 4 open questions (Q-034-01 to Q-034-04; 1 high, 2 medium, 1 low). Ready to begin T-034-01 once Q-034-03 resolved. |
| 032 | Security Advisories Check | 2026-04-06 | Spec, plan, tasks drafted. 18 tasks across 6 increments (I1 config/DTO, I2 fetch service, I3 diagnostic pipe, I4 REST endpoint, I5 frontend modal, I6 quality gates). All open questions resolved in spec. Ready to begin T-032-01. |
| 030 | AI Vision Service | 2026-03-15 | Spec, plan, tasks drafted. 43 tasks across 19 increments (I1–I3 Python service, I4–I12 PHP backend, I13–I18 frontend, I19 docs). Q-030-01 through Q-030-12 resolved. 13 new open questions (Q-030-13 through Q-030-25) — 6 high, 7 medium. I1–I3 can start; I8 blocked on Q-030-13; I10 blocked on Q-030-14, Q-030-15, Q-030-17. |
| 029 | Camera Capture | 2026-03-18 | "Take Photo" in `+` add menu (album and root views). CameraCapture.vue modal: live video → canvas capture → JPEG preview → push to existing UploadPanel queue. Secure-context guard, mobile layout fixes, `Permissions-Policy: camera=(self)` header. No backend changes. |
| 028 | Search UI Refactor | 2026-05-30 | Full refactor: simple input + collapsible advanced panel (17 fields: title, description, location, tags, date range, type, orientation, rating, EXIF fields). Token assembler/parser composable. No-debounce on-demand search. Auto-scroll to first result. vue-tsc clean, 74 PHP tests passed, PHPStan 0 errors. |
| 026 | Album Photo Tag Filter | 2026-03-09 | Spec, plan, tasks complete. 76 tasks across 9 increments (~32h estimated). All open questions resolved. Ready to begin Task 1.1. |
| 025 | Dynamic Landing Background Options | 2026-03-03 | Spec, plan, tasks completed |
| 024 | CLI Sync File-List Support | 2026-03-03 | `lychee:sync` now accepts individual file paths alongside directories; `Exec::doFiles()` added; `ImportImageJob` accepts nullable Album; 7 new tests. |
| 023 | Remember Me Login | 2026-03-01 | Spec, plan, tasks drafted. Implementation completed. |
| 022 | Contact Form | 2026-03-01 | Spec, plan, and tasks drafted. Supports-only feature: visitor form (public page), admin message management page, configurable sample Q&A, security question, consent text, privacy URL, custom submit button. 16 increments planned (~16 hours). Implementation completed. |
| 021 | Album Variant ZIP Download | 2026-02-28 | Spec, plan, tasks drafted. Starting implementation. |
| 020 | Raw Upload Support | 2026-02-28 | All 47 tasks done. RAW=0 enum shift, 4 migrations, RawToJpeg converter, DetectAndStoreRaw + CreateRawSizeVariant pipes, download gating, frontend RAW download button (22 langs), 38 tests passing. PHPStan 0 errors, php-cs-fixer clean, knowledge map + image-processing ref updated. |
| 019 | Friendly URLs (Album Slugs) | 2026-02-28 | All 24 tasks done. Migration, model, SlugRule, middleware, update requests, feature tests (26 tests/1048 assertions), frontend UI, translations (22 langs). Quality gates: PHPStan 0 errors, php-cs-fixer clean, npm build/check/format clean. |
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

*Last updated: 2026-04-22 (Feature 037 completed — Admin Dashboard & `/admin/` URL Reorg)*
