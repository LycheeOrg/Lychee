# Current Session

_Last updated: 2026-07-21_

## Active Features

- Feature 052 – Managed Cache Service: spec drafted and complete; **Q-052-01..05 all resolved** (Option A each). No plan/tasks yet.
- Feature 049 – Migration to Nuxt UI: spec, plan, and tasks drafted (Draft status), analysis gate passed. Not yet implemented.
- Feature 048 – Fix Multi-Group Permissions: spec, plan, and tasks drafted (Draft status). Not yet implemented.

## Session Summary

### Feature 052 – Managed Cache Service — Spec drafted and complete, all open questions resolved (this session, 2026-07-21)

**Request:** New feature to cache values whose result depends on the requesting user's access rights to one or more albums, with a dependency-mapping mechanism (album id + user id) so cached entries can be invalidated when access rights change, a photo is uploaded, an album is moved, etc.

**Existing infrastructure found (adjacent, not reused as-is):** `App\Metadata\Cache\RouteCacher`/`RouteCacheManager`/`App\Enum\CacheTag` already caches whole HTTP responses keyed by route+user, tagged by album id, with `AlbumCacheCleaner`/`TaggedRouteCacheCleaner` listeners reacting to `AlbumRouteCacheUpdated`/`TaggedRouteCacheUpdated`. Governed by `cache_enabled`, forced off by default since Feature 040 (`disable-request-caching`). Left untouched — this feature builds a new, independent, general-purpose service instead.

**Three real invalidation gaps confirmed during investigation (all fixed by this feature, FR-052-03/04/05):**
- `App\Actions\Album\Move::do()` (`app/Actions/Album/Move.php`) dispatches no event on album move/re-parent.
- `App\Http\Controllers\Gallery\SharingController` (`create`/`edit`/`delete`/`propagate`) dispatches no event when `AccessPermission` rows change.
- `App\Http\Controllers\Admin\UserGroupsManagementController` (`addUser`/`removeUser`/`updateUserRole`) dispatches no event on group-membership change.

**5 open questions logged and resolved same-day** (Q-052-01..05, all Option A):
- Q-052-01: generic service (not query-specific). **Pilot consumer changed 2026-07-21 (user instruction, post-resolution):** instead of `BaseAlbumImpl::current_user_permissions()`, the two pilots are `AlbumRepository::getChildrenPaginated()` (sub-albums) and `PhotoRepository::getPhotosForAlbumPaginated()` (photos) — both permission-filtered, hit on every album view, and the exact routes (`Album::albums`/`Album::photos`) the existing HTTP response cache already lists but runs uncached by default. No other adoption in this feature.
- Q-052-02: new independent `App\Services\Cache\ManagedCacheService` — user clarified it "does not necessarily have to be related to Query," driving the generic (not "query cache") naming/shape.
- Q-052-03: new independent config flag `managed_cache_enabled` (default `true`), decoupled from Feature 040's `cache_enabled`. New `managed_cache_ttl` config too.
- Q-052-04: ancestor-path tagging (tag a cached entry with its own album id + every ancestor id via `Album::ancestorsOf()`) so an ancestor's tag eviction reaches descendants without a runtime tree walk. **User correction:** there is no native cache-tagging primitive available (default `file` driver) — "tags" are hand-rolled key-list bookkeeping (a tag is a cache key whose value is a set of member keys), mirroring `RouteCacher::rememberTags()`/`forgetTag()`'s existing pattern, reimplemented independently.
- Q-052-05: user-group membership change is in scope as an invalidation trigger (new `UserGroupMembershipChanged` event, third gap found to match).

**Spec is now feature-complete** (FR-052-01..11, NFR-052-01..05, S-052-01..12, full Interface & Contract Catalogue including two new events `AccessPermissionChanged`/`UserGroupMembershipChanged` and two new listeners `ManagedCacheAlbumInvalidator`/`ManagedCacheUserInvalidator`). Directory renamed `052-query-cache-service` → `052-managed-cache-service` to match the generic naming resolution. The album-invalidation listener also evicts a mutated album's immediate-parent tag, closing a "negative cache" gap (a child becoming newly visible/hidden must still invalidate the parent's cached children list even though that list never referenced the child).

**Not yet done:** plan.md/tasks.md (spec is ready for planning — Analysis Gate not yet run). Implementation not started.

### Feature 049 – Migration to Nuxt UI — Spec/Plan/Tasks Drafted (this session)

**Request:** Replace PrimeVue (`primevue`, `@primeuix/themes`, `tailwindcss-primeui`, `primeicons`) with Nuxt UI (`@nuxt/ui`, standalone Vue mode — no full Nuxt framework) across the frontend.

**Codebase inventory (2026-07-02):** PrimeVue imported in 235 of 286 `.vue`/`.ts` files (~82%). Largest costs: `tailwindcss-primeui` utility classes in 197 files, a ~500-line custom theme preset (`resources/js/style/preset.ts`) with no Nuxt UI equivalent, and PrimeVue's `pt`/`dt` pass-through styling APIs in 36-42 files. High-frequency components: Button (154 files), `useToast` (119 call sites, no wrapper composable existed), Dialog (55), Toolbar (42, no direct Nuxt UI equivalent), ProgressSpinner (41). Icons: `primeicons` (562 occurrences/139 files) — an Iconify collection matching PrimeIcons 1:1 (`@iconify-json/prime`) exists on npm. DataTable (10 admin/statistics/webshop files) requires structural rewrite (TanStack-Table-based `UTable` vs. PrimeVue's slot-based API). Embed bundle (`resources/js/embed/`) has zero PrimeVue coupling — explicitly out of scope. No frontend automated test suite exists (0 `.test.ts` files) — verification is manual/browser-based.

**Three high-impact open questions resolved same-day (recommended option chosen in all three):**
- Q-049-01 (sizing): one feature, full scope, tracked to completion (not split across features, not foundation-only).
- Q-049-02 (icons): parity via `@iconify-json/prime`, no visual redesign bundled into this migration.
- Q-049-03 (ripple): dropped entirely, no replacement; Reka UI's built-in focus-trap replaces `v-focustrap`.

**ADR-0005** recorded (`docs/specs/6-decisions/ADR-0005-nuxt-ui-migration.md`) capturing the overall decision and all three sub-decisions.

**Plan shape:** 15 phases / 45 increments (I1-I43 plus I7a/I7b, I26a/I26b sub-increments) / 43 tasks in tasks.md. Ordered: foundation (install, theme, icons) → new composables (`useAppToast()`, `useConfirmDialog()` — fill gaps Nuxt UI has no built-in for) → app shell (`App.vue`, `LeftMenu.vue`) → toast/confirm call-site sweep → Button (154 files, 6 increments) → Dialog (55 files, 4 increments) → Toolbar (42 files, 2 increments, composed-flex-header pattern since no direct equivalent) → loading/layout primitives → form primitives (8 existing wrapper components are the migration seam) → navigation/context-menu → DataTable (10 files, structural rewrite, 3 increments) → misc components → pass-through/directive cleanup → dependency removal (hard completion gate, FR-049-18) → documentation sync.

**Analysis gate:** run and passed 2026-07-02 (see plan.md's Analysis Gate section) — one intentional, documented deviation (branch-coverage matrix replaced by per-directory grep-verified completion sweeps, appropriate for a mechanical library migration rather than new business logic).

**Not yet done:** Implementation (I1 onward / T-049-01 onward). Dependency approvals still needed before `npm install` for `@nuxt/ui` (T-049-01), `@iconify-json/prime` (T-049-03), and optionally `@tanstack/vue-virtual` (T-049-37).

### Feature 048 – Fix Multi-Group Permissions — Spec/Plan/Tasks Drafted (prior session, 2026-07-01)

**Bug report:** A user who belongs to two groups on the same album (e.g. "All" = View only, "Support_VIP" = View+Access+Download) only received the grants of whichever group's `AccessPermission` row was created first. Reordering the shares flipped the outcome.

**Root cause:** `BaseAlbumImpl::current_user_permissions()` (`app/Models/BaseAlbumImpl.php:261-271`) uses `Collection::first()` to pick a single matching `AccessPermission` row instead of merging every matching row.

**Resolution direction (Q-048-01, resolved — Option A):** Collect every matching row (direct-user row + every row for a group the user belongs to) and OR each of the 5 boolean grant flags. No precedence between direct-user and group rows — most permissive always wins, matching the existing pattern already used elsewhere (`AlbumPolicy` ORs `current_user_permissions()` with `public_permissions()`; `canDeleteById`/`canEditById` already OR across groups in SQL).

**Follow-up design decision (user-requested):** instead of returning a synthetic `App\Models\AccessPermission` Eloquent instance (which is inherently persistable — mass-assignable, `save()`-able), the merged result is a new `App\DTO\EffectiveAccessPermission` (`final readonly class`, plain DTO, matches the existing `CheckoutDTO`/`PixelSizeAssignment` style). This makes "cannot be persisted by accident" a type-level guarantee (NFR-048-03). `current_user_permissions()`'s return type changes accordingly across `BaseAlbumImpl`, the `BaseAlbum` trait, and `@property` docblocks on `Album`/`TagAlbum`/`PersonAlbum` (FR-048-04).

**Key constraint:** Zero new DB queries (NFR-048-01) — `access_permissions` is already eager-loaded via `BaseAlbumImpl::$with`, and `$user->user_groups` is already read by the current (buggy) code, so the fix is a pure in-memory `Collection` merge.

**Not yet done:** Implementation (I1–I7 in plan.md / T-048-01–11 in tasks.md), ADR-0004, quality gates.

## Next Steps

1. Run the Analysis Gate checklist on Feature 052's spec, then draft `plan.md`/`tasks.md` for the Managed Cache Service (`docs/specs/4-architecture/features/052-managed-cache-service/`) — all 5 open questions are resolved, spec is feature-complete.
2. Confirm dependency approvals (`@nuxt/ui`, `@iconify-json/prime`) with the user, then start Feature 049 implementation at T-049-01 (install Nuxt UI in standalone Vue mode) — see [tasks.md](4-architecture/features/049-nuxt-ui-migration/tasks.md).
3. Alternatively/in parallel across sessions: start Feature 048 implementation at T-048-01 (repo-wide caller sweep) then T-048-02/03 (unit tests reproducing the bug) — see [tasks.md](4-architecture/features/048-fix-multi-group-permissions/tasks.md).
4. Feature 047 (Person Smart Album) remains drafted but not implemented — no active work this session.
5. Feature 042 Part B (I7–I10, admin maintenance photo title links) remains outstanding from a prior session — see [tasks.md](4-architecture/features/042-webshop-order-item-display/tasks.md) T-042-16 to T-042-20.

## Open Questions

None blocking. Q-052-01..05 resolved 2026-07-21 (Feature 052, all Option A — see spec.md and open-questions.md for full rationale). Q-049-01, Q-049-02, Q-049-03 resolved 2026-07-02 (ADR-0005). Q-048-01 resolved 2026-07-01.

## Key Artefacts

- Feature 052: [spec.md](4-architecture/features/052-managed-cache-service/spec.md) (feature-complete; no plan/tasks yet)
- Feature 049: [spec.md](4-architecture/features/049-nuxt-ui-migration/spec.md) · [plan.md](4-architecture/features/049-nuxt-ui-migration/plan.md) · [tasks.md](4-architecture/features/049-nuxt-ui-migration/tasks.md) · [ADR-0005](6-decisions/ADR-0005-nuxt-ui-migration.md)
- Feature 048: [spec.md](4-architecture/features/048-fix-multi-group-permissions/spec.md) · [plan.md](4-architecture/features/048-fix-multi-group-permissions/plan.md) · [tasks.md](4-architecture/features/048-fix-multi-group-permissions/tasks.md)
- Open questions: [open-questions.md](4-architecture/open-questions.md) (Q-052-01..05, Q-049-01..03, Q-048-01 — all resolved)
- Roadmap: [roadmap.md](4-architecture/roadmap.md)
- Knowledge map: [knowledge-map.md](4-architecture/knowledge-map.md) (Frontend Dependencies section annotated with the pending PrimeVue→Nuxt UI swap)
