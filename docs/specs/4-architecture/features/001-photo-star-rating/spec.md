# Feature 001 – Photo Star Rating

| Field | Value |
|-------|-------|
| Status | Implemented |
| Last updated | 2026-01-21 |
| Owners | User |
| Linked plan | `docs/specs/4-architecture/features/001-photo-star-rating/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/001-photo-star-rating/tasks.md` |
| Roadmap entry | #001 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature adds star rating functionality to individual photos, allowing logged-in users to rate photos on a 1-5 scale. The system stores both aggregate statistics (sum and count) on the Photo model for performance and individual user ratings for tracking and preventing duplicate votes. The UI displays an interactive rating widget at the bottom of the photo view with immediate visual feedback.

**Affected modules:** Core (Photo model, Statistics model, new PhotoRating model), Application (PhotoController, Request/Resource classes), REST API (new rating endpoint), UI (PhotoDetails component).

## Goals

- Allow logged-in users to rate photos from 1 to 5 stars
- Store aggregate rating data (sum and count) on the Photo Statistics model for efficient display
- Track individual user ratings to prevent duplicate voting and allow rating updates/removal
- Display current average rating and rating count in the photo details view
- Provide intuitive UI for selecting/changing/removing ratings at the bottom of photo view
- Maintain consistency with existing Lychee patterns (favorites, statistics, metadata updates)

## Non-Goals

- Rating photos anonymously (must be logged in)
- Rating albums or other entities (only individual photos)
- Public display of who rated what (individual ratings are private)
- Rating history or audit trail beyond current user's rating
- Rating notifications or social features
- Advanced rating analytics or trends

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-001-01 | Logged-in user can rate a photo 1-5 stars via UI or API | POST `/Photo::rate` with `photo_id`, `rating` (1-5) returns updated PhotoResource with new average and count. User's rating is stored/updated in `photo_ratings` table. Statistics updated atomically. | Rating must be integer 1-5. User must be authenticated. Photo must exist and **user must have read access** (Q001-05 → Option B: rating is lightweight engagement like favoriting, not privileged edit). | 401 if not authenticated, 403 if no access, 404 if photo not found, 422 if rating invalid. | No telemetry (Q001-19). | User requirement, Q001-05, Q001-19 |
| FR-001-02 | Logged-in user can remove their rating by setting rating to 0 | POST `/Photo::rate` with `rating: 0` deletes user's rating record, decrements count, subtracts rating from sum, recalculates average. Returns updated PhotoResource with **200 OK**. | Rating value 0 is special signal for removal. **Idempotent: removing non-existent rating returns 200 OK (no-op)** (Q001-06 → simpler client logic, standard REST pattern). | Returns 200 OK even if rating doesn't exist (idempotent removal). 401 if not authenticated, 403 if no photo access. | No telemetry (Q001-19). | User requirement, Q001-06, Q001-19 |
| FR-001-03 | User can update their existing rating | POST `/Photo::rate` with new rating value replaces existing rating. Updates sum (subtract old, add new), keeps same count. Returns updated PhotoResource. | Same validation as FR-001-01. Detect existing rating by user_id + photo_id uniqueness. | Same as FR-001-01. | No telemetry (Q001-19). | User requirement, Q001-19 |
| FR-001-04 | Photo details view displays current average rating and vote count | PhotoResource includes `rating_avg` (decimal 0-5, nullable) and `rating_count` (integer >= 0) when metrics are enabled. UI displays stars visualization and count text. | Only show when `metrics_enabled` config is true and user has `CAN_READ_METRICS` permission, consistent with other statistics. | If no ratings exist, show 0 count and no average (nullable). | No event (read operation). | User requirement |
| FR-001-05 | Photo details view displays user's current rating (if any) | PhotoResource includes `user_rating` (integer 1-5, nullable) representing current user's rating. UI pre-selects corresponding star. | Only when user is authenticated. Nullable when user hasn't rated. | If not authenticated, field is null. | No event (read operation). | User requirement |
| FR-001-06 | Rating UI appears in photo details drawer | Interactive star rating component positioned in PhotoDetails drawer below statistics section. Shows 5 clickable star icons (1-5) and a reset option (0). | Follows existing PhotoDetails drawer layout patterns. Only visible to logged-in users. | Read-only for anonymous users (if viewing is allowed). | No event (UI state). | User requirement |
| FR-001-09 | Rating overlay appears on photo thumbnail hover | When mouse hovers over photo thumbnail in album grid, star rating overlay appears at bottom of thumbnail. Shows current average rating and interactive rating selector with **inline [0] button** for removal. Clicking star rates photo without opening details. | Uses existing thumbnail overlay pattern (group-hover, opacity transition). Only visible on desktop (md: breakpoint). Follows `display_thumb_photo_overlay` store pattern. Button 0 shown as "×" or "Remove" for clarity. | Hidden on mobile (details drawer only), respects overlay settings. | No event (UI state). | User requirement, Q001-03 (Option A), Q001-04 (Option A) |
| FR-001-10 | Rating overlay appears on full-size photo hover (lower area) | When viewing full-size photo, hovering over lower portion of image reveals rating overlay. Shows average rating and interactive selector. User can rate without opening details drawer. | Positioned **bottom-center** (horizontally centered, above metadata overlay). Uses gradient background for visibility. **Auto-hides after 3 seconds** of inactivity or when mouse leaves area. | Hidden if user preference disables overlays. Desktop-only (hidden on mobile below md: breakpoint). | No event (UI state). | User requirement, Q001-01 (Option A), Q001-02 (Option A), Q001-04 (Option A) |
| FR-001-07 | Statistics table stores aggregate rating data per photo | `photo_statistics` table gains `rating_sum` (unsigned big integer, default 0) and `rating_count` (unsigned integer, default 0). Average calculated as `sum / count` when count > 0. | Migration adds columns with default values. Existing photos have 0/0 (no ratings). Updates are atomic within rating transaction. | If statistics record doesn't exist, create it during first rating. | No event (schema change). | Performance requirement |
| FR-001-08 | PhotoRating table tracks individual user ratings | New `photo_ratings` table with columns: `id`, `photo_id` (char 24, FK to photos), `user_id` (int, FK to users), `rating` (tinyint 1-5), `created_at`, `updated_at`. Unique constraint on `(photo_id, user_id)`. | On rate action, upsert rating record. On remove (rating 0), delete record. | Foreign key constraints ensure data integrity. | No event (schema change). | Tracking requirement |
| FR-001-11 | Setting: Show average rating in photo details | Boolean config setting `rating_show_avg_in_details` (default: true). When enabled, PhotoDetails drawer displays aggregate rating (average + count). When disabled, aggregate is hidden (user's own rating may still be shown). | Stored in `configs` database table. Read on app initialization. | If disabled, average/count not rendered in PhotoDetails UI. | No event (config read). | User request |
| FR-001-12 | Setting: Show average rating in photo view | Boolean config setting `rating_show_avg_in_photo_view` (default: true). When enabled, full-size photo overlay (PhotoRatingOverlay) displays aggregate rating. When disabled, only user's rating selector shown. | Stored in `configs` database table. Read on app initialization. | If disabled, average/count not rendered in PhotoRatingOverlay. | No event (config read). | User request |
| FR-001-13 | Setting: Show rating UI in photo view (visibility mode) | Enum config setting `rating_photo_view_mode` with values: `always` (always visible, no auto-hide), `hover` (default: appear on hover, auto-hide after 3s), `hidden` (never show overlay). Controls PhotoRatingOverlay visibility behavior. | Stored in `configs` database table as string value. Default: `hover`. | Mode controls overlay rendering and auto-hide logic. `hidden` = no overlay rendered at all. | No event (config read). | User request |
| FR-001-14 | Setting: Show average rating in album view | Boolean config setting `rating_show_avg_in_album_view` (default: true). When enabled, thumbnail overlay (ThumbRatingOverlay) displays aggregate rating. When disabled, only user's rating selector shown. | Stored in `configs` database table. Read on app initialization. | If disabled, average/count not rendered in ThumbRatingOverlay. | No event (config read). | User request |
| FR-001-15 | Setting: Show rating UI in album view (visibility mode) | Enum config setting `rating_album_view_mode` with values: `always` (always visible on thumbnails), `hover` (default: appear on thumbnail hover), `hidden` (never show thumbnail overlay). Controls ThumbRatingOverlay visibility behavior. | Stored in `configs` database table as string value. Default: `hover`. Interacts with existing `display_thumb_photo_overlay` setting. | Mode controls overlay rendering. `hidden` = no rating overlay on thumbnails. | No event (config read). | User request |
| FR-001-16 | Setting: Enable rating functionality | Boolean config setting `ratings_enabled` (default: true). When disabled, all rating UI hidden and `/Photo::rate` endpoint disabled. Independent of `metrics_enabled` setting. Allows granular control over rating vs metrics display. | Stored in `configs` database table. Default: `true` (enabled). | When false, hide all rating widgets/overlays and return 403 from rating endpoint. | No event (config read). | Q001-11 (Option C) |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-001-01 | Rating updates must be atomic | Prevent race conditions when multiple users rate simultaneously or user rapidly changes rating | Use database transactions wrapping: (1) upsert/delete photo_ratings record, (2) update statistics sum/count. Test with concurrent requests. | Laravel DB transactions, unique constraints | Data integrity standard |
| NFR-001-02 | Average rating precision is 2 decimal places | Display consistency and rounding clarity | Store as `DECIMAL(3,2)` (range 0.00-5.00). Display formatted to 2 decimals. **Use PrimeVue half-star icons** (pi-star, pi-star-fill, pi-star-half, pi-star-half-fill) for visual representation (Q001-13 → Option B). | Database schema, PhotoResource serialization, PrimeVue icons | UX consistency, Q001-13 |
| NFR-001-03 | Rating endpoint response time < 500ms (p95) | Maintain UI responsiveness | Single photo rating should complete in sub-second, even under load. Use database indexes on photo_id and user_id. | Indexed foreign keys, efficient query patterns | Performance standard |
| NFR-001-04 | Must follow existing authorization patterns | Consistency with photo access controls | Use `authorize()` logic based on photo read access (Q001-05 → Option B: rating is lightweight engagement like favoriting, not privileged edit). **User must have read access to photo** (same as viewing). Reuse middleware `login_required:album`. | Photo authorization traits, existing middleware | Security consistency, Q001-05 |
| NFR-001-05 | Code follows Lychee PHP conventions | Maintainability and code quality | License headers, snake_case variables, strict comparison (===), PSR-4, no `empty()`, `in_array(..., true)`. Extends appropriate base classes. | php-cs-fixer, phpstan level 6 | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-001-06 | Frontend follows Vue3/TypeScript conventions | Maintainability and code quality | Template-first component structure, Composition API, regular function declarations (no arrow functions), `.then()` instead of async/await, axios calls in services directory. | Prettier, frontend tests | [docs/specs/3-reference/coding-conventions.md](../../../3-reference/coding-conventions.md) |
| NFR-001-07 | Test coverage for all rating paths | Ensure correctness and prevent regression | Unit tests for rating calculation logic. Feature tests for API endpoints covering: new rating, update rating, remove rating, concurrent updates, unauthorized access. Frontend tests for UI component states. | AbstractTestCase, BaseApiWithDataTest, in-memory SQLite | Testing standard |

## UI / Interaction Mock-ups

### 1. Photo Thumbnail Rating Overlay (Album Grid View)

```
┌──────────────────────────────────────┐
│  Album: Summer Vacation 2025         │
├──────────────────────────────────────┤
│                                      │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐
│  │ [Photo] │  │ [Photo] │  │ [Photo] │  ← Grid of thumbnails
│  │         │  │  HOVER  │  │         │
│  │         │  │         │  │         │
│  │    ☆    │  │  ★ ♡ 🛒 │  │    ☆    │  ← Top badges/actions
│  │         │  │─────────│  │         │
│  │         │  │ Sunset  │  │         │  ← Metadata overlay
│  │         │  │ 2025... │  │         │     (existing pattern)
│  │         │  ├─────────┤  │         │
│  │         │  │ ★★★★☆   │  │         │  ← Rating overlay
│  │         │  │ 4.2(15) │  │         │     (NEW - on hover)
│  │         │  │ [1][2]  │  │         │     Interactive stars
│  │         │  │ [3][4]  │  │         │     appear at bottom
│  │         │  │ [5][0]  │  │         │     of thumbnail
│  └─────────┘  └─────────┘  └─────────┘
│
└──────────────────────────────────────┘
```

**States:**

**A. Default state (no hover):**
```
┌─────────┐
│ [Photo] │
│         │
│    ☆    │  ← Only badges visible (starred, cover, etc.)
│         │
│         │
│         │
└─────────┘
```

**B. Hover state - rating overlay appears:**
```
┌─────────┐
│ [Photo] │
│  ★ ♡ 🛒 │  ← Action buttons (existing: favorite, buy)
│─────────│
│ Sunset  │  ← Metadata overlay (existing, respects settings)
│ 2025... │
├─────────┤
│ ★★★★☆   │  ← NEW: Average rating display (4.2 avg)
│ 4.2(15) │     "4.2 stars, 15 votes"
│ Rate:   │  ← NEW: Interactive rating selector
│ ★★★★☆   │     User's current rating: 4 stars
│ [1-4]   │     Stars 1-4 filled, 5 empty (cumulative display)
└─────────┘
```

**C. Interaction - hovering over star 3 (to change from 4 to 3):**
```
├─────────┤
│ ★★★★☆   │  ← Average unchanged (4.2)
│ 4.2(15) │
│ Rate:   │
│ ★★★☆☆   │  ← Preview shows stars 1-3 filled (hover at 3)
│ [1-3]   │     Click star 3 to rate 3 stars
└─────────┘
```

**D. After clicking star 3 (rating changed to 3):**
```
├─────────┤
│ ★★★☆☆   │  ← Average updated (now 3.8)
│ 3.8(15) │     Statistics recalculated
│ Rate:   │
│ ★★★☆☆   │  ← Your rating: 3 stars (1-3 filled)
│ [saved] │     Toast: "Rating updated to 3 stars"
└─────────┘
```

**Implementation notes:**
- **Rating removal:** Inline [0] button shown as "×" or "Remove" (Q001-03 → Option A)
- **Mobile behavior:** Hidden on mobile/tablet below md: breakpoint (Q001-04 → Option A)
- Overlay appears on `group-hover` (desktop only, `md:` breakpoint)
- Uses gradient background: `bg-linear-to-t from-[#00000099]`
- Positioned at bottom of thumbnail (absolute positioning)
- Respects `display_thumb_photo_overlay` store setting
- Star size: compact (smaller than details view for space)
- Clicking star immediately rates photo (no confirmation)
- Toast notification confirms rating saved
- **IMPORTANT: Rating visualization is cumulative:**
  - Rating 1: ★☆☆☆☆ (1 filled)
  - Rating 2: ★★☆☆☆ (1-2 filled)
  - Rating 3: ★★★☆☆ (1-3 filled)
  - Rating 4: ★★★★☆ (1-4 filled)
  - Rating 5: ★★★★★ (1-5 filled)
  - No rating: ☆☆☆☆☆ (all empty)

---

### 2. Full-Size Photo Rating Overlay (Photo View)

```
┌──────────────────────────────────────────────────────────────┐
│                                                              │
│                                                              │
│                    [Full-size photo]                         │
│                                                              │
│                                                              │
│                                                              │
│                                                              │
│  ┌──────────────────────────────────────────┐  ← Lower area │
│  │                                          │     hover zone │
│  │         [Mouse hovering lower area]     │                │
│  │                                          │                │
│  │  ┌────────────────────────────────────┐ │  ← Overlay     │
│  │  │ ★★★★☆ 4.2 (15 votes)               │ │     appears    │
│  │  │ Your rating: [ 0 ][ 1 ][ 2 ][ 3 ] │ │                │
│  │  │              [ 4 ][ 5 ]            │ │                │
│  │  │              ×    ☆    ☆    ☆     │ │                │
│  │  │                   ★    ☆           │ │                │
│  │  └────────────────────────────────────┘ │                │
│  └──────────────────────────────────────────┘                │
└──────────────────────────────────────────────────────────────┘
      ^                                           ^
      Overlay (title/EXIF)                       Dock buttons
      (existing, bottom-left)                    (existing, bottom-right)
```

**Chosen positioning (Q001-01 → Option A):**

```
│                    [Photo]                         │
│  ┌────────────────────────────────────────────┐   │
│  │ ★★★★☆ 4.2  Rate: ★★★★☆  [0][1][2][3][4][5]│   │ ← Bottom-center
│  └────────────────────────────────────────────┘   │
│  Title: Sunset                        [Dock btns] │
```

**Rationale:** Centered position is more discoverable and doesn't compete with Dock buttons. Symmetrical with metadata overlay below.

---

**States:**

**A. No hover - overlay hidden:**
```
│                    [Photo]                         │
│                                                    │
│  Title: Sunset                            [Dock]  │
```

**B. Hover lower area - overlay appears (user has rated 4 stars):**
```
│                    [Photo]                         │
│  ┌──────────────────────────────────────────┐     │
│  │ ★★★★☆ 4.2 (15)  Your rating: ★★★★☆      │     │
│  │ [0][1][2][3][4][5]  (click to change)   │     │
│  └──────────────────────────────────────────┘     │
│  Title: Sunset                            [Dock]  │
```

**C. Hover over star 3 while rated 4 (preview change):**
```
│                    [Photo]                         │
│  ┌──────────────────────────────────────────┐     │
│  │ ★★★★☆ 4.2 (15)  Preview: ★★★☆☆ (3)       │     │
│  │ [0][1][2][3][4][5]  (click to rate 3)   │     │
│  └──────────────────────────────────────────┘     │
│  Title: Sunset                            [Dock]  │
```

**Implementation notes:**
- **Positioning:** Bottom-center, horizontally centered (Q001-01 → Option A)
- **Auto-hide timer:** 3 seconds of inactivity (Q001-02 → Option A)
- **Rating removal:** Inline [0] button shown as "×" before stars (Q001-03 → Option A)
- **Mobile behavior:** Hidden on mobile/tablet (Q001-04 → Option A), rating only via details drawer
- Triggered by mouse entering lower 20-30% of photo area (desktop only, md: breakpoint)
- Semi-transparent gradient background for readability
- Persists while mouse is over the rating overlay itself (cancels auto-hide)
- Compact horizontal layout to minimize obstruction
- Respects `image_overlay_type` and overlay preference settings
- z-index layers properly with existing Overlay and Dock
- **Cumulative star display:** Rating N shows stars 1 through N filled

---

### 3. Photo Details Drawer - Rating Widget

```
┌────────────────────────────────────────────────────────────┐
│ Photo Details                                         [×]  │
├────────────────────────────────────────────────────────────┤
│                                                            │
│  [Photo metadata: title, description, EXIF, etc.]         │
│                                                            │
│  Statistics                                                │
│  ├─ Views: 142                                             │
│  ├─ Downloads: 23                                          │
│  ├─ Favorites: 8                                           │
│  └─ Shares: 5                                              │
│                                                            │
│  Rating                                                    │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  Average: ★★★★☆ 4.2 (15 votes)                       │ │
│  │                                                       │ │
│  │  Your rating:                                         │ │
│  │  [ 0 ] [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ]                 │ │
│  │   ×     ☆     ☆     ☆     ★     ☆                    │ │
│  │         │     │     │     │     │                     │ │
│  │         └─────┴─────┴─────┴─────┘                     │ │
│  │         Clickable star buttons (current: 4)           │ │
│  │                                                       │ │
│  │  ⇄ Click 1-5 to rate, 0 to remove your rating        │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                            │
└────────────────────────────────────────────────────────────┘
```

**States:**

1. **Not rated by user, no ratings exist:**
   ```
   Average: No ratings yet
   Your rating: [ 0 ] [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ]
                 ×     ☆     ☆     ☆     ☆     ☆
   ```

2. **Not rated by user, others have rated:**
   ```
   Average: ★★★☆☆ 3.4 (12 votes)
   Your rating: [ 0 ] [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ]
                 ×     ☆     ☆     ☆     ☆     ☆
   ```

3. **User has rated (example: 5 stars):**
   ```
   Average: ★★★★☆ 4.2 (15 votes)
   Your rating: [ 0 ] [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ]
                 ×     ☆     ☆     ☆     ☆     ★
                                               ^selected
   ```

4. **Hover state (hovering over 3):**
   ```
   Your rating: [ 0 ] [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ]
                 ×     ★     ★     ★     ☆     ☆
                       └─────┴─────┘
                       Hover preview
   ```

**Interaction flow:**
- Click any number 1-5: Submit rating, update statistics, show success toast
- Click 0: Remove rating (if exists), update statistics, show success toast
- Visual feedback: Filled stars (★) for selected/hovered, empty (☆) for unselected
- Disabled state: Gray out if user not logged in or lacks permission

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-001-01 | User rates unrated photo for first time → rating stored, statistics updated (count=1, avg=rating) |
| S-001-02 | User updates existing rating → old rating replaced, statistics recalculated (sum adjusted) |
| S-001-03 | User removes rating (sets to 0) → rating deleted, statistics updated (count-1, sum-old_rating) |
| S-001-04 | Multiple users rate same photo → each rating tracked separately, statistics aggregate correctly |
| S-001-05 | Concurrent rating updates by same user → last write wins, no duplicate records (unique constraint enforced) |
| S-001-06 | Concurrent ratings by different users → both succeed, statistics correctly reflect both (atomic updates) |
| S-001-07 | Unauthenticated user attempts to rate → 401 Unauthorized |
| S-001-08 | User without photo access attempts to rate → 403 Forbidden |
| S-001-09 | Invalid rating value (0.5, 6, -1, "abc") → 422 Unprocessable Entity with validation error |
| S-001-10 | Photo doesn't exist → 404 Not Found |
| S-001-11 | User views photo they haven't rated → UI shows average rating, user rating is null |
| S-001-12 | User views photo they have rated → UI shows average rating + pre-selects user's rating |
| S-001-13 | Photo with no ratings → displays "No ratings yet", count=0, avg=null |
| S-001-14 | User removes non-existent rating (rating 0 when never rated) → no-op, returns success (idempotent) |
| S-001-15 | Metrics disabled in config → rating data not shown in PhotoResource (but can still rate) |
| S-001-16 | User hovers over photo thumbnail → rating overlay appears at bottom with average and interactive stars |
| S-001-17 | User clicks star on thumbnail overlay → photo is rated, overlay updates, toast confirms |
| S-001-18 | User hovers over full-size photo lower area → rating overlay appears with current rating |
| S-001-19 | Thumbnail overlay respects `display_thumb_photo_overlay` setting (hover/always/never) |
| S-001-20 | Photo overlay auto-hides after inactivity or mouse leaves (configurable behavior) |
| S-001-21 | Setting `rating_show_avg_in_details` controls average display in PhotoDetails drawer |
| S-001-22 | Setting `rating_show_avg_in_photo_view` controls average display in PhotoRatingOverlay |
| S-001-23 | Setting `rating_photo_view_mode` controls overlay visibility (always/hover/hidden) |
| S-001-24 | Setting `rating_show_avg_in_album_view` controls average display in ThumbRatingOverlay |
| S-001-25 | Setting `rating_album_view_mode` controls thumbnail overlay visibility (always/hover/hidden) |

## Test Strategy

- **Core (Unit tests):**
  - PhotoRating model relationships (belongsTo Photo, belongsTo User)
  - Photo hasMany PhotoRatings relationship
  - Statistics model rating_avg calculation helper (if added)
  - Rating validation logic (1-5 range, integer only)

- **Application (Feature tests):**
  - `tests/Feature_v2/Photo/PhotoRatingTest.php`:
    - POST `/Photo::rate` with valid rating (1-5) → 200, statistics updated
    - POST `/Photo::rate` to update existing rating → 200, statistics recalculated
    - POST `/Photo::rate` with rating=0 to remove → 200, statistics decremented
    - POST `/Photo::rate` unauthenticated → 401
    - POST `/Photo::rate` without photo access → 403
    - POST `/Photo::rate` with invalid rating (6, 0.5, "abc") → 422
    - POST `/Photo::rate` with non-existent photo_id → 404
    - GET PhotoResource includes rating_avg, rating_count, user_rating
    - Concurrent rating test (simulate race condition) → verify atomicity
    - Rating removal idempotency test (remove twice) → no error

- **REST (API contract):**
  - OpenAPI schema for POST `/Photo::rate` endpoint
  - Request schema: `{ photo_id: string, rating: 0|1|2|3|4|5 }`
  - Response schema: PhotoResource with statistics embedded
  - Error response schemas (401, 403, 404, 422)

- **UI (Component tests):**
  - `PhotoRating.vue` component:
    - Renders 0-5 buttons correctly
    - Pre-selects user's current rating if exists
    - Displays average rating and count
    - Handles click events (calls rating service)
    - Shows loading state during API call
    - Displays success/error toasts
    - Disabled state when not logged in
    - Hover preview shows filled stars up to hovered value

- **Docs/Contracts:**
  - Update knowledge map with PhotoRating model and relationships
  - Update PhotoResource schema documentation
  - Add rating endpoint to API documentation

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-001-01 | PhotoRating model: id, photo_id, user_id, rating (1-5), timestamps. Relationships: belongsTo Photo, belongsTo User. Unique constraint (photo_id, user_id). | core (Models) |
| DO-001-02 | Photo model enhancement: hasMany PhotoRatings relationship. | core (Models) |
| DO-001-03 | Statistics model enhancement: rating_sum (unsigned bigint), rating_count (unsigned int). Calculated field: rating_avg (decimal 3,2). | core (Models) |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-001-01 | POST /Photo::rate | Set or update user's rating for a photo. Body: `{ photo_id: string, rating: 0-5 }`. Response: PhotoResource with updated statistics. | Middleware: login_required:album |
| API-001-02 | GET /Photo (existing) | Enhanced to include rating data in PhotoResource: rating_avg, rating_count (when metrics enabled), user_rating (when authenticated). | No API change, response enhancement |

### CLI Commands / Flags

Not applicable (no CLI component for this feature).

### Telemetry Events

Not applicable (no telemetry/analytics for this feature per Q001-19).

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-001-01 | `tests/Feature_v2/Photo/fixtures/photos_with_ratings.json` | Sample photos with varying rating counts and averages for testing display logic. |
| FX-001-02 | Database seeder (in-memory) | Seed photo_ratings records for testing concurrent updates, edge cases (single rating, many ratings, etc.). |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-001-01 | Rating widget - no user rating | User hasn't rated this photo. Display average + "Your rating" with unselected stars (0-5 buttons). |
| UI-001-02 | Rating widget - user has rated | User has rated this photo. Display average + pre-select user's rating button. |
| UI-001-03 | Rating widget - hover preview | User hovers over star button 1-5. Show filled stars **1 through N** for hover value N (cumulative visual preview). For example, hovering over star 3 shows stars 1, 2, 3 filled and 4, 5 empty. |
| UI-001-04 | Rating widget - loading | API call in progress. Disable buttons, show loading indicator. |
| UI-001-05 | Rating widget - success | Rating saved successfully. Show success toast, update display with new average/count. |
| UI-001-06 | Rating widget - error | API error (network, validation, auth). Show error toast with message. |
| UI-001-07 | Rating widget - disabled (not logged in) | User not authenticated. Gray out buttons, show tooltip "Log in to rate". |
| UI-001-08 | Rating display - no ratings | No one has rated this photo. Display "No ratings yet" instead of average. |
| UI-001-09 | Thumbnail rating overlay - hover | Mouse hovers over thumbnail. Overlay appears at bottom with gradient background, shows average + interactive stars. |
| UI-001-10 | Thumbnail rating overlay - click star | User clicks star on overlay. Loading indicator, then success toast, overlay updates with new average. |
| UI-001-11 | Photo rating overlay - lower area hover | Mouse in lower 20-30% of full-size photo. Overlay appears (center or bottom-right) with rating UI. |
| UI-001-12 | Photo rating overlay - auto-hide | After 3s inactivity or mouse leaves area, overlay fades out. Persists if mouse over overlay itself. |
| UI-001-13 | Mobile - overlays disabled | On mobile/tablet (below md: breakpoint), rating overlays hidden. Rating only via details drawer. |

## Telemetry & Observability

Not applicable (no telemetry/analytics for this feature per Q001-19).

**Logging (standard application logs only):**
- INFO: Successful rating creation/update/removal
- WARNING: Validation failures (invalid rating value)
- ERROR: Database transaction failures, foreign key violations

## Documentation Deliverables

- **Roadmap update:** Add Feature 001 to Active Features table
- **Knowledge map update:** Add PhotoRating model, relationships to Photo and User, Statistics column additions
- **API documentation:** Document POST `/Photo::rate` endpoint, updated PhotoResource schema
- **Feature README (this spec):** Serve as implementation reference
- **ADR (if applicable):** Decision on statistics denormalization vs. computed properties (deferred unless needed)

## Fixtures & Sample Data

**Test fixtures needed:**
1. `tests/Feature_v2/Photo/fixtures/unrated_photo.json` - Photo with no ratings (count=0, avg=null)
2. `tests/Feature_v2/Photo/fixtures/rated_photos.json` - Photos with various rating scenarios:
   - Single 5-star rating
   - Multiple ratings with fractional average (e.g., 4.33)
   - Many ratings (e.g., 100 ratings, avg 3.8)
3. Database seeder entries for photo_ratings table with known user_id/photo_id pairs

## Spec DSL

```yaml
domain_objects:
  - id: DO-001-01
    name: PhotoRating
    table: photo_ratings
    fields:
      - name: id
        type: bigint
        constraints: primary key
      - name: photo_id
        type: char(24)
        constraints: foreign key (photos.id), indexed
      - name: user_id
        type: integer
        constraints: foreign key (users.id), indexed
      - name: rating
        type: tinyint
        constraints: "1-5"
      - name: created_at
        type: timestamp
      - name: updated_at
        type: timestamp
    constraints:
      - unique: [photo_id, user_id]
  - id: DO-001-02
    name: Photo
    enhancements:
      - hasMany: PhotoRatings
  - id: DO-001-03
    name: Statistics
    table: photo_statistics
    new_fields:
      - name: rating_sum
        type: unsigned_bigint
        default: 0
      - name: rating_count
        type: unsigned_int
        default: 0
    computed_fields:
      - name: rating_avg
        type: decimal(3,2)
        formula: "rating_sum / rating_count (when count > 0, else null)"

routes:
  - id: API-001-01
    method: POST
    path: /Photo::rate
    middleware: login_required:album
    request:
      photo_id: string (required, exists in photos)
      rating: integer (required, 0-5)
    response:
      success: PhotoResource (200)
      errors:
        - 401: Unauthenticated
        - 403: Forbidden (no photo access)
        - 404: Photo not found
        - 422: Validation failed (invalid rating)
  - id: API-001-02
    method: GET
    path: /Photo
    enhancements:
      - rating_avg: decimal (nullable, in statistics section)
      - rating_count: integer (in statistics section)
      - user_rating: integer (nullable, 1-5, top level)

telemetry_events: []  # No telemetry (Q001-19)

fixtures:
  - id: FX-001-01
    path: tests/Feature_v2/Photo/fixtures/photos_with_ratings.json
    purpose: Display testing
  - id: FX-001-02
    type: database_seeder
    purpose: Concurrent update testing

ui_states:
  - id: UI-001-01
    description: No user rating, display average + unselected stars
  - id: UI-001-02
    description: User has rated, pre-select user rating
  - id: UI-001-03
    description: Hover preview on star buttons
  - id: UI-001-04
    description: Loading state during API call
  - id: UI-001-05
    description: Success state with toast
  - id: UI-001-06
    description: Error state with toast
  - id: UI-001-07
    description: Disabled state (not logged in)
  - id: UI-001-08
    description: No ratings yet display
```

## Appendix

### Database Schema Reference

**Migration: `create_photo_ratings_table`**
```sql
CREATE TABLE photo_ratings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    photo_id CHAR(24) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY unique_photo_user_rating (photo_id, user_id),
    FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_photo_id (photo_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Migration: `add_rating_columns_to_statistics`**
```sql
ALTER TABLE photo_statistics
ADD COLUMN rating_sum BIGINT UNSIGNED NOT NULL DEFAULT 0,
ADD COLUMN rating_count INT UNSIGNED NOT NULL DEFAULT 0;
```

### API Request/Response Examples

**Request: Rate a photo (new rating)**
```http
POST /Photo::rate HTTP/1.1
Content-Type: application/json
Authorization: Bearer <token>

{
  "photo_id": "abc123def456ghi789jkl012",
  "rating": 4
}
```

**Response: Success (200)**
```json
{
  "id": "abc123def456ghi789jkl012",
  "title": "Sunset Over Mountains",
  "description": "Beautiful sunset...",
  "is_starred": false,
  "owner_id": 42,
  "statistics": {
    "visit_count": 142,
    "download_count": 23,
    "favourite_count": 8,
    "shared_count": 5,
    "rating_avg": 4.20,
    "rating_count": 15
  },
  "user_rating": 4,
  "created_at": "2025-01-15T10:30:00Z",
  "updated_at": "2025-12-27T14:22:00Z"
}
```

**Request: Remove rating**
```http
POST /Photo::rate HTTP/1.1
Content-Type: application/json
Authorization: Bearer <token>

{
  "photo_id": "abc123def456ghi789jkl012",
  "rating": 0
}
```

**Response: Success (200, rating removed)**
```json
{
  "id": "abc123def456ghi789jkl012",
  "statistics": {
    "rating_avg": 4.14,
    "rating_count": 14
  },
  "user_rating": null
}
```

**Response: Validation error (422)**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "rating": [
      "The rating must be between 0 and 5."
    ]
  }
}
```

### Implementation Notes

- **Atomic updates:** Use `DB::transaction()` wrapper around PhotoRating upsert and Statistics update
- **Efficiency:** Consider adding database trigger or observer pattern to auto-update statistics (evaluate trade-offs in plan phase)
- **Future enhancement:** If rating volume becomes high, consider moving to event-driven update (queue job) instead of synchronous
- **Consistency check:** Provide artisan command to recalculate all statistics from photo_ratings table (for data integrity audits)

---

*Last updated: 2025-12-27*
