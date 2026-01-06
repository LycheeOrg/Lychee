# Feature Plan 001 – Photo Star Rating

_Linked specification:_ `docs/specs/4-architecture/features/001-photo-star-rating/spec.md`
_Status:_ Draft
_Last updated:_ 2025-12-27

> Guardrail: Keep this plan traceable back to the governing spec. Reference FR/NFR/Scenario IDs from `spec.md` where relevant, log any new high- or medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), and assume clarifications are resolved only when the spec's normative sections (requirements/NFR/behaviour/telemetry) and, where applicable, ADRs under `docs/specs/5-decisions/` have been updated.

## Key Implementation Patterns (from Resolved Questions)

This section summarizes critical implementation decisions from the 25 resolved open questions (Q001-01 through Q001-25):

**Backend Patterns:**
- **Q001-07:** Statistics record creation using `firstOrCreate()` in transaction (atomic, no race conditions)
- **Q001-08:** Return 409 Conflict on transaction failures (distinguishes DB errors from validation)
- **Q001-06:** Return 200 OK for idempotent rating removal (rating=0 on non-existent rating)
- **Q001-05:** Read access authorization (anyone who can view can rate, not write-only)
- **Q001-09:** Eager load user ratings with closure to prevent N+1: `$photos->load(['ratings' => fn($q) => $q->where('user_id', auth()->id())])`
- **Q001-11:** Independent `ratings_enabled` master switch (separate from `metrics_enabled`)

**Frontend Patterns:**
- **Q001-13:** Use PrimeVue half-star icons: pi-star, pi-star-fill, pi-star-half, pi-star-half-fill
- **Q001-10:** Loading state pattern - disable all star buttons during API call (set `loading = true`)
- **Q001-17:** Wait for server response (no optimistic updates)
- **Q001-14:** Persist current state while loading (don't clear selection immediately)
- **Q001-15:** No tooltips on star buttons
- **Q001-16:** Defer accessibility enhancements (basic ARIA only)
- **Cumulative star display:** Rating N shows stars 1 through N filled (e.g., rating 3 = ★★★☆☆, not just star 3)

**UI Overlay Behavior:**
- **Q001-01:** Bottom-center positioning for PhotoRatingOverlay on full-size photo
- **Q001-02:** 3-second auto-hide timer (clearable on mouse enter, restarts on mouse leave)
- **Q001-04:** Desktop-only overlays (hidden on mobile below md: breakpoint)
- **Q001-18:** Always show overlay when visible (no "show only when hovering stars" logic)
- **Q001-20:** Minimal implementation (basic hover + auto-hide, no advanced features)

**Configuration & Settings:**
- **Q001-11:** 6 independent config settings stored in `configs` database table (not Laravel config files)
- **Q001-12:** When `metrics_enabled = false`, hide all rating UI
- **Q001-25:** Sensible defaults, no backfill migration needed

**Deferred Features:**
- **Q001-19:** No telemetry events or analytics
- **Q001-21:** Album aggregate ratings (defer)
- **Q001-22:** No rating export/import
- **Q001-23:** No rating notifications
- **Q001-24:** No recalculation command (trust transactions)

## Vision & Success Criteria

**User Value:** Logged-in users can rate photos 1-5 stars, see aggregate ratings from the community, and manage their own ratings through an intuitive interface at the bottom of the photo view.

**Success Signals:**
- Users can rate, update, and remove ratings via API and UI
- Average rating and vote count display correctly in photo details
- User's current rating is pre-selected when viewing photo
- All rating operations complete in <500ms (p95)
- Zero data integrity issues (no orphaned ratings, correct statistics)
- 100% test coverage for rating paths (unit + feature + component)

**Quality Bars:**
- NFR-001-01: Atomic database updates (transactions)
- NFR-001-05: PHP conventions (license headers, snake_case, strict comparison, PSR-4)
- NFR-001-06: Vue3/TypeScript conventions (Composition API, .then() pattern, services pattern)
- NFR-001-07: Full test coverage (all scenarios from spec)

## Scope Alignment

**In scope:**
- PhotoRating model and migration (photo_ratings table)
- Statistics table enhancement (rating_sum, rating_count columns)
- POST `/Photo::rate` endpoint (create/update/remove ratings)
- PhotoResource enhancement (rating_avg, rating_count, user_rating fields)
- PhotoRatingWidget Vue component (star selector UI for details drawer)
- ThumbRatingOverlay Vue component (hover overlay on thumbnails)
- PhotoRatingOverlay Vue component (hover overlay on full-size photo)
- Integration into PhotoDetails drawer
- Integration into PhotoThumb component
- Integration into PhotoPanel component
- photo-service.ts rating method
- Full test suite (unit, feature, component)
- Database indexes for performance
- Atomic transaction logic for data integrity
- Hover detection and auto-hide logic
- Store setting respect (display_thumb_photo_overlay)
- **6 new config settings** (FR-001-11 through FR-001-16):
  - `rating_show_avg_in_details` (bool, default: true)
  - `rating_show_avg_in_photo_view` (bool, default: true)
  - `rating_photo_view_mode` (enum: always|hover|hidden, default: hover)
  - `rating_show_avg_in_album_view` (bool, default: true)
  - `rating_album_view_mode` (enum: always|hover|hidden, default: hover)
  - `ratings_enabled` (bool, default: true) - master switch for rating functionality

**Out of scope:**
- Rating other entities (albums, tags, etc.) - only photos
- Anonymous ratings - authentication required
- Rating history/audit trail - only current state
- Public display of individual user ratings - aggregate only
- Rating notifications or activity feeds
- Advanced analytics or trending ratings
- Album-level aggregate ratings
- Rating export/import functionality (Q001-22 → Option C: no export)
- Accessibility enhancements beyond basic ARIA labels (Q001-16 → Option C: defer)
- Album aggregate ratings (Q001-21 → Option A: defer)
- Rating notifications (Q001-23 → Option A: defer)
- Recalculation command (Q001-24 → Option B: not needed)
- Write access authorization (using read access per Q001-05)

## Dependencies & Interfaces

**Backend Dependencies:**
- Photo model (`app/Models/Photo.php`)
- Statistics model (`app/Models/Statistics.php`)
- User model (`app/Models/User.php`)
- PhotoController (`app/Http/Controllers/PhotoController.php`)
- PhotoResource (`app/Http/Resources/Models/PhotoResource.php`)
- Existing authorization traits/middleware (login_required:album)
- Laravel migrations and schema builder
- Database transaction support

**Frontend Dependencies:**
- PhotoDetails.vue component (`resources/js/components/drawers/PhotoDetails.vue`)
- photo-service.ts (`resources/js/services/photo-service.ts`)
- PrimeVue components (Button, Rating, or custom star component)
- Toast notification system (existing)
- Constants utility for API URL

**Tooling:**
- php-cs-fixer (PHP code style)
- PHPStan level 6 (static analysis)
- phpunit (testing)
- npm run format (Prettier)
- npm run check (frontend tests)

**Contracts:**
- PhotoResource API response schema
- OpenAPI/API documentation (to be updated)

## Assumptions & Risks

**Assumptions:**
1. Photo model uses 24-char random string IDs (verified from exploration)
2. Statistics table already exists with foreign key to photos
3. User authentication system is working and provides $this->user in controllers
4. Existing authorization patterns (photo access control) can be reused
5. PrimeVue or custom star rating component is acceptable for UI
6. Database supports transactions and foreign key constraints
7. Metrics system (metrics_enabled config, CAN_READ_METRICS permission) already works

**Risks & Mitigations:**

| Risk | Impact | Mitigation |
|------|--------|-----------|
| Race conditions on concurrent ratings | High - data corruption | Use DB transactions wrapping both photo_ratings and statistics updates. Add unique constraint on (photo_id, user_id). Test with concurrent requests. |
| Performance degradation with many ratings | Medium - slow UI | Add database indexes on photo_id and user_id in photo_ratings. Denormalize statistics (already planned). Monitor query performance during testing. |
| Statistics table doesn't exist for some photos | Medium - errors | Check for statistics record existence, create if missing during first rating (or ensure cascade creation). |
| Frontend component complexity | Low - dev time | Reuse existing PrimeVue Rating component or build simple custom component. Start with basic implementation, enhance UI later if needed. |
| Migration rollback complexity | Low - deployment | Ensure migration down() properly drops columns and table. Test rollback in local environment. |

## Implementation Drift Gate

**Execution Plan:**
1. After completing each increment, verify:
   - All tests pass (`php artisan test`, `npm run check`)
   - PHPStan passes (`make phpstan`)
   - Code style passes (`vendor/bin/php-cs-fixer fix --dry-run`, `npm run format`)
   - Manual smoke test in UI (if UI increment)
2. Record drift findings in this section with date and resolution
3. Update spec.md if requirements change during implementation

**Commands to Rerun:**
```bash
# Full quality gate
vendor/bin/php-cs-fixer fix
npm run format
php artisan test
npm run check
make phpstan
```

**Drift Log:**
_To be populated during implementation_

## Increment Map

### **I1 – Database Schema & Migrations** (≤60 min)

- **Goal:** Create photo_ratings table and add rating columns to photo_statistics table
- **Preconditions:** None (foundational increment)
- **Scenarios:** Foundation for all scenarios
- **Steps:**
  1. Create migration: `create_photo_ratings_table`
     - Columns: id, photo_id (char 24, FK), user_id (int, FK), rating (tinyint 1-5), timestamps
     - Unique constraint: (photo_id, user_id)
     - Foreign keys with CASCADE delete
     - Indexes on photo_id and user_id
  2. Create migration: `add_rating_columns_to_photo_statistics`
     - Add rating_sum (BIGINT UNSIGNED, default 0)
     - Add rating_count (INT UNSIGNED, default 0)
  3. Test migrations run successfully (up and down)
- **Commands:**
  ```bash
  php artisan make:migration create_photo_ratings_table
  php artisan make:migration add_rating_columns_to_photo_statistics
  php artisan migrate
  php artisan migrate:rollback --step=2
  php artisan migrate
  ```
- **Exit:** Migrations run cleanly, tables created with correct schema, rollback works

---

### **I2 – PhotoRating Model & Relationships** (≤60 min)

- **Goal:** Create PhotoRating model with relationships and validation
- **Preconditions:** I1 complete (database schema exists)
- **Scenarios:** Foundation for S-001-01 through S-001-15
- **Steps:**
  1. Write unit test: `tests/Unit/Models/PhotoRatingTest.php`
     - Test belongsTo Photo relationship
     - Test belongsTo User relationship
     - Test rating attribute casting (integer)
     - Test validation (rating must be 1-5)
  2. Create model: `app/Models/PhotoRating.php`
     - License header
     - Table name: photo_ratings
     - Fillable: photo_id, user_id, rating
     - Casts: rating => integer, timestamps => UTC
     - Relationships: belongsTo Photo, belongsTo User
     - No incrementing (uses auto-increment id)
  3. Update Photo model: add hasMany PhotoRatings relationship
  4. Update User model: add hasMany PhotoRatings relationship (optional, for future use)
  5. Run tests
- **Commands:**
  ```bash
  php artisan test tests/Unit/Models/PhotoRatingTest.php
  make phpstan
  ```
- **Exit:** All tests green, relationships work, PHPStan passes

---

### **I3 – Statistics Model Enhancement** (≤45 min)

- **Goal:** Add rating aggregation logic to Statistics model
- **Preconditions:** I1 complete (rating columns exist)
- **Scenarios:** Foundation for displaying ratings
- **Steps:**
  1. Write unit test: `tests/Unit/Models/StatisticsTest.php` (or extend existing)
     - Test rating_avg accessor (sum / count when count > 0, else null)
     - Test rating_sum and rating_count attributes
  2. Update Statistics model: `app/Models/Statistics.php`
     - Add rating_sum and rating_count to fillable/casts
     - Add accessor for rating_avg: `getRatingAvgAttribute()` returns decimal(3,2) or null
     - Cast rating_sum as integer, rating_count as integer
  3. Run tests
- **Commands:**
  ```bash
  php artisan test tests/Unit/Models/StatisticsTest.php
  make phpstan
  ```
- **Exit:** rating_avg calculation works correctly, all tests green

---

### **I4 – SetPhotoRatingRequest Validation** (≤60 min)

- **Goal:** Create request validation class for rating endpoint
- **Preconditions:** None (can run in parallel with I2/I3)
- **Scenarios:** S-001-09 (validation), S-001-07 (unauthenticated), S-001-08 (unauthorized)
- **Steps:**
  1. Write feature test: `tests/Feature_v2/Photo/SetPhotoRatingRequestTest.php`
     - Test rating validation: must be 0-5
     - Test rating must be integer (not string, float)
     - Test photo_id required and exists
     - Test authentication required
     - Test authorization (user has photo access)
  2. Create request class: `app/Http/Requests/Photo/SetPhotoRatingRequest.php`
     - License header
     - Rules: photo_id (required, exists:photos,id), rating (required, integer, min:0, max:5)
     - Authorize: user must have access to photo (reuse existing photo authorization logic)
     - Use HasPhotoTrait if appropriate
  3. Run tests
- **Commands:**
  ```bash
  php artisan test tests/Feature_v2/Photo/SetPhotoRatingRequestTest.php
  make phpstan
  ```
- **Exit:** Validation works, all test scenarios pass

---

### **I5 – PhotoController::rate Method (Core Logic)** (≤90 min)

- **Goal:** Implement rating endpoint with atomic database updates
- **Preconditions:** I1, I2, I3, I4 complete
- **Scenarios:** S-001-01, S-001-02, S-001-03, S-001-06 (atomic updates)
- **Steps:**
  1. Write feature test: `tests/Feature_v2/Photo/PhotoRatingTest.php`
     - Test POST /Photo::rate creates new rating (S-001-01)
     - Test POST /Photo::rate updates existing rating (S-001-02)
     - Test POST /Photo::rate with rating=0 removes rating (S-001-03)
     - Test statistics updated correctly (sum and count)
     - Test response includes updated PhotoResource
     - Test idempotent removal (S-001-14) - returns 200 OK when removing non-existent rating (Q001-06)
     - Test 409 Conflict on transaction failure (Q001-08)
  2. Implement `PhotoController::rate()` method
     - Accept SetPhotoRatingRequest
     - Wrap in DB::transaction with 409 Conflict error handling (Q001-08 → Option B)
     - Ensure statistics record exists using firstOrCreate (Q001-07 → Option A):
       ```php
       $statistics = PhotoStatistics::firstOrCreate(
           ['photo_id' => $photo_id],
           ['rating_sum' => 0, 'rating_count' => 0]
       );
       ```
     - If rating > 0:
       - Upsert PhotoRating (updateOrCreate by photo_id + user_id)
       - Get old rating if exists
       - Update statistics: adjust sum (subtract old, add new), increment count if new
     - If rating == 0:
       - Find and delete PhotoRating
       - Update statistics: subtract rating from sum, decrement count
       - Return 200 OK (idempotent removal per Q001-06)
     - Return PhotoResource
     - On transaction failure: catch exception, return 409 Conflict
  3. Add route in `routes/api_v2.php`: `Route::post('/Photo::rate', [PhotoController::class, 'rate'])->middleware('login_required:album')`
  4. Run tests
- **Commands:**
  ```bash
  php artisan test tests/Feature_v2/Photo/PhotoRatingTest.php
  make phpstan
  vendor/bin/php-cs-fixer fix app/Http/Controllers/PhotoController.php
  ```
- **Exit:** All rating scenarios work, atomic updates verified, tests green, PHPStan passes

---

### **I6 – PhotoResource Enhancement** (≤60 min)

- **Goal:** Add rating data to PhotoResource serialization
- **Preconditions:** I3, I5 complete (statistics and rating logic exist)
- **Scenarios:** S-001-11, S-001-12, S-001-13, S-001-15 (display scenarios)
- **Steps:**
  1. Write feature test: `tests/Feature_v2/Resources/PhotoResourceTest.php` (or extend existing)
     - Test PhotoResource includes rating_avg and rating_count when metrics enabled
     - Test PhotoResource includes user_rating when user is authenticated
     - Test user_rating is null when user hasn't rated
     - Test user_rating reflects user's actual rating
     - Test rating fields omitted when metrics disabled
  2. Update PhotoResource: `app/Http/Resources/Models/PhotoResource.php`
     - Add to statistics section (when metrics enabled):
       - `rating_avg` => $this->statistics?->rating_avg (decimal, nullable)
       - `rating_count` => $this->statistics?->rating_count ?? 0
     - Add at top level (when user authenticated):
       - `user_rating` => $this->ratings()->where('user_id', auth()->id())->value('rating')
  3. Update PhotoController methods that return PhotoResource to eager load ratings for current user (Q001-09 → Option A):
     ```php
     // Eager load user's rating to prevent N+1 queries
     $photos->load(['ratings' => fn($q) => $q->where('user_id', auth()->id())]);
     ```
  4. Run tests
- **Commands:**
  ```bash
  php artisan test tests/Feature_v2/Resources/PhotoResourceTest.php
  make phpstan
  ```
- **Exit:** PhotoResource includes all rating fields correctly, tests pass

---

### **I7 – Frontend Service Layer** (≤45 min)

- **Goal:** Add rating method to photo-service.ts
- **Preconditions:** I5 complete (API endpoint exists)
- **Scenarios:** Foundation for all UI scenarios
- **Steps:**
  1. Update `resources/js/services/photo-service.ts`
     - Add method: `setRating(photo_id: string, rating: 0 | 1 | 2 | 3 | 4 | 5, album_id?: string | null): Promise<AxiosResponse<PhotoResource>>`
     - Implementation: `return axios.post(\`\${Constants.getApiUrl()}/Photo::rate\`, { photo_id, rating })`
  2. Update TypeScript PhotoResource interface (if separate file)
     - Add rating_avg?: number (nullable)
     - Add rating_count: number
     - Add user_rating?: number (nullable, 1-5)
  3. Write basic service test (if testing infrastructure exists)
- **Commands:**
  ```bash
  npm run check
  npm run format
  ```
- **Exit:** Service method compiles, types are correct, format passes

---

### **I8 – PhotoRatingWidget Component (for Details Drawer)** (≤90 min)

- **Goal:** Create star rating widget for PhotoDetails drawer
- **Preconditions:** I7 complete (service exists)
- **Scenarios:** UI-001-01 through UI-001-08
- **Steps:**
  1. Create component: `resources/js/components/PhotoRatingWidget.vue`
     - Props: photo_id (string), initial_rating (number | null), rating_avg (number | null), rating_count (number)
     - State: selected_rating (ref), hover_rating (ref), loading (ref)
     - Template:
       - Display average rating and count (e.g., "★★★★☆ 4.2 (15 votes)")
       - Use PrimeVue half-star icons (Q001-13 → Option B):
         - pi-star (empty)
         - pi-star-fill (full)
         - pi-star-half (half outline)
         - pi-star-half-fill (half filled)
       - Display "Your rating:" label
       - Render buttons 0-5 with star icons (0 = ×, 1-5 = ☆/★)
       - **CUMULATIVE star display:** rating N shows stars 1-N filled (e.g., rating 3 = ★★★☆☆)
       - Highlight selected rating with filled stars (cumulative)
       - Show hover preview with filled stars 1 through hover value (cumulative)
       - No tooltips needed (Q001-15 → Option C)
       - Disable buttons when loading or not logged in (Q001-10 → Option A)
     - Methods:
       - `handleRatingClick(rating: number)`:
         - Set loading = true, disable all star buttons (Q001-10)
         - Call photoService.setRating()
         - Wait for server response (no optimistic updates per Q001-17 → Option A)
         - Update on success, show toast
         - Clear loading state
       - `handleMouseEnter(rating: number)`: set hover_rating
       - `handleMouseLeave()`: clear hover_rating
     - Style: Use PrimeVue icons (pi-star, pi-star-fill, pi-star-half, pi-star-half-fill)
  2. Write component test (if testing infrastructure exists)
     - Test buttons render
     - Test click handler calls service
     - Test loading state
     - Test disabled state
  3. Implement toast notifications (success/error)
- **Commands:**
  ```bash
  npm run check
  npm run format
  ```
- **Exit:** Component renders, handles clicks, shows loading/success/error states

---

### **I9 – Integrate PhotoRatingWidget into PhotoDetails** (≤60 min)

- **Goal:** Add rating widget to photo details drawer
- **Preconditions:** I6, I8 complete (PhotoResource has rating data, widget component exists)
- **Scenarios:** S-001-11, S-001-12, S-001-13, UI-001-01 through UI-001-08
- **Steps:**
  1. Update `resources/js/components/drawers/PhotoDetails.vue`
     - Import PhotoRatingWidget component
     - Add section below statistics (or appropriate location per mockup)
     - Pass props: photo_id, user_rating, rating_avg, rating_count from photo resource
     - Handle rating update event (refresh photo data or optimistically update)
  2. Manual smoke test in browser:
     - View photo without rating → see "No ratings yet"
     - Rate photo → see average update, your rating selected
     - Change rating → see average recalculate
     - Remove rating (click 0) → see average update, selection cleared
     - Verify statistics section displays correctly
  3. Test edge cases:
     - Not logged in → buttons disabled, tooltip shown
     - Photo with no ratings → displays correctly
     - Photo with many ratings → displays correctly
- **Commands:**
  ```bash
  npm run check
  npm run format
  npm run dev  # Start dev server for manual testing
  ```
- **Exit:** Rating widget displays correctly in PhotoDetails, all interactions work

---

### **I9a – ThumbRatingOverlay Component (for Thumbnails)** (≤90 min)

- **Goal:** Create rating overlay component that appears on photo thumbnail hover
- **Preconditions:** I7 complete (service exists), I8 complete (rating widget pattern established)
- **Scenarios:** S-001-16, S-001-17, S-001-19, UI-001-09, UI-001-10, UI-001-13
- **Steps:**
  1. Create component: `resources/js/components/gallery/albumModule/thumbs/ThumbRatingOverlay.vue`
     - Props: photo (PhotoResource), compact (boolean, default true)
     - State: hover_rating (ref), loading (ref)
     - Template structure:
       - Container div with gradient background: `bg-linear-to-t from-[#00000099]`
       - Average rating display: "★★★★☆ 4.2 (15)" (compact format)
       - Use PrimeVue half-star icons (Q001-13 → Option B): pi-star, pi-star-fill, pi-star-half, pi-star-half-fill
       - Interactive stars: compact horizontal layout
       - Loading indicator overlay
     - CSS classes:
       - Position: absolute, bottom-0, full-width
       - Visibility: `opacity-0 group-hover:opacity-100 transition-all ease-out`
       - Mobile hide: `hidden md:block` (only desktop per Q001-04)
       - Gradient padding for text readability
     - Methods:
       - `handleRatingClick(rating: number)`:
         - Set loading = true, disable all star buttons (Q001-10)
         - Call photoService.setRating()
         - Wait for server response (no optimistic updates per Q001-17)
         - Emit 'rated' when successful
       - `handleStarHover(rating: number)`: preview stars
       - No tooltips needed (Q001-15 → Option C)
     - Pattern reference: ThumbFavourite.vue (existing hover button pattern)
  2. Implement compact star design:
     - Smaller star icons (text-sm or custom sizing)
     - Horizontal layout with cumulative display
     - **CUMULATIVE visualization:** rating 3 = "★★★☆☆" (stars 1-3 filled, not just star 3)
     - Click target: minimum 24px (w-6) for touch accessibility
  3. Test component isolation:
     - Render with various rating states
     - Test hover transitions
     - Test click propagation (stop propagation to prevent thumbnail click)
     - Test loading state disables buttons (Q001-10)
- **Commands:**
  ```bash
  npm run check
  npm run format
  ```
- **Exit:** ThumbRatingOverlay component works in isolation

---

### **I9b – Integrate ThumbRatingOverlay into PhotoThumb** (≤60 min)

- **Goal:** Add rating overlay to photo thumbnails in album grid
- **Preconditions:** I9a complete (ThumbRatingOverlay exists)
- **Scenarios:** S-001-16, S-001-17, S-001-19
- **Steps:**
  1. Update `resources/js/components/gallery/albumModule/thumbs/PhotoThumb.vue`
     - Import ThumbRatingOverlay component
     - Add after existing overlay section (after line ~75, before video play icon)
     - Position at bottom of thumbnail (absolute, below metadata overlay)
     - Pass photo prop with rating data
     - Respect `display_thumb_photo_overlay` store setting (same as metadata overlay)
     - Handle 'rated' event: refresh photo data or optimistically update
  2. Test overlay stacking:
     - Ensure rating overlay doesn't conflict with metadata overlay
     - Verify z-index layering (rating overlay should be above metadata)
     - Test with various thumbnail sizes
  3. Test store setting integration:
     - `display_thumb_photo_overlay === 'hover'` → show on hover only
     - `display_thumb_photo_overlay === 'always'` → always visible
     - `display_thumb_photo_overlay === 'never'` → hidden
  4. Manual smoke test:
     - Hover over thumbnail → rating overlay appears
     - Click star → photo rated, toast confirms, overlay updates
     - Thumbnail click (non-overlay area) → still opens photo
- **Commands:**
  ```bash
  npm run check
  npm run format
  npm run dev  # Manual testing
  ```
- **Exit:** Rating overlay displays on thumbnails, respects settings, interactions work

---

### **I9c – PhotoRatingOverlay Component (for Full-Size Photo)** (≤90 min)

- **Goal:** Create rating overlay for full-size photo view (hover on lower area)
- **Preconditions:** I7, I8 complete (service and widget pattern exist)
- **Scenarios:** S-001-18, S-001-20, UI-001-11, UI-001-12
- **Steps:**
  1. Create component: `resources/js/components/gallery/photoModule/PhotoRatingOverlay.vue`
     - Props: photo_id (string), rating_avg (number | null), rating_count (number), user_rating (number | null)
     - State: visible (ref), hover_rating (ref), loading (ref), auto_hide_timer (ref)
     - Template:
       - Container with semi-transparent background
       - Horizontal compact layout with cumulative stars: "[0][1][2][3][4][5] ★★★★☆ 4.2 (15) Your rating: ★★★★☆"
       - Use PrimeVue half-star icons (Q001-13 → Option B): pi-star, pi-star-fill, pi-star-half, pi-star-half-fill
       - **CUMULATIVE display:** user rating 4 shows "★★★★☆" (stars 1-4 filled)
       - **Inline [0] button** for rating removal (shown as "×")
       - **Positioned bottom-center** (horizontally centered, above metadata overlay per Q001-01)
       - No tooltips needed (Q001-15 → Option C)
     - Visibility logic:
       - Show when parent emits 'hover-lower-area' event
       - **Auto-hide after 3 seconds** of inactivity (Q001-02 → Option A: 3 seconds)
       - Persist while mouse is over overlay itself (cancels auto-hide timer)
       - Fade transition: opacity 0 → 100
       - **Desktop-only:** Hidden on mobile below md: breakpoint (Q001-04 → Option A)
       - Always show overlay when visible (Q001-18 → Option A: no "show only when hovering stars" logic)
     - Methods:
       - `show()`: make visible, start auto-hide timer
       - `hide()`: fade out
       - `resetAutoHideTimer()`: clear and restart 3s timer
       - `handleMouseEnter()`: cancel auto-hide
       - `handleMouseLeave()`: restart auto-hide
       - `handleRatingClick(rating)`:
         - Set loading = true, disable all star buttons (Q001-10)
         - Call photoService.setRating()
         - Wait for server response (no optimistic updates per Q001-17)
         - Show toast on success
         - Clear loading state
  2. Implement auto-hide behavior (Q001-02 → 3 seconds):
     - Use setTimeout for 3-second delay
     - Clear timeout on mouse enter (cancel auto-hide)
     - Restart timeout on mouse leave (resume auto-hide)
  3. Style for readability:
     - Gradient background or solid semi-transparent background
     - Text shadow for visibility on any photo
     - z-index above photo, below Dock and metadata Overlay
- **Commands:**
  ```bash
  npm run check
  npm run format
  ```
- **Exit:** PhotoRatingOverlay component works in isolation

---

### **I9d – Integrate PhotoRatingOverlay into PhotoPanel** (≤60 min)

- **Goal:** Add hover-triggered rating overlay to full-size photo view
- **Preconditions:** I9c complete (PhotoRatingOverlay exists)
- **Scenarios:** S-001-18, S-001-20, UI-001-11, UI-001-12
- **Steps:**
  1. Update `resources/js/components/gallery/photoModule/PhotoPanel.vue`
     - Import PhotoRatingOverlay component
     - Add hover detection zone:
       - Div covering lower 20-30% of photo area (desktop-only, md: breakpoint)
       - On mouseenter → show overlay
       - On mouseleave → start auto-hide timer
     - **Position overlay bottom-center** (Q001-01 → Option A)
     - Pass photo rating props from photoStore
     - Handle overlay 'rated' event: refresh photo data
  2. OR update `resources/js/components/gallery/photoModule/PhotoBox.vue` (alternative approach):
     - Add hover zone to photo element itself
     - Emit 'hover-lower-area' event when mouse in lower portion
     - Parent (PhotoPanel) shows PhotoRatingOverlay
  3. Test positioning:
     - Ensure overlay doesn't block Dock buttons
     - Ensure overlay doesn't block metadata Overlay
     - Test with different photo aspect ratios
     - Test with different screen sizes
  4. Test auto-hide behavior (Q001-02 → 3 seconds):
     - Hover lower area → overlay appears
     - Wait 3 seconds → overlay fades out
     - Hover over overlay → auto-hide cancelled (timer cleared)
     - Move mouse away from overlay → auto-hide restarts (3s timer)
     - Test on mobile → overlay hidden (Q001-04 → desktop-only)
  5. Manual smoke test:
     - View full-size photo, hover lower area → overlay appears
     - Click star → photo rated, toast confirms
     - Overlay auto-hides after inactivity
- **Commands:**
  ```bash
  npm run check
  npm run format
  npm run dev  # Manual testing
  ```
- **Exit:** Rating overlay displays on full-size photo hover, auto-hide works

---

### **I10 – Error Handling & Edge Cases** (≤60 min)

- **Goal:** Handle error scenarios and edge cases
- **Preconditions:** I5, I9 complete (API and UI exist)
- **Scenarios:** S-001-07 (unauthenticated), S-001-08 (unauthorized), S-001-09 (validation), S-001-10 (not found), S-001-14 (idempotent removal)
- **Steps:**
  1. Write feature tests for error scenarios:
     - POST /Photo::rate without auth → 401
     - POST /Photo::rate without photo access → 403
     - POST /Photo::rate with invalid rating (6, -1, "abc") → 422
     - POST /Photo::rate with non-existent photo_id → 404
  2. Verify frontend error handling:
     - Network error → show error toast
     - 401/403/404/422 → show appropriate error message
     - Loading state clears on error
  3. Test statistics edge case:
     - Photo without statistics record → create on first rating
     - Rating removal when count=1 → avg becomes null, count becomes 0
  4. Run full test suite
- **Commands:**
  ```bash
  php artisan test tests/Feature_v2/Photo/PhotoRatingTest.php
  npm run check
  ```
- **Exit:** All error scenarios handled gracefully, tests pass

---

### **I11 – Concurrency & Data Integrity Tests** (≤60 min)

- **Goal:** Verify atomic updates under concurrent load
- **Preconditions:** I5 complete (transaction logic exists)
- **Scenarios:** S-001-05, S-001-06 (concurrent updates)
- **Steps:**
  1. Write concurrency test: `tests/Feature_v2/Photo/PhotoRatingConcurrencyTest.php`
     - Test scenario: Same user updates rating rapidly (last write wins, no duplicates)
     - Test scenario: Multiple users rate same photo concurrently (all succeed, correct final count)
     - Use parallel requests or database transaction simulation
  2. Verify unique constraint prevents duplicate records
  3. Verify statistics sum and count remain consistent
  4. Run tests multiple times to catch race conditions
- **Commands:**
  ```bash
  php artisan test tests/Feature_v2/Photo/PhotoRatingConcurrencyTest.php --repeat=10
  make phpstan
  ```
- **Exit:** No race conditions, unique constraint enforced, statistics always consistent

---

### **I12 – Documentation & Knowledge Map Updates** (≤45 min)

- **Goal:** Update project documentation with new feature
- **Preconditions:** All implementation complete
- **Scenarios:** Documentation deliverables from spec
- **Steps:**
  1. Update `docs/specs/4-architecture/knowledge-map.md`:
     - Add PhotoRating model
     - Add relationships: Photo hasMany PhotoRatings, User hasMany PhotoRatings
     - Add Statistics enhancements: rating_sum, rating_count, rating_avg
  2. Update `docs/specs/4-architecture/roadmap.md`:
     - Move Feature 001 from Active to Completed
     - Record completion date
  3. Update API documentation (if separate file exists):
     - Document POST /Photo::rate endpoint
     - Document PhotoResource schema changes
  4. Add feature README summary to main README (if appropriate)
- **Commands:**
  ```bash
  # No specific commands, manual documentation updates
  ```
- **Exit:** All documentation updated and accurate

---

### **I12a – Config Settings for Rating Visibility Control** (≤60 min)

- **Goal:** Implement 6 config settings to control rating display behavior (FR-001-11 through FR-001-16)
- **Preconditions:** I8, I9a, I9c complete (UI components exist)
- **Scenarios:** FR-001-11 through FR-001-16
- **Steps:**
  1. **Backend - Add to Configs table:**
     - Create migration to add 6 new rows to `configs` table (Q001-11 → Option C: independent setting):
       - `ratings_enabled` (type: boolean, value: '1', default: true) - master switch (FR-001-16)
       - `rating_show_avg_in_details` (type: boolean, value: '1', default: true)
       - `rating_show_avg_in_photo_view` (type: boolean, value: '1', default: true)
       - `rating_photo_view_mode` (type: string, value: 'hover', allowed: 'always|hover|hidden')
       - `rating_show_avg_in_album_view` (type: boolean, value: '1', default: true)
       - `rating_album_view_mode` (type: string, value: 'hover', allowed: 'always|hover|hidden')
     - Update Config model/seeder if necessary to include these new keys
     - Ensure configs are loaded and accessible via Config facade or service
     - Update `/Photo::rate` endpoint to check `ratings_enabled` and return 403 if disabled
  2. **Frontend - Add to Lychee store:**
     - Add 6 settings to Lychee store (LycheeState or SettingsState)
     - Fetch config values from backend API (likely included in initial config load)
     - Define TypeScript types for enum values: `type RatingViewMode = 'always' | 'hover' | 'hidden'`
     - Add getters for each setting
  3. **Update components to respect settings:**
     - **All components:** Check `ratings_enabled`, don't render if false (FR-001-16)
     - **PhotoRatingWidget (I8):**
       - Check `rating_show_avg_in_details`, hide average/count if false
       - When metrics disabled (Q001-12 → Option B): hide all rating UI
     - **ThumbRatingOverlay (I9a):**
       - Check `rating_show_avg_in_album_view`, hide average/count if false
       - Check `rating_album_view_mode`:
         - `always` → no group-hover, always visible
         - `hover` → existing group-hover behavior
         - `hidden` → don't render component at all
     - **PhotoRatingOverlay (I9c):**
       - Check `rating_show_avg_in_photo_view`, hide average/count if false
       - Check `rating_photo_view_mode`:
         - `always` → no auto-hide timer, always visible (Q001-20 → Option B: minimal implementation)
         - `hover` → existing hover + auto-hide behavior
         - `hidden` → don't render component at all
  4. **Settings UI (optional, can defer):**
     - Add UI in settings panel to toggle these 6 settings
     - Save to backend config
  5. **Test all combinations:**
     - `ratings_enabled = false` → no rating UI anywhere, `/Photo::rate` returns 403
     - Average hidden but selector shown
     - Overlay mode set to `always` (no auto-hide)
     - Overlay mode set to `hidden` (no rendering)
     - `metrics_enabled = false` → no rating UI (per Q001-12)
  6. **Default configuration (Q001-25 → Option A):**
     - All 6 settings have sensible defaults (all enabled/hover)
     - No backfill migration needed for existing photos
- **Commands:**
  ```bash
  npm run check
  npm run format
  php artisan test  # If backend config changes
  ```
- **Exit:** All 6 settings implemented and respected by UI components, defaults applied

---

### **I13 – Final Quality Gate & Cleanup** (≤60 min)

- **Goal:** Run full quality gate and clean up any issues
- **Preconditions:** All increments complete
- **Scenarios:** All scenarios verified
- **Steps:**
  1. Run full PHP quality gate:
     - `vendor/bin/php-cs-fixer fix` (apply fixes)
     - `php artisan test` (all tests)
     - `make phpstan` (static analysis)
  2. Run full frontend quality gate:
     - `npm run format` (apply fixes)
     - `npm run check` (all tests)
  3. Manual smoke test checklist:
     - ✅ Rate photo as logged-in user
     - ✅ Update rating
     - ✅ Remove rating
     - ✅ View photo with ratings from others
     - ✅ View photo with no ratings
     - ✅ Verify statistics display
     - ✅ Verify disabled state when not logged in
     - ✅ Verify error handling (invalid rating, network error)
  4. Review code for:
     - License headers in all new files
     - Consistent naming (snake_case variables, etc.)
     - No unused imports or variables
     - Comments only where logic isn't self-evident
  5. Record any deferred items in Follow-ups section
- **Commands:**
  ```bash
  vendor/bin/php-cs-fixer fix
  npm run format
  php artisan test
  npm run check
  make phpstan
  ```
- **Exit:** All quality gates pass, feature ready for review/commit

---

## Scenario Tracking

| Scenario ID | Increment / Task reference | Notes |
|-------------|---------------------------|-------|
| S-001-01 | I5 (PhotoController::rate) | New rating creation |
| S-001-02 | I5 (PhotoController::rate) | Update existing rating |
| S-001-03 | I5 (PhotoController::rate) | Remove rating (rating=0) |
| S-001-04 | I5, I11 (Controller + Concurrency test) | Multiple users rating |
| S-001-05 | I11 (Concurrency test) | Same user concurrent updates |
| S-001-06 | I11 (Concurrency test) | Different users concurrent |
| S-001-07 | I4, I10 (Request validation + Error handling) | Unauthenticated |
| S-001-08 | I4, I10 (Request validation + Error handling) | Unauthorized |
| S-001-09 | I4, I10 (Request validation + Error handling) | Invalid rating |
| S-001-10 | I4, I10 (Request validation + Error handling) | Photo not found |
| S-001-11 | I6, I9 (PhotoResource + UI) | View without rating |
| S-001-12 | I6, I9 (PhotoResource + UI) | View with user rating |
| S-001-13 | I6, I9 (PhotoResource + UI) | No ratings exist |
| S-001-14 | I5, I10 (Controller + Edge cases) | Idempotent removal |
| S-001-15 | I6 (PhotoResource) | Metrics disabled |
| S-001-16 | I9a, I9b (ThumbRatingOverlay + Integration) | Thumbnail hover |
| S-001-17 | I9a, I9b (ThumbRatingOverlay + Integration) | Thumbnail click star |
| S-001-18 | I9c, I9d (PhotoRatingOverlay + Integration) | Full photo hover |
| S-001-19 | I9b (PhotoThumb integration) | Store setting respect |
| S-001-20 | I9c, I9d (PhotoRatingOverlay + Integration) | Auto-hide behavior |
| UI-001-01 | I8, I9 (Widget + Details) | No user rating state |
| UI-001-02 | I8, I9 (Widget + Details) | User has rated state |
| UI-001-03 | I8 (PhotoRatingWidget) | Hover preview |
| UI-001-04 | I8 (PhotoRatingWidget) | Loading state |
| UI-001-05 | I8 (PhotoRatingWidget) | Success state |
| UI-001-06 | I8, I10 (Widget + Error handling) | Error state |
| UI-001-07 | I8, I9 (Widget + Details) | Disabled (not logged in) |
| UI-001-08 | I8, I9 (Widget + Details) | No ratings display |
| UI-001-09 | I9a, I9b (ThumbRatingOverlay) | Thumbnail overlay hover |
| UI-001-10 | I9a, I9b (ThumbRatingOverlay) | Thumbnail overlay click |
| UI-001-11 | I9c, I9d (PhotoRatingOverlay) | Photo overlay hover |
| UI-001-12 | I9c, I9d (PhotoRatingOverlay) | Photo overlay auto-hide |
| UI-001-13 | I9a, I9c (Both overlays) | Mobile disabled |
| FR-001-11 | I12a (Config settings) | Show avg in details setting |
| FR-001-12 | I12a (Config settings) | Show avg in photo view setting |
| FR-001-13 | I12a (Config settings) | Photo view mode setting |
| FR-001-14 | I12a (Config settings) | Show avg in album view setting |
| FR-001-15 | I12a (Config settings) | Album view mode setting |
| FR-001-16 | I12a (Config settings) | Ratings enabled master switch |

## Analysis Gate

**Status:** Not yet executed

**Checklist (to be completed before implementation):**
- [ ] Spec reviewed and approved
- [ ] Plan reviewed and approved
- [ ] All high/medium-impact questions resolved
- [ ] Dependencies identified and available
- [ ] Test strategy defined
- [ ] Increment breakdown reasonable (all ≤90 min)
- [ ] No architectural conflicts with existing code

**Findings:** _To be populated during analysis gate review_

## Exit Criteria

- [x] All migrations run successfully (up and down)
- [x] PhotoRating model created with relationships
- [x] Statistics model enhanced with rating columns
- [x] POST /Photo::rate endpoint implemented
- [x] PhotoResource includes rating data
- [x] Frontend service method added
- [x] PhotoRatingWidget component created (details drawer)
- [x] ThumbRatingOverlay component created (thumbnail hover)
- [x] PhotoRatingOverlay component created (full photo hover)
- [x] PhotoRatingWidget integrated into PhotoDetails drawer
- [x] ThumbRatingOverlay integrated into PhotoThumb component
- [x] PhotoRatingOverlay integrated into PhotoPanel component
- [x] 6 config settings implemented (FR-001-11 through FR-001-16)
- [x] All unit tests pass (models, relationships)
- [x] All feature tests pass (API endpoints, validation, errors, concurrency)
- [x] All frontend tests pass (component, integration)
- [x] PHPStan level 6 passes with no errors
- [x] PHP CS Fixer passes (code style)
- [x] Prettier passes (frontend formatting)
- [x] Manual smoke test completed (all scenarios verified):
  - [x] Rate in details drawer
  - [x] Rate on thumbnail hover
  - [x] Rate on full-size photo hover
  - [x] Overlay auto-hide behavior
  - [x] Mobile responsiveness (overlays hidden)
  - [x] Store setting respect
  - [x] Loading/success/error states
  - [x] Not logged in state
- [x] Documentation updated (knowledge map, roadmap, API docs)
- [x] No security vulnerabilities (SQL injection, XSS, authorization bypass)
- [x] Performance verified (<500ms p95 for rating operations)
- [x] License headers in all new PHP files
- [x] Code follows conventions (snake_case, strict comparison, no empty(), etc.)

## Follow-ups / Backlog

**Deferred Enhancements (Post-Feature):**
1. **Album aggregate ratings** (Q001-21 → Option A): Display average album rating based on photo ratings
2. **Rating notifications** (Q001-23 → Option A): Notify photo owner when photo receives ratings
3. **Accessibility enhancements** (Q001-16 → Option C): Ensure star rating components meet WCAG 2.1 AA standards (keyboard navigation, screen reader support, focus indicators)
4. **Overlay performance optimization:** Consider debouncing hover events and lazy-loading rating data for large albums

**Explicitly Out of Scope (Resolved Questions):**
- **Rating export/import** (Q001-22 → Option C): Not implementing export functionality
- **Data integrity audit command** (Q001-24 → Option B): Not needed - trust transactions
- **Telemetry/analytics** (Q001-19): No telemetry events or metrics collection

**Technical Debt:**
- None identified yet (to be updated during implementation)

**Monitoring:**
- Monitor query performance on photo_ratings table as rating volume grows
- Monitor statistics calculation accuracy (spot check aggregate vs. computed)
- Monitor transaction deadlocks or lock wait timeouts under high concurrency

---

*Last updated: 2025-12-27*
