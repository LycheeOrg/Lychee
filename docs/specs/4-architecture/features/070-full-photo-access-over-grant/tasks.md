# Feature 070 Tasks – Fix Full-Photo-Access Over-Grant

_Status: Draft_  
_Last updated: 2026-09-24_

> Stage tests before implementation. Mark `[x]` immediately after each passes.

## Checklist

- [x] T-070-00 – Analysis gate: re-read the five sites and the four collection resources; confirm A1.  
  _Verification:_ review only.

### I1 – Shared helper
- [x] T-070-01 – Failing tests: deny-by-default for an unresolved id (S-070-08); owner of an album-less photo (S-070-09).  
  _Verification:_ `php artisan test --filter=ResolvesPhotoGrantsTest`
- [x] T-070-02 – Implement `ResolvesPhotoGrants::downgradeMap()` (ownership + admin folded in).  
  _Verification:_ `php artisan test --filter=ResolvesPhotoGrantsTest`, `make phpstan`

### I2 – v2 Search (FR-070-03)
- [x] T-070-03 – Failing test: S-070-01 on `GET /api/v2/Search`.  
  _Verification:_ `php artisan test --filter=SearchFullPhotoAccessV2Test`
- [x] T-070-04 – `ResultsResource::fromData()` takes the map; `SearchController` builds it.  
  _Verification:_ `php artisan test --filter=SearchFullPhotoAccessV2Test`, `php artisan test --filter=SearchTest`

### I3 – v2 Album Photos (FR-070-04)
- [x] T-070-05 – Failing tests: S-070-01 on all three branches; S-070-06 multi-album under-grant.  
  _Verification:_ `php artisan test --filter=AlbumPhotosFullPhotoAccessTest`
- [x] T-070-06 – `PaginatedPhotosResource` takes the map; update all three `AlbumPhotosController` branches.  
  _Verification:_ `php artisan test --filter=AlbumPhotosFullPhotoAccessTest`, `make phpstan`

### I4 – Map position data (FR-070-05)
- [x] T-070-07 – Failing test: S-070-01 on root and album position data.  
  _Verification:_ `php artisan test --filter=PositionDataFullPhotoAccessTest`
- [x] T-070-08 – `PositionDataResource` takes the map; update both actions.  
  _Verification:_ `php artisan test --filter=PositionDataFullPhotoAccessTest`, `make phpstan`

### I5 – Embed + v2 Timeline (FR-070-06/07)
- [x] T-070-09 – Failing tests: S-070-01 on the embed stream and the v2 timeline.  
  _Verification:_ `php artisan test --filter=EmbedFullPhotoAccessTest`, `php artisan test --filter=TimelineFullPhotoAccessTest`
- [x] T-070-10 – `EmbedStreamResource` + `TimelineResource::fromData()` take the map.  
  _Verification:_ as above, `make phpstan`

### I7 – RSS feed (FR-070-10, GHSA-m9h3-925m-vvpp)
- [x] T-070-15 – Failing tests: S-070-11 (grant off → medium2x, medium fallback; grant on, owner, no-medium → original).  
  _Verification:_ `php artisan test --filter=RssTest`
- [x] T-070-16 – `Generate` resolves `downgradeMap()` and serves the authorized variant.  
  _Verification:_ `php artisan test --filter=RssTest`, `--filter=FullPhotoAccessOverGrantTest`, `make phpstan`, `php-cs-fixer`

### I6 – Coverage and gates
- [ ] T-070-11 – Query-count assertions per surface (S-070-07, NFR-070-01).  
  _Verification:_ per-surface test filters
- [x] T-070-12 – R3 canary: run the suites that use `original.url` as a real path.  
  _Verification:_ `php artisan test --filter=WatermarkerTest`, `--filter=PhotoAddTest`, `--filter=FixPermissionsTest`, `--filter=TakeDateTest`
- [ ] T-070-13 – Docs: roadmap, knowledge-map, resolve Q-069-13, refresh `_current-session.md`.
- [ ] T-070-14 – Full quality gate.  
  _Verification:_ `vendor/bin/php-cs-fixer fix`, `make phpstan`, scoped `--filter=` runs

## Notes / TODOs
- Run test classes **sequentially**; the shared SQLite file corrupts under concurrent runs. Hit twice this session — a background suite running alongside a foreground one produced a wave of bogus `ModelDBException: Updating user failed` fixture failures that look exactly like a real regression. If a suite that passed minutes ago suddenly fails in `setUp`, check for a concurrent run before debugging.

## Deviations from the plan

- **D-070-A — one test class, not five.** The plan listed a test class per surface (`SearchFullPhotoAccessV2Test`, `AlbumPhotosFullPhotoAccessTest`, ...). Implemented as a single `tests/Feature_v2/FullPhotoAccessOverGrantTest.php` with a method per surface: all seven share one setup (config ON, grant OFF, non-owner) and one response-walking helper, so five classes would have been five copies of the same scaffolding.
- **D-070-B — the parameter was removed, not retyped.** The plan (R4) assumed changing `bool` to `array` would make every stale call site a static error. It does not: the project runs phpstan at **level 3**, where `argument.type` is not reported (verified — a direct `--level=6` run does flag it). Removing the parameter instead makes call sites fail with `argument.unknown`, which **is** reported at level 3. That turned the gate into the driver of the refactor rather than a rubber stamp.
- **D-070-C — scope grew from five surfaces to seven.** `PersonPhotosController` passed `should_downgrade: false` (an unconditional grant, strictly worse than the config-driven ones) and `FlowItemResource` used an album-wide gate. Neither was in the spec's list; both were surfaced by D-070-B's mechanism.
- **D-070-E — RSS added as an eighth surface (2026-09-24).** Reported via GHSA-m9h3-925m-vvpp: `Generate` joined and emitted `SizeVariantType::ORIGINAL` unconditionally. The advisory suggested scoping the decision to the item's link album; FR-070-01's per-photo rule was applied instead so the feed agrees with every other surface. Two extra queries per feed (grants, medium variants), independent of item count.
- **D-070-D — AGENTS.md says "PHPStan level 6 minimum" but `phpstan.neon` sets `level: 3`.** Not changed here (out of scope, repo-wide blast radius) but recorded: it is why D-070-B was necessary.
