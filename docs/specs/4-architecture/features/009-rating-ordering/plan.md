# Feature Plan 009 – Rating Ordering and Smart Albums

_Linked specification:_ `docs/specs/4-architecture/features/009-rating-ordering/spec.md`
_Status:_ Draft
_Last updated:_ 2026-01-16

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Vision & Success Criteria

**User Value:** Enable users to sort photos by rating and discover highly-rated content through dedicated smart albums. Rating-based smart albums provide curated views of the best content without manual album organization.

**Success Signals:**
- Photos can be sorted by rating (DESC, unrated last)
- 7 new smart albums accessible and functional (Unrated, 1★, 2★, 3★+, 4★+, 5★, Best Pictures [SE only])
- Each smart album has independent enable/disable and public/private settings
- `rating_avg` column stays in sync with rating statistics
- All existing tests pass, new tests cover all scenarios

**Quality Bars:**
- PHPStan level 6 passes
- All feature tests pass
- No N+1 query regressions
- Performance acceptable for 10k+ photos

## Scope Alignment

**In scope:**
- Add `rating_avg` column to photos table with index
- Sync `rating_avg` on rating create/update/delete
- Add `RATING` to `ColumnSortingPhotoType` enum
- Create 7 smart album classes extending `BaseSmartAlbum`
- Extend `SmartAlbumType` enum with 7 new cases
- Register new smart albums in `AlbumFactory`
- Add 8 config settings for enable/disable and best_pictures_count
- Add language translations for smart album titles
- Artisan command for backfilling `rating_avg`

**Out of scope:**
- Changes to the rating system itself (Feature 001)
- UI changes to sorting dropdown (config-driven, automatic)
- Custom threshold configuration for rating albums
- Album-level aggregate ratings

## Dependencies & Interfaces

| Dependency | Type | Notes |
|------------|------|-------|
| Feature 001 (Photo Star Rating) | Feature | Must be complete. Provides `rating_sum`, `rating_count` in statistics, `photo_ratings` table |
| `BaseSmartAlbum` | Class | Base class for all smart albums |
| `SmartAlbumType` | Enum | Must be extended with new cases |
| `AlbumFactory` | Class | Must register new smart album classes |
| `ColumnSortingPhotoType` | Enum | Must add `RATING` case |
| `Rating` action | Class | Must be modified to sync `rating_avg` |
| `configs` table | Database | Must add new config rows |

## Assumptions & Risks

**Assumptions:**
- Feature 001 is complete and stable
- Statistics table always has a record for rated photos
- Smart album patterns are consistent and can be followed
- Generated columns work across MySQL/PostgreSQL/SQLite

**Risks / Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|------------|
| Performance of Best Pictures tie-inclusion query | Medium | Use subquery for Nth rating, then single WHERE; add index |
| Database compatibility for NULL handling | Low | Use `COALESCE(rating_avg, -1) DESC` for cross-database index usage per Q-009-06 |
| Migration on large databases | Medium | Make migration additive; backfill via artisan command |
| Enum extension breaking existing code | Low | Follow existing pattern; add cases at end |

## Implementation Drift Gate

**Evidence Required:**
- All FR-009-* requirements have corresponding tests
- All S-009-* scenarios have test coverage
- PHPStan passes at level 6
- `php artisan test` passes
- Manual verification of smart album photo counts

**Commands to Rerun:**
```bash
vendor/bin/php-cs-fixer fix
php artisan test --filter=Rating
php artisan test --filter=SmartAlbum
make phpstan
```

## Increment Map

### I1 – Database Schema (rating_avg column)
_Goal:_ Add `rating_avg` column to photos table with index for sorting.

_Preconditions:_ Feature 001 complete, migrations run successfully.

_Steps:_
1. Create migration `add_rating_avg_to_photos`
2. Add `rating_avg DECIMAL(5,4) NULL` column (4 decimal places for fine granularity per Q-009-05)
3. Add index on `rating_avg`
4. Update Photo model with `$casts` for `rating_avg` as `decimal:4`

_Commands:_
```bash
php artisan make:migration add_rating_avg_to_photos
php artisan migrate
```

_Exit:_ Migration runs successfully, Photo model has `rating_avg` attribute.

---

### I2 – Rating Sync Logic
_Goal:_ Sync `rating_avg` column when photos are rated/unrated.

_Preconditions:_ I1 complete.

_Steps:_
1. Write test for rating_avg sync on new rating
2. Write test for rating_avg sync on rating update
3. Write test for rating_avg sync on rating removal (NULL)
4. Modify `Rating::do()` to update `photo.rating_avg` in transaction
5. Verify tests pass

_Commands:_
```bash
php artisan test --filter=PhotoRatingSyncTest
```

_Exit:_ `rating_avg` updates atomically with rating changes.

---

### I3 – Sorting by Rating
_Goal:_ Add rating as a photo sorting option.

_Preconditions:_ I1 complete.

_Steps:_
1. Add `RATING = 'rating'` case to `ColumnSortingPhotoType` enum
2. Update `SortingDecorator` to handle rating column with NULLS LAST
3. Write test for sorting photos by rating
4. Verify existing sorting tests still pass

_Commands:_
```bash
php artisan test --filter=Sorting
```

_Exit:_ Photos can be sorted by rating descending with unrated last.

---

### I4 – Config Settings Migration
_Goal:_ Add config rows for smart album enable/disable and best_pictures_count.

_Preconditions:_ None.

_Steps:_
1. Create migration `add_rating_smart_album_configs`
2. Add 7 enable configs (enable_unrated, enable_1_star, ..., enable_best_pictures)
3. Add best_pictures_count config (default 100)
4. Verify migration runs successfully

_Commands:_
```bash
php artisan make:migration add_rating_smart_album_configs
php artisan migrate
```

_Exit:_ All 8 config rows exist in database.

---

### I5 – SmartAlbumType Enum Extension
_Goal:_ Add 7 new smart album type cases to enum.

_Preconditions:_ None.

_Steps:_
1. Add enum cases: UNRATED, ONE_STAR, TWO_STARS, THREE_STARS, FOUR_STARS, FIVE_STARS, BEST_PICTURES
2. Extend `is_enabled()` method with match arms for new cases
3. Write unit test for enum values and is_enabled logic

_Commands:_
```bash
php artisan test --filter=SmartAlbumType
```

_Exit:_ Enum has 12 cases (5 existing + 7 new), is_enabled works for all.

---

### I6 – Smart Album Classes (Unrated, 1★, 2★)
_Goal:_ Create smart album classes for Unrated, 1★, and 2★ albums.

_Preconditions:_ I4, I5 complete.

_Steps:_
1. Create `UnratedAlbum` class with condition `rating_avg IS NULL`
2. Create `OneStarAlbum` class with condition `rating_avg >= 1.0 AND rating_avg < 2.0`
3. Create `TwoStarsAlbum` class with condition `rating_avg >= 2.0 AND rating_avg < 3.0`
4. Each class follows singleton pattern with `getInstance()`
5. Write tests for each album's photo filtering

_Commands:_
```bash
php artisan test --filter=UnratedAlbumTest
php artisan test --filter=OneStarAlbumTest
php artisan test --filter=TwoStarsAlbumTest
```

_Exit:_ Three smart album classes created and tested.

---

### I7 – Smart Album Classes (3★+, 4★+, 5★)
_Goal:_ Create smart album classes for 3★+, 4★+, and 5★ albums.

_Preconditions:_ I4, I5 complete.

_Steps:_
1. Create `ThreeStarsAlbum` class with condition `rating_avg >= 3.0`
2. Create `FourStarsAlbum` class with condition `rating_avg >= 4.0`
3. Create `FiveStarsAlbum` class with condition `rating_avg >= 5.0`
4. Each class follows singleton pattern with `getInstance()`
5. Write tests for each album's photo filtering

_Commands:_
```bash
php artisan test --filter=ThreeStarsAlbumTest
php artisan test --filter=FourStarsAlbumTest
php artisan test --filter=FiveStarsAlbumTest
```

_Exit:_ Three smart album classes created and tested.

---

### I8 – Best Pictures Smart Album (Lychee SE)
_Goal:_ Create Best Pictures smart album with tie-inclusion logic. **Requires Lychee SE license.**

_Preconditions:_ I4, I5 complete.

_Steps:_
1. Create `BestPicturesAlbum` class
2. Implement tie-inclusion logic:
   - Get rating of Nth photo
   - Include all photos with rating >= Nth photo's rating
3. Override `photos()` method for custom LIMIT behavior
4. Add Lychee SE license check in `is_enabled()` method
5. Write tests for cutoff behavior with and without ties
6. Write test verifying album hidden when SE not activated

_Commands:_
```bash
php artisan test --filter=BestPicturesAlbumTest
```

_Exit:_ Best Pictures album returns top N photos including ties; hidden when SE not activated.

---

### I9 – AlbumFactory Registration
_Goal:_ Register all new smart albums in AlbumFactory.

_Preconditions:_ I6, I7, I8 complete.

_Steps:_
1. Add 7 entries to `BUILTIN_SMARTS_CLASS` constant
2. Verify `getAllBuiltInSmartAlbums()` returns new albums when enabled
3. Verify `findAbstractAlbumOrFail()` resolves new album IDs
4. Write integration tests

_Commands:_
```bash
php artisan test --filter=AlbumFactory
```

_Exit:_ All 7 new smart albums registered and retrievable.

---

### I10 – Smart Album Sorting Override
_Goal:_ Rating smart albums sort by rating DESC instead of default.

_Preconditions:_ I6, I7, I8 complete.

_Steps:_
1. Override `getPhotosAttribute()` in rating smart albums to use rating sort
2. Unrated album uses `created_at DESC` as primary sort
3. Write tests verifying sort order

_Commands:_
```bash
php artisan test --filter=RatingSmartAlbumSortTest
```

_Exit:_ Rating smart albums return photos sorted by rating DESC.

---

### I11 – Language Translations
_Goal:_ Add localized titles for new smart albums.

_Preconditions:_ None.

_Steps:_
1. Add entries to `lang/en/gallery.php` under `smart_album` key
2. Add entries for other supported languages (or English fallback)
3. Verify titles display correctly

_Commands:_
```bash
# Manual verification
```

_Exit:_ Smart album titles localized.

---

### I12 – Backfill Artisan Command
_Goal:_ Create command to backfill rating_avg for existing installations.

_Preconditions:_ I1, I2 complete.

_Steps:_
1. Create `php artisan lychee:sync-rating-avg` command
2. Iterate all photos with statistics
3. Calculate and set `rating_avg = rating_sum / rating_count` or NULL
4. Add progress bar and completion message
5. Write test for command

_Commands:_
```bash
php artisan lychee:sync-rating-avg
php artisan test --filter=SyncRatingAvgCommandTest
```

_Exit:_ Command backfills rating_avg for all existing photos.

---

### I13 – Integration Tests
_Goal:_ End-to-end tests for smart album API and access control.

_Preconditions:_ I1-I12 complete.

_Steps:_
1. Test API returns new smart albums in gallery root
2. Test enable/disable config hides albums
3. Test public/private access control
4. Test boundary conditions (photos at exact thresholds)
5. Test Best Pictures tie inclusion

_Commands:_
```bash
php artisan test --filter=RatingSmartAlbumsIntegrationTest
```

_Exit:_ All integration tests pass.

---

### I14 – Quality Gate
_Goal:_ Final quality checks before feature complete.

_Preconditions:_ I1-I13 complete.

_Steps:_
1. Run php-cs-fixer
2. Run full test suite
3. Run PHPStan
4. Manual verification of smart albums in UI

_Commands:_
```bash
vendor/bin/php-cs-fixer fix
php artisan test
make phpstan
```

_Exit:_ All quality gates pass, feature ready for review.

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-009-01 | I3 | Sort photos by rating |
| S-009-02 | I2 | Photo rated → rating_avg updated |
| S-009-03 | I2 | Photo rating removed → rating_avg NULL |
| S-009-04 | I6 | Unrated album filtering |
| S-009-05 | I6 | 1★ album filtering |
| S-009-06 | I6 | 2★ album filtering |
| S-009-07 | I7 | 3★+ album filtering |
| S-009-08 | I7 | 4★+ album filtering |
| S-009-09 | I7 | 5★ album filtering |
| S-009-10 | I8 | Best Pictures top N |
| S-009-11 | I8 | Best Pictures ties |
| S-009-12 | I13 | Disable smart album |
| S-009-13 | I13 | Enable disabled album |
| S-009-14 | I13 | Set smart album public |
| S-009-15 | I13 | Set smart album private |
| S-009-16 | I7 | Boundary at 3.0 |
| S-009-17 | I6 | Boundary at 2.0 |
| S-009-18 | I13 | Photo access permissions |
| S-009-19 | I13 | NSFW filtering |

## Analysis Gate

_To be completed before implementation begins._

- [ ] Spec reviewed and requirements clear
- [ ] Dependencies identified and available
- [ ] Test strategy defined
- [ ] No open questions blocking implementation

## Exit Criteria

- [ ] All migrations run successfully
- [ ] `rating_avg` syncs correctly on all rating operations
- [ ] Sorting by rating works correctly
- [ ] All 7 smart albums functional with correct filtering
- [ ] Enable/disable configs work for all albums
- [ ] Public/private access control works
- [ ] Best Pictures tie-inclusion verified
- [ ] Backfill command works for existing data
- [ ] All tests pass (`php artisan test`)
- [ ] PHPStan level 6 passes
- [ ] php-cs-fixer clean
- [ ] Roadmap updated to "Complete"

## Follow-ups / Backlog

- Consider adding smart album photo count to gallery root display
- Potential optimization: cache Nth rating for Best Pictures
- Future: configurable thresholds for rating albums
- Future: rating-based sorting in album settings (per-album override)

---

*Last updated: 2026-01-16*
