# Open Questions

Track unresolved high- and medium-impact questions here. Remove each row as soon as it is resolved, ensuring the answer is captured first in the governing spec's normative sections and, for high-impact clarifications, in an ADR.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| (none) | — | — | — | — | — | — |

## Question Details

### ~~Q-017-01: Context Menu Scope Behaviour for Photos vs Albums~~ ✅ RESOLVED

**Decision:** Option A — Scope radio hidden for photos, shown for albums
**Rationale:** Most intuitive UX. Photos have no descendants so scope is meaningless — hide it. Albums support "Current level" (rename only selected album titles) and "All descendants" (selected albums + sub-albums recursively). Backend receives `album_ids[]` + `scope` for the album path; `photo_ids[]` only (no scope) for the photo path.
**Updated in spec:** FR-017-07 (scope hidden for photos), FR-017-08 (scope shown for albums), FR-017-09 (contract split by target type)

---

### ~~Q-017-02: No Renamer Rules Configured Edge Case~~ ✅ RESOLVED

**Decision:** Option A — Show the empty preview with an enhanced message
**Rationale:** Simplest approach with no extra API calls. The empty-state message is enhanced: "No titles would change. If you haven't configured renamer rules yet, visit Settings → Renamer Rules." Minimal code change, no additional data dependencies.
**Updated in spec:** FR-017-05 (enhanced empty-state message), UI-017-05

---

### ~~Q-011-02: Default Sort Order for My Rated Pictures Album~~ ✅ RESOLVED

**Decision:** Option A - Sort by rating DESC, then by created_at DESC
**Rationale:** Shows highest-rated photos first, consistent with "favorites" concept. Most intuitive for users wanting to see their best-rated photos at the top.
**Updated in spec:** FR-011-01, query implementation details

---

### ~~Q-011-01: Config Key Naming for My Best Pictures Count~~ ✅ RESOLVED

**Decision:** Option A - Separate config key `my_best_pictures_count`
**Rationale:** Allows independent configuration. Users might want different counts for overall best pictures vs personal favorites. Clearer semantics with each album having its own setting.
**Updated in spec:** CFG-011-03, DO-011-02 implementation

---

### Q-011-01: Config Key Naming for My Best Pictures Count

**Question:** Should "My Best Pictures" use a separate config key from "Best Pictures" count, or share the same `best_pictures_count` config?

- **Option A (Recommended):** Separate config key `my_best_pictures_count`
  - Allows independent configuration of the two albums
  - Users might want different counts (e.g., top 50 overall vs top 20 personal favorites)
  - Clearer semantics: each album has its own count setting
  - Requires new config entry in database/config system
  
- **Option B:** Share existing `best_pictures_count` config
  - Simpler configuration (one less setting)
  - Both albums show same count
  - Less flexible for users
  - No code changes to config system needed

**Pros/Cons:**
- **A:** More flexible, clearer intent; adds one config key
- **B:** Simpler, less config; less flexible, potentially confusing

**Impact:** MEDIUM - affects config system, admin UI (if implemented), user experience

---

### Q-011-02: Default Sort Order for My Rated Pictures Album

**Question:** What should be the default sort order for "My Rated Pictures" album?

- **Option A (Recommended):** Sort by rating DESC, then by created_at DESC
  - Shows highest-rated photos first
  - Consistent with "best pictures" concept
  - Users likely want to see their favorites at top
  
- **Option B:** Sort by created_at DESC (recently rated first)
  - Shows most recently rated photos first
  - Consistent with "Recent" album pattern
  - Good for reviewing recent rating activity
  
- **Option C:** Use default photo sorting (from user preferences)
  - Respects user's chosen sort order
  - Most flexible
  - Might not match user expectations for a "rated" album

**Pros/Cons:**
- **A:** Most intuitive for "favorites" view; opinionated
- **B:** Good for activity tracking; less relevant for "best" concept
- **C:** Most flexible; potentially confusing

**Impact:** MEDIUM - affects user experience, query implementation, consistency with other smart albums

---

### ~~Q-010-12: TLS/StartTLS Configuration~~ ✅ RESOLVED

**Decision:** Option A - Single `LDAP_USE_TLS` flag, protocol determined by port
**Rationale:** Simpler configuration with fewer env vars. Protocol auto-detected: port 636 = LDAPS, port 389 = StartTLS. Documentation in .env.example clarifies both scenarios.
**Updated in spec:** ENV-010-13, I10 documentation deliverables

---

### ~~Q-010-11: Authentication Flow Sequence~~ ✅ RESOLVED

**Decision:** Option A - Search-first pattern (username → search → DN → bind → groups)
**Rationale:** Flexible approach supporting diverse LDAP schemas. Flow: 1) User submits username+password, 2) Search LDAP using `LDAP_USER_FILTER`, 3) Get userDn from search result, 4) Bind with userDn+password, 5) Query groups using userDn, 6) Retrieve user attributes.
**Updated in spec:** FR-010-01, I2 LdapService `authenticate()` method, I4 `getUserGroups()` signature

---

### ~~Q-010-10: Testing Strategy~~ ✅ RESOLVED

**Decision:** Option A - LdapRecord testing utilities for unit tests, skip Docker integration tests
**Rationale:** Fast unit tests using LdapRecord's `DirectoryEmulator` or test helpers. Mock LDAP responses at service boundary. Docker integration tests deferred to future enhancement.
**Updated in spec:** I2-I7 test implementation, no Docker CI configuration needed

---

### ~~Q-010-09: Connection Pooling Implementation~~ ✅ RESOLVED

**Decision:** Option A - Configure LdapRecord's built-in connection management
**Rationale:** Leverage existing, tested library features. Configure timeouts and connection caching via LdapRecord config. No custom pooling code needed.
**Updated in spec:** I2 implementation approach, NFR-010-04

---

### ~~Q-010-08: LdapConfiguration DTO Purpose~~ ✅ RESOLVED

**Decision:** Option A - LdapConfiguration validates/transforms .env values
**Rationale:** Clean validation layer providing type-safe value object. Single source of truth: .env → LdapConfiguration::fromEnv() validates → values passed to LdapRecord config. Prevents invalid config, provides testability.
**Updated in spec:** I1 LdapConfiguration DTO implementation, validation strategy

---

### ~~Q-010-07: LdapRecord Integration Strategy~~ ✅ RESOLVED

**Decision:** Option A - Service layer wrapping LdapRecord
**Rationale:** Better separation of concerns and testability. `LdapService` acts as facade/adapter over LdapRecord's Connection and query builder. Business logic abstracted from LDAP library details. Easier to test (mock LdapService interface) and swap libraries if needed.
**Updated in spec:** I2-I5 architecture, LdapService design as wrapper pattern

---

### ~~Q-010-07: LdapRecord Integration Strategy~~ (ARCHIVED - moved above)

**Question:** How should `App\Services\Auth\LdapService` integrate with LdapRecord?

- **Option A (Recommended):** Service layer wrapping LdapRecord
  - Create `LdapService` as a facade/adapter over LdapRecord's `Connection`, `Model`, and query builder
  - Business logic lives in `LdapService`, LDAP library details abstracted
  - Easier testing (mock LdapService interface)
  - Easier to swap LDAP libraries in future if needed
  
- **Option B:** Direct LdapRecord usage throughout codebase
  - AuthController and Actions call LdapRecord directly
  - Less abstraction, fewer layers
  - Tighter coupling to LdapRecord API
  - Testing requires mocking LdapRecord classes

**Pros/Cons:**
- **A:** Better separation of concerns, testability; adds abstraction layer
- **B:** Simpler, fewer files; harder to test, tight coupling

**Impact:** HIGH - affects architecture, testing strategy, and implementation complexity across all increments (I2-I5)

---

### Q-010-08: LdapConfiguration DTO Purpose

**Question:** What is the relationship between `App\DTO\LdapConfiguration` and LdapRecord's `config/ldap.php`?

- **Option A (Recommended):** LdapConfiguration validates/transforms .env values
  - `LdapConfiguration` is a validated value object created from .env variables
  - Values are passed to LdapRecord's config at runtime
  - Single source of truth: .env → LdapConfiguration → LdapRecord config
  - Validation happens in DTO constructor
  
- **Option B:** LdapConfiguration duplicates LdapRecord config
  - Separate parallel configuration system
  - Risk of config drift between two systems
  - More complex synchronization

**Pros/Cons:**
- **A:** Clean validation layer, no duplication; .env values must be transformed
- **B:** More flexible; potential sync issues, redundant config

**Impact:** MEDIUM - affects I1 configuration setup and validation strategy

---

### Q-010-09: Connection Pooling Implementation

**Question:** What does "implement connection pooling logic" mean given LdapRecord already manages connections?

- **Option A (Recommended):** Configure LdapRecord's built-in connection management
  - Use LdapRecord's connection caching and reuse features
  - Configure timeouts via LdapRecord config
  - No custom pooling code needed
  
- **Option B:** Build custom connection pool
  - Implement connection reuse, timeout, retry logic manually
  - More control over pool behavior
  - Significant additional complexity

**Pros/Cons:**
- **A:** Leverage existing, tested library feature; less code
- **B:** Full control; reinventing the wheel, higher maintenance

**Impact:** MEDIUM - affects I2 implementation complexity and testing

---

### Q-010-10: Testing Strategy for LDAP Operations

**Question:** How should LDAP server responses be mocked for deterministic testing?

- **Option A (Recommended):** LdapRecord's testing utilities for unit tests + optional Docker for integration
  - Use LdapRecord's `DirectoryEmulator` or test helpers for unit tests
  - Mock LDAP responses at service boundary
  - Optional: `rroemhild/test-openldap` Docker image for integration tests
  
- **Option B:** Docker LDAP server for all tests
  - Realistic LDAP server in test environment
  - Slower test execution
  - More complex CI setup
  
- **Option C:** PHP mock/stub classes only
  - Fastest execution
  - May not catch library integration issues
  - No LdapRecord-specific testing utilities

**Pros/Cons:**
- **A:** Fast unit tests + realistic integration tests; best of both worlds
- **B:** Most realistic; slowest, most complex
- **C:** Simplest, fastest; least realistic

**Impact:** MEDIUM - affects I2-I7 test implementation and CI configuration

---

### Q-010-11: Authentication Flow Sequence

**Question:** What is the complete flow from username to group membership, including how userDn is obtained?

Need to clarify the sequence:
1. User submits username + password
2. How do we get the userDn? 
   - **Option A:** Search for user first (`LDAP_USER_FILTER`) → get DN → bind with DN + password
   - **Option B:** Construct DN from username (e.g., `uid={username},ou=people,dc=example,dc=com`) → bind directly
3. After successful bind, query groups using userDn
4. Retrieve user attributes
5. Map groups to roles

**Recommended:** Option A (search-first pattern) for flexibility with diverse LDAP schemas

**Impact:** HIGH - affects I2-I4 implementation, especially `bind()` and `getUserGroups()` method signatures

---

### Q-010-12: TLS/StartTLS Configuration Clarity

**Question:** Does `LDAP_USE_TLS=true` cover both LDAPS (port 636) and StartTLS (port 389), or do we need separate configuration?

- **Option A (Recommended):** Single `LDAP_USE_TLS` flag, protocol determined by port
  - `LDAP_USE_TLS=true` + `LDAP_PORT=636` → LDAPS (SSL/TLS from start)
  - `LDAP_USE_TLS=true` + `LDAP_PORT=389` → StartTLS (upgrade connection)
  - `LDAP_USE_TLS=false` → plaintext (dev only)
  - Document both scenarios in .env.example
  
- **Option B:** Separate flags for LDAPS and StartTLS
  - `LDAP_USE_LDAPS=true` for port 636
  - `LDAP_USE_STARTTLS=true` for port 389
  - More explicit configuration
  - More environment variables

**Pros/Cons:**
- **A:** Simpler configuration, fewer env vars; requires clear documentation
- **B:** More explicit; more complex, more env vars

**Impact:** MEDIUM - affects I1 configuration, I2 TLS implementation, and documentation

---

### ~~Q-010-06: Configuration Method~~ ✅ RESOLVED

**Decision:** Option A - Environment variables only
**Rationale:** LDAP is an expert/power-user setting; .env configuration is appropriate and avoids database complexity.
**Updated in spec:** All configuration options use .env variables, NFR-010-01

---

### ~~Q-010-05: Password Storage~~ ✅ RESOLVED

**Decision:** Option A - Don't store LDAP passwords
**Rationale:** Most secure approach; authenticate only against LDAP server without password duplication.
**Updated in spec:** FR-010-01, authentication flow, security model

---

### ~~Q-010-04: User Attribute Mapping~~ ✅ RESOLVED

**Decision:** Option C - Defaults with optional override via .env
**Rationale:** Provides sensible defaults (uid→username, mail→email, displayName→display_name) with .env configuration for LDAP schemas that differ.
**Updated in spec:** FR-010-02, attribute mapping configuration

---

### ~~Q-010-03: LDAP Group Mapping~~ ✅ RESOLVED

**Decision:** Option B - Map LDAP groups to Lychee roles (admin/user)
**Rationale:** Allows admin role assignment via LDAP groups; provides automatic role sync without complex user group management.
**Updated in spec:** FR-010-03, role mapping configuration

---

### ~~Q-010-02: User Provisioning~~ ✅ RESOLVED

**Decision:** Option C - User provisioning configurable via .env
**Rationale:** Flexibility for different deployment scenarios; allows auto-create or pre-existing-only mode via configuration.
**Updated in spec:** FR-010-04, user provisioning behavior

---

### ~~Q-010-01: LDAP Authentication Method~~ ✅ RESOLVED

**Decision:** Option C - Both basic auth and LDAP independently configurable via .env
**Rationale:** Maximum flexibility; allows deployments to use LDAP-only, basic-only, or both. LDAP enablement controlled by .env variables.
**Updated in spec:** FR-010-05, authentication method selection

---

### ~~Q-009-06: NULLS LAST Cross-Database Strategy~~ ✅ RESOLVED

**Decision:** Simple indexed ORDER BY with COALESCE pattern for fastest performance
**Rationale:** User specified "fastest ordering possible with indexing." Using `COALESCE(rating_avg, -1) DESC` allows the query to use the index on `rating_avg` efficiently across all databases. Since ratings are always positive (1-5), -1 as sentinel value is safe and pushes NULLs to the end.
**Updated in spec:** FR-009-02, sorting strategy, SortingDecorator implementation

---

### ~~Q-009-01: Average Rating Storage Strategy~~ ✅ RESOLVED

**Decision:** Option B - Add denormalized rating_avg column to photos table
**Rationale:** Fast indexed sorting with simple ORDER BY. Application logic will keep it in sync when ratings are updated (same transaction as rating_sum/rating_count updates).
**Updated in spec:** FR-009-01, DO-009-01, migration strategy

---

### ~~Q-009-02: Rating Smart Album Threshold Logic~~ ✅ RESOLVED

**Decision:** Option C - Hybrid (threshold for 3★+, exact for 1★-2★)
**Rationale:** Matches user's explicit statement that "3_stars album will contain all photos rated 3 stars or above." Low ratings (1★, 2★) use exact buckets so photos only appear in one album; high ratings (3★+) use threshold for cumulative view.
**Updated in spec:** FR-009-03 through FR-009-08, smart album filtering logic

---

### ~~Q-009-03: Best Pictures Cutoff Behavior~~ ✅ RESOLVED

**Decision:** Option B - Top N by rating, include ties
**Rationale:** Fair behavior that doesn't arbitrarily exclude photos with the same rating as the Nth photo. May show more than N photos if ties exist, but ensures no photo is unfairly excluded.
**Updated in spec:** FR-009-09, Best Pictures smart album logic

---

### ~~Q-009-04: Smart Album Sorting Default~~ ✅ RESOLVED

**Decision:** Custom - Rating smart albums and Best Pictures sorted by rating DESC
**Rationale:** Shows highest-rated photos first, which is the natural expectation for rating-based albums.
**Updated in spec:** FR-009-10, NFR-009-03

---

### ~~Q-008-01: User Preference Storage Location~~ ✅ RESOLVED

**Decision:** Option A - New column in users table
**Rationale:** Follows existing Lychee pattern (user attributes in users table), simple implementation with single query, no new tables needed.
**Updated in spec:** FR-008-02, COL-008-01, migration strategy

---

### ~~Q-008-02: Smart Albums in Tabbed View~~ ✅ RESOLVED

**Decision:** Option D - Show above tabs (outside tab context)
**Rationale:** Smart albums span all content (photos from both owned and shared albums), so they should be displayed above the tab bar and remain always visible regardless of selected tab.
**Updated in spec:** UI mockups, FR-008-06, FR-008-07

---

### ~~Q-008-03: Tab Visibility When Empty~~ ✅ RESOLVED

**Decision:** Option A - Hide empty tabs
**Rationale:** Cleaner UX - if "Shared with Me" has no albums, don't show tab bar at all (behave like SHOW mode). Simpler for users with no shared albums.
**Updated in spec:** S-008-08, UI-008-02

---

---

### ~~Q-007-01: Pagination Strategy (Offset vs Cursor) and Page Size Configuration~~ ✅ RESOLVED

**Decision:** Option A - Offset-based pagination with config table page size
**Rationale:** Simple Laravel pagination pattern with standard LIMIT/OFFSET, easy navigation to specific pages, admin-configurable page sizes via config table. Performance acceptable for expected album sizes.
**Updated in spec:** FR-007-01 through FR-007-06, NFR-007-01, NFR-007-05, DO-007-01

---

### ~~Q-007-02: API Endpoint Design (New Endpoints vs Modify Existing)~~ ✅ RESOLVED

**Decision:** Option B - New paginated endpoints (`/Album/{id}/head`, `/Album/{id}/albums`, `/Album/{id}/photos`)
**Rationale:** Clear separation of concerns, existing `/Album` endpoint unchanged for backward compatibility (avoiding test changes), consistent response structure per endpoint. Code duplication acceptable to minimize refactoring risk.
**Updated in spec:** FR-007-01, FR-007-02, FR-007-03, FR-007-12, NFR-007-04, NFR-007-06, API-007-01 through API-007-05

---

### ~~Q-007-03: Frontend Loading Strategy (Load-More vs Page Navigation)~~ ✅ RESOLVED

**Decision:** Configurable with infinite scroll as default
**Rationale:** User specified configurable UI modes: "infinite_scroll" (default), "load_more_button", "page_navigation". Infinite scroll provides smoothest UX for photo galleries. First page always loaded automatically, subsequent pages on demand based on UI mode.
**Updated in spec:** FR-007-07, FR-007-08, FR-007-09, FR-007-10, DO-007-02, UI mockups

---

### ~~Q-007-04: Config Key Naming and Default Values~~ ✅ RESOLVED

**Decision:** Option C - Multiple granular configs
**Rationale:** User specified: `albums_per_page` (default 30), `photos_per_page` (default 100), Flexible tuning for different resource types with appropriate defaults based on typical usage patterns.
**Updated in spec:** FR-007-06, NFR-007-05, DO-007-01

---

### ~~Q-007-05: Refactoring Scope (Extract Album/Photo Fetching Logic)~~ ✅ RESOLVED

**Decision:** Option B - Repository pattern methods, code duplication acceptable
**Rationale:** User directive to avoid extensive refactoring, prioritize backward compatibility and minimal test changes. New endpoints can duplicate logic from existing implementation. Repository pattern methods for data access without extracting to separate service classes.
**Updated in spec:** NFR-007-06, Goals section, Non-Goals section

---

### ~~Q-007-06: Backward Compatibility Strategy for Existing Clients~~ ✅ RESOLVED

**Decision:** New endpoints default page=1, existing `/Album` endpoint unchanged
**Rationale:** User specified creating new endpoints only. Legacy `/Album?album_id=X` endpoint remains unchanged returning full data. New endpoints (`/Album/{id}/albums`, `/Album/{id}/photos`) default to page 1 if `?page=` parameter absent (not "return all").
**Updated in spec:** FR-007-11, FR-007-12, API-007-02, API-007-03, API-007-04

---

### ~~Q-006-01: Filter UI Control Design and Interaction Pattern~~ ✅ RESOLVED

**Decision:** Option D - Hover star list with minimum threshold filtering and toggle-off
**Rationale:** User specified custom interaction: Display 5 hoverable stars. Empty stars = no filtering. Click on star N = show photos with rating ≥ N (minimum threshold). Click same star again = remove filtering. Combines visual clarity of inline stars with flexible threshold filtering.
**Updated in spec:** FR-006-01, FR-006-02, FR-006-03, UI mockup section

---

### ~~Q-006-02: Filter Behavior for Unrated Photos~~ ✅ RESOLVED

**Decision:** Addressed by Q-006-01 decision
**Rationale:** Minimum threshold filtering (≥ N stars) inherently excludes unrated photos (which have no rating value). Empty stars (no filter) shows all photos including unrated.
**Updated in spec:** FR-006-02, filtering logic section

---

### ~~Q-006-03: Filter State Persistence Strategy~~ ✅ RESOLVED

**Decision:** Custom - State store persistence (like NSFW visibility)
**Rationale:** User specified to keep selection in state store, similar to existing NSFW visibility pattern. State persists during session but managed by Pinia store, not localStorage (follows existing Lychee patterns for view state).
**Updated in spec:** FR-006-04, NFR-006-01

---

### ~~Q-006-04: Multi-Rating Filter Support (AND vs OR)~~ ✅ RESOLVED

**Decision:** Option C - Range filter (minimum threshold) as explained in Q-006-01
**Rationale:** User clarified in Q-006-01 that clicking star N shows photos with rating ≥ N (3+ stars shows 3, 4, 5 star photos). Simple single-selection UI with flexible filtering capability.
**Updated in spec:** FR-006-01, FR-006-02, filtering algorithm section

---

### ~~Q-005-01: List View Layout Structure and Information Display~~ ✅ RESOLVED

**Decision:** Option A - Windows Details View Pattern
**Rationale:** Familiar file manager pattern with horizontal row layout: `[Thumb 64px] [Album Name - Full] [X photos] [Y sub-albums]`. Scannable, information-dense, shows full untruncated album names.
**Updated in spec:** FR-005-01, FR-005-02, UI mockup section

---

### ~~Q-005-02: Toggle Control Placement and Styling~~ ✅ RESOLVED

**Decision:** Custom - AlbumHero.vue icon row (same line as statistics/download toggles)
**Rationale:** User specified placement on the same line as the statistics and download toggle buttons in AlbumHero.vue (line 33, flex-row-reverse container). Follows existing icon pattern with px-3 spacing and hover animations.
**Updated in spec:** FR-005-03, UI implementation section

---

### ~~Q-005-03: View Preference Persistence Strategy~~ ✅ RESOLVED

**Decision:** Option B - LocalStorage/session-only (no backend)
**Rationale:** Simple implementation, no backend changes needed, fast toggle response. User preference stored in browser localStorage per-device.
**Updated in spec:** FR-005-04, NFR-005-01

---

### ~~Q-003-09: Multi-user Cover Selection Strategy for computed_cover_id~~ ✅ RESOLVED

**Decision:** Option D - Store dual cover IDs with privilege-based selection (`auto_cover_id_max_privilege` and `auto_cover_id_least_privilege`)
**Rationale:** Balances performance (pre-computation) with security (no photo leakage). Two cover IDs stored per album: one for admin/owner view (max privilege), one for public view (least privilege). Display logic selects appropriate cover based on user permissions at query time (simple column read, no subquery). Simple schema (2 columns vs. per-user table), guaranteed safe (least-privilege cover never leaks private photos), good UX (admin/owner sees best possible cover).
**Updated in spec:** FR-003-01, FR-003-02, FR-003-04, FR-003-07, NFR-003-05, DO-003-03, DO-003-04, Migration Strategy, Cover Selection Logic appendix
**ADR:** ADR-0003-album-computed-fields-precomputation.md (to be updated with Q-003-09 resolution)

---

### ~~Q-003-01: Recomputation Job Queue Priority~~ ✅ RESOLVED

**Decision:** Option A - Use default queue, rely on worker scaling
**Rationale:** Simpler configuration, standard Laravel pattern, natural backpressure signaling. Operators scale worker count to meet 30-second consistency target.
**Updated in spec:** FR-003-02, JOB-003-01

---

### ~~Q-003-02: Backfill Execution Strategy During Migration~~ ✅ RESOLVED

**Decision:** Option A - Manual trigger after migration (with `lychee:` prefix requirement)
**Rationale:** Operator controls timing during maintenance window, migration completes quickly, aligns with dual-read fallback pattern. All Lychee commands use `lychee:` namespace.
**Updated in spec:** FR-003-06, CLI-003-01, Migration Strategy appendix
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-03: Concurrent Album Mutation Deduplication~~ ✅ RESOLVED

**Decision:** Option A - Laravel WithoutOverlapping middleware
**Rationale:** Built-in Laravel feature (same as Feature 002 Q-002-03), prevents wasted work, automatic lock release, simple implementation.
**Updated in spec:** FR-003-02, JOB-003-01
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-04: Cover Selection Race Condition Handling~~ ✅ RESOLVED

**Decision:** Option A - Foreign key ON DELETE SET NULL (already in spec)
**Rationale:** Database handles automatically, simple, eventual consistency. Photo deletion events trigger recomputation for parent albums.
**Updated in spec:** FR-003-02 (added photo deletion event trigger), Migration Strategy appendix (FK constraint confirmed)

---

### ~~Q-003-05: Propagation Chain Failure Handling~~ ✅ RESOLVED

**Decision:** Option A - Stop propagation, log error, manual recovery
**Rationale:** Prevents cascading errors, clear failure boundary, operator can investigate root cause before retrying via `lychee:recompute-album-stats`.
**Updated in spec:** FR-003-02, CLI-003-02
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-003-06: Soft-Deleted Photo Exclusion from Computations~~ ✅ RESOLVED

**Decision:** N/A - Lychee does not use soft deletes
**Rationale:** Per user clarification, Lychee does not implement soft delete pattern for photos. Hard deletes only.
**Updated in spec:** FR-003-02 (removed soft-delete references)

---

### ~~Q-003-07: NULL taken_at Handling in Min/Max Calculations~~ ✅ RESOLVED

**Decision:** Option A - Ignore NULL taken_at, use SQL MIN/MAX directly
**Rationale:** Mirrors existing AlbumBuilder.php behavior (lines 111, 125). SQL MIN/MAX ignores NULLs by default. Semantically correct (taken_at unknown = exclude from range).
**Updated in spec:** FR-003-02 validation path

---

### ~~Q-003-08: Migration Rollback Strategy for Multi-Phase Deployment~~ ✅ RESOLVED

**Decision:** Option B - Full rollback with down() migration
**Rationale:** Clean schema restoration, simple one-command rollback. Trade-off: data loss if backfill ran, but values can be regenerated. Critical constraint: do NOT rollback after Phase 4 cleanup.
**Updated in spec:** FR-003-06, Migration Strategy appendix (new Rollback Strategy section)
**ADR:** ADR-0003-album-computed-fields-precomputation.md

---

### ~~Q-002-01: Worker Auto-Restart Queue Priority~~ ✅ RESOLVED

**Decision:** Option A - Support multiple queue workers with priority via QUEUE_NAMES environment variable
**Rationale:** Allows time-sensitive jobs to be prioritized, standard Laravel pattern, operator flexibility.
**Updated in spec:** FR-002-02, DO-002-02, CLI-002-01, Spec DSL, Queue Connection Configuration appendix

---

### ~~Q-002-02: Worker Max-Time Configurability~~ ✅ RESOLVED

**Decision:** Option A - Configurable with sensible default via WORKER_MAX_TIME environment variable
**Rationale:** Operators can tune for their workload, no code changes needed to adjust restart interval.
**Updated in spec:** FR-002-02, DO-002-03, CLI-002-01, Spec DSL, Queue Connection Configuration appendix

---

### ~~Q-002-03: Job Deduplication for Concurrent Mutations~~ ✅ RESOLVED

**Decision:** Option A - Laravel job middleware with deduplication using WithoutOverlapping
**Rationale:** Built-in Laravel feature, prevents wasted work, automatic lock release.
**Updated in spec:** NFR-002-05, Documentation Deliverables

---

### ~~Q-002-04: Worker Healthcheck Failure Behavior~~ ✅ RESOLVED

**Decision:** Option B - Healthcheck tracks restart count, fail after 10 restarts in 5 minutes
**Rationale:** Orchestrator can restart container if worker is fundamentally broken, prevents infinite crash loops.
**Updated in spec:** FR-002-05

---

### ~~Q001-07: Statistics Record Creation Strategy~~ ✅ RESOLVED

**Decision:** Option A - firstOrCreate in transaction
**Rationale:** Atomic operation with no race conditions, Laravel handles duplicate creation attempts automatically, simple implementation.
**Updated in spec:** Implementation plan I5

---

### ~~Q001-08: Transaction Rollback Error Handling~~ ✅ RESOLVED

**Decision:** Option B - 409 Conflict for transaction errors
**Rationale:** More semantic HTTP status, indicates temporary issue that suggests retry, clearer to frontend.
**Updated in spec:** Implementation plan I5, I10

---

### ~~Q001-09: N+1 Query Performance for user_rating~~ ✅ RESOLVED

**Decision:** Option A - Eager load with closure in controller
**Rationale:** Standard Laravel pattern, single additional query for all photos, no global scope side effects.
**Updated in spec:** Implementation plan I6

---

### ~~Q001-10: Concurrent Update Debouncing (Rapid Clicks)~~ ✅ RESOLVED

**Decision:** Option A - Disable stars during API call
**Rationale:** Simple implementation, prevents concurrent requests, clear visual feedback with loading state.
**Updated in spec:** Implementation plan I8, I9a, I9c

---

### ~~Q001-11: Metrics Disabled Behavior (Can Still Rate?)~~ ✅ RESOLVED

**Decision:** Option C - Admin setting controls independently
**Rationale:** Granular control allows enabling rating without showing aggregates, future-proof configuration.
**Updated in spec:** New config setting needed (separate `ratings_enabled` from `metrics_enabled`)

---

### ~~Q001-12: Rating Display When Metrics Disabled~~ ✅ RESOLVED

**Decision:** Option B - Hide all rating data when metrics disabled
**Rationale:** Fully consistent with metrics disabled setting, simplest implementation, respects admin preference.
**Updated in spec:** UI components conditional rendering

---

### ~~Q001-13: Half-Star Display for Fractional Averages~~ ✅ RESOLVED

**Decision:** Option B - Half-star display using PrimeVue icons
**Rationale:** PrimeVue provides pi-star, pi-star-fill, pi-star-half, pi-star-half-fill icons. More precise visual representation, common rating pattern.
**Updated in spec:** UI mockups, component implementation uses PrimeVue star icons

---

### ~~Q001-14: Overlay Persistence on Active Interaction~~ ✅ RESOLVED

**Decision:** Option A - Persist while loading, then restart auto-hide timer
**Rationale:** User sees confirmation (success toast + updated rating), natural interaction flow.
**Updated in spec:** Implementation plan I9c, PhotoRatingOverlay behavior

---

### ~~Q001-15: Rating Tooltip/Label Clarity~~ ✅ RESOLVED

**Decision:** Option C - No labels/tooltips (stars are self-evident)
**Rationale:** Cleanest UI, stars are universal rating symbol, keeps overlays compact.
**Updated in spec:** UI components (no tooltip implementation needed)

---

### ~~Q001-16: Accessibility (Keyboard Navigation, ARIA)~~ ✅ RESOLVED

**Decision:** Option C - Defer to post-MVP
**Rationale:** Ship faster with basic implementation, gather user feedback first, can enhance accessibility later.
**Updated in spec:** Out of scope (deferred enhancement)

---

### ~~Q001-17: Optimistic UI Updates vs Server Confirmation~~ ✅ RESOLVED

**Decision:** Option A - Wait for server confirmation
**Rationale:** Always shows accurate server state, clear error handling, no phantom updates.
**Updated in spec:** Implementation plan I8, I9a, I9c (loading state pattern)

---

### ~~Q001-18: Rating Count Threshold for Display~~ ✅ RESOLVED

**Decision:** Option A - Always show rating, regardless of count
**Rationale:** Transparent, simpler logic, users can judge significance from count displayed.
**Updated in spec:** UI components (no threshold logic needed)

---

### ~~Q001-19: Telemetry Event Granularity~~ ✅ RESOLVED

**Decision:** No telemetry events / analytics
**Rationale:** Feature does not include telemetry or analytics tracking.
**Updated in spec:** Remove telemetry events from FR-001-01, FR-001-02, FR-001-03

---

### ~~Q001-20: Rating Analytics/Trending Features~~ ✅ RESOLVED

**Decision:** Option B - Implement minimally for current scope
**Rationale:** Follows YAGNI principle, simpler initial implementation, faster to ship.
**Updated in spec:** Out of scope (no future analytics preparation)

---

### ~~Q001-21: Album Aggregate Rating Display~~ ✅ RESOLVED

**Decision:** Option A - Defer to future feature
**Rationale:** Keeps current feature focused, can design properly later with user feedback on photo ratings.
**Updated in spec:** Out of scope, potential future Feature 00X

---

### ~~Q001-22: Rating Export in Photo Backup~~ ✅ RESOLVED

**Decision:** Option C - No export (ratings are ephemeral/server-side only)
**Rationale:** Simpler export logic, smaller export files.
**Updated in spec:** Out of scope (no export functionality)

---

### ~~Q001-23: Rating Notification to Photo Owner~~ ✅ RESOLVED

**Decision:** Option A - Defer to future feature (notifications system)
**Rationale:** Keeps feature scope focused, requires notifications infrastructure that may not exist yet.
**Updated in spec:** Out of scope (deferred to future notifications feature)

---

### ~~Q001-24: Statistics Recalculation Artisan Command~~ ✅ RESOLVED

**Decision:** Option B - No command, rely on transaction integrity
**Rationale:** Trust atomic transactions to maintain consistency, simpler implementation.
**Updated in spec:** Out of scope (no artisan command)

---

### ~~Q001-25: Migration Strategy for Existing Installations~~ ✅ RESOLVED

**Decision:** Option A - Migration adds columns with defaults, no backfill
**Rationale:** Clean state (accurate: no ratings yet), fast migration, no assumptions about historical data.
**Updated in spec:** Implementation plan I1 (migrations with default values)

---

### ~~Q001-05: Authorization Model for Rating~~ ✅ RESOLVED

**Decision:** Option B - Read access (anyone who can view can rate)
**Rationale:** Follows standard rating system patterns. Rating is a lightweight engagement action similar to favoriting, not a privileged edit operation. Makes ratings more accessible and useful.
**Updated in spec:** FR-001-01, NFR-001-04

---

### ~~Q001-06: Rating Removal HTTP Status Code~~ ✅ RESOLVED

**Decision:** 200 OK (idempotent behavior)
**Rationale:** Removing a non-existent rating is a no-op and should return success (200 OK) rather than 404 error. This makes the endpoint idempotent and simpler to use.
**Updated in spec:** FR-001-02

---

### ~~Q001-01: Full-size Photo Overlay Positioning~~ ✅ RESOLVED

**Decision:** Option A - Bottom-center
**Rationale:** Centered position is more discoverable and doesn't compete with Dock buttons. Symmetrical with metadata overlay below.
**Updated in spec:** FR-001-10, UI mockup section 2, implementation plan I9c/I9d

---

### ~~Q001-02: Auto-hide Timer Duration~~ ✅ RESOLVED

**Decision:** Option A - 3 seconds
**Rationale:** Standard UX pattern, balanced duration (not too fast, not too slow).
**Updated in spec:** FR-001-10, UI mockup section 2, implementation plan I9c

---

### ~~Q001-03: Rating Removal Button Placement~~ ✅ RESOLVED

**Decision:** Option A - Inline [0] button
**Rationale:** Consistent button pattern, simple implementation, shown as "×" or "Remove" for clarity.
**Updated in spec:** FR-001-09, UI mockup section 1, implementation plan I9a

---

### ~~Q001-04: Overlay Visibility on Mobile Devices~~ ✅ RESOLVED

**Decision:** Option A - Details drawer only on mobile
**Rationale:** Follows existing Lychee pattern (overlays are desktop-only), simple and consistent experience.
**Updated in spec:** FR-001-09, FR-001-10, UI mockup sections 1-2, implementation plan I9a/I9c

---

### ~~Q001-01: Full-size Photo Overlay Positioning~~ (ARCHIVED)

**Context:** When hovering over the lower area of a full-size photo, the rating overlay can be positioned in different locations. The spec currently presents two options.

**Question:** Which positioning approach should we use for the full-size photo rating overlay?

**Options (ordered by preference):**

**Option A: Bottom-center (Recommended)**
- **Position:** Horizontally centered, positioned above the metadata overlay (title/EXIF)
- **Layout:** `★★★★☆ 4.2 (15) Your rating: ★★★★☆ [0][1][2][3][4][5]`
- **Pros:**
  - Centered position is intuitive and balanced
  - Doesn't compete with Dock buttons for space
  - More visible and discoverable
  - Symmetrical with metadata overlay below it
- **Cons:**
  - May obstruct central portion of photo
  - Wider horizontal space required

**Option B: Bottom-right (near Dock buttons)**
- **Position:** Bottom-right corner, adjacent to existing Dock action buttons
- **Layout:** Compact vertical or horizontal near Dock
- **Pros:**
  - Groups with other photo actions (Dock buttons)
  - Consistent with action button placement pattern
  - Less obstruction of photo center
- **Cons:**
  - May crowd the Dock button area
  - Less discoverable (user might not look at corner)
  - Asymmetrical with metadata overlay (which is bottom-left)

**Impact:** Medium - affects UX discoverability and visual balance, but either option is functional.

---

### Q001-02: Auto-hide Timer Duration

**Context:** The full-size photo rating overlay auto-hides after a period of inactivity to avoid obstructing the photo view.

**Question:** What duration should the auto-hide timer be set to?

**Options (ordered by preference):**

**Option A: 3 seconds (Recommended)**
- **Duration:** Overlay fades out after 3 seconds of no mouse movement
- **Pros:**
  - Short enough to not be annoying
  - Long enough for user to read and interact
  - Common UX pattern for transient overlays
- **Cons:**
  - May feel rushed for slower users
  - Might hide before user finishes reading

**Option B: 5 seconds**
- **Duration:** Overlay fades out after 5 seconds of no mouse movement
- **Pros:**
  - More time for users to read and decide
  - Less pressure to act quickly
- **Cons:**
  - Longer obstruction of photo view
  - May feel sluggish

**Option C: Configurable (with 3s default)**
- **Duration:** User setting for auto-hide duration (1-10 seconds)
- **Pros:**
  - User preference accommodated
  - Accessible for users with different needs
- **Cons:**
  - Added complexity (settings UI, store management)
  - Deferred to post-MVP

**Option D: No auto-hide (manual dismiss only)**
- **Duration:** Overlay persists until user moves mouse away from lower area
- **Pros:**
  - No time pressure
  - User controls when it disappears
- **Cons:**
  - Overlay may linger and obstruct photo
  - Less elegant UX

**Impact:** Medium - affects user experience and perception of polish, but any reasonable duration works.

---

### Q001-03: Rating Removal Button Placement

**Context:** Users can remove their rating by selecting "0". The UI design needs to clarify how this is presented.

**Question:** How should the "remove rating" (0) option be presented in the UI?

**Options (ordered by preference):**

**Option A: Inline button [0] before stars (Recommended)**
- **Layout:** `[0] [1] [2] [3] [4] [5]` with 0 shown as "×" or "Remove"
- **Pros:**
  - Consistent with the button pattern
  - Clear that 0 is a special action (remove)
  - Simple implementation (same component pattern)
- **Cons:**
  - May be confused with a rating of zero
  - Takes up space in compact overlays

**Option B: Separate "Clear rating" button**
- **Layout:** `[1] [2] [3] [4] [5] [Clear ×]`
- **Pros:**
  - Visually distinct from rating action
  - Clearer intent (remove vs rate)
  - Reduces accidental removal
- **Cons:**
  - Additional UI element
  - Less compact for overlays

**Option C: Right-click or long-press to remove**
- **Interaction:** Click star to rate, right-click/long-press to remove
- **Pros:**
  - No additional UI needed
  - Clean visual design
- **Cons:**
  - Not discoverable (hidden interaction)
  - Accessibility concerns
  - Mobile long-press may be awkward

**Impact:** Low - all options are functional, mainly affects visual design and user discovery.

---

### Q001-04: Overlay Visibility on Mobile Devices

**Context:** The current spec hides rating overlays on mobile (below md: breakpoint) because hover interactions don't work well on touch devices. Users can still rate via the details drawer.

**Question:** Should we provide any rating interaction on mobile beyond the details drawer?

**Options (ordered by preference):**

**Option A: Details drawer only on mobile (Recommended)**
- **Behavior:** No overlays on mobile, rating only via PhotoDetails drawer
- **Pros:**
  - Simple, consistent experience
  - No awkward touch interaction patterns needed
  - Cleaner thumbnail grid (no overlay clutter)
  - Follows existing Lychee mobile pattern (overlays are desktop-only)
- **Cons:**
  - Requires opening details drawer to rate
  - Less convenient for quick ratings

**Option B: Tap-to-show overlay on thumbnails**
- **Behavior:** Single tap shows overlay (without opening photo), tap star to rate, tap outside to dismiss
- **Pros:**
  - Quick access to rating on mobile
  - No need to open details drawer
- **Cons:**
  - Conflicts with tap-to-open-photo gesture
  - Requires double-tap or long-press (poor UX)
  - Added complexity in touch event handling

**Option C: Always-visible compact rating on thumbnails (mobile)**
- **Behavior:** Small rating display (stars or number) always visible on thumbnails on mobile
- **Pros:**
  - Ratings always visible at a glance
  - Tap star to rate directly
- **Cons:**
  - Clutters thumbnail grid
  - Inconsistent with desktop (hover-only)
  - May obscure thumbnail image

**Impact:** Medium - affects mobile user experience, but details drawer provides full fallback.

---

### Q001-07: Statistics Record Creation Strategy

**Context:** When a user rates a photo for the first time, the `photo_statistics` record may not exist yet. The implementation must handle this gracefully.

**Question:** How should we ensure the statistics record exists when creating the first rating?

**Options (ordered by preference):**

**Option A: firstOrCreate in transaction (Recommended)**
- **Approach:** Use `PhotoStatistics::firstOrCreate(['photo_id' => $photo_id], [...defaults])` within the transaction
- **Pros:**
  - Atomic operation, no race condition
  - Laravel handles duplicate creation attempts
  - Simple implementation
- **Cons:**
  - May create statistics record even if rating fails validation
  - Extra query overhead

**Option B: Check existence before rating**
- **Approach:** Check if statistics exists, create if missing before rating transaction
- **Pros:**
  - Explicit control flow
  - Clear error handling
- **Cons:**
  - Two separate operations (not atomic)
  - Race condition if two users rate simultaneously
  - More complex code

**Option C: Database trigger**
- **Approach:** Create database trigger to auto-create statistics record on photo insert
- **Pros:**
  - Guarantees statistics always exists
  - No application logic needed
- **Cons:**
  - Adds database complexity
  - Migration complexity for existing photos
  - Not Lychee's pattern (application-level logic preferred)

**Impact:** High - affects data integrity and implementation complexity

---

### Q001-08: Transaction Rollback Error Handling

**Context:** When a database transaction fails (e.g., deadlock, constraint violation), the spec doesn't clarify what error should be returned to the user.

**Question:** How should we handle transaction failures in the rating endpoint?

**Options (ordered by preference):**

**Option A: 500 Internal Server Error with generic message (Recommended)**
- **Response:** HTTP 500, `{"message": "Unable to save rating. Please try again."}`
- **Pros:**
  - Doesn't expose database implementation details
  - Standard error handling pattern
  - User-friendly message
- **Cons:**
  - Less specific for debugging
  - May retry without fixing underlying issue

**Option B: 409 Conflict for transaction errors**
- **Response:** HTTP 409, `{"message": "Rating conflict. Please refresh and try again."}`
- **Pros:**
  - More semantic (conflict suggests retry)
  - Indicates temporary issue
- **Cons:**
  - 409 typically used for optimistic locking conflicts
  - May confuse frontend logic

**Option C: Log error, retry transaction automatically**
- **Approach:** Catch deadlock exceptions, retry transaction 2-3 times before failing
- **Pros:**
  - Transparent to user
  - Handles temporary deadlocks gracefully
- **Cons:**
  - Added complexity
  - May mask underlying database issues
  - Increased latency

**Impact:** High - affects error handling strategy and user experience

---

### Q001-09: N+1 Query Performance for user_rating

**Context:** PhotoResource includes `user_rating` field by querying `$this->ratings()->where('user_id', auth()->id())->value('rating')`. When loading many photos (album grid), this creates N+1 query problem.

**Question:** How should we optimize user_rating loading for photo collections?

**Options (ordered by preference):**

**Option A: Eager load with closure in controller (Recommended)**
- **Implementation:**
  ```php
  $photos->load(['ratings' => fn($q) => $q->where('user_id', auth()->id())]);
  ```
- **Pros:**
  - Single additional query for all photos
  - Standard Laravel pattern
  - No PhotoResource changes needed
- **Cons:**
  - Must remember to eager load in every controller method
  - Easy to forget and create N+1

**Option B: Global scope on Photo model**
- **Implementation:** Add global scope to always eager load current user's rating
- **Pros:**
  - Automatic, no controller changes needed
  - Consistent across all queries
- **Cons:**
  - Always loads ratings even when not needed
  - Performance overhead for unauthenticated users
  - Global scopes can have unexpected side effects

**Option C: Separate endpoint for ratings**
- **Implementation:** Load photos without ratings, fetch ratings separately via `/api/photos/{ids}/ratings`
- **Pros:**
  - Decoupled data loading
  - Can defer ratings until needed
- **Cons:**
  - Two API calls required
  - More complex frontend logic
  - Increased latency

**Impact:** High - affects performance for album views with many photos

---

### Q001-10: Concurrent Update Debouncing (Rapid Clicks)

**Context:** If a user rapidly clicks different star values, multiple concurrent API requests may be sent. This could cause race conditions or display inconsistencies.

**Question:** Should we debounce or throttle rapid rating changes in the UI?

**Options (ordered by preference):**

**Option A: Disable stars during API call (Recommended)**
- **Behavior:** Set `loading = true`, disable all star buttons until API returns
- **Pros:**
  - Simple implementation
  - Prevents concurrent requests
  - Clear visual feedback (loading state)
- **Cons:**
  - User must wait for each rating to complete
  - Slower if user wants to correct mistake

**Option B: Debounce rating submissions (300ms)**
- **Behavior:** Wait 300ms after last click before sending API request, cancel pending requests
- **Pros:**
  - Allows user to change mind quickly
  - Reduces API calls for rapid clicks
- **Cons:**
  - Delayed feedback
  - More complex implementation (cancel logic)
  - May feel sluggish

**Option C: Queue requests, send last value only**
- **Behavior:** Queue rating changes, send only most recent value when previous request completes
- **Pros:**
  - Always saves final user choice
  - No wasted API calls
- **Cons:**
  - Complex state management
  - User may see intermediate states that don't persist

**Impact:** High - affects UX responsiveness and data consistency

---

### Q001-11: Metrics Disabled Behavior (Can Still Rate?)

**Context:** The spec says rating data is hidden when `metrics_enabled` config is false, but doesn't clarify if users can still submit ratings when metrics are disabled.

**Question:** When metrics are disabled, should users still be able to rate photos?

**Options (ordered by preference):**

**Option A: Yes, rating functionality always available (Recommended)**
- **Behavior:** Users can rate, but aggregates/counts are hidden in UI. Data is still stored.
- **Pros:**
  - Consistent user experience
  - Data collection continues even if display is disabled
  - Easy to re-enable metrics later with existing data
- **Cons:**
  - May confuse users (why can I rate if I can't see ratings?)
  - Data stored but not shown

**Option B: No, disable rating when metrics disabled**
- **Behavior:** Hide all rating UI and disable `/Photo::rate` endpoint when metrics disabled
- **Pros:**
  - Consistent (if metrics off, ratings off)
  - Respects privacy/metrics setting fully
- **Cons:**
  - Loss of data collection
  - Hard to re-enable later (no historical data)
  - Inconsistent with favorites (favorites work when metrics disabled)

**Option C: Admin setting controls independently**
- **Behavior:** Separate `ratings_enabled` config independent of `metrics_enabled`
- **Pros:**
  - Granular control
  - Can enable rating without showing aggregates
- **Cons:**
  - More configuration complexity
  - May confuse admins

**Impact:** High - affects feature scope and user experience

---

### Q001-12: Rating Display When Metrics Disabled

**Context:** FR-001-04 says rating data is shown "when metrics are enabled," but spec doesn't clarify if user's own rating is shown when metrics are disabled.

**Question:** When metrics are disabled, should the UI show the user's own rating (even if aggregates are hidden)?

**Options (ordered by preference):**

**Option A: Show user's own rating regardless of metrics setting (Recommended)**
- **Behavior:** User sees their own rating stars highlighted, but no aggregate average/count
- **Pros:**
  - User feedback on their own action
  - Doesn't expose community metrics (privacy preserved)
  - Consistent with user-centric data (my data vs community data)
- **Cons:**
  - Slightly inconsistent with "metrics disabled" (rating is a metric)

**Option B: Hide all rating data when metrics disabled**
- **Behavior:** No rating display at all, including user's own
- **Pros:**
  - Fully consistent with metrics disabled
  - Simplest implementation
- **Cons:**
  - Poor UX (user can't see what they rated)
  - Feels broken ("I clicked 4 stars, where did it go?")

**Impact:** Medium - affects UX when metrics are disabled

---

### Q001-13: Half-Star Display for Fractional Averages

**Context:** Spec stores rating_avg as decimal(3,2), allowing fractional values like 4.33. UI mockups show full/empty stars only (no half-stars).

**Question:** Should we display half-stars for fractional average ratings?

**Options (ordered by preference):**

**Option A: Full stars only, round to nearest integer (Recommended)**
- **Display:** 4.33 avg → ★★★★☆ (4 stars), show "4.33" as text next to stars
- **Pros:**
  - Simpler UI implementation
  - Clear visual (full or empty)
  - Numeric value still shows precision
- **Cons:**
  - Visual representation less precise

**Option B: Half-star display for .25-.74 range**
- **Display:** 4.33 avg → ★★★★⯨ (4.5 stars visually), show "4.33" as text
- **Pros:**
  - More precise visual representation
  - Common rating pattern (Amazon, IMDb)
- **Cons:**
  - More complex implementation (half-star icon, rounding logic)
  - May not match user's mental model (users rate 1-5, not 1-10)

**Option C: Gradient fill for precise fractional display**
- **Display:** 4.33 avg → ★★★★⯨ (4th star 33% filled)
- **Pros:**
  - Exact visual representation
  - Visually interesting
- **Cons:**
  - Complex implementation (SVG/CSS gradients)
  - May be hard to read at small sizes
  - Uncommon pattern (users may not understand)

**Impact:** Medium - affects UI polish and clarity

---

### Q001-14: Overlay Persistence on Active Interaction

**Context:** PhotoRatingOverlay (full photo) auto-hides after 3 seconds of inactivity. Spec says "persists if mouse over overlay itself," but doesn't clarify behavior when user is actively clicking/interacting.

**Question:** Should the overlay stay visible while the user is actively interacting with the rating stars, even if they briefly move the mouse outside the overlay?

**Options (ordered by preference):**

**Option A: Persist while loading, then restart auto-hide timer (Recommended)**
- **Behavior:** After user clicks a star, overlay stays visible during API call (loading state), then restarts 3s auto-hide timer on success
- **Pros:**
  - User sees confirmation (success toast + updated rating)
  - Natural flow (interact → see result → overlay fades)
- **Cons:**
  - May stay visible longer than expected

**Option B: Auto-hide immediately after successful rating**
- **Behavior:** After rating succeeds, overlay fades out immediately (no 3s delay)
- **Pros:**
  - Faster cleanup after action
  - User sees toast notification for confirmation
- **Cons:**
  - Abrupt (overlay disappears right after click)
  - User may not see updated average

**Option C: Persist until mouse leaves lower area entirely**
- **Behavior:** Overlay stays visible as long as mouse is in lower 20-30% zone, regardless of timer
- **Pros:**
  - User has full control
  - Overlay available for multiple rating changes
- **Cons:**
  - May linger too long
  - Obstructs photo view longer

**Impact:** Medium - affects UX polish and expected behavior

---

### Q001-15: Rating Tooltip/Label Clarity (What Are Stars?)

**Context:** UI mockups don't show tooltips or ARIA labels explaining what the star rating means (1 = lowest, 5 = highest).

**Question:** Should we add tooltips/labels to explain the star rating scale?

**Options (ordered by preference):**

**Option A: Hover tooltips on star buttons (Recommended)**
- **Implementation:** Each star button shows tooltip: "1 star", "2 stars", ... "5 stars"
- **Pros:**
  - Self-explanatory on hover
  - Accessible (screen reader friendly with aria-label)
  - Doesn't clutter UI
- **Cons:**
  - Requires tooltip implementation
  - May be obvious to most users

**Option B: Label text: "Rate 1-5 stars"**
- **Implementation:** Static text label above star buttons
- **Pros:**
  - Always visible, no hover needed
  - Clear scale indication
- **Cons:**
  - Takes up space in compact overlays
  - May be redundant (stars are intuitive)

**Option C: No labels/tooltips (stars are self-evident)**
- **Implementation:** No additional labels, star icons only
- **Pros:**
  - Cleanest UI
  - Stars are universal rating symbol
- **Cons:**
  - Accessibility concerns (screen reader users)
  - New users may not understand scale

**Impact:** Medium - affects accessibility and UX clarity

---

### Q001-16: Accessibility (Keyboard Navigation, ARIA)

**Context:** Spec doesn't specify keyboard navigation or ARIA attributes for rating components.

**Question:** What accessibility features should be implemented for the rating UI?

**Options (ordered by preference):**

**Option A: Full WCAG 2.1 AA compliance (Recommended)**
- **Implementation:**
  - Keyboard navigation: Tab to focus rating, Arrow keys to select star, Enter/Space to rate
  - ARIA attributes: `role="radiogroup"`, `aria-label="Rate this photo"`, `aria-checked` on selected star
  - Focus indicators: Visible outline on focused star
  - Screen reader announcements: "4 stars selected, 15 total votes, average 4.2"
- **Pros:**
  - Fully accessible to all users
  - Meets legal/compliance requirements
  - Better UX for keyboard users
- **Cons:**
  - More implementation effort
  - Testing complexity

**Option B: Basic accessibility (tab focus, ARIA labels only)**
- **Implementation:** Tab to rating widget, click to rate, basic aria-labels
- **Pros:**
  - Simpler implementation
  - Covers most accessibility needs
- **Cons:**
  - Not fully keyboard navigable
  - May not meet WCAG AA

**Option C: Defer to post-MVP**
- **Decision:** Launch with basic implementation, enhance accessibility later
- **Pros:**
  - Faster to ship
  - Can gather user feedback first
- **Cons:**
  - Excludes users with disabilities
  - Harder to retrofit later
  - Potential compliance issues

**Impact:** Medium - affects accessibility and inclusivity

---

### Q001-17: Optimistic UI Updates vs Server Confirmation

**Context:** Spec doesn't clarify whether UI should update optimistically (immediately on click) or wait for server confirmation.

**Question:** Should the rating UI update optimistically or wait for API response?

**Options (ordered by preference):**

**Option A: Wait for server confirmation (Recommended)**
- **Behavior:** Show loading state on click, update UI only after API success
- **Pros:**
  - Always shows accurate server state
  - Clear error handling (revert on failure)
  - No phantom updates
- **Cons:**
  - Slower perceived responsiveness
  - Requires loading state UI

**Option B: Optimistic update, revert on error**
- **Behavior:** Update UI immediately on click, show error and revert if API fails
- **Pros:**
  - Instant feedback, feels faster
  - Better perceived performance
- **Cons:**
  - Complex state management (revert logic)
  - User may see incorrect state briefly
  - Confusing if network is slow and revert happens seconds later

**Option C: Hybrid (optimistic for user rating, wait for aggregate)**
- **Behavior:** Update user's star selection immediately, but wait for server to update average/count
- **Pros:**
  - Fast feedback for user action
  - Accurate aggregate display
- **Cons:**
  - Split state management
  - May show inconsistent state (user rating updated, aggregate unchanged)

**Impact:** Medium - affects perceived performance and UX

---

### Q001-18: Rating Count Threshold for Display

**Context:** Spec doesn't specify if ratings should be hidden when count is very low (e.g., 1-2 ratings may not be statistically meaningful).

**Question:** Should we hide average rating display until a minimum number of ratings exist?

**Options (ordered by preference):**

**Option A: Always show rating, regardless of count (Recommended)**
- **Display:** Show "★★★★★ 5.0 (1)" even for single rating
- **Pros:**
  - Transparent, shows all data
  - Simpler logic (no threshold)
  - Users can judge significance from count
- **Cons:**
  - Single ratings may be misleading (not representative)
  - May encourage rating manipulation

**Option B: Hide average until N >= 3 ratings**
- **Display:** Show "(3 ratings)" text only until 3+ ratings, then show average
- **Pros:**
  - More statistically meaningful average
  - Reduces impact of single outlier ratings
- **Cons:**
  - Hides data from users
  - Arbitrary threshold (why 3?)
  - Users may be confused why they can't see average after rating

**Option C: Show with disclaimer for low counts**
- **Display:** "★★★★★ 5.0 (1 rating)" with styling/tooltip: "Based on limited ratings"
- **Pros:**
  - Shows data with context
  - Users can make informed judgment
- **Cons:**
  - More UI complexity
  - May clutter compact overlays

**Impact:** Medium - affects data presentation and perceived trustworthiness

---

### Q001-19: Telemetry Event Granularity

**Context:** Spec defines three telemetry events (photo.rated, photo.rating_updated, photo.rating_removed). These events overlap (updating is also rating).

**Question:** Should we emit separate events for create vs update, or combine into one event?

**Options (ordered by preference):**

**Option A: Three separate events (as spec defines) (Recommended)**
- **Events:** `photo.rated` (new), `photo.rating_updated` (change), `photo.rating_removed` (delete)
- **Pros:**
  - Granular analytics (can track rating changes separately from new ratings)
  - Easier to query specific actions
- **Cons:**
  - More event types to maintain
  - Logic to determine which event to emit

**Option B: Single event with action field**
- **Event:** `photo.rating_changed` with field `action: "created"|"updated"|"removed"`
- **Pros:**
  - Simpler event schema
  - Single event handler
- **Cons:**
  - Less semantic
  - Requires filtering by action field in analytics

**Option C: Two events (rated/removed only)**
- **Events:** `photo.rated` (create or update), `photo.rating_removed`
- **Pros:**
  - Simpler (updates are just "rated again")
  - Matches user mental model (user doesn't distinguish create vs update)
- **Cons:**
  - Can't track rating changes separately from new ratings

**Impact:** Low - affects telemetry analytics, doesn't affect user experience

---

### Q001-20: Rating Analytics/Trending Features

**Context:** Spec explicitly excludes "advanced rating analytics or trends" from scope, but this may be a desirable future feature.

**Question:** Should we design the schema and telemetry to support future analytics features (trending photos, rating distributions)?

**Options (ordered by preference):**

**Option A: Yes, design for extensibility (Recommended)**
- **Approach:** Include timestamps, consider adding indexes for common queries (ORDER BY rating_avg), design telemetry for time-series analysis
- **Pros:**
  - Easier to add features later
  - Better query performance from day 1
  - Minimal overhead now
- **Cons:**
  - May add complexity that's never used
  - YAGNI (You Aren't Gonna Need It) principle violation

**Option B: No, implement minimally for current scope**
- **Approach:** Bare minimum schema/indexes for current requirements, add analytics support later if needed
- **Pros:**
  - Simpler initial implementation
  - Follows YAGNI principle
  - Faster to ship
- **Cons:**
  - May require schema changes later
  - Migration complexity for existing data

**Impact:** Low - affects future extensibility, not current functionality

---

### Q001-21: Album Aggregate Rating Display

**Context:** Spec excludes "album-level aggregate ratings" from scope, but users may expect to see album ratings in album grid view.

**Question:** Should we display aggregate album ratings (average of all photo ratings in album)?

**Options (ordered by preference):**

**Option A: Defer to future feature (Recommended)**
- **Decision:** Not in scope for Feature 001, track as separate future feature (Feature 00X)
- **Pros:**
  - Keeps current feature focused
  - Can design properly later with user feedback on photo ratings
- **Cons:**
  - Users may expect this feature
  - More work to add later

**Option B: Add to current feature scope**
- **Implementation:** Calculate album average from photo ratings, display in album grid
- **Pros:**
  - Complete feature (photos + albums)
  - More useful to users
- **Cons:**
  - Increases scope significantly
  - More complex queries (aggregate of aggregates)
  - Unclear UX (what does album rating mean? average of photos? weighted by photo quality?)

**Impact:** Low - out of current scope, but may be user expectation

---

### Q001-22: Rating Export in Photo Backup

**Context:** Lychee supports photo export/backup functionality. Spec doesn't clarify if rating data should be included in exports.

**Question:** Should photo export/backup include rating data (user's own rating and/or aggregates)?

**Options (ordered by preference):**

**Option A: Include in export (CSV/JSON format) (Recommended)**
- **Export fields:** photo_id, user's rating, average rating, rating count
- **Pros:**
  - Complete data portability
  - Users can back up their ratings
  - Useful for data analysis outside Lychee
- **Cons:**
  - Larger export files
  - Privacy concerns if export is shared (includes others' aggregate data)

**Option B: Export user's ratings only (not aggregates)**
- **Export fields:** photo_id, user's rating
- **Pros:**
  - User data portability
  - No privacy concerns (only user's own data)
- **Cons:**
  - Incomplete export (aggregates lost)

**Option C: No export (ratings are ephemeral/server-side only)**
- **Decision:** Ratings not included in photo exports
- **Pros:**
  - Simpler export logic
  - Smaller export files
- **Cons:**
  - Data loss risk if server fails
  - No migration path to other platforms

**Impact:** Low - affects data portability, not core functionality

---

### Q001-23: Rating Notification to Photo Owner

**Context:** When other users rate a photo, the photo owner may want to be notified (similar to comment notifications).

**Question:** Should photo owners receive notifications when their photos are rated?

**Options (ordered by preference):**

**Option A: Defer to future feature (notifications system) (Recommended)**
- **Decision:** Not in scope for Feature 001, add when notifications framework is implemented
- **Pros:**
  - Keeps feature scope focused
  - Requires notifications infrastructure (may not exist yet)
  - Can be added non-intrusively later
- **Cons:**
  - Photo owners won't know when photos are rated
  - Lower engagement

**Option B: Simple email notification**
- **Implementation:** Send email to photo owner when photo is rated (with throttling: max 1 email per photo per day)
- **Pros:**
  - Engagement boost
  - Photo owners stay informed
- **Cons:**
  - Email fatigue (could get many emails)
  - Requires email configuration
  - Increases scope

**Option C: In-app notification only (no email)**
- **Implementation:** Show notification bell/count in Lychee UI when photos are rated
- **Pros:**
  - Less intrusive than email
  - Real-time feedback when user is active
- **Cons:**
  - Requires notification UI infrastructure
  - User may miss notifications if not logged in

**Impact:** Low - nice-to-have feature, not core rating functionality

---

### Q001-24: Statistics Recalculation Artisan Command

**Context:** Implementation notes mention "artisan command to recalculate all statistics from photo_ratings table for data integrity audits."

**Question:** Should we implement an artisan command to recalculate rating statistics, and if so, when should it be used?

**Options (ordered by preference):**

**Option A: Yes, implement `php artisan photos:recalculate-ratings` command (Recommended)**
- **Usage:** Run manually after data migration, database corruption, or as periodic audit
- **Behavior:** Iterate all photos, sum ratings from photo_ratings table, update photo_statistics
- **Pros:**
  - Data integrity safety net
  - Useful for debugging/auditing
  - Can fix inconsistencies from bugs or manual DB edits
- **Cons:**
  - Extra code to maintain
  - May be slow on large databases
  - Risk of overwriting correct data if command is buggy

**Option B: No command, rely on transaction integrity**
- **Decision:** Trust atomic transactions to maintain consistency, no recalculation needed
- **Pros:**
  - Simpler (less code)
  - Transactions should guarantee consistency
- **Cons:**
  - No recovery if bug causes inconsistency
  - No way to audit/verify correctness

**Option C: Automated periodic recalculation (cron job)**
- **Implementation:** Run recalculation command daily/weekly via scheduler
- **Pros:**
  - Automatic data integrity maintenance
  - Catches and fixes issues proactively
- **Cons:**
  - Resource intensive (extra DB load)
  - May mask underlying bugs instead of fixing them
  - Overkill if transactions are working correctly

**Impact:** Low - data integrity safety feature, not core functionality

---

### Q001-25: Migration Strategy for Existing Installations

**Context:** When existing Lychee installations upgrade to this feature, they'll have photos but no rating data. Migration behavior isn't specified.

**Question:** How should the migration handle existing photos with no rating data?

**Options (ordered by preference):**

**Option A: Migration adds columns with defaults, no backfill (Recommended)**
- **Behavior:** Migration adds rating_sum/rating_count columns with default 0, existing photos have no ratings
- **Pros:**
  - Clean state (accurate: no ratings yet)
  - Fast migration (no data processing)
  - No assumptions about historical data
- **Cons:**
  - Existing photos start with no ratings (expected behavior)

**Option B: Backfill with random/seeded ratings (dev/test only)**
- **Behavior:** For development, optionally seed some random ratings for testing
- **Pros:**
  - Easier to test rating display with real-looking data
- **Cons:**
  - Fake data, not suitable for production
  - Could confuse users if accidentally run in production

**Option C: Import from external source (if available)**
- **Behavior:** If migrating from another system with ratings, provide import script
- **Pros:**
  - Preserves historical rating data
- **Cons:**
  - Complex, requires external data source
  - Not applicable to most installations
  - Out of scope for Feature 001

**Impact:** Low - affects upgrade experience, but default behavior (no ratings) is expected

---

### ~~Q-004-01: Recomputation Trigger Strategy for Size Statistics~~ ✅ RESOLVED

**Decision:** Option B - Separate `RecomputeAlbumSizeJob` triggered independently, using Skip middleware with cache-based job tracking (same pattern as Feature 003's `RecomputeAlbumStatsJob`)
**Rationale:** Decoupled from Feature 003, can optimize independently, reuses proven Skip middleware pattern from [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php:76-93) with cache key `album_size_latest_job:{album_id}` and unique job IDs for deduplication.
**Updated in spec:** FR-004-02, JOB-004-01, middleware implementation details

---

### ~~Q-004-02: Migration/Backfill Strategy for Existing Albums~~ ✅ RESOLVED

**Decision:** Option A - Separate artisan command, manual execution, PLUS maintenance UI button for operators
**Rationale:** Operator controls timing during maintenance window, fast migration (schema only), progress monitoring. Admin UI button provides convenient trigger for backfill without CLI access.
**Updated in spec:** FR-004-04, CLI-004-01, maintenance UI addition

---

### ~~Q-004-03: Job Deduplication Approach for Concurrent Updates~~ ✅ RESOLVED

**Decision:** Option D (Custom) - Use Skip middleware with cache-based job tracking (same pattern as Feature 003)
**Rationale:** Reuses proven pattern from [RecomputeAlbumStatsJob.php](app/Jobs/RecomputeAlbumStatsJob.php): Each job gets unique ID, latest job ID stored in cache with key `album_size_latest_job:{album_id}`, `Skip::when()` middleware checks if newer job queued. Simpler than `WithoutOverlapping`, guarantees most recent update eventually processes.
**Updated in spec:** FR-004-02, JOB-004-01

---

## How to Use This Document

1. **Log new questions:** Add a row to the Active Questions table with a unique ID (format: `Q###-##`), feature reference, priority (High/Medium), and brief summary.
2. **Add details:** Create a corresponding section under Question Details with:
   - Full question context
   - Options (A, B, C...) ordered by preference
   - Pros/cons for each option
   - Impact analysis
3. **Present to user:** Once logged, present the question inline in chat referencing the question ID.
4. **Resolve and remove:** When answered, update the relevant spec sections (and create ADR if high-impact), then delete both the table row and Question Details entry.

---

*Last updated: 2026-01-02*
