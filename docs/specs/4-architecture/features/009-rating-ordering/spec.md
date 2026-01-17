# Feature 009 – Rating Ordering and Smart Albums

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-01-16 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/009-rating-ordering/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/009-rating-ordering/tasks.md` |
| Roadmap entry | #009 |
| Dependencies | Feature 001 (Photo Star Rating) |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/5-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

This feature extends the photo rating system (Feature 001) with sorting capabilities and rating-based smart albums. Photos will have a denormalized `rating_avg` column for efficient sorting. A new sorting option allows ordering photos by rating. Seven new smart albums are introduced: Unrated, 1★, 2★, 3★, 4★, 5★, and Best Pictures. Each smart album has independent enable/disable and public/private settings following existing smart album patterns.

**Affected modules:** Core (Photo model, Statistics model), Application (sorting configuration, smart album classes), REST API (SmartAlbumResource), UI (sorting dropdown, smart album display).

## Goals

- Add `rating_avg` column to photos table for efficient ORDER BY operations
- Add "Sort by Rating" option to photo sorting configuration
- Create 6 rating-tier smart albums (Unrated, 1★ through 5★) with hybrid threshold logic
- Create Best Pictures smart album with configurable photo count cutoff (includes ties) - **Lychee SE only**
- Provide enable/disable config for each new smart album
- Provide public/private access control for each new smart album (like existing smart albums)
- Sort rating-based smart albums by rating descending by default

## Non-Goals

- Changing the rating system itself (Feature 001 scope)
- Album-level aggregate ratings
- Rating history or trends
- User-specific smart albums (all users see same smart albums based on their access permissions)
- Customizable thresholds for rating smart albums (fixed logic per Q-009-02)

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-009-01 | Photos table has `rating_avg` column for sorting | Migration adds `rating_avg DECIMAL(5,4) NULL` column with index. Updated atomically when rating changes (same transaction as rating_sum/rating_count). NULL when no ratings. 4 decimal places provide fine granularity for sorting. | Column exists, indexed, values match computed rating_sum/rating_count. | Migration rollback removes column. | No telemetry. | Q-009-01 (Option B), Q-009-05 (Option B) |
| FR-009-02 | Sorting photos by rating is available | New sorting option `rating` added to photo sorting enum. When selected, photos ordered by `COALESCE(rating_avg, -1) DESC` for cross-database compatibility and index usage. Unrated photos (NULL) appear last. | Config `sorting_photos_column` accepts `rating` value. UI shows "Rating" in sort dropdown. | Invalid sort column rejected with 422. | No telemetry. | User requirement, Q-009-06 |
| FR-009-03 | Unrated smart album shows photos with no ratings | Smart album `unrated` contains photos where `rating_avg IS NULL` (equivalent to rating_count = 0). | Photos with any rating excluded. New photos without ratings included. | Empty if all photos rated. | No telemetry. | User requirement |
| FR-009-04 | 1★ smart album shows photos rated 1.0 to <2.0 | Smart album `1_star` contains photos where `1.0 <= rating_avg < 2.0` (exact bucket). | Only photos in range included. Photos at exactly 2.0 excluded. | Empty if no photos in range. | No telemetry. | Q-009-02 (Option C) |
| FR-009-05 | 2★ smart album shows photos rated 2.0 to <3.0 | Smart album `2_stars` contains photos where `2.0 <= rating_avg < 3.0` (exact bucket). | Only photos in range included. Photos at exactly 3.0 excluded. | Empty if no photos in range. | No telemetry. | Q-009-02 (Option C) |
| FR-009-06 | 3★ smart album shows photos rated 3.0+ | Smart album `3_stars` contains photos where `rating_avg >= 3.0` (threshold). | Photos rated 3.0, 4.0, 5.0 all included. Photos below 3.0 excluded. | Empty if no photos >= 3.0. | No telemetry. | Q-009-02 (Option C), User requirement |
| FR-009-07 | 4★ smart album shows photos rated 4.0+ | Smart album `4_stars` contains photos where `rating_avg >= 4.0` (threshold). | Photos rated 4.0, 5.0 included. Photos below 4.0 excluded. | Empty if no photos >= 4.0. | No telemetry. | Q-009-02 (Option C) |
| FR-009-08 | 5★ smart album shows photos rated 5.0 | Smart album `5_stars` contains photos where `rating_avg >= 5.0` (threshold, effectively = 5.0). | Only photos with perfect 5.0 average included. | Empty if no perfect ratings. | No telemetry. | Q-009-02 (Option C) |
| FR-009-09 | Best Pictures smart album shows top N photos by rating with ties (Lychee SE only) | Smart album `best_pictures` shows top N photos ordered by `rating_avg DESC`. Includes all photos that tie with the Nth photo's rating. Config `best_pictures_count` (default: 100) sets N. **Requires Lychee SE license activation.** | Photos with highest ratings shown. If photo N and N+1 have same rating, both included. Unrated photos excluded. | Empty if no rated photos. May show > N if ties exist. Hidden if Lychee SE not activated. | No telemetry. | Q-009-03 (Option B) |
| FR-009-10 | Rating smart albums sorted by rating descending | All rating-based smart albums (unrated, 1★-5★, best_pictures) default to `ORDER BY rating_avg DESC NULLS LAST`. For unrated album, secondary sort by `created_at DESC`. | Highest-rated photos appear first. | N/A (default sort). | No telemetry. | Q-009-04 |
| FR-009-11 | Each rating smart album has enable/disable config | Configs: `enable_unrated`, `enable_1_star`, `enable_2_stars`, `enable_3_stars`, `enable_4_stars`, `enable_5_stars`, `enable_best_pictures` (all BOOL, default true). Disabled albums not shown in UI or API. | Album hidden when disabled. Re-enabling shows album with correct photos. | N/A (config read). | No telemetry. | User requirement |
| FR-009-12 | Each rating smart album has public/private setting | Uses existing `AccessPermission` table with `base_album_id` = smart album ID. Admins can set public, public_hidden, or private via existing smart album protection API. | Public albums visible to anonymous users. Private albums require authentication. | Default is private (no AccessPermission record). | No telemetry. | User requirement |
| FR-009-13 | Smart album type enum extended with rating types | `SmartAlbumType` enum gains: `UNRATED`, `ONE_STAR`, `TWO_STARS`, `THREE_STARS`, `FOUR_STARS`, `FIVE_STARS`, `BEST_PICTURES`. | Enum values map to album IDs. Factory creates correct album class. | Invalid type returns 404. | No telemetry. | Implementation detail |
| FR-009-14 | Rating sync on photo rating change | When a photo is rated/unrated via `/Photo::rate`, the `rating_avg` column is updated in the same transaction. Value = rating_sum / rating_count (or NULL if count = 0). | Column always matches computed value. No stale data. | Transaction rollback on failure. | No telemetry. | Q-009-01 decision |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-009-01 | Rating-based sorting must perform well on large albums | User experience for albums with 10k+ photos | Query uses index on `rating_avg` column. p95 response < 500ms for 10k photos. | Indexed `rating_avg` column | Performance standard |
| NFR-009-02 | Smart album queries must be efficient | Avoid full table scans for rating filtering | Queries use index on `rating_avg`. WHERE clauses are sargable. | Indexed `rating_avg` column | Performance standard |
| NFR-009-03 | Rating smart albums sorted by rating DESC | Natural expectation for rating-based albums | Default ORDER BY includes `rating_avg DESC`. UI shows highest-rated first. | Sorting infrastructure | Q-009-04 |
| NFR-009-04 | Code follows Lychee PHP conventions | Maintainability | License headers, snake_case, strict comparison, PSR-4, no `empty()`. Smart album classes follow existing patterns. | php-cs-fixer, phpstan level 6 | Coding conventions |
| NFR-009-05 | Smart albums follow existing architectural patterns | Consistency and maintainability | New smart albums extend `BaseSmartAlbum`, use singleton pattern, register in `AlbumFactory`. | Existing smart album infrastructure | Architecture consistency |
| NFR-009-06 | Test coverage for all smart album conditions | Correctness | Unit tests for each threshold/bucket logic. Feature tests for API responses. Edge cases (boundary values, empty albums). | AbstractTestCase, BaseApiWithDataTest | Testing standard |

## UI / Interaction Mock-ups

### 1. Sorting Dropdown with Rating Option

```
┌──────────────────────────────────────┐
│  Album: Summer Vacation 2025         │
├──────────────────────────────────────┤
│                                      │
│  Sort by: [Rating        ▼]          │  ← New "Rating" option
│           ┌──────────────┐           │
│           │ Created Date │           │
│           │ Taken Date   │           │
│           │ Title        │           │
│           │ Rating     ◄─┼───────────│  ← Selected
│           │ Random       │           │
│           └──────────────┘           │
│                                      │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐
│  │ [Photo] │  │ [Photo] │  │ [Photo] │
│  │ ★★★★★   │  │ ★★★★☆   │  │ ★★★★☆   │  ← Sorted by rating DESC
│  │  5.0    │  │  4.5    │  │  4.2    │
│  └─────────┘  └─────────┘  └─────────┘
│                                      │
└──────────────────────────────────────┘
```

### 2. Smart Albums in Gallery Root

```
┌──────────────────────────────────────────────────────────────┐
│  Gallery                                                      │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  Smart Albums                                                │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐│
│  │ Recent  │ │ Starred │ │On This  │ │Unsorted │ │Untagged ││  ← Existing
│  │   📅    │ │   ⭐    │ │  Day    │ │   📁    │ │   🏷️    ││
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘│
│                                                              │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐│
│  │Unrated  │ │  1★     │ │  2★     │ │  3★+    │ │  4★+    ││  ← NEW
│  │   ☆     │ │   ★     │ │  ★★    │ │  ★★★   │ │ ★★★★   ││
│  │  (142)  │ │  (12)   │ │  (28)   │ │  (156)  │ │  (89)   ││
│  └─────────┘ └─────────┘ └─────────┘ └─────────┘ └─────────┘│
│                                                              │
│  ┌─────────┐ ┌─────────┐                                    │
│  │  5★     │ │  Best   │                                    │  ← NEW
│  │ ★★★★★  │ │Pictures │                                    │
│  │  (23)   │ │  (50)   │                                    │
│  └─────────┘ └─────────┘                                    │
│                                                              │
│  Albums                                                      │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐                        │
│  │[Album 1]│ │[Album 2]│ │[Album 3]│                        │
│  └─────────┘ └─────────┘ └─────────┘                        │
│                                                              │
└──────────────────────────────────────────────────────────────┘
```

### 3. Smart Album Titles (Localized)

| Album ID | English Title | Description |
|----------|--------------|-------------|
| unrated | Unrated | Photos without ratings |
| 1_star | 1 Star | Photos rated 1.0-1.9 |
| 2_stars | 2 Stars | Photos rated 2.0-2.9 |
| 3_stars | 3+ Stars | Photos rated 3.0 or higher |
| 4_stars | 4+ Stars | Photos rated 4.0 or higher |
| 5_stars | 5 Stars | Photos with perfect 5.0 rating |
| best_pictures | Best Pictures | Top rated photos |

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-009-01 | Sort photos by rating → highest-rated first, unrated last |
| S-009-02 | Photo rated → rating_avg column updated in same transaction |
| S-009-03 | Photo rating removed → rating_avg set to NULL |
| S-009-04 | View Unrated album → only photos with no ratings shown |
| S-009-05 | View 1★ album → only photos with 1.0 <= avg < 2.0 shown |
| S-009-06 | View 2★ album → only photos with 2.0 <= avg < 3.0 shown |
| S-009-07 | View 3★ album → photos with avg >= 3.0 shown (includes 4★, 5★) |
| S-009-08 | View 4★ album → photos with avg >= 4.0 shown (includes 5★) |
| S-009-09 | View 5★ album → only photos with avg = 5.0 shown |
| S-009-10 | View Best Pictures → top N photos by rating, ties included |
| S-009-11 | Best Pictures with ties → more than N photos shown if ties at cutoff |
| S-009-12 | Disable smart album → album not shown in UI or API |
| S-009-13 | Enable disabled smart album → album appears with correct photos |
| S-009-14 | Set smart album public → anonymous users can view |
| S-009-15 | Set smart album private → only authenticated users can view |
| S-009-16 | Photo boundary at 3.0 → appears in 3★+ but not 2★ |
| S-009-17 | Photo boundary at 2.0 → appears in 2★ but not 1★ |
| S-009-18 | Rating smart albums respect photo access permissions |
| S-009-19 | Rating smart albums respect NSFW filtering settings |

## Test Strategy

- **Core (Unit tests):**
  - Smart album condition closures for each rating tier
  - Boundary value tests (1.0, 2.0, 3.0, 4.0, 5.0, NULL)
  - Best Pictures tie-inclusion logic
  - Rating avg calculation and NULL handling

- **Application (Feature tests):**
  - `tests/Feature_v2/SmartAlbum/RatingSmartAlbumsTest.php`:
    - Each smart album returns correct photos
    - Sorting by rating works correctly
    - Enable/disable config respected
    - Public/private access control works
    - Photos at exact boundaries handled correctly
    - Best Pictures cutoff with ties
  - `tests/Feature_v2/Photo/PhotoRatingSyncTest.php`:
    - Rating change updates rating_avg column
    - Rating removal sets rating_avg to NULL

- **REST (API contract):**
  - SmartAlbumResource includes new album types
  - Sorting parameter accepts `rating` value
  - Error responses for disabled/private albums

- **UI (Component tests):**
  - Sort dropdown includes Rating option
  - Smart albums display with correct icons and counts
  - Rating-based albums show photos in rating order

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-009-01 | Photo model enhancement: `rating_avg DECIMAL(5,4) NULL` column, indexed | core (Models), database |
| DO-009-02 | SmartAlbumType enum extension: UNRATED, ONE_STAR, TWO_STARS, THREE_STARS, FOUR_STARS, FIVE_STARS, BEST_PICTURES | core (Enum) |
| DO-009-03 | UnratedAlbum class: extends BaseSmartAlbum, condition `rating_avg IS NULL` | app/SmartAlbums |
| DO-009-04 | OneStarAlbum class: extends BaseSmartAlbum, condition `1.0 <= rating_avg < 2.0` | app/SmartAlbums |
| DO-009-05 | TwoStarsAlbum class: extends BaseSmartAlbum, condition `2.0 <= rating_avg < 3.0` | app/SmartAlbums |
| DO-009-06 | ThreeStarsAlbum class: extends BaseSmartAlbum, condition `rating_avg >= 3.0` | app/SmartAlbums |
| DO-009-07 | FourStarsAlbum class: extends BaseSmartAlbum, condition `rating_avg >= 4.0` | app/SmartAlbums |
| DO-009-08 | FiveStarsAlbum class: extends BaseSmartAlbum, condition `rating_avg >= 5.0` | app/SmartAlbums |
| DO-009-09 | BestPicturesAlbum class: extends BaseSmartAlbum, top N by rating with ties | app/SmartAlbums |

### API Routes / Services

| ID | Transport | Description | Notes |
|----|-----------|-------------|-------|
| API-009-01 | GET /Album (existing) | Smart albums include new rating-based albums | Response includes unrated, 1_star, ..., best_pictures |
| API-009-02 | GET /Album/{smart_album_id} | Fetch individual rating smart album | Same as existing smart albums |
| API-009-03 | POST /Album::setSmartProtectionPolicy | Set public/private for rating smart albums | Existing endpoint, new album IDs supported |

### CLI Commands / Flags

| ID | Command | Behaviour |
|----|---------|-----------|
| CLI-009-01 | `php artisan lychee:sync-rating-avg` | Backfill rating_avg column from existing rating_sum/rating_count. For migration of existing installations. |

### Telemetry Events

Not applicable (no telemetry for this feature).

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-009-01 | Database seeder | Photos with various ratings: unrated, each tier, boundary values, ties for Best Pictures testing |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-009-01 | Sort by Rating selected | Photos reorder with highest-rated first, unrated at end |
| UI-009-02 | Rating smart album displayed | Album shows with star icon, photo count, correct photos |
| UI-009-03 | Empty rating smart album | Album shows "(0)" count or hidden based on config |
| UI-009-04 | Best Pictures with ties | Album may show more than configured count |

## Telemetry & Observability

Not applicable (no telemetry for this feature).

**Logging (standard application logs only):**
- INFO: Smart album photo queries (if verbose logging enabled)
- DEBUG: Rating avg sync on photo rate action

## Documentation Deliverables

- **Roadmap update:** Add Feature 009 to Active Features table
- **Knowledge map update:** Add rating smart album classes, photo.rating_avg column
- **User documentation:** Document new smart albums and sorting option
- **Admin documentation:** Document enable/disable configs and public/private settings

## Fixtures & Sample Data

**Test fixtures needed:**
1. Photos with NULL rating_avg (unrated)
2. Photos with rating_avg at each boundary (1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 4.0, 4.5, 5.0)
3. Multiple photos with identical ratings (for tie testing in Best Pictures)
4. Large dataset for performance testing (1000+ rated photos)

## Spec DSL

```yaml
domain_objects:
  - id: DO-009-01
    name: Photo
    table: photos
    new_fields:
      - name: rating_avg
        type: decimal(5,4)
        nullable: true
        indexed: true
        description: Denormalized average rating for sorting efficiency (4 decimal places for granularity)
  - id: DO-009-02
    name: SmartAlbumType
    type: enum
    new_values:
      - UNRATED: 'unrated'
      - ONE_STAR: '1_star'
      - TWO_STARS: '2_stars'
      - THREE_STARS: '3_stars'
      - FOUR_STARS: '4_stars'
      - FIVE_STARS: '5_stars'
      - BEST_PICTURES: 'best_pictures'

smart_albums:
  - id: unrated
    class: UnratedAlbum
    condition: "rating_avg IS NULL"
    config_enable: enable_unrated
    sort: "created_at DESC"
  - id: 1_star
    class: OneStarAlbum
    condition: "rating_avg >= 1.0 AND rating_avg < 2.0"
    config_enable: enable_1_star
    sort: "rating_avg DESC"
  - id: 2_stars
    class: TwoStarsAlbum
    condition: "rating_avg >= 2.0 AND rating_avg < 3.0"
    config_enable: enable_2_stars
    sort: "rating_avg DESC"
  - id: 3_stars
    class: ThreeStarsAlbum
    condition: "rating_avg >= 3.0"
    config_enable: enable_3_stars
    sort: "rating_avg DESC"
  - id: 4_stars
    class: FourStarsAlbum
    condition: "rating_avg >= 4.0"
    config_enable: enable_4_stars
    sort: "rating_avg DESC"
  - id: 5_stars
    class: FiveStarsAlbum
    condition: "rating_avg >= 5.0"
    config_enable: enable_5_stars
    sort: "rating_avg DESC"
  - id: best_pictures
    class: BestPicturesAlbum
    condition: "rating_avg IS NOT NULL ORDER BY rating_avg DESC LIMIT N (with ties)"
    config_enable: enable_best_pictures
    config_count: best_pictures_count
    sort: "rating_avg DESC"

configs:
  - key: enable_unrated
    type: bool
    default: true
    category: smart_albums
  - key: enable_1_star
    type: bool
    default: true
    category: smart_albums
  - key: enable_2_stars
    type: bool
    default: true
    category: smart_albums
  - key: enable_3_stars
    type: bool
    default: true
    category: smart_albums
  - key: enable_4_stars
    type: bool
    default: true
    category: smart_albums
  - key: enable_5_stars
    type: bool
    default: true
    category: smart_albums
  - key: enable_best_pictures
    type: bool
    default: true
    category: smart_albums
  - key: best_pictures_count
    type: int
    default: 100
    category: smart_albums
    description: Number of top-rated photos to show in Best Pictures (ties included)

sorting:
  - column: rating
    order: COALESCE(rating_avg, -1) DESC
    description: Sort by average rating, highest first, unrated last. COALESCE pattern for cross-database index usage.

cli_commands:
  - id: CLI-009-01
    command: php artisan lychee:sync-rating-avg
    description: Backfill rating_avg from statistics for existing installations
```

## Appendix

### Database Schema Reference

**Migration: `add_rating_avg_to_photos`**
```sql
ALTER TABLE photos
ADD COLUMN rating_avg DECIMAL(5,4) NULL;

CREATE INDEX idx_photos_rating_avg ON photos(rating_avg);
```

**Migration: `add_rating_smart_album_configs`**
```sql
INSERT INTO configs (key, value, type_range, confidentiality, cat, description)
VALUES
  ('enable_unrated', '1', 'bool', 0, 'Smart Albums', 'Enable Unrated smart album'),
  ('enable_1_star', '1', 'bool', 0, 'Smart Albums', 'Enable 1 Star smart album'),
  ('enable_2_stars', '1', 'bool', 0, 'Smart Albums', 'Enable 2 Stars smart album'),
  ('enable_3_stars', '1', 'bool', 0, 'Smart Albums', 'Enable 3+ Stars smart album'),
  ('enable_4_stars', '1', 'bool', 0, 'Smart Albums', 'Enable 4+ Stars smart album'),
  ('enable_5_stars', '1', 'bool', 0, 'Smart Albums', 'Enable 5 Stars smart album'),
  ('enable_best_pictures', '1', 'bool', 0, 'Smart Albums', 'Enable Best Pictures smart album'),
  ('best_pictures_count', '100', 'int', 0, 'Smart Albums', 'Number of photos in Best Pictures album');
```

### Smart Album Condition Summary

| Album | SQL Condition | Overlap |
|-------|--------------|---------|
| Unrated | `rating_avg IS NULL` | None |
| 1★ | `rating_avg >= 1.0 AND rating_avg < 2.0` | None |
| 2★ | `rating_avg >= 2.0 AND rating_avg < 3.0` | None |
| 3★+ | `rating_avg >= 3.0` | Includes 4★+, 5★ |
| 4★+ | `rating_avg >= 4.0` | Includes 5★ |
| 5★ | `rating_avg >= 5.0` | None |
| Best | Top N by rating_avg DESC (with ties) | Varies |

### Rating Sync Logic

When `/Photo::rate` is called:
```php
DB::transaction(function () use ($photo, $new_rating, $old_rating) {
    // Update photo_ratings table (existing logic)
    // Update statistics.rating_sum, rating_count (existing logic)

    // NEW: Sync rating_avg to photos table
    $stats = $photo->statistics;
    $photo->rating_avg = $stats->rating_count > 0
        ? round($stats->rating_sum / $stats->rating_count, 4)
        : null;
    $photo->save();
});
```

### Best Pictures Tie-Inclusion Logic

```php
public function photos(): Builder
{
    $cutoff = $this->config_manager->getValueAsInt('best_pictures_count');

    // Get the rating of the Nth photo
    $nth_rating = Photo::query()
        ->whereNotNull('rating_avg')
        ->orderByDesc('rating_avg')
        ->offset($cutoff - 1)
        ->limit(1)
        ->value('rating_avg');

    // Include all photos with rating >= Nth photo's rating
    return Photo::query()
        ->whereNotNull('rating_avg')
        ->where('rating_avg', '>=', $nth_rating)
        ->orderByDesc('rating_avg');
}
```

---

*Last updated: 2026-01-16*
