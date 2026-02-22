# Feature 013 – Starred to Highlighted Rename

| Field | Value |
|-------|-------|
| Status | Active |
| Last updated | 2026-02-22 |
| Owners | ildyria |
| Linked plan | `docs/specs/4-architecture/features/013-starred-to-highlighted/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/013-starred-to-highlighted/tasks.md` |
| Roadmap entry | #13 |

> Guardrail: This specification is the single normative source of truth for the feature.

## Overview

The `is_starred` flag on photos and the `starred` smart album have served as a "highlight" mechanism for photographers to surface their best work. Following discussion [#4056](https://github.com/LycheeOrg/Lychee/discussions/4056), the feature is being renamed to better reflect its intent. The `is_starred` column is renamed to `is_highlighted`, existing starred photos receive a `5`-star rating entry from their owner (to preserve sorting semantics), rating averages are recomputed, and the `starred` smart album becomes `highlighted`. All translations are updated accordingly.

## Goals

1. Rename DB column `is_starred` → `is_highlighted` with all associated indexes recreated.
2. For every photo with `is_starred = true`, insert a `photo_ratings` row (owner, rating=5) if one doesn't already exist; update statistics and recompute `rating_avg`.
3. Rename `SmartAlbumType::STARRED` → `HIGHLIGHTED` (value `'highlighted'`).
4. Rename the `StarredAlbum` PHP class to `HighlightedAlbum`.
5. Rename `enable_starred` config key → `enable_highlighted`.
6. Update all PHP, TypeScript/Vue, and translation references.

## Non-Goals

- Renaming the `Photo::star` API endpoint (action naming is separate from data model).
- Renaming `can_star` right (ability to highlight still called "star" in rights).
- Changing star-rating UI icons or semantics for numerical ratings (1–5 stars).

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path |
|----|-------------|--------------|-----------------|--------------|
| FR-013-01 | Each photo with `is_starred=true` MUST have a 5-star rating from its owner after migration. | `photo_ratings` row inserted (or already exists). | Check uniqueness constraint on `(photo_id, user_id)`. | Skip if owner already rated that photo. |
| FR-013-02 | `rating_avg` for affected photos MUST be recomputed after inserting new ratings. | `photos.rating_avg` updated via statistics. | Query `photo_ratings` for affected photos. | No-op if already computed. |
| FR-013-03 | DB column `is_starred` MUST be renamed `is_highlighted` with all compound indexes recreated. | Schema migration succeeds; rollback recreates original. | `Schema::hasColumn` guards. | Migration halts on DB error. |
| FR-013-04 | `SmartAlbumType::STARRED` case (value `'starred'`) MUST become `HIGHLIGHTED` (value `'highlighted'`). | Smart album loads under new ID. | Unit test enum value. | N/A. |
| FR-013-05 | Config key `enable_starred` MUST be renamed `enable_highlighted` via migration. | Setting toggles the Highlighted album. | Migration updates `configs` table. | Rollback restores old key name. |
| FR-013-06 | All translations MUST expose a `highlighted` key in `gallery.smart_album` and `enable_highlighted` in `all_settings`. | Gallery renders correct label. | Review all language files. | N/A. |
| FR-013-07 | The `is_highlighted` property MUST be exposed in `PhotoResource` and the TypeScript types. | Frontend accesses `photo.is_highlighted`. | TS type check. | N/A. |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement |
|----|-------------|--------|-------------|
| NFR-013-01 | Migration MUST be safe on large datasets (chunked inserts). | Production resilience. | Chunk size ≤ 500 rows. |
| NFR-013-02 | All PHPStan level-6 checks MUST pass after rename. | Code quality. | `make phpstan`. |
| NFR-013-03 | All PHP tests MUST pass after rename. | Regression safety. | `php artisan test`. |

## Branch & Scenario Matrix

| Scenario ID | Description |
|-------------|-------------|
| S-013-01 | Photo with `is_starred=true` and owner → rating row inserted, avg recomputed. |
| S-013-02 | Photo with `is_starred=true` but owner already has rating 5 → skip. |
| S-013-03 | Photo with `is_starred=false` → no rating row inserted. |
| S-013-04 | `highlighted` smart album returned by Album factory. |
| S-013-05 | `enable_highlighted` config key controls album visibility. |
| S-013-06 | Sorting by `is_highlighted` column works. |

## Test Strategy

- **Unit:** `tests/Unit/Enum/SmartAlbumTypeTest.php` — assert enum value is `'highlighted'`.
- **Unit:** `tests/Unit/CoverageTest.php` — update STARRED map entry.
- **Feature:** `tests/Feature_v2/SmartAlbums/OverridePermissionsTest.php` — update album id and title.
- **Feature:** `tests/Feature_v2/Album/AlbumHeadEndpointTest.php` — use `highlighted` album id.
- **Precomputing:** All cover-selection tests use `is_highlighted` in factory data.

---

*Last updated: 2026-02-22*
