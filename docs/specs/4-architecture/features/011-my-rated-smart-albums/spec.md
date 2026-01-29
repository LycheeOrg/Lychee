# Feature 011 – My Rated Pictures Smart Albums

| Field | Value |
|-------|-------|
| Status | Draft |
| Last updated | 2026-01-28 |
| Owners | Agent |
| Linked plan | `docs/specs/4-architecture/features/011-my-rated-smart-albums/plan.md` |
| Linked tasks | `docs/specs/4-architecture/features/011-my-rated-smart-albums/tasks.md` |
| Roadmap entry | Feature 011 |

> Guardrail: This specification is the single normative source of truth for the feature. Track high- and medium-impact questions in [docs/specs/4-architecture/open-questions.md](../../open-questions.md), encode resolved answers directly in the Requirements/NFR/Behaviour/UI/Telemetry sections below (no per-feature `## Clarifications` sections), and use ADRs under `docs/specs/6-decisions/` for architecturally significant clarifications (referencing their IDs from the relevant spec sections).

## Overview

Add two new smart albums that display photos based on the current user's rating activity, not photo ownership. "My Rated Pictures" shows all photos the user has rated (any rating 1-5), while "My Best Pictures" shows the top X highest-rated photos by the user (configurable count, tie-inclusive). These albums filter on the `photo_ratings.user_id` relationship, allowing users to see all photos they've rated regardless of who owns them. Affects: core (models/queries), application (smart albums), configuration.

## Goals

- Enable users to quickly view all photos they have personally rated
- Provide a "best of" view showing their top-rated photos
- Support configurable count for "My Best Pictures" (similar to existing Best Pictures album)
- Maintain consistency with existing smart album patterns (BaseSmartAlbum, enable/disable config)
- Ensure proper security filtering (only show photos user has permission to view)

## Non-Goals

- Modifying existing Best Pictures album (which filters by aggregate rating_avg)
- Adding UI for configuring the "My Best Pictures" count (use existing config system)
- Backend API changes beyond smart album data fetching
- Sorting/filtering by other users' ratings

## Functional Requirements

| ID | Requirement | Success path | Validation path | Failure path | Telemetry & traces | Source |
|----|-------------|--------------|-----------------|--------------|--------------------|--------|
| FR-011-01 | My Rated Pictures album shows all photos rated by current user, sorted by rating DESC then created_at DESC | Query joins photos with photo_ratings where user_id = current user, returns all photos with any rating (1-5), ordered by user's rating descending then photo creation date descending | Query must exclude photos without user's rating entry | Empty result if user has rated no photos | None (per project scope) | User request, Q-011-02 |
| FR-011-02 | My Best Pictures album shows top N highest-rated photos by current user | Query joins photos with photo_ratings where user_id = current user, orders by rating DESC, applies tie-inclusive logic for Nth rating | N is configurable via `my_best_pictures_count` config key | Empty result if user has rated fewer than 1 photo | None | User request, consistency with BestPicturesAlbum |
| FR-011-03 | Both albums require authenticated user and are hidden from guest users | Albums do not appear in smart album list for guest users | is_enabled() returns false for guest users | Albums completely hidden from UI for guests | None | Security requirement |
| FR-011-04 | Albums respect photo visibility/searchability policies | Queries apply PhotoQueryPolicy filters (searchability, sensitivity, NSFW) | Only photos user has permission to view are included | Photos user cannot access are excluded | None | Security consistency |
| FR-011-05 | Albums can be enabled/disabled via configuration | Config keys `enable_my_rated_pictures` and `enable_my_best_pictures` control visibility | Albums hidden from UI when disabled | N/A | None | Consistency with other smart albums |
| FR-011-06 | Albums appear in SmartAlbumType enum and smart album lists | New enum cases MY_RATED_PICTURES and MY_BEST_PICTURES added | Enum values follow naming convention, translations exist | N/A | None | Type system requirement |

## Non-Functional Requirements

| ID | Requirement | Driver | Measurement | Dependencies | Source |
|----|-------------|--------|-------------|--------------|--------|
| NFR-011-01 | Query performance must remain acceptable for users with 1000+ rated photos | User experience, database load | Query execution time ≤ 500ms for 1000 ratings on standard hardware | Proper indexing on photo_ratings (user_id, rating) | Performance consistency |
| NFR-011-02 | Code follows existing SmartAlbum patterns | Maintainability, consistency | Extends BaseSmartAlbum, follows StarredAlbum/BestPicturesAlbum structure | BaseSmartAlbum class | Coding conventions |
| NFR-011-03 | Configuration keys follow naming conventions | Consistency | Uses snake_case, prefixed appropriately | ConfigManager | Coding conventions |
| NFR-011-04 | Translation keys added for both albums | i18n completeness | Keys in lang/en/gallery.php smart_album array | Laravel localization | i18n standard |
| NFR-011-05 | License check for My Best Pictures | Lychee SE feature gating | Requires Lychee SE license (similar to Best Pictures) | VerifyInterface | Business requirement |

## UI / Interaction Mock-ups

Not applicable - smart albums appear in existing smart album list UI with no UI changes required beyond album name display.

## Branch & Scenario Matrix

| Scenario ID | Description / Expected outcome |
|-------------|--------------------------------|
| S-011-01 | Authenticated user views My Rated Pictures → sees all photos they have rated (1-5 stars) |
| S-011-02 | Authenticated user views My Best Pictures → sees top N photos they rated highest, ties included |
| S-011-03 | Guest user browses smart albums → My Rated Pictures not shown in list |
| S-011-04 | Guest user browses smart albums → My Best Pictures not shown in list |
| S-011-05 | User with 0 rated photos views either album → empty result |
| S-011-06 | User rates photo in album A, views My Rated Pictures → photo appears in album |
| S-011-07 | User rates 50 photos all 5 stars, my_best_pictures_count=10 → sees all 50 (ties) |
| S-011-08 | User rates 50 photos: 10×5★, 15×4★, 25×3★, my_best_pictures_count=10 → sees 10×5★ |
| S-011-09 | User rates 50 photos: 8×5★, 15×4★, 25×3★, my_best_pictures_count=10 → sees 8×5★ + 2×4★ (10 total, but tie at 4★ means all 15×4★ shown = 23 total) |
| S-011-10 | Admin disables enable_my_rated_pictures → album hidden from smart album list |
| S-011-11 | User without SE license views My Best Pictures → album hidden (requires license) |
| S-011-12 | User rates photo in private album they don't own → photo not shown in My Rated Pictures (respects visibility policy) |

## Test Strategy

- **Core (Unit):**
  - `MyRatedPicturesAlbumTest`: Test query logic, guest user behavior, empty results
  - `MyBestPicturesAlbumTest`: Test tie-inclusive logic, cutoff calculation, various rating distributions

- **Application (Feature_v2):**
  - `MyRatedSmartAlbumsIntegrationTest`: Test end-to-end album fetching, photo visibility filtering, rating updates
  - Test SE license gating for My Best Pictures
  - Test config enable/disable flags

- **Coverage:**
  - All scenarios S-011-01 through S-011-12 covered by tests
  - Edge cases: 0 ratings, all same rating, tie scenarios

## Interface & Contract Catalogue

### Domain Objects

| ID | Description | Modules |
|----|-------------|---------|
| DO-011-01 | MyRatedPicturesAlbum extends BaseSmartAlbum, implements getInstance(), overrides photos() query | app/SmartAlbums |
| DO-011-02 | MyBestPicturesAlbum extends BaseSmartAlbum, implements getInstance(), overrides getPhotosAttribute() with tie logic | app/SmartAlbums |

### API Routes / Services

No new routes - smart albums use existing album data fetching endpoints.

### CLI Commands / Flags

No CLI changes required.

### Telemetry Events

None (per project scope - no telemetry).

### Fixtures & Sample Data

| ID | Path | Purpose |
|----|------|---------|
| FX-011-01 | Test factories create photo_ratings for test users | Unit/feature tests |

### UI States

| ID | State | Trigger / Expected outcome |
|----|-------|---------------------------|
| UI-011-01 | My Rated Pictures album visible in smart album list | User authenticated, enable_my_rated_pictures=true |
| UI-011-02 | My Best Pictures album visible in smart album list | User authenticated, enable_my_best_pictures=true, SE license active |
| UI-011-03 | Both albums hidden | Config disabled or (for My Best Pictures) no SE license |

## Telemetry & Observability

No telemetry events required per project scope.

## Documentation Deliverables

- Update [docs/specs/4-architecture/roadmap.md](../../roadmap.md) to add Feature 011
- Update [docs/specs/4-architecture/knowledge-map.md](../../knowledge-map.md) if new architectural patterns emerge
- Add feature plan: `docs/specs/4-architecture/features/011-my-rated-smart-albums/plan.md`
- Add task checklist: `docs/specs/4-architecture/features/011-my-rated-smart-albums/tasks.md`

## Fixtures & Sample Data

Test fixtures will use existing PhotoRating factory to create ratings for test users. No new fixture files required.

## Spec DSL

```yaml
domain_objects:
  - id: DO-011-01
    name: MyRatedPicturesAlbum
    type: SmartAlbum
    extends: BaseSmartAlbum
    query_condition: whereHas('ratings', fn($q) => $q->where('user_id', Auth::id()))

  - id: DO-011-02
    name: MyBestPicturesAlbum
    type: SmartAlbum
    extends: BaseSmartAlbum
    query_condition: whereHas('ratings', fn($q) => $q->where('user_id', Auth::id()))
    tie_logic: true
    config_key: my_best_pictures_count

enums:
  - id: ENUM-011-01
    name: SmartAlbumType
    new_cases:
      - MY_RATED_PICTURES: 'my_rated_pictures'
      - MY_BEST_PICTURES: 'my_best_pictures'

config_keys:
  - id: CFG-011-01
    key: enable_my_rated_pictures
    type: boolean
    default: true
    description: Enable My Rated Pictures smart album

  - id: CFG-011-02
    key: enable_my_best_pictures
    type: boolean
    default: true
    description: Enable My Best Pictures smart album (requires SE license)

  - id: CFG-011-03
    key: my_best_pictures_count
    type: integer
    default: 50
    description: Number of top-rated photos to show in My Best Pictures

translation_keys:
  - id: I18N-011-01
    file: lang/en/gallery.php
    path: smart_album.my_rated_pictures
    value: "My Rated Pictures"

  - id: I18N-011-02
    file: lang/en/gallery.php
    path: smart_album.my_best_pictures
    value: "My Best Pictures"

  - id: I18N-011-03
    file: lang/en/all_settings.php
    path: descriptions.enable_my_rated_pictures
    value: "Enable My Rated Pictures smart album."

  - id: I18N-011-04
    file: lang/en/all_settings.php
    path: descriptions.enable_my_best_pictures
    value: "Enable My Best Pictures smart album."

routes: []
cli_commands: []
telemetry_events: []
fixtures: []
```

## Appendix

### Guest User Handling

Both albums check authentication in `is_enabled()` to completely hide from guest users:

```php
// In SmartAlbumType::is_enabled()
self::MY_RATED_PICTURES => Auth::check() && $config_manager->getValueAsBool('enable_my_rated_pictures'),
self::MY_BEST_PICTURES => Auth::check() && $config_manager->getValueAsBool('enable_my_best_pictures') && $this->isLycheeSEActive(),
```

This ensures the albums don't appear in the smart album list at all for guest users.

### Query Pattern

Both albums use `whereHas` to join with `photo_ratings` table:

```php
// My Rated Pictures: any rating
$query->whereHas('ratings', function($q) {
    $q->where('user_id', '=', Auth::id());
});

// My Best Pictures: top N with ties
// Similar to BestPicturesAlbum but filters on user_id in ratings relationship
$cutoff_rating = $this->getCutoffRating($limit); // Nth user rating
$query->whereHas('ratings', function($q) use ($cutoff_rating) {
    $q->where('user_id', '=', Auth::id())
      ->where('rating', '>=', $cutoff_rating);
});
```

### Tie Logic for My Best Pictures

Follows BestPicturesAlbum pattern:
1. Query user's ratings ordered by rating DESC
2. Get Nth rating value as cutoff
3. Include all photos with user rating ≥ cutoff
4. If fewer than N ratings exist, include all

### Relationship to Existing Features

- **Feature 001 (Photo Star Rating):** Depends on PhotoRating model and photo_ratings table
- **Feature 009 (Rating Ordering):** Uses same rating data but different filtering criteria
- **Best Pictures Album:** Similar tie logic but filters by user_id instead of aggregate rating_avg

### Security Considerations

- Albums must respect PhotoQueryPolicy filters (searchability, sensitivity)
- Guest users get empty results (no Auth::id())
- Photos from private albums user cannot access are excluded by existing policy layer
- No information leakage about photos user cannot view

---

*Last updated: 2026-01-28*
