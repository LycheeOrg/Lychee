# Feature Plan 011 – My Rated Pictures Smart Albums

| Field | Value |
|-------|-------|
| Feature ID | 011 |
| Status | In Progress |
| Last updated | 2026-01-28 |
| Linked spec | [spec.md](./spec.md) |
| Linked tasks | [tasks.md](./tasks.md) |

## Overview

Implement two new smart albums that filter photos based on the current user's ratings: "My Rated Pictures" (all photos rated by user) and "My Best Pictures" (top N photos rated by user with tie-inclusive logic). These albums use `whereHas` queries on the `ratings` relationship to filter by `user_id` instead of `owner_id`.

## Implementation Approach

Follow existing smart album patterns:
1. Extend `BaseSmartAlbum` similar to `StarredAlbum` and `BestPicturesAlbum`
2. Add enum cases to `SmartAlbumType`
3. Add configuration keys for enable/disable and count
4. Add translation keys
5. Implement query logic with proper user filtering
6. Write comprehensive tests

## Dependencies

- Feature 001 (Photo Star Rating) - requires `photo_ratings` table and `PhotoRating` model
- `BaseSmartAlbum` infrastructure
- `PhotoQueryPolicy` for security filtering
- ConfigManager for settings

## Increment Map

### I1 – Add SmartAlbumType Enum Cases

**Goal:** Add MY_RATED_PICTURES and MY_BEST_PICTURES to SmartAlbumType enum

**Preconditions:** None

**Steps:**
1. Read `app/Enum/SmartAlbumType.php`
2. Add `case MY_RATED_PICTURES = 'my_rated_pictures';` after BEST_PICTURES
3. Add `case MY_BEST_PICTURES = 'my_best_pictures';` after MY_RATED_PICTURES
4. Update `is_enabled()` method to include both new cases
5. For both cases, check Auth::check() first (hide from guest users)
6. For MY_BEST_PICTURES, also check config flag AND SE license (like BEST_PICTURES)
7. Add `use Illuminate\Support\Facades\Auth;` import at top of file

**Commands:**
- `make phpstan` (verify types)
- `php artisan test --filter=SmartAlbumType` (if tests exist)

**Exit:** SmartAlbumType enum has two new cases with proper enable checks

**Implements:** FR-011-06, ENUM-011-01

---

### I2 – Add Translation Keys

**Goal:** Add translation keys for album names and config descriptions

**Preconditions:** I1 complete

**Steps:**
1. Edit `lang/en/gallery.php`, add to `smart_album` array:
   - `'my_rated_pictures' => 'My Rated Pictures',`
   - `'my_best_pictures' => 'My Best Pictures',`
2. Edit `lang/en/all_settings.php`, add to `descriptions` array:
   - `'enable_my_rated_pictures' => 'Enable My Rated Pictures smart album.',`
   - `'enable_my_best_pictures' => 'Enable My Best Pictures smart album.',`
   - `'my_best_pictures_count' => 'Number of top-rated photos to display in My Best Pictures smart album.',`

**Commands:**
- None (no tests for translations)

**Exit:** Translation keys exist for both albums

**Implements:** NFR-011-04, I18N-011-01 through I18N-011-04

---

### I3 – Add Configuration Keys (Migration)

**Goal:** Add database config entries for the new smart albums

**Preconditions:** I2 complete

**Steps:**
1. Create migration: `php artisan make:migration add_my_rated_pictures_config_keys`
2. Add three config entries in `up()`:
   ```php
   Configs::create([
       'key' => 'enable_my_rated_pictures',
       'value' => '1',
       'cat' => 'Smart Albums',
       'type_range' => 'bool',
       'description' => 'Enable My Rated Pictures smart album.'
   ]);
   
   Configs::create([
       'key' => 'enable_my_best_pictures',
       'value' => '1',
       'cat' => 'Smart Albums',
       'type_range' => 'bool',
       'description' => 'Enable My Best Pictures smart album.'
   ]);
   
   Configs::create([
       'key' => 'my_best_pictures_count',
       'value' => '50',
       'cat' => 'Smart Albums',
       'type_range' => 'positive',
       'description' => 'Number of photos in My Best Pictures album.'
   ]);
   ```
3. Add `down()` method to remove config keys
4. Run migration: `php artisan migrate`

**Commands:**
- `php artisan migrate`
- `php artisan test` (verify no breaks)

**Exit:** Three config keys exist in database

**Implements:** CFG-011-01, CFG-011-02, CFG-011-03

---

### I4 – Implement MyRatedPicturesAlbum

**Goal:** Create MyRatedPicturesAlbum smart album class

**Preconditions:** I1-I3 complete

**Steps:**
1. Create `app/SmartAlbums/MyRatedPicturesAlbum.php`
2. Extend BaseSmartAlbum
3. Implement singleton pattern with getInstance()
4. Override photos() method to add join with photo_ratings and user_id filter
5. Add sorting: order by rating DESC, created_at DESC
6. Ensure query only runs for authenticated users (return empty if guest)

**Implementation:**
```php
<?php

namespace App\SmartAlbums;

use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyRatedPicturesAlbum extends BaseSmartAlbum
{
    public const ID = SmartAlbumType::MY_RATED_PICTURES->value;

    protected function __construct()
    {
        parent::__construct(
            id: SmartAlbumType::MY_RATED_PICTURES,
            smart_condition: fn (Builder $q) => $q->whereHas('ratings', function ($query) {
                $query->where('user_id', '=', Auth::id() ?? 0);
            })
        );
    }

    public static function getInstance(): self
    {
        return new self();
    }
    
    public function photos(): Builder
    {
        // Get base query from parent
        $query = parent::photos();
        
        // Add ordering: user's rating DESC, then created_at DESC
        // Join with photo_ratings to access the user's rating
        return $query->leftJoin('photo_ratings', function ($join) {
            $join->on('photos.id', '=', 'photo_ratings.photo_id')
                 ->where('photo_ratings.user_id', '=', Auth::id() ?? 0);
        })
        ->orderByRaw('photo_ratings.rating DESC')
        ->orderBy('photos.created_at', 'desc');
    }
}
```

**Commands:**
- `make phpstan`
- `php artisan test --filter=MyRatedPicturesAlbum` (after tests written)

**Exit:** MyRatedPicturesAlbum class exists and compiles

**Implements:** DO-011-01, FR-011-01

---

### I5 – Implement MyBestPicturesAlbum

**Goal:** Create MyBestPicturesAlbum with tie-inclusive logic

**Preconditions:** I4 complete

**Steps:**
1. Create `app/SmartAlbums/MyBestPicturesAlbum.php`
2. Follow BestPicturesAlbum pattern but filter by user_id in ratings
3. Override getPhotosAttribute() to implement tie logic
4. Add getCutoffRating() helper method
5. Use my_best_pictures_count config

**Implementation:** Similar to BestPicturesAlbum but with user_id filtering in whereHas

**Commands:**
- `make phpstan`
- `php artisan test --filter=MyBestPicturesAlbum` (after tests written)

**Exit:** MyBestPicturesAlbum class exists with tie logic

**Implements:** DO-011-02, FR-011-02

---

### I6 – Unit Tests for MyRatedPicturesAlbum

**Goal:** Test MyRatedPicturesAlbum query logic and edge cases

**Preconditions:** I4 complete

**Steps:**
1. Create `tests/Feature_v2/SmartAlbums/MyRatedPicturesAlbumTest.php`
2. Extend BaseApiWithDataTest
3. Test scenarios:
   - S-011-01: Authenticated user sees rated photos
   - S-011-03: Guest user cannot see album (not in list)
   - S-011-05: User with 0 ratings sees empty result (but album is visible)
   - S-011-06: User rates photo, appears in album
   - S-011-12: Private photo not shown if no permission
4. Verify sorting: highest rated first, then newest

**Commands:**
- `php artisan test --filter=MyRatedPicturesAlbumTest`

**Exit:** All tests pass, coverage for scenarios

**Implements:** Test strategy, S-011-01, S-011-03, S-011-05, S-011-06, S-011-12

---

### I7 – Unit Tests for MyBestPicturesAlbum

**Goal:** Test MyBestPicturesAlbum tie logic and edge cases

**Preconditions:** I5 complete

**Steps:**
1. Create `tests/Feature_v2/SmartAlbums/MyBestPicturesAlbumTest.php`
2. Extend BaseApiWithDataTest
3. Test scenarios:
   - S-011-02: Top N photos with ties
   - S-011-04: Guest user cannot see album (not in list)
   - S-011-07: All same rating, all included
   - S-011-08: Exact N photos, no ties
   - S-011-09: Tie at cutoff, all included
   - S-011-11: No SE license, album disabled

**Commands:**
- `php artisan test --filter=MyBestPicturesAlbumTest`

**Exit:** All tests pass, tie logic verified

**Implements:** Test strategy, S-011-02, S-011-04, S-011-07, S-011-08, S-011-09, S-011-11

---

### I8 – Integration Tests

**Goal:** Test end-to-end album behavior with config flags

**Preconditions:** I6-I7 complete

**Steps:**
1. Create `tests/Feature_v2/SmartAlbums/MyRatedSmartAlbumsIntegrationTest.php`
2. Test config enable/disable (S-011-10)
3. Test SE license requirement for My Best Pictures (S-011-11)
4. Test photo visibility filtering (S-011-12)
5. Test interaction with rating updates

**Commands:**
- `php artisan test --filter=MyRatedSmartAlbumsIntegrationTest`

**Exit:** Integration tests pass

**Implements:** S-011-10, S-011-11, FR-011-03, FR-011-04, FR-011-05

---

### I9 – Quality Gate & Documentation

**Goal:** Run full quality gate and finalize documentation

**Preconditions:** I1-I8 complete

**Steps:**
1. Run PHP quality checks:
   - `vendor/bin/php-cs-fixer fix`
   - `php artisan test`
   - `make phpstan`
2. Update knowledge map if needed
3. Mark all tasks complete in tasks.md
4. Update roadmap status to Complete

**Commands:**
- `vendor/bin/php-cs-fixer fix`
- `php artisan test`
- `make phpstan`

**Exit:** All checks pass, documentation complete

**Implements:** NFR-011-02, NFR-011-03

---

## Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|------------|--------|------------|
| Query performance with many ratings | Low | Medium | Proper indexing on photo_ratings(user_id, rating), test with 1000+ ratings |
| Guest user edge case | Low | Low | Explicit Auth::check() guards, tests cover scenario |
| SE license check fails | Low | Medium | Follow BestPicturesAlbum pattern exactly |
| Tie logic incorrect | Medium | Medium | Extensive tests for S-011-07, S-011-08, S-011-09 |

## Success Criteria

- [ ] Both smart albums appear in UI when enabled
- [ ] My Rated Pictures shows all user-rated photos, sorted correctly
- [ ] My Best Pictures shows top N with ties handled
- [ ] Guest users see empty results
- [ ] Config flags work correctly
- [ ] SE license requirement enforced for My Best Pictures
- [ ] All tests pass
- [ ] PHPStan level 6 passes
- [ ] Code follows project conventions

## Follow-ups / Backlog

- Consider adding "My Unrated Pictures" (photos user can see but hasn't rated)
- Add sorting options (by date, by album, etc.)
- Add filtering to show only photos from specific albums
- Consider admin UI for configuring counts

---

*Last updated: 2026-01-28*
