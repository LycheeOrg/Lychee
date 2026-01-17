# Feature 009 Tasks – Rating Ordering and Smart Albums

_Status: Draft_
_Last updated: 2026-01-16_

> Keep this checklist aligned with the feature plan increments. Stage tests before implementation, record verification commands beside each task, and prefer bite-sized entries (≤90 minutes).
> **Mark tasks `[x]` immediately** after each one passes verification—do not batch completions. Update the roadmap status when all tasks are done.
> When referencing requirements, keep feature IDs (`FR-`), non-goal IDs (`NFR-`), and scenario IDs (`S-009-`) inside the same parentheses immediately after the task title.
> When new high- or medium-impact questions arise during execution, add them to [docs/specs/4-architecture/open-questions.md](../../open-questions.md) instead of informal notes.

## Increment I1 – Database Schema

- [x] T-009-01 – Create migration for `rating_avg` column on photos table (FR-009-01, S-009-02).
  _Intent:_ Add `rating_avg DECIMAL(5,4) NULL` column with index.
  _Verification commands:_
  - `php artisan migrate`
  - `php artisan migrate:rollback && php artisan migrate`
  _Notes:_ Column should be nullable (NULL = unrated).

- [x] T-009-02 – Update Photo model with `rating_avg` cast (FR-009-01).
  _Intent:_ Add `rating_avg` to `$casts` array as `decimal:4`.
  _Verification commands:_
  - `make phpstan`

## Increment I2 – Rating Sync Logic

- [x] T-009-03 – Write test for rating_avg sync on new rating (FR-009-14, S-009-02).
  _Intent:_ Test that creating a rating updates photo.rating_avg.
  _Verification commands:_
  - `php artisan test --filter=PhotoRatingSyncTest`
  _Notes:_ Test in `tests/Feature_v2/Photo/`.

- [x] T-009-04 – Write test for rating_avg sync on rating update (FR-009-14, S-009-02).
  _Intent:_ Test that updating a rating updates photo.rating_avg.
  _Verification commands:_
  - `php artisan test --filter=PhotoRatingSyncTest`

- [x] T-009-05 – Write test for rating_avg sync on rating removal (FR-009-14, S-009-03).
  _Intent:_ Test that removing rating (rating=0) sets photo.rating_avg to NULL.
  _Verification commands:_
  - `php artisan test --filter=PhotoRatingSyncTest`

- [x] T-009-06 – Modify Rating::do() to sync rating_avg (FR-009-14).
  _Intent:_ Update photo.rating_avg in same transaction as statistics update.
  _Verification commands:_
  - `php artisan test --filter=PhotoRatingSyncTest`
  - `php artisan test --filter=PhotoRating`
  _Notes:_ Calculate as `rating_sum / rating_count` or NULL if count = 0.

## Increment I3 – Sorting by Rating

- [x] T-009-07 – Add RATING_AVG case to ColumnSortingPhotoType enum (FR-009-02).
  _Intent:_ Add `RATING_AVG = 'rating_avg'` case to enum.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-08 – Update SortingDecorator for rating column (FR-009-02, S-009-01).
  _Intent:_ Handle rating column with `COALESCE(rating_avg, -1) DESC` for cross-database index usage.
  _Verification commands:_
  - `php artisan test --filter=Sorting`
  _Notes:_ Use `COALESCE(rating_avg, -1)` pattern for fastest indexed ordering across MySQL/MariaDB/PostgreSQL/SQLite.

- [x] T-009-09 – Write test for sorting photos by rating (FR-009-02, S-009-01).
  _Intent:_ Test photos sorted by rating DESC, unrated last.
  _Verification commands:_
  - `php artisan test --filter=PhotoSortingByRatingTest`

## Increment I4 – Config Settings Migration

- [x] T-009-10 – Create migration for rating smart album configs (FR-009-11).
  _Intent:_ Add 8 config rows: 7 enable flags + best_pictures_count.
  _Verification commands:_
  - `php artisan migrate`
  _Notes:_ All enable flags default true, best_pictures_count default 100.

## Increment I5 – SmartAlbumType Enum Extension

- [x] T-009-11 – Add 7 new cases to SmartAlbumType enum (FR-009-13).
  _Intent:_ Add UNRATED, ONE_STAR, TWO_STARS, THREE_STARS, FOUR_STARS, FIVE_STARS, BEST_PICTURES.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-12 – Extend is_enabled() method in SmartAlbumType (FR-009-11, FR-009-13).
  _Intent:_ Add match arms for new cases reading enable configs. Best Pictures also checks Lychee SE.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ BEST_PICTURES requires `$config_manager->getValueAsBool('enable_best_pictures') && VerifyInterface::is_supporter()`.

- [x] T-009-13 – Write unit test for SmartAlbumType enum (FR-009-13).
  _Intent:_ Test enum values and is_enabled logic for all new cases.
  _Verification commands:_
  - `php artisan test --filter=SmartAlbumTypeTest`

## Increment I6 – Smart Album Classes (Unrated, 1★, 2★)

- [x] T-009-14 – Create UnratedAlbum class (FR-009-03, S-009-04).
  _Intent:_ Extend BaseSmartAlbum with condition `rating_avg IS NULL`.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Follow StarredAlbum pattern with getInstance().

- [x] T-009-15 – Write test for UnratedAlbum filtering (FR-009-03, S-009-04).
  _Intent:_ Test album contains only photos with no ratings.
  _Verification commands:_
  - `php artisan test --filter=UnratedAlbumTest`

- [x] T-009-16 – Create OneStarAlbum class (FR-009-04, S-009-05).
  _Intent:_ Extend BaseSmartAlbum with condition `rating_avg >= 1.0 AND rating_avg < 2.0`.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-17 – Write test for OneStarAlbum filtering (FR-009-04, S-009-05, S-009-17).
  _Intent:_ Test album contains only photos with 1.0 <= rating_avg < 2.0.
  _Verification commands:_
  - `php artisan test --filter=OneStarAlbumTest`
  _Notes:_ Include boundary test at exactly 2.0 (should be excluded).

- [x] T-009-18 – Create TwoStarsAlbum class (FR-009-05, S-009-06).
  _Intent:_ Extend BaseSmartAlbum with condition `rating_avg >= 2.0 AND rating_avg < 3.0`.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-19 – Write test for TwoStarsAlbum filtering (FR-009-05, S-009-06).
  _Intent:_ Test album contains only photos with 2.0 <= rating_avg < 3.0.
  _Verification commands:_
  - `php artisan test --filter=TwoStarsAlbumTest`

## Increment I7 – Smart Album Classes (3★+, 4★+, 5★)

- [x] T-009-20 – Create ThreeStarsAlbum class (FR-009-06, S-009-07, S-009-16).
  _Intent:_ Extend BaseSmartAlbum with condition `rating_avg >= 3.0`.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-21 – Write test for ThreeStarsAlbum filtering (FR-009-06, S-009-07, S-009-16).
  _Intent:_ Test album contains photos with rating_avg >= 3.0 (includes 4★, 5★).
  _Verification commands:_
  - `php artisan test --filter=ThreeStarsAlbumTest`
  _Notes:_ Include boundary test at exactly 3.0 (should be included).

- [x] T-009-22 – Create FourStarsAlbum class (FR-009-07, S-009-08).
  _Intent:_ Extend BaseSmartAlbum with condition `rating_avg >= 4.0`.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-23 – Write test for FourStarsAlbum filtering (FR-009-07, S-009-08).
  _Intent:_ Test album contains photos with rating_avg >= 4.0.
  _Verification commands:_
  - `php artisan test --filter=FourStarsAlbumTest`

- [x] T-009-24 – Create FiveStarsAlbum class (FR-009-08, S-009-09).
  _Intent:_ Extend BaseSmartAlbum with condition `rating_avg >= 5.0`.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-25 – Write test for FiveStarsAlbum filtering (FR-009-08, S-009-09).
  _Intent:_ Test album contains only photos with perfect 5.0 rating.
  _Verification commands:_
  - `php artisan test --filter=FiveStarsAlbumTest`

## Increment I8 – Best Pictures Smart Album (Lychee SE)

- [x] T-009-26 – Create BestPicturesAlbum class (FR-009-09, S-009-10, S-009-11).
  _Intent:_ Extend BaseSmartAlbum with tie-inclusion logic for top N photos.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Override getPhotosAttribute() method to implement tie logic.

- [x] T-009-27 – Write test for BestPicturesAlbum basic cutoff (FR-009-09, S-009-10).
  _Intent:_ Test album returns top N photos by rating.
  _Verification commands:_
  - `php artisan test --filter=BestPicturesAlbumTest`

- [x] T-009-28 – Write test for BestPicturesAlbum tie inclusion (FR-009-09, S-009-11).
  _Intent:_ Test album includes ties (may show > N photos).
  _Verification commands:_
  - `php artisan test --filter=BestPicturesAlbumTest`

- [x] T-009-29 – Write test for BestPicturesAlbum SE requirement (FR-009-09).
  _Intent:_ Test album is hidden when Lychee SE not activated.
  _Verification commands:_
  - `php artisan test --filter=BestPicturesAlbumTest`

## Increment I9 – AlbumFactory Registration

- [x] T-009-30 – Add 7 entries to AlbumFactory::BUILTIN_SMARTS_CLASS (FR-009-13).
  _Intent:_ Register all new smart album classes in factory.
  _Verification commands:_
  - `make phpstan`

- [x] T-009-31 – Write test for AlbumFactory with new smart albums (FR-009-13).
  _Intent:_ Test getAllBuiltInSmartAlbums() returns new albums when enabled.
  _Verification commands:_
  - `php artisan test --filter=AlbumFactoryTest`

## Increment I10 – Smart Album Sorting Override

- [x] T-009-32 – Override sorting in rating smart albums (FR-009-10).
  _Intent:_ Rating albums sort by rating_avg DESC, Unrated uses created_at DESC.
  _Verification commands:_
  - `make phpstan`
  _Notes:_ Override getPhotosAttribute() or add custom sorting criterion.

- [x] T-009-33 – Write test for rating smart album sort order (FR-009-10).
  _Intent:_ Test photos returned in rating DESC order.
  _Verification commands:_
  - `php artisan test --filter=RatingSmartAlbumSortTest`

## Increment I11 – Language Translations

- [x] T-009-34 – Add smart album titles to lang/en/gallery.php (FR-009-13).
  _Intent:_ Add localized titles under smart_album key.
  _Verification commands:_
  - Manual verification
  _Notes:_ Keys: unrated, one_star, two_stars, three_stars, four_stars, five_stars, best_pictures. COMPLETED: All translations already present.

## Increment I13 – Integration Tests

- [ ] T-009-37 – Write integration test for smart album API responses (S-009-12, S-009-13).
  _Intent:_ Test API returns new smart albums, enable/disable works.
  _Verification commands:_
  - `php artisan test --filter=RatingSmartAlbumsIntegrationTest`
  _Notes:_ BLOCKED - Requires API routes `/api/v2/gallery/albums` and `/api/v2/smart-album/{album}` from earlier tasks

- [ ] T-009-38 – Write integration test for smart album access control (S-009-14, S-009-15).
  _Intent:_ Test public/private access control for rating smart albums.
  _Verification commands:_
  - `php artisan test --filter=RatingSmartAlbumsIntegrationTest`
  _Notes:_ BLOCKED - Requires API routes and `public_smart_albums` config from earlier tasks

- [ ] T-009-39 – Write integration test for photo access permissions (S-009-18).
  _Intent:_ Test smart albums respect photo access permissions.
  _Verification commands:_
  - `php artisan test --filter=RatingSmartAlbumsIntegrationTest`
  _Notes:_ BLOCKED - Requires API routes from earlier tasks

- [ ] T-009-40 – Write integration test for NSFW filtering (S-009-19).
  _Intent:_ Test smart albums respect NSFW filtering settings.
  _Verification commands:_
  - `php artisan test --filter=RatingSmartAlbumsIntegrationTest`
  _Notes:_ BLOCKED - Requires API routes from earlier tasks

## Increment I14 – Quality Gate

- [ ] T-009-41 – Run php-cs-fixer on all modified files (NFR-009-04).
  _Intent:_ Ensure code style compliance.
  _Verification commands:_
  - `vendor/bin/php-cs-fixer fix`

- [ ] T-009-42 – Run full test suite (NFR-009-06).
  _Intent:_ Ensure no regressions.
  _Verification commands:_
  - `php artisan test`

- [ ] T-009-43 – Run PHPStan level 6 (NFR-009-04).
  _Intent:_ Ensure static analysis passes.
  _Verification commands:_
  - `make phpstan`

- [ ] T-009-44 – Update roadmap to Complete.
  _Intent:_ Mark feature as complete in roadmap.
  _Verification commands:_
  - Manual verification

## Notes / TODOs

- Best Pictures album requires Lychee SE license activation
- Sorting by rating uses `COALESCE(rating_avg, -1) DESC` for cross-database index usage (pushes unrated to end)
- Boundary values for bucket albums: 1★ is [1.0, 2.0), 2★ is [2.0, 3.0)
- Threshold albums: 3★+ is [3.0, ∞), 4★+ is [4.0, ∞), 5★ is [5.0, 5.0]
- Unrated album condition: `rating_avg IS NULL` (not `rating_avg = 0`)
- rating_avg uses DECIMAL(5,4) for fine granularity (0.0001 increments)

---

_Last updated: 2026-01-16_
