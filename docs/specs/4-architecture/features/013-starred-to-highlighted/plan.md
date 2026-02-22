# Feature Plan 013 – Starred to Highlighted Rename

## Overview

Comprehensive rename of the `is_starred` / `starred` concept to `is_highlighted` / `highlighted` across DB, PHP, TypeScript, and translations.

## Implementation Steps

1. **Migration** – Create `2026_02_22_100000_rename_starred_to_highlighted.php`:
   - Insert `photo_ratings(photo_id, owner_id, 5)` for all `is_starred=true` photos (skip if duplicate).
   - Update `statistics.rating_sum += 5`, `statistics.rating_count += 1` for each inserted row.
   - Recompute `photos.rating_avg`.
   - Drop all compound indexes containing `is_starred`.
   - Rename column to `is_highlighted`.
   - Recreate indexes using `is_highlighted`.
   - Rename config key `enable_starred` → `enable_highlighted`.
   - Update `sorting_photos_col` type_range.

2. **PHP Enums** – `SmartAlbumType`, `ColumnSortingPhotoType`, `ColumnSortingType`.

3. **Smart Album class** – Create `HighlightedAlbum`, delete `StarredAlbum`.

4. **Photo Model + DTOs/Pipes** – Column name change throughout.

5. **HTTP layer** – `PhotoResource`, `SetPhotosStarredRequest` (parameter rename), Controller.

6. **Frontend** – `lychee.d.ts`, Vue components, stores, services, composables.

7. **Translations** – 22 languages × 2 files (`gallery.php`, `all_settings.php`).

8. **Tests** – Update factory data and assertion strings.

## Dependencies

- Feature 001 (photo_ratings table must exist)
- statistics table must have rating_sum / rating_count columns

---

*Last updated: 2026-02-22*
