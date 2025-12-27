# Development Roadmap

High-level planning document for Lychee features and architectural initiatives.

## Active Features

| Feature ID | Name | Status | Priority | Assignee | Started | Updated |
|------------|------|--------|----------|----------|---------|---------|
| 001 | Photo Star Rating | Planning | P2 | User | 2025-12-27 | 2025-12-27 |

## Completed Features

| Feature ID | Name | Completed | Notes |
|------------|------|-----------|-------|
| _No completed features yet_ | | | |

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

*Last updated: 2025-12-27*
