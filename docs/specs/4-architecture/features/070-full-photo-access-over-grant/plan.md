# Feature Plan 070 – Fix Full-Photo-Access Over-Grant

_Linked specification:_ [spec.md](spec.md)  
_Status:_ Draft  
_Last updated:_ 2026-09-22

## Vision & Success Criteria

Every surface emitting `size_variants.original.url` obeys `PhotoPolicy::canAccessFullPhoto()`, per photo, at one extra query per response. Success: S-070-01 fails before the change and passes after, on each of the five over-granting surfaces; query count independent of photo count; `make phpstan` 0 errors; every pre-existing test green.

## Scope Alignment

- **In scope:** the five config-driven surfaces (FR-070-03..07), the two album-scoped surfaces folded into the same mechanism, the `downgradeMap()` helper, and regression coverage.
- **Out of scope:** spec.md's Non-Goals — the config key itself (NG1), policy semantics (NG2), `grants_download` (NG3), v3 tiers (NG4), `AlbumPolicy`'s null-album fallback (NG5).

## Dependencies & Interfaces

| Dependency | Use |
|------------|-----|
| `ResolvesPhotoGrants` / `PhotoGrants` | Built in Feature 069 (D8); the batched grant resolution this feature reuses. |
| `AlbumQueryPolicy::joinSubComputedAccessPermissions()` | The `computed_access_permissions` sub-query underneath it. |
| `PhotoResource` | Keeps its per-photo `bool` parameter — unchanged, and has many callers outside this feature. |

## Assumptions & Risks

**Assumptions**
- A1 — The per-photo rule is universal: it needs no scope-specific variant, because `PhotoPolicy::canAccessFullPhoto()` is already defined for every case (owner, multi-album, no album). Verified while specifying.

**Risks / Mitigations**
- R1 — **Under-grant fix widens access** (Behavioural Change Register, final row). *Mitigation:* called out explicitly in the spec for veto; covered by S-070-06 so the change is visible in a test rather than implicit.
- R2 — **Deny-by-default could blank URLs if a map lookup silently misses.** *Mitigation:* NFR-070-03 + S-070-08 pin the behaviour; each surface's test asserts a *positive* case too, so a map that resolved nothing would fail loudly rather than quietly downgrade everything.
- R3 — **Existing tests use `original.url` as a real file path** (`WatermarkerTest`, `PhotoAddTest`, `FixPermissionsTest`, `TakeDateTest`, `RawUploadImagickTest`). If those fixtures are non-owner/non-granting, they would start seeing `null`. *Mitigation:* S-070-10 — run those suites; they act as the canary.
- R4 — Four collection-resource signatures change (8 call sites). *Mitigation:* type change from `bool` to `array` makes every missed call site a static error, not a silent bug.

## Increment Map

1. **I1 – `downgradeMap()` helper + unit tests** — fold ownership and admin into `ResolvesPhotoGrants`, returning `array<string,bool>`; deny-by-default (S-070-08, S-070-09).
2. **I2 – v2 Search** (FR-070-03) — `ResultsResource::fromData()` takes the map.
3. **I3 – v2 Album Photos** (FR-070-04) — all three branches; `PaginatedPhotosResource` takes the map. Includes the under-grant fix (S-070-06).
4. **I4 – Map position data** (FR-070-05) — both actions; `PositionDataResource` takes the map.
5. **I5 – Embed stream + v2 Timeline** (FR-070-06/07) — `EmbedStreamResource`, `TimelineResource`.
6. **I6 – Regression suite + gates** — S-070-01..10, query-count assertions, full quality gate, R3 canary suites.

## Scenario Tracking

| Scenario | Increment |
|----------|-----------|
| S-070-08, S-070-09 | I1 |
| S-070-01..05 | I2–I5, one test per surface |
| S-070-06 | I3 |
| S-070-07 | I6 |
| S-070-10 | I6 (R3 canary) |

## Analysis Gate

_Not yet run._ To be completed before I1.

## Exit Criteria

- All tasks `[x]`; S-070-01..10 covered.
- `make phpstan` 0 errors; `php-cs-fixer` clean.
- Every pre-existing test green, especially R3's canary suites.
- Q-069-13 marked resolved; roadmap and knowledge-map updated.

## Follow-ups / Backlog

- `grants_download` per photo (NG3) — the resolver already computes it; no consumer yet.
- The v8 search page gates photo download on `albumStore.rights`, which is `undefined` for an unscoped search — a separate frontend defect found while investigating.
