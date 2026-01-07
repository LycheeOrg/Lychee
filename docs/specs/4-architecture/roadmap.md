# Development Roadmap

High-level planning document for Lychee features and architectural initiatives.

## Active Features

| Feature ID | Name | Status | Priority | Assignee | Started | Updated |
|------------|------|--------|----------|----------|---------|---------|
| 007 | Photos and Albums Pagination | Planning | P1 | Agent | 2026-01-07 | 2026-01-07 |
| 006 | Photo Star Rating Filter | Planning | P2 | Agent | 2026-01-03 | 2026-01-03 |
| 004 | Album Size Statistics Pre-computation | Planning | P1 | - | 2026-01-02 | 2026-01-02 |

## Completed Features

| Feature ID | Name | Completed | Notes |
|------------|------|-----------|-------|
| 005 | Album List View Toggle | 2026-01-04 | Toggle between grid/card and list view for albums, admin-configurable default, session-only user preference, full RTL support, drag-select compatible |
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

*Last updated: 2026-01-07*
