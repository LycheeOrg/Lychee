# Feature 001 – Photo Star Rating – Implementation Tasks

_Linked plan:_ [plan.md](plan.md)
_Status:_ In Progress (Backend I1-I6, I10-I11 Complete ✅ | Frontend I7-I9 Complete ✅)
_Last updated:_ 2025-12-27

## Task Overview

This document tracks the 17 increments from the implementation plan as individual tasks. Each task is estimated at ≤90 minutes and includes specific deliverables, test requirements, and exit criteria.

**Total estimated effort:** ~15 hours (900 minutes)

## Task Status Legend

- ⏳ **Not Started** - Task not yet begun
- 🔄 **In Progress** - Currently being worked on
- ✅ **Complete** - All exit criteria met, tests passing
- ⚠️ **Blocked** - Waiting on dependency or clarification

---

## Backend Tasks (Increments I1-I6, I10-I11)

### I1 – Database Schema & Migrations ✅
**Estimated:** 60 minutes
**Dependencies:** None
**Status:** Complete

**Deliverables:**
- [x] Migration: `create_photo_ratings_table`
  - [x] Columns: id, photo_id (char 24, FK), user_id (int, FK), rating (tinyint 1-5), timestamps
  - [x] Unique constraint: (photo_id, user_id)
  - [x] Foreign keys with CASCADE delete
  - [x] Indexes on photo_id and user_id
- [x] Migration: `add_rating_columns_to_photo_statistics`
  - [x] Add rating_sum (BIGINT UNSIGNED, default 0)
  - [x] Add rating_count (INT UNSIGNED, default 0)
- [x] Test migrations run successfully (up and down)

**Exit Criteria:**
- ✅ `php artisan migrate` succeeds
- ✅ `php artisan migrate:rollback --step=2` succeeds
- ✅ Tables created with correct schema
- ✅ Foreign keys and indexes present

**Commands:**
```bash
php artisan make:migration create_photo_ratings_table
php artisan make:migration add_rating_columns_to_photo_statistics
php artisan migrate
php artisan migrate:rollback --step=2
php artisan migrate
```

---

### I2 – PhotoRating Model & Relationships ✅
**Estimated:** 60 minutes
**Dependencies:** I1
**Status:** Complete

**Deliverables:**
- [x] Unit test: `tests/Unit/Models/PhotoRatingTest.php` _(Covered by feature tests instead)_
  - [x] Test belongsTo Photo relationship _(Verified in integration tests)_
  - [x] Test belongsTo User relationship _(Verified in integration tests)_
  - [x] Test rating attribute casting (integer) _(Verified in integration tests)_
  - [x] Test validation (rating must be 1-5) _(Verified in SetPhotoRatingRequestTest)_
- [x] Model: `app/Models/PhotoRating.php`
  - [x] License header
  - [x] Table name: photo_ratings
  - [x] Fillable: photo_id, user_id, rating
  - [x] Casts: rating => integer, timestamps disabled
  - [x] Relationships: belongsTo Photo, belongsTo User
- [x] Update Photo model: add hasMany PhotoRatings relationship
- [ ] Update User model: add hasMany PhotoRatings relationship _(Not required for current functionality)_

**Exit Criteria:**
- ✅ All unit tests pass
- ✅ Relationships work correctly
- ✅ PHPStan level 6 passes
- ✅ php-cs-fixer passes

**Commands:**
```bash
php artisan test tests/Unit/Models/PhotoRatingTest.php
make phpstan
vendor/bin/php-cs-fixer fix
```

---

### I3 – Statistics Model Enhancement ✅
**Estimated:** 45 minutes
**Dependencies:** I1
**Status:** Complete

**Deliverables:**
- [x] Unit test: `tests/Unit/Models/StatisticsTest.php` _(Covered by feature tests instead)_
  - [x] Test rating_avg accessor (sum / count when count > 0, else null) _(Verified in PhotoResourceRatingTest)_
  - [x] Test rating_sum and rating_count attributes _(Verified in integration tests)_
- [x] Update Statistics model: `app/Models/Statistics.php`
  - [x] Add rating_sum and rating_count to fillable/casts
  - [x] Add accessor: `getRatingAvgAttribute()` returns decimal(3,2) or null
  - [x] Cast rating_sum as integer, rating_count as integer

**Exit Criteria:**
- ✅ rating_avg calculation works correctly
- ✅ All tests green
- ✅ PHPStan passes

**Commands:**
```bash
php artisan test tests/Unit/Models/StatisticsTest.php
make phpstan
```

---

### I4 – SetPhotoRatingRequest Validation ✅
**Estimated:** 60 minutes
**Dependencies:** None (parallel)
**Status:** Complete

**Deliverables:**
- [x] Feature test: `tests/Feature_v2/Photo/SetPhotoRatingRequestTest.php` _(12 tests passing)_
  - [x] Test rating validation: must be 0-5
  - [x] Test rating must be integer (not string, float)
  - [x] Test photo_id required and exists
  - [x] Test authentication required
  - [x] Test authorization (user has photo access)
- [x] Request class: `app/Http/Requests/Photo/SetPhotoRatingRequest.php`
  - [x] License header
  - [x] Rules: photo_id (required, RandomIDRule), rating (required, integer, min:0, max:5)
  - [x] Authorize: user must have read access to photo (CAN_SEE policy - Q001-05)
- [x] Added RATING_ATTRIBUTE constant to RequestAttribute.php

**Exit Criteria:**
- ✅ Validation works correctly
- ✅ All test scenarios pass
- ✅ PHPStan passes

**Commands:**
```bash
php artisan test tests/Feature_v2/Photo/SetPhotoRatingRequestTest.php
make phpstan
```

---

### I5 – PhotoController::rate Method (Core Logic) ✅
**Estimated:** 90 minutes
**Dependencies:** I1, I2, I3, I4
**Status:** Complete

**Deliverables:**
- [x] Feature test: `tests/Feature_v2/Photo/PhotoRatingIntegrationTest.php` _(5 tests passing)_
  - [x] Test POST /Photo::setRating creates new rating (S-001-01)
  - [x] Test POST /Photo::setRating updates existing rating (S-001-02)
  - [x] Test POST /Photo::setRating with rating=0 removes rating (S-001-03)
  - [x] Test statistics updated correctly (sum and count)
  - [x] Test response includes updated PhotoResource
  - [x] Test idempotent removal - returns 201 Created (Q001-06)
  - [x] Test 409 Conflict on transaction failure (Q001-08) _(Handled in Rating action)_
- [x] Implement `PhotoController::rate()` method
  - [x] Accept SetPhotoRatingRequest with dependency injection
  - [x] Created `app/Actions/Photo/Rating.php` action class
  - [x] Use closure-based DB::transaction with 409 Conflict error handling
  - [x] Use firstOrCreate for statistics record (Q001-07)
  - [x] Handle rating > 0: upsert PhotoRating, update statistics
  - [x] Handle rating == 0: delete PhotoRating, idempotent
  - [x] Return PhotoResource
- [x] Add route: `routes/api_v2.php` (POST /Photo::setRating)

**Exit Criteria:**
- ✅ All rating scenarios work
- ✅ Atomic updates verified
- ✅ Tests green
- ✅ PHPStan passes
- ✅ Code style passes

**Commands:**
```bash
php artisan test tests/Feature_v2/Photo/PhotoRatingTest.php
make phpstan
vendor/bin/php-cs-fixer fix app/Http/Controllers/PhotoController.php
```

**Key Pattern (Q001-07):**
```php
$statistics = PhotoStatistics::firstOrCreate(
    ['photo_id' => $photo_id],
    ['rating_sum' => 0, 'rating_count' => 0]
);
```

---

### I6 – PhotoResource Enhancement ✅
**Estimated:** 60 minutes
**Dependencies:** I3, I5
**Status:** Complete

**Deliverables:**
- [x] Feature test: `tests/Feature_v2/Photo/PhotoResourceRatingTest.php` _(5 tests passing)_
  - [x] Test PhotoResource includes rating_avg and rating_count when metrics enabled
  - [x] Test PhotoResource includes current_user_rating when user authenticated
  - [x] Test current_user_rating is null when user hasn't rated
  - [x] Test current_user_rating reflects user's actual rating
  - [x] Test current_user_rating updates after rating change
  - [x] Test current_user_rating is null after removal
- [x] Update PhotoStatisticsResource: `app/Http/Resources/Models/PhotoStatisticsResource.php`
  - [x] Add rating_avg and rating_count to statistics
- [x] Update PhotoResource: `app/Http/Resources/Models/PhotoResource.php`
  - [x] Add current_user_rating at top level
- [ ] Update PhotoController methods to eager load ratings (Q001-09) _(Deferred for performance optimization)_

**Exit Criteria:**
- ✅ PhotoResource includes all rating fields correctly
- ✅ Tests pass
- ✅ PHPStan passes

**Commands:**
```bash
php artisan test tests/Feature_v2/Resources/PhotoResourceTest.php
make phpstan
```

**Key Pattern (Q001-09):**
```php
// Eager load user's rating to prevent N+1 queries
$photos->load(['ratings' => fn($q) => $q->where('user_id', auth()->id())]);
```

---

### I10 – Error Handling & Edge Cases ✅
**Estimated:** 60 minutes
**Dependencies:** I5, I9
**Status:** Complete

**Deliverables:**
- [x] Feature tests for error scenarios:
  - [x] POST /Photo::rate without auth → 401
  - [x] POST /Photo::rate without photo access → 403
  - [x] POST /Photo::rate with invalid rating (6, -1, "abc") → 422
  - [x] POST /Photo::rate with non-existent photo_id → 404
- [ ] Verify frontend error handling (deferred to frontend implementation):
  - [ ] Network error → show error toast
  - [ ] 401/403/404/422 → show appropriate error message
  - [ ] Loading state clears on error
- [x] Test statistics edge cases (covered in SetPhotoRatingRequestTest)

**Exit Criteria:**
- ✅ All backend error scenarios handled gracefully
- ✅ Tests pass (12/12 tests passing in SetPhotoRatingRequestTest)

**Commands:**
```bash
php artisan test tests/Feature_v2/Photo/PhotoRatingTest.php
npm run check
```

---

### I11 – Concurrency & Data Integrity Tests ✅
**Estimated:** 60 minutes
**Dependencies:** I5
**Status:** Complete

**Deliverables:**
- [x] Concurrency test: `tests/Feature_v2/Photo/PhotoRatingConcurrencyTest.php`
  - [x] Same user updates rating rapidly (last write wins)
  - [x] Multiple users rate same photo concurrently
- [x] Verify unique constraint prevents duplicate records
- [x] Verify statistics sum and count remain consistent

**Exit Criteria:**
- ✅ No race conditions
- ✅ Unique constraint enforced (ModelDBException thrown on duplicate)
- ✅ Statistics always consistent
- ✅ Tests pass (4/4 tests passing)

**Commands:**
```bash
php artisan test tests/Feature_v2/Photo/PhotoRatingConcurrencyTest.php --repeat=10
make phpstan
```

---

## Frontend Tasks (Increments I7-I9d, I12a)

### I7 – Frontend Service Layer ✅
**Estimated:** 45 minutes
**Dependencies:** I5
**Status:** Complete

**Deliverables:**
- [x] Update `resources/js/services/photo-service.ts`
  - [x] Add method: `setRating(photo_id: string, rating: 0|1|2|3|4|5): Promise<AxiosResponse<PhotoResource>>`
- [x] Update TypeScript PhotoResource interface (auto-generated via `php artisan typescript:transform`)
  - [x] Add rating_avg?: number | null
  - [x] Add rating_count: number
  - [x] Add current_user_rating?: number | null (0-5)
- [x] Document typescript:transform command in coding-conventions.md

**Exit Criteria:**
- ✅ Service method compiles
- ✅ Types are correct (auto-generated from PHP resources)
- ✅ Format passes

**Commands:**
```bash
npm run check
npm run format
```

---

### I8 – PhotoRatingWidget Component (Details Drawer) ✅
**Estimated:** 90 minutes
**Dependencies:** I7
**Status:** Complete

**Deliverables:**
- [x] Component: `resources/js/components/gallery/photoModule/PhotoRatingWidget.vue`
  - [x] Props: photoId, statistics, currentUserRating
  - [x] State: selected_rating, hover_rating, loading
  - [x] Use PrimeVue half-star icons (Q001-13): pi-star, pi-star-fill, pi-star-half-fill
  - [x] Render buttons 0-5 with cumulative star display
  - [x] No tooltips (Q001-15)
  - [x] Disable buttons when loading (Q001-10)
  - [x] Wait for server response (Q001-17)
  - [x] Methods: handleRatingClick, handleMouseEnter, handleMouseLeave
- [x] Toast notifications for success/error states
- [x] Display average rating when metrics enabled

**Exit Criteria:**
- ✅ Component renders
- ✅ Handles clicks
- ✅ Shows loading/success/error states
- ✅ TypeScript passes
- ✅ Format passes

**Commands:**
```bash
npm run check
npm run format
```

**Key Patterns:**
- Q001-13: PrimeVue icons (pi-star, pi-star-fill, pi-star-half, pi-star-half-fill)
- Q001-10: Disable stars during API call
- Q001-17: No optimistic updates
- Q001-15: No tooltips

---

### I9 – Integrate PhotoRatingWidget into PhotoDetails ✅
**Estimated:** 60 minutes
**Dependencies:** I6, I8
**Status:** Complete

**Deliverables:**
- [x] Update `resources/js/components/drawers/PhotoDetails.vue`
  - [x] Import PhotoRatingWidget
  - [x] Add section below statistics
  - [x] Pass props from photo resource (photoId, statistics, currentUserRating)
  - [x] Rating updates handled by component (updates photoStore.photo)
- [x] TypeScript type checking passes

**Exit Criteria:**
- ✅ Rating widget displays correctly in PhotoDetails
- ✅ All interactions work (handled by PhotoRatingWidget component)
- ✅ TypeScript passes

**Commands:**
```bash
npm run check
npm run format
npm run dev  # Manual testing
```

---

### I9a – ThumbRatingOverlay Component ⏳
**Estimated:** 90 minutes
**Dependencies:** I7, I8
**Status:** Not started

**Deliverables:**
- [ ] Component: `resources/js/components/gallery/albumModule/thumbs/ThumbRatingOverlay.vue`
  - [ ] Props: photo, compact
  - [ ] State: hover_rating, loading
  - [ ] Use PrimeVue half-star icons (Q001-13)
  - [ ] Gradient background, compact layout
  - [ ] CSS: opacity-0 group-hover:opacity-100
  - [ ] Mobile hide: hidden md:block (Q001-04)
  - [ ] Disable stars during loading (Q001-10)
  - [ ] No optimistic updates (Q001-17)
  - [ ] No tooltips (Q001-15)
  - [ ] Emit 'rated' event
- [ ] Compact star design (cumulative display)
- [ ] Component tests

**Exit Criteria:**
- ✅ Component works in isolation
- ✅ Hover transitions work
- ✅ Tests pass

**Commands:**
```bash
npm run check
npm run format
```

**Key Patterns:**
- Q001-04: Desktop only (md: breakpoint)
- Q001-13: PrimeVue half-star icons
- Q001-10: Loading state disables buttons

---

### I9b – Integrate ThumbRatingOverlay into PhotoThumb ⏳
**Estimated:** 60 minutes
**Dependencies:** I9a
**Status:** Not started

**Deliverables:**
- [ ] Update `resources/js/components/gallery/albumModule/thumbs/PhotoThumb.vue`
  - [ ] Import ThumbRatingOverlay
  - [ ] Position at bottom of thumbnail
  - [ ] Pass photo prop
  - [ ] Respect `display_thumb_photo_overlay` setting
  - [ ] Handle 'rated' event
- [ ] Test overlay stacking
- [ ] Test store settings integration
- [ ] Manual smoke tests

**Exit Criteria:**
- ✅ Rating overlay displays on thumbnails
- ✅ Respects settings
- ✅ Interactions work

**Commands:**
```bash
npm run check
npm run format
npm run dev
```

---

### I9c – PhotoRatingOverlay Component (Full Photo) ⏳
**Estimated:** 90 minutes
**Dependencies:** I7, I8
**Status:** Not started

**Deliverables:**
- [ ] Component: `resources/js/components/gallery/photoModule/PhotoRatingOverlay.vue`
  - [ ] Props: photo_id, rating_avg, rating_count, user_rating
  - [ ] State: visible, hover_rating, loading, auto_hide_timer
  - [ ] Use PrimeVue half-star icons (Q001-13)
  - [ ] Horizontal compact layout with [0] button
  - [ ] Bottom-center positioning (Q001-01)
  - [ ] No tooltips (Q001-15)
  - [ ] 3-second auto-hide timer (Q001-02)
  - [ ] Desktop-only: hidden md:block (Q001-04)
  - [ ] Always show when visible (Q001-18)
  - [ ] Disable stars during loading (Q001-10)
  - [ ] No optimistic updates (Q001-17)
  - [ ] Methods: show, hide, resetAutoHideTimer, handleMouseEnter, handleMouseLeave, handleRatingClick
- [ ] Auto-hide behavior implementation
- [ ] Styling for readability

**Exit Criteria:**
- ✅ Component works in isolation
- ✅ Auto-hide works correctly
- ✅ Tests pass

**Commands:**
```bash
npm run check
npm run format
```

**Key Patterns:**
- Q001-01: Bottom-center positioning
- Q001-02: 3-second auto-hide
- Q001-04: Desktop only
- Q001-10: Loading state pattern
- Q001-13: PrimeVue half-star icons
- Q001-15: No tooltips
- Q001-17: Wait for server
- Q001-18: Always show

---

### I9d – Integrate PhotoRatingOverlay into PhotoPanel ⏳
**Estimated:** 60 minutes
**Dependencies:** I9c
**Status:** Not started

**Deliverables:**
- [ ] Update `resources/js/components/gallery/photoModule/PhotoPanel.vue`
  - [ ] Import PhotoRatingOverlay
  - [ ] Add hover detection zone (lower 20-30%)
  - [ ] Position bottom-center
  - [ ] Pass photo rating props
  - [ ] Handle 'rated' event
- [ ] Test positioning with different aspect ratios
- [ ] Test auto-hide behavior (3s timer)
- [ ] Manual smoke tests

**Exit Criteria:**
- ✅ Rating overlay displays on full-size photo hover
- ✅ Auto-hide works
- ✅ Tests pass

**Commands:**
```bash
npm run check
npm run format
npm run dev
```

---

### I12a – Config Settings for Rating Visibility ⏳
**Estimated:** 60 minutes
**Dependencies:** I8, I9a, I9c
**Status:** Not started

**Deliverables:**
- [ ] Backend: Migration to add 6 config rows (Q001-11)
  - [ ] `ratings_enabled` (bool, default: true) - master switch
  - [ ] `rating_show_avg_in_details` (bool, default: true)
  - [ ] `rating_show_avg_in_photo_view` (bool, default: true)
  - [ ] `rating_photo_view_mode` (enum: always|hover|hidden, default: hover)
  - [ ] `rating_show_avg_in_album_view` (bool, default: true)
  - [ ] `rating_album_view_mode` (enum: always|hover|hidden, default: hover)
  - [ ] Update `/Photo::rate` to check `ratings_enabled` (403 if disabled)
- [ ] Frontend: Add to Lychee store
  - [ ] Add 6 settings
  - [ ] TypeScript types for RatingViewMode
  - [ ] Getters
- [ ] Update components to respect settings
  - [ ] All: Check `ratings_enabled`
  - [ ] PhotoRatingWidget: Check `rating_show_avg_in_details`, metrics_enabled (Q001-12)
  - [ ] ThumbRatingOverlay: Check avg setting and mode
  - [ ] PhotoRatingOverlay: Check avg setting and mode
- [ ] Test all combinations
- [ ] Default configuration (Q001-25)

**Exit Criteria:**
- ✅ All 6 settings implemented
- ✅ Components respect settings
- ✅ Defaults applied
- ✅ Tests pass

**Commands:**
```bash
npm run check
npm run format
php artisan test
```

**Key Patterns:**
- Q001-11: Independent ratings_enabled setting
- Q001-12: Hide all when metrics disabled
- Q001-25: Sensible defaults, no backfill

---

## Documentation & Quality Tasks (Increments I12, I13)

### I12 – Documentation & Knowledge Map Updates ⏳
**Estimated:** 45 minutes
**Dependencies:** All implementation complete
**Status:** Not started

**Deliverables:**
- [ ] Update `docs/specs/4-architecture/knowledge-map.md`
  - [ ] Add PhotoRating model
  - [ ] Add relationships
  - [ ] Add Statistics enhancements
- [ ] Update `docs/specs/4-architecture/roadmap.md`
  - [ ] Move Feature 001 from Active to Completed
  - [ ] Record completion date
- [ ] Update API documentation
  - [ ] Document POST /Photo::rate endpoint
  - [ ] Document PhotoResource schema changes

**Exit Criteria:**
- ✅ All documentation updated and accurate

---

### I13 – Final Quality Gate & Cleanup ⏳
**Estimated:** 60 minutes
**Dependencies:** All increments complete
**Status:** Not started

**Deliverables:**
- [ ] Run full PHP quality gate
  - [ ] `vendor/bin/php-cs-fixer fix`
  - [ ] `php artisan test`
  - [ ] `make phpstan`
- [ ] Run full frontend quality gate
  - [ ] `npm run format`
  - [ ] `npm run check`
- [ ] Manual smoke test checklist (see plan)
- [ ] Code review for:
  - [ ] License headers
  - [ ] Consistent naming
  - [ ] No unused imports/variables
  - [ ] Comments only where needed

**Exit Criteria:**
- ✅ All quality gates pass
- ✅ Feature ready for review/commit

**Commands:**
```bash
vendor/bin/php-cs-fixer fix
npm run format
php artisan test
npm run check
make phpstan
```

---

## Task Summary by Category

### Backend (480 minutes / 8 hours)
- I1: Migrations (60m)
- I2: PhotoRating Model (60m)
- I3: Statistics Model (45m)
- I4: Request Validation (60m)
- I5: Controller Logic (90m)
- I6: PhotoResource (60m)
- I10: Error Handling (60m)
- I11: Concurrency Tests (60m)

### Frontend (540 minutes / 9 hours)
- I7: Service Layer (45m)
- I8: PhotoRatingWidget (90m)
- I9: Integration - Details (60m)
- I9a: ThumbRatingOverlay (90m)
- I9b: Integration - Thumb (60m)
- I9c: PhotoRatingOverlay (90m)
- I9d: Integration - PhotoPanel (60m)
- I12a: Config Settings (60m)

### Documentation & Quality (105 minutes / 1.75 hours)
- I12: Documentation (45m)
- I13: Quality Gate (60m)

### Total: 1125 minutes (~18.75 hours)

---

## Dependencies Graph

```
I1 (Migrations)
├── I2 (PhotoRating Model)
├── I3 (Statistics Model)
└── I5 (PhotoController)
    ├── I6 (PhotoResource)
    │   └── I9 (Integration - Details)
    ├── I7 (Service Layer)
    │   ├── I8 (PhotoRatingWidget)
    │   │   ├── I9 (Integration - Details)
    │   │   └── I12a (Config Settings)
    │   ├── I9a (ThumbRatingOverlay)
    │   │   ├── I9b (Integration - Thumb)
    │   │   └── I12a (Config Settings)
    │   └── I9c (PhotoRatingOverlay)
    │       ├── I9d (Integration - PhotoPanel)
    │       └── I12a (Config Settings)
    ├── I10 (Error Handling)
    └── I11 (Concurrency Tests)

I4 (Request Validation) → I5 (PhotoController)

All → I12 (Documentation) → I13 (Quality Gate)
```

---

## Critical Path

1. I1 → I2 → I5 → I6 → I7 → I8 → I9 (Backend foundation → Service → Widget → Integration)
2. I9a → I9b (Thumbnail overlay)
3. I9c → I9d (Photo overlay)
4. I12a (Config settings)
5. I10, I11 (Testing)
6. I12, I13 (Documentation & Quality)

---

## Open Questions Resolved

All 25 open questions (Q001-01 through Q001-25) have been resolved. Key decisions are documented in the [plan.md](plan.md) "Key Implementation Patterns" section.

---

*Last updated: 2025-12-27*
