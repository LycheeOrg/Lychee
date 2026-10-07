# Open Questions – Feature 052

Open questions for [Feature 052](spec.md). Log every high- and medium-impact question here (table row + Question Details entry) before asking the user; see [open-questions-format.md](../../spec-guidelines/open-questions-format.md). Once answered, fold the outcome into [spec.md](spec.md) (and an ADR when architecturally significant), then mark the entry resolved.

## Active Questions

| Question ID | Feature | Priority | Summary | Status | Opened | Updated |
|-------------|---------|----------|---------|--------|--------|---------|
| ~~Q-052-01~~ | 052 – Managed Cache Service | High | Scope — generic caching infra only, infra + a pilot consumer, or broad adoption across permission-dependent queries in this same feature? | Resolved (A — generic service, proven via one pilot consumer) | 2026-07-21 | 2026-07-21 |
| ~~Q-052-02~~ | 052 – Managed Cache Service | High | Relationship to existing `RouteCacher`/`RouteCacheManager`/`CacheTag` HTTP response-cache infra (Feature 040) — new independent service, or extend/reuse the existing tag-bookkeeping mechanism? | Resolved (A modified — new independent, general-purpose service, not query-specific) | 2026-07-21 | 2026-07-21 |
| ~~Q-052-03~~ | 052 – Managed Cache Service | High | Enablement gating — share the existing `cache_enabled` config (currently forced off by default per Feature 040), a new dedicated flag, or always-on with no toggle? | Resolved (A — new flag `managed_cache_enabled`) | 2026-07-21 | 2026-07-21 |
| ~~Q-052-04~~ | 052 – Managed Cache Service | Medium | Nested-tree cascade — must invalidation on access-rights change / album move propagate to descendant albums, and how? | Resolved (A — ancestor-path tagging, hand-rolled key-list bookkeeping since no native tag support exists) | 2026-07-21 | 2026-07-21 |
| ~~Q-052-05~~ | 052 – Managed Cache Service | Medium | User-group membership changes — does adding/removing a user from a group invalidate that user's cached permission-dependent entries? | Resolved (A — in scope) | 2026-07-21 | 2026-07-21 |
| ~~Q-052-06~~ | 052 – Managed Cache Service | Medium | `AlbumDeleted` event carries only `parent_id`, not the deleted album's own id — FR-052-06 asks the listener to evict the album's own tag, which isn't possible without either accepting the gap or extending the event payload | Resolved (A — evict parent's tag only, no event change) | 2026-07-28 | 2026-07-28 |
| ~~Q-052-07~~ | 052 – Managed Cache Service | High | Settings category for `managed_cache_enabled`/`managed_cache_ttl` — reusing `'Mod Cache'` would hide both fields whenever `features.enable-request-caching` is `false` (its default), contradicting the required independence from Feature 040 | Resolved (B — reuse `'Mod Cache'`, patch `SettingsController`'s visibility filter) | 2026-07-28 | 2026-07-28 |

## Question Details

### ~~Q-052-01~~ · Scope — generic infra only, infra + pilot consumer, or broad adoption? ✅ RESOLVED

**Status:** Resolved — **Option A, generic-first** (the service itself must be built as a fully generic, reusable mechanism — not hardcoded to any one query — proven out via a single pilot consumer)
**Feature:** 052 – Managed Cache Service
**Priority:** High
**Opened:** 2026-07-21
**Resolved:** 2026-07-21

**Resolution:** Build a generic caching service with no knowledge of "queries," "albums," or "users" baked into its public API — it accepts an arbitrary cache key, an arbitrary callable, and an arbitrary set of dependency tags supplied by the caller. **Pilot consumer updated 2026-07-21** (user instruction, after initial spec draft): rather than `BaseAlbumImpl::current_user_permissions()`, the two pilot consumers are `AlbumRepository::getChildrenPaginated()` (an album's sub-albums) and `PhotoRepository::getPhotosForAlbumPaginated()` (an album's photos) — both permission-filtered, user-dependent, and hit on every album-view page load; both supply album-id and user-id tags, proving the mechanism end-to-end. The service class itself still carries no album/user-specific logic. Broader adoption beyond the two pilots (including `current_user_permissions()`) is deferred to future features/backlog.

**Spec impact:** Goals/Non-Goals and FR-052 section below; drives the generic (not query-specific) shape of `ManagedCacheService`.

---

### ~~Q-052-02~~ · Relationship to the existing `RouteCacher` / `RouteCacheManager` / `CacheTag` infrastructure ✅ RESOLVED

**Status:** Resolved — **Option A, generalized** (new, independent service — and explicitly *not* scoped to query-caching; a general-purpose managed cache usable for any cacheable value)
**Feature:** 052 – Managed Cache Service
**Priority:** High
**Opened:** 2026-07-21
**Resolved:** 2026-07-21

**Resolution:** The service is named and designed as a general-purpose cache manager (`App\Services\Cache\ManagedCacheService`), not a "query cache" — the user explicitly noted it "does not necessarily have to be related to Query." It reuses the *pattern* `RouteCacher` established (tag → key-set bookkeeping on top of plain `Cache::get/put/forget`) but has no dependency on `RouteCacheManager`'s per-URI config or the HTTP request/response lifecycle, and is not limited to caching query results — any value a caller wants memoized under key + dependency tags is in scope. `RouteCacher`/`RouteCacheManager` remain untouched, serving Feature 040's route-level cache independently.

**Spec impact:** Feature renamed 052 – Managed Cache Service (directory `052-managed-cache-service`); Interface & Contract Catalogue below.

---

### ~~Q-052-03~~ · Enablement gating — shared `cache_enabled`, a new flag, or always-on? ✅ RESOLVED

**Status:** Resolved — **Option A** (new, independent config key)
**Feature:** 052 – Managed Cache Service
**Priority:** High
**Opened:** 2026-07-21
**Resolved:** 2026-07-21

**Resolution:** New config key `managed_cache_enabled`, decoupled from Feature 040's `cache_enabled`. Default value and settings-UI visibility follow the same category/config-row pattern used elsewhere (see FR-052 below).

**Spec impact:** FR-052-06 below; new `configs` migration row.

---

### ~~Q-052-04~~ · Nested-tree cascade on access-rights change / album move ✅ RESOLVED

**Status:** Resolved — **Option A** (ancestor-path tagging at write time), with an implementation-constraint correction from the user
**Feature:** 052 – Managed Cache Service
**Priority:** Medium
**Opened:** 2026-07-21
**Resolved:** 2026-07-21

**Resolution:** Confirmed Option A (tag cache entries with the full ancestor-path at write time so evicting one ancestor's tag covers all descendants). **Correction from the user:** there is no native "tag" primitive available — the underlying cache store is plain key:value (default `CACHE_DRIVER=file` has no tag support). "Tags" in this feature are therefore a hand-rolled bookkeeping layer: a tag is itself just a cache key whose value is the set of member keys currently associated with it (exactly the mechanism `RouteCacher::rememberTags()`/`forgetTag()` already implements for the HTTP response cache — see `app/Metadata/Cache/RouteCacher.php:142-149`). `ManagedCacheService` reimplements this same key-list-as-a-value pattern independently (per Q-052-02, no shared class with `RouteCacher`).

**Spec impact:** FR-052-03/04/07 below; Appendix note on the key-list bookkeeping mechanism.

---

### ~~Q-052-05~~ · Do user-group membership changes invalidate a user's cached entries? ✅ RESOLVED

**Status:** Resolved — **Option A** (in scope)
**Feature:** 052 – Managed Cache Service
**Priority:** Medium
**Opened:** 2026-07-21
**Resolved:** 2026-07-21

**Resolution:** In scope. A third pre-existing gap was found to match: `UserGroupsManagementController::addUser()/removeUser()/updateUserRole()` (`app/Http/Controllers/Admin/UserGroupsManagementController.php`) dispatches no event today. This feature adds an event dispatch there (mirroring the Move/SharingController fixes) and a listener that evicts the affected user's cache tag.

**Spec impact:** FR-052-02b below; Overview's gap list extended to three items.

---

### ~~Q-052-06~~ · `AlbumDeleted` event payload gap — can't evict the deleted album's own tag ✅ RESOLVED

**Status:** Resolved — **Option A** (evict only the parent's tag; no event change)
**Feature:** 052 – Managed Cache Service
**Priority:** Medium
**Opened:** 2026-07-28
**Resolved:** 2026-07-28

**Resolution:** `ManagedCacheAlbumInvalidator::handleAlbumDeleted(AlbumDeleted $event)` calls `forgetTag("album:" . ($event->parent_id ?? 'root'))` only. `App\Events\AlbumDeleted`'s signature (`?string $parent_id`) is unchanged; the two pre-existing listeners (`RecomputeAlbumSizeOnAlbumChange`, `RecomputeAlbumStatsOnAlbumChange`) are untouched. The deleted album's own `"album:{id}"` tag, if it was ever written, is left to expire via TTL — harmless, since no route can query a deleted album again.

**Spec impact:** FR-052-06 below carries an explicit carve-out note for the `AlbumDeleted` case.

---

### ~~Q-052-07~~ · Settings category for `managed_cache_enabled`/`managed_cache_ttl` ✅ RESOLVED

**Status:** Resolved — **Option B** (reuse `'Mod Cache'`, patch the visibility filter)
**Feature:** 052 – Managed Cache Service
**Priority:** High
**Opened:** 2026-07-28
**Resolved:** 2026-07-28

**Resolution:** The two new config rows (`managed_cache_enabled`, `managed_cache_ttl`) are added under the existing `cat => 'Mod Cache'` category — no new `config_categories` row. `SettingsController::getAll()`'s `->when(config('features.enable-request-caching') === false, ...)` clause (`app/Http/Controllers/Admin/SettingsController.php:74`) is changed from `$q->where('cat', '!=', 'Mod Cache')` to `$q->where(fn ($q2) => $q2->where('cat', '!=', 'Mod Cache')->orWhereIn('key', ['managed_cache_enabled', 'managed_cache_ttl']))`, so those two keys remain visible even when the Feature-040 flag is off, while every other `'Mod Cache'` row keeps its existing gating. **User correction:** the recommended new-category option (A) was not chosen — this is now a normative deviation from that recommendation; implementers must not restore the excluded `->where('cat', '!=', 'Mod Cache')` form without also re-checking these two keys' visibility.

**Spec impact:** FR-052-11/UI-052-01/02 below note the shared category and the split-visibility filter; `SettingsController::getAll()` is explicitly in scope for this feature (amends the Non-Goals' "Feature 040 untouched" framing to "Feature 040's `Mod Cache` config rows are untouched; only the category-visibility filter itself gains a two-key exemption").
